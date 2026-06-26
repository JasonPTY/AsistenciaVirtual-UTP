<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/courses/coursesRepository.php';

$courseRepository     = new CourseRepository();
$self                 = basename(__FILE__);

$registros_por_pagina = 20;
$pagina_actual        = max(1, (int) ($_GET['pagina']   ?? 1));
$busqueda             = trim($_GET['busqueda'] ?? '');
$offset               = ($pagina_actual - 1) * $registros_por_pagina;

// ─── Agregar curso ───────────────────────────────────────────────────────────

if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombreCurso = trim($_POST['nombre_curso'] ?? '');
    $idGrupo     = trim($_POST['id_grupo']     ?? '') ?: null;

    if ($nombreCurso === '') {
        $_SESSION['error'] = 'El nombre del curso es obligatorio.';
    } elseif ($courseRepository->createCourse($nombreCurso, $idGrupo)) {
        $_SESSION['mensaje'] = 'Curso agregado exitosamente.';
    } else {
        $_SESSION['error'] = 'Error al agregar el curso.';
    }

    header('Location: ' . $self . ($busqueda ? "?busqueda={$busqueda}" : ''));
    exit();
}

// ─── Actualizar curso ────────────────────────────────────────────────────────

if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $idCurso     = (int)   ($_POST['id_curso']     ?? 0);
    $nombreCurso = trim(   $_POST['nombre_curso']  ?? '');
    $idGrupo     = trim(   $_POST['id_grupo']      ?? '') ?: null;

    if ($idCurso === 0 || $nombreCurso === '') {
        $_SESSION['error'] = 'Datos inválidos para actualizar.';
    } elseif ($courseRepository->updateCourse($idCurso, $nombreCurso, $idGrupo)) {
        $_SESSION['mensaje'] = 'Curso actualizado exitosamente.';
    } else {
        $_SESSION['error'] = 'Error al actualizar el curso.';
    }

    header('Location: ' . $self . ($busqueda ? "?busqueda={$busqueda}" : ''));
    exit();
}

// ─── Eliminar curso ──────────────────────────────────────────────────────────

if (isset($_GET['eliminar'])) {
    $idCurso = (int) $_GET['eliminar'];

    if ($courseRepository->deleteCourse($idCurso)) {
        $_SESSION['mensaje'] = 'Curso eliminado exitosamente.';
    } else {
        $_SESSION['error'] = 'Error al eliminar el curso.';
    }

    header('Location: ' . $self . ($busqueda ? "?busqueda={$busqueda}" : ''));
    exit();
}

// ─── Datos para la vista ─────────────────────────────────────────────────────

$total_registros = $courseRepository->getTotalCourses($busqueda);
$total_paginas   = (int) ceil($total_registros / $registros_por_pagina);
$cursos          = $courseRepository->getCourses($busqueda, $registros_por_pagina, $offset);
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
                                    <th>ID Curso</th>
                                    <th>Nombre del Curso</th>
                                    <th>ID Grupo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cursos)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No se encontraron cursos.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($cursos as $curso): ?>
                                <tr>
                                    <td><?= (int) $curso['id_curso'] ?></td>
                                    <td><?= htmlspecialchars($curso['nombre_curso']) ?></td>
                                    <td><?= $curso['id_grupo'] !== null
                                            ? htmlspecialchars($curso['id_grupo'])
                                            : '<span class="text-muted">Sin grupo</span>' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-action editar-curso"
                                                data-id="<?= (int) $curso['id_curso'] ?>"
                                                data-nombre="<?= htmlspecialchars($curso['nombre_curso'], ENT_QUOTES) ?>"
                                                data-grupo="<?= htmlspecialchars($curso['id_grupo'] ?? '', ENT_QUOTES) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#cursoModal">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-action eliminar-curso"
                                                data-id="<?= (int) $curso['id_curso'] ?>">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <?php if ($total_paginas > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?= $i === $pagina_actual ? 'active' : '' ?>">
                                    <a class="page-link"
                                       href="?pagina=<?= $i . ($busqueda ? '&busqueda=' . urlencode($busqueda) : '') ?>">
                                        <?= $i ?>
                                    </a>
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
                    <input type="hidden" name="accion"   id="accion"   value="agregar">
                    <input type="hidden" name="id_curso" id="id_curso">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Curso</label>
                        <input type="text" class="form-control" name="nombre_curso" id="nombre_curso" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Grupo <span class="text-muted">(Opcional)</span></label>
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
    const SELF     = '<?= $self ?>';
    const BUSQUEDA = '<?= urlencode($busqueda) ?>';

    document.addEventListener('DOMContentLoaded', function () {

        // Editar curso — poblar el modal
        document.querySelectorAll('.editar-curso').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('accion').value       = 'actualizar';
                document.getElementById('id_curso').value     = this.dataset.id;
                document.getElementById('nombre_curso').value = this.dataset.nombre;
                document.getElementById('id_grupo').value     = this.dataset.grupo || '';
            });
        });

        // Eliminar curso — confirmación con SweetAlert
        document.querySelectorAll('.eliminar-curso').forEach(btn => {
            btn.addEventListener('click', function () {
                const idCurso = this.dataset.id;
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
                        window.location.href = `${SELF}?eliminar=${idCurso}${BUSQUEDA ? '&busqueda=' + BUSQUEDA : ''}`;
                    }
                });
            });
        });

        // Resetear modal al cerrar
        document.getElementById('cursoModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('accion').value = 'agregar';
            document.getElementById('formularioCurso').reset();
        });
    });
</script>
</body>
</html>