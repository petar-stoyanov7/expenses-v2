<?php

namespace App\Helpers;

use DateInterval;
use DateTime;
use Exception;

class DateHelper
{
    public static function isValidDate(string $date): bool
    {
        try {
            $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        } catch (Exception $e) {
            return false;
        }

        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }

    public static function isPastDate(string $date): ?bool
    {
        try {
            $dateTime = new DateTime($date);
        } catch (Exception $e) {
            return false;
        }

        $now = new DateTime();

        return $dateTime < $now;
    }

    public static function isValidInterval(string $interval) : bool
    {
        return
            preg_match("/^\d+ day$/", $interval) ||
            preg_match("/^\d+ month$/", $interval) ||
            preg_match("/^\d+ year$/", $interval);
    }

    public static function createDateFromInterval(string $interval, bool $formatted = true)
    {
        $DateTime = new DateTime();
        $DateTime->add(DateInterval::createFromDateString($interval));

        if (!$formatted) {
            return $DateTime;
        }

        return $DateTime->format('Y-m-d');
    }
}