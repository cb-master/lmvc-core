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
namespace CBM\Core\Console\Commands;

use CBM\Core\Console\Command;

class ListCommands Extends Command
{
    /**
     * Run the command to create a new controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        $this->info('List of Commands');
    }
}