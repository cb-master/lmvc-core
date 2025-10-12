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
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\Http\Request;

######################################################################
/*------------------------ REQUEST FILTERS -------------------------*/
######################################################################
// Get Request Header
add_filter('request.header', function(string $key): ?string {
    return Request::header($key);
});

// Get Request Input Value
add_filter('request.input', function(string $key, mixed $default = ''): mixed {
    return Request::input($key, $default);
});

// Get Request Values
add_filter('request.all', function(): array {
    return Request::all();
});

// Check Method Request is Post/Get/Ajax
add_filter('request.is', function(string $method): bool {
    $method = strtolower($method);
    switch ($method) {
        case 'post':
            return Request::isPost();
            break;
        case 'get':
            return Request::isGet();
            break;
        case 'ajax':
            return Request::isAjax();
            break;        
        default:
            return false;
            break;
    }
});

/**
 * Get Request Error
 * @return string
 */
add_filter('request.error', function(string $key): string{
    $errors = Request::errors();
    return $errors[$key][0] ?? '';
});