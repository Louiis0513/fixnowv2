<?php
require_once __DIR__ . '/includes/funciones.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /FixNow/tecnicos.php');
    exit;
}

// WhatsApp: registrar contacto y redirigir
if (isset($_GET['wa']) && $_GET['wa'] === '1') {
    $stmt = $conexion->prepare(
        'SELECT id_tecnico, telefono, nombre, apellido FROM tecnico WHERE id_tecnico = ? AND verificado = 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $tec = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tec) {
        header('Location: /FixNow/tecnicos.php');
        exit;
    }

    $nombre = 'Contacto WhatsApp';
    $telefono = 'No indicado';
    $problema = 'El usuario abrió WhatsApp desde el perfil del técnico.';
    $fecha = date('Y-m-d');
    $medio = 'whatsapp';

    $ins = $conexion->prepare(
        'INSERT INTO contacto (nombre_usuario, telefono_usuario, descripcion_problema, fecha_solicitud, id_tecnico, medio)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $ins->bind_param('ssssis', $nombre, $telefono, $problema, $fecha, $id, $medio);
    $ins->execute();
    $ins->close();

    $wa = telefonoWhatsApp($tec['telefono']);
    $texto = rawurlencode(
        'Hola ' . $tec['nombre'] . ', te contacto desde Fix Now. Necesito ayuda con un servicio en casa.'
    );
    header('Location: https://wa.me/' . $wa . '?text=' . $texto);
    exit;
}

$tecnico = obtenerTecnicoPublico($conexion, $id);

if (!$tecnico) {
    header('Location: /FixNow/tecnicos.php');
    exit;
}

$titulo_pagina = $tecnico['nombre'] . ' ' . $tecnico['apellido'];
$iniciales = mb_strtoupper(mb_substr($tecnico['nombre'], 0, 1) . mb_substr($tecnico['apellido'], 0, 1));

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <p class="meta" style="margin-bottom:1rem">
        <a href="/FixNow/tecnicos.php">← Técnicos</a>
        · <?= e($tecnico['especialidad']) ?>
    </p>

    <div class="perfil">
        <aside class="perfil-aside">
            <div class="perfil-foto">
                <?php if (!empty($tecnico['foto_url'])): ?>
                    <img src="<?= e($tecnico['foto_url']) ?>" alt="<?= e($tecnico['nombre'] . ' ' . $tecnico['apellido']) ?>">
                <?php else: ?>
                    <div class="avatar-fallback"><?= e($iniciales) ?></div>
                <?php endif; ?>
            </div>
            <div class="perfil-aside-info">
                <h2><?= e($tecnico['nombre'] . ' ' . $tecnico['apellido']) ?></h2>
                <p class="perfil-esp"><?= e($tecnico['especialidad']) ?></p>
                <p class="perfil-barrio">📍 <?= e($tecnico['barrio']) ?></p>
                <span class="badge badge-ok" style="margin-top:0.85rem">✓ Verificado</span>
            </div>
        </aside>

        <div class="perfil-body">
            <h1 class="section-title">Perfil del técnico</h1>
            <p><?= nl2br(e($tecnico['descripcion'] ?? 'Sin descripción.')) ?></p>

            <div class="perfil-facts">
                <div class="perfil-fact">
                    <strong>Especialidad</strong>
                    <span><?= e($tecnico['especialidad']) ?></span>
                </div>
                <div class="perfil-fact">
                    <strong>Zona</strong>
                    <span><?= e($tecnico['barrio']) ?></span>
                </div>
                <div class="perfil-fact">
                    <strong>Teléfono</strong>
                    <span><?= e($tecnico['telefono']) ?></span>
                </div>
                <div class="perfil-fact">
                    <strong>En Fix Now desde</strong>
                    <span><?= e($tecnico['fecha_registro']) ?></span>
                </div>
            </div>

            <div class="acciones">
                <a class="btn btn-whatsapp" href="/FixNow/tecnico.php?id=<?= (int) $id ?>&amp;wa=1">
                    Contactar por WhatsApp
                </a>
                <a class="btn btn-primary" href="/FixNow/contacto.php?id_tecnico=<?= (int) $id ?>">
                    Solicitar contacto
                </a>
                <a class="btn btn-ghost" href="/FixNow/tecnicos.php">Volver al listado</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
