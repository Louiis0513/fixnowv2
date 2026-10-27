<?php
if (!isset($titulo_pagina)) {
    $titulo_pagina = 'Fix Now';
}
$base = '/FixNow';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo_pagina) ?> | Fix Now</title>
    <link rel="stylesheet" href="<?= $base ?>/css/estilos.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= $base ?>/index.php" class="brand">
            <img src="<?= $base ?>/img/logo.png" alt="Fix Now" class="brand-logo">
            <span class="brand-name">Fix <span>Now</span></span>
        </a>
        <nav class="nav-main">
            <a href="<?= $base ?>/index.php">Inicio</a>
            <a href="<?= $base ?>/tecnicos.php">Técnicos</a>
            <a href="<?= $base ?>/tecnicosmap.php">Mapa</a>
            <?php if (tecnicoLogueado()): ?>
                <a href="<?= $base ?>/mi-cuenta.php">Mi cuenta</a>
                <a href="<?= $base ?>/logout.php">Salir</a>
            <?php else: ?>
                <a href="<?= $base ?>/login.php">Login</a>
                <a href="<?= $base ?>/registro.php" class="nav-cta">Crear</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="site-main">
