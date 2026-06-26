<?php

class AttendanceHelper
{
    public static function calculatePercentage(
        int $presentes,
        int $total
    ): float
    {
        if ($total <= 0) {
            return 0;
        }

        return round(
            ($presentes / $total) * 100,
            1
        );
    }
}