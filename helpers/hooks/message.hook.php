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

use CBM\Session\Session;

####################################################################
/*----------------------- MESSAGE FILTERS ------------------------*/
####################################################################
// Set Notification Message
add_filter('message.set', function(?string $message, bool $status): void {
    Session::set('message', ['info'=>$message,'status'=>$status]);
    return;
});

// Get Notification Message
add_filter('message.show', function(): array {
    $message = Session::get('message');
    Session::pop('message');
    if(!$message) return [];

    return $message;
});