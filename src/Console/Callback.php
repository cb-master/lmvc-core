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

class Callback
{
    // Args
    private array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    public function register(): void
    {
        if(isset($this->args[0]) && $this->args[0]){
            $params = explode(':', $this->args[0]);
            $service = $params[0];
            $action = $params[1] ?? null;
            array_shift($this->args);
            $result = $this->action($service, $action);
            print_r($result['message'] ?? $result);
        }
        // Show Message if Blank Input Given
        else{
            echo Message::show("Error", "Invalid Input: '" . implode(' ', $this->args) . "'. Please run 'php laika -h' for help", 'red');
        }
    }

    /**
     * @param string $service Service to call. Example middleware,model etc.
     * @param ?string $action Action to call. Example create,modify etc.
     */
    private function action(string $service, ?string $action): string|array
    {
        $action = $action ? strtolower($action) : 'handle';

        $class = __NAMESPACE__.'\\Service\\'.ucfirst($service);

        if(!class_exists($class) || !method_exists($class, $action)){
            return Message::show("Error", "Invalid Parameter. Please run 'php laika -h' for help", "red");
        }
        
        return call_user_func([new $class($this->args), $action]);
    }
}