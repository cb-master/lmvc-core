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
namespace CBM\Core\Console\Commands\Middleware;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\Console\Command;

// Remove Middleware Class
class Pop Extends Command
{
    // App Middleware Path
    protected string $path = APP_PATH . '/lf-app/Middleware';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Check Parameters
        if(count($params) < 1){
            $this->error("USAGE: laika pop:middleware <name>");
            return;
        }

        // Check Middleware Name is Valid
        if(!preg_match('/^[a-zA-Z_\/]+$/', $params[0])){
            // Invalid Middleware Name
            $this->error("Invalid Middleware Name: '{$params[0]}'");
            return;
        }

        // Get Middleware Parts
        $parts = $this->parts($params[0]);

        // Set Path
        $this->path .= $parts['path'];

        $file = "{$this->path}/{$parts['name']}.php";

         // Check Middleware Path is Valid
        if(!is_file($file)){
            $this->error("Invalid Middleware or Path: '{$params[0]}'");
            return;
        }

        if(!unlink($file)){
            $this->error("Failed to Remove Middleware: '{$file}'");
            return;
        }
        
        $this->info("Middleware Removed Successfully: '{$params[0]}'");
    }
}