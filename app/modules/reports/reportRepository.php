<?php

require_once __DIR__ . '/../../core/database/Database.php';

class ReportRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }
    public function getCoursesByProfessor(string $cedulaProfesor): array
{
    $sql = "
        SELECT c.id_curso, c.nombre_curso
        FROM cursos c
        JOIN profesor_curso pc ON c.id_curso = pc.id_curso
        WHERE pc.cedula_profesor = ?
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $cedulaProfesor);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
public function getStudentsAtRisk(string $cedulaProfesor): array
{
    $sql = "
        SELECT
            u.nombre,
            u.apellido,
            SUM(
                CASE
                    WHEN ae.asistencia = 'Presente'
                    THEN 1
                    ELSE 0
                END
            ) AS asistencias,
            COUNT(a.id_asistencia) AS total_clases,
            (
                SUM(
                    CASE
                        WHEN ae.asistencia = 'Presente'
                        THEN 1
                        ELSE 0
                    END
                ) / COUNT(a.id_asistencia)
            ) * 100 AS porcentaje_asistencia
        FROM usuarios u
        JOIN asistencia_detalle ae
            ON u.cedula = ae.cedula
        JOIN asistencia a
            ON ae.id_asistencia = a.id_asistencia
        JOIN cursos c
            ON a.id_curso = c.id_curso
        JOIN profesor_curso pc
            ON c.id_curso = pc.id_curso
        WHERE pc.cedula_profesor = ?
        GROUP BY
            u.cedula,
            u.nombre,
            u.apellido
        HAVING porcentaje_asistencia < 50
        ORDER BY porcentaje_asistencia ASC
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $cedulaProfesor);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
public function getExcellentStudents(string $cedulaProfesor): array
{
    $sql = "
        SELECT
            u.nombre,
            u.apellido,
            SUM(
                CASE
                    WHEN ae.asistencia = 'Presente'
                    THEN 1
                    ELSE 0
                END
            ) AS asistencias,
            COUNT(a.id_asistencia) AS total_clases,
            (
                SUM(
                    CASE
                        WHEN ae.asistencia = 'Presente'
                        THEN 1
                        ELSE 0
                    END
                ) / COUNT(a.id_asistencia)
            ) * 100 AS porcentaje_asistencia
        FROM usuarios u
        JOIN asistencia_detalle ae
            ON u.cedula = ae.cedula
        JOIN asistencia a
            ON ae.id_asistencia = a.id_asistencia
        JOIN cursos c
            ON a.id_curso = c.id_curso
        JOIN profesor_curso pc
            ON c.id_curso = pc.id_curso
        WHERE pc.cedula_profesor = ?
        GROUP BY
            u.cedula,
            u.nombre,
            u.apellido
        HAVING porcentaje_asistencia > 80
        ORDER BY porcentaje_asistencia DESC
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $cedulaProfesor);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function getRegularStudents(
    string $cedulaProfesor,
    int $umbralBajo,
    int $umbralAlto
): array
{
    $sql = "
        SELECT
            u.nombre,
            u.apellido,
            SUM(
                CASE
                    WHEN ae.asistencia = 'Presente'
                    THEN 1
                    ELSE 0
                END
            ) AS asistencias,
            COUNT(a.id_asistencia) AS total_clases,
            (
                SUM(
                    CASE
                        WHEN ae.asistencia = 'Presente'
                        THEN 1
                        ELSE 0
                    END
                ) / COUNT(a.id_asistencia)
            ) * 100 AS porcentaje_asistencia
        FROM usuarios u
        JOIN asistencia_detalle ae
            ON u.cedula = ae.cedula
        JOIN asistencia a
            ON ae.id_asistencia = a.id_asistencia
        JOIN cursos c
            ON a.id_curso = c.id_curso
        JOIN profesor_curso pc
            ON c.id_curso = pc.id_curso
        WHERE pc.cedula_profesor = ?
        GROUP BY
            u.cedula,
            u.nombre,
            u.apellido
        HAVING porcentaje_asistencia BETWEEN ? AND ?
        ORDER BY porcentaje_asistencia DESC
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param(
        "sii",
        $cedulaProfesor,
        $umbralBajo,
        $umbralAlto
    );

    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function getTotalClasses(
    string $cedulaProfesor
): int
{
    $sql = "
        SELECT COUNT(DISTINCT a.id_asistencia) AS total_clases
        FROM asistencia a
        JOIN profesor_curso pc
            ON a.id_curso = pc.id_curso
        WHERE pc.cedula_profesor = ?
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $cedulaProfesor);
    $stmt->execute();

    $result = $stmt->get_result();

    return (int) $result->fetch_assoc()['total_clases'];
}

public function getAverageAttendance(
    string $cedulaProfesor
): array
{
    $sql = "
        SELECT
            ROUND(
                AVG(porcentaje_asistencia),
                2
            ) AS promedio_asistencia
        FROM (
            SELECT
                (
                    SUM(
                        CASE
                            WHEN ad.asistencia = 'Presente'
                            THEN 1
                            ELSE 0
                        END
                    ) / COUNT(*)
                ) * 100 AS porcentaje_asistencia
            FROM asistencia a
            JOIN asistencia_detalle ad
                ON a.id_asistencia = ad.id_asistencia
            JOIN profesor_curso pc
                ON a.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
            GROUP BY a.id_curso
        ) AS subquery
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $cedulaProfesor);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

public function getCourseDistribution(
    string $cedulaProfesor
): array
{
    $sql = "
        SELECT
            subquery.nombre_curso,
            ROUND(
                AVG(subquery.porcentaje_asistencia),
                2
            ) AS porcentaje_asistencia
        FROM (
            SELECT
                c.nombre_curso,
                (
                    SUM(
                        CASE
                            WHEN ad.asistencia = 'Presente'
                            THEN 1
                            ELSE 0
                        END
                    ) / COUNT(*)
                ) * 100 AS porcentaje_asistencia
            FROM cursos c
            JOIN asistencia a
                ON c.id_curso = a.id_curso
            JOIN asistencia_detalle ad
                ON a.id_asistencia = ad.id_asistencia
            JOIN profesor_curso pc
                ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
            GROUP BY
                c.id_curso,
                c.nombre_curso
        ) AS subquery
        GROUP BY
            subquery.nombre_curso
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $cedulaProfesor);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

}