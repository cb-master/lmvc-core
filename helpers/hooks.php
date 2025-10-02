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

// App Host
add_filter('app.host', function(): string { return host(); });

// Asset Path
/**
 * @param string $path. Example 'css/style.css'
 * @param bool $in_template - If true, it will look for the asset in the lf-templates folder. Default is false.
 * @return string
 */
// Load Asset
add_filter('template.asset', function(string $file): string {
    if(parse_url($file, PHP_URL_HOST)){
        return $file;
    }
    $file = trim($file, '/');
    return apply_filter('app.host') . "resource/{$file}";
});

/**
 * App Logo
 * @param ?string $option_key opt_ken column value in Database options Table
 * @return string
 */
add_filter('app.logo', function(?string $option_key = null): string {
    $name = option($option_key ?? '') ?: null;
    $logo = $name ?: 'logo.png';
    return apply_filter('app.host') . "resource/img/{$logo}";
});

/**
 * App Icon
 * @param ?string $option_key opt_ken column value in Database options Table
 * @return string
 */
add_filter('app.icon', function(?string $option_key = null): string {
    $name = option($option_key ?? '') ?: null;
    $icon = $name ?: 'favicon.ico';
    return apply_filter('app.host') . "resource/img/{$icon}";
});