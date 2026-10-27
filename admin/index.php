<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo_pagina = 'Dashboard';

$total_tecnicos = 0;
$verificados = 0;
$total_contactos = 0;
$contactos_hoy = 0;
$especialidades_n = 0;
$barrios_n = 0;

$r = $conexion->query('SELECT COUNT(*) AS c FROM tecnico');
if ($r) {
    $total_tecnicos = (int) $r->fetch_assoc()['c'];
}
$r = $conexion->query('SELECT COUNT(*) AS c FROM tecnico WHERE verificado = 1');
if ($r) {
    $verificados = (int) $r->fetch_assoc()['c'];
}
$r = $conexion->query('SELECT COUNT(*) AS c FROM contacto');
if ($r) {
    $total_contactos = (int) $r->fetch_assoc()['c'];
}
$hoy = date('Y-m-d');
$stmt = $conexion->prepare('SELECT COUNT(*) AS c FROM contacto WHERE fecha_solicitud = ?');
$stmt->bind_param('s', $hoy);
$stmt->execute();
$contactos_hoy = (int) $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

$r = $conexion->query('SELECT COUNT(*) AS c FROM especialidad');
if ($r) {
    $especialidades_n = (int) $r->fetch_assoc()['c'];
}
$r = $conexion->query('SELECT COUNT(*) AS c FROM barrio');
if ($r) {
    $barrios_n = (int) $r->fetch_assoc()['c'];
}

$recientes = [];
$sql = 'SELECT c.nombre_usuario, c.descripcion_problema, c.fecha_solicitud, c.medio,
               t.nombre AS tecnico_nombre, t.apellido AS tecnico_apellido
        FROM contacto c
        INNER JOIN tecnico t ON t.id_tecnico = c.id_tecnico
        ORDER BY c.id_contacto DESC
        LIMIT 5';
$res = $conexion->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $recientes[] = $row;
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>

<div class="container">
    <div class="section-head">
        <div>
            <h1 class="section-title">Dashboard</h1>
            <p class="section-sub">Hola, <?= e($_SESSION['admin_nombre'] ?? '') ?>. Resumen del taller Fix Now.</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="num"><?= $total_tecnicos ?></div>
            <div class="label">Técnicos</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= $verificados ?></div>
            <div class="label">Verificados</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= $total_contactos ?></div>
            <div class="label">Contactos</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= $contactos_hoy ?></div>
            <div class="label">Hoy</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= $especialidades_n ?></div>
            <div class="label">Oficios</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= $barrios_n ?></div>
            <div class="label">Barrios</div>
        </div>
    </div>

    <div class="acciones" style="margin-bottom:2rem">
        <a class="btn btn-primary" href="tecnicos.php">Gestionar técnicos</a>
        <a class="btn btn-secondary" href="contactos.php">Bandeja de contactos</a>
        <a class="btn btn-ghost" href="tecnico_form.php">Nuevo técnico</a>
    </div>

    <h2 class="section-title">Últimos contactos</h2>
    <?php if (empty($recientes)): ?>
        <div class="empty">Sin contactos recientes.</div>
    <?php else: ?>
        <?php foreach ($recientes as $row): ?>
            <article class="inbox-card">
                <div class="inbox-top">
                    <span class="inbox-user"><?= e($row['nombre_usuario']) ?></span>
                    <?php if ($row['medio'] === 'whatsapp'): ?>
                        <span class="badge badge-wa">WhatsApp</span>
                    <?php else: ?>
                        <span class="badge badge-form">Formulario</span>
                    <?php endif; ?>
                </div>
                <p class="inbox-problema"><?= e(mb_strimwidth($row['descripcion_problema'], 0, 120, '…')) ?></p>
                <div class="inbox-meta">
                    <span>📅 <?= e($row['fecha_solicitud']) ?></span>
                    <span>🛠️ <?= e($row['tecnico_nombre'] . ' ' . $row['tecnico_apellido']) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
        <a class="btn btn-ghost" href="contactos.php">Ver bandeja completa</a>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
