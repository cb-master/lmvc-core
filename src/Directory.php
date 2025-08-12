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

namespace CBM\Core;

defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use Exception;

class Directory
{
    public static function folders(string $path): array
    {
        $path = realpath($path);
        if (!$path || !is_dir($path)) {
            throw new Exception("Invalid directory: '{$path}'");
        }
        return glob("{$path}/*", GLOB_ONLYDIR) ?: [];
    }

    public static function files(string $path, string $ext = '*'): array
    {
        $path = realpath($path);
        if (!$path || !is_dir($path)) {
            throw new Exception("Invalid directory: '{$path}'");
        }
        $ext = ltrim($ext, '.');
        return glob("{$path}/*.{$ext}") ?: [];
    }

    public static function exists(string $path): bool
    {
        return is_dir($path);
    }

    public static function make(string $path, int $permissions = 0755, bool $recursive = true): bool
    {
        if (self::exists($path)) {
            return true;
        }
        return mkdir($path, $permissions, $recursive);
    }

    public static function delete(string $path): bool
    {
        if (!self::exists($path)) {
            return false;
        }
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $fullPath = "{$path}/{$file}";
            if (is_dir($fullPath)) {
                self::delete($fullPath);
            } else {
                unlink($fullPath);
            }
        }
        return rmdir($path);
    }

    public static function empty(string $path): bool
    {
        if (!self::exists($path)) {
            return false;
        }
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $fullPath = "{$path}/{$file}";
            if (is_dir($fullPath)) {
                self::delete($fullPath);
            } else {
                unlink($fullPath);
            }
        }
        return true;
    }

    /**
     * Recursively scans a directory.
     * @param string $path Directory path
     * @param bool $includeDirs Whether to include directories in the result
     * @param string|array $ext File extension(s) to filter (e.g., 'php' or ['php','json']), or '*' for all
     * @return array
     * @throws Exception
     */
    public static function scanRecursive(string $path, bool $includeDirs = true, string|array $ext = '*'): array
    {
        $path = realpath($path);
        if (!$path || !is_dir($path)) {
            throw new Exception("Invalid directory: '{$path}'");
        }

        $result = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        // Normalize extension filter
        $extList = is_array($ext) ? array_map('strtolower', $ext) : [$ext];
        $extList = array_map(fn($e) => ltrim($e, '.'), $extList);

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if ($includeDirs) {
                    $result[] = $item->getPathname();
                }
            } else {
                if ($extList !== ['*']) {
                    $fileExt = strtolower(pathinfo($item->getFilename(), PATHINFO_EXTENSION));
                    if (!in_array($fileExt, $extList, true)) {
                        continue;
                    }
                }
                $result[] = $item->getPathname();
            }
        }

        return $result;
    }
}