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
namespace CBM\Core\Console\Commands\Secret;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\{Console\Command,Config};

class Pop Extends Command
{
    /**
     * Run the command to create a new controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        // Create Secret Config File if Not Exist
        if(!Config::has('secret')) Config::create('secret', []);
        // Create Secret Key Value
        Config::set('secret', 'key', '');
        // Set Message
        $this->info("Secret Key Removed Successfully");
        return;
    }
}