<?php
require_once __DIR__ . '/includes/funciones.php';
requiereTecnico();

$titulo_pagina = 'Mi cuenta';
$id = (int) $_SESSION['tecnico_id'];

$stmt = $conexion->prepare(
    'SELECT t.*, e.nombre AS especialidad, b.nombre AS barrio
     FROM tecnico t
     INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad
     INNER JOIN barrio b ON b.id_barrio = t.id_barrio
     WHERE t.id_tecnico = ?
     LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$tec = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tec) {
    cerrarSesionTecnico();
    header('Location: /FixNow/login.php');
    exit;
}

$flash = tomarFlash();
$verificado = (int) ($tec['verificado'] ?? 0) === 1;

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="section-head">
        <div>
            <h1 class="section-title">Hola, <?= e($tec['nombre']) ?></h1>
            <p class="section-sub">Estado de tu perfil en Fix Now</p>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['tipo'] === 'ok' ? 'ok' : 'error' ?>"><?= e($flash['texto']) ?></div>
    <?php endif; ?>

    <div class="form-card" style="max-width:560px">
        <p style="margin:0 0 1rem">
            <?php if ($verificado): ?>
                <span class="status-pill is-ok">Verificado · visible en el directorio</span>
            <?php else: ?>
                <span class="status-pill is-pending">Pendiente de aceptación</span>
            <?php endif; ?>
        </p>

        <?php if (!$verificado): ?>
            <p class="meta">El administrador revisará tu cédula y datos. Cuando te acepte y coloque (zona / mapa), aparecerás en Técnicos y en el Mapa.</p>
        <?php else: ?>
            <p class="meta">Ya estás publicado. Puedes compartir tu perfil con vecinos.</p>
            <p><a class="btn btn-secondary" href="/FixNow/tecnico.php?id=<?= (int) $tec['id_tecnico'] ?>">Ver mi perfil público</a></p>
        <?php endif; ?>

        <dl class="meta" style="margin-top:1.25rem;display:grid;gap:0.5rem">
            <div><strong>CC:</strong> <?= e($tec['cedula'] ?? '—') ?></div>
            <div><strong>Correo:</strong> <?= e($tec['correo'] ?? '—') ?></div>
            <div><strong>Teléfono:</strong> <?= e($tec['telefono']) ?></div>
            <div><strong>Oficio:</strong> <?= e($tec['especialidad']) ?></div>
            <div><strong>Barrio:</strong> <?= e($tec['barrio']) ?></div>
            <div><strong>Registro:</strong> <?= e($tec['fecha_registro']) ?></div>
        </dl>

        <?php if (!empty($tec['descripcion'])): ?>
            <p style="margin-top:1rem"><?= nl2br(e($tec['descripcion'])) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
