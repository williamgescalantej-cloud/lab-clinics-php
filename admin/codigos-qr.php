<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/qr-lib.php';

requireLogin();

$pageTitle = 'Códigos QR';
$pdo = getConnection();

// Generar QR masivo
if (isset($_POST['generar_masivo'])) {
    $stmt = $pdo->query("SELECT id FROM medicos WHERE activo = 1");
    $medicos = $stmt->fetchAll();
    
    $generados = 0;
    foreach ($medicos as $medico) {
        if (generarQR($medico['id'])) {
            $generados++;
        }
    }
    
    $mensaje = "Se generaron $generados códigos QR exitosamente";
    $tipoMensaje = 'success';
}

// Obtener médicos con sus QR
$stmt = $pdo->query("
    SELECT m.id, m.nombre_completo, m.foto, e.nombre as especialidad,
           cq.ruta_archivo, cq.fecha_generacion,
           (SELECT COUNT(*) FROM referencias WHERE medico_id = m.id) as total_escaneos
    FROM medicos m
    INNER JOIN especialidades e ON m.especialidad_id = e.id
    LEFT JOIN codigos_qr cq ON m.id = cq.medico_id AND cq.activo = 1
    WHERE m.activo = 1
    ORDER BY m.nombre_completo
");
$medicos = $stmt->fetchAll();

include 'includes/layout-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-qr-code"></i> Gestión de Códigos QR</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item active">Códigos QR</li>
        </ol>
    </nav>
</div>

<?php if (isset($mensaje)): ?>
    <?php echo mostrarAlerta($mensaje, $tipoMensaje); ?>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-lightning-charge"></i> Acciones Rápidas</h5>
                        <p class="text-muted mb-0">Genera códigos QR para todos los médicos activos</p>
                    </div>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="generar_masivo" class="btn btn-primary"
                                onclick="return confirm('¿Generar QR para todos los médicos?')">
                            <i class="bi bi-lightning"></i> Generar QR Masivo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul"></i> Médicos y sus Códigos QR
    </div>
    <div class="card-body">
        <?php if (empty($medicos)): ?>
            <p class="text-muted text-center py-4">No hay médicos registrados</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th class="text-center">Escaneos</th>
                            <th class="text-center">Estado QR</th>
                            <th class="text-center">Generado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicos as $medico): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($medico['foto']): ?>
                                            <img src="<?php echo SITE_URL; ?>/assets/img/medicos/<?php echo $medico['foto']; ?>" 
                                                 alt="Foto" class="rounded-circle me-2" width="40" height="40" 
                                                 style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($medico['nombre_completo']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($medico['especialidad']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?php echo $medico['total_escaneos']; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($medico['ruta_archivo']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Generado</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($medico['fecha_generacion']): ?>
                                        <small class="text-muted"><?php echo formatearFecha($medico['fecha_generacion']); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($medico['ruta_archivo']): ?>
                                            <a href="<?php echo SITE_URL; ?>/assets/qr/<?php echo $medico['ruta_archivo']; ?>" 
                                               class="btn btn-outline-success" download title="Descargar QR">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="medico-qr.php?id=<?php echo $medico['id']; ?>" 
                                           class="btn btn-outline-primary" title="Gestionar QR">
                                            <i class="bi bi-qr-code-scan"></i>
                                        </a>
                                        <a href="<?php echo urlPerfilMedico($medico['id']); ?>" 
                                           target="_blank" class="btn btn-outline-info" title="Ver perfil">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/layout-footer.php'; ?>
