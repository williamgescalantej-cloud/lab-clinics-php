<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Médico'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Estilos personalizados públicos -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/public.css">
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo htmlspecialchars($medico['especialidad_nombre'] ?? ''); ?> - <?php echo SITE_NAME; ?>">
</head>
<body>
