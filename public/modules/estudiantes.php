<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /AsistenciaVirtual/View/login.php");
    exit();
}
$cedula_profesor = $_SESSION['cedula']; 

require_once('../../config.php');

$limit = 30;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$baseSql = "
    SELECT 
        u.apellido,
        u.nombre, 
        u.correo, 
        e.id_grupo, 
        e.estado_academico,
        GROUP_CONCAT(DISTINCT c.id_curso) AS cursos,
        (
            SELECT 
                IFNULL(
                    (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(ad.asistencia)) * 100,
                    0
                )
            FROM asistencia_detalle ad
            WHERE ad.cedula = e.cedula
        ) AS porcentaje_asistencia
    FROM estudiantes e
    JOIN usuarios u ON e.cedula = u.cedula
    LEFT JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
    LEFT JOIN cursos c ON ec.id_curso = c.id_curso
    LEFT JOIN profesor_curso pc ON c.id_curso = pc.id_curso
    WHERE pc.cedula_profesor = ?
";

$whereConditions = [];
$params = [$cedula_profesor];

// Filtro de cédula
if (isset($_GET['cedulaFilter']) && !empty($_GET['cedulaFilter'])) {
    $cedulaFilter = $conn->real_escape_string($_GET['cedulaFilter']);
    $whereConditions[] = "u.cedula = ?";
    $params[] = $cedulaFilter;
}

// Filtro de grupo
if (isset($_GET['groupFilter']) && !empty($_GET['groupFilter'])) {
    $groupFilter = $conn->real_escape_string($_GET['groupFilter']);
    $whereConditions[] = "e.id_grupo = ?";
    $params[] = $groupFilter;
}

// Filtro de curso
if (isset($_GET['courseFilter']) && !empty($_GET['courseFilter'])) {
    $courseFilter = $conn->real_escape_string($_GET['courseFilter']);
    $whereConditions[] = "c.id_curso = ?";
    $params[] = $courseFilter;
}

if (!empty($whereConditions)) {
    $baseSql .= " AND " . implode(" AND ", $whereConditions);
}

$sqlCount = "SELECT COUNT(DISTINCT e.cedula) as total 
             FROM estudiantes e 
             JOIN usuarios u ON e.cedula = u.cedula
             LEFT JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
             LEFT JOIN cursos c ON ec.id_curso = c.id_curso
             LEFT JOIN profesor_curso pc ON c.id_curso = pc.id_curso 
             WHERE pc.cedula_profesor = ?";

if (!empty($whereConditions)) {
    $sqlCount .= " AND " . implode(" AND ", $whereConditions);
}

$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param(str_repeat('s', count($params)), ...$params);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$totalEstudiantes = $resultCount->fetch_assoc()['total'];

$baseSql .= " GROUP BY e.cedula ORDER BY u.nombre, u.apellido LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($baseSql);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$resultEstudiantes = $stmt->get_result();
$students = $resultEstudiantes->fetch_all(MYSQLI_ASSOC);

$sqlCedulas = "
    SELECT DISTINCT e.cedula 
    FROM estudiantes e
    JOIN usuarios u ON e.cedula = u.cedula
    JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
    LEFT JOIN cursos c ON ec.id_curso = c.id_curso
    LEFT JOIN profesor_curso pc ON c.id_curso = pc.id_curso
    WHERE pc.cedula_profesor = ?";

$searchTerm = isset($_GET['search']) ? "%" . $conn->real_escape_string($_GET['search']) . "%" : '%';
$sqlCedulas .= " AND e.cedula LIKE ?";

if (isset($_GET['groupFilter']) && !empty($_GET['groupFilter'])) {
    $groupFilter = $conn->real_escape_string($_GET['groupFilter']);
    $sqlCedulas .= " AND e.id_grupo = ?";
}

if (isset($_GET['courseFilter']) && !empty($_GET['courseFilter'])) {
    $courseFilter = $conn->real_escape_string($_GET['courseFilter']);
    $sqlCedulas .= " AND c.id_curso = ?";
}

$sqlCedulas .= " ORDER BY e.cedula";
$stmtCedulas = $conn->prepare($sqlCedulas);

$paramsCedulas = [$cedula_profesor, $searchTerm];
if (isset($groupFilter)) {
    $paramsCedulas[] = $groupFilter;
}
if (isset($courseFilter)) {
    $paramsCedulas[] = $courseFilter;
}

$stmtCedulas->bind_param(str_repeat('s', count($paramsCedulas)), ...$paramsCedulas);
$stmtCedulas->execute();
$resultCedulas = $stmtCedulas->get_result();
$cedulas = $resultCedulas->fetch_all(MYSQLI_ASSOC);

$sqlGrupos = "
    SELECT DISTINCT e.id_grupo 
    FROM estudiantes e
    JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
    JOIN cursos c ON ec.id_curso = c.id_curso
    JOIN profesor_curso pc ON c.id_curso = pc.id_curso
    WHERE pc.cedula_profesor = ?
    ORDER BY e.id_grupo";
$stmtGrupos = $conn->prepare($sqlGrupos);
$stmtGrupos->bind_param('s', $cedula_profesor);
$stmtGrupos->execute();
$resultGrupos = $stmtGrupos->get_result();
$groups = $resultGrupos->fetch_all(MYSQLI_ASSOC);

