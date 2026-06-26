<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/user/userRepository.php';

$userRepository       = new UserRepository();
$self                 = basename(__FILE__);

$registros_por_pagina = 20;
$pagina_actual        = max(1, (int) ($_GET['pagina'] ?? 1));
$offset               = ($pagina_actual - 1) * $registros_por_pagina;

const TIPO_USUARIO = [
    1 => 'Administrador',
    2 => 'Estudiante',
    3 => 'Profesor',
];

// ─── Agregar usuario ─────────────────────────────────────────────────────────

if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $cedula      = trim($_POST['cedula']      ?? '');
    $nombre      = trim($_POST['nombre']      ?? '');
    $apellido    = trim($_POST['apellido']    ?? '');
    $correo      = trim($_POST['correo']      ?? '');
    $tipoUsuario = (int) ($_POST['tipo_usuario'] ?? 0);
    $pass        = $_POST['pass'] ?? '';

    if ($cedula === '' || $nombre === '' || $apellido === '' || $correo === '' || $pass === '') {
        $_SESSION['error'] = 'Todos los campos son obligatorios al crear un usuario.';
    } elseif ($userRepository->createUser($cedula, $nombre, $apellido, $correo, $tipoUsuario, $pass)) {
        $_SESSION['mensaje'] = 'Usuario agregado exitosamente.';
    } else {
        $_SESSION['error'] = 'Error al agregar el usuario.';
    }

    header("Location: {$self}");
    exit();
}

// ─── Actualizar usuario ───────────────────────────────────────────────────────

if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $cedula      = trim($_POST['cedula']      ?? '');
    $nombre      = trim($_POST['nombre']      ?? '');
    $apellido    = trim($_POST['apellido']    ?? '');
    $correo      = trim($_POST['correo']      ?? '');
    $tipoUsuario = (int) ($_POST['tipo_usuario'] ?? 0);
    $pass        = $_POST['pass'] ?? '';

    if ($cedula === '' || $nombre === '' || $apellido === '' || $correo === '') {
        $_SESSION['error'] = 'Datos inválidos para actualizar.';
    } elseif ($userRepository->updateUser($cedula, $nombre, $apellido, $correo, $tipoUsuario, $pass ?: null)) {
        $_SESSION['mensaje'] = 'Usuario actualizado exitosamente.';
    } else {
        $_SESSION['error'] = 'Error al actualizar el usuario.';
    }

    header("Location: {$self}");
    exit();
}

// ─── Eliminar usuario ─────────────────────────────────────────────────────────

if (isset($_GET['eliminar'])) {
    $cedula = trim($_GET['eliminar']);

    if ($userRepository->deleteUser($cedula)) {
        $_SESSION['mensaje'] = 'Usuario eliminado exitosamente.';
    } else {
        $_SESSION['error'] = 'Error al eliminar el usuario.';
    }

    header("Location: {$self}");
    exit();
}

// ─── Datos para la vista ──────────────────────────────────────────────────────

$total_registros = $userRepository->getTotalUsers();
$total_paginas   = (int) ceil($total_registros / $registros_por_pagina);
$usuarios        = $userRepository->getUsers($registros_por_pagina, $offset);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .main-content { padding: 2rem; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.075); }
        .btn-action { margin-right: 0.5rem; }
        .modal-header { background-color: #f8f9fa; }
    </style>
</head>
<body>
<main role="main" class="main-content">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Gestión de Usuarios</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal">
                        <i class="ri-add-line"></i> Agregar Usuario
                    </button>
                </div>

                <div class="card-body">

                    <?php if (isset($_SESSION['mensaje'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Correo</th>
                                    <th>Tipo Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No hay usuarios registrados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= htmlspecialchars($usuario['cedula']) ?></td>
                                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                    <td><?= htmlspecialchars($usuario['apellido']) ?></td>
                                    <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                    <td><?= TIPO_USUARIO[$usuario['id_tipoUsuario']] ?? 'No definido' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-action editar-usuario"
                                                data-cedula="<?=   htmlspecialchars($usuario['cedula'],   ENT_QUOTES) ?>"
                                                data-nombre="<?=   htmlspecialchars($usuario['nombre'],   ENT_QUOTES) ?>"
                                                data-apellido="<?= htmlspecialchars($usuario['apellido'], ENT_QUOTES) ?>"
                                                data-correo="<?=   htmlspecialchars($usuario['correo'],   ENT_QUOTES) ?>"
                                                data-tipo="<?=     (int) $usuario['id_tipoUsuario'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#usuarioModal">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-action eliminar-usuario"
                                                data-cedula="<?= htmlspecialchars($usuario['cedula'], ENT_QUOTES) ?>">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_paginas > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?= $i === $pagina_actual ? 'active' : '' ?>">
                                    <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestionar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formularioUsuario" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accion" value="agregar">
                    <div class="mb-3">
                        <label class="form-label">Cédula</label>
                        <input type="text" class="form-control" name="cedula" id="cedula" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" class="form-control" name="apellido" id="apellido" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" id="correo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Usuario</label>
                        <select class="form-select" name="tipo_usuario" id="tipo_usuario" required>
                            <option value="1">Administrador</option>
                            <option value="2">Estudiante</option>
                            <option value="3">Profesor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="pass" id="pass">
                        <small class="form-text text-muted">Deja en blanco si no deseas cambiar</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    const SELF = '<?= $self ?>';

    document.addEventListener('DOMContentLoaded', function () {

        // Editar usuario — poblar el modal
        document.querySelectorAll('.editar-usuario').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('accion').value        = 'actualizar';
                document.getElementById('cedula').value        = this.dataset.cedula;
                document.getElementById('nombre').value        = this.dataset.nombre;
                document.getElementById('apellido').value      = this.dataset.apellido;
                document.getElementById('correo').value        = this.dataset.correo;
                document.getElementById('tipo_usuario').value  = this.dataset.tipo;
            });
        });

        // Eliminar usuario — confirmación SweetAlert
        document.querySelectorAll('.eliminar-usuario').forEach(btn => {
            btn.addEventListener('click', function () {
                const cedula = this.dataset.cedula;
                Swal.fire({
                    title:              '¿Estás seguro?',
                    text:               'No podrás revertir esta acción.',
                    icon:               'warning',
                    showCancelButton:   true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor:  '#d33',
                    confirmButtonText:  'Sí, eliminar',
                    cancelButtonText:   'Cancelar',
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = `${SELF}?eliminar=${cedula}`;
                    }
                });
            });
        });

        // Resetear modal al cerrar
        document.getElementById('usuarioModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('accion').value = 'agregar';
            document.getElementById('formularioUsuario').reset();
        });
    });
</script>
</body>
</html>