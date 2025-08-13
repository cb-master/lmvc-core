<?php
/**
 * Laika PHP MVC Framework
 * Author: Showket Ahmed
 * License: MIT
 */

declare(strict_types=1);

namespace CBM\Core\Storage;

use RuntimeException;

/**
 * File Storage
 */
class FileStorage
{
    /**
     * @var self $instance
     */
    protected static ?self $instance = null;

    /**
     * @var string
     */
    protected string $path = BASE_PATH . '/storage';

    private function __construct(){}

    // Load Instance
    private static function instance()
    {
        self::$instance ??= new Static();
        if (!is_dir(self::$instance->path) && !mkdir(self::$instance->path, 0777, true)) {
            throw new RuntimeException('Cannot create storage directory: '.self::$instance->path);
        }
        return self::$instance;
    }

    public static function set(string $key, mixed $value): bool
    {
        $key = '/'.trim($key, '/');
        $file = self::instance()->path . $key . '.php';
        $data = "<?php return \n\t" . var_export($value, true) . ';';
        return file_put_contents($file, $data) !== false;
    }

    public static function get(string $key): mixed
    {
        $key = '/'.trim($key, '/');
        $file = self::instance()->path . $key . '.php';
        if (!file_exists($file)) return null;
        return include $file;
    }

    public static function pop(string $key): bool
    {
        $key = '/'.trim($key, '/');
        $file = self::instance()->path . $key . '.php';
        return file_exists($file) ? unlink($file) : false;
    }
}