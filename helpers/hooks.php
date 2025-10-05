<?php
/**
 * Laika PHP MVC Framework
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com
 * License: MIT
 * This file is part of the Laika PHP MVC Framework.
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

############################################################################
/*----------------------------- CALL FILTERS -----------------------------*/
############################################################################

declare(strict_types=1);

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

// Require All Functions File
array_map(function($file){ require_once $file; }, glob(__DIR__ . '/hooks/*.hook.php'));