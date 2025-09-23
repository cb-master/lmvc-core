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
namespace CBM\Core\Console\Commands\Template;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\{Console\Command, Directory};

// Make Template Class
class Lists Extends Command
{
    // App Template Path
    protected string $path = APP_PATH . '/lf-templates';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z0-9_\-\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Path
        $path = trim($params[0] ?? '', '/');

        // Check Template Name is Valid
        if($path && !preg_match($this->exp, $path)){
            // Invalid Template Name
            $this->error("Invalid Template Path: '{$path}'");
            return;
        }

        // Get Path if Given
        if($path) $this->path .= "/{$path}";

        // Check Path Exist
        if(!Directory::exists($this->path)){
            $this->error("Template Path Not Found: '{$this->path}'");
            return;
        }

        $paths = Directory::scanRecursive($this->path, true, 'php');
        $total = 0;

        echo <<<PHP
        -------------------------------------------------------------------
        LIST OF TEMPLATE NAMES:
        -------------------------------------------------------------------\n
        PHP;
        foreach($paths as $path){
            if(is_file($path)){
                $str = str_replace(["{$this->path}/", '.tpl.php'], [''], $path);
                if(strpos($str, 'tpl-') === 0){
                    $total++;
                    echo "\t>> {$str}\n";
                }
            }
        }
        echo <<<TOTAL
        -------------------------------------------------------------------
        Total Templates: {$total}\n\n
        TOTAL;

        return;
    }
}