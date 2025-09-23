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

use CBM\Core\{Console\Command, Directory};

// Make Controller Class
class Lists Extends Command
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
        // Path
        $path = trim($params[0] ?? '', '/');

        // Check Controller Name is Valid
        if($path && !preg_match($this->exp, $path)){
            // Invalid Controller Name
            $this->error("Invalid Controller Path: '{$path}'");
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
            $this->error("Controller Path Not Found: '{$this->path}'");
            return;
        }

        $paths = Directory::scanRecursive($this->path, true, 'php');
        $total = 0;

        echo <<<PHP
        -------------------------------------------------------------------
        LIST OF CONTROLLER CLASSES:
        -------------------------------------------------------------------\n
        PHP;
        foreach($paths as $path){
            if(is_file($path)){
                $total++;
                echo "\t>> ".'CBM\\App\\Controller\\'.str_replace(["{$this->path}/", '.php','/'], ['','','\\'], $path)."\n";
            }
        }
        echo <<<TOTAL
        -------------------------------------------------------------------
        Total Controllers: {$total}\n\n
        TOTAL;

        return;
    }
}