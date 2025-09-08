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

defined('BASE_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Session\SessionManager;
use CBM\Session\Session;
use Exception;
use Throwable;
use PDO;

class Auth
{
    // Instance Object
    private static ?object $instance = null;

    // PDO Object
    private PDO $pdo;

    // DB Table Name
    private string $table = 'authentication';

    // Cookie Name
    private string $cookie = '__AUTH';

    // Cookie Expire After TTL
    private int $ttl = 1800; // 1800 Seconds or 30 Minutes

    // User Data
    private ?array $user = null;

    // Event ID
    private ?string $event;

    // Initiate Session
    /**
     * @param PDO $pdo. PDO Instance
     * @param ?string $table. Table name. Default is null
     */
    private function __construct(PDO $pdo, ?string $table = null)
    {
        SessionManager::init($pdo);
        $this->pdo = $pdo;
        $this->table = $table ?: $this->table;
        // $this->event = Cookie::get($this->cookie);
        $this->event = Session::get($this->cookie, 'auth');
    }

    // Config Instance
    /**
     * @param PDO $pdo. PDO Instance
     * @param ?string $table. Table name. Default is null
     * @return self
     */
    public static function config(PDO $pdo, ?string $table = null): self
    {
        self::$instance ??= new self($pdo, $table);
        // Create Table if Not Exist
        try{
            $makeSql = "CREATE TABLE IF NOT EXISTS " . self::$instance->table . " (event VARCHAR(64) NOT NULL,data TEXT NOT NULL,expire INT NOT NULL,created INT NOT NULL, INDEX(event));";
            $stmt = self::$instance->pdo->prepare($makeSql);
            $stmt->execute();
        }catch(Throwable $th){
            if(Config::get('app', 'debug')){
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
        $event = bin2hex(random_bytes(32));
        // Set Expire Time
        $time = time();
        $expire = $time + $obj->ttl;

        // Make SQL
        $sql = "INSERT INTO {$obj->table} (event, data, expire, created) VALUES (:event, :data, :expire, :created)";
        $stmt = self::$instance->pdo->prepare($sql);
        $stmt->execute([
            ':event'    =>  $event,
            ':data'     =>  json_encode($user),
            ':expire'   =>  $expire,
            ':created'  =>  $time,
        ]);

        // Set cookie
        // Cookie::set(self::$instance->cookie, $event, $obj->ttl, '/'.Uri::directory());
        Session::set(self::$instance->cookie, $event, 'auth');

        return $event;
    }

    // Validate User Exist
    /**
     * Check User is Authenticated and Not Expired
     */
    public static function validate(): ?array
    {
        // Check Instance Loaded
        $obj = self::$instance ?? throw new Exception("Please Initiate Auth::config() First");

        $realtime = time();

        // Get DB Data
        $stmt = $obj->pdo->prepare("SELECT data, expire FROM {$obj->table} WHERE event = :event AND expire > :expire LIMIT 1");
        $stmt->execute([':event' => $obj->event, ':expire' => $realtime]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row){
            return null;
        }

        $obj->user = json_decode($row['data'], true);
        if(($row['expire'] - $realtime) < ($obj->ttl / 2)) self::regenerate();

        return $obj->user;
    }

    /**
     * Regenerate Auth Event ID
     */
    public static function regenerate(): string
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
        // Cookie::pop($obj->cookie, '/'.Uri::directory());
        Session::pop($obj->cookie, 'auth');
    }
}