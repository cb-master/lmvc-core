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

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Model\ConnectionManager;
use CBM\Session\Session;
use Exception;
use Throwable;
use PDO;

class Auth
{
    // Instance Object
    private static ?object $instance = null;

    // Session For
    private string $for;

    // PDO Object
    private PDO $pdo;

    // DB Table Name
    private string $table;

    // Cookie Name
    private string $cookie = '__AUTH';

    // Cookie Expire After TTL
    private int $ttl = 1800; // 1800 Seconds or 30 Minutes

    // User Data
    private ?array $user = null;

    // Event ID
    private ?string $event;

    // Real Time
    private int $time;

    // Initiate Session
    /**
     * @param string $table. Table name. Example: 'authentication'
     */
    private function __construct(string $table, string $for)
    {
        $this->for = strtoupper($for);
        $this->pdo = ConnectionManager::get();
        $this->table = $table;
        $this->event = Session::get($this->cookie, $this->for);
        $this->time = (int) option('start.time', time());
    }

    // Config Instance
    /**
     * @param PDO $pdo. PDO Instance
     * @param ?string $table. Table name. Default is null
     * @return self
     */
    public static function config(string $table, string $for = 'APP'): self
    {
        self::$instance ??= new self($table, $for);
        // Create Table if Not Exist
        try{
            $makeSql = "CREATE TABLE IF NOT EXISTS " . self::$instance->table . " (event VARCHAR(64) NOT NULL,data TEXT NOT NULL,expire INT NOT NULL,created INT NOT NULL, INDEX(event), INDEX(expire), INDEX(created));";
            $stmt = self::$instance->pdo->prepare($makeSql);
            $stmt->execute();
        }catch(Throwable $th){
            if(option('debug')){
                ErrorHandler::handleException($th);
            }
        }

        return self::$instance;
    }

    // Checkng TTL
    /**
     * @param int $ttl Required TTL Numer. Sytem Default is 1800 Seconds or 30 Minutes
     * @return void
     */
    public static function setTtl(int $ttl): void
    {
        $obj = self::$instance ?? throw new Exception("Please Initiate Auth::config() First");
        $obj->ttl = $ttl;
    }

    // Create Auth Token in DB Table
    /**
     * @param array $user User Data
     */
    public static function create(array $user): string
    {
        // Get Instance & Set User
        $obj = self::$instance ?? throw new Exception("Please Initiate Auth::config() First");
        $obj->user = $user;

        // Get Event ID
        $obj->event = bin2hex(random_bytes(32));
        // Set Expire Time
        $time = $obj->time;
        $expire = $time + $obj->ttl;
        // Make SQL
        $sql = "INSERT INTO {$obj->table} (event, data, expire, created) VALUES (:event, :data, :expire, :created)";
        $stmt = $obj->pdo->prepare($sql);

        $stmt->execute([
            ':event'    =>  $obj->event,
            ':data'     =>  json_encode($user),
            ':expire'   =>  $expire,
            ':created'  =>  $time,
        ]);

        // Set Session
        Session::set($obj->cookie, $obj->event, $obj->for);
        
        return $obj->event;
    }

    // Get User Data
    /**
     * Check User is Authenticated and Not Expired
     * @return ?array
     */
    public static function user(): ?array
    {
        // Check Instance Loaded
        $obj = self::$instance ?? throw new Exception("Please Initiate Auth::config() First");

        // Clear Session if Event Mssing
        if (empty($obj->event)) {
            Session::pop($obj->cookie, $obj->for);
            return null;
        }

        // Get DB Data
        $stmt = $obj->pdo->prepare("SELECT data, expire FROM {$obj->table} WHERE event = :event AND expire > :expire LIMIT 1");
        $stmt->execute([':event' => $obj->event, ':expire' => $obj->time]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$row){
            Session::pop($obj->cookie, $obj->for);
            return null;
        }
        
        $obj->user = json_decode($row['data'], true);

        if(($row['expire'] - $obj->time) < ($obj->ttl / 2)) self::regenerate();

        return $obj->user;
    }

    /**
     * Regenerate Auth Event ID
     */
    public function regenerate(): string
    {
        // Check Instance Loaded
        $obj = self::$instance ?? throw new Exception("Please Initiate Auth::config() First");

        self::destroy();
        return self::create($obj->user);
    }

    /**
     * Destroy Auth Event ID
     */
    public static function destroy(): void
    {
        // Check Instance Loaded
        $obj = self::$instance ?? throw new Exception("Please Initiate Auth::config() First");

        $stmt = $obj->pdo->prepare("DELETE FROM {$obj->table} WHERE event = :event");
        $stmt->execute([':event' => $obj->event]);
        Session::pop($obj->cookie, $obj->for);
    }
}