$sqlCursos = "
    SELECT c.id_curso, c.nombre_curso
    FROM cursos c
    JOIN profesor_curso pc ON c.id_curso = pc.id_curso
    WHERE pc.cedula_profesor = ?
    ORDER BY c.nombre_curso";
$stmtCursos = $conn->prepare($sqlCursos);
$stmtCursos->bind_param('s', $cedula_profesor);
$stmtCursos->execute();
$resultCursos = $stmtCursos->get_result();
$cursos = $resultCursos->fetch_all(MYSQLI_ASSOC);

$sqlTotalCursos = "SELECT COUNT(*) AS total_cursos FROM cursos";
$totalCursos = $conn->query($sqlTotalCursos)->fetch_assoc()['total_cursos'];

$sqlTotalClases = "SELECT COUNT(*) AS total_clases FROM asistencia";
$totalClasesCompletadas = $conn->query($sqlTotalClases)->fetch_assoc()['total_clases'];

$conn->close();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Estudiantes - Sistema de Gestión de Asistencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/../AsistenciaVirtual/public/assets/css/estudiantes.css">
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
        <?php foreach ($cedulas as $cedula): ?>
            <option value="<?= $cedula['cedula'] ?>" 
                    <?= isset($_GET['cedulaFilter']) && $_GET['cedulaFilter'] == $cedula['cedula'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cedula['cedula']) ?>
            </option>
        <?php endforeach; ?>
    </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Curso</label>
                        <select class="form-select" name="courseFilter" onchange="this.form.submit()">
                <option value="">Todos los cursos</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= $curso['id_curso'] ?>" 
                            <?= isset($_GET['courseFilter']) && $_GET['courseFilter'] == $curso['id_curso'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($curso['nombre_curso']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold">Grupo</label>
            <select class="form-select" name="groupFilter" onchange="this.form.submit()">
                <option value="">Todos los grupos</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id_grupo'] ?>" 
                            <?= isset($_GET['groupFilter']) && $_GET['groupFilter'] == $group['id_grupo'] ? 'selected' : '' ?>>
                        <?= $group['id_grupo'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
                    </div>
                    </div>
                </div>
            </form>
        </div>

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
        // Determinar el color basado en el porcentaje de asistencia
        $porcentaje_asistencia = $student['porcentaje_asistencia']; 
        if ($porcentaje_asistencia > 85) {
            $color = 'bg-success'; // Verde
        } elseif ($porcentaje_asistencia < 50) {
            $color = 'bg-danger'; // Rojo
        } else {
            $color = 'bg-warning'; // Amarillo
        }
    ?>
    <tr>
        <td><?= htmlspecialchars($student['nombre'] . ' ' . $student['apellido']) ?></td>
        <td><?= htmlspecialchars($student['correo']) ?></td>
        <td><?= htmlspecialchars($student['id_grupo']) ?></td>
        <td><?= htmlspecialchars($student['cursos']) ?></td>
        <td>
            <span class="badge <?= $color ?>">
                <?= number_format($porcentaje_asistencia, 2) . '%' ?>
            </span>
        </td>
        <td>
            <span class="badge <?= $student['estado_academico'] == 'Activo' ? 'bg-success' : 
                                ($student['estado_academico'] == 'Retirado' ? 'bg-danger' : 'bg-warning') ?>">
                <?= htmlspecialchars($student['estado_academico']) ?>
            </span>
        </td>
    </tr>
<?php endforeach; ?>

                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No hay resultados</td></tr> <!-- Asegúrate de que el colspan tenga el valor correcto -->
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


        <div class="pagination-container">
            <nav>
                <ul class="pagination">
                    <?php 
                    $totalPages = ceil($totalEstudiantes / $limit);
                    
                    if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= 
                                isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' 
                            ?><?= 
                                isset($_GET['groupFilter']) ? '&groupFilter=' . htmlspecialchars($_GET['groupFilter']) : '' 
                            ?><?= 
                                isset($_GET['courseFilter']) ? '&courseFilter=' . htmlspecialchars($_GET['courseFilter']) : '' 
                            ?>">&laquo; Anterior</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): 
                        if ($i <= 3 || $i > $totalPages - 3 || abs($i - $page) <= 1): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= 
                                    isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' 
                                ?><?= 
                                    isset($_GET['groupFilter']) ? '&groupFilter=' . htmlspecialchars($_GET['groupFilter']) : '' 
                                ?><?= 
                                    isset($_GET['courseFilter']) ? '&courseFilter=' . htmlspecialchars($_GET['courseFilter']) : '' 
                                ?>"><?= $i ?></a>
                            </li>
                        <?php endif;
                    endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= 
                                isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' 
                            ?><?= 
                                isset($_GET['groupFilter']) ? '&groupFilter=' . htmlspecialchars($_GET['groupFilter']) : '' 
                            ?><?= 
                                isset($_GET['courseFilter']) ? '&courseFilter=' . htmlspecialchars($_GET['courseFilter']) : '' 
                            ?>">Siguiente &raquo;</a>
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