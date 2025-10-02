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
namespace CBM\Core\Console;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

class Kernel
{
    // Arguments
    protected array $args;

    // Commands
    protected array $commands = [];

    /**
     * @param array $args Pass $argv from the command line
     */
    public function __construct(array $args)
    {
        // Set Arguments
        $this->args = $args;
        // Register Commands
        $this->registerCommands();
    }

    
    public function handle()
    {
        // Remove "laika"
        array_shift($this->args);

        $command = $this->args[0] ?? null;

        $params = array_slice($this->args, 1);

        if ($command && isset($this->commands[strtolower($command)])) {
            $class = $this->commands[strtolower($command)];
            
            call_user_func([new $class(), 'run'], $params);
        } else {
            $this->printHelp();
        }
    }

    protected function registerCommands()
    {
        $this->commands = [
            /* Template Controller Commands */
            'make:controller'       =>  \CBM\Core\Console\Commands\Controller\Make::class,
            'rename:controller'     =>  \CBM\Core\Console\Commands\Controller\Rename::class,
            'pop:controller'        =>  \CBM\Core\Console\Commands\Controller\Pop::class,
            'list:controller'       =>  \CBM\Core\Console\Commands\Controller\Lists::class,
            /* Middleware Commands */
            'make:middleware'       =>  \CBM\Core\Console\Commands\Middleware\Make::class,
            'rename:middleware'     =>  \CBM\Core\Console\Commands\Middleware\Rename::class,
            'pop:middleware'        =>  \CBM\Core\Console\Commands\Middleware\Pop::class,
            'list:middleware'       =>  \CBM\Core\Console\Commands\Middleware\Lists::class,
            /* Model Commands */
            'make:model'            =>  \CBM\Core\Console\Commands\Model\Make::class,
            'rename:model'          =>  \CBM\Core\Console\Commands\Model\Rename::class,
            'pop:model'             =>  \CBM\Core\Console\Commands\Model\Pop::class,
            'list:model'            =>  \CBM\Core\Console\Commands\Model\Lists::class,
            /* View Commands */
            'make:view'             =>  \CBM\Core\Console\Commands\View\Make::class,
            'rename:view'           =>  \CBM\Core\Console\Commands\View\Rename::class,
            'pop:view'              =>  \CBM\Core\Console\Commands\View\Pop::class,
            'list:view'             =>  \CBM\Core\Console\Commands\View\Lists::class,
            /* Other Commands */
            'help'                  =>  \CBM\Core\Console\Commands\ListCommands::class,
            /* Migrate */
            'migrate'               =>  \CBM\Core\Console\Commands\Migrate::class,
            /* Route */
            'list:route'            =>  \CBM\Core\Console\Commands\Route\Lists::class,
            /* Secret Key */
            'generate:secret'       =>  \CBM\Core\Console\Commands\Secret\Generate::class,
            'pop:secret'            =>  \CBM\Core\Console\Commands\Secret\Pop::class,
        ];
    }

    protected function printHelp()
    {
        echo <<<COMMON
        ##################################
        LAIKA CLI TOOL
        Usage: php laika <command> [options]
        ##################################

        AVAILABLE COMMANDS\n
        COMMON;
        $keys = array_keys($this->commands);
        foreach($keys as $key){
            echo "\t-> {$key}\n";
        }
    }
}