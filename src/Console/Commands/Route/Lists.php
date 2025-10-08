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
namespace CBM\Core\Console\Commands\Route;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\{Console\Command, App\Http};

// Make Controller Class
class Lists Extends Command
{
    // App Controller Path
    protected string $path = APP_PATH . '/lf-routes';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z_\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        echo <<<PHP
        -------------------------------------------------------------------
        REGISTERED ROUTES:
        -------------------------------------------------------------------\n
        PHP;
        // Get Http List
        Http::inspectAll();
        return;
    }
}