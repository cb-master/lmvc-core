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
namespace CBM\Core;

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

// Cookie Hndler
class Cookie
{
    // Set Cookie
    /**
    * @param ?string $key Required Cookie Name Key
    * @param ?string $value Required Cookie value
    * @param int $expires Default is time() + 7 Days
    * @param ?string $path Optional Argument. Default is '/'.
    */
    public static function set(string $name, string $value, int $expires = 604800, string $path = '/'):bool
    {
        return setcookie($name, $value, [
            'expires' => time() + $expires,
            'path' => $path,
            'domain' => Uri::host(),
            'secure' => Uri::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    // Get Cookie
    /**
    * @param ?string $key - Required Cookie Name
    */
    public static function get(string $name):string
    {
        return $_COOKIE[$name] ?? '';
    }

    // Destroy Cookie
    /**
    * @param string $key - Required Cookie Name
    */
    public static function pop(string $name):bool
    {
        if(isset($_COOKIE[$name])){
            self::set($name, '', -1);
        }
        return true;
    }
}