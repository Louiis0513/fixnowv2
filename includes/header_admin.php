<?php
if (!isset($titulo_pagina)) {
    $titulo_pagina = 'Admin';
}
$base = '/FixNow';
$admin_nombre = $_SESSION['admin_nombre'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo_pagina) ?> | Fix Now Admin</title>
    <link rel="stylesheet" href="<?= $base ?>/css/estilos.css">
</head>
<body class="admin-body">
<header class="admin-header">
    <div class="container header-inner">
        <a href="<?= $base ?>/admin/index.php" class="brand">
            <img src="<?= $base ?>/img/logo.png" alt="Fix Now" class="brand-logo">
            <span class="brand-name">Fix <span>Now</span> <span class="brand-admin-label">Admin</span></span>
        </a>
        <nav class="nav-admin-bar" aria-label="Administración">
            <a href="<?= $base ?>/admin/index.php">Dashboard</a>
            <a href="<?= $base ?>/admin/especialidades.php">Especialidades</a>
            <a href="<?= $base ?>/admin/barrios.php">Barrios</a>
            <a href="<?= $base ?>/admin/tecnicos.php">Técnicos</a>
            <a href="<?= $base ?>/admin/contactos.php">Contactos</a>
            <a href="<?= $base ?>/index.php">Sitio</a>
            <span class="admin-user"><?= e($admin_nombre) ?></span>
            <a href="<?= $base ?>/admin/logout.php" class="btn btn-sm btn-outline">Salir</a>
        </nav>
    </div>
</header>
<main class="site-main admin-main">
