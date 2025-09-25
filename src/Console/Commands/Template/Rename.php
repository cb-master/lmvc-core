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

// Rename Template Class
class Rename Extends Command
{
    // App Template Old Path
    protected string $old_path = APP_PATH . '/lf-templates';
    
    // App Template New Path
    protected string $new_path = APP_PATH . '/lf-templates';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z0-9_\-\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Check Parameters
        if(count($params) < 2){
            $this->error("Usage: laika rename:template <old_name> <new_name>");
            return;
        }

        // Template Name
        $old = $params[0];
        $new = $params[1];

        // Check Old Template Name is Valid
        if(!preg_match($this->exp, $old)){
            // Invalid Template Name
            $this->error("Invalid Old Template Name: '{$old}'");
            return;
        }
        // Check New Template Name is Valid
        if(!preg_match($this->exp, $new)){
            // Invalid Template Name
            $this->error("Invalid New Template Name: '{$old}'");
            return;
        }

        // Get Old and New Parts
        $old_parts = $this->parts($old, false);
        $new_parts = $this->parts($new, false);

        // Get Directory Paths
        $this->old_path .= $old_parts['path'];
        $this->new_path .= $new_parts['path'];

        $old_file = "{$this->old_path}/tpl-{$old_parts['name']}.tpl.php";
        $new_file = "{$this->new_path}/tpl-{$new_parts['name']}.tpl.php";

        // Check Old Template Path is Valid
        if(!is_file($old_file)){
            $this->error("Invalid Template Name or Path: '$old'");
            return;
        }

        // Check New Path Exist
        if(!Directory::exists($this->new_path)){
            Directory::make($this->new_path);
        }

        // Check New Template Path is Valid
        if(is_file($new_file)){
            $this->error("New Template Already Exist: '$old'");
            return;
        }

        // Get Contents
        $content = file_get_contents($old_file);
        if($content === false){
            $this->error("Failed to Read Template: '{$old}'");
            return;
        }

        // Create New Template File
        if(file_put_contents($new_file, $content) === false){
            $this->error("Failed to Create Template: {$new}");
            return;
        }

        // Remove Old Template File

        if(!unlink($old_file)){
            $this->error("Failed to Remove Template: '{$old_file}'");
            return;
        }
        
        $this->info("Template Renamed Successfully: '{$old}'->'{$new}'");
        return;
    }
}