<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfLogged();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizar($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Complete todos los campos';
    } else {
        $usuario = verificarLogin($email, $password);
        
        if ($usuario) {
            loginAdmin($usuario);
            redirect(SITE_URL . '/admin/index.php');
        } else {
            $error = 'Credenciales incorrectas';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }
        
        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            color: white;
            padding: 40px 32px;
            text-align: center;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .logo-circle i {
            font-size: 36px;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .login-body {
            padding: 40px 32px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #e8ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 15px;
            height: 56px;
        }
        
        .form-control:focus {
            border-color: #0066FF;
            box-shadow: 0 0 0 4px rgba(0, 102, 255, 0.1);
        }
        
        .form-floating label {
            padding: 16px;
        }
        
        .btn-login {
            width: 100%;
            height: 56px;
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 8px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 102, 255, 0.3);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px;
            margin-bottom: 24px;
        }
        
        .alert-danger {
            background: #FEE;
            color: #C00;
        }
        
        .divider {
            text-align: center;
            margin: 24px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e8ecef;
        }
        
        .divider span {
            background: white;
            padding: 0 16px;
            position: relative;
            font-size: 13px;
            color: #999;
        }
        
        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
        
        .info-box small {
            display: block;
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .info-box strong {
            color: #0066FF;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 24px;
            color: white;
            font-size: 13px;
        }
        
        .footer-text a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-circle">
                    <i class="bi bi-hospital"></i>
                </div>
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Sistema de Gestión de Referencias Médicas</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                               placeholder="nombre@ejemplo.com"
                               required autofocus>
                        <label for="email">
                            <i class="bi bi-envelope"></i> Correo Electrónico
                        </label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Contraseña"
                               required>
                        <label for="password">
                            <i class="bi bi-lock"></i> Contraseña
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Iniciar Sesión
                    </button>
                </form>
                
                <div class="divider">
                    <span>Acceso seguro</span>
                </div>
                
                <div class="info-box">
                    <small>
                        <i class="bi bi-shield-check text-success"></i><br>
                        Sistema exclusivo para personal autorizado de <strong><?php echo LAB_NAME; ?></strong>
                    </small>
                </div>
            </div>
        </div>
        
        <div class="footer-text">
            © <?php echo date('Y'); ?> <?php echo LAB_NAME; ?>. Todos los derechos reservados.<br>
            <small>Versión <?php echo SITE_VERSION; ?></small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
