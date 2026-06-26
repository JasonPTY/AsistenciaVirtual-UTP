<?php

require_once __DIR__ . '/../../core/database/Database.php';

class HistoryRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getCursos(string $cedula): array
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT c.id_curso, c.nombre_curso
            FROM cursos c
            INNER JOIN asistencia a ON a.id_curso = c.id_curso
            WHERE a.cedula_profesor = ?
            ORDER BY c.nombre_curso ASC
        ");
        $stmt->bind_param('s', $cedula);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getHistorialClases(
        string  $cedula,
        ?string $curso        = null,
        ?string $fecha_inicio = null,
        ?string $fecha_fin    = null
    ): array {
        $sql = "
            SELECT
                a.id_asistencia,
                c.nombre_curso,
                a.fecha,
                a.hora,
                COUNT(ad.id_asistencia_detalle) AS total_estudiantes,
                SUM(ad.asistencia = 'Presente') AS total_presentes
            FROM asistencia a
            INNER JOIN cursos             c  ON c.id_curso       = a.id_curso
            LEFT  JOIN asistencia_detalle ad ON ad.id_asistencia = a.id_asistencia
            WHERE a.cedula_profesor = ?
        ";

        $params = [$cedula];
        $types  = 's';

        if (!empty($curso)) {
            $sql     .= " AND a.id_curso = ?";
            $params[] = $curso;
            $types   .= 'i';
        }
        if (!empty($fecha_inicio)) {
            $sql     .= " AND a.fecha >= ?";
            $params[] = $fecha_inicio;
            $types   .= 's';
        }
        if (!empty($fecha_fin)) {
            $sql     .= " AND a.fecha <= ?";
            $params[] = $fecha_fin;
            $types   .= 's';
        }

        $sql .= "
            GROUP BY a.id_asistencia, c.nombre_curso, a.fecha, a.hora
            ORDER BY a.fecha DESC, a.hora DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getDetallesAsistencia(int $idAsistencia): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                ad.cedula,
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                ad.asistencia                      AS estado
            FROM asistencia_detalle ad
            INNER JOIN usuarios u ON u.cedula = ad.cedula
            WHERE ad.id_asistencia = ?
            ORDER BY u.apellido ASC, u.nombre ASC
        ");
        $stmt->bind_param('i', $idAsistencia);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function editarAsistencia(
        int    $idAsistencia,
        string $cedula,
        string $nuevoEstado
    ): bool {
        $allowed = ['Presente', 'Ausente', 'Tardanza'];
        if (!in_array($nuevoEstado, $allowed, true)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE asistencia_detalle
            SET    asistencia = ?
            WHERE  id_asistencia = ?
              AND  cedula        = ?
        ");
        $stmt->bind_param('sis', $nuevoEstado, $idAsistencia, $cedula);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function eliminarAsistencia(int $idAsistencia): bool
    {
        $stmt = $this->conn->prepare("
            DELETE FROM asistencia_detalle WHERE id_asistencia = ?
        ");
        $stmt->bind_param('i', $idAsistencia);
        $stmt->execute();

        $stmt = $this->conn->prepare("
            DELETE FROM asistencia WHERE id_asistencia = ?
        ");
        $stmt->bind_param('i', $idAsistencia);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function getExportData(int $idAsistencia): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT a.fecha, a.hora, c.nombre_curso
            FROM   asistencia a
            INNER JOIN cursos c ON c.id_curso = a.id_curso
            WHERE  a.id_asistencia = ?
        ");
        $stmt->bind_param('i', $idAsistencia);
        $stmt->execute();
        $header = $stmt->get_result()->fetch_assoc();

        if (!$header) return null;

        $stmt = $this->conn->prepare("
            SELECT
                ad.cedula,
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                ad.asistencia                      AS estado
            FROM asistencia_detalle ad
            INNER JOIN usuarios u ON u.cedula = ad.cedula
            WHERE ad.id_asistencia = ?
            ORDER BY u.apellido ASC, u.nombre ASC
        ");
        $stmt->bind_param('i', $idAsistencia);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return ['header' => $header, 'rows' => $rows];
    }
}