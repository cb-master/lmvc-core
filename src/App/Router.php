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

// Namespace
namespace CBM\Core\App;

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\Uri;

Class Router
{
    protected $routes = [];
    protected $group = '';

    public function group($prefix, $callback)
    {
        $this->group = $prefix;
        $callback($this);
        $this->group = '';
    }

    public function get($uri, $callback)
    {
        $fullUri = $this->group . $uri;
        $this->routes['GET'][$this->normalize($fullUri)] = $callback;
    }

    public function post(string $uri, array|string|callable $callback)
    {
        $this->routes['POST'][$this->normalize($uri)] = $callback;
    }

    public function dispatch()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $path = $this->normalize('/' . Uri::path());

        foreach ($this->routes[$method] as $route => $callback) {
            $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([a-zA-Z0-9-_]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // remove full match

                if (is_string($callback)) {
                    [$controller, $method] = explode('@', $callback);
                    $controller = "CBM\\App\\Controllers\\$controller";
                    if (class_exists($controller)) {
                        call_user_func_array([new $controller, $method], $matches);
                        return;
                    }
                }elseif (is_array($callback)) {
                    [$controller, $method] = $callback;
                    $controller = "CBM\\App\\Controllers\\{$controller}";
                    if (class_exists($controller)) {
                        call_user_func_array([new $controller, $method], $matches);
                        return;
                    }
                } elseif (is_callable($callback)) {
                    call_user_func_array($callback, $matches);
                    return;
                }
            }
        }
        http_response_code(404);
        echo "404 - Not Found";
    }

    private function normalize($uri)
    {
        return rtrim($uri, '/') ?: '/';
    }
}