<?php
require_once __DIR__ . '/../../src/database/connection.php';
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/helpers.php';
require_once __DIR__ . '/../../src/lib/validation.php';
require_once __DIR__ . '/../../src/lib/medicos.php';

requireLogin();

$pageTitle = 'Nuevo Médico';
$pdo = getConnection();

// Obtener especialidades
$stmt = $pdo->query("SELECT * FROM especialidades WHERE activo = 1 ORDER BY nombre");
$especialidades = $stmt->fetchAll();

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
    if (!empty($telefono) && !validarTelefono($telefono)) {
        $errores[] = 'El teléfono no es válido';
    }
    
    // Procesar imagen
    $nombreImagen = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nombreImagen = subirImagen($_FILES['foto']);
        if (!$nombreImagen) {
            $errores[] = 'Error al subir la imagen. Verifique el formato y tamaño (máx. 5MB)';
        }
    }
    
    // Si no hay errores, insertar
    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO medicos (especialidad_id, nombre_completo, telefono, email, 
                                    direccion_consultorio, numero_consultorio, mapa_url, 
                                    foto, descripcion, años_experiencia) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                $años_experiencia > 0 ? $años_experiencia : null
            ]);
            
            $medicoId = $pdo->lastInsertId();
            
            // Procesar horarios
            if (!empty($_POST['horarios'])) {
                $stmtHorario = $pdo->prepare("
                    INSERT INTO horarios_atencion (medico_id, dia_semana, hora_inicio, hora_fin) 
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($_POST['horarios'] as $horario) {
                    if (!empty($horario['dia']) && !empty($horario['inicio']) && !empty($horario['fin'])) {
                        $stmtHorario->execute([
                            $medicoId,
                            $horario['dia'],
                            $horario['inicio'],
                            $horario['fin']
                        ]);
                    }
                }
            }
            
            // Procesar redes sociales
            if (!empty($_POST['redes'])) {
                $stmtRed = $pdo->prepare("
                    INSERT INTO redes_sociales (medico_id, plataforma, url, orden) 
                    VALUES (?, ?, ?, ?)
                ");
                
                $orden = 1;
                foreach ($_POST['redes'] as $red) {
                    if (!empty($red['plataforma']) && !empty($red['url'])) {
                        $stmtRed->execute([
                            $medicoId,
                            $red['plataforma'],
                            $red['url'],
                            $orden++
                        ]);
                    }
                }
            }
            
            redirect(SITE_URL . '/admin/medicos.php?msg=creado');
            
        } catch (PDOException $e) {
            $errores[] = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../../src/templates/admin-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-plus-circle"></i> Agregar Nuevo Médico</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="medicos.php">Médicos</a></li>
            <li class="breadcrumb-item active">Nuevo</li>
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
                                   value="<?php echo htmlspecialchars($nombre ?? ''); ?>" 
                                   placeholder="Dr. Juan Pérez"
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="especialidad_id" class="form-label">Especialidad *</label>
                            <select class="form-select" id="especialidad_id" name="especialidad_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?php echo $esp['id']; ?>"
                                            <?php echo (isset($especialidad_id) && $especialidad_id == $esp['id']) ? 'selected' : ''; ?>>
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
                                   value="<?php echo htmlspecialchars($telefono ?? ''); ?>" 
                                   placeholder="591-12345678">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                   placeholder="doctor@ejemplo.com">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="años_experiencia" class="form-label">
                                <i class="bi bi-award"></i> Años de Experiencia
                            </label>
                            <input type="number" class="form-control" id="años_experiencia" name="años_experiencia" 
                                   value="<?php echo htmlspecialchars($años_experiencia ?? ''); ?>"
                                   min="0" max="60"
                                   placeholder="15">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="foto" class="form-label">
                                <i class="bi bi-image"></i> Foto del Médico
                            </label>
                            <input type="file" class="form-control" id="foto" name="foto" 
                                   accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted">JPG, PNG, GIF. Max: 5MB</small>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="descripcion" class="form-label">Descripción Profesional</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3" placeholder="Breve descripción de experiencia y especialización..."><?php echo htmlspecialchars($descripcion ?? ''); ?></textarea>
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
                                   value="<?php echo htmlspecialchars($direccion ?? ''); ?>" 
                                   placeholder="Av. Arce #1234, La Paz">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="numero_consultorio" class="form-label">Consultorio/Piso</label>
                            <input type="text" class="form-control" id="numero_consultorio" name="numero_consultorio" 
                                   value="<?php echo htmlspecialchars($numero_consultorio ?? ''); ?>" 
                                   placeholder="Cons. 205, 2do Piso">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="mapa_url" class="form-label">
                                <i class="bi bi-map"></i> URL del Mapa (Google Maps Embed)
                            </label>
                            <input type="url" class="form-control" id="mapa_url" name="mapa_url" 
                                   value="<?php echo htmlspecialchars($mapa_url ?? ''); ?>"
                                   placeholder="https://www.google.com/maps/embed?pb=...">
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
                        <div class="row mb-2 horario-item">
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" name="horarios[0][dia]">
                                    <option value="">Día...</option>
                                    <option value="Lunes">Lunes</option>
                                    <option value="Martes">Martes</option>
                                    <option value="Miércoles">Miércoles</option>
                                    <option value="Jueves">Jueves</option>
                                    <option value="Viernes">Viernes</option>
                                    <option value="Sábado">Sábado</option>
                                    <option value="Domingo">Domingo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="time" class="form-control form-control-sm" 
                                       name="horarios[0][inicio]" placeholder="Inicio">
                            </div>
                            <div class="col-md-3">
                                <input type="time" class="form-control form-control-sm" 
                                       name="horarios[0][fin]" placeholder="Fin">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger remove-horario">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-horario">
                        <i class="bi bi-plus-circle"></i> Agregar Horario
                    </button>
                </div>
            </div>
            
            <!-- Redes Sociales -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-share"></i> Redes Sociales (máximo 3)
                </div>
                <div class="card-body">
                    <div id="redes-container">
                        <div class="row mb-2 red-item">
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" name="redes[0][plataforma]">
                                    <option value="">Plataforma...</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="WhatsApp">WhatsApp</option>
                                    <option value="LinkedIn">LinkedIn</option>
                                    <option value="TikTok">TikTok</option>
                                    <option value="YouTube">YouTube</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="url" class="form-control form-control-sm" 
                                       name="redes[0][url]" placeholder="https://...">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger remove-red">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
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
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> Información
                </div>
                <div class="card-body">
                    <p class="mb-3">Complete la información del médico para crear su perfil.</p>
                    <ul class="small text-muted mb-0">
                        <li>Los campos con * son obligatorios</li>
                        <li>La foto debe ser cuadrada (recomendado: 500x500px)</li>
                        <li>Agregue horarios de atención</li>
                        <li>El mapa mejora la experiencia del paciente</li>
                        <li>Las redes sociales son opcionales</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Médico
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
                    <li class="mb-3">
                        Ve a <a href="https://www.google.com/maps" target="_blank">Google Maps</a>
                    </li>
                    <li class="mb-3">
                        Busca la dirección del consultorio
                    </li>
                    <li class="mb-3">
                        Haz clic en el botón <strong>"Compartir"</strong>
                    </li>
                    <li class="mb-3">
                        Selecciona la pestaña <strong>"Insertar un mapa"</strong>
                    </li>
                    <li class="mb-3">
                        Copia el código que aparece en <code>src="..."</code>
                    </li>
                    <li class="mb-0">
                        Pega solo la URL que está dentro de las comillas en el campo de arriba
                    </li>
                </ol>
                <div class="alert alert-info mt-3 mb-0">
                    <small>
                        <strong>Ejemplo:</strong><br>
                        <code>https://www.google.com/maps/embed?pb=!1m18!1m12...</code>
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

<?php include __DIR__ . '/../../src/templates/admin-footer.php'; ?>
