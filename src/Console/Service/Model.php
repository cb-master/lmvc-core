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

class Model
{
    // Model Directory
    private string $dir = BASE_PATH . '/app/Model';

    // Args
    private array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    // Create Model
    public function create(): array
    {
        // Check Model Name is Alphabetic or Not Blank or No Special Character
        $name = $this->args[0] ?? '';
        $table = $this->args[1] ?? 'table_name';
        $id = $this->args[2] ?? 'id';
        if(!preg_match('/^[a-zA-Z_]+$/', $name)) return [
            'status'    => false,
            'message'   => Message::show("Error", "Invalid Name: '{$name}'", "red")
        ];

        // Get File Path
        $file_path = "{$this->dir}/{$name}.php";

        // Check File Already Exist
        if(is_file($file_path)) return [
            'status'    => false,
            'message'   => Message::show("Error", "Model: '{$name}' Already Exist", "red")
        ];

        // Make File
        $content = file_get_contents(__DIR__.'/../Samples/Model.sample');
        $content = str_replace('{{NAME}}', $name, $content);
        $content = str_replace('{{TABLE_NAME}}', $table, $content);
        $content = str_replace('{{TABLE_ID}}', $id, $content);

        if(file_put_contents($file_path, $content) === false) return [
            'status'    => false,
            'message'   => Message::show("Error", "Something Went Wrong! Unable to Create '{$file_path}'", 'red')
        ];
        return [
            'status'    => true,
            'message'   => Message::show("Success", "Model: '{$name}' Created Successfully")
        ];
    }

    // Rename Model
    public function rename(): array
    {
        // Check Model Name is Alphabetic or Not Blank or No Special Character
        $old_name = $this->args[0] ?? '';
        $new_name = $this->args[1] ?? '';

        if(!preg_match('/^[a-zA-Z_]+$/', $old_name)) return [
            'status'    => false,
            'message'   => Message::show("Error", "Model Old Name: '{$old_name}' is Invalid", "red")
        ];

        if(!preg_match('/^[a-zA-Z_]+$/', $new_name)) return [
            'status'    => false,
            'message'   => Message::show("Error", "Model New Name: '{$new_name}' is Invalid", "red")
        ];

        // Get File Path
        $old_file_path = "{$this->dir}/{$old_name}.php";
        $new_file_path = "{$this->dir}/{$new_name}.php";

        // Check File Exist
        if(!is_file($old_file_path)) return [
            'status'    => false,
            'message'   => Message::show("Error", "Old Model: '{$old_name}' Doesn't Exist", "red")
        ];

        // Check New Named File Does Not Exist
        if(is_file($new_file_path)) return [
            'status'    => false,
            'message'   => Message::show("Error", "New Model: '{$new_name}' Already Exist", "red")
        ];

        // Get Old File Content
        $content = file_get_contents($old_file_path);
        // Replace Old Class With New Name
        $content = preg_replace("/class {$old_name} Extends Model/i","class {$new_name} Extends Model", $content);

        // Write New File
        if(file_put_contents($new_file_path, $content) === false) return [
            'status'    => false,
            'message'   => Message::show("Error", "Something Went Wrong! Unable to Rename Model", 'red')
        ];

        // Remove Old File
        if(unlink($old_file_path)) return [
            'status'    =>  true,
            'message'   =>  Message::show("Success", "Model Renamed: From '{$old_name}' to '{$new_name}' Successfully.")
        ];

        // If Unable to Remove Old File
        return [
            'status'    =>  false,
            'message'   =>  Message::show("Error", "Something Went Wrong! Unable to Remove Old File", 'red')
        ];
    }

    // Remove Model
    public function pop(): array
    {
        // Check Model Name is Alphabetic or Not Blank or No Special Character
        $name = $this->args[0] ?? '';

        if(!preg_match('/^[a-zA-Z_]+$/', $name)) return [
            'status'    => false,
            'message'   => Message::show("Error", "Model Old Name: '{$name}' is Invalid", "red")
        ];

        // Get File Path
        $file_path = "{$this->dir}/{$name}.php";

        // Check File Exist
        if(!is_file($file_path)) return [
            'status'    => false,
            'message'   => Message::show("Error", "Model: '{$name}' Doesn't Exist", "red")
        ];

        if(unlink($file_path)) return [
            'status'    => true,
            'message'   => Message::show("Success", "Model '{$name}' Removed Successfully")
        ];
        
        return [
            'status'    => false,
            'message'   => Message::show("Error", "Something Went Wrong! Unable to Remove Model", 'red')
        ];
    }

    // Models List
    public function list(): array
    {
        $files = Directory::files($this->dir, 'php');
        return array_map(function($file){
            return 'CBM\\App\\Model\\'.basename($file, '.php');
        }, $files);
    }
}