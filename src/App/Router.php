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

use CBM\Core\Http\Request;
use CBM\Core\Uri;
use Exception;

class Router
{
    protected static object $instance;
    protected array $routes = [];
    protected string $group = '';
    protected array $middlewares = [];
    protected array $globalMiddlewares = [];

    private function __construct(){} // Prevent Object Creation
    public function __clone(){}     // Prevent Cloning
    public function __wakeup(){}     // Prevent Unserialization

    // Load Instance
    private static function instance(): self
    {
        self::$instance ??= new self();
        return self::$instance;
    }

    ####################################################################################################
    ## --------------------------------------- PUBLIC METHODS --------------------------------------- ##
    ####################################################################################################

    // Group Routes with Prefix and Middlewares
    /**
     * @param string $prefix URI prefix for the group
     * @param callable $callback Function that defines the routes within the group
     * @param array $middlewares Middlewares to apply to all routes in the group
     * @return static
     */
    public static function group(string $prefix, callable $callback, array $middlewares = []): self
    {
        $previousGroup = self::instance()->group;
        $previousMiddlewares = self::instance()->middlewares;

        self::instance()->group = $previousGroup . $prefix;
        self::instance()->middlewares = array_merge(self::instance()->middlewares, $middlewares);

        $callback(self::instance());

        self::instance()->group = $previousGroup;
        self::instance()->middlewares = $previousMiddlewares;

        return self::$instance;
    }

