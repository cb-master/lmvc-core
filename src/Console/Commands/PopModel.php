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
namespace CBM\Core\Console\Commands;

use CBM\Core\Console\Command;

class PopModel Extends Command
{
    // App Model Path
    protected string $path = BASE_PATH . '/app/Model';

    /**
     * Run the command to create a new controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Model Name
        $name = $params[0] ?? null;

        if(!preg_match('/^[a-zA-Z_]+$/', $name)){
            // Invalid Name
            $this->error("Invalid Model Name: {$name}");
            return;
        }

        $file = "{$this->path}/{$name}.php";

        if(!is_file($file)){
            $this->error("Model Doesn't Exist: {$name}");
            return;
        }

        if(!unlink($file)){
            $this->error("Failed to Remove Model: {$name}");
            return;
        }
        
        
        
        $this->info("Model Created Successfully: {$name}");
    }
}