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
    // Show Error Message With Color
    public static function show(string $type, string $message, string $color = 'green'): string
    {
        // Get Exact Values
        $type = strtoupper($type);
        $color = strtolower($color);
        // Enable ANSI If OS Is Windows Terminal
        if((PHP_OS_FAMILY == 'Windows') && sapi_windows_vt100_support(STDOUT, true)){
            switch($color)
            {
                case 'green':
                    $str = "\n\033[1;32m[{$type}]\033[0m - {$message}\n\n";
                    break;

                case 'red':
                    $str = "\n\033[1;31m[{$type}]\033[0m - {$message}\n\n";
                    break;

                case 'yellow':
                    $str = "\n\033[1;33m[{$type}]\033[0m - {$message}\n\n";
                    break;

                case 'blue':
                    $str = "\n\033[1;34m[{$type}]\033[0m - {$message}\n\n";
                    break;

                default:
                    $str = "\n\033[1;0m[{$type}] - {$message}\n\n";
                    break;
            }
        }else{
            switch($color)
            {
                case 'green':
                    $str = "\n\033[1;32m[{$type}]\033[0m - {$message}\n\n";
                    break;

                case 'red':
                    $str = "\n\033[1;31m[{$type}]\033[0m - {$message}\n\n";
                    break;

                case 'yellow':
                    $str = "\n\033[1;33m[{$type}]\033[0m - {$message}\n\n";
                    break;

                case 'blue':
                    $str = "\n\033[1;34m[{$type}]\033[0m - {$message}\n\n";
                    break;

                default:
                    $str = "\n\033[1;0m[{$type}] - {$message}\n\n";
                    break;
            }
        }
        return $str;
    }
}