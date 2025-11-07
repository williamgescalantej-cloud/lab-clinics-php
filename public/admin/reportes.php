<?php
require_once __DIR__ . '/../../src/database/connection.php';
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/helpers.php';

requireLogin();

$pageTitle = 'Reportes';
$pdo = getConnection();

// Estadísticas generales
$stmt = $pdo->query("SELECT COUNT(*) as total FROM medicos WHERE activo = 1");
$totalMedicos = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM referencias");
$totalReferencias = $stmt->fetch()['total'];

// Escaneos por mes (últimos 6 meses)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(fecha_escaneo, '%Y-%m') as mes,
        COUNT(*) as total
    FROM referencias
    WHERE fecha_escaneo >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(fecha_escaneo, '%Y-%m')
    ORDER BY mes ASC
");
$escaneosPorMes = $stmt->fetchAll();

// Especialidades más consultadas
$stmt = $pdo->query("
    SELECT e.nombre, COUNT(r.id) as total
    FROM especialidades e
    INNER JOIN medicos m ON e.id = m.especialidad_id
    LEFT JOIN referencias r ON m.id = r.medico_id
    WHERE m.activo = 1
    GROUP BY e.id
    ORDER BY total DESC
    LIMIT 5
");
$especialidadesTop = $stmt->fetchAll();

// Médicos sin escaneos
$stmt = $pdo->query("
    SELECT m.nombre_completo, e.nombre as especialidad, m.fecha_registro
    FROM medicos m
    INNER JOIN especialidades e ON m.especialidad_id = e.id
    LEFT JOIN referencias r ON m.id = r.medico_id
    WHERE m.activo = 1 AND r.id IS NULL
    ORDER BY m.fecha_registro DESC
");
$medicosSinEscaneos = $stmt->fetchAll();

include __DIR__ . '/../../src/templates/admin-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-graph-up"></i> Reportes y Estadísticas</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item active">Reportes</li>
        </ol>
    </nav>
</div>

<!-- Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5><i class="bi bi-people"></i> Resumen General</h5>
                <div class="row mt-3">
                    <div class="col-6">
                        <div class="text-center">
                            <h2 class="text-primary mb-0"><?php echo $totalMedicos; ?></h2>
                            <small class="text-muted">Médicos Activos</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h2 class="text-success mb-0"><?php echo $totalReferencias; ?></h2>
                            <small class="text-muted">Total Escaneos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5><i class="bi bi-calculator"></i> Promedios</h5>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="text-center">
                            <h2 class="text-info mb-0">
                                <?php echo $totalMedicos > 0 ? number_format($totalReferencias / $totalMedicos, 1) : 0; ?>
                            </h2>
                            <small class="text-muted">Escaneos promedio por médico</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Escaneos por Mes (Últimos 6 meses)
            </div>
            <div class="card-body">
                <?php if (empty($escaneosPorMes)): ?>
                    <p class="text-muted text-center py-4">No hay datos disponibles</p>
                <?php else: ?>
                    <canvas id="chartMeses"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart"></i> Top 5 Especialidades Más Consultadas
            </div>
            <div class="card-body">
                <?php if (empty($especialidadesTop)): ?>
                    <p class="text-muted text-center py-4">No hay datos disponibles</p>
                <?php else: ?>
                    <?php 
                    $maxTotal = max(array_column($especialidadesTop, 'total'));
                    foreach ($especialidadesTop as $esp): 
                        $porcentaje = $maxTotal > 0 ? ($esp['total'] / $maxTotal) * 100 : 0;
                    ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small><strong><?php echo htmlspecialchars($esp['nombre']); ?></strong></small>
                                <small class="text-muted"><?php echo $esp['total']; ?> escaneos</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Médicos sin escaneos -->
<?php if (!empty($medicosSinEscaneos)): ?>
    <div class="card">
        <div class="card-header">
            <i class="bi bi-exclamation-triangle"></i> Médicos sin Escaneos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th>Fecha Registro</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicosSinEscaneos as $medico): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medico['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($medico['especialidad']); ?></td>
                                <td><?php echo formatearFecha($medico['fecha_registro']); ?></td>
                                <td class="text-end">
                                    <a href="medico-qr.php?id=<?php echo $medico['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-qr-code"></i> Ver QR
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../src/templates/admin-footer.php'; ?>
