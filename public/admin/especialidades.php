<?php
require_once __DIR__ . '/../../src/database/connection.php';
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/helpers.php';

requireLogin();

$pageTitle = 'Especialidades';
$pdo = getConnection();

$mensaje = '';
$tipoMensaje = '';

// CREAR nueva especialidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $nombre = sanitizar($_POST['nombre'] ?? '');
    $descripcion = sanitizar($_POST['descripcion'] ?? '');
    
    if (!empty($nombre)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO especialidades (nombre, descripcion) VALUES (?, ?)");
            $stmt->execute([$nombre, $descripcion]);
            $mensaje = 'Especialidad creada exitosamente';
            $tipoMensaje = 'success';
        } catch (PDOException $e) {
            $mensaje = 'Error al crear la especialidad';
            $tipoMensaje = 'danger';
        }
    }
}

// EDITAR especialidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $nombre = sanitizar($_POST['nombre'] ?? '');
    $descripcion = sanitizar($_POST['descripcion'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (!empty($nombre) && $id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE especialidades SET nombre = ?, descripcion = ?, activo = ? WHERE id = ?");
            $stmt->execute([$nombre, $descripcion, $activo, $id]);
            $mensaje = 'Especialidad actualizada exitosamente';
            $tipoMensaje = 'success';
        } catch (PDOException $e) {
            $mensaje = 'Error al actualizar la especialidad';
            $tipoMensaje = 'danger';
        }
    }
}

// ELIMINAR especialidad
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Verificar si tiene médicos asociados
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM medicos WHERE especialidad_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetch()['total'];
    
    if ($count > 0) {
        $mensaje = "No se puede eliminar. Hay $count médico(s) con esta especialidad";
        $tipoMensaje = 'warning';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM especialidades WHERE id = ?");
            $stmt->execute([$id]);
            $mensaje = 'Especialidad eliminada exitosamente';
            $tipoMensaje = 'success';
        } catch (PDOException $e) {
            $mensaje = 'Error al eliminar la especialidad';
            $tipoMensaje = 'danger';
        }
    }
}

// Obtener todas las especialidades
$stmt = $pdo->query("
    SELECT e.*, COUNT(m.id) as total_medicos 
    FROM especialidades e
    LEFT JOIN medicos m ON e.id = m.especialidad_id AND m.activo = 1
    GROUP BY e.id
    ORDER BY e.nombre
");
$especialidades = $stmt->fetchAll();

// Obtener especialidad para editar
$editando = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM especialidades WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editando = $stmt->fetch();
}

include __DIR__ . '/../../src/templates/admin-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-clipboard2-pulse"></i> Especialidades Médicas</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item active">Especialidades</li>
        </ol>
    </nav>
</div>

<?php if ($mensaje): ?>
    <?php echo mostrarAlerta($mensaje, $tipoMensaje); ?>
<?php endif; ?>

<div class="row">
    <!-- Formulario -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-<?php echo $editando ? 'pencil' : 'plus-circle'; ?>"></i>
                <?php echo $editando ? 'Editar Especialidad' : 'Nueva Especialidad'; ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editando ? 'edit' : 'create'; ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?php echo $editando ? htmlspecialchars($editando['nombre']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $editando ? htmlspecialchars($editando['descripcion']) : ''; ?></textarea>
                    </div>
                    
                    <?php if ($editando): ?>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                       <?php echo $editando['activo'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">
                                    Especialidad activa
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-<?php echo $editando ? 'check' : 'plus'; ?>-circle"></i>
                            <?php echo $editando ? 'Actualizar' : 'Crear'; ?>
                        </button>
                        <?php if ($editando): ?>
                            <a href="especialidades.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Lista -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> Lista de Especialidades</span>
                <span class="badge bg-primary"><?php echo count($especialidades); ?> registros</span>
            </div>
            <div class="card-body">
                <?php if (empty($especialidades)): ?>
                    <p class="text-muted text-center py-4">No hay especialidades registradas</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Médicos</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($especialidades as $esp): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($esp['nombre']); ?></strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars(substr($esp['descripcion'], 0, 50)); ?>
                                                <?php echo strlen($esp['descripcion']) > 50 ? '...' : ''; ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?php echo $esp['total_medicos']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($esp['activo']): ?>
                                                <span class="badge bg-success">Activa</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactiva</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="especialidades.php?edit=<?php echo $esp['id']; ?>" 
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($esp['total_medicos'] == 0): ?>
                                                    <a href="especialidades.php?delete=<?php echo $esp['id']; ?>" 
                                                       class="btn btn-outline-danger" title="Eliminar"
                                                       onclick="return confirm('¿Eliminar esta especialidad?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php endif; ?>
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
    </div>
</div>

<?php include __DIR__ . '/../../src/templates/admin-footer.php'; ?>
