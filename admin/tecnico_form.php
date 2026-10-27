<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$id = (int) ($_GET['id'] ?? 0);
$titulo_pagina = $id > 0 ? 'Colocar / editar técnico' : 'Nuevo técnico';
$error = '';
$tecnico = [
    'nombre' => '',
    'apellido' => '',
    'cedula' => '',
    'correo' => '',
    'id_especialidad' => 0,
    'id_barrio' => 0,
    'telefono' => '',
    'descripcion' => '',
    'foto_url' => '',
    'latitud' => '',
    'longitud' => '',
    'verificado' => 0,
];

if ($id > 0) {
    $stmt = $conexion->prepare('SELECT * FROM tecnico WHERE id_tecnico = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        header('Location: tecnicos.php');
        exit;
    }
    $tecnico = $row;
    $tecnico['latitud'] = $row['latitud'] ?? '';
    $tecnico['longitud'] = $row['longitud'] ?? '';
    $tecnico['cedula'] = $row['cedula'] ?? '';
    $tecnico['correo'] = $row['correo'] ?? '';
}

$especialidades = obtenerEspecialidades($conexion);
$barrios = obtenerBarrios($conexion);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $cedula = normalizarCedula($_POST['cedula'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $id_especialidad = (int) ($_POST['id_especialidad'] ?? 0);
    $id_barrio = (int) ($_POST['id_barrio'] ?? 0);
    $telefono = trim($_POST['telefono'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $foto_url = trim($_POST['foto_url'] ?? '');
    $latitud_raw = trim($_POST['latitud'] ?? '');
    $longitud_raw = trim($_POST['longitud'] ?? '');
    $verificado = isset($_POST['verificado']) ? 1 : 0;
    $claveNueva = (string) ($_POST['clave_nueva'] ?? '');

    $latitud = $latitud_raw === '' ? null : (string) round((float) $latitud_raw, 7);
    $longitud = $longitud_raw === '' ? null : (string) round((float) $longitud_raw, 7);
    $correoDb = $correo === '' ? null : $correo;

    $tecnico = [
        'nombre' => $nombre,
        'apellido' => $apellido,
        'cedula' => $cedula,
        'correo' => $correo,
        'id_especialidad' => $id_especialidad,
        'id_barrio' => $id_barrio,
        'telefono' => $telefono,
        'descripcion' => $descripcion,
        'foto_url' => $foto_url,
        'latitud' => $latitud_raw,
        'longitud' => $longitud_raw,
        'verificado' => $verificado,
    ];

    if ($nombre === '' || $apellido === '' || $telefono === '' || $id_especialidad <= 0 || $id_barrio <= 0) {
        $error = 'Completa nombre, apellido, teléfono, especialidad y barrio.';
    } elseif ($cedula === '' || !cedulaValida($cedula)) {
        $error = 'La cédula es obligatoria (6–12 dígitos).';
    } elseif ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo inválido.';
    } elseif (($latitud_raw === '') xor ($longitud_raw === '')) {
        $error = 'Indica latitud y longitud juntas, o déjalas vacías.';
    } elseif ($latitud_raw !== '' && (!is_numeric($latitud_raw) || !is_numeric($longitud_raw))) {
        $error = 'Latitud y longitud deben ser numéricas.';
    } else {
        try {
            if ($id > 0) {
                $stmt = $conexion->prepare(
                    'UPDATE tecnico SET nombre=?, apellido=?, cedula=?, id_especialidad=?, id_barrio=?, telefono=?, correo=?, descripcion=?, foto_url=?, latitud=?, longitud=?, verificado=? WHERE id_tecnico=?'
                );
                $stmt->bind_param(
                    'sssiissssssii',
                    $nombre,
                    $apellido,
                    $cedula,
                    $id_especialidad,
                    $id_barrio,
                    $telefono,
                    $correoDb,
                    $descripcion,
                    $foto_url,
                    $latitud,
                    $longitud,
                    $verificado,
                    $id
                );
                $stmt->execute();
                $stmt->close();

                if ($claveNueva !== '' && strlen($claveNueva) >= 6) {
                    $hash = password_hash($claveNueva, PASSWORD_DEFAULT);
                    $up = $conexion->prepare('UPDATE tecnico SET password_hash = ? WHERE id_tecnico = ?');
                    $up->bind_param('si', $hash, $id);
                    $up->execute();
                    $up->close();
                }

                trasCambioTecnico($conexion, $id);
            } else {
                $hoy = date('Y-m-d');
                $hash = $claveNueva !== '' ? password_hash($claveNueva, PASSWORD_DEFAULT) : null;
                $stmt = $conexion->prepare(
                    'INSERT INTO tecnico (nombre, apellido, cedula, id_especialidad, id_barrio, telefono, correo, password_hash, descripcion, foto_url, latitud, longitud, verificado, fecha_registro) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
                );
                $stmt->bind_param(
                    'sssiisssssssis',
                    $nombre,
                    $apellido,
                    $cedula,
                    $id_especialidad,
                    $id_barrio,
                    $telefono,
                    $correoDb,
                    $hash,
                    $descripcion,
                    $foto_url,
                    $latitud,
                    $longitud,
                    $verificado,
                    $hoy
                );
                $stmt->execute();
                $nuevoId = (int) $conexion->insert_id;
                $stmt->close();
                trasCambioTecnico($conexion, $nuevoId);
            }
            header('Location: tecnicos.php');
            exit;
        } catch (mysqli_sql_exception $ex) {
            if ((int) $ex->getCode() === 1062) {
                $error = 'Cédula o correo ya registrados en otro técnico.';
            } else {
                $error = 'No se pudo guardar.';
            }
        }
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>

<div class="container">
    <h1 class="section-title"><?= e($titulo_pagina) ?></h1>
    <p class="section-sub">Colocar = especialidad, barrio, pin del mapa y marcar verificado para publicar.</p>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" class="form-card" style="max-width:560px">
        <label>Nombre
            <input type="text" name="nombre" required value="<?= e($tecnico['nombre']) ?>">
        </label>
        <label>Apellido
            <input type="text" name="apellido" required value="<?= e($tecnico['apellido']) ?>">
        </label>
        <label>Cédula (CC)
            <input type="text" name="cedula" required inputmode="numeric" value="<?= e($tecnico['cedula'] ?? '') ?>">
        </label>
        <label>Correo (login técnico, opcional si lo creas tú)
            <input type="email" name="correo" value="<?= e($tecnico['correo'] ?? '') ?>">
        </label>
        <label><?= $id > 0 ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña (opcional)' ?>
            <input type="password" name="clave_nueva" autocomplete="new-password" minlength="6">
        </label>
        <label>Especialidad
            <select name="id_especialidad" required>
                <option value="">Seleccionar</option>
                <?php foreach ($especialidades as $esp): ?>
                    <option value="<?= (int) $esp['id_especialidad'] ?>" <?= (int) $tecnico['id_especialidad'] === (int) $esp['id_especialidad'] ? 'selected' : '' ?>>
                        <?= e($esp['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Barrio
            <select name="id_barrio" required>
                <option value="">Seleccionar</option>
                <?php foreach ($barrios as $b): ?>
                    <option value="<?= (int) $b['id_barrio'] ?>" <?= (int) $tecnico['id_barrio'] === (int) $b['id_barrio'] ? 'selected' : '' ?>>
                        <?= e($b['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Teléfono
            <input type="text" name="telefono" required value="<?= e($tecnico['telefono']) ?>">
        </label>
        <label>Descripción
            <textarea name="descripcion" rows="4"><?= e($tecnico['descripcion'] ?? '') ?></textarea>
        </label>
        <label>Foto URL (opcional)
            <input type="text" name="foto_url" placeholder="/FixNow/img/tecnicos/ejemplo.jpg" value="<?= e($tecnico['foto_url'] ?? '') ?>">
        </label>
        <?php if (!empty($tecnico['foto_url'])): ?>
            <img class="thumb-admin" style="margin:0.25rem 0 0.75rem;width:72px;height:72px" src="<?= e($tecnico['foto_url']) ?>" alt="Vista previa">
        <?php endif; ?>
        <div class="form-row-coords">
            <label>Latitud (mapa)
                <input type="text" name="latitud" inputmode="decimal" placeholder="4.6486200" value="<?= e((string) ($tecnico['latitud'] ?? '')) ?>">
            </label>
            <label>Longitud (mapa)
                <input type="text" name="longitud" inputmode="decimal" placeholder="-74.0628100" value="<?= e((string) ($tecnico['longitud'] ?? '')) ?>">
            </label>
        </div>
        <p class="meta" style="margin:-0.35rem 0 0.85rem">Opcional. Bogotá aprox. lat 4.5–4.8 / lng -74.2 a -74.0. Necesario para aparecer en el mapa.</p>
        <label class="check-inline">
            <input type="checkbox" name="verificado" value="1" <?= (int) ($tecnico['verificado'] ?? 0) === 1 ? 'checked' : '' ?>>
            Verificado / aceptado (visible en el directorio público)
        </label>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="tecnicos.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
