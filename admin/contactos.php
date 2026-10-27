<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo_pagina = 'Contactos';
$filtro_medio = trim($_GET['medio'] ?? '');

$lista = [];
$sql = 'SELECT c.id_contacto, c.nombre_usuario, c.telefono_usuario, c.descripcion_problema,
               c.fecha_solicitud, c.medio,
               t.nombre AS tecnico_nombre, t.apellido AS tecnico_apellido, t.foto_url,
               e.nombre AS especialidad
        FROM contacto c
        INNER JOIN tecnico t ON t.id_tecnico = c.id_tecnico
        INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad';
$params = [];
$types = '';

if ($filtro_medio === 'whatsapp' || $filtro_medio === 'formulario') {
    $sql .= ' WHERE c.medio = ?';
    $types = 's';
    $params[] = $filtro_medio;
}

$sql .= ' ORDER BY c.fecha_solicitud DESC, c.id_contacto DESC';

if ($types !== '') {
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $lista[] = $row;
    }
    $stmt->close();
} else {
    $res = $conexion->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $lista[] = $row;
        }
    }
}

$total = count($lista);

require __DIR__ . '/../includes/header_admin.php';
?>

<div class="container">
    <div class="toolbar">
        <div>
            <h1 class="section-title" style="margin:0">Bandeja de contactos</h1>
            <p class="section-sub"><?= $total ?> solicitud<?= $total === 1 ? '' : 'es' ?> · solo lectura</p>
        </div>
        <div class="filtros-actions">
            <a class="btn btn-sm <?= $filtro_medio === '' ? 'btn-primary' : 'btn-ghost' ?>" href="contactos.php">Todos</a>
            <a class="btn btn-sm <?= $filtro_medio === 'whatsapp' ? 'btn-primary' : 'btn-ghost' ?>" href="?medio=whatsapp">WhatsApp</a>
            <a class="btn btn-sm <?= $filtro_medio === 'formulario' ? 'btn-primary' : 'btn-ghost' ?>" href="?medio=formulario">Formulario</a>
        </div>
    </div>

    <?php if (empty($lista)): ?>
        <div class="empty">
            <strong>Bandeja vacía</strong>
            Aún no hay contactos<?= $filtro_medio !== '' ? ' con este filtro' : '' ?>.
        </div>
    <?php else: ?>
        <?php foreach ($lista as $row): ?>
            <article class="inbox-card">
                <div class="inbox-top">
                    <span class="inbox-user"><?= e($row['nombre_usuario']) ?></span>
                    <?php if ($row['medio'] === 'whatsapp'): ?>
                        <span class="badge badge-wa">WhatsApp</span>
                    <?php else: ?>
                        <span class="badge badge-form">Formulario</span>
                    <?php endif; ?>
                </div>
                <p class="inbox-problema"><?= e($row['descripcion_problema']) ?></p>
                <div class="inbox-meta">
                    <span>📅 <?= e($row['fecha_solicitud']) ?></span>
                    <span>📞 <?= e($row['telefono_usuario']) ?></span>
                    <span>🛠️ <?= e($row['tecnico_nombre'] . ' ' . $row['tecnico_apellido']) ?> (<?= e($row['especialidad']) ?>)</span>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
