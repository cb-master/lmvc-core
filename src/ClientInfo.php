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

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

class ClientInfo
{
    // User Agent
    /**
     * @var string $userAgent
     */
    protected string $userAgent;

    // Client IP
    /**
     * @var string $ip
     */
    protected string $ip;

    public function __construct()
    {
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $this->ip = $this->getIp();
    }

    // Get User Agent
    public function userAgent(): string
    {
        return $this->userAgent;
    }

    // Get Client IP
    public function ip(): string
    {
        return $this->ip;
    }

    // Get Client Language
    public function language(): string
    {
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en-US';
        return explode(',', $lang)[0];
    }

    // Get Client OS Info
    public function os(): string
    {
        $ua = $this->userAgent;

        if (preg_match('/Android\s+([0-9\.]+)/i', $ua, $m)) {
            return 'Android ' . $m[1];
        }

        if (preg_match('/iPhone OS ([\d_]+)/i', $ua, $m)) {
            return 'iOS ' . str_replace('_', '.', $m[1]);
        }

        if (preg_match('/iPad; CPU OS ([\d_]+)/i', $ua, $m)) {
            return 'iPadOS ' . str_replace('_', '.', $m[1]);
        }

        if (preg_match('/Windows NT ([0-9\.]+)/i', $ua, $m)) {
            $map = [
                '10.0' => 'Windows 10',
                '6.3'  => 'Windows 8.1',
                '6.2'  => 'Windows 8',
                '6.1'  => 'Windows 7',
                '6.0'  => 'Windows Vista',
                '5.1'  => 'Windows XP',
            ];
            return $map[$m[1]] ?? 'Windows NT ' . $m[1];
        }

        if (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m)) {
            return 'Mac OS X ' . str_replace('_', '.', $m[1]);
        }

        if (preg_match('/Linux/i', $ua)) {
            return 'Linux';
        }

        return 'Unknown OS';
    }

    // Get Client Browser Info
    public function browser(): string
    {
        $ua = $this->userAgent;

        $browsers = [
            ['name' => 'Edge',              'pattern' => '/Edg\/([0-9\.]+)/'],
            ['name' => 'Internet Explorer', 'pattern' => '/MSIE\s([0-9\.]+)/'],
            ['name' => 'Internet Explorer', 'pattern' => '/Trident.*rv:([0-9\.]+)/'],
            ['name' => 'Chrome',            'pattern' => '/Chrome\/([0-9\.]+)/'],
            ['name' => 'Firefox',           'pattern' => '/Firefox\/([0-9\.]+)/'],
            ['name' => 'Safari',            'pattern' => '/Version\/([0-9\.]+).*Safari/'],
            ['name' => 'Opera',             'pattern' => '/OPR\/([0-9\.]+)/'],
            ['name' => 'Opera',             'pattern' => '/Opera\/([0-9\.]+)/'],
            ['name' => 'Brave',             'pattern' => '/Brave\/([0-9\.]+)/'],
            ['name' => 'Vivaldi',           'pattern' => '/Vivaldi\/([0-9\.]+)/'],
            ['name' => 'UC Browser',        'pattern' => '/UCBrowser\/([0-9\.]+)/'],
            ['name' => 'Samsung Internet',  'pattern' => '/SamsungBrowser\/([0-9\.]+)/'],
            ['name' => 'QQ Browser',        'pattern' => '/QQBrowser\/([0-9\.]+)/'],
            ['name' => 'Baidu',             'pattern' => '/BIDUBrowser\/([0-9\.]+)/'],
            ['name' => 'DuckDuckGo',        'pattern' => '/DuckDuckGo\/([0-9\.]+)/'],
        ];

        foreach($browsers as $browser){
            if(preg_match($browser['pattern'], $ua, $match)){
                return $browser['name'] . ' ' . $match[1];
            }
        }

        // Fallback to generic UA info
        if(preg_match('/[a-z]+\/([0-9\.]+)/i', $ua, $match)){
            return 'Unknown Browser ' . $match[1];
        }

        return 'Unknown Browser';
    }

    // Get Client Device Type
    public function deviceType(): string
    {
        $ua = strtolower($this->userAgent);

        if(strpos($ua, 'mobile') !== false || preg_match('/iphone|ipod|android/i', $ua)){
            return 'Mobile';
        }

        if(preg_match('/ipad|tablet/i', $ua)){
            return 'Tablet';
        }

        if(preg_match('/bot|crawl|slurp|spider/i', $ua)){
            return 'Bot';
        }

        return 'Desktop';
    }

    // Get Client All Info
    public function all(): array
    {
        return [
            'ip'        => $this->ip(),
            'os'        => $this->os(),
            'browser'   => $this->browser(),
            'device'    => $this->deviceType(),
            'language'  => $this->language(),
            'userAgent' => $this->userAgent(),
        ];
    }

    protected function getIp(): string
    {
        foreach([
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ] as $key){
            if(!empty($_SERVER[$key])){
                return explode(',', $_SERVER[$key])[0];
            }
        }

        return 'Unknown';
    }
}