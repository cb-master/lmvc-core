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

// Deny direct access to this file
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

Class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        echo BASE_PATH . "/app/Views/{$view}.php";
        require BASE_PATH . "/app/Views/{$view}.php";
    }
}