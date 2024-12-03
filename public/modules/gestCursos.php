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

// Pagination and Search
$registros_por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;
$busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';

// Función para limpiar y validar datos
function limpiarDato($dato) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($dato))));
}

// Agregar Curso
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $nombre_curso = limpiarDato($_POST['nombre_curso']);
    $id_grupo = !empty($_POST['id_grupo']) ? limpiarDato($_POST['id_grupo']) : NULL;

    $query = "INSERT INTO cursos (nombre_curso, id_grupo) VALUES ('$nombre_curso', " . 
             ($id_grupo ? "'$id_grupo'" : "NULL") . ")";
    
    if ($conn->query($query)) {
        $_SESSION['mensaje'] = "Curso agregado exitosamente";
    } else {
        $_SESSION['error'] = "Error al agregar curso: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . (!empty($busqueda) ? "?busqueda=$busqueda" : ''));
    exit();
}

// Eliminar Curso
if (isset($_GET['eliminar'])) {
    $id_curso = limpiarDato($_GET['eliminar']);
    $query = "DELETE FROM cursos WHERE id_curso = '$id_curso'";
    
    if ($conn->query($query)) {
        $_SESSION['mensaje'] = "Curso eliminado exitosamente";
    } else {
        $_SESSION['error'] = "Error al eliminar curso: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . (!empty($busqueda) ? "?busqueda=$busqueda" : ''));
    exit();
}

// Actualizar Curso
if (isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    $id_curso = limpiarDato($_POST['id_curso']);
    $nombre_curso = limpiarDato($_POST['nombre_curso']);
    $id_grupo = !empty($_POST['id_grupo']) ? limpiarDato($_POST['id_grupo']) : NULL;

    $query = "UPDATE cursos SET 
              nombre_curso = '$nombre_curso', 
              id_grupo = " . ($id_grupo ? "'$id_grupo'" : "NULL") . "
              WHERE id_curso = '$id_curso'";
    
    if ($conn->query($query)) {
        $_SESSION['mensaje'] = "Curso actualizado exitosamente";
    } else {
        $_SESSION['error'] = "Error al actualizar curso: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . (!empty($busqueda) ? "?busqueda=$busqueda" : ''));
    exit();
}

// Consulta con paginación y búsqueda
$where_clause = $busqueda ? 
    "WHERE (nombre_curso LIKE '%$busqueda%' OR id_curso LIKE '%$busqueda%')" : '';

// Contar total de registros para paginación
$total_registros_query = "SELECT COUNT(*) as total FROM cursos $where_clause";
$total_registros_result = $conn->query($total_registros_query);
$total_registros = $total_registros_result->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta con paginación y búsqueda
$query = "SELECT id_curso, nombre_curso, id_grupo 
          FROM cursos 
          $where_clause
          ORDER BY nombre_curso 
          LIMIT $registros_por_pagina OFFSET $offset";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos</title>
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
                            <h3 class="mb-0">Gestión de Cursos</h3>
                            <div class="d-flex align-items-center">
                                <form class="me-3" method="GET">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="busqueda" 
                                               placeholder="Buscar por nombre o ID" 
                                               value="<?= htmlspecialchars($busqueda) ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="ri-search-line"></i>
                                        </button>
                                    </div>
                                </form>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cursoModal">
                                    <i class="ri-add-line"></i> Agregar Curso
                                </button>
                            </div>
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
                                            <th>ID Curso</th>
                                            <th>Nombre del Curso</th>
                                            <th>ID Grupo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($curso = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $curso['id_curso'] ?></td>
                                            <td><?= $curso['nombre_curso'] ?></td>
                                            <td><?= $curso['id_grupo'] ?? 'Sin grupo' ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning btn-action editar-curso" 
                                                    data-id="<?= $curso['id_curso'] ?>"
                                                    data-nombre="<?= $curso['nombre_curso'] ?>"
                                                    data-grupo="<?= $curso['id_grupo'] ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#cursoModal">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-action eliminar-curso" 
                                                    data-id="<?= $curso['id_curso'] ?>">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                                        <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                                            <a class="page-link" href="?pagina=<?= $i . ($busqueda ? "&busqueda=$busqueda" : '') ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Curso -->
        <div class="modal fade" id="cursoModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Gestionar Curso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formularioCurso" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="accion" id="accion" value="agregar">
                            <input type="hidden" name="id_curso" id="id_curso">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Curso</label>
                                <input type="text" class="form-control" name="nombre_curso" id="nombre_curso" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ID Grupo (Opcional)</label>
                                <input type="text" class="form-control" name="id_grupo" id="id_grupo">
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
        // Evento para editar curso
        const editarBotones = document.querySelectorAll('.editar-curso');
        editarBotones.forEach(boton => {
            boton.addEventListener('click', function() {
                document.getElementById('accion').value = 'actualizar';
                document.getElementById('id_curso').value = this.dataset.id;
                document.getElementById('nombre_curso').value = this.dataset.nombre;
                document.getElementById('id_grupo').value = this.dataset.grupo || '';
            });
        });

        // Evento para eliminar curso
        const eliminarBotones = document.querySelectorAll('.eliminar-curso');
        eliminarBotones.forEach(boton => {
            boton.addEventListener('click', function() {
                const id_curso = this.dataset.id;
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
                        const busqueda = '<?= $busqueda ?>';
                        const url = '?eliminar=' + id_curso + (busqueda ? '&busqueda=' + busqueda : '');
                        window.location.href = url;
                    }
                });
            });
        });

        // Resetear modal al cerrar
        const modal = document.getElementById('cursoModal');
        modal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('accion').value = 'agregar';
            document.getElementById('formularioCurso').reset();
        });
    });
    </script>
</body>
</html>
<?php
$conn->close();
?>