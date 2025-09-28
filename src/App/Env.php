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
namespace CBM\Core\App;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

// Application Environments
class Env
{
    // Environment Obkect
    private static ?object $instance = null;

    // Parameters
    private array $params = [];

    // Singleton Process
    private function __construct(){} // Prevent Making New Instance

    // Get Instance
    private static function getInstance()
    {
        self::$instance ??= new self();
        return self::$instance;
    }

    // Set Env
    /**
     * @param string $key Key Name of Environment. Example: 'route' or 'route|get'
     */
    public static function set(string $key, mixed $value): void
    {
        $instance = self::getInstance();
        $keys = array_filter(explode('|', $key), fn($k) => $k !== '');

        $ref = &$instance->params;
        foreach ($keys as $k) {
            if (!isset($ref[$k]) || !is_array($ref[$k])) {
                $ref[$k] = [];
            }
            $ref = &$ref[$k];
        }
        $ref = $value;
        return;
    }

    // Get Env
    public static function get(string $key, mixed $default = null): mixed
    {
        $instance = self::getInstance();
        $keys = array_filter(explode('|', $key), fn($k) => $k !== '');

        $ref = $instance->params;
        foreach ($keys as $k) {
            if (!isset($ref[$k])) {
                return $default;
            }
            $ref = $ref[$k];
        }
        return $ref;
    }

    // Get All Params
    public static function all(): array
    {
        $instance = self::getInstance();
        return $instance->params;
    }
}