<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo_pagina = 'Barrios';
$mensaje = '';
$error = '';

if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $stmt = $conexion->prepare('DELETE FROM barrio WHERE id_barrio = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        trasCambioCatalogo();
        $mensaje = 'Barrio eliminado.';
    } else {
        $error = 'No se pudo eliminar (puede estar en uso por técnicos).';
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id_barrio'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } elseif ($id > 0) {
        $stmt = $conexion->prepare('UPDATE barrio SET nombre = ? WHERE id_barrio = ?');
        $stmt->bind_param('si', $nombre, $id);
        $stmt->execute();
        $stmt->close();
        trasCambioCatalogo();
        $mensaje = 'Barrio actualizado.';
    } else {
        $stmt = $conexion->prepare('INSERT INTO barrio (nombre) VALUES (?)');
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $stmt->close();
        trasCambioCatalogo();
        $mensaje = 'Barrio creado.';
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $stmt = $conexion->prepare('SELECT id_barrio, nombre FROM barrio WHERE id_barrio = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$lista = [];
$res = $conexion->query('SELECT id_barrio, nombre FROM barrio ORDER BY nombre');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $lista[] = $row;
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>

<div class="container">
    <div class="toolbar">
        <h1 class="section-title" style="margin:0">Barrios</h1>
    </div>

    <?php if ($mensaje !== ''): ?><div class="alert alert-ok"><?= e($mensaje) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form class="form-card" method="post" action="" style="margin-bottom:1.5rem">
        <input type="hidden" name="id_barrio" value="<?= (int) ($editar['id_barrio'] ?? 0) ?>">
        <div class="form-group">
            <label for="nombre"><?= $editar ? 'Editar barrio' : 'Nuevo barrio' ?></label>
            <input type="text" id="nombre" name="nombre" required
                   value="<?= e($editar['nombre'] ?? '') ?>">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $editar ? 'Guardar' : 'Crear' ?></button>
            <?php if ($editar): ?>
                <a class="btn btn-ghost" href="barrios.php">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lista)): ?>
                    <tr><td colspan="3">Sin registros.</td></tr>
                <?php else: ?>
                    <?php foreach ($lista as $row): ?>
                        <tr>
                            <td><?= (int) $row['id_barrio'] ?></td>
                            <td><?= e($row['nombre']) ?></td>
                            <td>
                                <a class="btn btn-sm btn-ghost" href="?editar=<?= (int) $row['id_barrio'] ?>">Editar</a>
                                <a class="btn btn-sm btn-danger" href="?eliminar=<?= (int) $row['id_barrio'] ?>"
                                   onclick="return confirm('¿Eliminar este barrio?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
