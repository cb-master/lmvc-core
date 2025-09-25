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

class Csrf
{
    // Session Name
    protected string $key;

    public function __construct(?string $key = null)
    {
        $this->key = $key ?: 'csrf';
        $this->generate();
    }

    // Create CSRF Token
    private function generate(): void
    {
        $refresh_time = (int) (Config::get('app','refresh_time') ?: 300);
        if((time() - (int) (Session::get('csrf_refresh_time')) > $refresh_time)){
            Session::set('csrf_refresh_time', time());
            Session::pop($this->key);
        }
        if(!Session::get($this->key)){    
            // Set Session CSRF
            Session::set($this->key, bin2hex(random_bytes(64)));
        }
    }

    // Get Form Token
    public function get(): string
    {
        return (string) Session::get($this->key);
    }

    // Reset Form Token
    public function reset(): void
    {
        Session::set($this->key, bin2hex(random_bytes(64)));
    }

    // Validate Form Token
    public function validate(): bool
    {
        $request = new Request();
        $existing_token = $this->get();
        $this->reset();
        $requestToken = (string) $request->input($this->key);
        return hash_equals($requestToken, $existing_token);
    }
}