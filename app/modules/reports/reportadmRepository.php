<?php

require_once __DIR__ . '/../../core/database/Database.php';

class ReportAdminRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getAllCourses(): array
    {
        $stmt = $this->conn->prepare("
            SELECT id_curso, nombre_curso FROM cursos
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getStudentsAtRisk(string $cedulaProfesor): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                u.nombre,
                u.apellido,
                SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) AS asistencias,
                COUNT(a.id_asistencia) AS total_clases,
                (SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(a.id_asistencia)) * 100 AS porcentaje_asistencia
            FROM usuarios u
            JOIN asistencia_detalle ae ON u.cedula = ae.cedula
            JOIN asistencia a ON ae.id_asistencia = a.id_asistencia
            JOIN cursos c ON a.id_curso = c.id_curso
            JOIN profesor_curso pc ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
            GROUP BY u.cedula, u.nombre, u.apellido
            HAVING porcentaje_asistencia < 50
            ORDER BY porcentaje_asistencia ASC
        ");
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getStudentsExcellent(): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                u.nombre,
                u.apellido,
                SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) AS asistencias,
                COUNT(a.id_asistencia) AS total_clases,
                (SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(a.id_asistencia)) * 100 AS porcentaje_asistencia
            FROM usuarios u
            JOIN asistencia_detalle ae ON u.cedula = ae.cedula
            JOIN asistencia a ON ae.id_asistencia = a.id_asistencia
            JOIN cursos c ON a.id_curso = c.id_curso
            GROUP BY u.cedula, u.nombre, u.apellido
            HAVING porcentaje_asistencia > 80
            ORDER BY porcentaje_asistencia DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getStudentsRegular(int $umbralBajo, int $umbralAlto): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                u.nombre,
                u.apellido,
                SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) AS asistencias,
                COUNT(a.id_asistencia) AS total_clases,
                (SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(a.id_asistencia)) * 100 AS porcentaje_asistencia
            FROM usuarios u
            JOIN asistencia_detalle ae ON u.cedula = ae.cedula
            JOIN asistencia a ON ae.id_asistencia = a.id_asistencia
            JOIN cursos c ON a.id_curso = c.id_curso
            GROUP BY u.cedula, u.nombre, u.apellido
            HAVING porcentaje_asistencia BETWEEN ? AND ?
            ORDER BY porcentaje_asistencia DESC
        ");
        $stmt->bind_param('ii', $umbralBajo, $umbralAlto);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalClases(): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT a.id_asistencia) AS total_clases
            FROM asistencia a
            JOIN cursos c ON a.id_curso = c.id_curso
        ");
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total_clases'];
    }

    public function getAttendanceDistributionByCourse(): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                subquery.nombre_curso,
                ROUND(AVG(subquery.porcentaje_asistencia), 2) AS porcentaje_asistencia
            FROM (
                SELECT 
                    c.nombre_curso,
                    (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS porcentaje_asistencia
                FROM cursos c
                JOIN asistencia a ON c.id_curso = a.id_curso
                JOIN asistencia_detalle ad ON a.id_asistencia = ad.id_asistencia
                GROUP BY c.id_curso, c.nombre_curso
            ) AS subquery
            GROUP BY subquery.nombre_curso
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAverageAttendance(array $distributionData): float
    {
        if (empty($distributionData)) {
            return 0.0;
        }
        $total = array_sum(array_column($distributionData, 'porcentaje_asistencia'));
        return round($total / count($distributionData), 2);
    }
}