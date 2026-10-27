<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo_pagina = 'Login';

if (tecnicoLogueado()) {
    header('Location: /FixNow/mi-cuenta.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($correo === '' || $password === '') {
        $error = 'Ingresa correo y contraseña.';
    } else {
        $stmt = $conexion->prepare(
            'SELECT id_tecnico, nombre, apellido, correo, password_hash, verificado
             FROM tecnico WHERE correo = ? LIMIT 1'
        );
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $tec = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($tec && !empty($tec['password_hash']) && password_verify($password, $tec['password_hash'])) {
            iniciarSesionTecnico($tec);
            header('Location: /FixNow/mi-cuenta.php');
            exit;
        }
        $error = 'Credenciales incorrectas o cuenta sin acceso (solo técnicos registrados).';
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="login-wrap" style="min-height:auto;padding:0 0 2rem">
        <div class="login-card">
            <img src="/FixNow/img/logo.png" alt="Fix Now" class="logo-center">
            <h1>Login técnico</h1>
            <p class="sub">Entra para ver el estado de tu solicitud o perfil.</p>

            <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="correo" required autocomplete="username"
                           value="<?= e($_POST['correo'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </form>

            <p class="auth-hint">¿Aún no estás en Fix Now? <a href="/FixNow/registro.php">Crear cuenta</a></p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
