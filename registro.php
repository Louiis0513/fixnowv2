<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo_pagina = 'Crear cuenta';

if (tecnicoLogueado()) {
    header('Location: /FixNow/mi-cuenta.php');
    exit;
}

$especialidades = obtenerEspecialidades($conexion);
$barrios = obtenerBarrios($conexion);
$error = '';
$ok = '';

$form = [
    'nombre' => '',
    'apellido' => '',
    'cedula' => '',
    'telefono' => '',
    'correo' => '',
    'id_especialidad' => 0,
    'id_barrio' => 0,
    'descripcion' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['nombre'] = trim($_POST['nombre'] ?? '');
    $form['apellido'] = trim($_POST['apellido'] ?? '');
    $form['cedula'] = normalizarCedula($_POST['cedula'] ?? '');
    $form['telefono'] = trim($_POST['telefono'] ?? '');
    $form['correo'] = trim($_POST['correo'] ?? '');
    $form['id_especialidad'] = (int) ($_POST['id_especialidad'] ?? 0);
    $form['id_barrio'] = (int) ($_POST['id_barrio'] ?? 0);
    $form['descripcion'] = trim($_POST['descripcion'] ?? '');
    $clave = (string) ($_POST['clave'] ?? '');
    $clave2 = (string) ($_POST['clave_confirmar'] ?? '');

    if (
        $form['nombre'] === '' || $form['apellido'] === '' || $form['cedula'] === ''
        || $form['telefono'] === '' || $form['correo'] === '' || $clave === '' || $clave2 === ''
        || $form['id_especialidad'] <= 0 || $form['id_barrio'] <= 0 || $form['descripcion'] === ''
    ) {
        $error = 'Completa todos los campos obligatorios.';
    } elseif (!cedulaValida($form['cedula'])) {
        $error = 'La cédula debe tener entre 6 y 12 dígitos.';
    } elseif (!filter_var($form['correo'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Escribe un correo válido.';
    } elseif (strlen(preg_replace('/\D+/', '', $form['telefono']) ?? '') < 7) {
        $error = 'Escribe un teléfono / WhatsApp válido.';
    } elseif (strlen($clave) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($clave !== $clave2) {
        $error = 'La contraseña y su confirmación no coinciden.';
    } else {
        $chkAdmin = $conexion->prepare('SELECT id_admin FROM admin WHERE correo = ? LIMIT 1');
        $chkAdmin->bind_param('s', $form['correo']);
        $chkAdmin->execute();
        $esAdmin = (bool) $chkAdmin->get_result()->fetch_assoc();
        $chkAdmin->close();

        if ($esAdmin) {
            $error = 'Ese correo está reservado. Usa otro.';
        } else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            $hoy = date('Y-m-d');
            $verificado = 0;
            $foto = null;
            $lat = null;
            $lng = null;
            $stmt = $conexion->prepare(
                'INSERT INTO tecnico
                    (nombre, apellido, cedula, id_especialidad, id_barrio, telefono, correo, password_hash,
                     descripcion, foto_url, latitud, longitud, verificado, fecha_registro)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            try {
                $stmt->bind_param(
                    'sssiisssssssis',
                    $form['nombre'],
                    $form['apellido'],
                    $form['cedula'],
                    $form['id_especialidad'],
                    $form['id_barrio'],
                    $form['telefono'],
                    $form['correo'],
                    $hash,
                    $form['descripcion'],
                    $foto,
                    $lat,
                    $lng,
                    $verificado,
                    $hoy
                );
                $stmt->execute();
                $nuevoId = (int) $conexion->insert_id;
                $stmt->close();
                trasCambioTecnico($conexion, $nuevoId);
                iniciarSesionTecnico([
                    'id_tecnico' => $nuevoId,
                    'nombre' => $form['nombre'],
                    'apellido' => $form['apellido'],
                    'correo' => $form['correo'],
                ]);
                guardarFlash('ok', 'Cuenta creada. El admin revisará tu cédula y te colocará en el directorio.');
                header('Location: /FixNow/mi-cuenta.php');
                exit;
            } catch (mysqli_sql_exception $ex) {
                $stmt->close();
                if ((int) $ex->getCode() === 1062) {
                    $error = 'Ya existe un técnico con esa cédula o correo. Inicia sesión o usa otros datos.';
                } else {
                    $error = 'No se pudo crear la cuenta. Intenta de nuevo.';
                }
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="login-wrap" style="min-height:auto;padding:0 0 2rem">
        <div class="login-card" style="max-width:520px">
            <h1>Crear cuenta de técnico</h1>
            <p class="sub">Regístrate con tu cédula. El administrador valida tus datos, te coloca (barrio/mapa) y te hace visible.</p>

            <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

            <form method="post" class="form-card" style="box-shadow:none;padding:0;background:transparent;max-width:none">
                <div class="form-row-coords">
                    <label>Nombre *
                        <input name="nombre" required autocomplete="given-name" value="<?= e($form['nombre']) ?>">
                    </label>
                    <label>Apellido *
                        <input name="apellido" required autocomplete="family-name" value="<?= e($form['apellido']) ?>">
                    </label>
                </div>
                <label>Cédula (CC) *
                    <input name="cedula" required inputmode="numeric" pattern="[0-9.\-\s]{6,20}"
                           value="<?= e($form['cedula']) ?>" placeholder="Solo números">
                </label>
                <label>Teléfono / WhatsApp *
                    <input name="telefono" required autocomplete="tel" value="<?= e($form['telefono']) ?>" placeholder="3001234567">
                </label>
                <label>Correo *
                    <input type="email" name="correo" required autocomplete="email" value="<?= e($form['correo']) ?>">
                </label>
                <label>Especialidad *
                    <select name="id_especialidad" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= (int) $esp['id_especialidad'] ?>" <?= (int) $form['id_especialidad'] === (int) $esp['id_especialidad'] ? 'selected' : '' ?>>
                                <?= e($esp['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Barrio / zona *
                    <select name="id_barrio" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($barrios as $b): ?>
                            <option value="<?= (int) $b['id_barrio'] ?>" <?= (int) $form['id_barrio'] === (int) $b['id_barrio'] ? 'selected' : '' ?>>
                                <?= e($b['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Descripción de tu oficio *
                    <textarea name="descripcion" rows="4" required placeholder="Experiencia, servicios, cobertura…"><?= e($form['descripcion']) ?></textarea>
                </label>
                <div class="form-row-coords">
                    <label>Contraseña *
                        <input type="password" name="clave" required autocomplete="new-password" minlength="6">
                    </label>
                    <label>Confirmar *
                        <input type="password" name="clave_confirmar" required autocomplete="new-password" minlength="6">
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Enviar solicitud</button>
            </form>

            <p class="auth-hint">¿Ya tienes cuenta? <a href="/FixNow/login.php">Inicia sesión</a></p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
