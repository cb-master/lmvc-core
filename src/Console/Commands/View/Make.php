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
namespace CBM\Core\Console\Commands\View;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\{Console\Command,Directory};

class Make Extends Command
{
    // App View Path
    protected string $path = APP_PATH . '/lf-templates';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z0-9_\-\/]+$/';

    /**
     * Run the command to create a new controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Check Parameters
        if(count($params) < 1){
            $this->error("USAGE: laika make:view <name>");
            return;
        }

        if(!preg_match($this->exp, $params[0])){
            // Invalid Name
            $this->error("Invalid View Name: '{$params[0]}'");
            return;
        }
        $parts = $this->parts($params[0], false);

        $this->path .= $parts['path'];
        
        // Make Directory if Not Exist
        if(!Directory::exists($this->path)){
            Directory::make($this->path);
        }

        $file = "{$this->path}/{$parts['name']}.tpl.php";

        if(is_file($file)){
            $this->error("View Already Exist: {$file}");
            return;
        }

        // Get Sample Content
        $content = file_get_contents(__DIR__ . '/../../Samples/View.sample');

        // Replace Placeholders
        // $content = str_replace('{{NAME}}',$parts['name'], $content);

        if(file_put_contents($file, $content) === false){
            $this->error("Failed to Create View: {$file}");
            return;
        }

        $this->info("View Created Successfully: {$params[0]}");
        return;
    }
}