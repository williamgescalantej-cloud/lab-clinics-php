<?php
require_once __DIR__ . '/../src/database/connection.php';
require_once __DIR__ . '/../src/lib/helpers.php';
require_once __DIR__ . '/../src/lib/medicos.php';

$pdo = getConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    include __DIR__ . '/../src/templates/error-404.php';
    exit;
}

// Obtener datos del médico
$stmt = $pdo->prepare("
    SELECT m.*, e.nombre as especialidad_nombre
    FROM medicos m
    INNER JOIN especialidades e ON m.especialidad_id = e.id
    WHERE m.id = ? AND m.activo = 1
");
$stmt->execute([$id]);
$medico = $stmt->fetch();

if (!$medico) {
    http_response_code(404);
    include __DIR__ . '/../src/templates/error-404.php';
    exit;
}

// Obtener horarios agrupados por día
$stmt = $pdo->prepare("
    SELECT * FROM horarios_atencion 
    WHERE medico_id = ? 
    ORDER BY FIELD(dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')
");
$stmt->execute([$id]);
$horarios = $stmt->fetchAll();

// Obtener redes sociales
$stmt = $pdo->prepare("SELECT * FROM redes_sociales WHERE medico_id = ? ORDER BY orden");
$stmt->execute([$id]);
$redes = $stmt->fetchAll();

// Registrar visita
try {
    $stmt = $pdo->prepare("INSERT INTO referencias (medico_id, ip_visitante, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([$id, obtenerIP(), obtenerUserAgent()]);
} catch (PDOException $e) {
    error_log("Error registrando referencia: " . $e->getMessage());
}

// Iconos y colores para redes sociales
$redesInfo = [
    'Facebook' => ['icon' => 'bi-facebook', 'color' => '#1877f2', 'name' => 'Facebook'],
    'Instagram' => ['icon' => 'bi-instagram', 'color' => '#e4405f', 'name' => 'Instagram'],
    'WhatsApp' => ['icon' => 'bi-whatsapp', 'color' => '#25d366', 'name' => 'WhatsApp'],
    'LinkedIn' => ['icon' => 'bi-linkedin', 'color' => '#0077b5', 'name' => 'LinkedIn'],
    'TikTok' => ['icon' => 'bi-tiktok', 'color' => '#000000', 'name' => 'TikTok'],
    'YouTube' => ['icon' => 'bi-youtube', 'color' => '#ff0000', 'name' => 'YouTube']
];

// Días de la semana
$diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

// Agrupar horarios por día
$horariosPorDia = [];
foreach ($horarios as $horario) {
    $horariosPorDia[$horario['dia_semana']][] = $horario;
}

$pageTitle = htmlspecialchars($medico['nombre_completo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/medico-profile.css">
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo htmlspecialchars($medico['especialidad_nombre']); ?> - <?php echo SITE_NAME; ?>">
    <meta name="theme-color" content="#0066FF">
</head>
<body>
    <!-- Header compacto -->
    <div class="top-bar">
        <div class="container-fluid px-3">
            <div class="d-flex align-items-center justify-content-between py-2">
                <div class="brand">
                    <i class="bi bi-hospital"></i>
                    <span><?php echo SITE_NAME; ?></span>
                </div>
                <div class="verified-mini">
                    <i class="bi bi-patch-check-fill"></i>
                    <span>Verificado</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Perfil del médico -->
    <div class="profile-container">
        <div class="container-fluid px-3 py-4">
            <!-- Card principal -->
            <div class="doctor-card">
                <!-- Foto y nombre -->
                <div class="doctor-header">
                    <div class="doctor-photo-wrapper">
                        <?php if ($medico['foto']): ?>
                            <img src="<?php echo SITE_URL; ?>/assets/img/medicos/<?php echo $medico['foto']; ?>" 
                                 alt="<?php echo $pageTitle; ?>" 
                                 class="doctor-photo">
                        <?php else: ?>
                            <div class="doctor-photo-placeholder">
                                <i class="bi bi-person"></i>
                            </div>
                        <?php endif; ?>
                        <div class="status-badge">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                    
                    <div class="doctor-info">
                        <h1 class="doctor-name"><?php echo $pageTitle; ?></h1>
                        <div class="doctor-specialty">
                            <i class="bi bi-stethoscope"></i>
                            <?php echo htmlspecialchars($medico['especialidad_nombre']); ?>
                        </div>
                        
                        <?php if ($medico['años_experiencia']): ?>
                            <div class="doctor-experience">
                                <i class="bi bi-award"></i>
                                <?php echo $medico['años_experiencia']; ?> años de experiencia
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($medico['descripcion']): ?>
                    <!-- Descripción -->
                    <div class="section-card">
                        <div class="section-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <div class="section-content">
                            <h3 class="section-title">Sobre mí</h3>
                            <p class="section-text"><?php echo nl2br(htmlspecialchars($medico['descripcion'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Información de contacto -->
                <div class="section-card">
                    <div class="section-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div class="section-content">
                        <h3 class="section-title">Contacto</h3>
                        <div class="contact-list">
                            <?php if ($medico['telefono']): ?>
                                <a href="tel:<?php echo htmlspecialchars($medico['telefono']); ?>" class="contact-item">
                                    <div class="contact-icon phone">
                                        <i class="bi bi-telephone"></i>
                                    </div>
                                    <div class="contact-details">
                                        <span class="contact-label">Teléfono</span>
                                        <span class="contact-value"><?php echo htmlspecialchars($medico['telefono']); ?></span>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($medico['email']): ?>
                                <a href="mailto:<?php echo htmlspecialchars($medico['email']); ?>" class="contact-item">
                                    <div class="contact-icon email">
                                        <i class="bi bi-envelope"></i>
                                    </div>
                                    <div class="contact-details">
                                        <span class="contact-label">Email</span>
                                        <span class="contact-value"><?php echo htmlspecialchars($medico['email']); ?></span>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Horarios -->
                <?php if (!empty($horarios)): ?>
                    <div class="section-card">
                        <div class="section-icon">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <div class="section-content">
                            <h3 class="section-title">Horarios de Atención</h3>
                            <div class="schedule-list">
                                <?php foreach ($diasSemana as $dia): 
                                    $tieneHorario = isset($horariosPorDia[$dia]);
                                    $hoy = date('l');
                                    $diasIngles = ['Lunes' => 'Monday', 'Martes' => 'Tuesday', 'Miércoles' => 'Wednesday', 
                                                   'Jueves' => 'Thursday', 'Viernes' => 'Friday', 'Sábado' => 'Saturday', 'Domingo' => 'Sunday'];
                                    $esHoy = ($hoy === $diasIngles[$dia]);
                                ?>
                                    <div class="schedule-day <?php echo $tieneHorario ? 'active' : 'closed'; ?> <?php echo $esHoy ? 'today' : ''; ?>">
                                        <div class="day-name">
                                            <span class="day-dot"></span>
                                            <?php echo $dia; ?>
                                            <?php if ($esHoy): ?>
                                                <span class="today-badge">Hoy</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="day-hours">
                                            <?php if ($tieneHorario): ?>
                                                <?php foreach ($horariosPorDia[$dia] as $h): ?>
                                                    <span class="hour-tag">
                                                        <?php echo substr($h['hora_inicio'], 0, 5); ?> - 
                                                        <?php echo substr($h['hora_fin'], 0, 5); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="closed-text">Cerrado</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Ubicación -->
                <?php if ($medico['direccion_consultorio']): ?>
                    <div class="section-card">
                        <div class="section-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="section-content">
                            <h3 class="section-title">Ubicación</h3>
                            <div class="location-info">
                                <div class="location-text">
                                    <i class="bi bi-building"></i>
                                    <div>
                                        <strong><?php echo htmlspecialchars($medico['direccion_consultorio']); ?></strong>
                                        <?php if ($medico['numero_consultorio']): ?>
                                            <span class="location-detail"><?php echo htmlspecialchars($medico['numero_consultorio']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($medico['mapa_url']): ?>
                                    <div class="map-wrapper">
                                        <iframe 
                                            src="<?php echo htmlspecialchars($medico['mapa_url']); ?>"
                                            width="100%" 
                                            height="250"
                                            style="border:0; border-radius: 12px;" 
                                            allowfullscreen="" 
                                            loading="lazy">
                                        </iframe>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Redes Sociales -->
                <?php if (!empty($redes)): ?>
                    <div class="section-card">
                        <div class="section-icon">
                            <i class="bi bi-share-fill"></i>
                        </div>
                        <div class="section-content">
                            <h3 class="section-title">Redes Sociales</h3>
                            <div class="social-list">
                                <?php foreach ($redes as $red): 
                                    $info = $redesInfo[$red['plataforma']] ?? ['icon' => 'bi-link', 'color' => '#6c757d', 'name' => $red['plataforma']];
                                ?>
                                    <a href="<?php echo htmlspecialchars($red['url']); ?>" 
                                       target="_blank" 
                                       class="social-link"
                                       style="--social-color: <?php echo $info['color']; ?>">
                                        <div class="social-icon">
                                            <i class="bi <?php echo $info['icon']; ?>"></i>
                                        </div>
                                        <span class="social-name"><?php echo $info['name']; ?></span>
                                        <i class="bi bi-box-arrow-up-right social-arrow"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Botón de acción flotante -->
            <?php if ($medico['telefono']): ?>
                <div class="floating-action">
                    <a href="tel:<?php echo htmlspecialchars($medico['telefono']); ?>" class="btn-call-floating">
                        <i class="bi bi-telephone-fill"></i>
                        <span>Llamar Ahora</span>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="footer-info">
                <div class="verified-badge">
                    <i class="bi bi-shield-check"></i>
                    <span>Médico verificado por <?php echo SITE_NAME; ?></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
