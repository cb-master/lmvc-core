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

// Directory Hndler
class Directory
{
    // Get Directory Folder
    /**
     * @param string $path - Required Argument as Directory
     */
    public static function folders(string $path)
    {
        $path = realpath($path);
        if(!$path){
            throw new Exception("Invalid Directory '{$path}'");
        }
        return glob("{$path}/*", GLOB_ONLYDIR);
    }

    // Get Directory Folder
    /**
     * @param string $path - Required Argument as Directory
     * @param string $ext - Required Argument as File Extension. As Example 'php'
     */
    public static function files(string $path, string $ext):array
    {
        $path = realpath($path);
        if(!$path){
            throw new Exception("Invalid Directory '{$path}'");
        }
        $ext = ltrim($ext, '.');
        return glob("{$path}/*.{$ext}");
    }
}