<?php
//Script que utilize para convertir todas als contraseñas de los usuarios q sean hasheadas, se ejecuta independiente 
require_once('../../config.php');
try {
    $sql = "SELECT correo, pass FROM usuarios";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            $hashedPassword = password_hash($user['pass'], PASSWORD_BCRYPT);

            $updateSql = "UPDATE usuarios SET pass = ? WHERE correo = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('ss', $hashedPassword, $user['correo']);
            $updateStmt->execute();
        }

        echo "Las contraseñas se han actualizado correctamente a formato hasheado.";
    } else {
        echo "No se encontraron usuarios en la base de datos.";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

