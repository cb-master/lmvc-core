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
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

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
            // Controller Commands
            'make:controller'   =>  \CBM\Core\Console\Commands\MakeController::class, // Complete
            'rename:controller' =>  \CBM\Core\Console\Commands\RenameController::class, // Complete
            'pop:controller'    =>  \CBM\Core\Console\Commands\PopController::class, // Complete
            'list:controller'   =>  \CBM\Core\Console\Commands\ListController::class, // Complete
            // Middleware Commands
            'make:middleware'   =>  \CBM\Core\Console\Commands\MakeMiddleware::class,
            'rename:middleware' =>  \CBM\Core\Console\Commands\RenameMiddleware::class,
            'pop:middleware'    =>  \CBM\Core\Console\Commands\PopMiddleware::class,
            'list:middleware'   =>  \CBM\Core\Console\Commands\ListMiddleware::class,
            // Model Commands
            'make:model'        =>  \CBM\Core\Console\Commands\MakeModel::class,
            'rename:model'      =>  \CBM\Core\Console\Commands\RenameModel::class,
            'pop:model'         =>  \CBM\Core\Console\Commands\PopModel::class,
            // View Commands
            'make:view'         =>  \CBM\Core\Console\Commands\MakeView::class,
            'rename:view'       =>  \CBM\Core\Console\Commands\RenameView::class,
            'pop:view'          =>  \CBM\Core\Console\Commands\PopView::class,
            // Other Commands
            'list'              =>  \CBM\Core\Console\Commands\ListCommands::class,
            'help'              =>  \CBM\Core\Console\Commands\HelpCommands::class
        ];
    }

    protected function printHelp()
    {
        echo "\n##################################\n";
        echo "LAIKA CLI TOOL\n";
        echo "Usage: laika <command> [options]\n";
        echo "##################################\n\n";
        echo "Available Commands:\n\n";
        array_filter($this->commands, function($command){
            echo "\t$command\n";
        }, ARRAY_FILTER_USE_KEY);
        echo "\n";
    }
}