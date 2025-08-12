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

use DateTimeZone;
use DateInterval;
use DateTime;

class Date
{
    // DateTime Object
    protected DateTime $dateTime;

    // Date Format
    protected string $format;

    // Timezone
    protected string $timezone;

    // Initiate Date Class
    /**
     * @param string $time Optional Argument. Default is 'now'.
     * @param string $format Optional Argument. Default is 'Y-m-d H:i:s'.
     * @param ?string $timezone Optional Argument. Default is null.
     */
    public function __construct(string $time = 'now', string $format = 'Y-m-d H:i:s', ?string $timezone = null)
    {
        $this->timezone = $timezone ?: 'UTC';
        $this->format = $format;
        $this->dateTime = new DateTime($time, new DateTimeZone($this->timezone));
    }

    // This Time
    /**
     * @param string $format Optional Argument. Default is 'Y-m-d H:i:s'.
     * @param ?string $timezone Optional Argument. Default is null.
     * @return object
     */
    public static function now(string $format = 'Y-m-d H:i:s', ?string $timezone = null): static
    {
        return new static('now', $format, $timezone);
    }

    // Get Formated DateTime
    /**
     * @param string Optional Argument. Default is null
     * @return string
     */
    public function format(?string $format = null): string
    {
        return $this->dateTime->format($format ?: $this->format);
    }

    // Modify DateTime.
    /**
     * @param $modifier Required Argument. Example: '+1 day'
     * @return object
     */
    public function modify(string $modifier): static
    {
        $this->dateTime->modify($modifier);
        return $this;
    }

    // Set DateTime Format.
    /**
     * @param $format Required Argument. Example: 'Y-m-d H:i:s'
     * @return object
     */
    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    public function setTimestamp(int $timestamp): static
    {
        $this->dateTime->setTimestamp($timestamp);
        return $this;
    }

    public function setTimezone(string $timezone): static
    {
        $this->timezone = $timezone;
        $this->dateTime->setTimezone(new DateTimeZone($this->timezone));
        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function diff(Date $other): DateInterval
    {
        return $this->dateTime->diff($other->dateTime);
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function toUtc(): static
    {
        return $this->setTimezone('UTC');
    }

    public function toLocal(string $timezone): static
    {
        return $this->setTimezone($timezone);
    }

    public function toArray(): array
    {
        return [
            'year'      =>  (int)$this->dateTime->format('Y'),
            'month'     =>  (int)$this->dateTime->format('m'),
            'day'       =>  (int)$this->dateTime->format('d'),
            'hour'      =>  (int)$this->dateTime->format('H'),
            'minute'    =>  (int)$this->dateTime->format('i'),
            'second'    =>  (int)$this->dateTime->format('s'),
            'timezone'  =>  $this->timezone,
            'timestamp' =>  $this->getTimestamp()
        ];
    }

    public function humanDiff(?Date $other = null): string
    {
        $other = $other ?: Date::now($this->format, $this->timezone);
        $diff = $this->diff($other);

        $units = [
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($units as $key => $text) {
            $value = (int)$diff->$key;
            if ($value > 0) {
                $plural = $value > 1 ? 's' : '';
                return $diff->invert
                    ? "in {$value} {$text}{$plural}"
                    : "{$value} {$text}{$plural} ago";
            }
        }

        return 'just now';
    }

    public function humanDiffShort(?Date $other = null): string
    {
        $other = $other ?: Date::now($this->format, $this->timezone);
        $diff = $this->diff($other);

        $units = [
            'y' => 'y',
            'm' => 'mo',
            'd' => 'd',
            'h' => 'h',
            'i' => 'm',
            's' => 's',
        ];

        foreach ($units as $key => $abbr) {
            $value = (int)$diff->$key;
            if ($value > 0) {
                return $diff->invert
                    ? "in {$value}{$abbr}"
                    : "{$value}{$abbr} ago";
            }
        }

        return 'now';
    }

    public static function fromFormat(
        string $format,
        string $time,
        string $outputFormat = 'Y-m-d H:i:s',
        string $timezone = 'UTC'
    ): static
    {
        $tz = new DateTimeZone($timezone);
        $dt = DateTime::createFromFormat($format, $time, $tz);
        $instance = new static('now', $outputFormat, $timezone);
        $instance->dateTime = $dt instanceof DateTime ? $dt : new DateTime('now', $tz);
        return $instance;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}