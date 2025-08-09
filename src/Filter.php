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

use Exception;

class Filter
{
    // Filters Var
    private static array $filters = [];

    // Assets
    private static array $assets = [];

    // Add Filter Method
    /**
     * @param string $filter - Required Argument.
     * @param callable $callback - Required Argument.
     * @param int $priority - Optional Argument. Default is 10
     */
    public static function add_filter(string $filter, callable $callback, int $priority = 10)
    {
        self::$filters[$filter][$priority][] = $callback;
        ksort(self::$filters[$filter]);
    }

    // Apply Filters
    /**
     * @param string $tag - Required Argument.
     * @param mixed $value - Optional Argument. Default is Null.
     * @param mixed ...$args - Optional Arguments.
     */
    public static function apply_filter(string $filter, mixed $value = null, mixed ...$args):mixed
    {
        if (!isset(self::$filters[$filter])){
            return $value;
        }
        foreach (self::$filters[$filter] as $callbacks){
            foreach ($callbacks as $callback){
                $value = $callback($value, ...$args);
            }
        }    
        return $value;

    }

    // Assign Asset
    /**
     * @param string $key - Required Argument.
     * @param mixed $value - Required Argument.
     */
    public static function assign(string $key, mixed $value):void
    {
        self::$assets[$key][] = $value;
    }

    // Get Asset
    /**
     * @param string $key - Required Argument.
     */
    public static function getAssignedFilter(string $key):array
    {
        if(!isset(self::$assets[$key])){
            throw new Exception("Assigned Filter '{$key}' Does Not Exist!", 80000);
        }
        return self::$assets[$key];
    }
}