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

class View
{
    // View Directory
    private string $dir = BASE_PATH . '/app/Views';

    // Args
    private array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    // Create View
    public function create(): array
    {
        // Check View Name is Alphabetic or Not Blank or No Special Character
        $name = $this->args[0] ?? '';
        $name = trim($name, '/');
        if(!preg_match('/^[a-zA-Z\/_-]+$/', $name)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Invalid Name: '{$name}'", "red")
            ];
        }

        // Get File Path
        $file_path = "{$this->dir}/{$name}.tpl.php";

        Directory::make(dirname($file_path));

        // Check File Already Exist
        if(is_file($file_path)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "View: '{$name}' Already Exist", "red")
            ];
        }

        // Make File
        $content = file_get_contents(__DIR__.'/../Samples/View.sample');

        if(file_put_contents($file_path, $content) === false){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Something Went Wrong! Unable to Create '{$file_path}'", 'red')
            ];
        }
        return [
            'status'    =>  true,
            'message'   =>  Message::show("Success", "View: '{$name}' Created Successfully")
        ];
    }

    // Create Template View
    public function template(): array
    {
        // Check View Name is Alphabetic or Not Blank or No Special Character
        $name = $this->args[0] ?? '';
        $name = trim($name, '/');
        if(!preg_match('/^[a-zA-Z-\/_-]+$/', $name)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Invalid Name: '{$name}'", "red")
            ];
        }

        // Get File Path
        $file_path = "{$this->dir}/{$name}.tpl.php";

        Directory::make(dirname($file_path));

        // Check File Already Exist
        if(is_file($file_path)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "View: '{$name}' Already Exist", "red")
            ];
        }

        // Make File
        $content = file_get_contents(__DIR__.'/../Samples/TemplateView.sample');

        if(file_put_contents($file_path, $content) === false){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Something Went Wrong! Unable to Create '{$file_path}'", 'red')
            ];
        }
        return [
            'status'    =>  true,
            'message'   =>  Message::show("Success", "View: '{$name}' Created Successfully")
        ];
    }

    // Rename View
    public function rename(): array
    {
        // Check View Name is Alphabetic or Not Blank or No Special Character
        $old_name = $this->args[0] ?? '';
        $old_name = trim($old_name, '/');

        $new_name = $this->args[1] ?? '';
        $new_name = trim($new_name, '/');

        if(!preg_match('/^[a-zA-Z\/_-]+$/', $old_name)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Invalid Old View Name: '{$old_name}'", "red")
            ];
        }

        if(!preg_match('/^[a-zA-Z\/_-]+$/', $new_name)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Invalid New View Name: '{$new_name}'", "red")
            ];
        }

        // Get File Path
        $old_file_path = "{$this->dir}/{$old_name}.tpl.php";
        $new_file_path = "{$this->dir}/{$new_name}.tpl.php";

        // Check Old File Exist
        if(!is_file($old_file_path)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "Old View: '{$old_name}' Doesn't Exist", "red")
            ];
        }

        // Check New File Does Not Exist
        if(is_file($new_file_path)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "New View: '{$new_name}' Already Exist", "red")
            ];
        }

        if(rename($old_file_path, $new_file_path)){
            return [
                'status'    =>  true,
                'message'   =>  Message::show("Success", "View Renamed: From '{$old_name}' to '{$new_name}' Successfully.")
            ];
        }
        
        return [
            'status'    =>  false,
            'message'   =>  Message::show("Error", "Something Went Wrong! Unable to Rename View", 'red')
        ];
    }

    // Remove View
    public function pop(): array
    {
        // Check View Name is Alphabetic or Not Blank or No Special Character
        $name = $this->args[0] ?? '';
        $name = trim($name, '/');

        if(!preg_match('/^[a-zA-Z\/_-]+$/', $name)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "View Name: '{$name}' is Invalid!", "red")
            ];
        }

        // Get File Path
        $file_path = "{$this->dir}/{$name}.tpl.php";

        // Check File Exist
        if(!is_file($file_path)){
            return [
                'status'    =>  false,
                'message'   =>  Message::show("Error", "View: '{$name}' Doesn't Exist!", "red")
            ];
        }

        if(unlink($file_path)){
            return [
                'status'    =>  true,
                'message'   =>  Message::show("Success", "View '{$name}' removed successfully.")
            ];
        };
        
        return [
            'status'    =>  false,
            'message'   =>  Message::show("Error", "Something went wrong! unable to remove View!", 'red')
        ];
    }

    // Views List
    public function list(): array
    {
        $path = $this->args[1] ?? '';
        $path = trim($path, '/');

        if($path) $this->dir = "{$this->dir}/$path";
        
        $files = Directory::files($this->dir, 'php');
        return array_map(function($file){
            return basename($file, '.tpl.php');
        }, $files);
    }
}