    // Assign Middleware to the Last Registered Route or as Global Middleware
    /**
     * @param array|string $middlewares Middleware class names or callables
     * @return static
     */
    public function middleware(array|string $middlewares): self
    {
        // Wrap single item into an array
        if(!is_array($middlewares)) $middlewares = [$middlewares];

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
                return self::$instance;
            }
        }

        // Fallback: treat as global middleware
        self::instance()->middlewares = array_merge(self::instance()->middlewares, $middlewares);
        return self::$instance;
    }

    // Define GET Route
    /**
     * @param string $uri The URI pattern
     * @param callable|array|string $callback The handler (callable, 'Controller@method', or [Controller, method])
     * @param array $middlewares Middlewares to apply to this route
     * @return static
     */
    public static function get(string $uri, callable|array|string $callback, array $middlewares = []): static
    {
        self::instance()->addRoute('GET', $uri, $callback, $middlewares);
        return self::$instance;
    }

    // Define POST Route
    /**
     * @param string $uri The URI pattern
     * @param callable|array|string $callback The handler (callable, 'Controller@method', or [Controller, method])
     * @param array $middlewares Middlewares to apply to this route
     * @return static
     */
    public static function post(string $uri, callable|array|string $callback, array $middlewares = []): static
    {
        self::instance()->addRoute('POST', $uri, $callback, $middlewares);
        return self::$instance;
    }

    // Define PUT Route
    /**
     * @param string $uri The URI pattern
     * @param callable|array|string $callback The handler (callable, 'Controller@method', or [Controller, method])
     * @param array $middlewares Middlewares to apply to this route
     * @return static
     */
    public static function put(string $uri, callable|array|string $callback, array $middlewares = []): static
    {
        self::instance()->addRoute('PUT', $uri, $callback, $middlewares);
        return self::$instance;
    }

    // Define PATCH Route
    /**
     * @param string $uri The URI pattern
     * @param callable|array|string $callback The handler (callable, 'Controller@method', or [Controller, method])
     * @param array $middlewares Middlewares to apply to this route
     * @return static
     */
    public static function patch(string $uri, callable|array|string $callback, array $middlewares = []): static
    {
        self::instance()->addRoute('PATCH', $uri, $callback, $middlewares);
        return self::$instance;
    }

    // Define DELETE Route
    /**
     * @param string $uri The URI pattern
     * @param callable|array|string $callback The handler (callable, 'Controller@method', or [Controller, method])
     * @param array $middlewares Middlewares to apply to this route
     * @return static
     */
    public static function delete(string $uri, callable|array|string $callback, array $middlewares = []): static
    {
        self::instance()->addRoute('DELETE', $uri, $callback, $middlewares);
        return self::$instance;
    }

    // Define OPTIONS Route
    /**
     * @param string $uri The URI pattern
     * @param callable|array|string $callback The handler (callable, 'Controller@method', or [Controller, method])
     * @param array $middlewares Middlewares to apply to this route
     * @return static
     */
    public static function options(string $uri, callable|array|string $callback, array $middlewares = []): static
    {
        self::instance()->addRoute('OPTIONS', $uri, $callback, $middlewares);
        return self::$instance;
    }

    // Add Global Middleware
    /**
     * @param string|callable $middleware Middleware class name or callable
     * @return void
     */
    public static function addGlobalMiddleware(string|callable $middleware): void
    {
        self::instance()->globalMiddlewares[] = $middleware;
    }

    // Dispatch the URI & Request
    public static function dispatch(): void
    {
        // Get Request Method
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Get Request Path
        $path   = self::instance()->normalize('/' . Uri::path());

        // Request Object
        $request = new Request();

        foreach (self::instance()->routes[$method] ?? [] as $route => $data) {
            $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([a-zA-Z0-9-_]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);

                $args['params'] = $matches;
                $args['request'] = $request;

                // Run global + route middlewares
                foreach (array_merge(self::instance()->globalMiddlewares, $data['middlewares']) as $middleware) {
                    if (!self::instance()->runMiddleware($middleware, $args)) {
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

    #####################################################################################################
    ## --------------------------------------- PRIVATE METHODS --------------------------------------- ##
    #####################################################################################################

    // Add Route
    /**
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $uri The URI pattern
     * @param callable|array|string $callback The handler (callable, 'Controller@method', or [Controller, method])
     * @param array $middlewares Middlewares to apply to this route
     * @return void
     */
    private function addRoute(string $method, string $uri, callable|array|string $callback, array $middlewares = []): void
    {
        $uri = '/'.trim($uri,'/');
        $fullUri = self::instance()->group . $uri;
        self::instance()->routes[strtoupper($method)][self::instance()->normalize($fullUri)] = [
            'handler'     => $callback,
            'middlewares' => array_merge(self::instance()->middlewares, $middlewares),
        ];
    }

    // Normalize URI by removing trailing slashes
    /**
     * @param string $uri The URI to normalize
     * @return string The normalized URI
     */
    private function normalize(string $uri): string
    {
        return rtrim($uri, '/') ?: '/';
    }

    // Invoke Controller Method with Dependency Injection
    /**
     * @param string $controller Controller class name
     * @param string $methodName Method name to invoke
     * @param array $params Parameters extracted from the URL
     * @param Request $request The current HTTP request
     * @return void
     */
    private function invokeController(string $controller, string $methodName, array $params, Request $request): void
    {
        if (!class_exists($controller)) {
            http_response_code(500);
            throw new Exception("Controller class {$controller} does not exist");
            return;
        }

        $args['params'] = $params;
        $args['request'] = $request;

        // $reflection = new ReflectionMethod($controller, $methodName);
        // $args = [];

        // foreach ($reflection->getParameters() as $param) {
        //     $type = $param->getType();
        //     if ($type && !$type->isBuiltin() && $type->getName() === Request::class) {
        //         $args[] = $request;
        //     } else {
        //         $args[] = $params;
        //     }
        // }

        call_user_func([new $controller, $methodName], $args);
    }

    // Run Middleware
    /**
     * @param string|callable $middleware Middleware class name or callable
     * @param mixed ...$args The current HTTP request
     * @return bool True to continue, false to stop the request
     */
    private function runMiddleware(string|callable $middleware, array ...$args): bool
    {
        if(is_callable($middleware)){
            return call_user_func_array($middleware, $args) !== false;
        }

        if(class_exists($middleware)){
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                return call_user_func_array([$instance, 'handle'], $args) !== false;
            }
        }

        return true; // If middleware not found, allow request
    }
}