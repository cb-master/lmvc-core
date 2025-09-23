<?php
/**
 * Laika PHP MVC Framework
 * Author: Showket Ahmed
 * License: MIT
 */

declare(strict_types=1);

namespace CBM\Core\Storage;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

/**
 * JSON Storage
 */
class JsonStorage
{
    /**
     * @var self $instance
     */
    protected static ?self $instance = null;

    /**
     * @var string
     */
    protected string $path = APP_PATH . '/../lf-storage';

    private function __construct(){}

    // Load Instance
    private static function instance()
    {
        self::$instance ??= new Static();
        return self::$instance;
    }

    public static function set(string $key, mixed $value): bool
    {
        $key = '/'.trim($key, '/');
        $file = self::instance()->path . $key . '.json';
        return file_put_contents($file, json_encode($value, JSON_PRETTY_PRINT|JSON_FORCE_OBJECT|JSON_NUMERIC_CHECK)) !== false;
    }

    public static function get(string $key): mixed
    {
        $key = '/'.trim($key, '/');
        $file = self::instance()->path . $key . '.json';
        if (!file_exists($file)) return null;
        return json_decode(file_get_contents($file), true);
    }

    public static function pop(string $key): bool
    {
        $key = '/'.trim($key, '/');
        $file = self::instance()->path . $key . '.json';
        return file_exists($file) ? unlink($file) : false;
    }
}