<?php

require_once __DIR__ . '/../../core/database/Database.php';

class StudentsRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getStudents(
        string $cedulaProfesor,
        int $limit,
        int $offset,
        ?string $cedulaFilter,
        ?string $groupFilter,
        ?string $courseFilter
    ): array {
        $sql = "
            SELECT 
                u.apellido,
                u.nombre, 
                u.correo, 
                e.id_grupo, 
                e.estado_academico,
                GROUP_CONCAT(DISTINCT c.id_curso) AS cursos,
                (
                    SELECT 
                        IFNULL(
                            (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(ad.asistencia)) * 100,
                            0
                        )
                    FROM asistencia_detalle ad
                    WHERE ad.cedula = e.cedula
                ) AS porcentaje_asistencia
            FROM estudiantes e
            JOIN usuarios u ON e.cedula = u.cedula
            LEFT JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
            LEFT JOIN cursos c ON ec.id_curso = c.id_curso
            LEFT JOIN profesor_curso pc ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
        ";

        $params = [$cedulaProfesor];
        $types  = 's';

        if ($cedulaFilter !== null && $cedulaFilter !== '') {
            $sql     .= " AND u.cedula = ?";
            $params[] = $cedulaFilter;
            $types   .= 's';
        }
        if ($groupFilter !== null && $groupFilter !== '') {
            $sql     .= " AND e.id_grupo = ?";
            $params[] = $groupFilter;
            $types   .= 's';
        }
        if ($courseFilter !== null && $courseFilter !== '') {
            $sql     .= " AND c.id_curso = ?";
            $params[] = $courseFilter;
            $types   .= 's';
        }

        $sql     .= " GROUP BY e.cedula ORDER BY u.nombre, u.apellido LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countStudents(
        string $cedulaProfesor,
        ?string $cedulaFilter,
        ?string $groupFilter,
        ?string $courseFilter
    ): int {
        $sql = "
            SELECT COUNT(DISTINCT e.cedula) AS total
            FROM estudiantes e
            JOIN usuarios u ON e.cedula = u.cedula
            LEFT JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
            LEFT JOIN cursos c ON ec.id_curso = c.id_curso
            LEFT JOIN profesor_curso pc ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
        ";

        $params = [$cedulaProfesor];
        $types  = 's';

        if ($cedulaFilter !== null && $cedulaFilter !== '') {
            $sql     .= " AND u.cedula = ?";
            $params[] = $cedulaFilter;
            $types   .= 's';
        }
        if ($groupFilter !== null && $groupFilter !== '') {
            $sql     .= " AND e.id_grupo = ?";
            $params[] = $groupFilter;
            $types   .= 's';
        }
        if ($courseFilter !== null && $courseFilter !== '') {
            $sql     .= " AND c.id_curso = ?";
            $params[] = $courseFilter;
            $types   .= 's';
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getCedulasByProfesor(
        string $cedulaProfesor,
        string $search,
        ?string $groupFilter,
        ?string $courseFilter
    ): array {
        $sql = "
            SELECT DISTINCT e.cedula
            FROM estudiantes e
            JOIN usuarios u ON e.cedula = u.cedula
            JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
            LEFT JOIN cursos c ON ec.id_curso = c.id_curso
            LEFT JOIN profesor_curso pc ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
            AND e.cedula LIKE ?
        ";

        $params = [$cedulaProfesor, '%' . $search . '%'];
        $types  = 'ss';

        if ($groupFilter !== null && $groupFilter !== '') {
            $sql     .= " AND e.id_grupo = ?";
            $params[] = $groupFilter;
            $types   .= 's';
        }
        if ($courseFilter !== null && $courseFilter !== '') {
            $sql     .= " AND c.id_curso = ?";
            $params[] = $courseFilter;
            $types   .= 's';
        }

        $sql .= " ORDER BY e.cedula";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getGroupsByProfesor(string $cedulaProfesor): array
    {
        $sql = "
            SELECT DISTINCT e.id_grupo
            FROM estudiantes e
            JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
            JOIN cursos c ON ec.id_curso = c.id_curso
            JOIN profesor_curso pc ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
            ORDER BY e.id_grupo
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getCoursesByProfesor(string $cedulaProfesor): array
    {
        $sql = "
            SELECT c.id_curso, c.nombre_curso
            FROM cursos c
            JOIN profesor_curso pc ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
            ORDER BY c.nombre_curso
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getClasesByEstudiante(string $cedulaEstudiante): array
{
    $stmt = $this->conn->prepare("
        SELECT
            c.id_curso,
            c.nombre_curso,
            cl.dia_semana,
            cl.hora_clase
        FROM estudiantes_cursos ec
        JOIN cursos c  ON ec.id_curso = c.id_curso
        JOIN clases cl ON cl.id_curso = c.id_curso
        WHERE ec.cedula = ?
        GROUP BY c.id_curso, cl.dia_semana, cl.hora_clase
    ");
    $stmt->bind_param('s', $cedulaEstudiante);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function getNombreByEstudiante(string $cedula): ?array
{
    $stmt = $this->conn->prepare("
        SELECT nombre, apellido FROM usuarios WHERE cedula = ?
    ");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

public function getTotalCursosByEstudiante(string $cedula): int
{
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) AS total_cursos
        FROM estudiantes_cursos ec
        JOIN cursos c ON ec.id_curso = c.id_curso
        WHERE ec.cedula = ?
    ");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()['total_cursos'];
}

public function getAsistenciasPresente(string $cedula): int
{
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) AS asistencias_registradas
        FROM asistencia_detalle
        WHERE cedula = ? AND asistencia = 'Presente'
    ");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()['asistencias_registradas'];
}

public function getResumenCursosByEstudiante(string $cedula): array
{
    $stmt = $this->conn->prepare("
        SELECT
            c.nombre_curso,
            COUNT(CASE WHEN ae.asistencia = 'Presente' THEN 1 END)           AS asistencias,
            COUNT(DISTINCT a.id_asistencia)                                   AS total_clases,
            (COUNT(CASE WHEN ae.asistencia = 'Presente' THEN 1 END)
                / NULLIF(COUNT(DISTINCT a.id_asistencia), 0)) * 100           AS porcentaje_asistencia,
            COUNT(a.id_asistencia)                                            AS cantidad_asistencias
        FROM cursos c
        JOIN estudiantes_cursos ec ON c.id_curso = ec.id_curso
        LEFT JOIN asistencia a ON c.id_curso = a.id_curso
        LEFT JOIN asistencia_detalle ae
            ON a.id_asistencia = ae.id_asistencia AND ae.cedula = ?
        WHERE ec.cedula = ?
        GROUP BY c.id_curso, c.nombre_curso
    ");
    $stmt->bind_param('ss', $cedula, $cedula);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
}