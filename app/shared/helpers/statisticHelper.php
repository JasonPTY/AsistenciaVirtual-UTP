<?php

class StatisticsHelper
{
    public static function calculateAverageAttendance(
        array $courses
    ): float
    {
        $totalPorcentaje = 0;
        $totalCursos = count($courses);

        if ($totalCursos === 0) {
            return 0;
        }

        foreach ($courses as $course) {
            $totalPorcentaje += $course['porcentaje_asistencia'];
        }

        return round(
            $totalPorcentaje / $totalCursos,
            2
        );
    }
}