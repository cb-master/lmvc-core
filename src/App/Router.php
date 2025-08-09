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

// Deny direct access to this file
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\Uri;

Class Router
{
    protected $routes = [];

    public function get($uri, $callback)
    {
        $this->routes['GET'][$this->normalize($uri)] = $callback;
    }

    public function post($uri, $callback)
    {
        $this->routes['POST'][$this->normalize($uri)] = $callback;
    }

    public function dispatch()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $path = $this->normalize('/' . Uri::path());

        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];

            if (is_callable($callback)) {
                call_user_func($callback);
            } elseif (is_string($callback)) {
                [$controller, $method] = explode('@', $callback);
                $controller = "CBM\\App\\Controllers\\{$controller}";
                if (class_exists($controller)) {
                    call_user_func([new $controller, $method]);
                }
            }
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
    }

    private function normalize($uri)
    {
        return rtrim($uri, '/') ?: '/';
    }
}