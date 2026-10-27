<?php
require_once __DIR__ . '/../includes/funciones.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($correo === '' || $password === '') {
        $error = 'Ingresa correo y contraseña.';
    } else {
        $stmt = $conexion->prepare('SELECT id_admin, nombre, correo, password_hash FROM admin WHERE correo = ?');
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = (int) $admin['id_admin'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            $_SESSION['admin_correo'] = $admin['correo'];
            header('Location: index.php');
            exit;
        }
        $error = 'Credenciales incorrectas.';
    }
}

$titulo_pagina = 'Login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Fix Now Admin</title>
    <link rel="stylesheet" href="/FixNow/css/estilos.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <img src="/FixNow/img/logo.png" alt="Fix Now" class="logo-center">
        <h1>Fix Now</h1>
        <p class="sub">Panel del taller · Administración</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" required
                       value="<?= e($_POST['correo'] ?? '') ?>" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Ingresar</button>
        </form>
        <p style="text-align:center; margin-top:1.25rem">
            <a href="/FixNow/index.php">← Volver al sitio</a>
        </p>
    </div>
</div>
</body>
</html>
