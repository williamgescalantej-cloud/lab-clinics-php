<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/qr-lib.php';

requireLogin();

$pageTitle = 'Código QR';
$pdo = getConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect(SITE_URL . '/admin/medicos.php');
}

// Obtener datos del médico
$stmt = $pdo->prepare("SELECT m.*, e.nombre as especialidad FROM medicos m INNER JOIN especialidades e ON m.especialidad_id = e.id WHERE m.id = ?");
$stmt->execute([$id]);
$medico = $stmt->fetch();

if (!$medico) {
    redirect(SITE_URL . '/admin/medicos.php');
}

$mensaje = '';
$tipoMensaje = 'info';

// Generar nuevo QR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar'])) {
    $resultado = generarQR($id);
    if ($resultado) {
        $mensaje = 'Código QR generado exitosamente';
        $tipoMensaje = 'success';
    } else {
        $mensaje = 'Error al generar el código QR';
        $tipoMensaje = 'danger';
    }
}

// Obtener QR actual
$qrActual = obtenerQRMedico($id);

// Obtener estadísticas de escaneos
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM referencias WHERE medico_id = ?");
$stmt->execute([$id]);
$totalEscaneos = $stmt->fetch()['total'];

include 'includes/layout-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-qr-code"></i> Código QR</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="medicos.php">Médicos</a></li>
            <li class="breadcrumb-item active">Código QR</li>
        </ol>
    </nav>
</div>

<?php if ($mensaje): ?>
    <?php echo mostrarAlerta($mensaje, $tipoMensaje); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <!-- Info del médico -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person-badge"></i> Información del Médico
            </div>
            <div class="card-body text-center">
                <?php if ($medico['foto']): ?>
                    <img src="<?php echo SITE_URL; ?>/assets/img/medicos/<?php echo $medico['foto']; ?>" 
                         alt="Foto" class="rounded-circle mb-3" width="120" height="120" 
                         style="object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 120px; height: 120px;">
                        <i class="bi bi-person text-white" style="font-size: 3rem;"></i>
                    </div>
                <?php endif; ?>
                
                <h5 class="mb-2"><?php echo htmlspecialchars($medico['nombre_completo']); ?></h5>
                <p class="text-muted mb-3"><?php echo htmlspecialchars($medico['especialidad']); ?></p>
                
                <div class="alert alert-info mb-0">
                    <small>
                        <strong>URL del perfil:</strong><br>
                        <a href="<?php echo urlPerfilMedico($id); ?>" target="_blank" class="text-break">
                            <?php echo urlPerfilMedico($id); ?>
                        </a>
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart"></i> Estadísticas
            </div>
            <div class="card-body text-center">
                <h2 class="text-primary mb-1"><?php echo $totalEscaneos; ?></h2>
                <p class="text-muted mb-0">Total de escaneos</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8 mb-4">
        <!-- Código QR -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-qr-code-scan"></i> Código QR Generado
            </div>
            <div class="card-body">
                <?php if ($qrActual): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center p-4 bg-light rounded">
                                <img src="<?php echo SITE_URL; ?>/assets/qr/<?php echo $qrActual; ?>" 
                                     alt="Código QR" class="img-fluid" 
                                     style="max-width: 300px;">
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div>
                                <h5><i class="bi bi-check-circle-fill text-success"></i> QR Activo</h5>
                                <p class="text-muted">Este código QR está listo para usar. Puedes descargarlo e imprimirlo.</p>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo SITE_URL; ?>/assets/qr/<?php echo $qrActual; ?>" 
                                       download="QR_<?php echo sanitizar($medico['nombre_completo']); ?>.png" 
                                       class="btn btn-success">
                                        <i class="bi bi-download"></i> Descargar QR (PNG)
                                    </a>
                                    <a href="<?php echo urlPerfilMedico($id); ?>" 
                                       target="_blank" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> Ver Perfil Público
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-qr-code" style="font-size: 4rem; color: #cbd5e1;"></i>
                        <p class="text-muted mt-3">No hay código QR generado para este médico.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Instrucciones -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightbulb"></i> Instrucciones de Uso
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">Genera o regenera el código QR usando el botón de abajo</li>
                    <li class="mb-2">Descarga la imagen del código QR</li>
                    <li class="mb-2">Imprime el código QR en tamaño adecuado (recomendado: 10x10 cm mínimo)</li>
                    <li class="mb-2">Coloca el QR en las ventanillas o áreas visibles del laboratorio</li>
                    <li class="mb-0">Los pacientes podrán escanear el QR con su celular y ver el perfil del médico</li>
                </ol>
            </div>
        </div>
        
        <!-- Botones de acción -->
        <div class="card mt-4">
            <div class="card-body">
                <form method="POST">
                    <div class="d-flex gap-2">
                        <button type="submit" name="generar" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat"></i> <?php echo $qrActual ? 'Regenerar' : 'Generar'; ?> Código QR
                        </button>
                        <a href="medico-editar.php?id=<?php echo $id; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Editar Médico
                        </a>
                        <a href="medicos.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/layout-footer.php'; ?>
