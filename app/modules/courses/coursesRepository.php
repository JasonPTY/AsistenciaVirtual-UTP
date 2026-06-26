<?php

require_once __DIR__ . '/../../core/database/Database.php';

class CourseRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getTotalCourses(string $busqueda = ''): int
    {
        if ($busqueda !== '') {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS total
                FROM cursos
                WHERE nombre_curso LIKE ? OR id_curso LIKE ?
            ");
            $like = "%{$busqueda}%";
            $stmt->bind_param('ss', $like, $like);
        } else {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS total FROM cursos
            ");
        }

        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getCourses(
        string $busqueda = '',
        int    $limit    = 20,
        int    $offset   = 0
    ): array {
        if ($busqueda !== '') {
            $stmt = $this->conn->prepare("
                SELECT id_curso, nombre_curso, id_grupo
                FROM cursos
                WHERE nombre_curso LIKE ? OR id_curso LIKE ?
                ORDER BY nombre_curso
                LIMIT ? OFFSET ?
            ");
            $like = "%{$busqueda}%";
            $stmt->bind_param('ssii', $like, $like, $limit, $offset);
        } else {
            $stmt = $this->conn->prepare("
                SELECT id_curso, nombre_curso, id_grupo
                FROM cursos
                ORDER BY nombre_curso
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param('ii', $limit, $offset);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createCourse(string $nombreCurso, ?string $idGrupo): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO cursos (nombre_curso, id_grupo) VALUES (?, ?)
        ");
        $stmt->bind_param('ss', $nombreCurso, $idGrupo);
        return $stmt->execute();
    }

    public function updateCourse(
        int     $idCurso,
        string  $nombreCurso,
        ?string $idGrupo
    ): bool {
        $stmt = $this->conn->prepare("
            UPDATE cursos
            SET nombre_curso = ?, id_grupo = ?
            WHERE id_curso = ?
        ");
        $stmt->bind_param('ssi', $nombreCurso, $idGrupo, $idCurso);
        return $stmt->execute();
    }

    public function deleteCourse(int $idCurso): bool
    {
        $stmt = $this->conn->prepare("
            DELETE FROM cursos WHERE id_curso = ?
        ");
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}