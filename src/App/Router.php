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

use CBM\Core\Uri;

class Router
{
    private static array $routes = [];
    private static array $globalBefore = [];
    private static array $globalAfter = [];
    private static array $middlewareGroups = [];
    // private static string $groupPrefix = '';
    private static array $groupMiddlewares = [];
    private static array $namedRoutes = [];
    private static $fallback = null;
    private static array $groupStack = [];
    private static array $groupFallbacks = [];
    private static ?string $lastMethod = null;
    private static ?string $lastUri    = null;

    ################################################################
    /* ------------------- ROUTE REGISTRATION ------------------- */
    ################################################################

    public function name(string $name): self
    {
        if (self::$lastMethod && self::$lastUri) {
            self::$routes[self::$lastMethod][self::$lastUri]['name'] = $name;
            self::$namedRoutes[$name] = [self::$lastMethod, self::$lastUri];
        }
        return $this;
    }

    public static function url(string $name, array $params = [], bool $absolute = false): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \Exception("Route name '{$name}' not defined.");
        }

        $uri = self::$namedRoutes[$name]['uri'];

        // Replace {param} placeholders
        foreach ($params as $key => $value) {
            $uri = preg_replace('/\{'.$key.'(:[^}]*)?\}/', (string) $value, $uri);
        }

        // Remove unreplaced params
        $uri = preg_replace('/\{[^}]+\}/', '', $uri);

        // Ensure leading slash
        $uri = '/' . trim($uri, '/');

        return $absolute ? Uri::base().trim($uri, '/') : $uri;
    }

    /**
     * Register Get Route
     * @param string $uri Route Uri
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function
     * @return self
     */
    public static function get(string $uri, callable|array|string $callback): self
    {
        $slug = self::groupedUri($uri);
        self::$routes['GET'][$slug] = self::makeRoute($callback);
        // track last registered route
        self::$lastMethod = 'GET';
        self::$lastUri    = $slug;
        return new self;
    }

    /**
     * Register Post Route
     * @param string $uri Route Uri
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function
     * @return self
     */
    public static function post(string $uri, callable|array|string $callback): self
    {
        $slug = self::groupedUri($uri);
        self::$routes['POST'][$slug] = self::makeRoute($callback);
        // track last registered route
        self::$lastMethod = 'POST';
        self::$lastUri    = $slug;
        return new self;
    }

    /**
     * Register Put Route
     * @param string $uri Route Uri
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function
     * @return self
     */
    public static function put(string $uri, callable|array|string $callback): self
    {
        $slug = self::groupedUri($uri);
        self::$routes['PUT'][$slug] = self::makeRoute($callback);
        // track last registered route
        self::$lastMethod = 'PUT';
        self::$lastUri    = $slug;
        return new self;
    }

    /**
     * Register Patch Route
     * @param string $uri Route Uri
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function
     * @return self
     */
    public static function patch(string $uri, callable|array|string $callback): self
    {
        $slug = self::groupedUri($uri);
        self::$routes['PATCH'][$slug] = self::makeRoute($callback);
        // track last registered route
        self::$lastMethod = 'PATCH';
        self::$lastUri    = $slug;
        return new self;
    }

    /**
     * Register Delete Route
     * @param string $uri Route Uri
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function
     * @return self
     */
    public static function delete(string $uri, callable|array|string $callback): self
    {
        $slug = self::groupedUri($uri);
        self::$routes['DELETE'][$slug] = self::makeRoute($callback);
        // track last registered route
        self::$lastMethod = 'DELETE';
        self::$lastUri    = $slug;
        return new self;
    }

    /**
     * Register Options Route
     * @param string $uri Route Uri
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function
     * @return self
     */
    public static function options(string $uri, callable|array|string $callback): self
    {
        $slug = self::groupedUri($uri);
        self::$routes['OPTIONS'][$slug] = self::makeRoute($callback);
        // track last registered route
        self::$lastMethod = 'OPTIONS';
        self::$lastUri    = $slug;
        return new self;
    }

    /**
     * Register Head Route
     * @param string $uri Route Uri
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function
     * @return self
     */
    public static function head(string $uri, callable|array|string $callback): self
    {
        $slug = self::groupedUri($uri);
        self::$routes['HEAD'][$slug] = self::makeRoute($callback);
        // track last registered route
        self::$lastMethod = 'HEAD';
        self::$lastUri    = $slug;
        return new self;
    }

    /**
     * Register Group Router
     * @param string $prefix Route Prefix. Example: 'admin'
     * @param callable $callback Callback With Router Groups.
     * @param array $middlewares Array of Middlewares. Example: ['InitiateDB', 'Auth']
     * @return self
     */
    public static function group(string $prefix, callable $callback, array $middlewares = []): self
    {
        // // Add Prefix for Group Fallback
        // self::$groupStack[] = $prefix;

        // // Get Previous Prefix & Middlewares
        // $previousPrefix = self::$groupPrefix;
        // $previousMiddlewares = self::$groupMiddlewares;

        // self::$groupPrefix .= $prefix;
        // self::$groupMiddlewares = array_merge(
        //     self::$groupMiddlewares,
        //     self::expandGroups($middlewares)
        // );

        // $callback(new self);

        // // Pop Group Fallback
        // array_pop(self::$groupStack);
        // // Reset to Previous Prefix & Middlewares
        // self::$groupPrefix = $previousPrefix;
        // self::$groupMiddlewares = $previousMiddlewares;
        // return new self;

        // push normalized prefix fragment onto stack (ensures leading slash, no trailing)
        self::$groupStack[] = self::normalize($prefix);

        // Get previous middlewares
        $previousMiddlewares = self::$groupMiddlewares;

        // merge group middlewares
        self::$groupMiddlewares = array_merge(
            self::$groupMiddlewares,
            self::expandGroups($middlewares)
        );

        // call user callback (allows Router::get() calls inside)
        $callback(new self);

        // Pop prefix & restore middlewares
        array_pop(self::$groupStack);
        self::$groupMiddlewares = $previousMiddlewares;

        return new self;
    }

    /**
     * Middleware Group
     * @param string $name Midleware Group Name. Example: Router::middlewareGroup('web', ['InitiateDB','Auth'])
     * @param array $middlewares Array of Middlewares. Router::middlewareGroup('web', ['InitiateDB','Auth'])
     * @return self
     */
    public static function middlewareGroup(string $name, array $middlewares): self
    {
        self::$middlewareGroups[$name] = $middlewares;
        return new self;
    }

    /**
     * Middleware
     * @param array|string $middlewares Midleware Name. Example: Router::middleware(['InitiateDB']) or Router::middleware('InitiateDB'])
     * @return self
     */
    public function middleware(array|string $middlewares): self
    {
        if (!is_array($middlewares)) $middlewares = [$middlewares];
        if (self::$lastMethod && self::$lastUri) {
            self::$routes[self::$lastMethod][self::$lastUri]['middlewares'] =
                array_merge(self::$routes[self::$lastMethod][self::$lastUri]['middlewares'], $middlewares);
        }
        return $this;
    }

    /**
     * After Middleware
     * @param array|string $middlewares Midleware Name. Example: Router::middleware(['InitiateDB']) or Router::middleware('InitiateDB'])
     * @return self
     */
    public function afterware(array|string $middlewares): self
    {
        if (!is_array($middlewares)) $middlewares = [$middlewares];
        if (self::$lastMethod && self::$lastUri) {
            self::$routes[self::$lastMethod][self::$lastUri]['after'] =
                array_merge(self::$routes[self::$lastMethod][self::$lastUri]['after'], $middlewares);
        }
        return $this;
    }

    /**
     * Global Middleware
     * @param array|string $middlewares Midleware Name. Example: Router::middleware(['InitiateDB']) or Router::middleware('InitiateDB'])
     * @param int $priority Middleware Priority. Default is 50
     * @return self
     */
    public static function globalMiddleware(array|string $middlewares, int $priority = 50): self
    {
        foreach ((array)$middlewares as $mw) {
            foreach (self::expandGroups([$mw]) as $expanded) {
                self::$globalBefore[] = ['name' => $expanded, 'priority' => $priority];
            }
        }
        return new self;
    }

    /**
     * Global After Middleware
     * @param array|string $middlewares Midleware Name. Example: Router::middleware(['InitiateDB']) or Router::middleware('InitiateDB'])
     * @param int $priority Middleware Priority. Default is 50
     * @return self
     */
    public static function globalAfter(array|string $middlewares, int $priority = 50): self
    {
        foreach ((array)$middlewares as $mw) {
            foreach (self::expandGroups([$mw]) as $expanded) {
                self::$globalAfter[] = ['name' => $expanded, 'priority' => $priority];
            }
        }
        return new self;
    }

    /**
     * Register Fallback Route for 404
     * @param callable|array|string $callback
     * @return self
     */
    public static function fallback(callable|array|string $callback): self
    {
        self::$fallback = $callback;
        return new self;
    }

    /**
     * Define fallback for current group
     * @param callable|array|string $callback
     * @return self
     */
    public static function groupFallback(callable|array|string $callback): self
    {
        if (!empty(self::$groupStack)) {
            $prefix = implode('', self::$groupStack);
            self::$groupFallbacks[$prefix] = $callback;
        }
        return new self;
    }

    ########################################################
    /* ------------------- DISPATCHER ------------------- */
    ########################################################

    public static function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = self::normalize('/' . Uri::path());

        if (!isset(self::$routes[$method])) {
            header('Content-Type: application/json');
            http_response_code(405);
            print(json_encode([
                'status'    =>  'failed',
                'message'   =>  'Method Not Allowed'
            ]));
            return;
        }

        foreach (self::$routes[$method] as $route => $data) {
            $pattern = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)(:([^}]+))?\}/',
                function ($params) {
                    $name = $params[1];
                    $regex = !empty($params[3]) ? $params[3] : '[a-zA-Z0-9-_]+';
                    return '(?P<' . $name . '>' . $regex . ')';
                },
                $route
            );
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $matches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Sort global before/after
                usort(self::$globalBefore, fn($a, $b) => $a['priority'] <=> $b['priority']);
                usort(self::$globalAfter, fn($a, $b) => $a['priority'] <=> $b['priority']);
                $before = array_column(self::$globalBefore, 'name');
                $after  = array_column(self::$globalAfter, 'name');

                // Merge before middlewares
                $middlewares = array_merge($before, $data['middlewares']);
                $callback = $data['callback'];

                // Collect after middlewares
                $afterwares = array_merge($data['after'], $after);

                // Run pipeline
                ob_start();
                $runner = function ($index) use (&$runner, $middlewares, $callback, $matches) {
                    if (isset($middlewares[$index])) {
                        [$name, $params] = self::parseMiddleware($middlewares[$index], $matches);
                        $middlewareClass = "CBM\\App\\Middleware\\{$name}";
                        if (class_exists($middlewareClass)) {
                            $instance = new $middlewareClass();
                            if (method_exists($instance, 'handle')) {
                                return $instance->handle(fn() => $runner($index + 1), ...$params);
                            }
                        }
                        return;
                    } else {
                        self::executeCallback($callback, $matches);
                    }
                };
                $result = $runner(0);

                // Combine echoed output + returned string (if any)
                $buffer = ob_get_clean();
                $response = ($buffer !== '' ? $buffer : '') . ($result ?? '');

                // Run AFTER middlewares (route + global)
                $allAfter = array_merge($data['after'], $after);

                foreach ($allAfter as $mw) {
                    [$name, $params] = self::parseMiddleware($mw, $matches);
                    $middlewareClass = "CBM\\App\\Middleware\\{$name}";
                    if (class_exists($middlewareClass)) {
                        $instance = new $middlewareClass();
                        if (method_exists($instance, 'terminate')) {
                            $response = $instance->terminate($response, ...$params);
                        }
                    }
                }

                echo $response;
                return;
            }
        }

        // Try group-specific fallbacks
        foreach (array_reverse(self::$groupFallbacks) as $prefix => $callback) {
            if (str_starts_with($path, $prefix)) {
                self::executeCallback($callback, []);
                return;
            }
        }

        // Try global fallback
        if (self::$fallback) {
            self::executeCallback(self::$fallback, []);
            return;
        }

        http_response_code(404);
        require_once __DIR__.'/404.php';
    }

    #######################################################
    /* ------------------- INSPECTOR ------------------- */
    #######################################################

    /**
     * Inspect middleware pipeline for a given method + uri
     * @param string $method Method Name: Example: 'get'
     * @param string $uri Route: Example: '/dashboard'
     */
    public static function inspect(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = self::normalize($uri);

        $isCli = (php_sapi_name() === 'cli');

        if (!isset(self::$routes[$method])) {
            echo $isCli
                ? "No Routes Registered for {$method}\n"
                : "No Routes Registered for {$method}<br>";
            return;
        }

        foreach (self::$routes[$method] as $route => $data) {
            // Allow {param} and {param:regex}
            $pattern = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)(:([^}]+))?\}/',
                function ($params) {
                    $name = $params[1];
                    $regex = !empty($params[3]) ? $params[3] : '[a-zA-Z0-9-_]+';
                    return '(?P<' . $name . '>' . $regex . ')';
                },
                $route
            );
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path)) {
                // Sort global middleware
                usort(self::$globalBefore, fn($a, $b) => $a['priority'] <=> $b['priority']);
                usort(self::$globalAfter, fn($a, $b) => $a['priority'] <=> $b['priority']);

                $before = array_column(self::$globalBefore, 'name');
                $after  = array_column(self::$globalAfter, 'name');

                $routeBefore = self::expandGroups($data['middlewares']);
                $routeAfter  = self::expandGroups($data['after']);

                // Build execution order
                $pipeline = array_merge($before, $routeBefore, ['[Controller]'], $routeAfter, $after);

                // Header
                echo $isCli
                    ? "=== Middleware Pipeline for [{$method} {$uri}] ===\n"
                    : "=== Middleware Pipeline for [{$method} {$uri}] ===<br>";

                // Steps
                foreach ($pipeline as $i => $step) {
                    $num = $i + 1;
                    echo $isCli
                        ? "{$num}. {$step}\n"
                        : "{$num}. {$step}<br>";
                }
                return;
            }
        }
        echo $isCli
            ? "No Route Matches {$method} {$uri}\n"
            : "No Route Matches {$method} {$uri}<br>";
        return;
    }

    public static function inspectAll(): void
    {
        $isCli = (php_sapi_name() === 'cli');

        if (empty(self::$routes)) {
            echo $isCli
                ? "No routes registered.\n"
                : "<p>No routes registered.</p>";
            return;
        }

        if ($isCli) {
            // CLI MODE → print console table
            echo str_repeat("=", 100) . "\n";
            echo sprintf("%-8s %-30s %-30s %-20s\n", "METHOD", "ROUTE", "NAME", "CALLBACK", "PIPELINE");
            echo str_repeat("-", 100) . "\n";

            foreach (self::$routes as $method => $routes) {
                foreach ($routes as $uri => $data) {
                    $callback = is_array($data['callback'])
                        ? implode('@', $data['callback'])
                        : (is_string($data['callback']) ? $data['callback'] : 'Closure');

                    $name = $data['name'] ?: '.';
                    $pipeline = implode(' -> ', self::buildPipeline($data));
                    echo sprintf("%-8s %-30s %-30s %-20s\n", $method, $uri, $name, $callback, $pipeline);
                }
            }
            echo str_repeat("=", 100) . "\n";
        } else {
            // Browser output: HTML table
            echo '<style>
                table.routes { width:100%; border-collapse: collapse; margin:20px 0; font-family:monospace; }
                table.routes th, table.routes td { border:1px solid #ccc; padding:6px 10px; text-align:left; }
                table.routes th { background:#f8f8f8; }
                table.routes tr:nth-child(even) { background:#fafafa; }
                .method { font-weight:bold; color:#0366d6; }
                .pipeline { color:#555; font-size:0.9em; }
            </style>';

            echo "<table class='routes'>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Route</th>
                        <th>Name</th>
                        <th>Callback</th>
                        <th>Pipeline</th>
                    </tr>
                </thead>
                <tbody>";

            foreach (self::$routes as $method => $routes) {
                foreach ($routes as $route => $data) {
                    $callbackInfo = self::callbackToString($data['callback']);
                    $pipeline = self::buildPipeline($data);
                    $name = $data['name'] ?? '-';

                    echo "<tr>
                        <td class='method'>{$method}</td>
                        <td>{$route}</td>
                        <td>{$name}</td>
                        <td>{$callbackInfo}</td>
                        <td class='pipeline'>" . implode(" → ", $pipeline) . "</td>
                    </tr>";
                }
            }

            echo "</tbody></table>";
        }
    }

    ######################################################
    /* ----------------- INTERNAL API ----------------- */
    ######################################################

    /**
     * Execute Callback
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function 
     * @param array ...$params Parameters from Slug & Middlewares
     */
    private static function executeCallback(callable|array|string $callback, array ...$params): void
    {
        if (is_string($callback)) {
            [$controller, $method] = explode('@', $callback);
            $controller = "CBM\\App\\Controller\\{$controller}";

            if(!class_exists($controller) || !method_exists($controller, $method)){
                throw new \Exception('Invalid Route Callback: '.print_r($callback, true), 500);
            }

            call_user_func_array([new $controller(), $method], $params);
            return;
        } elseif (is_array($callback)) {
            [$controller, $method] = $callback;
            $controller = "CBM\\App\\Controller\\{$controller}";
            // Check Controller & Methods
            if(!class_exists($controller) || !method_exists($controller, $method)){
                throw new \Exception('Invalid Route Callback: '.print_r($callback, true), 500);
            }
            if (class_exists($controller)) {
                call_user_func_array([new $controller(), $method], $params);
                return;
            }
        } elseif (is_callable($callback)) {
            call_user_func_array($callback, $params);
            return;
        }

        throw new \Exception('Invalid Route Callback: '.print_r($callback, true), 500);
        return;
    }

    private static function expandGroups(array $middlewares): array
    {
        $expanded = [];
        foreach ($middlewares as $mw) {
            if (isset(self::$middlewareGroups[$mw])) {
                $expanded = array_merge($expanded, self::$middlewareGroups[$mw]);
            } else {
                $expanded[] = $mw;
            }
        }
        return $expanded;
    }

    private static function parseMiddleware(string $middleware, array $matches): array
    {
        $parts = explode(':', $middleware, 2);
        $name = $parts[0];

        $routeParams = $matches; // Associative named params (['id' => 3])
        $extra = isset($parts[1]) ? explode(',', $parts[1]) : [];
        return [$name, array_merge($routeParams, ['action'=>$extra])];
    }

    private static function normalize(string $uri): string
    {
        $slug = '/' . trim($uri, '/');
        return $slug === '' ? '/' : $slug;
    }

    private static function groupedUri(string $uri): string
    {
        // join normalized stack fragments (each fragment starts with '/')
        $prefix = implode('', self::$groupStack); // e.g. '/admin/shop'
        $full   = rtrim($prefix, '/') . '/' . ltrim($uri, '/');

        return self::normalize($full);
    }

    private static function groupedMiddlewares(): array
    {
        return self::$groupMiddlewares;
    }
    private static function callbackToString($callback): string
    {
        if (is_string($callback)) {
            return $callback;
        } elseif (is_array($callback)) {
            return "{$callback[0]}@{$callback[1]}";
        } elseif ($callback instanceof \Closure) {
            return 'Closure';
        }
        return 'Unknown';
    }

    private static function buildPipeline(array $data): array
    {
        // Sort global middleware
        usort(self::$globalBefore, fn($a, $b) => $a['priority'] <=> $b['priority']);
        usort(self::$globalAfter, fn($a, $b) => $a['priority'] <=> $b['priority']);

        $before = array_column(self::$globalBefore, 'name');
        $after  = array_column(self::$globalAfter, 'name');

        $routeBefore = self::expandGroups($data['middlewares']);
        $routeAfter  = self::expandGroups($data['after']);

        return array_merge($before, $routeBefore, ['[Controller]'], $routeAfter, $after);
    }

    /**
     * Make Route
     * @param callable|array|string $callback Example: HomeController@index or ['HomeController','index'] or Anonimous function
     */
    private static function makeRoute(callable|array|string $callback)
    {
        return [
            'callback'      =>  $callback,
            'middlewares'   =>  self::groupedMiddlewares(),
            'after'         =>  [],
            'name'          =>  null
        ];
    }
}