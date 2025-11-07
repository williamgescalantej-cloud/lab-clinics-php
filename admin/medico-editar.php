<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Editar Médico';
$pdo = getConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect(SITE_URL . '/admin/medicos.php');
}

// Obtener datos del médico
$stmt = $pdo->prepare("SELECT * FROM medicos WHERE id = ?");
$stmt->execute([$id]);
$medico = $stmt->fetch();

if (!$medico) {
    redirect(SITE_URL . '/admin/medicos.php');
}

// Obtener especialidades
$stmt = $pdo->query("SELECT * FROM especialidades WHERE activo = 1 ORDER BY nombre");
$especialidades = $stmt->fetchAll();

// Obtener horarios
$stmt = $pdo->prepare("SELECT * FROM horarios_atencion WHERE medico_id = ? ORDER BY id");
$stmt->execute([$id]);
$horarios = $stmt->fetchAll();

// Obtener redes sociales
$stmt = $pdo->prepare("SELECT * FROM redes_sociales WHERE medico_id = ? ORDER BY orden");
$stmt->execute([$id]);
$redes = $stmt->fetchAll();

$errores = [];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitizar($_POST['nombre'] ?? '');
    $especialidad_id = (int)($_POST['especialidad_id'] ?? 0);
    $telefono = sanitizar($_POST['telefono'] ?? '');
    $email = sanitizar($_POST['email'] ?? '');
    $direccion = sanitizar($_POST['direccion'] ?? '');
    $numero_consultorio = sanitizar($_POST['numero_consultorio'] ?? '');
    $mapa_url = sanitizar($_POST['mapa_url'] ?? '');
    $descripcion = sanitizar($_POST['descripcion'] ?? '');
    $años_experiencia = (int)($_POST['años_experiencia'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio';
    }
    if ($especialidad_id <= 0) {
        $errores[] = 'Debe seleccionar una especialidad';
    }
    if (!empty($email) && !validarEmail($email)) {
        $errores[] = 'El email no es válido';
    }
    
    // Procesar imagen
    $nombreImagen = $medico['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nuevaImagen = subirImagen($_FILES['foto']);
        if ($nuevaImagen) {
            if ($nombreImagen) {
                eliminarImagen($nombreImagen);
            }
            $nombreImagen = $nuevaImagen;
        } else {
            $errores[] = 'Error al subir la imagen';
        }
    }
    
    // Si no hay errores, actualizar
    if (empty($errores)) {
        try {
            $pdo->beginTransaction();
            
            // Actualizar médico
            $stmt = $pdo->prepare("
                UPDATE medicos 
                SET especialidad_id = ?, nombre_completo = ?, telefono = ?, email = ?, 
                    direccion_consultorio = ?, numero_consultorio = ?, mapa_url = ?,
                    foto = ?, descripcion = ?, años_experiencia = ?, activo = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $especialidad_id,
                $nombre,
                $telefono ?: null,
                $email ?: null,
                $direccion ?: null,
                $numero_consultorio ?: null,
                $mapa_url ?: null,
                $nombreImagen,
                $descripcion ?: null,
                $años_experiencia > 0 ? $años_experiencia : null,
                $activo,
                $id
            ]);
            
            // Eliminar horarios anteriores
            $stmt = $pdo->prepare("DELETE FROM horarios_atencion WHERE medico_id = ?");
            $stmt->execute([$id]);
            
            // Insertar nuevos horarios
            if (!empty($_POST['horarios'])) {
                $stmtHorario = $pdo->prepare("
                    INSERT INTO horarios_atencion (medico_id, dia_semana, hora_inicio, hora_fin) 
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($_POST['horarios'] as $horario) {
                    if (!empty($horario['dia']) && !empty($horario['inicio']) && !empty($horario['fin'])) {
                        $stmtHorario->execute([
                            $id,
                            $horario['dia'],
                            $horario['inicio'],
                            $horario['fin']
                        ]);
                    }
                }
            }
            
            // Eliminar redes anteriores
            $stmt = $pdo->prepare("DELETE FROM redes_sociales WHERE medico_id = ?");
            $stmt->execute([$id]);
            
            // Insertar nuevas redes
            if (!empty($_POST['redes'])) {
                $stmtRed = $pdo->prepare("
                    INSERT INTO redes_sociales (medico_id, plataforma, url, orden) 
                    VALUES (?, ?, ?, ?)
                ");
                
                $orden = 1;
                foreach ($_POST['redes'] as $red) {
                    if (!empty($red['plataforma']) && !empty($red['url'])) {
                        $stmtRed->execute([
                            $id,
                            $red['plataforma'],
                            $red['url'],
                            $orden++
                        ]);
                    }
                }
            }
            
            $pdo->commit();
            redirect(SITE_URL . '/admin/medicos.php?msg=actualizado');
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errores[] = 'Error al actualizar: ' . $e->getMessage();
        }
    }
}

include 'includes/layout-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-pencil"></i> Editar Médico</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="medicos.php">Médicos</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
        <h5><i class="bi bi-exclamation-triangle"></i> Errores encontrados:</h5>
        <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8">
            <!-- Información Básica -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person"></i> Información Básica
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($medico['nombre_completo']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="especialidad_id" class="form-label">Especialidad *</label>
                            <select class="form-select" id="especialidad_id" name="especialidad_id" required>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?php echo $esp['id']; ?>"
                                            <?php echo ($medico['especialidad_id'] == $esp['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($esp['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">
                                <i class="bi bi-phone"></i> Teléfono
                            </label>
                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                   value="<?php echo htmlspecialchars($medico['telefono'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($medico['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="años_experiencia" class="form-label">
                                <i class="bi bi-award"></i> Años de Experiencia
                            </label>
                            <input type="number" class="form-control" id="años_experiencia" name="años_experiencia" 
                                   value="<?php echo htmlspecialchars($medico['años_experiencia'] ?? ''); ?>"
                                   min="0" max="60">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="foto" class="form-label">
                                <i class="bi bi-image"></i> Foto del Médico
                            </label>
                            <?php if ($medico['foto']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo SITE_URL; ?>/assets/img/medicos/<?php echo $medico['foto']; ?>" 
                                         alt="Foto actual" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="foto" name="foto" 
                                   accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted">Dejar vacío para mantener la foto actual</small>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="descripcion" class="form-label">Descripción Profesional</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3"><?php echo htmlspecialchars($medico['descripcion'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                       <?php echo $medico['activo'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">
                                    Médico activo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ubicación del Consultorio -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt"></i> Ubicación del Consultorio
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?php echo htmlspecialchars($medico['direccion_consultorio'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="numero_consultorio" class="form-label">Consultorio/Piso</label>
                            <input type="text" class="form-control" id="numero_consultorio" name="numero_consultorio" 
                                   value="<?php echo htmlspecialchars($medico['numero_consultorio'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="mapa_url" class="form-label">
                                <i class="bi bi-map"></i> URL del Mapa (Google Maps Embed)
                            </label>
                            <input type="url" class="form-control" id="mapa_url" name="mapa_url" 
                                   value="<?php echo htmlspecialchars($medico['mapa_url'] ?? ''); ?>">
                            <small class="text-muted">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#mapaModal">
                                    ¿Cómo obtener la URL del mapa?
                                </a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Horarios -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-clock"></i> Horarios de Atención
                </div>
                <div class="card-body">
                    <div id="horarios-container">
                        <?php if (!empty($horarios)): ?>
                            <?php foreach ($horarios as $index => $horario): ?>
                                <div class="row mb-2 horario-item">
                                    <div class="col-md-4">
                                        <select class="form-select form-select-sm" name="horarios[<?php echo $index; ?>][dia]">
                                            <option value="">Día...</option>
                                            <?php foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia): ?>
                                                <option value="<?php echo $dia; ?>" 
                                                        <?php echo ($horario['dia_semana'] === $dia) ? 'selected' : ''; ?>>
                                                    <?php echo $dia; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="time" class="form-control form-control-sm" 
                                               name="horarios[<?php echo $index; ?>][inicio]" 
                                               value="<?php echo substr($horario['hora_inicio'], 0, 5); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="time" class="form-control form-control-sm" 
                                               name="horarios[<?php echo $index; ?>][fin]" 
                                               value="<?php echo substr($horario['hora_fin'], 0, 5); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger remove-horario">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="row mb-2 horario-item">
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" name="horarios[0][dia]">
                                        <option value="">Día...</option>
                                        <?php foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia): ?>
                                            <option value="<?php echo $dia; ?>"><?php echo $dia; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="time" class="form-control form-control-sm" name="horarios[0][inicio]">
                                </div>
                                <div class="col-md-3">
                                    <input type="time" class="form-control form-control-sm" name="horarios[0][fin]">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-horario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-horario">
                        <i class="bi bi-plus-circle"></i> Agregar Horario
                    </button>
                </div>
            </div>
            
            <!-- Redes Sociales -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-share"></i> Redes Sociales
                </div>
                <div class="card-body">
                    <div id="redes-container">
                        <?php if (!empty($redes)): ?>
                            <?php foreach ($redes as $index => $red): ?>
                                <div class="row mb-2 red-item">
                                    <div class="col-md-4">
                                        <select class="form-select form-select-sm" name="redes[<?php echo $index; ?>][plataforma]">
                                            <option value="">Plataforma...</option>
                                            <?php foreach (['Facebook','Instagram','WhatsApp','LinkedIn','TikTok','YouTube'] as $plat): ?>
                                                <option value="<?php echo $plat; ?>" 
                                                        <?php echo ($red['plataforma'] === $plat) ? 'selected' : ''; ?>>
                                                    <?php echo $plat; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="url" class="form-control form-control-sm" 
                                               name="redes[<?php echo $index; ?>][url]" 
                                               value="<?php echo htmlspecialchars($red['url']); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger remove-red">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="row mb-2 red-item">
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" name="redes[0][plataforma]">
                                        <option value="">Plataforma...</option>
                                        <?php foreach (['Facebook','Instagram','WhatsApp','LinkedIn','TikTok','YouTube'] as $plat): ?>
                                            <option value="<?php echo $plat; ?>"><?php echo $plat; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="url" class="form-control form-control-sm" name="redes[0][url]">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-red">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-red">
                        <i class="bi bi-plus-circle"></i> Agregar Red Social
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php if ($medico['foto']): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/img/medicos/<?php echo $medico['foto']; ?>" 
                             alt="Preview" class="img-thumbnail mb-3" style="max-width: 200px;">
                    <?php endif; ?>
                    <h5><?php echo htmlspecialchars($medico['nombre_completo']); ?></h5>
                    <p class="text-muted mb-3">Registrado: <?php echo formatearFecha($medico['fecha_registro']); ?></p>
                    <a href="<?php echo urlPerfilMedico($id); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-eye"></i> Ver Perfil Público
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Actualizar Médico
                        </button>
                        <a href="medicos.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal: Cómo obtener URL de Google Maps -->
<div class="modal fade" id="mapaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-question-circle"></i> ¿Cómo obtener la URL del mapa de Google Maps?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ol class="mb-0">
                    <li class="mb-3">Ve a <a href="https://www.google.com/maps" target="_blank">Google Maps</a></li>
                    <li class="mb-3">Busca la dirección del consultorio</li>
                    <li class="mb-3">Haz clic en el botón <strong>"Compartir"</strong></li>
                    <li class="mb-3">Selecciona la pestaña <strong>"Insertar un mapa"</strong></li>
                    <li class="mb-3">Copia el código que aparece en <code>src="..."</code></li>
                    <li class="mb-0">Pega solo la URL que está dentro de las comillas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

<?php include 'includes/layout-footer.php'; ?>
