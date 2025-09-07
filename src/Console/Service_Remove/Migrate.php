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
namespace CBM\Core\Console\Service;

use CBM\Core\Date;

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\Console\Message;

class Migrate
{
    /**
     * @var array $args
     */
    private array $args;

    /**
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->args = $args;
    }

    // Handle Migration
    public function handle(): array
    {
        $db = new Database($this->args);

        // Up Database
        $result = $db->up();

        if(!$result['status']) return $result;

        return [
            'status'    =>  true,
            'message'   =>  Message::show('Success', 'laika Migration Completed Successfully'),
        ];
    }
}