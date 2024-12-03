<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    die("No hay sesión activa. Por favor, inicie sesión.");
}
require_once('../../config.php');

// Database Connection
$host = "localhost";
$username = "jasonpty";
$password = "jason27278";
$database = "asistencia_virtual";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$registros_por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

function limpiarDato($dato) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($dato))));
}

// Agregar Usuario
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $cedula = limpiarDato($_POST['cedula']);
    $nombre = limpiarDato($_POST['nombre']);
    $apellido = limpiarDato($_POST['apellido']);
    $correo = limpiarDato($_POST['correo']);
    $tipo_usuario = intval($_POST['tipo_usuario']);
    
    // Hash de contraseña
    $pass_hash = password_hash($_POST['pass'], PASSWORD_DEFAULT);

    $query = "INSERT INTO usuarios (cedula, nombre, apellido, correo, id_tipoUsuario, pass) 
              VALUES ('$cedula', '$nombre', '$apellido', '$correo', $tipo_usuario, '$pass_hash')";
    
    if ($conn->query($query)) {
        $_SESSION['mensaje'] = "Usuario agregado exitosamente";
    } else {
        $_SESSION['error'] = "Error al agregar usuario: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Eliminar Usuario
if (isset($_GET['eliminar'])) {
    $cedula = limpiarDato($_GET['eliminar']);
    $query = "DELETE FROM usuarios WHERE cedula = '$cedula'";
    
    if ($conn->query($query)) {
        $_SESSION['mensaje'] = "Usuario eliminado exitosamente";
    } else {
        $_SESSION['error'] = "Error al eliminar usuario: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Actualizar Usuario
if (isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    $cedula = limpiarDato($_POST['cedula']);
    $nombre = limpiarDato($_POST['nombre']);
    $apellido = limpiarDato($_POST['apellido']);
    $correo = limpiarDato($_POST['correo']);
    $tipo_usuario = intval($_POST['tipo_usuario']);

    $query = "UPDATE usuarios SET 
              nombre = '$nombre', 
              apellido = '$apellido', 
              correo = '$correo', 
              id_tipoUsuario = $tipo_usuario";
    
    if (!empty($_POST['pass'])) {
        $pass_hash = password_hash($_POST['pass'], PASSWORD_DEFAULT);
        $query .= ", pass = '$pass_hash'";
    }
    
    $query .= " WHERE cedula = '$cedula'";
    
    if ($conn->query($query)) {
        $_SESSION['mensaje'] = "Usuario actualizado exitosamente";
    } else {
        $_SESSION['error'] = "Error al actualizar usuario: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$total_registros_query = "SELECT COUNT(*) as total FROM usuarios";
$total_registros_result = $conn->query($total_registros_query);
$total_registros = $total_registros_result->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

$query = "SELECT cedula, nombre, apellido, correo, id_tipoUsuario 
          FROM usuarios 
          ORDER BY nombre 
          LIMIT $registros_por_pagina OFFSET $offset";
$result = $conn->query($query);
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
                            <?php if(isset($_SESSION['mensaje'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
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
                                        <?php while($usuario = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $usuario['cedula'] ?></td>
                                            <td><?= $usuario['nombre'] ?></td>
                                            <td><?= $usuario['apellido'] ?></td>
                                            <td><?= $usuario['correo'] ?></td>
                                            <td>
                                                <?php 
                                                switch($usuario['id_tipoUsuario']) {
                                                    case 1: echo "Administrador"; break;
                                                    case 2: echo "Estudiante"; break;
                                                    case 3: echo "Profesor"; break;
                                                    default: echo "No definido";
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning btn-action editar-usuario" 
                                                    data-cedula="<?= $usuario['cedula'] ?>"
                                                    data-nombre="<?= $usuario['nombre'] ?>"
                                                    data-apellido="<?= $usuario['apellido'] ?>"
                                                    data-correo="<?= $usuario['correo'] ?>"
                                                    data-tipo="<?= $usuario['id_tipoUsuario'] ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#usuarioModal">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-action eliminar-usuario" 
                                                    data-cedula="<?= $usuario['cedula'] ?>">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                                        <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                                            <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
    document.addEventListener('DOMContentLoaded', function() {
        // Evento para editar usuario
        const editarBotones = document.querySelectorAll('.editar-usuario');
        editarBotones.forEach(boton => {
            boton.addEventListener('click', function() {
                document.getElementById('accion').value = 'actualizar';
                document.getElementById('cedula').value = this.dataset.cedula;
                document.getElementById('nombre').value = this.dataset.nombre;
                document.getElementById('apellido').value = this.dataset.apellido;
                document.getElementById('correo').value = this.dataset.correo;
                document.getElementById('tipo_usuario').value = this.dataset.tipo;
            });
        });

        // Evento para eliminar usuario
        const eliminarBotones = document.querySelectorAll('.eliminar-usuario');
        eliminarBotones.forEach(boton => {
            boton.addEventListener('click', function() {
                const cedula = this.dataset.cedula;
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "No podrás revertir esta acción",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '?eliminar=' + cedula;
                    }
                });
            });
        });

        // Resetear modal al cerrar
        const modal = document.getElementById('usuarioModal');
        modal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('accion').value = 'agregar';
            document.getElementById('formularioUsuario').reset();
        });
    });
    </script>
</body>
</html>