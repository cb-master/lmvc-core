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

use CBM\Core\{App\Router, Config, Filter, Uri, Option};

// Dump Data & Die
/**
 * @param mixed $data - Required Argument
 * @param bool $die - Default is false
 * @return void
*/
function dd(mixed $data, bool $die = false): void
{
    echo '<pre style="background-color:#000;color:#fff;">';
    var_dump($data);
    echo '</pre>';
    $die ? die() : $die;
}

// Show Data & Die
/**
 * @param mixed $data - Required Argument
 * @param bool $die - Default is false
 * @return void
*/
function show(mixed $data, bool $die = false): void
{
    echo '<pre style="background-color:#000;color:#fff;">';
    print_r($data);
    echo '</pre>';
    $die ? die() : $die;
}

// Purify Arry Values
/**
 * @param array $data Array Data to Purify
 * @return array
 */
function purify(array $data): array
{
    return array_map(function($val){
        return is_array($val)
            ? purify($val)
            : htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
    }, $data);
}

// Redirect
/**
 * @param string|array $slug Required Argument
 * @param ?array $params Optional Argument.
 * @return void
*/
function redirect(string|array $slug, ?array $params = null): void
{
    // Convert to String if Slug is Array
    if(is_array($slug)) $slug = implode('/', array_map('trim', $slug));
    $slug = str_replace('\\', '/', $slug);
    $slug = trim($slug, '/');

    // Redirect
    header('Location:'.Uri::build($slug, $params ?: []), true);
    die();
}

// Add Filter
/**
 * @param string $filter Required Argument.
 * @param callable $callback Required Argument.
 * @param int $priority Optional Argument. Default is 10
 * @return void
*/
function add_filter(string $filter, callable $callback, int $priority = 10): void
{
    Filter::add_filter($filter, $callback, $priority);
}

// Apply Filter
/**
 * @param string $filter Required Argument.
 * @param mixed $value Optional Argument. Default is Null.
 * @param mixed ...$args Optional Arguments.
 * @return mixed
*/
function apply_filter(string $filter, mixed $value = null, mixed ...$args): mixed
{
    return Filter::apply_filter($filter, $value, ...$args);
}

// Get option Value
/**
 * @param string $key Required Argument. Options Key Name
 * @param ?string $default Optional Argument. Default will return if no value found
 * @return ?string
*/
function option(string $key, ?string $default = null): ?string
{
    return Option::get($key) ?: Config::get('app', $key, $default);
}

/**
 * Host Path
 * @return string Return Host Path. Example: http://example.com or http://example.com/path if app hosted in path
 */
function host(): string
{
    return option('app.host') ?: Uri::base();
}

/**
 * Get Named Route
 * @return string
 */
function named(string $name, array $params = [], bool $url = false)
{
    return Router::url($name, $params, $url);
}