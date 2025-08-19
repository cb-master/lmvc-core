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
namespace CBM\Core\Console\Service;

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\Console\Message;
use CBM\Core\Directory;

class Controller
{
    // Controller Directory
    private string $dir = BASE_PATH . '/app/Controller';

    // Args
    private array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    // Create Controller
    public function create(): array
    {
        // Check Controller Name is Alphabetic or Not Blank or No Special Character
        $name = $this->args[0] ?? '';
        $view_file = $this->args[1] ?? 'view_file';
        $view_file = trim($view_file, '/');

        if(!preg_match('/^[a-zA-Z_-]+$/', $name)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Invalid Name: '{$name}'!", "red")
            ];
        }

        if(!preg_match('/^[a-zA-Z\/_-]+$/', $view_file)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Invalid View File Name: '{$view_file}'!", "red")
            ];
        }

        // Get File Path
        $file_path = "{$this->dir}/{$name}.php";

        // Check File Already Exist
        if(is_file($file_path)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Controller: '{$name}' Already Exist!", "red")
            ];
        }

        // Make File
        $content = file_get_contents(__DIR__.'/../Samples/Controller.sample');
        $content = str_replace('{{NAME}}', $name, $content);
        $content = str_replace('{{VIEW_NAME}}', $view_file, $content);

        // Create View File
        $view_path = BASE_PATH . "/app/Views/{$view_file}.tpl.php";
        if(!is_file($view_path)){
            $obj = new View([$view_file]);
            $result = $obj->create();
            if(!$result['status']){
                return $result;
            }
        }

        if(file_put_contents($file_path, $content) === false){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Something Went Wrong! Unable to Create '{$file_path}'", 'red')
            ];
        }
        return [
            'status'    =>  true,
            'message'   =>  Message::show("Success", "Controller: '{$name}' Created Successfully.")
        ];
    }

    // Create Template Controller
    public function template(): array
    {
        // Check Controller Name is Alphabetic or Not Blank or No Special Character
        $name = $this->args[0] ?? '';
        $view_file = $this->args[1] ?? 'view_file';
        $view_file = trim($view_file, '/');

        if(!preg_match('/^[a-zA-Z_-]+$/', $name)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Invalid Name: '{$name}'!", "red")
            ];
        }

        if(!preg_match('/^[a-zA-Z\/_-]+$/', $view_file)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Invalid View File Name: '{$view_file}'!", "red")
            ];
        }

        // Get File Path
        $file_path = "{$this->dir}/{$name}.php";

        // Check File Already Exist
        if(is_file($file_path)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Controller: '{$name}' Already Exist!", "red")
            ];
        }

        // Make File
        $content = file_get_contents(__DIR__.'/../Samples/TemplateController.sample');
        $content = str_replace('{{NAME}}', $name, $content);
        $content = str_replace('{{VIEW_NAME}}', $view_file, $content);

        // Create View File
        $view_path = BASE_PATH . "/app/Views/{$view_file}.tpl.php";
        if(!is_file($view_path)){
            $obj = new View([$view_file]);
            $result = $obj->template();
            if(!$result['status']){
                return $result;
            }
        }

        if(file_put_contents($file_path, $content) === false){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Something Went Wrong! Unable to Create '{$file_path}'", 'red')
            ];
        }
        return [
            'status'    =>  true,
            'message'   =>  Message::show("Success", "Controller: '{$name}' Created Successfully.")
        ];
    }

    // Rename Controller
    public function rename(): string
    {
        // Check Controller Name is Alphabetic or Not Blank or No Special Character
        $old_name = $this->args[0] ?? '';
        $new_name = $this->args[1] ?? '';

        if(!preg_match('/^[a-zA-Z_]+$/', $old_name)){
            return Message::show("Error", "Controller old name: '{$old_name}' is invalid!", "red");
        }

        if(!preg_match('/^[a-zA-Z_]+$/', $new_name)){
            return Message::show("Error", "Controller new name: '{$new_name}' is invalid!", "red");
        }

        // Get File Path
        $old_file_path = "{$this->dir}/{$old_name}.php";
        $new_file_path = "{$this->dir}/{$new_name}.php";

        // Check File Exist
        if(!file_exists($old_file_path)){
            return Message::show("Error", "Old Controller: '{$old_name}' Doesn't Exist!", "red");
        }

        // Check New Named File Does Not Exist
        if(file_exists($new_file_path)){
            return Message::show("Error", "New Controller: '{$new_name}' Already Exist!", "red");
        }

        $content = file_get_contents($old_file_path);
        $content = str_replace($old_name, $new_name, $content);

        if(file_put_contents($new_file_path, $content) === false){
            return Message::show("Error", "Something Went Wrong! Unable to Rename '{$old_file_path}' to '{$new_file_path}'", 'red');
        }

        if(unlink($old_file_path)) return Message::show("Success", "Controller Renamed: From '{$old_name}' to '{$new_name}' Successfully.");

        return Message::show("Error", "Something Went Wrong! Unable to Rename Controller!", 'red');
        
    }

    // Remove Controller
    public function pop(): array
    {
        // Check Controller Name is Alphabetic or Not Blank or No Special Character
        $name = $this->args[0] ?? '';

        if(!preg_match('/^[a-zA-Z_]+$/', $name)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Controller old name: '{$name}' is invalid!", "red")
            ];
        }

        // Get File Path
        $file_path = "{$this->dir}/{$name}.php";

        // Check File Exist
        if(!is_file($file_path)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Controller: '{$name}' Doesn't Exist!", "red")
            ];
        }

        if(unlink($file_path)){
            return [
                'status'    =>  true,
                'message'   =>  Message::show("Success", "Controller '{$name}' removed successfully.")
            ];
        }
        
        return [
            'status'    =>  false,
            'message'   =>  Message::show("Error", "Something went wrong! unable to remove Controller!", 'red')
        ];
    }

    // Controllers List
    public function list(): array
    {
        $files = Directory::files($this->dir, 'php');
        return array_map(function($file){
            return 'CBM\\App\\Controller\\'.basename($file, '.php');
        }, $files);
    }
}