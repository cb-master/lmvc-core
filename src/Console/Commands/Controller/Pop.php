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
namespace CBM\Core\Console\Commands\Controller;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\Console\Command;

// Remove Controller Class
class Pop Extends Command
{
    // App Controller Path
    protected string $path = APP_PATH . '/lf-app/Controller';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z_\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Check Parameters
        if(count($params) < 1){
            $this->error("USAGE: laika pop:controller <name>");
            return;
        }

        // Check Controller Name is Valid
        if(!preg_match($this->exp, $params[0])){
            // Invalid Controller Name
            $this->error("Invalid Controller Name: '{$params[0]}'");
            return;
        }

        // Get Controller Parts
        $parts = $this->parts($params[0]);

        // Set Path
        $this->path .= $parts['path'];

        $file = "{$this->path}/{$parts['name']}.php";

         // Check Controller Path is Valid
        if(!is_file($file)){
            $this->error("Invalid Controller or Path: '{$params[0]}'");
            return;
        }

        if(!unlink($file)){
            $this->error("Failed to Remove Controller: '{$file}'");
            return;
        }
        
        $this->info("Controller Removed Successfully: '{$params[0]}'");
        return;
    }
}