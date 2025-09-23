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

use CBM\Core\{Console\Command, Directory};

// Rename Model Class
class Rename Extends Command
{
    // App Model Old Path
    protected string $old_path = APP_PATH . '/lf-app/Model';
    
    // App Model New Path
    protected string $new_path = APP_PATH . '/lf-app/Model';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z_\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Check Parameters
        if(count($params) < 2){
            $this->error("Usage: laika rename:model <old_name> <new_name>");
            return;
        }

        // Model Name
        $old = $params[0];
        $new = $params[1];

        // Check Old Model Name is Valid
        if(!preg_match($this->exp, $old)){
            // Invalid Model Name
            $this->error("Invalid Old Model Name: '{$old}'");
            return;
        }
        // Check New Model Name is Valid
        if(!preg_match($this->exp, $new)){
            // Invalid Model Name
            $this->error("Invalid New Model Name: '{$old}'");
            return;
        }

        // Get Old and New Parts
        $old_parts = $this->parts($old);
        $new_parts = $this->parts($new);

        // Get Directory Paths
        $this->old_path .= $old_parts['path'];
        $this->new_path .= $new_parts['path'];

         // Old and New Namespace
        $old_namespace = "namespace CBM\\App\\Model{$old_parts['namespace']}";
        $new_namespace = "namespace CBM\\App\\Model{$new_parts['namespace']}";

        $old_file = "{$this->old_path}/{$old_parts['name']}.php";
        $new_file = "{$this->new_path}/{$new_parts['name']}.php";

        // Check Old Model Path is Valid
        if(!is_file($old_file)){
            $this->error("Invalid Model Name or Path: '$old'");
            return;
        }

        // Check New Path Exist
        if(!Directory::exists($this->new_path)){
            Directory::make($this->new_path);
        }

        // Check New Model Path is Valid
        if(is_file($new_file)){
            $this->error("New Model Already Exist: '$old'");
            return;
        }

        // Get Contents
        $content = file_get_contents($old_file);
        if($content === false){
            $this->error("Failed to Read Model: '{$old}'");
            return;
        }

        // Replace Namespace if Not Same
        if($old_namespace != $new_namespace) $content = preg_replace('/'.preg_quote($old_namespace,'/').'/', $new_namespace, $content);

        // Replace Class Name
        $content = preg_replace("/class {$old_parts['name']}/i", "class {$new_parts['name']}", $content);

        // Create New Model File
        if(file_put_contents($new_file, $content) === false){
            $this->error("Failed to Create Model: {$new}");
            return;
        }

        // Remove Old Model File

        if(!unlink($old_file)){
            $this->error("Failed to Remove Model: '{$old_file}'");
            return;
        }
        
        $this->info("Model Renamed Successfully: '{$old}'->'{$new}'");
        return;
    }
}