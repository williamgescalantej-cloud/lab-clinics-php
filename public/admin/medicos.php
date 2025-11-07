<?php
require_once __DIR__ . '/../../src/database/connection.php';
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/helpers.php';
require_once __DIR__ . '/../../src/lib/medicos.php';

requireLogin();

$pageTitle = 'Médicos';
$pdo = getConnection();

// Obtener todos los médicos con sus especialidades
$stmt = $pdo->query("
    SELECT m.*, e.nombre as especialidad_nombre,
           (SELECT COUNT(*) FROM referencias WHERE medico_id = m.id) as total_referencias
    FROM medicos m
    INNER JOIN especialidades e ON m.especialidad_id = e.id
    ORDER BY m.fecha_registro DESC
");
$medicos = $stmt->fetchAll();

// Mensaje de éxito si viene de otra página
$mensaje = $_GET['msg'] ?? '';

include __DIR__ . '/../../src/templates/admin-header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-person-badge"></i> Gestión de Médicos</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Médicos</li>
                </ol>
            </nav>
        </div>
        <a href="medico-nuevo.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Agregar Médico
        </a>
    </div>
</div>

<?php if ($mensaje === 'creado'): ?>
    <?php echo mostrarAlerta('Médico creado exitosamente', 'success'); ?>
<?php elseif ($mensaje === 'actualizado'): ?>
    <?php echo mostrarAlerta('Médico actualizado exitosamente', 'success'); ?>
<?php elseif ($mensaje === 'eliminado'): ?>
    <?php echo mostrarAlerta('Médico eliminado exitosamente', 'success'); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul"></i> Lista de Médicos</span>
        <span class="badge bg-primary"><?php echo count($medicos); ?> registrados</span>
    </div>
    <div class="card-body">
        <?php if (empty($medicos)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #cbd5e1;"></i>
                <p class="text-muted mt-3">No hay médicos registrados</p>
                <a href="medico-nuevo.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Agregar el primero
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th>Contacto</th>
                            <th class="text-center">Referencias</th>
                            <th class="text-center">Estado</th>
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
                                                 alt="Foto" class="rounded-circle me-3" width="48" height="48" 
                                                 style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 48px; height: 48px;">
                                                <i class="bi bi-person text-white fs-5"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong class="d-block"><?php echo htmlspecialchars($medico['nombre_completo']); ?></strong>
                                            <?php if ($medico['email']): ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-envelope"></i> 
                                                    <?php echo htmlspecialchars($medico['email']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <?php echo htmlspecialchars($medico['especialidad_nombre']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($medico['telefono']): ?>
                                        <small>
                                            <i class="bi bi-phone"></i> 
                                            <?php echo htmlspecialchars($medico['telefono']); ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?php echo $medico['total_referencias']; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($medico['activo']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo urlPerfilMedico($medico['id']); ?>" 
                                           class="btn btn-outline-info" target="_blank" 
                                           title="Ver perfil público">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="medico-qr.php?id=<?php echo $medico['id']; ?>" 
                                           class="btn btn-outline-secondary" 
                                           title="Código QR">
                                            <i class="bi bi-qr-code"></i>
                                        </a>
                                        <a href="medico-editar.php?id=<?php echo $medico['id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="medico-eliminar.php?id=<?php echo $medico['id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Eliminar"
                                           onclick="return confirm('¿Está seguro de eliminar este médico?')">
                                            <i class="bi bi-trash"></i>
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

<?php include __DIR__ . '/../../src/templates/admin-footer.php'; ?>
