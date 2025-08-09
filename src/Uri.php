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
namespace CBM\Core;

class Uri
{
    // Instance
    protected static ?self $instance = null;

    // Scheme
    protected string $scheme;
    // Host
    protected string $host;
    // Path
    protected string $path;
    // Query string
    protected string $queryString;
    // Base URL
    protected string $baseUrl;
    // Script name
    protected string $scriptName;
    // Script directory
    protected string $directory;

    // Constructor
    public function __construct()
    {
        $this->scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $this->host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $this->queryString = $_SERVER['QUERY_STRING'] ?? '';

        $this->scriptName = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '/index.php');
        $this->directory = rtrim(str_replace('\\', '/', dirname($this->scriptName)), '/');

        $this->baseUrl = $this->scheme . '://' . $this->host . $this->directory . '/';
    }

    // Get Instance
    /**
     * * @return self
     */
    private static function instance():self
    {
        return self::$instance ??= new self();
    }

    // Get Current URL
    /**
     * * @return string Current URL
     */
    public static function current():string
    {
        return rtrim(self::instance()->scheme . '://' . self::instance()->host . ($_SERVER['REQUEST_URI'] ?? '/'), '/');
    }

    // Get Base URL
    /**
     * * @return string Base URL
     */
    public static function base(): string
    {
        return self::instance()->baseUrl;
    }

    // Get Sub Directory
    /**
     * @return string
     */
    public static function directory():string
    {
        return trim(self::instance()->directory, '/');
    }

    // Get Path
    /**
     * * @return string Path/Sub Folder
     */
    public static function path():string
    {
        return trim(str_replace(self::instance()->directory, '', self::instance()->path), '/');
    }

    // Get Query Strings
    /**
     * * @return array Query Strings
     */
    public static function query():array
    {
        parse_str(self::instance()->queryString, $queries);
        return $queries;
    }

    // Get Query String by Key
    /**
     * @param string $key - Required Argument as String
     * @param string|null $default - Optional Argument as String
     * @return string Get Query String by Key
     */
    public static function get(string $key, ?string $default = null):?string
    {
        return self::instance()->query()[$key] ?? $default;
    }

    // Build URL From Args
    /**
     * @param string $path Required Argument as String. Default is '/'
     * @param array $params Optional Argument as Array. Example ['key' => 'value']
     * @return string Build URL
     */
    public static function build(string $path = '/', array $params = []):string
    {
        $path = trim($path, '/');
        $url = self::instance()->base() . $path;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    // Get Segment by Index
    /**
     * @param int $index - Required Argument as Integer, Start from 1
     * @return string Get Segment by Index
     */
    public static function segment(int $index):?string
    {
        $segments = explode('/', trim(self::instance()->path(), '/'));
        return $segments[$index - 1] ?? null;
    }

    // Get All Segments
    /**
     * @return array Get All Segments
     */
    public static function segments(): array
    {
        $segments = explode('/', trim(self::instance()->path(), '/'));
        return $segments[0] ? $segments : [];
    }

    // Get URL With Query String(s)
    /**
     * @param array $params - Required Argument as Array. Example ['key' => 'value']
     * @return string Get URL With Query String(s)
     */
    public static function withQuery(array $params):string
    {
        $queries = array_merge(self::query(), $params);

        return self::base() . self::path() . '?' . http_build_query($queries);
    }

    // Get URL Without Query String(s)
    /**
     * @param array $keys - Required Argument as Array. Example ['key1', 'key2']
     * @return string Get URL Without Query String(s)
     */
    public static function withoutQuery(array $keys): string
    {
        $queries = self::query();
        foreach ($keys as $key) {
            unset($queries[$key]);
        }
        return self::base() . self::path() . (empty($queries) ? '' : '?' . http_build_query($queries));
    }

    // Get URL With Incremented Query String
    /**
     * @param ?string $key Optional Argument. Default is null
     * @return string Get URL With Incremented Query String
     */
    public static function incrementQuery(?string $key = null):string
    {
        $key = $key ?: 'page';
        $queries = self::query();
        $queries[$key] = isset($queries[$key]) && is_numeric($queries[$key]) && (int) $queries[$key] > 1
            ? (int)$queries[$key] + 1 : 2;

        return self::base() . self::path() . '?' . http_build_query($queries);
    }

    // Get URL With Decremented Query String
    /**
     * @param ?string $key Optional Argument. Default is null
     * @return string Get URL With Decremented Query String
     */
    public static function decrementQuery(?string $key = null): string
    {
        $key = $key ?: 'page';
        $queries = self::query();
        $queries[$key] = isset($queries[$key]) && is_numeric($queries[$key]) && (int) $queries[$key] > 1
            ? (int)$queries[$key] - 1 : 1;

        return self::base() . self::path() . '?' . http_build_query($queries);
    }

    // Get Host Name
    /**
     * * @return string Host Name
     */
    public static function host():string
    {
        $host = parse_url($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '', PHP_URL_HOST);
        return $host ?: 'localhost';
    }

    // HTTPS Check
    public static function isHttps():bool
    {
        return ($_SERVER['HTTPS'] ?? 'off') != 'off';
    }
}