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

use CBM\Core\Http\Request;
use CBM\Session\Session;

class CSRF
{
    // CSRF Token Lifetime
    protected int $lifetime;

    // CSRF For
    protected string $for;

    // Session Name
    protected string $key;

    /**
     * @param array $config Default is []. Accepted Keys: ['key', 'for']. Example: ['key'=>'csrf', 'for'=>'admin']
     */
    public function __construct(array $config = [])
    {
        $this->key = $config['key'] ?? 'csrf';
        $this->for = strtoupper($config['for'] ?? 'APP');
        $this->lifetime = (int) option('csrf.lifetime', 300); // Default Lifetime is 300
        $this->generate();
    }

    /**
     * Create CSRF Token
     * @return string
     */
    private function generate(): string
    {
        $csrf = Session::get($this->key, $this->for);
        // Generate CSRF Token if Not Exists
        if(
            !isset($csrf['created'], $csrf['token']) ||
            !$csrf['created'] ||
            !$csrf['token'] ||
            (time() - $csrf['created'] > $this->lifetime)
        ){
            return $this->reset();
        }
        // if(!isset($csrf['created'], $csrf['token'])){
        //     $arr = [
        //         'created'   =>  time(),
        //         'token'     =>  bin2hex(random_bytes(64))
        //     ];
        //     Session::set($this->key, $arr, $this->for);
        //     return $arr['token'];
        // }

        // // Regenerate if Expired
        // if((time() - $csrf['created'] > $this->lifetime)){
        //     return $this->reset();
        // }
        return $csrf['token'];
    }

    /**
     * Get CSRF Token
     * @return string
     */
    public function get(): string
    {
        $csrf = Session::get($this->key, $this->for);
        if (!isset($csrf['token']) || !$csrf['token']) return $this->reset();
        return $csrf['token'];
    }

    /**
     * Reset Form Token
     * @return string
     */
    public function reset(): string
    {
        $arr = [
                'created'   =>  time(),
                'token'     =>  bin2hex(random_bytes(64))
        ];
        Session::set($this->key, $arr, $this->for);
        return $arr['token'];
    }

    /**
     * @return string CSRF Html Field
     */
    public function field(): string
    {
        return "<input type=\"hidden\" name=\"{$this->key}\" value=\"{$this->get()}\">\n";
    }

    /**
     * Validate Form Token
     * @return bool
     */
    public function validate(): bool
    {
        // If CSRF Request Key Missing or Blank, Return false
        $request_token = (string) Request::input($this->key);
        if(!$request_token) return false;

        $existing_token = $this->get();
        $this->reset();
        return hash_equals($request_token, $existing_token);
    }
}