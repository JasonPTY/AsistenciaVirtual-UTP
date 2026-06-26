<?php

require_once __DIR__ . '/../../core/database/Database.php';

class DashboardRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getTotalStudents(string $cedulaProfesor): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT e.cedula) AS total
            FROM estudiantes e
            JOIN usuarios u             ON e.cedula    = u.cedula
            LEFT JOIN estudiantes_cursos ec ON e.cedula    = ec.cedula
            LEFT JOIN cursos c          ON ec.id_curso  = c.id_curso
            LEFT JOIN profesor_curso pc ON c.id_curso   = pc.id_curso
            WHERE pc.cedula_profesor = ?
        ");
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getTotalCourses(string $cedulaProfesor): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT c.id_curso) AS total
            FROM cursos c
            LEFT JOIN profesor_curso pc ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
        ");
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getTotalClasses(string $cedulaProfesor): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT ad.id_asistencia) AS total_clases
            FROM asistencia_detalle ad
            JOIN asistencia      a  ON ad.id_asistencia = a.id_asistencia
            JOIN cursos          c  ON a.id_curso       = c.id_curso
            JOIN profesor_curso  pc ON c.id_curso       = pc.id_curso
            WHERE pc.cedula_profesor = ?
        ");
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total_clases'];
    }

    public function getAverageAttendance(string $cedulaProfesor): float
    {
        $stmt = $this->conn->prepare("
            SELECT ROUND(AVG(porcentaje_asistencia), 2) AS promedio_asistencia
            FROM (
                SELECT
                    (
                        SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END)
                        / COUNT(*)
                    ) * 100 AS porcentaje_asistencia
                FROM asistencia a
                JOIN asistencia_detalle ad ON a.id_asistencia = ad.id_asistencia
                JOIN profesor_curso     pc ON a.id_curso      = pc.id_curso
                WHERE pc.cedula_profesor = ?
                GROUP BY a.id_curso
            ) AS subquery
        ");
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return (float) ($stmt->get_result()->fetch_assoc()['promedio_asistencia'] ?? 0);
    }

    public function getTotalEstudiantes(): int
{
    $stmt = $this->conn->prepare("
        SELECT COUNT(DISTINCT e.cedula) AS total
        FROM estudiantes e
        JOIN usuarios u ON e.cedula = u.cedula
        LEFT JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
        LEFT JOIN cursos c ON ec.id_curso = c.id_curso
    ");
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()['total'];
}

public function getTotalCursos(): int
{
    $stmt = $this->conn->prepare("
        SELECT COUNT(DISTINCT id_curso) AS total FROM cursos
    ");
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()['total'];
}

public function getTotalClasesDictadas(): int
{
    $stmt = $this->conn->prepare("
        SELECT COUNT(DISTINCT ad.id_asistencia) AS total_clases
        FROM asistencia_detalle ad
        JOIN asistencia a ON ad.id_asistencia = a.id_asistencia
        JOIN cursos c ON a.id_curso = c.id_curso
    ");
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()['total_clases'];
}

public function getPromedioAsistenciaGeneral(): float
{
    $stmt = $this->conn->prepare("
        SELECT ROUND(AVG(porcentaje_asistencia), 2) AS promedio_asistencia
        FROM (
            SELECT
                (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS porcentaje_asistencia
            FROM asistencia a
            JOIN asistencia_detalle ad ON a.id_asistencia = ad.id_asistencia
            GROUP BY a.id_curso
        ) AS subquery
    ");
    $stmt->execute();
    return (float) ($stmt->get_result()->fetch_assoc()['promedio_asistencia'] ?? 0);
}
}