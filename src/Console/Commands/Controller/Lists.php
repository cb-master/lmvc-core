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

use CBM\Core\{Console\Command, Directory};


// Make Controller Class
class Lists Extends Command
{
    // App Controller Path
    protected string $path = BASE_PATH . '/app/Controller';

    /**
     * Run The Command to Create a New Controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Name
        $path = $params[0] ?? '';
        $trimed_path = trim($path, '/');

        // Check Controller Name is Valid
        if($trimed_path && !preg_match('/^[a-zA-Z\/]+$/', $trimed_path)){
            // Invalid Controller Name
            $this->error("Invalid Controller Path: '{$path}'");
            return;
        }

        $path_parts = explode('/', $trimed_path);
        if(!empty($path_parts) && $path_parts[0]){
            foreach ($path_parts as $part) {
                if (!$part) {
                    $this->error("Invalid Controller Path: '{$path}'");
                    return;
                }
                $this->path .= '/'.ucfirst($part);
            }
        }

        // Check Path Exist
        if(!Directory::exists($this->path)){
            $this->error("Controller Path Not Found: '{$path}'");
            return;
        }

        $paths = Directory::scanRecursive($this->path, true, 'php');
        $total = count($paths);
        echo <<<PHP
        -------------------------------------------------------------------
        LIST OF CONTROLLERS:
        -------------------------------------------------------------------\n
        PHP;
        foreach($paths as $path){
            if(is_file($path)) echo "\t>> ".'CBM\\App\\Controller\\'.str_replace([BASE_PATH . '/app/Controller/', '.php','/'], ['','','\\'], $path)."\n";
        }
        echo <<<TOTAL
        -------------------------------------------------------------------
        Total Controllers Found: {$total}\n\n
        TOTAL;

        return;
    }
}