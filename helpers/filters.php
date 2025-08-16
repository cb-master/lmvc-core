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

// Load View
add_filter('load_view', function(string $view){ return trim($view); });

// Load Style
add_filter('load_style', function(string $style){ return trim($style); });

// Load Script
add_filter('load_script', function(string $script){ return trim($script); });