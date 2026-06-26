<?php

require_once __DIR__ . '/../../core/database/Database.php';

class NotificationRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function getTipoUsuario(string $cedula): int
    {
        $stmt = $this->conn->prepare("
            SELECT id_tipoUsuario FROM usuarios WHERE cedula = ?
        ");
        $stmt->bind_param('s', $cedula);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['id_tipoUsuario'];
    }

    public function getCorreoByCedula(string $cedula): string
    {
        $stmt = $this->conn->prepare("
            SELECT correo FROM usuarios WHERE cedula = ?
        ");
        $stmt->bind_param('s', $cedula);
        $stmt->execute();
        return (string) ($stmt->get_result()->fetch_assoc()['correo'] ?? '');
    }

    public function getCedulaByCorreo(string $correo): ?string
    {
        $stmt = $this->conn->prepare("
            SELECT cedula FROM usuarios WHERE correo = ?
        ");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (string) $row['cedula'] : null;
    }

    public function getNotificacionesEstudiante(string $correo): array
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM notificaciones
            WHERE correo_destinatario LIKE ?
            ORDER BY fecha_envio DESC
        ");
        $like = '%' . $correo . '%';
        $stmt->bind_param('s', $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getNotificacionesProfesor(string $cedulaProfesor): array
    {
        $stmt = $this->conn->prepare("
            SELECT n.*, u.correo AS correo_profesor
            FROM notificaciones n
            JOIN usuarios u ON n.cedula_profesor = u.cedula
            WHERE n.cedula_profesor = ?
            ORDER BY n.fecha_envio DESC
        ");
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getEstudiantesByProfesor(string $cedulaProfesor): array
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT u.nombre, u.correo, e.cedula,
                IFNULL(
                    (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(ad.asistencia)) * 100,
                    0
                ) AS porcentaje_asistencia
            FROM estudiantes e
            JOIN usuarios u ON e.cedula = u.cedula
            JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
            LEFT JOIN asistencia_detalle ad ON ad.cedula = e.cedula
            WHERE ec.id_curso IN (
                SELECT id_curso FROM profesor_curso WHERE cedula_profesor = ?
            )
            GROUP BY e.cedula
        ");
        $stmt->bind_param('s', $cedulaProfesor);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function insertNotificacion(
        string $tipo,
        string $asunto,
        string $mensaje,
        int    $urgente,
        string $fechaEnvio,
        string $estado,
        string $correoDestinatario,
        string $cedulaProfesor
    ): int {
        $stmt = $this->conn->prepare("
            INSERT INTO notificaciones
                (tipo, asunto, mensaje, es_urgente, fecha_envio, estado, correo_destinatario, cedula_profesor)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sssissss', $tipo, $asunto, $mensaje, $urgente, $fechaEnvio, $estado, $correoDestinatario, $cedulaProfesor);
        $stmt->execute();
        return (int) $stmt->insert_id;
    }

    public function insertNotificacionUsuario(
        int    $idNotificacion,
        string $cedulaProfesor,
        string $cedulaEstudiante,
        string $fechaRecibido
    ): bool {
        $stmt = $this->conn->prepare("
            INSERT INTO notificaciones_usuarios
                (id_notificacion, cedula_profesor, cedula_estudiante, fecha_recibido)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('isss', $idNotificacion, $cedulaProfesor, $cedulaEstudiante, $fechaRecibido);
        return $stmt->execute();
    }

    public function countNotificacionesEstudiante(string $correo): int
{
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) AS total
        FROM notificaciones
        WHERE correo_destinatario LIKE ?
    ");
    $like = '%' . $correo . '%';
    $stmt->bind_param('s', $like);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()['total'];
}
}