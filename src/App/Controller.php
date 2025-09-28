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
namespace CBM\Core\App;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\{Directory, Config, ClientInfo};
use RuntimeException;

Class Controller
{
    // Template Directory
    private string $templateDirectory = APP_PATH . "/lf-templates";

    /** Variables available to templates */
    protected array $vars = [];

    // Initiate Controller & Assign Controller Vars
    public function __construct(array $args)
    {
        // Assign Vars
        $this->vars = array_merge($this->vars, $args);
        // Add Default Config Data
        $this->vars['app_info'] = Config::get('app');
        $this->vars['client_info'] = new ClientInfo();
    }

    // Assign Variables to Controller
    /**
     * @param string|array $key Key or Array of Key-Value Pairs
     * @param mixed $value Value if Key is String
     * @example $this->assign('title', 'Dashboard');
     * @example $this->assign(['title'=>'Dashboard', 'name'=>'John']);
     * @return void
     */
    protected function assign(string|array $key, mixed $value = null): void
    {
        if(is_array($key)){
            $this->vars = array_merge($key, $this->vars);
        }else{
            $this->vars[$key] = $value;
        }
        return;
    }

    // Get Assigned Vars
    /**
     * Get All Assigned Vars in Controller
     * @return array
     */
    protected function getAssignedVars(): array
    {
        return array_keys($this->vars);
    }

    // Set Template Sub Directory
    /**
     * @param string $directory Sub Directory inside Views Directory
     * @throws RuntimeException
     * @example $this->templateSubDirectory('admin');
     * @example $this->templateSubDirectory('admin/themes/default');
     * @return void
     */
    protected function addTemplateDir(string $directory): void
    {
        $directory = trim($directory, '/');
        if($directory == '') throw new RuntimeException("Template Sub Directory Can't Be Empty");
        $this->templateDirectory .= "/{$directory}";
        return;
    }

    // View Controller
    /**
     * @param string $view View File Name. Example: index
     */
    protected function view(string $view): void
    {        
        // Require All Template Helpers
        $hooks_path = $this->templateDirectory . '/hooks';
        // Create Hooks Path if Does Not Exists
        Directory::make($hooks_path);
        // Load Hooks
        $files = Directory::scanRecursive($hooks_path, true, ['php']);
        foreach($files as $file){
            require_once $file;
        }

        // Create Template Directory htaccess if Not Available
        if(!is_file("{$this->templateDirectory}/.htaccess")) file_put_contents("{$this->templateDirectory}/.htaccess", "Deny from all");

        $view = trim($view, '/');
        $viewFile = "{$this->templateDirectory}/{$view}.tpl.php";
        
        // Load Template Content
        View::render($viewFile, $this->vars);
        return;
    }
}