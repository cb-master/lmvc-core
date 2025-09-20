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

use CBM\Core\{Filters, Uri, Directory};

// App Uri
add_filter('app_uri', function(): string { return Uri::base(); });

// Template Asset Path
/**
 * @param string $subDirectory Sub Directory inside Views Directory. Default: assets
 * @example apply_filter('template_asset_path') // Returns: http://yourdomain.com/app/Views/assets
 * @example apply_filter('template_asset_path', 'admin/assets') // Returns: http://yourdomain.com/app/Views/admin/assets
 * @return string
 */
add_filter('template_asset_dir', function(string $subDirectory = ''): string
{
    // Remove 'assets' from Sub Directory if exists
    if(preg_match('/assets/i', $subDirectory)) $subDirectory = trim(str_ireplace('assets', '', $subDirectory), '/');
    // Set Default Sub Directory if not provided
    if(!$subDirectory) $subDirectory = 'assets';
    return BASE_PATH . "/app/Views/{$subDirectory}";
});

// Template Asset Path
/**
 * @param string $subDirectory Sub Directory inside Views Directory. Default: assets
 * @example apply_filter('template_asset_path') // Returns: http://yourdomain.com/app/Views/assets
 * @example apply_filter('template_asset_path', 'admin/assets') // Returns: http://yourdomain.com/app/Views/admin/assets
 * @return string
 */
add_filter('template_asset_path', function(string $subDirectory = ''): string
{
    // Remove 'assets' from Sub Directory if exists
    if(preg_match('/assets/i', $subDirectory)) $subDirectory = trim(str_ireplace('assets', '', $subDirectory), '/');
    // Set Default Sub Directory if not provided
    $subDirectory = !$subDirectory ? 'assets' : "{$subDirectory}/assets";
    return apply_filter('app_uri') . "app/Views/{$subDirectory}";
});

// Get Style Uri
/**
 * @param string $style Style File Name without Extension. Example: style
 * @param string $subDirectory Sub Directory inside Views Directory. Default: assets
 * @example apply_filter('load_style', 'style') // Returns: http://yourdomain.com/app/Views/assets/css/style.css
 * @example apply_filter('load_style', 'style.min') // Returns: http://yourdomain.com/app/Views/assets/css/style.min.css
 * @return string
 */
add_filter('load_style', function(string $style, string $subDirectory = ''): string { return apply_filter('template_asset_path', $subDirectory) . "/css/{$style}.css"; });

// Get Script Uri
/**
 * @param string $script Script File Name without Extension. Example: app
 * * @param string $subDirectory Sub Directory inside Views Directory. Default: assets
 * @example apply_filter('load_script', 'app') // Returns: http://yourdomain.com/app/Views/assets/js/app.css
 * @example apply_filter('load_script', 'app.min') // Returns: http://yourdomain.com/app/Views/assets/css/js.min.css
 * @return string
 */
add_filter('load_script', function(string $script, string $subDirectory = ''): string { return apply_filter('template_asset_path', $subDirectory) . "/js/{$script}.js"; });

// Get Image Uri
/**
 * @param string $image Image File Name without Extension. Example: app
 * @example apply_filter('load_image', 'logo.png') // Returns: http://yourdomain.com/app/Views/assets/images/logo.png
 * @return string
 */
add_filter('load_image', function(string $image, string $subDirectory = ''): string {
    // Validate Image Extension
    $imgFile = apply_filter('template_asset_dir', $subDirectory) . "/images/{$image}";
    if(is_file($imgFile)){
        $mimeType = pathinfo($imgFile, PATHINFO_EXTENSION);
        if(!preg_match('/image/i', $mimeType)) throw new InvalidArgumentException("Image File Must Have Valid Image Extension.");
        return apply_filter('template_asset_path', $subDirectory) . "/images/{$image}";
    }
    return '';
});