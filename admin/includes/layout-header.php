<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos personalizados del admin -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center">
                <div class="logo-wrapper">
                    <i class="bi bi-hospital-fill"></i>
                </div>
                <div class="brand-text">
                    <h4><?php echo SITE_SHORT_NAME; ?></h4>
                    <small>Connect v<?php echo SITE_VERSION; ?></small>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <!-- Dashboard -->
            <a href="<?php echo SITE_URL; ?>/admin/index.php" 
               class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- Sección: Gestión -->
            <div class="menu-section-title">Gestión</div>
            
            <a href="<?php echo SITE_URL; ?>/admin/medicos.php" 
               class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['medicos.php', 'medico-nuevo.php', 'medico-editar.php', 'medico-eliminar.php', 'medico-qr.php']) ? 'active' : ''; ?>">
                <i class="bi bi-person-badge"></i>
                <span>Médicos</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/especialidades.php" 
               class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'especialidades.php' ? 'active' : ''; ?>">
                <i class="bi bi-clipboard2-pulse"></i>
                <span>Especialidades</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/codigos-qr.php" 
               class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'codigos-qr.php' ? 'active' : ''; ?>">
                <i class="bi bi-qr-code"></i>
                <span>Códigos QR</span>
            </a>
            
            <!-- Sección: Reportes -->
            <div class="menu-section-title">Reportes</div>
            
            <a href="<?php echo SITE_URL; ?>/admin/reportes.php" 
               class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i>
                <span>Estadísticas</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/referencias.php" 
               class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'referencias.php' ? 'active' : ''; ?>">
                <i class="bi bi-activity"></i>
                <span>Referencias</span>
            </a>
            
            <!-- Sección: Sistema -->
            <div class="menu-section-title">Sistema</div>
            
            <a href="<?php echo SITE_URL; ?>/admin/perfil.php" 
               class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i>
                <span>Mi Perfil</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/logout.php" 
               class="menu-item" 
               onclick="return confirm('¿Cerrar sesión?')">
                <i class="bi bi-box-arrow-right"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
        
        <!-- Footer del sidebar -->
        <div class="sidebar-footer">
            
        </div>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <button class="btn btn-link d-md-none" id="toggleSidebar">
                    <i class="bi bi-list" style="font-size: 1.5rem;"></i>
                </button>
            </div>
            <div class="topbar-right">
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <span class="user-name"><?php echo getUserName(); ?></span>
                    <span class="user-badge">Admin</span>
                </div>
            </div>
        </div>
        
        <div class="page-content">
