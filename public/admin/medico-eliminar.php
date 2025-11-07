<?php
require_once __DIR__ . '/../../src/database/connection.php';
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/helpers.php';
require_once __DIR__ . '/../../src/lib/medicos.php';

requireLogin();

$pageTitle = 'Eliminar Médico';
$pdo = getConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect(SITE_URL . '/admin/medicos.php');
}

$stmt = $pdo->prepare("SELECT m.*, e.nombre as especialidad FROM medicos m INNER JOIN especialidades e ON m.especialidad_id = e.id WHERE m.id = ?");
$stmt->execute([$id]);
$medico = $stmt->fetch();

if (!$medico) {
    redirect(SITE_URL . '/admin/medicos.php');
}

// Obtener estadísticas
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM referencias WHERE medico_id = ?");
$stmt->execute([$id]);
$totalReferencias = $stmt->fetch()['total'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        // Eliminar imagen
        if ($medico['foto']) {
            eliminarImagen($medico['foto']);
        }
        
        // Eliminar QR
        $stmt = $pdo->prepare("SELECT ruta_archivo FROM codigos_qr WHERE medico_id = ?");
        $stmt->execute([$id]);
        $qrs = $stmt->fetchAll();
        
        foreach ($qrs as $qr) {
            $rutaQR = __DIR__ . '/../assets/qr/' . $qr['ruta_archivo'];
            if (file_exists($rutaQR)) {
                unlink($rutaQR);
            }
        }
        
        // Eliminar médico
        $stmt = $pdo->prepare("DELETE FROM medicos WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect(SITE_URL . '/admin/medicos.php?msg=eliminado');
        
    } catch (PDOException $e) {
        $error = 'Error al eliminar: ' . $e->getMessage();
    }
}

include __DIR__ . '/../../src/templates/admin-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-trash"></i> Confirmar Eliminación</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="medicos.php">Médicos</a></li>
            <li class="breadcrumb-item active">Eliminar</li>
        </ol>
    </nav>
</div>

<?php if (isset($error)): ?>
    <?php echo mostrarAlerta($error, 'danger'); ?>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="alert alert-danger d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
            <div>
                <h5 class="mb-1">¡Atención! Esta acción no se puede deshacer</h5>
                <p class="mb-0">Se eliminarán permanentemente todos los datos asociados a este médico.</p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-person-x"></i> Médico a Eliminar
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <?php if ($medico['foto']): ?>
                            <img src="<?php echo SITE_URL; ?>/assets/img/medicos/<?php echo $medico['foto']; ?>" 
                                 alt="Foto" class="rounded-circle" width="80" height="80" 
                                 style="object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-person text-white fs-2"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <h4 class="mb-1"><?php echo htmlspecialchars($medico['nombre_completo']); ?></h4>
                        <p class="text-muted mb-2">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($medico['especialidad']); ?></span>
                        </p>
                        <?php if ($medico['telefono']): ?>
                            <p class="mb-1">
                                <i class="bi bi-phone"></i> <?php echo htmlspecialchars($medico['telefono']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($medico['email']): ?>
                            <p class="mb-0">
                                <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($medico['email']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle"></i> Datos que se Eliminarán
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-image text-muted fs-3 me-3"></i>
                            <div>
                                <div class="fw-bold">Foto del médico</div>
                                <small class="text-muted">Si existe</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-qr-code text-muted fs-3 me-3"></i>
                            <div>
                                <div class="fw-bold">Códigos QR</div>
                                <small class="text-muted">Todos los generados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock text-muted fs-3 me-3"></i>
                            <div>
                                <div class="fw-bold">Horarios</div>
                                <small class="text-muted">De atención</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-share text-muted fs-3 me-3"></i>
                            <div>
                                <div class="fw-bold">Redes sociales</div>
                                <small class="text-muted">Enlaces</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-activity text-muted fs-3 me-3"></i>
                            <div>
                                <div class="fw-bold"><?php echo $totalReferencias; ?> referencias</div>
                                <small class="text-muted">Registros de escaneos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="medicos.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" name="confirmar" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Sí, Eliminar Permanentemente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../src/templates/admin-footer.php'; ?>
