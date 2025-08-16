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
namespace CBM\Core\Console;

class Message
{
    /**
     * @return string
     */
    public static function default(): string
    {
        return "Please Run: 'php laika --h' or 'php laika --help' for help\n";
    }

    /**
     * @return string
     */
    public static function error(): string
    {
        return "Something Went Wrong\n";
    }

    /**
     * @param string $str Service Name. Example: middleware, model, view
     * @return string
     */
    public static function invalidParameter(string $str): string
    {
        return "Invalid Parameter Given: {$str}\n";
    }

    /**
     * @param string $str Service Name. Example: middleware, model, view
     * @return string
     */
    public static function invalidService(string $str): string
    {
        return "{$str} Service Name Not Defined or Invalid\n";
    }

    /**
     * @param string $str Service Name. Example: SampleMiddleware, SampleModel etc.
     * @return string
     */
    public static function exist(string $str): string
    {
        return "{$str} Already Exist!\n";
    }

    // Created Message
    /**
     * @return string
     */
    public static function created(string $str): string
    {
        return "{$str} Created Successfully!\n";
    }
}