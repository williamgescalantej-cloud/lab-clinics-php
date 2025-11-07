<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Mi Perfil';
$pdo = getConnection();

$mensaje = '';
$tipoMensaje = '';

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([getUserId()]);
$usuario = $stmt->fetch();

// Actualizar perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = sanitizar($_POST['nombre'] ?? '');
    $email = sanitizar($_POST['email'] ?? '');
    
    if (empty($nombre) || empty($email)) {
        $mensaje = 'Complete todos los campos';
        $tipoMensaje = 'danger';
    } elseif (!validarEmail($email)) {
        $mensaje = 'Email no válido';
        $tipoMensaje = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
            $stmt->execute([$nombre, $email, getUserId()]);
            
            // Actualizar sesión
            $_SESSION['admin_nombre'] = $nombre;
            $_SESSION['admin_email'] = $email;
            
            $mensaje = 'Perfil actualizado exitosamente';
            $tipoMensaje = 'success';
            
            // Recargar datos
            $usuario['nombre'] = $nombre;
            $usuario['email'] = $email;
        } catch (PDOException $e) {
            $mensaje = 'Error al actualizar el perfil';
            $tipoMensaje = 'danger';
        }
    }
}

// Cambiar contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $passwordActual = $_POST['password_actual'] ?? '';
    $passwordNuevo = $_POST['password_nuevo'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    if (empty($passwordActual) || empty($passwordNuevo) || empty($passwordConfirm)) {
        $mensaje = 'Complete todos los campos de contraseña';
        $tipoMensaje = 'danger';
    } elseif ($passwordNuevo !== $passwordConfirm) {
        $mensaje = 'Las contraseñas nuevas no coinciden';
        $tipoMensaje = 'danger';
    } elseif (strlen($passwordNuevo) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipoMensaje = 'danger';
    } else {
        // Verificar contraseña actual
        if (password_verify($passwordActual, $usuario['password_hash'])) {
            try {
                $nuevoHash = password_hash($passwordNuevo, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
                $stmt->execute([$nuevoHash, getUserId()]);
                
                $mensaje = 'Contraseña actualizada exitosamente';
                $tipoMensaje = 'success';
            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar la contraseña';
                $tipoMensaje = 'danger';
            }
        } else {
            $mensaje = 'La contraseña actual es incorrecta';
            $tipoMensaje = 'danger';
        }
    }
}

include 'includes/layout-header.php';
?>

<div class="page-header">
    <h1><i class="bi bi-person-circle"></i> Mi Perfil</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item active">Mi Perfil</li>
        </ol>
    </nav>
</div>

<?php if ($mensaje): ?>
    <?php echo mostrarAlerta($mensaje, $tipoMensaje); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary mx-auto mb-3 d-flex align-items-center justify-content-center" 
                     style="width: 120px; height: 120px;">
                    <i class="bi bi-person text-white" style="font-size: 4rem;"></i>
                </div>
                <h4><?php echo htmlspecialchars($usuario['nombre']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($usuario['email']); ?></p>
                
                <hr>
                
                <div class="text-start">
                    <p class="mb-2">
                        <i class="bi bi-calendar-check text-muted"></i>
                        <strong>Miembro desde:</strong><br>
                        <small class="text-muted"><?php echo formatearFecha($usuario['fecha_creacion']); ?></small>
                    </p>
                    <?php if ($usuario['ultima_sesion']): ?>
                        <p class="mb-0">
                            <i class="bi bi-clock text-muted"></i>
                            <strong>Último acceso:</strong><br>
                            <small class="text-muted"><?php echo formatearFecha($usuario['ultima_sesion']); ?></small>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Actualizar Perfil -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-pencil"></i> Actualizar Información Personal
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="actualizar_perfil" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Cambiar Contraseña -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-lock"></i> Cambiar Contraseña
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="password_actual" 
                               name="password_actual" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password_nuevo" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password_nuevo" 
                                   name="password_nuevo" required>
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="password_confirm" 
                                   name="password_confirm" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="cambiar_password" class="btn btn-warning">
                        <i class="bi bi-key"></i> Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/layout-footer.php'; ?>
