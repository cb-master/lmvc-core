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

use CBM\Core\{Console\Command, Directory};


// Make Middleware Class
class Lists Extends Command
{
    // App Middleware Path
    protected string $path = BASE_PATH . '/app/Middleware';

    /**
     * Run The Command to Create a New Middleware.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Name
        $input = $params[0] ?? '';
        $trimed_path = trim($input, '/');

        // Check Middleware Name is Valid
        if($trimed_path && !preg_match('/^[a-zA-Z_\/]+$/', $trimed_path)){
            // Invalid Middleware Name
            $this->error("Invalid Middleware Path: '{$input}'");
            return;
        }

        $path_parts = explode('/', $trimed_path);
        
        if(!empty($path_parts) && $path_parts[0]){
            foreach ($path_parts as $part) {
                if (!$part) {
                    $this->error("Invalid Middleware Path: '{$input}'");
                    return;
                }
                $this->path .= '/'.ucfirst($part);
            }
        }

        // Check Path Exist
        if(!Directory::exists($this->path)){
            $this->error("Middleware Path Not Found: '{$this->path}'");
            return;
        }

        $paths = Directory::scanRecursive($this->path, true, 'php');
        $total = count($paths);
        echo <<<PHP
        -------------------------------------------------------------------
        LIST OF MIDDLEWARES:
        -------------------------------------------------------------------\n
        PHP;
        foreach($paths as $path){
            if(is_file($path)) echo "\t>> ".'CBM\\App\\Middleware\\'.str_replace([BASE_PATH . '/app/Middleware/', '.php','/'], ['','','\\'], $path)."\n";
        }
        echo <<<TOTAL
        -------------------------------------------------------------------
        Total Middlewares Found: {$total}\n\n
        TOTAL;

        return;
    }
}