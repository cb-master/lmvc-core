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

use CBM\Core\{Console\Command,Directory};

class Make Extends Command
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
            $this->error("USAGE: laika make:model <name> <table::optional> <id::optional>");
            return;
        }

        // Table Name
        $table = $params[1] ?? 'table_name';

        // Primary Key Name
        $id = $params[2] ?? 'id';

        if(!preg_match($this->exp, $params[0])){
            // Invalid Name
            $this->error("Invalid Model Name: '{$params[0]}'");
            return;
        }
        $parts = $this->parts($params[0]);

        $this->path .= $parts['path'];
        
        // Make Directory if Not Exist
        if(!Directory::exists($this->path)){
            Directory::make($this->path);
        }

        $file = "{$this->path}/{$parts['name']}.php";

        if(is_file($file)){
            $this->error("Model Already Exist: {$file}");
            return;
        }

        // Get Sample Content
        $content = file_get_contents(__DIR__ . '/../../Samples/Model.sample');

        // Replace Placeholders
        $content = str_replace([
            '{{NAMESPACE}}',
            '{{NAME}}',
            '{{TABLE_NAME}}',
            '{{PRIMARY_KEY}}'
        ],[
            $parts['namespace'],
            $parts['name'],
            $table,
            $id
        ], $content);

        if(file_put_contents($file, $content) === false){
            $this->error("Failed to Create Model: {$file}");
            return;
        }

        $this->info("Model Created Successfully: {$params[0]}");
        return;
    }
}