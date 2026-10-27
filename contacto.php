<?php
require_once __DIR__ . '/includes/funciones.php';

$id_tecnico = (int) ($_GET['id_tecnico'] ?? $_POST['id_tecnico'] ?? 0);
$titulo_pagina = 'Contacto';
$exito = false;
$error = '';

$stmt = $conexion->prepare(
    'SELECT t.id_tecnico, t.nombre, t.apellido, t.foto_url, e.nombre AS especialidad
     FROM tecnico t
     INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad
     WHERE t.id_tecnico = ? AND t.verificado = 1'
);
$stmt->bind_param('i', $id_tecnico);
$stmt->execute();
$tecnico = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tecnico) {
    header('Location: /FixNow/tecnicos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_usuario'] ?? '');
    $telefono = trim($_POST['telefono_usuario'] ?? '');
    $problema = trim($_POST['descripcion_problema'] ?? '');

    if ($nombre === '' || $telefono === '' || $problema === '') {
        $error = 'Completa todos los campos.';
    } else {
        $fecha = date('Y-m-d');
        $medio = 'formulario';
        $ins = $conexion->prepare(
            'INSERT INTO contacto (nombre_usuario, telefono_usuario, descripcion_problema, fecha_solicitud, id_tecnico, medio)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $ins->bind_param('ssssis', $nombre, $telefono, $problema, $fecha, $id_tecnico, $medio);
        if ($ins->execute()) {
            $exito = true;
        } else {
            $error = 'No se pudo enviar la solicitud. Intenta de nuevo.';
        }
        $ins->close();
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <p class="meta" style="margin-bottom:1rem">
        <a href="/FixNow/tecnico.php?id=<?= (int) $id_tecnico ?>">← Volver al perfil</a>
    </p>

    <h1 class="section-title">Solicitar contacto</h1>
    <p class="section-sub" style="margin-bottom:1.25rem">
        Técnico: <strong><?= e($tecnico['nombre'] . ' ' . $tecnico['apellido']) ?></strong>
        · <?= e($tecnico['especialidad']) ?>
    </p>

    <?php if ($exito): ?>
        <div class="alert alert-ok">Tu solicitud fue enviada. El técnico te contactará pronto.</div>
        <div class="acciones">
            <a class="btn btn-whatsapp" href="/FixNow/tecnico.php?id=<?= (int) $id_tecnico ?>&amp;wa=1">También escribir por WhatsApp</a>
            <a class="btn btn-primary" href="/FixNow/tecnico.php?id=<?= (int) $id_tecnico ?>">Volver al perfil</a>
            <a class="btn btn-ghost" href="/FixNow/tecnicos.php">Ver más técnicos</a>
        </div>
    <?php else: ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="alert alert-info">
            Describe el problema con detalle (zona, urgencia, tipo de falla). También puedes contactar por WhatsApp desde el perfil.
        </div>

        <form class="form-card" method="post" action="">
            <input type="hidden" name="id_tecnico" value="<?= (int) $id_tecnico ?>">
            <div class="form-group">
                <label for="nombre_usuario">Tu nombre</label>
                <input type="text" id="nombre_usuario" name="nombre_usuario" required
                       value="<?= e($_POST['nombre_usuario'] ?? '') ?>" autocomplete="name">
            </div>
            <div class="form-group">
                <label for="telefono_usuario">Tu teléfono</label>
                <input type="tel" id="telefono_usuario" name="telefono_usuario" required
                       placeholder="Ej. 3001234567"
                       value="<?= e($_POST['telefono_usuario'] ?? '') ?>" autocomplete="tel">
            </div>
            <div class="form-group">
                <label for="descripcion_problema">Describe el problema</label>
                <textarea id="descripcion_problema" name="descripcion_problema" required
                          placeholder="¿Qué falla? ¿Dónde? ¿Es urgente?"><?= e($_POST['descripcion_problema'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enviar solicitud</button>
                <a class="btn btn-ghost" href="/FixNow/tecnico.php?id=<?= (int) $id_tecnico ?>">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
