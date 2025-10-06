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

###################################################################
/*------------------------- URI FILTERS -------------------------*/
###################################################################
/**
 * Make Uri
 * @param string|array $slug - Optional Argument. Default is ''
 * @param string|array $queries - Optional Argument. Default is ''
 * @return string New Url
 */
add_filter('uri.make', function(string|array $slug = '', array $queries = []): string {
    // Get Slug
    $slug = is_array($slug) ? $slug : [$slug];
    $slug = implode('/', $slug);
    return Uri::build($slug, $queries);
});
