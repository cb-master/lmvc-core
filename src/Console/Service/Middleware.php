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
namespace CBM\Core\Console\Service;

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\Console\Message;

class Middleware
{
    // Middleware Directory
    private string $dir = BASE_PATH . '/app/Middleware';

    // Create Middleware
    public function create($args)
    {
        // Check Middleware Name is Alphabetic or Not Blank or No Special Character
        if(!isset($args[0]) || !$args[0] || !preg_match('/^[a-zA-Z]+$/', $args[0])) return Message::invalidParameter($args[0] ?? '');

        // Get File Path
        $file_path = "{$this->dir}/{$args[0]}.php";

        // Check File Already Exist
        if(file_exists($file_path)){
            return Message::exist("{$args[0]}");
        }

        // Make File
        $content = file_get_contents(__DIR__.'/../Samples/Middleware.sample');
        $content = str_replace('{{NAME}}', $args[0], $content);
        if(file_put_contents($file_path, $content) === false){
            return Message::error();
        }
        return Message::created($args[0]);
    }
}