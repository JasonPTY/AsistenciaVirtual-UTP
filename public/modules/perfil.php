<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/user/userRepository.php';

$self   = basename(__FILE__);
$repo   = new UserRepository();
$cedula = $_SESSION['cedula'];

$perfil = $repo->getProfileByCedula($cedula);
if ($perfil === null) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

$nombreCompleto  = $perfil['nombre'] . ' ' . $perfil['apellido'];
$correo          = $perfil['correo'];
$tipoUsuario     = $perfil['tipo'];
$idTipoUsuario   = (int) $perfil['id_tipoUsuario'];
$estadoAcademico = $idTipoUsuario === 2 ? $repo->getEstadoAcademico($cedula) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/../Demo-Sas/public/assets/css/perfil.css">
</head>
<body>
<main class="main-content">
    <div class="container-fluid">

        <div class="profile-header text-center">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h2 class="mb-2"><?= htmlspecialchars($nombreCompleto, ENT_QUOTES) ?></h2>
            <?php if ($idTipoUsuario === 2): ?>
                <span class="status-badge">
                    <i class="fas fa-check-circle me-1"></i>
                    <?= htmlspecialchars($estadoAcademico, ENT_QUOTES) ?>
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
                            <div class="info-value"><?= htmlspecialchars($nombreCompleto, ENT_QUOTES) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">
                                <i class="fas fa-envelope"></i>Correo Institucional
                            </div>
                            <div class="info-value"><?= htmlspecialchars($correo, ENT_QUOTES) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <div class="info-label">
                                <i class="fas fa-user-tag"></i>Rol
                            </div>
                            <div class="info-value"><?= htmlspecialchars($tipoUsuario, ENT_QUOTES) ?></div>
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