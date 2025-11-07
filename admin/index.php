<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Dashboard';
$pdo = getConnection();

// Estadísticas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM medicos WHERE activo = 1");
$totalMedicos = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM especialidades WHERE activo = 1");
$totalEspecialidades = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM referencias");
$totalReferencias = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM referencias WHERE DATE(fecha_escaneo) = CURDATE()");
$referenciasHoy = $stmt->fetch()['total'];

// Top médicos
$stmt = $pdo->query("
    SELECT m.id, m.nombre_completo, m.foto, e.nombre as especialidad, COUNT(r.id) as total_escaneos
    FROM medicos m
    LEFT JOIN especialidades e ON m.especialidad_id = e.id
    LEFT JOIN referencias r ON m.id = r.medico_id
    WHERE m.activo = 1
    GROUP BY m.id
    ORDER BY total_escaneos DESC
    LIMIT 5
");
$topMedicos = $stmt->fetchAll();

include 'includes/layout-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Inicio</li>
        </ol>
    </nav>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card" style="--gradient-start: #0ea5e9; --gradient-end: #0284c7;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 mb-1">Médicos Activos</div>
                        <h3><?php echo $totalMedicos; ?></h3>
                    </div>
                    <i class="bi bi-person-badge icon"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card" style="--gradient-start: #8b5cf6; --gradient-end: #7c3aed;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 mb-1">Especialidades</div>
                        <h3><?php echo $totalEspecialidades; ?></h3>
                    </div>
                    <i class="bi bi-clipboard2-pulse icon"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card" style="--gradient-start: #10b981; --gradient-end: #059669;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 mb-1">Total Escaneos</div>
                        <h3><?php echo $totalReferencias; ?></h3>
                    </div>
                    <i class="bi bi-qr-code-scan icon"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card" style="--gradient-start: #f59e0b; --gradient-end: #d97706;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 mb-1">Escaneos Hoy</div>
                        <h3><?php echo $referenciasHoy; ?></h3>
                    </div>
                    <i class="bi bi-calendar-check icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy"></i> Top 5 Médicos Más Consultados
            </div>
            <div class="card-body">
                <?php if (empty($topMedicos)): ?>
                    <p class="text-muted text-center py-4">No hay datos disponibles</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Médico</th>
                                    <th>Especialidad</th>
                                    <th class="text-end">Escaneos</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topMedicos as $index => $medico): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($medico['foto']): ?>
                                                    <img src="<?php echo SITE_URL; ?>/assets/img/medicos/<?php echo $medico['foto']; ?>" 
                                                         alt="Foto" class="rounded-circle me-2" width="32" height="32" 
                                                         style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 32px; height: 32px;">
                                                        <i class="bi bi-person text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <strong><?php echo htmlspecialchars($medico['nombre_completo']); ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($medico['especialidad']); ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-success"><?php echo $medico['total_escaneos']; ?></span>
                                        </td>
                                        <td class="text-end">
                                            <a href="medico-editar.php?id=<?php echo $medico['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Accesos Rápidos
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="medico-nuevo.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Agregar Médico
                    </a>
                    <a href="especialidades.php?action=new" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Nueva Especialidad
                    </a>
                    <a href="reportes.php" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-bar-graph"></i> Ver Reportes
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Información del Sistema
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Versión:</strong> 1.0.0</p>
                <p class="mb-2"><strong>Usuario:</strong> <?php echo getUserName(); ?></p>
                <p class="mb-0"><strong>Último acceso:</strong> Ahora</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/layout-footer.php'; ?>
