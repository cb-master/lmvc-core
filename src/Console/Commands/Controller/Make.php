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
class Make Extends Command
{
    // App Controller Path
    protected string $path = APP_PATH . '/lf-app/Controller';

    // App View Path
    protected string $view_path = APP_PATH . '/lf-templates';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z_\/][a-zA-Z0-9_\/]+$/';

    // Accepted Regular Expresion
    private string $view_exp = '/^[a-zA-Z0-9_\-\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Check Parameters
        if(count($params) < 1){
            $this->error("USAGE: laika make:controller <name> <view::optional>");
            return;
        }

        // View Name
        $view = $params[1] ?? 'default-view';
        
        // Get Controller & View Parts
        $controller_parts   =   $this->parts($params[0]);
        $view_parts         =   $this->parts($view, false);

        // Check Controller Name is Valid
        if(!preg_match($this->exp, $params[0])){
            // Invalid Controller Name
            $this->error("Invalid Controller Name: '{$params[0]}'");
            return;
        }

        // Check View Name is Valid
        if(!preg_match($this->view_exp, $view)){
            $this->error("Invalid View Name: '{$view}'");
            return;
        }

        // Set Controller & View Path
        $this->path     .=  $controller_parts['path'];
        $this->view_path.=  $view_parts['path'];

        // Create Controller Directory if Not Exist
        if(!Directory::exists($this->path)){
            try {
                Directory::make($this->path);
            } catch (\Throwable $th) {
                $this->error($th->getMessage());
                return;
            }
        }

        // Create View Directory if Not Exist
        if(!Directory::exists($this->view_path)){
            try {
                Directory::make($this->view_path);
            } catch (\Throwable $th) {
                $this->error($th->getMessage());
                return;
            }
        }

        $controller_file    =   "{$this->path}/{$controller_parts['name']}.php";
        $view_file          =   "{$this->view_path}/{$view_parts['name']}.tpl.php";

        // Check Controller Already Exist
        if(is_file($controller_file)){
            $this->error("Controller Already Exist: '{$params[0]}'");
            return;
        }

        // Get Sample Controller Content
        $content = file_get_contents(__DIR__ . '/../../Samples/Controller.sample');

        // Replace Placeholders
        $content = str_replace([
            '{{NAMESPACE}}',
            '{{NAME}}',
            '{{VIEW}}'
        ],[
            $controller_parts['namespace'],
            $controller_parts['name'],
            trim($view, '/')
        ], $content);

        if(file_put_contents($controller_file, $content) === false){
            $this->error("Failed to Create Controller: {$controller_file}");
            return;
        }

        if(!is_file($view_file)){
            // Get Sample View Content
            $content = file_get_contents(__DIR__ . '/../../Samples/View.sample');
            // Replace Placeholders
            $content = str_replace('{{NAME}}', $view, $content);

            if(file_put_contents($view_file, $content) === false){
                $this->error("Failed to Create View: {$view}");
                return;
            }
        }
        
        $this->info("Controller Created Successfully: '{$params[0]}' With View: '{$view}'");
        return;
    }
}