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

use CBM\Core\{Console\Command, Directory};

// Rename Controller Class
class RenameController Extends Command
{
    // App Controller Old Path
    protected string $old_path = BASE_PATH . '/app/Controller';
    
    // App Controller New Path
    protected string $new_path = BASE_PATH . '/app/Controller';

    /**
     * Run The Command to Remove a Controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Model Name
        $old = $params[0] ?? null;
        $new = $params[1] ?? null;

        // Check Old Controller Name is Valid
        if(!$old || !preg_match('/^[a-zA-Z\/]+$/', $old)){
            // Invalid Controller Name
            $this->error("Invalid Old Controller Name: '{$old}'");
            return;
        }
        // Check New Controller Name is Valid
        if(!$new || !preg_match('/^[a-zA-Z\/]+$/', $new)){
            // Invalid Controller Name
            $this->error("Invalid New Controller Name: '{$old}'");
            return;
        }

        // Make Controller Directory if Not Exist
        $old_controller_parts   =   explode('/', trim($old));
        $new_controller_parts   =   explode('/', trim($new));

        // Count Parts
        $total_old_controller_parts = count($old_controller_parts);
        $total_new_controller_parts = count($new_controller_parts);

        // Check Old Sub Namespace Exist
        if($total_old_controller_parts > 1){
            // Set Controller Name
            $old_controller = array_pop($old_controller_parts);
            // Check Controller Name is Valid
            if(!$old_controller){
                // Invalid Controller Name
                $this->error("Invalid Old Controller Name: '{$old}'");
                return;
            }
            
            // Sub Namespace
            $old_sub_namespace = '';
            foreach($old_controller_parts as $part){
                if(!$part){
                    // Invalid Controller Name
                    $this->error("Invalid Old Controller Name: '{$old}'");
                    return;
                }
                $old_sub_namespace .= ucfirst($part).'\\';
            }
            $old_sub_namespace = trim($old_sub_namespace, '\\');
            // Set Path
            $this->old_path .= "/{$old_sub_namespace}";
            $this->old_path = str_replace('\\', '/', $this->old_path);
        }else{
            // Sub Namespace
            $old_sub_namespace = null;
            // Set Controller Name
            $old_controller = $old_controller_parts[0];
        }

        // Check New Sub Namespace Exist
        if($total_new_controller_parts > 1){
            // Set Controller Name
            $new_controller = array_pop($new_controller_parts);
            // Check Controller Name is Valid
            if(!$new_controller){
                // Invalid Controller Name
                $this->error("Invalid Old Controller Name: '{$new}'");
                return;
            }
            // Sub Namespace
            $new_sub_namespace = '';
            foreach($new_controller_parts as $part){
                if(!$part){
                    // Invalid Controller Name
                    $this->error("Invalid New Controller Name: '{$new}'");
                    return;
                }
                $new_sub_namespace .= ucfirst($part).'\\';
            }
            $new_sub_namespace = trim($new_sub_namespace, '\\');

            // Set Path
            $this->new_path .= "/{$new_sub_namespace}";
            $this->new_path = str_replace('\\', '/', $this->new_path);
        }else{
            // Sub Namespace
            $new_sub_namespace = null;
            // Set Controller Name
            $new_controller = $new_controller_parts[0];
        }

        $default_namespace = "namespace CBM\\App\\Controller";
         // Old and New Namespace
        $old_namespace = $old_sub_namespace ? "namespace CBM\\App\\Controller\\{$old_sub_namespace}" : $default_namespace;
        $new_namespace = $new_sub_namespace ? "namespace CBM\\App\\Controller\\{$new_sub_namespace}" : $default_namespace;

        $old_file = "{$this->old_path}/{$old_controller}.php";
        $new_file = "{$this->new_path}/{$new_controller}.php";

         // Check Controller Path is Valid

        if(!is_file($old_file)){
            $this->error("Invalid Controller Name or Path: '$old'");
            return;
        }
        // Get Contents
        $content = file_get_contents($old_file);
        if($content === false){
            $this->error("Failed to Read Controller: '{$old}'");
            return;
        }
        // Replace Namespace if Not Same
        if($old_namespace != $new_namespace) $content = preg_replace('/'.preg_quote($old_namespace,'/').'/', $new_namespace, $content);
        // Replace Class Name
        $content = preg_replace("/class {$old_controller}/i", "class {$new_controller}", $content);

        // Check New Path Exist
        if(!is_dir($this->new_path)){
            Directory::make($this->new_path);
        }

        // Create New Controller File
        if(file_put_contents($new_file, $content) === false){
            $this->error("Failed to Create Controller: {$new}");
            return;
        }

        // Remove Old Controller File

        if(!unlink($old_file)){
            $this->error("Failed to Remove Controller: '{$old}'");
            return;
        }        
        
        
        $this->info("Controller Renamed Successfully: '{$old}'->'{$new}'");
    }
}