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

namespace CBM\Core\Storage;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use InvalidArgumentException;
use Redis as PhPRedis;
use Exception;

/**
 * Redis Storage
 */
class RedisStorage
{
    /**
     * @var self $instance
     */
    protected static ?self $instance = null;

    /**
     * @var PhPRedis
     */
    protected PhPRedis $client;

    /**
     * @var string $host
     */
    protected string $host;

    /**
     * @var int $port
     */
    protected int $port;

    /**
     * @var string $prefix
     */
    protected string $prefix;

    /**
     * @var string $password
     */
    protected string $password;

    private function __construct(){}

    // Load Instance
    private static function instance()
    {
        self::$instance ??= new Static();
        return self::$instance;
    }

    // Configure Redis Connection
    /**
     * @param array $config Required Redis Config to Connect.
     * Required Array keys are driver,host,port,prefix,password
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function config(array $config)
    {
        // Check Redis Extension is Loaded;
        if(!extension_loaded('redis')) throw new InvalidArgumentException("'redis' Extension is Missing!");

        $instance = self::instance();

        // Set Host
        $instance->host = $config['host'] ?? '127.0.0.1';
        // Set Port
        $instance->port = (int) ($config['port'] ?? 11211);
        // Set Prefix
        $instance->prefix = ($config['prefix'] ?? 'cbm');
        // Set Password
        $instance->password = $config['password'] ?? '';

        $instance->client = new PhPRedis();

        if (!$instance->client->connect($instance->host, $instance->port)) {
            throw new Exception("Unable to connect to Redis at {$instance->host}:{$instance->port}");
        }

        if ($instance->password && !$instance->client->auth($instance->password)) {
            throw new Exception("Redis authentication failed!");
        }
    }

    // Set Value
    /**
     * @param string $key Key Name
     * @param mixed $value Key Value
     * @param int $expiration Default is 0 for No Expire Time
     * @return bool
     */
    public static function set(string $key, mixed $value, int $expiration = 0): bool
    {
        $instance = self::instance();
        $key = $instance->prefix . ':' . $key;

        return ($expiration > 0) ? $instance->client->setex($key, $expiration, serialize($value)) :
                                    $instance->client->set($key, serialize($value));
    }

    // Get Value
    /**
     * @param string $key Key Name
     * @return mixed
     */
    public static function get($key): mixed
    {
        $instance = self::instance();
        $key = $instance->prefix . ':' . $key;

        $value = $instance->client->get($key);
        return $value !== false ? unserialize($value) : null;
    }

    // Remove Value
    /**
     * @param string $key Key Name
     * @return bool
     */
    public static function pop($key): bool
    {
        $instance = self::instance();
        $key = $instance->prefix . ':' . $key;

        return (bool) $instance->client->del($key);
    }
}