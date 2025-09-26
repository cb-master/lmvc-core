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

defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\Http\Request;
use CBM\Core\Uri;
use Exception;

class Router
{
    /**
     * @property object $instance Singleton Object
     */
    protected static object $instance;

    /**
     * @property array $routes Routes Parameters
     */
    protected array $routes = [];

    /**
     * @property string $group Group Name of Routes
     */
    protected string $group = '';

    /**
     * @property array $middleware Middlewares to Register in Route
     */
    protected array $middlewares = [];

    /**
     * @property array $globalMiddlewares Middlewares to Register Globally in All Routes
     */
    protected array $globalMiddlewares = [];

    /**
     * @property $fallback Fallback Route for 404 Page
     */
    protected $fallback = null;

    /**
     * @property $groupFallbacks Group Fallback Route for 404 Page
     */
    protected array $groupFallbacks = [];

    /**
     * @property ?array $lastRoute Set Last Route as [method, uri]
     */
    protected ?array $lastRoute = null; // [method, uri]

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

        // Normalize prefix
        $prefix = '/' . trim($prefix, '/');

        self::instance()->group = $previousGroup . $prefix;
        self::instance()->middlewares = array_merge(self::instance()->middlewares, $middlewares);

        $callback(self::instance());

        self::instance()->group = $previousGroup;
        self::instance()->middlewares = $previousMiddlewares;

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

    // Assign Middleware to the Last Registered Route or as Global Middleware
    /**
     * @param array|string $middlewares Middleware class names or callables
     * @return static
     */
    public function middleware(array|string $middlewares): self
    {
        // Wrap single item into an array
        if(!is_array($middlewares)) $middlewares = [$middlewares];

        if(self::instance()->lastRoute){
            [$lastMethod, $lastKey] = self::instance()->lastRoute;
            self::instance()->routes[$lastMethod][$lastKey]['middlewares'] = array_merge(
                self::instance()->routes[$lastMethod][$lastKey]['middlewares'] ?? [],
                $middlewares
            );
        }else{
            // fallback: global
            self::instance()->middlewares = array_merge(self::instance()->middlewares, $middlewares);
        }

        return self::$instance;
    }

    // Set 404 fallback
    /**
     * @param callable|array|string $callback The handler for 404 (callable, 'Controller@method', or [Controller, method])
     */
    public static function fallback(callable|array|string $callback): void
    {
        self::instance()->fallback = $callback;
        return;
    }

    // Set group 404 fallback
    /**
     * @param string $prefix The group prefix
     * @param callable|array|string $callback The handler for 404 (callable, 'Controller@method', or [Controller, method])
     */
    public static function groupFallback(string $prefix, callable|array|string $callback): void
    {
        $prefix = '/' . trim($prefix, '/');
        self::instance()->groupFallbacks[$prefix] = $callback;
        return;
    }

    /**
     * Dispatch Routes and Run Application
     * @return void
     */
    public static function dispatch(): void
    {
        // Get Request Method
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Get Instance
        $routes = self::instance();

        // Get Request Path
        $path   = $routes->normalize('/' . Uri::path());

        // Request Object
        $request = new Request();

        foreach ($routes->routes[$method] ?? [] as $route => $data) {
            // Allow {param} and {param:regex}
            $pattern = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)(:([^}]+))?\}/',
                function ($matches) {
                    // {param:regex}
                    if (!empty($matches[3])) {
                        return '(' . $matches[3] . ')';
                    }
                    // {param} default
                    return '([a-zA-Z0-9-_]+)';
                },
                $route
            );
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);

                $args['params'] =   $matches;
                $args['request']=   $request;
                $args['uri']    =   Uri::getInstance();

                // Run global + route middlewares
                foreach(array_merge($routes->globalMiddlewares, $data['middlewares']) as $middleware){
                    if (!$routes->runMiddleware($middleware, $args)) {
                        return; // Stop if middleware blocks request
                    }
                }

                $callback = $data['handler'];

                // Handle "Controller@method"
                if (is_string($callback)) {
                    [$controller, $methodName] = explode('@', $callback);
                    $controller = "CBM\\App\\Controller\\{$controller}";
                    $routes->invokeController($controller, $methodName, $matches, $request);
                    return;
                }

                // Handle [Controller, method]
                if (is_array($callback)) {
                    [$controller, $methodName] = $callback;
                    $controller = "CBM\\App\\Controller\\{$controller}";
                    $routes->invokeController($controller, $methodName, $matches, $request);
                    return;
                }

                // Handle closures/callables
                if (is_callable($callback)) {
                    call_user_func_array($callback, $matches);
                    return;
                }
            }
        }

        // No route matched
        http_response_code(404);
        // Check for group-specific fallback first
        foreach ($routes->groupFallbacks as $prefix => $callback) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                $routes->runFallback($callback, $request);
                return;
            }
        }

        // Global fallback
        if ($routes->fallback) {
            $routes->runFallback($routes->fallback, $request);
            return;
        }
        require_once __DIR__.'/404.php';
        return;
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
        $method = strtoupper($method);
        $uri = '/' . trim($uri, '/');
        $fullUri = self::instance()->group . $uri;
        $key = self::instance()->normalize($fullUri);

        self::instance()->routes[$method][$key] = [
            'handler'     => $callback,
            'middlewares' => array_merge(self::instance()->middlewares, $middlewares),
        ];

        // Last Route for Chaining
        self::instance()->lastRoute = [$method, $key];
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

        $args['params'] =   $params;
        $args['request']=   $request;
        $args['uri']    =   Uri::getInstance();

        call_user_func([new $controller, $methodName], $args);
        return;
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

    // Run fallback handler
    /**
     * @param callable|array|string $callback The handler for 404 (callable, 'Controller@method', or [Controller, method])
     * @param Request $request The current HTTP request
     * @return void
     */
    private function runFallback(callable|array|string $callback, Request $request): void
    {
        if (is_string($callback)) {
            [$controller, $methodName] = explode('@', $callback);
            $controller = "CBM\\App\\Controller\\{$controller}";
            $this->invokeController($controller, $methodName, [], $request);
            return;
        }

        if (is_array($callback)) {
            [$controller, $methodName] = $callback;
            $controller = "CBM\\App\\Controller\\{$controller}";
            $this->invokeController($controller, $methodName, [], $request);
            return;
        }

        if (is_callable($callback)) {
            call_user_func($callback, $request);
            return;
        }
    }
}