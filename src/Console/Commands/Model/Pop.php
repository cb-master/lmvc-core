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
namespace CBM\Core\Console\Commands\Model;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\Console\Command;

class Pop Extends Command
{
    // App Model Path
    protected string $path = APP_PATH . '/lf-app/Model';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z_\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Check Parameters
        if(count($params) < 1){
            $this->error("USAGE: laika pop:model <name>");
            return;
        }

        if(!preg_match($this->exp, $params[0])){
            // Invalid Name
            $this->error("Invalid Model Name: {$params[0]}");
            return;
        }

        // Get Parts
        $parts = $this->parts($params[0]);

        // Get Path
        $this->path .= $parts['path'];


        $file = "{$this->path}/{$parts['name']}.php";

        if(!is_file($file)){
            $this->error("Model Doesn't Exist: {$file}");
            return;
        }

        if(!unlink($file)){
            $this->error("Failed to Remove Model: {$file}");
            return;
        }

        $this->info("Model Created Successfully: {$params[0]}");
        return;
    }
}