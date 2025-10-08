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
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\Http\Response;
use ErrorException;
use PDOException;
use Throwable;

class ErrorHandler
{
    // Debug Mode
    protected static bool $debug = false;

    // Error Exceptions
    protected static array $exceptions = [];

    // Has Output
    protected static bool $hasOutput = false;

    // Register Error Handlers
    /**
     * @param bool $debug Whether to enable debug mode.
     */
    public static function register():void
    {
        self::$debug = (bool) Config::get('env', 'debug', true);

        if (self::$debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    // Handle Errors
    /**
     * @param int $severity The severity of the error.
     * @param string $message The error message.
     * @param string $file The file in which the error occurred.
     * @param int $line The line number on which the error occurred.
     */
    public static function handleError($severity, $message, $file, $line):void
    {
        self::handleException(new ErrorException($message, 0, $severity, $file, $line));
    }

    // Handle Exceptions
    /**
     * @param \Throwable $exception The exception to handle.
     */
    public static function handleException(Throwable $exception):void
    {
        if (self::$hasOutput) {
            Response::code(500);
            // Already output error, just exit
            return;
        }
        self::$hasOutput = true;

        self::$exceptions[] = $exception;
        self::logError($exception);

        if (self::$debug) {
            self::errorHtml();
        } else {
            self::internalErrorHtml();
        }

        return;
    }

    // Handle Shutdown
    public static function handleShutdown():void
    {
        if (self::$hasOutput) {
            Response::code(500);
            // Already output error, just exit
            return;
        }

        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::$exceptions[] = new ErrorException(
                $error['message'], 0, $error['type'], $error['file'], $error['line']
            );
        }

        if (!empty(self::$exceptions)) {
            // Response::code(500);
            if (self::$debug) {
                self::errorHtml();
            } else {
                self::internalErrorHtml();
            }
            return;
        }
    }

    // Log Errors in Log File
    protected static function logError(Throwable $exception): void
    {
        $logDir = APP_PATH . '/lf-logs';
        // Create Directory If Not Exists
        Directory::make($logDir);

        $logFile = $logDir . '/error-' . time() . '.log';

        $log = sprintf(
            "[%s] %s: %s in %s on line %d\nTrace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        file_put_contents($logFile, $log, FILE_APPEND);
    }

    // Error Message HTML
    protected static function errorHtml()
    {
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Application Errors</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    body{font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; color: #333;}
                    h1{color: #d9534f; font-size: 1.8em;}
                    .error-block {margin-bottom: 30px; border: 1px solid #ccc; background: #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1);}
                    table{width: 100%; border-collapse: collapse;}
                    th,td{padding: 10px 15px; border: 1px solid #ddd; text-align: left; vertical-align: top;}
                    th{background: #f2f2f2; width: 160px;}
                    pre{white-space: pre-wrap; margin: 0;}
                    .table-container{overflow-x: auto;}
                </style>
            </head>
            <body>
                <h1>LAIKA APPLICATION ERROR!</h1>\n
        HTML;

        foreach(self::$exceptions as $ex){
            $type = get_class($ex);
            $message = htmlspecialchars($ex->getMessage());
            $file = htmlspecialchars($ex->getFile());
            $line = $ex->getLine();
            $code = $ex->getCode();
            $trace = htmlspecialchars($ex->getTraceAsString());

            $extra = '';

            // Special handling for PDOException
            if($ex instanceof PDOException){
                $extra .= "<tr><th>SQLSTATE</th><td>{$ex->getCode()}</td></tr>";

                if(isset($ex->errorInfo) && is_array($ex->errorInfo)){
                    $str = implode(' >> ', $ex->errorInfo);
                    $extra .= "<tr><th>Driver Error Info</th><td><pre>" . htmlspecialchars($str) . "</pre></td></tr>";
                }
            }

            echo <<<HTML
                <div class="error-block">
                    <div class="table-container">
                        <table>
                            <tr><th>Type</th><td>{$type}</td></tr>
                            <tr><th>Message</th><td>{$message}</td></tr>
                            <tr><th>File</th><td>{$file}</td></tr>
                            <tr><th>Line</th><td>{$line}</td></tr>
                            {$extra}
                            <tr><th>Code</th><td>{$code}</td></tr>
                            <tr><th>Trace</th><td><pre>{$trace}</pre></td></tr>
                        </table>
                    </div>
                </div>
            </body>
        </html>
        HTML;
        }
    }

    // 500 Internal Error HTML
    protected static function internalErrorHtml()
    {
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Application Errors</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    *{margin:0;padding:0;box-sizing:border-box;}body{font-family: Arial, sans-serif; background: #f9f9f9; color: #333;width:100%;height:100%}
                    h1{color:#af3733a3;font-size:3em;display:flex;width:100%;height:100dvh;place-items:center;justify-content: center;position: absolute;background: white;}
                </style>
            </head>
            <body>
                <h1>500 Internal Server Error!</h1>
            </body>
        </html>
        HTML;
    }
}