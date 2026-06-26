<?php

require_once __DIR__ . '/../../core/database/Database.php';

class UserRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // ─── Gestión de usuarios (gestUsuarios.php) ──────────────────────────────

    public function getTotalUsers(): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total FROM usuarios
        ");
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getUsers(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->conn->prepare("
            SELECT cedula, nombre, apellido, correo, id_tipoUsuario
            FROM usuarios
            ORDER BY nombre
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createUser(
        string $cedula,
        string $nombre,
        string $apellido,
        string $correo,
        int    $tipoUsuario,
        string $pass
    ): bool {
        $passHash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            INSERT INTO usuarios (cedula, nombre, apellido, correo, id_tipoUsuario, pass)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('ssssis', $cedula, $nombre, $apellido, $correo, $tipoUsuario, $passHash);
        return $stmt->execute();
    }

    public function updateUser(
        string  $cedula,
        string  $nombre,
        string  $apellido,
        string  $correo,
        int     $tipoUsuario,
        ?string $pass = null
    ): bool {
        if ($pass !== null && $pass !== '') {
            $passHash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("
                UPDATE usuarios
                SET nombre = ?, apellido = ?, correo = ?, id_tipoUsuario = ?, pass = ?
                WHERE cedula = ?
            ");
            $stmt->bind_param('sssiss', $nombre, $apellido, $correo, $tipoUsuario, $passHash, $cedula);
        } else {
            $stmt = $this->conn->prepare("
                UPDATE usuarios
                SET nombre = ?, apellido = ?, correo = ?, id_tipoUsuario = ?
                WHERE cedula = ?
            ");
            $stmt->bind_param('sssis', $nombre, $apellido, $correo, $tipoUsuario, $cedula);
        }

        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function deleteUser(string $cedula): bool
    {
        $stmt = $this->conn->prepare("
            DELETE FROM usuarios WHERE cedula = ?
        ");
        $stmt->bind_param('s', $cedula);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    // ─── Auth (controllerUser.php) ────────────────────────────────────────────

    public function getUserByEmail(string $correo): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT cedula, nombre, apellido, pass
            FROM usuarios
            WHERE correo = ?
        ");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function isBlocked(
        string $correo,
        int    $maxAttempts,
        int    $blockDuration
    ): bool {
        $stmt = $this->conn->prepare("
            SELECT intentos, ultimo_intento
            FROM intentos_login
            WHERE correo = ?
        ");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) return false;

        $elapsed = time() - strtotime($row['ultimo_intento']);
        return $row['intentos'] >= $maxAttempts && $elapsed <= $blockDuration;
    }

    public function getRemainingBlockTime(string $correo, int $blockDuration): int
    {
        $stmt = $this->conn->prepare("
            SELECT TIMESTAMPDIFF(SECOND, ultimo_intento, NOW()) AS tiempo_transcurrido
            FROM intentos_login
            WHERE correo = ?
        ");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) return 0;
        return max(0, $blockDuration - (int) $row['tiempo_transcurrido']);
    }

    public function registerLoginAttempt(string $correo, int $blockDuration): int
    {
        $stmt = $this->conn->prepare("
            SELECT intentos, ultimo_intento
            FROM intentos_login
            WHERE correo = ?
        ");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $elapsed  = time() - strtotime($row['ultimo_intento']);
            $attempts = $elapsed > $blockDuration ? 1 : $row['intentos'] + 1;

            $stmt = $this->conn->prepare("
                UPDATE intentos_login
                SET intentos = ?, ultimo_intento = NOW()
                WHERE correo = ?
            ");
            $stmt->bind_param('is', $attempts, $correo);
        } else {
            $attempts = 1;
            $stmt = $this->conn->prepare("
                INSERT INTO intentos_login (correo, intentos, ultimo_intento)
                VALUES (?, 1, NOW())
            ");
            $stmt->bind_param('s', $correo);
        }

        $stmt->execute();
        return $attempts;
    }

    public function resetLoginAttempts(string $correo): void
    {
        $stmt = $this->conn->prepare("
            UPDATE intentos_login
            SET intentos = 0, ultimo_intento = NOW()
            WHERE correo = ?
        ");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
    }

    public function registerSession(
        string $cedula,
        string $ipAddress,
        string $userAgent
    ): void {
        $inicio = date('Y-m-d H:i:s');
        $stmt   = $this->conn->prepare("
            INSERT INTO sesiones_usuarios (cedula, inicio_sesion, ip_address, user_agent)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('ssss', $cedula, $inicio, $ipAddress, $userAgent);
        $stmt->execute();
    }

    public function saveRememberToken(string $cedula, string $token): void
    {
        $stmt = $this->conn->prepare("
            DELETE FROM tokens_recuerdo WHERE cedula = ?
        ");
        $stmt->bind_param('s', $cedula);
        $stmt->execute();

        $stmt = $this->conn->prepare("
            INSERT INTO tokens_recuerdo (cedula, token) VALUES (?, ?)
        ");
        $stmt->bind_param('ss', $cedula, $token);
        $stmt->execute();
    }

    public function getUserByToken(string $token): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT u.*
            FROM usuarios u
            JOIN tokens_recuerdo tr ON u.cedula = tr.cedula
            WHERE tr.token = ?
        ");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function deleteRememberToken(string $cedula): void
    {
        $stmt = $this->conn->prepare("
            DELETE FROM tokens_recuerdo WHERE cedula = ?
        ");
        $stmt->bind_param('s', $cedula);
        $stmt->execute();
    }

    public function getProfileByCedula(string $cedula): ?array
{
    $stmt = $this->conn->prepare("
        SELECT u.nombre, u.apellido, u.correo, u.id_tipoUsuario, t.tipo
        FROM usuarios u
        JOIN tipos_usuario t ON u.id_tipoUsuario = t.id_tipoUsuario
        WHERE u.cedula = ?
    ");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ?: null;
}

public function getEstadoAcademico(string $cedula): string
{
    $stmt = $this->conn->prepare("
        SELECT estado_academico FROM estudiantes WHERE cedula = ?
    ");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['estado_academico'] ?? 'Desconocido';
}
}