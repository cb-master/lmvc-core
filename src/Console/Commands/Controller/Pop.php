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

use CBM\Core\Console\Command;

// Remove Controller Class
class Pop Extends Command
{
    // App Controller Path
    protected string $path = BASE_PATH . '/app/Controller';

    /**
     * Run The Command to Remove a Controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Model Name
        $name = $params[0] ?? '';
        $controller = trim($name, '/');

        // Check Controller Name is Valid
        if(!preg_match('/^[a-zA-Z\/]+$/', $controller)){
            // Invalid Controller Name
            $this->error("Invalid Controller Name: '{$controller}'");
            return;
        }

        // Make Controller Directory if Not Exist
        $parts   =   explode('/', $controller);

        // Get Controller Name
        $controller_name = array_pop($parts);
        // Set Namespace
        $namespace = '';

        // Check Sub Namespace Exist
        if(!empty($parts)){
            // Namespace
            foreach($parts as $part){
                $namespace .= '\\'.ucfirst($part);
            }
        }
        // Set Path
        $this->path .= str_replace('\\', '/', $namespace);

        $file = "{$this->path}/{$controller_name}.php";

         // Check Controller Path is Valid

        if(!is_file($file)){
            $this->error("Invalid Controller Name or Path: '{$name}'");
            return;
        }

        if(!unlink($file)){
            $this->error("Failed to Remove Controller: '{$name}'");
            return;
        }
        
        
        
        $this->info("Controller Removed Successfully: '{$name}'");
    }
}