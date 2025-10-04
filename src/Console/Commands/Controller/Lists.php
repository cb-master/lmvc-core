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
namespace CBM\Core\Console\Commands\Controller;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\{Console\Command, Directory};

// Make Controller Class
class Lists Extends Command
{
    // App Controller Path
    protected string $path = APP_PATH . '/lf-app/Controller';

    // Accepted Regular Expresion
    private string $exp = '/^[a-zA-Z_\/]+$/';

    /**
     * @param array $params
     */
    public function run(array $params): void
    {
        // Path
        $path = trim($params[0] ?? '', '/');

        // Check View Name is Valid
        if($path && !preg_match($this->exp, $path)){
            // Invalid View Name
            $this->error("Invalid View Path: '{$path}'");
            return;
        }

        // Get Path if Given
        if($path) $this->path .= "/{$path}";

        // Check Path Exist
        if(!Directory::exists($this->path)){
            $this->error("View Path Not Found: '{$this->path}'");
            return;
        }

        $paths = Directory::scanRecursive($this->path, true, 'php');
        $items = [];
        foreach($paths as $file){
            if(is_file($file)) $items[] = 'CBM\\App\\Controller\\'.str_replace(["{$this->path}/", '.php','/'], ['','','\\'], $file);
        }

        // Header
        $headers = ['#', 'Templates'];
        
        // Find max width for "File Path" column
        $maxLength = max(array_map('strlen', $items));
        $col2Width = max(strlen($headers[1]), $maxLength);

        // Table width
        $line = '+' . str_repeat('-', 5) . '+' . str_repeat('-', $col2Width + 2) . "+\n";

        // Print Header
        echo $line;
        printf("| %-3s | %-{$col2Width}s |\n", $headers[0], $headers[1]);
        echo $line;

        $count = 1;
        // Print Rows
        foreach ($items as $item) {
            $item = str_replace(["{$this->path}/", '.tpl.php'], [''], $item);
            printf("| %-3d | %-{$col2Width}s |\n", $count, $item);
            $count ++;
        }

        echo $line;
        echo "Total: {$count}\n\n";
        return;
    }
}