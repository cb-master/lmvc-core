<?php
/**
 * Laika PHP MVC Framework
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com
 * License: MIT
 * This file is part of the Laika PHP MVC Framework.
 */

declare(strict_types=1);

namespace CBM\Core;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

class Cookie
{
    /**
     * Set a cookie (supports string, array, or object)
     *
     * @param string $name    Cookie name
     * @param mixed  $value   String, array, or object to store
     * @param int    $expires Lifetime in seconds (default 7 days)
     * @param string $path    Cookie path (default '/')
     */
    public static function set(string $name, mixed $value, int $expires = 604800, string $path = '/'): bool
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_THROW_ON_ERROR);
        } else {
            $value = (string)$value;
        }
        return setcookie($name, rawurlencode($value), [
            'expires'  => time() + $expires,
            'path'     => $path,
            'domain'   => Uri::host(),
            'secure'   => Uri::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    /**
     * Get a cookie value (will decode JSON if possible)
     *
     * @param string $name Cookie name
     * @return mixed Returns string or decoded array/object if JSON
     */
    public static function get(string $name): mixed
    {
        if (!isset($_COOKIE[$name])) {
            return null;
        }

        $value = rawurldecode($_COOKIE[$name]);

        // Try to decode JSON; if fails, return raw string
        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return $decoded;
        } catch (\JsonException $e) {
            return $value;
        }
    }

    /**
     * Remove a cookie
     *
     * @param string $name Cookie name
     * @param string $path Cookie path. Default is '/'
     */
    public static function pop(string $name, string $path = '/'): bool
    {
        if (isset($_COOKIE[$name])) {
            setcookie($name, '', [
                'expires'  => time() - 3600,
                'path'     => $path,
                'domain'   => Uri::host(),
                'secure'   => Uri::isHttps(),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            unset($_COOKIE[$name]);
        }
        return true;
    }
}