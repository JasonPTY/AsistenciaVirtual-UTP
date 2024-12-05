<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /AsistenciaVirtual/View/login.php");
    exit();
}$cedula = $_SESSION['cedula']; 


require_once('../../config.php');

$sql = "SELECT nombre, apellido, correo, id_tipoUsuario FROM usuarios WHERE cedula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $nombre_completo = $user['nombre'] . " " . $user['apellido'];
    $correo = $user['correo'];
    $id_tipo_usuario = $user['id_tipoUsuario'];

    $sql_tipo_usuario = "SELECT tipo FROM tipos_usuario WHERE id_tipoUsuario = ?";
    $stmt_tipo_usuario = $conn->prepare($sql_tipo_usuario);
    $stmt_tipo_usuario->bind_param("i", $id_tipo_usuario);
    $stmt_tipo_usuario->execute();
    $result_tipo_usuario = $stmt_tipo_usuario->get_result();

    if ($result_tipo_usuario->num_rows > 0) {
        $tipo_usuario = $result_tipo_usuario->fetch_assoc()['tipo'];
    } else {
        $tipo_usuario = "Desconocido";
    }

    if ($id_tipo_usuario == 2) {
        $sql_estado = "SELECT e.estado_academico FROM estudiantes e WHERE cedula = ?";
        $stmt_estado = $conn->prepare($sql_estado);
        $stmt_estado->bind_param("s", $cedula);
        $stmt_estado->execute();
        $result_estado = $stmt_estado->get_result();

        if ($result_estado->num_rows > 0) {
            $estado_academico = $result_estado->fetch_assoc()['estado_academico'];
        } else {
            $estado_academico = "Desconocido";
        }

        $stmt_estado->close();
    } else {
        $estado_academico = null;
    }

    $stmt_tipo_usuario->close();
} else {
    header("Location: /AsistenciaVirtual/View/login.php");
    exit;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/../AsistenciaVirtual/public/assets/css/perfil.css">
</head>
<body>
    <main class="main-content">
        <div class="container-fluid">
            <div class="profile-header text-center">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="mb-2"><?php echo $nombre_completo; ?></h2>
                
                <?php if ($id_tipo_usuario == 2) : ?>
                    <span class="status-badge">
                        <i class="fas fa-check-circle me-1"></i><?php echo $estado_academico; ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="profile-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-id-card me-2"></i>Información Personal
                    </h5>
                </div>
                <div class="profile-info">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="fas fa-user"></i>Nombre Completo
                                </div>
                                <div class="info-value"><?php echo $nombre_completo; ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="fas fa-envelope"></i>Correo Institucional
                                </div>
                                <div class="info-value"><?php echo $correo; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="fas fa-user-tag"></i>Rol
                                </div>
                                <div class="info-value"><?php echo $tipo_usuario; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>