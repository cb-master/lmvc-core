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
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

Class Controller
{
    // Template Directory
    protected ?string $templatedirectory;

    public function __construct(string $template_directory = '')
    {
        $this->templatedirectory = trim($template_directory, '/');
    }

    // View Controller
    /**
     * @param string $view View File Name. Example: index
     * @param array $data Extracted Data. Example ['title'=>'Dashboard']
     */
    protected function view(string $view, array $data = []): void
    {
        if($this->templatedirectory) $view = "{$this->templatedirectory}/{$view}";
        View::render($view, $data);
    }
}