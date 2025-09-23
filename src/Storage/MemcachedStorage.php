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
use Memcached as PhPMemcached;

/**
 * Memcached Storage
 */
class MemcachedStorage
{
    /**
     * @var self $instance
     */
    protected static ?self $instance = null;

    /**
     * @var PhPMemcached
     */
    protected PhPMemcached $client;

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
     * @var string $username
     */
    protected string $username;

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

    // Configure Memcached Connection
    /**
     * @param array $config Required Memcached Config to Connect.
     * Required Array keys are driver,host,port,prefix,username,password
     * @throws InvalidArgumentException
     */
    public static function config(array $config)
    {
        // Check Memcached Extension is Loaded;
        if(!extension_loaded('memcached')) throw new InvalidArgumentException("'memcached' Extension is Missing!");

        $instance = self::instance();

        // Set Host
        $instance->host = $config['host'] ?? '127.0.0.1';
        // Set Port
        $instance->port = (int) ($config['port'] ?? 11211);
        // Set Prefix
        $instance->prefix = ($config['prefix'] ?? 'cbm');
        // Set Username
        $instance->username = $config['username'] ?? '';
        // Set Password
        $instance->password = $config['password'] ?? '';

        $instance->client = new PhPMemcached();

        // avoid adding duplicate servers if config() is called multiple times
        $servers = $instance->client->getServerList();
        if (empty($servers)) {
            $instance->client->addServer($instance->host, $instance->port);
        }

        $instance->client->setOption(PhPMemcached::OPT_PREFIX_KEY, $instance->prefix . ':');

        // SASL auth (needs binary protocol)
        if ($instance->username && $instance->password) {
            $instance->client->setOption(PhPMemcached::OPT_BINARY_PROTOCOL, true);
            $instance->client->setSaslAuthData($instance->username, $instance->password);
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
        return self::instance()->client->set($key, $value, $expiration);
    }

    // Get Value
    /**
     * @param string $key Key Name
     * @return mixed
     */
    public static function get($key): mixed
    {
        $instance = self::instance()->client;
        $result = $instance->get($key);

        // Return null if the key does not exist
        if (($result === false) && ($instance->getResultCode() !== PhPMemcached::RES_SUCCESS)) {
            return null;
        }
        return $result;
    }

    // Remove Value
    /**
     * @param string $key Key Name
     * @return bool
     */
    public static function pop($key): bool
    {
        $instance = self::instance();
        if ($instance::get($key) !== null) {
            return $instance->client->delete($key);
        }
        return false;
    }
}