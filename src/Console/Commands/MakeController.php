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


// Make Controller Class
class MakeController Extends Command
{
    // App Controller Path
    protected string $path = BASE_PATH . '/app/Controller';

    // App View Path
    protected string $view_path = BASE_PATH . '/app/Views';

    /**
     * Run The Command to Create a New Controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Name
        $controller = $params[0] ?? null;

        // View Name
        $view = $params[1] ?? 'default-view';
        $view = trim($view, '/');

        // Check Controller Name is Valid
        if(!preg_match('/^[a-zA-Z\/]+$/', $controller)){
            // Invalid Controller Name
            $this->error("Invalid Controller Name: '{$controller}'");
            return;
        }

        // Check View Name is Valid
        if(!preg_match('/^[a-zA-Z0-9_\-\/]+$/', $view)){
            // Invalid View Name
            $this->error("Invalid View Name: '{$view}'");
            return;
        }

        // Make Controller/View Directory if Not Exist
        $controller_parts   =   explode('/', trim($controller));
        $view_parts         =   explode('/', trim($view));

        // Check Sub Namespace Exist
        $sub_namespace = '';
        $total_controller_paths = count($controller_parts);
        if($total_controller_paths > 1){
            $controller = array_pop($controller_parts);
            foreach ($controller_parts as $part) {
                if (!$part) {
                    $this->error("Invalid Controller Name: '{$params[0]}'");
                    return;
                }
                $sub_namespace .= "\\".ucfirst($part);
            }
            // Check Controller Name is Valid
            if(!$controller){
                // Invalid Controller Name
                $this->error("Invalid Controller Name: '{$params[0]}'");
                return;
            }
        }else{
            $controller = $controller_parts[0];
        }
        // Set Path
        $this->path .= str_replace('\\', '/', $sub_namespace);

        // Create Controller Directory if Not Exist
        if(!Directory::exists($this->path)){
            try {
                Directory::make($this->path);
            } catch (\Throwable $th) {
                $this->error($th->getMessage());
                return;
            }
        }

        // Check View Sub Directory Exist
        $sub_view_path = '';
        $view_file_name = $view;
        if(count($view_parts) > 1){
            // Get Sub View Path
            $view_file_name = array_pop($view_parts);

            foreach($view_parts as $part){
                if (!$part) {
                    $this->error("Invalid View Name: '{$params[1]}'");
                    return;
                }
                $sub_view_path .= "/{$part}";
            }

            // $controller_view_name = "{$sub_view_path}/{$view}";
        }
        // Get View Path
        $this->view_path .= $sub_view_path;
        
        // Create View Directory if Not Exist
        if(!Directory::exists($this->view_path)){
            try {
                Directory::make($this->view_path);
            } catch (\Throwable $th) {
                $this->error($th->getMessage());
                return;
            }
        }

        $controller_file    =   "{$this->path}/{$controller}.php";
        $view_file          =   "{$this->view_path}/{$view_file_name}.tpl.php";

        // Check Controller Already Exist
        if(is_file($controller_file)){
            $this->error("Controller Already Exist: '{$params[0]}'");
            return;
        }

        // Get Sample Controller Content
        $content = file_get_contents(__DIR__ . '/../Samples/Controller.sample');

        // $sub_namespace = $sub_namespace ?: '';

        // Replace Placeholders
        $content = str_replace(['{{SUB_CONTROLLER}}', '{{NAME}}', '{{VIEW_NAME}}'], [$sub_namespace, $controller, $view_file_name], $content);

        if(file_put_contents($controller_file, $content) === false){
            $this->error("Failed to Create Controller: {$controller}");
            return;
        }

        if(!is_file($view_file)){
            // Get Sample View Content
            $view_content = file_get_contents(__DIR__ . '/../Samples/View.sample');
            // Replace Placeholders
            $view_content = str_replace('{{NAME}}', $view, $view_content);

            if(file_put_contents($view_file, $view_content) === false){
                $this->error("Failed to Create View: {$view}");
                return;
            }
        }        
        
        
        $this->info("Controller Created Successfully: '{$params[0]}' With View: '{$view}'");
    }
}