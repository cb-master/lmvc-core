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
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Model\DB;
use Throwable;

class Option
{
    // Table Name
    private static $table = 'options';

    // Option Key Column
    private static string $key = 'opt_key';

    // Option Value Column
    private static string $value = 'opt_value';

    /**
     * Get Option Value
     * @param string $name - Required Argument as Option Key.
     * @param mixed $default - If No Valu Exists/Found, Default will Return.
     * @return mixed
     */
    public static function get(string $name, mixed $default = null): mixed
    {
        try{
            $db = DB::getInstance();
            $option = $db->table(self::$table)->where(self::$key, '=', $name)->first(self::$value);
            return $option[self::$value] ?? $default;
        }catch(Throwable $th){}
        return $default;
    }

    // Set Option
    /**
     * @param string $name Required Argument. Option Name
     * @param string $value Required Argument. Option Value
     * @param bool $default Optional Argument. Default is false
     */
    public static function set(string $name, string $value, bool $default = false): bool
    {
        $db = DB::getInstance();
        $opt_default = $default ? 'yes' : 'no';

        $exist = $db->table(self::$table)->where(self::$key, '=', $name)->first();

        if(empty($exist)){
            return (bool) $db->table(self::$table)->insert([self::$key => $name, self::$value => $value, 'opt_default'=>$opt_default]);
        }

        return (bool) $db->table(self::$table)->where(self::$key, '=', $name)->update([self::$value=>$value]);
    }
}