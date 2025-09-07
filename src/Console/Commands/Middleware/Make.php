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
namespace CBM\Core\Console\Commands\Middleware;

use CBM\Core\{Console\Command, Directory};

// Make Middleware Class
class Make Extends Command
{
    // App Middleware Path
    protected string $path = BASE_PATH . '/app/Middleware';

    /**
     * Run The Command to Create a New Middleware.
     *
     * @param array $params
     * @return void
     */
    public function run(array $params): void
    {
        // Middleware Name
        $input = $params[0] ?? '';
        $name = trim($input);
        $namespace = '';

        if(!preg_match('/^[a-zA-Z_\/]+$/', $name)){
            // Invalid Name
            $this->error("Invalid Middleware Name: '{$input}'");
            return;
        }

        $parts = explode('/', $input);
        $name = array_pop($parts);

        // Make Path and Namespace
        foreach($parts as $part){
            $part = ucfirst($part);
            $this->path .= "/{$part}";
            $namespace .= "\\{$part}";
        }

        // Make Directory if Not Exists
        if(!Directory::exists($this->path)){
            Directory::make($this->path);
        }

        $file = "{$this->path}/{$name}.php";

        if(is_file($file)){
            $this->error("Middleware Already Exist: {$file}");
            return;
        }

        // Get Sample Content
        $content = file_get_contents(__DIR__ . '/../../Samples/Middleware.sample');

        // Replace Placeholders
        $content = str_replace(['{{NAMESPACE}}','{{NAME}}'], [$namespace,$name], $content);

        if(file_put_contents($file, $content) === false){
            $this->error("Failed to Create Middleware: {$input}");
            return;
        }
        
        
        
        $this->info("Middleware Created Successfully: {$input}");
    }
}