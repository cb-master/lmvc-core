<?php
/**
 * Laika PHP MVC Framework
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com
 * License: MIT
 */

declare(strict_types=1);

namespace CBM\Core;

use InvalidArgumentException;

defined('BASE_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

class Config
{
    /**
     * @var string $storagePath
     */
    private static string $storagePath = BASE_PATH . '/config';

    /**
     * Ensure the storage directory exists.
     */
    private static function ensurePath(): void
    {
        if (!is_dir(self::$storagePath)) {
            mkdir(self::$storagePath, 0775, true);
        }
    }

    /**
     * @param string $name Required Argument.
     * @param array $data Required Argument
     * @return bool
     */
    public static function set(string $name, array $data): bool
    {
        self::ensurePath();
        $file = self::$storagePath . '/' . $name . '.php';
        $content = "<?php
    /**
     * Laika PHP MVC Framework
     * Author: Showket Ahmed
     * Email: riyadhtayf@gmail.com
     * License: MIT
     * This file is part of the Laika PHP MVC Framework.
     * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
     */

    declare(strict_types=1);\n\nreturn [\n\n";

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $content .= "\t'{$key}'\t=>\t" . ($value ? 'true' : 'false') . ",\n\n";
            } elseif (is_null($value)) {
                $content .= "\t'{$key}'\t=>\tnull,\n\n";
            } elseif (is_numeric($value)) {
                $content .= "\t'{$key}'\t=>\t{$value},\n\n";
            } elseif (is_string($value)) {
                $escaped = str_replace("'", "\\'", $value); // escape single quotes
                $content .= "\t'{$key}'\t=>\t'{$escaped}',\n\n";
            } else {
                throw new InvalidArgumentException("Acceptable Array Values Are 'null|int|string|bool'");
            }
        }

        $content .= "];";

        // Atomic write to prevent corruption
        $tmpFile = $file . '.tmp';
        $written = file_put_contents($tmpFile, $content);
        if ($written !== false) {
            rename($tmpFile, $file);
            return true;
        }
        return false;
    }

    /**
     * @param string $name Required Argument.
     * @param ?string $key Optional Argument
     * @return mixed
     */
    public static function get(string $name, ?string $key = null): mixed
    {
        $file = self::$storagePath . "/{$name}.php";
        if (!is_file($file)) {
            return null;
        }
        $arr = require $file;
        return $key ? ($arr[$key] ?? null) : $arr;
    }

    /**
     * @param string $name Required Argument.
     * @param string $key Required Argument
     * @param null|int|string|bool $value Required Argument
     * @return bool
     */
    public static function updateKey(string $name, string $key, null|int|string|bool $value): bool
    {
        $data = self::get($name);
        $data[$key] = $value;
        return self::set($name, $data);
    }

    /**
     * @param string $name Required Argument.
     * @param string $key Optional Argument
     * @return bool
     */
    public static function removeKey(string $name, string $key): bool
    {
        $data = self::get($name);
        if(array_key_exists($key, $data)) unset($data[$key]);
        return self::set($name, $data);
    }

    /**
     * @param string $name Required Argument.
     * @return bool
     */
    public static function delete(string $name): bool
    {
        $file = self::$storagePath . "/{$name}.php";
        return is_file($file) ? unlink($file) : false;
    }

    /**
     * @param string $name Required Argument.
     * @return bool
     */
    public static function has(string $name): bool
    {
        return is_file(self::$storagePath . "/{$name}.php");
    }
}