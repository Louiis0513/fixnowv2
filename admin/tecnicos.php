<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo_pagina = 'Técnicos';
$mensaje = '';
$error = '';
$filtro = $_GET['filtro'] ?? 'todos';
if (!in_array($filtro, ['todos', 'pendientes', 'verificados'], true)) {
    $filtro = 'todos';
}

if (isset($_GET['aceptar'])) {
    $id = (int) $_GET['aceptar'];
    $stmt = $conexion->prepare('UPDATE tecnico SET verificado = 1 WHERE id_tecnico = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        trasCambioTecnico($conexion, $id);
        $mensaje = 'Técnico aceptado y visible en el directorio. Revisa colocación (barrio/mapa) si hace falta.';
    } else {
        $error = 'No se pudo aceptar.';
    }
    $stmt->close();
}

if (isset($_GET['rechazar'])) {
    $id = (int) $_GET['rechazar'];
    $stmt = $conexion->prepare('UPDATE tecnico SET verificado = 0 WHERE id_tecnico = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        trasCambioTecnico($conexion, $id);
        $mensaje = 'Técnico marcado como no verificado (oculto del público).';
    } else {
        $error = 'No se pudo rechazar.';
    }
    $stmt->close();
}

if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $stmt = $conexion->prepare('UPDATE tecnico SET verificado = IF(verificado = 1, 0, 1) WHERE id_tecnico = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        trasCambioTecnico($conexion, $id);
        $mensaje = 'Estado de verificación actualizado.';
    } else {
        $error = 'No se pudo actualizar.';
    }
    $stmt->close();
}

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $stmt = $conexion->prepare('DELETE FROM tecnico WHERE id_tecnico = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        cacheInvalidarTecnicosPublicos($id);
        $mensaje = 'Técnico eliminado.';
    } else {
        $error = 'No se pudo eliminar.';
    }
    $stmt->close();
}

$lista = [];
$sql = 'SELECT t.id_tecnico, t.nombre, t.apellido, t.cedula, t.telefono, t.correo, t.verificado, t.fecha_registro, t.foto_url,
               t.latitud, t.longitud,
               e.nombre AS especialidad, b.nombre AS barrio
        FROM tecnico t
        INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad
        INNER JOIN barrio b ON b.id_barrio = t.id_barrio';
if ($filtro === 'pendientes') {
    $sql .= ' WHERE t.verificado = 0';
} elseif ($filtro === 'verificados') {
    $sql .= ' WHERE t.verificado = 1';
}
$sql .= ' ORDER BY t.verificado ASC, t.fecha_registro DESC, t.nombre';
$res = $conexion->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $lista[] = $row;
    }
}

$pendientes = 0;
$resP = $conexion->query('SELECT COUNT(*) AS n FROM tecnico WHERE verificado = 0');
if ($resP) {
    $pendientes = (int) ($resP->fetch_assoc()['n'] ?? 0);
}

require __DIR__ . '/../includes/header_admin.php';
?>

<div class="container">
    <div class="toolbar">
        <div>
            <h1 class="section-title" style="margin:0">Técnicos</h1>
            <p class="section-sub"><?= count($lista) ?> en vista · <?= $pendientes ?> pendientes</p>
        </div>
        <a class="btn btn-primary" href="tecnico_form.php">Nuevo técnico</a>
    </div>

    <div class="acciones" style="margin-bottom:1rem">
        <a class="btn btn-sm <?= $filtro === 'todos' ? 'btn-secondary' : 'btn-ghost' ?>" href="?filtro=todos">Todos</a>
        <a class="btn btn-sm <?= $filtro === 'pendientes' ? 'btn-secondary' : 'btn-ghost' ?>" href="?filtro=pendientes">Pendientes<?= $pendientes ? ' (' . $pendientes . ')' : '' ?></a>
        <a class="btn btn-sm <?= $filtro === 'verificados' ? 'btn-secondary' : 'btn-ghost' ?>" href="?filtro=verificados">Verificados</a>
    </div>

    <?php if ($mensaje !== ''): ?><div class="alert alert-ok"><?= e($mensaje) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>CC</th>
                    <th>Especialidad</th>
                    <th>Barrio</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista)): ?>
                    <tr><td colspan="8">Sin técnicos en este filtro.</td></tr>
                <?php else: ?>
                    <?php foreach ($lista as $row): ?>
                        <?php
                        $ok = (int) $row['verificado'] === 1;
                        $sinPin = empty($row['latitud']) || empty($row['longitud']);
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['foto_url'])): ?>
                                    <img class="thumb-admin" src="<?= e($row['foto_url']) ?>" alt="">
                                <?php else: ?>
                                    <span class="badge">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($row['nombre'] . ' ' . $row['apellido']) ?></strong>
                                <div class="meta"><?= e($row['fecha_registro']) ?></div>
                            </td>
                            <td><?= e($row['cedula'] ?? '—') ?></td>
                            <td><?= e($row['especialidad']) ?></td>
                            <td>
                                <?= e($row['barrio']) ?>
                                <?php if ($sinPin): ?><div class="meta">Sin pin mapa</div><?php endif; ?>
                            </td>
                            <td>
                                <?= e($row['telefono']) ?>
                                <?php if (!empty($row['correo'])): ?>
                                    <div class="meta"><?= e($row['correo']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ok): ?>
                                    <span class="badge badge-ok">Verificado</span>
                                <?php else: ?>
                                    <span class="badge badge-warn">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$ok): ?>
                                    <a class="btn btn-sm btn-primary" href="?filtro=<?= e($filtro) ?>&aceptar=<?= (int) $row['id_tecnico'] ?>">Aceptar</a>
                                <?php else: ?>
                                    <a class="btn btn-sm btn-ghost" href="?filtro=<?= e($filtro) ?>&rechazar=<?= (int) $row['id_tecnico'] ?>">Ocultar</a>
                                <?php endif; ?>
                                <a class="btn btn-sm btn-secondary" href="tecnico_form.php?id=<?= (int) $row['id_tecnico'] ?>">Colocar / Editar</a>
                                <a class="btn btn-sm btn-danger" href="?filtro=<?= e($filtro) ?>&eliminar=<?= (int) $row['id_tecnico'] ?>"
                                   onclick="return confirm('¿Eliminar este técnico?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
