<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/students/studentsRepository.php';

$self            = basename(__FILE__);
$repo            = new StudentsRepository();
$cedulaProfesor  = $_SESSION['cedula'];

// Parámetros de paginación y filtros
$limit        = 30;
$page         = isset($_GET['page'])         ? (int)$_GET['page']              : 1;
$offset       = ($page - 1) * $limit;
$cedulaFilter = isset($_GET['cedulaFilter']) ? trim($_GET['cedulaFilter'])      : null;
$groupFilter  = isset($_GET['groupFilter'])  ? trim($_GET['groupFilter'])       : null;
$courseFilter = isset($_GET['courseFilter']) ? trim($_GET['courseFilter'])      : null;
$search       = isset($_GET['search'])       ? trim($_GET['search'])            : '';

// Datos
$students         = $repo->getStudents($cedulaProfesor, $limit, $offset, $cedulaFilter, $groupFilter, $courseFilter);
$totalEstudiantes = $repo->countStudents($cedulaProfesor, $cedulaFilter, $groupFilter, $courseFilter);
$cedulas          = $repo->getCedulasByProfesor($cedulaProfesor, $search, $groupFilter, $courseFilter);
$groups           = $repo->getGroupsByProfesor($cedulaProfesor);
$cursos           = $repo->getCoursesByProfesor($cedulaProfesor);
$totalPages       = (int) ceil($totalEstudiantes / $limit);

// Helper para construir query string de paginación
function buildPageQuery(int $targetPage, array $get): string
{
    $params = ['page' => $targetPage];
    foreach (['search', 'groupFilter', 'courseFilter', 'cedulaFilter'] as $key) {
        if (!empty($get[$key])) {
            $params[$key] = $get[$key];
        }
    }
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Estudiantes - Sistema de Gestión de Asistencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/../Demo-Sas/public/assets/css/estudiantes.css">
</head>
<body>
<main class="main-content">

    <!-- Filtros -->
    <div class="filters-card">
        <form method="GET" action="" id="filterForm">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Buscar</label>
                    <select class="form-select" name="cedulaFilter" onchange="this.form.submit()">
                        <option value="">Todas las cédulas</option>
                        <?php foreach ($cedulas as $c): ?>
                            <option value="<?= htmlspecialchars($c['cedula'], ENT_QUOTES) ?>"
                                <?= $cedulaFilter === $c['cedula'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['cedula'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Curso</label>
                    <select class="form-select" name="courseFilter" onchange="this.form.submit()">
                        <option value="">Todos los cursos</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= htmlspecialchars($curso['id_curso'], ENT_QUOTES) ?>"
                                <?= $courseFilter === $curso['id_curso'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($curso['nombre_curso'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Grupo</label>
                    <select class="form-select" name="groupFilter" onchange="this.form.submit()">
                        <option value="">Todos los grupos</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= htmlspecialchars($group['id_grupo'], ENT_QUOTES) ?>"
                                <?= $groupFilter === $group['id_grupo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($group['id_grupo'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="form-container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Correo Institucional</th>
                        <th>IdGrupo</th>
                        <th>IdCurso</th>
                        <th>% Asistencia</th>
                        <th>Estado Académico</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <?php
                                $pct = (float) $student['porcentaje_asistencia'];
                                if ($pct > 85) {
                                    $colorPct = 'bg-success';
                                } elseif ($pct < 50) {
                                    $colorPct = 'bg-danger';
                                } else {
                                    $colorPct = 'bg-warning';
                                }
                                $colorEstado = match($student['estado_academico']) {
                                    'Activo'   => 'bg-success',
                                    'Retirado' => 'bg-danger',
                                    default    => 'bg-warning'
                                };
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($student['nombre'] . ' ' . $student['apellido'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($student['correo'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($student['id_grupo'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($student['cursos'] ?? '', ENT_QUOTES) ?></td>
                                <td><span class="badge <?= $colorPct ?>"><?= number_format($pct, 2) ?>%</span></td>
                                <td><span class="badge <?= $colorEstado ?>"><?= htmlspecialchars($student['estado_academico'], ENT_QUOTES) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No hay resultados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <div class="pagination-container">
        <nav>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= buildPageQuery($page - 1, $_GET) ?>">&laquo; Anterior</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i <= 3 || $i > $totalPages - 3 || abs($i - $page) <= 1): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= buildPageQuery($i, $_GET) ?>"><?= $i ?></a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= buildPageQuery($page + 1, $_GET) ?>">Siguiente &raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="text-center mt-2">
            Mostrando <?= count($students) ?> de <?= $totalEstudiantes ?> estudiantes
        </div>
    </div>

</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>