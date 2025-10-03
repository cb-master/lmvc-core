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
namespace CBM\Core\Http;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

class Request
{
    /**
     * @property ?object $instance
     */
    protected static ?object $instance;

    /**
     * @property array $get
     */
    protected static array $get;

    /**
     * @property array $post
     */
    protected static array $post;

    /**
     * @property array $files
     */
    protected static array $files;

    /**
     * @property array $json
     */
    protected static array $json;

    /**
     * @property string $rawBody
     */
    protected static string $rawBody;

    /**
     * @property string $method
     */
    protected static string $method;


    private function __construct()
    {
        self::$get = purify($_GET ?? []);
        self::$post = purify($_POST ?? []);
        self::$files = $_FILES ?? [];
        self::$rawBody = file_get_contents('php://input');
        self::$json = purify(self::json());
        self::$method = strtoupper(self::$post['_method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Get Request Instance
     * @return static
     */
    private static function getInstance(): static
    {
        self::$instance ??= new self();
        return self::$instance;
    }

    ##################################################################
    /*------------------------- PUBLIC API -------------------------*/
    ##################################################################

    /**
     * Get Method
     * @return string
     */
    public static function method(): string
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return self::$method;
    }

    /**
     * Get Header Key Values
     * @param string $key Key name of headers. Example: 'content-type'
     * @return ?string
     */
    public static function header(string $key): ?string
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$headerKey] ?? null;
    }

    /**
     * Request is POST
     * @return bool
     */
    public static function isPost(): bool
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return self::$method === 'POST';
    }

    /**
     * Request is GET
     * @return string
     */
    public static function isGet(): bool
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return self::$method === 'GET';
    }

    /**
     * Check Request is Ajax
     * @return bool
     */
    public static function isAjax(): bool
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return strtolower(self::header('X-Requested-With')) === 'xmlhttprequest';
    }

    /**
     * Get Value From Input Key
     * @param string $key Key Name of Request
     * @param mixed $default Default is null if not Key Exists
     * @return mixed
     */
    public static function input(string $key, mixed $default = null): mixed
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return self::$post[$key] ?? self::$get[$key] ?? self::$json[$key] ?? $default;
    }

    /**
     * Get All Request Key & Values
     * @return array
     */
    public static function all(): array
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return array_merge(self::$get, self::$post, self::$json);
    }

    // Get Selected Key Values
    public static function only(array $keys): array
    {
        // Define $instance if Not Defined Yet
        self::getInstance();

        return array_map(function($key){
            return self::input($key, null);
        },$keys);
    }

    /**
     * Check Request Key Exist
     * @param string $key Key Name of Request
     * @return bool
     */
    public static function has(string $key): bool
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return array_key_exists($key, self::$post) || array_key_exists($key, self::$get) || array_key_exists($key, self::$json);
    }

    /**
     * Get JSON Body
     * @return array
     */
    public static function json(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_starts_with(strtolower($contentType), 'application/json')) {
            $decoded = json_decode(self::$rawBody, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Get Selected Request File or All Request Files
     * @param ?string $key Key Name of Request File. Null Will Return All Request File Info
     * @return ?array
     */
    public static function file(?string $key = null): ?array
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return $key ? (self::$files[$key] ?? []) : self::$files;
    }

    /**
     * Get JSON String
     * @return string
     */
    public static function raw(): string
    {
        // Define $instance if Not Defined Yet
        self::getInstance();
        return self::$rawBody;
    }

    /**
     * Validate Request Keys
     * @param array $keys Request Keys. Example: ['name', 'password']
     * @return bool
     */
    public static function validRequestKeys(array $keys): bool
    {
        foreach($keys as $key){
            if(!self::input($key)){
                return false;
            }
        }
        return true;
    }

    /**
     * Check If Required Inputs Has Blank Value
     * @param $keys Required Argument. Example: ['username','email','password']
     */
    public function hasBlankInput(array $keys): bool
    {
        foreach($keys as $key){
            $value = self::input($key);
            if($value === null || $value === ''){
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $rules Required Argument. Example ['email'=>'required','age'=>'required|min:18|max:65']
     * @param array $customMessages Optional Argument. Example: ['email.required'=>'Email is Required!']
     * @return array
     */
    public static function validate(array $rules, array $customMessages = []): array
    {
        return Validator::make(self::all(), $rules, $customMessages);
    }
}