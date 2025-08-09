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
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

class Request
{
    protected array $get;
    protected array $post;
    protected array $files;
    protected array $server;
    protected array $json;
    protected string $rawBody;
    protected string $method;
    protected object $instance;

    public function __construct()
    {
        $this->server = $_SERVER ?? [];
        $this->get = $this->purify($_GET ?? []);
        $this->post = $this->purify($_POST ?? []);
        $this->files = $_FILES ?? [];
        $this->rawBody = file_get_contents('php://input');
        $this->json = $this->purify($this->getJSON());
        $this->method = $this->detectMethod();
    }

    // Detect Request Method
    protected function detectMethod(): string
    {
        if (!empty($this->post['_method'])) {
            return strtoupper($this->post['_method']);
        }

        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    // Get JSON BODY
    protected function getJSON(): array
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (str_starts_with(strtolower($contentType), 'application/json')) {
            $decoded = json_decode($this->rawBody, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    // Get Method
    public function method(): string
    {
        return $this->method;
    }

    // Request is Post
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    // Request is Post
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    // Get Value From Input Key
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $this->json[$key] ?? $default;
    }

    // Get All Request Key & Values
    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->json);
    }

    // Get Selected Key Values
    public function only(array $keys): array
    {
        // $data = [];
        return array_map(function($key){
            return $this->input($key);
        },$keys);
    }

    // Check Request Key Exist
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->post) || array_key_exists($key, $this->get) || array_key_exists($key, $this->json);
    }

    // Get Selected Request File or All Request Files
    public function file(?string $key = null): ?array
    {
        return $key ? $this->files[$key] : $this->files;
    }

    // Gets Header Key Values
    public function header(string $key): ?string
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$headerKey] ?? null;
    }

    // Check Request is Ajax
    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With')) === 'xmlhttprequest';
    }

    // Get JSON String
    public function raw(): string
    {
        return $this->rawBody;
    }

    // Purify Input Values
    public function purify(array $data): array
    {
        return array_map(function($val){
            return is_array($val)
                ? $this->purify($val)
                : htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
        }, $data);
    }

    // Check Request Keys Are Exist
    public function validRequestKeys(array $keys): bool
    {
        foreach($keys as $key){
            if(!$this->input($key)){
                return false;
            }
        }
        return true;
    }

    // Check If Required Inputs Has Blank Value
    /**
     * @param $keys Required Argument. Example: ['username','email','password']
     */
    public function hasBlankInput(array $keys): bool
    {
        foreach($keys as $key){
            $value = $this->input($key);
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
    public function validate(array $rules, array $customMessages = []): array
    {
        return Validator::make($this->all(), $rules, $customMessages);
    }
}