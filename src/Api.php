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

namespace CBM\Core;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

class Api
{
    /**
     * @property int $code Response Code
     */
    private int $code;

    /**
     * @property string $message Response Message
     */
    private string $message;

    /**
     * @property bool $status Response Status
     */
    private bool $status;

    /**
     * @property array $data Response Data as JSON
     */
    private array $data;

    public function __construct()
    {
        $this->code     =   200;
        $this->message  =   $this->defaultMessage($this->code);
        $this->status   =   $this->isSuccess($this->code);
        $this->data     =   [];
    }

    /**
     * @param int $code Response Code
     * @return void
     */
    public function code(int $code): void
    {
        $this->code = $code;
        return;
    }

    /**
     * @param string $message Response Message
     * @return void
     */
    public function message(string $message): void
    {
        $this->message = $message;
        return;
    }

    /**
     * @param bool $status Response Status
     * @return void
     */
    public function status(bool $status): void
    {
        $this->status = $status;
        return;
    }

    /**
     * @param array $data Response Data
     * @return void
     */
    public function data(array $data): void
    {
        $this->data = $data;
        return;
    }


    /**
     * Check Response is Success or Failed
     * @param int $code Response Code
     * @return bool
     */
    public function isSuccess(int $code): bool
    {
        return in_array($code, [200,201,202,203,204,205,206]);
    }

    /**
     * Get Default Response Message
     * @param int $code Response Code
     * @return string
     */
    public function defaultMessage(int $code): string
    {
        switch($code){
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
            default:
                $text = "Unknown Response Code: '{$code}'";
            break;
        }
        return $text;
    }

    /**
     * Get JSON Data
     * @return string JSON Data
     */
    public function json(): string
    {
        // Set Application/Json Header
        Http\Response::setHeader(['Content-Type'=>'Application/Json']);
        // Return Result
        return json_encode([
            'code'      =>  $this->code,
            'status'    =>  $this->status,
            'message'   =>  $this->message,
            'data'      =>  $this->data
        ], JSON_PRETTY_PRINT|JSON_FORCE_OBJECT);
    }
}