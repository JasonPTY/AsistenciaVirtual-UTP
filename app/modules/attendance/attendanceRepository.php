<?php

require_once __DIR__ . '/../../core/database/Database.php';

class AttendanceRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getStudentsByCourse(int $idCurso, string $cedulaProfesor): array
    {
        $sql = "
            SELECT e.cedula, u.nombre, u.apellido
            FROM estudiantes_cursos ec
            JOIN estudiantes e ON ec.cedula = e.cedula
            JOIN usuarios u ON e.cedula = u.cedula
                        JOIN profesor_curso pc ON pc.id_curso = ec.id_curso
            WHERE ec.id_curso = ?
                            AND pc.cedula_profesor = ?
            ORDER BY u.apellido, u.nombre
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $idCurso, $cedulaProfesor);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createAttendance(
        int $idCurso,
        string $fecha,
        string $hora,
        string $cedulaProfesor
    ): int|false
    {
        $access = $this->conn->prepare("SELECT 1 FROM profesor_curso WHERE id_curso = ? AND cedula_profesor = ?");
        $access->bind_param("is", $idCurso, $cedulaProfesor);
        $access->execute();
        if ($access->get_result()->num_rows === 0) {
            return false;
        }

        $sql = "
            INSERT INTO asistencia (id_curso, fecha, hora, cedula_profesor)
            VALUES (?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $idCurso, $fecha, $hora, $cedulaProfesor);

        if (!$stmt->execute()) {
            return false;
        }

        return $this->conn->insert_id;
    }

    public function getAttendanceById(int $idAsistencia, string $cedulaProfesor): array|null
    {
        $sql = "
            SELECT a.id_asistencia, a.fecha, a.hora, c.nombre_curso
            FROM asistencia a
            JOIN cursos c ON a.id_curso = c.id_curso
            WHERE a.id_asistencia = ? AND a.cedula_profesor = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $idAsistencia, $cedulaProfesor);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function saveAttendanceDetail(
        int $idAsistencia,
        string $cedula,
        string $estado,
        string $cedulaProfesor
    ): bool
    {
        $sql = "
            SELECT 1
            FROM asistencia a
            JOIN profesor_curso pc ON pc.id_curso = a.id_curso
            JOIN estudiantes_cursos ec ON ec.id_curso = a.id_curso AND ec.cedula = ?
            WHERE a.id_asistencia = ? AND a.cedula_profesor = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $cedula, $idAsistencia, $cedulaProfesor);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            return false;
        }

        $existing = $this->conn->prepare("SELECT 1 FROM asistencia_detalle WHERE id_asistencia = ? AND cedula = ?");
        $existing->bind_param("is", $idAsistencia, $cedula);
        $existing->execute();
        if ($existing->get_result()->num_rows > 0) {
            $update = $this->conn->prepare("UPDATE asistencia_detalle SET asistencia = ? WHERE id_asistencia = ? AND cedula = ?");
            $update->bind_param("sis", $estado, $idAsistencia, $cedula);
            return $update->execute();
        }

        $sql = "
            INSERT INTO asistencia_detalle (id_asistencia, cedula, asistencia)
            VALUES (?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $idAsistencia, $cedula, $estado);

        return $stmt->execute();
    }

    public function getCoursesByProfessor(string $cedulaProfesor): array
    {
        $sql = "
            SELECT DISTINCT c.id_curso, c.nombre_curso
            FROM cursos c
            JOIN profesor_curso pc ON c.id_curso = pc.id_curso
            WHERE pc.cedula_profesor = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cedulaProfesor);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalCourses(): int
    {
        $sql = "SELECT COUNT(*) AS total_cursos FROM cursos";

        return (int) $this->conn
            ->query($sql)
            ->fetch_assoc()['total_cursos'];
    }

    public function getTotalClasses(): int
    {
        $sql = "SELECT COUNT(*) AS total_clases FROM asistencia";

        return (int) $this->conn
            ->query($sql)
            ->fetch_assoc()['total_clases'];
    }
}