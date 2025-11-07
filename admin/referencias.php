<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Referencias';
$pdo = getConnection();

// Paginación
$porPagina = 20;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $porPagina;

// Filtros
$filtroMedico = $_GET['medico'] ?? '';
$filtroFecha = $_GET['fecha'] ?? '';

// Construir query
$where = [];
$params = [];

if ($filtroMedico) {
    $where[] = "m.id = ?";
    $params[] = $filtroMedico;
}

if ($filtroFecha) {
    $where[] = "DATE(r.fecha_escaneo) = ?";
    $params[] = $filtroFecha;
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Total registros
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM referencias r INNER JOIN medicos m ON r.medico_id = m.id $whereClause");
$stmt->execute($params);
$totalRegistros = $stmt->fetch()['total'];
$totalPaginas = ceil($totalRegistros / $porPagina);

// Obtener referencias
$stmt = $pdo->prepare("
    SELECT r.*, m.nombre_completo as medico_nombre, m.foto, e.nombre as especialidad
    FROM referencias r
    INNER JOIN medicos m ON r.medico_id = m.id
    INNER JOIN especialidades e ON m.especialidad_id = e.id
    $whereClause
    ORDER BY r.fecha_escaneo DESC
    LIMIT ? OFFSET ?
");
$stmt->execute(array_merge($params, [$porPagina, $offset]));
$referencias = $stmt->fetchAll();

// Lista de médicos para filtro
$medicos = $pdo->query("SELECT id, nombre_completo FROM medicos WHERE activo = 1 ORDER BY nombre_completo")->fetchAll();

include 'includes/layout-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-activity"></i> Referencias y Escaneos</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item active">Referencias</li>
        </ol>
    </nav>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Médico</label>
                <select name="medico" class="form-select">
                    <option value="">Todos los médicos</option>
                    <?php foreach ($medicos as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo $filtroMedico == $m['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['nombre_completo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($filtroFecha); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="referencias.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul"></i> Registro de Escaneos</span>
        <span class="badge bg-primary"><?php echo $totalRegistros; ?> registros</span>
    </div>
    <div class="card-body">
        <?php if (empty($referencias)): ?>
            <p class="text-muted text-center py-4">No hay referencias registradas</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th>IP</th>
                            <th>Navegador</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referencias as $ref): ?>
                            <tr>
                                <td>
                                    <small><?php echo formatearFecha($ref['fecha_escaneo']); ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($ref['foto']): ?>
                                            <img src="<?php echo SITE_URL; ?>/assets/img/medicos/<?php echo $ref['foto']; ?>" 
                                                 alt="Foto" class="rounded-circle me-2" width="32" height="32" 
                                                 style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="bi bi-person text-white small"></i>
                                            </div>
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($ref['medico_nombre']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($ref['especialidad']); ?></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($ref['ip_visitante']); ?></small></td>
                                <td>
                                    <small class="text-muted" title="<?php echo htmlspecialchars($ref['user_agent']); ?>">
                                        <?php echo htmlspecialchars(substr($ref['user_agent'], 0, 30)); ?>...
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo $filtroMedico ? '&medico='.$filtroMedico : ''; ?><?php echo $filtroFecha ? '&fecha='.$filtroFecha : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/layout-footer.php'; ?>
