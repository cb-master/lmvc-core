<?php
/**
 * Laika PHP MVC Framework
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com
 * License: MIT
 * This file is part of the Laika PHP MVC Framework.
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace CBM\Core\App;

defined('BASE_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\Uri;
use CBM\Core\Http\Request;
use ReflectionMethod;

class Router
{
    protected static object $instance;
    protected array $routes = [];
    protected string $group = '';
    protected array $middlewares = [];
    protected array $globalMiddlewares = [];

    private function __construct(){}

    // Load Instance
    protected static function instance(): static
    {
        self::$instance ??= new Static;
        return self::$instance;
    }

    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousGroup = self::instance()->group;
        $previousMiddlewares = self::instance()->middlewares;

        self::instance()->group = $previousGroup . $prefix;
        self::instance()->middlewares = array_merge(self::instance()->middlewares, $middlewares);

        $callback(self::instance());

        self::instance()->group = $previousGroup;
        self::instance()->middlewares = $previousMiddlewares;
    }

    public function middleware(array|string $middlewares): self
    {
        // Wrap single item into an array
        if (!is_array($middlewares)) {
            $middlewares = [$middlewares];
        }

        // Target the last registered route
        $lastMethod = array_key_last(self::instance()->routes);
        if ($lastMethod !== null) {
            $lastRoute = array_key_last(self::instance()->routes[$lastMethod]);
            if ($lastRoute !== null) {
                // Append to this specific route's middlewares
                self::instance()->routes[$lastMethod][$lastRoute]['middlewares'] = array_merge(
                    self::instance()->routes[$lastMethod][$lastRoute]['middlewares'] ?? [],
                    $middlewares
                );
                return self::instance();
            }
        }

        // Fallback: treat as global middleware
        self::instance()->middlewares = array_merge(self::instance()->middlewares, $middlewares);
        return self::instance();
    }

    public static function get(string $uri, callable|array|string $callback, array $middlewares = []): static
    {
        self::instance()->addRoute('GET', $uri, $callback, $middlewares);
        return self::instance();
    }

    public static function post(string $uri, callable|array|string $callback, array $middlewares = []): static
    {
        self::instance()->addRoute('POST', $uri, $callback, $middlewares);
        return self::instance();
    }

    public static function addGlobalMiddleware(string|callable $middleware): void
    {
        self::instance()->globalMiddlewares[] = $middleware;
    }

    public static function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path   = self::instance()->normalize('/' . Uri::path());
        $request = new Request();

        foreach (self::instance()->routes[$method] ?? [] as $route => $data) {
            $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([a-zA-Z0-9-_]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);

                // Run global + route middlewares
                foreach (array_merge(self::instance()->globalMiddlewares, $data['middlewares']) as $middleware) {
                    if (!self::instance()->runMiddleware($middleware, $request)) {
                        return; // Stop if middleware blocks request
                    }
                }

                $callback = $data['handler'];

                // Handle "Controller@method"
                if (is_string($callback)) {
                    [$controller, $methodName] = explode('@', $callback);
                    $controller = "CBM\\App\\Controller\\{$controller}";
                    self::instance()->invokeController($controller, $methodName, $matches, $request);
                    return;
                }

                // Handle [Controller, method]
                if (is_array($callback)) {
                    [$controller, $methodName] = $callback;
                    $controller = "CBM\\App\\Controller\\{$controller}";
                    self::instance()->invokeController($controller, $methodName, $matches, $request);
                    return;
                }

                // Handle closures/callables
                if (is_callable($callback)) {
                    call_user_func_array($callback, $matches);
                    return;
                }
            }
        }

        http_response_code(404);
        echo "404 - Not Found";
    }

    private function addRoute(string $method, string $uri, callable|array|string $callback, array $middlewares = []): void
    {
        $uri = '/'.trim($uri,'/');
        $fullUri = self::instance()->group . $uri;
        self::instance()->routes[$method][self::instance()->normalize($fullUri)] = [
            'handler'     => $callback,
            'middlewares' => array_merge(self::instance()->middlewares, $middlewares),
        ];
    }

    private function normalize(string $uri): string
    {
        return rtrim($uri, '/') ?: '/';
    }

    private function invokeController(string $controller, string $methodName, array $params, Request $request): void
    {
        if (!class_exists($controller)) {
            http_response_code(500);
            echo "Controller {$controller} not found";
            return;
        }

        $reflection = new ReflectionMethod($controller, $methodName);
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin() && $type->getName() === Request::class) {
                $args[] = $request;
            } else {
                $args[] = array_shift($params);
            }
        }

        call_user_func_array([new $controller, $methodName], $args);
    }

    private function runMiddleware(string|callable $middleware, Request $request): bool
    {
        if (is_callable($middleware)) {
            return $middleware($request) !== false;
        }

        if (class_exists($middleware)) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                return $instance->handle($request) !== false;
            }
        }

        return true; // If middleware not found, allow request
    }
}