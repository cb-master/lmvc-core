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

use CBM\Core\Date;

#####################################################################
/*------------------------- DATE FILTERS --------------------------*/
#####################################################################
// Date Object
add_filter('date.object', function(string $time = 'now'): object {
    $format = option('time.format', 'Y-M-d H:i:s');
    $timezone = option('time.zone', 'Europe/London');
    return new Date($time, $format, $timezone);
});