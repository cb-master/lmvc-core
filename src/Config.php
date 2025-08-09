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

#[\AllowDynamicProperties]

class Config
{
    // Instance
    private static null|object $instance = null;

    // Initiate Instance
    private static function instance()
    {
        self::$instance ??= new Static;
        return self::$instance;
    }

    // Set System Property and Value
    /**
     * @param string|array $array - Required Argument. This is the File Name in 'system' Folder. Example: ['<file_name>' => '<file_return_values>']
     */
    public static function set(string|array $paths):void
    {
        $paths = is_array($paths) ? $paths : [$paths];
        foreach($paths as $path){
            $key = strtolower(basename($path, '.php'));
            if(($key != 'index') && preg_match('/^[a-zA-Z0-9_]+$/', $key)){
                self::instance()->$key = require($path);
            }
        }
    }

    // Get Function Value
    public static function get(string $property, ?string $key = null): mixed
    {
        if(!property_exists(self::instance(), $property)){
            return false;
        }
        return $key ? (self::instance()->{$property}[$key] ?? '') : self::instance()->{$property};
    }

    // Change Config Value in File
    /**
     * @param string $property - Required Argument. This is the File Name in 'system' Folder
     * @param string $key - Required Argument
     * @param string $value - Required Argument
     */
    public static function change(string $property, string $key, string $value): int|bool
    {
        if(!property_exists(self::instance(), $property)){
            throw new \Exception("'{$property}' Does Not Exist!");
        };
        $file = BASE_PATH."/config/{$property}.php";
        $content = file_get_contents($file);
        if(!preg_match("/'{$key}'\s*=>\s*'[^']*'/i", $content)){
            throw new \Exception("Key '{$key}' Does Not Exist in System Property '{$property}'!");
        };
        $content = preg_replace("/'{$key}'\s*=>\s*'[^']*'/i", "'{$key}' => '{$value}'", $content);
        return file_put_contents($file, $content);
    }
}