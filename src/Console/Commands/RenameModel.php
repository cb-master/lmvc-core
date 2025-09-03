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

use CBM\Core\Console\Command;

class RenameModel Extends Command
{
    // App Model Path
    protected string $path = BASE_PATH . '/app/Model';

    /**
     * Run the command to create a new controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Model Name
        $name = $params[0] ?? null;
        // Table Name
        $table = $params[1] ?? 'test_table';
        // Primary Key Name
        $id = $params[2] ?? 'id';

        if(!preg_match('/^[a-zA-Z_]+$/', $name)){
            // Invalid Name
            $this->error("Invalid Model Name: '{$name}'");
            return;
        }

        $file = "{$this->path}/{$name}.php";

        if(is_file($file)){
            $this->error("Model Already Exist: {$name}");
            return;
        }

        // Get Sample Content
        $content = file_get_contents(__DIR__ . '/../Samples/Model.sample');

        // Replace Placeholders
        $content = str_replace(['{{NAME}}','{{TABLE_NAME}}','{{TABLE_ID}}'], [$name,$table,$id], $content);

        if(file_put_contents($file, $content) === false){
            $this->error("Failed to Create Model: {$name}");
            return;
        }
        
        
        $this->info("Model Created Successfully: {$name}");
        return;
    }
}