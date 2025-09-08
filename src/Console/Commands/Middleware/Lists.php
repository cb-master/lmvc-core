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

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z_\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Path
        $path = trim($params[0] ?? '', '/');

        // Check Middleware Name is Valid
        if($path && !preg_match($this->exp, $path)){
            // Invalid Middleware Name
            $this->error("Invalid Middleware Path: '{$path}'");
            return;
        }

        // Get Path if Given
        if($path){
            // Get Parts
            $exploded = explode('/',$path);
            $parts = array_map('ucfirst', $exploded);
            $this->path .= '/' . implode('/', $parts);
        }

        // Check Path Exist
        if(!Directory::exists($this->path)){
            $this->error("Middleware Path Not Found: '{$this->path}'");
            return;
        }

        $paths = Directory::scanRecursive($this->path, true, 'php');
        $total = 0;

        echo <<<PHP
        -------------------------------------------------------------------
        LIST OF MIDDLEWARE CLASSES:
        -------------------------------------------------------------------\n
        PHP;
        foreach($paths as $path){
            if(is_file($path)){
                $total++;
                echo "\t>> ".'CBM\\App\\Middleware\\'.str_replace([BASE_PATH . '/app/Middleware/', '.php','/'], ['','','\\'], $path)."\n";
            }
        }
        echo <<<TOTAL
        -------------------------------------------------------------------
        Total Middlewares: {$total}\n\n
        TOTAL;

        return;
    }
}