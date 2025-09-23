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

use CBM\Core\Uri;

// App Uri
add_filter('app_uri', function(): string { return option('app_host') ?: Uri::base(); });

// Template Asset Path
/**
 * @param string $path. Example 'css/style.css'
 * @param bool $in_template - If true, it will look for the asset in the lf-templates folder. Default is false.
 * @return string
 */
// Load Asset
add_filter('load_asset', function(string $path, bool $in_template = false): string {
    $path = trim($path, '/');
    if($in_template) $path = "lf-templates/{$path}";
    $path = preg_replace('/asset[s]*/i', 'resource', $path);
    return apply_filter('app_uri') . "assets/{$path}";
});