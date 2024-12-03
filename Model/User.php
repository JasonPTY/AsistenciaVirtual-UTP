<?php
class Usuarios {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function registrarIntento($correo, $blockDuration) {
        $currentTime = time();
        $attempts = 0;

        $stmt = $this->conn->prepare("SELECT intentos, ultimo_intento FROM intentos_login WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $attempts = $row['intentos'];
            $lastAttempt = strtotime($row['ultimo_intento']);

            if ($currentTime - $lastAttempt > $blockDuration) {
                $attempts = 0;
            }

            $attempts++;
            $stmt = $this->conn->prepare("UPDATE intentos_login SET intentos = ?, ultimo_intento = NOW() WHERE correo = ?");
            $stmt->bind_param("is", $attempts, $correo);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO intentos_login (correo, intentos, ultimo_intento) VALUES (?, 1, NOW())");
            $stmt->bind_param("s", $correo);
        }

        $stmt->execute();
        return $attempts;
    }

    public function estaBloqueado($correo, $maxAttempts, $blockDuration) {
        $stmt = $this->conn->prepare("SELECT intentos, ultimo_intento FROM intentos_login WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentTime = time();
            $lastAttempt = strtotime($row['ultimo_intento']);

            if ($row['intentos'] >= $maxAttempts && ($currentTime - $lastAttempt <= $blockDuration)) {
                return true;
            }
        }
        return false;
    }

    public function resetearIntentos($correo) {
        $stmt = $this->conn->prepare("UPDATE intentos_login SET intentos = 0, ultimo_intento = NOW() WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
    }

    public function obtenerUsuarioPorCorreo($correo) {
        $stmt = $this->conn->prepare("SELECT cedula, nombre, apellido, pass FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function registrarSesion($cedula, $ip_address, $user_agent) {
        $inicio_sesion = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("INSERT INTO sesiones_usuarios (cedula, inicio_sesion, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $cedula, $inicio_sesion, $ip_address, $user_agent);
        $stmt->execute();
    }

    public function guardarTokenDeRecuerdo($cedula, $token) {
        $sql = "DELETE FROM tokens_recuerdo WHERE cedula = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $stmt->close();

        $sql = "INSERT INTO tokens_recuerdo (cedula, token) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $cedula, $token);
        $stmt->execute();
        $stmt->close();
    }

    public function obtenerUsuarioPorToken($token) {
        $sql = "SELECT u.* FROM usuarios u
                JOIN tokens_recuerdo tr ON u.cedula = tr.cedula
                WHERE tr.token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();

        return $usuario;
    }
    
    public function eliminarTokenDeRecuerdo($cedula) {
        $sql = "DELETE FROM tokens_recuerdo WHERE cedula = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $stmt->close();
    }
}
?>
