<?php
// Importar la configuración
$host = "localhost";
$username = "jasonpty"; // Cambia a tu usuario de la base de datos
$password = "jason27278"; // Cambia a la contraseña de tu base de datos
$database = "asistencia_virtual"; // Cambia a tu base de datos

// Conexión a la base de datos
$conn = new mysqli($host, $username, $password, $database);

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para verificar credenciales
    public function login($correo, $password) {
        // Consulta para obtener la contraseña de la base de datos
        $sql = "SELECT pass FROM usuarios WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            die("Error en la preparación de la consulta: " . $this->conn->error);
        }

        // Vincular el parámetro de correo
        $stmt->bind_param("s", $correo);
        $stmt->execute();

        // Declaración de la variable $hash antes de bind_result
        $hash = '';

        // Vincular el resultado de la consulta con la variable $hash
        $stmt->bind_result($hash);

        // Si la consulta devuelve un resultado, comprobar la contraseña
        if ($stmt->fetch()) {
            $stmt->close(); // Cerrar el statement

            // Verificar si la contraseña coincide con el hash almacenado
            if (password_verify($password, $hash)) {
                return "Login exitoso"; // Mensaje de éxito
            } else {
                return "Contraseña incorrecta"; // Mensaje de contraseña incorrecta
            }
        } else {
            $stmt->close(); // Cerrar el statement si no se encuentra el correo
            return "Correo no registrado"; // Mensaje de correo no encontrado
        }
    }
}
?>
