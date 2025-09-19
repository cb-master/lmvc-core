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

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

// Load View
add_filter('load_view', function(string $view){
    $view = trim($view, '/');
    $viewFile = BASE_PATH . "/app/Views/{$view}.tpl.php";
    return $viewFile;
});

// Load Style
add_filter('load_style', function(string $style){ return trim($style); });

// Load Script
add_filter('load_script', function(string $script){ return trim($script); });