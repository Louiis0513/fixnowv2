<?php
require_once __DIR__ . '/../includes/funciones.php';
requiereAdmin();

$titulo_pagina = 'Especialidades';
$mensaje = '';
$error = '';

// Eliminar
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $stmt = $conexion->prepare('DELETE FROM especialidad WHERE id_especialidad = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        trasCambioCatalogo();
        $mensaje = 'Especialidad eliminada.';
    } else {
        $error = 'No se pudo eliminar (puede estar en uso por técnicos).';
    }
    $stmt->close();
}

// Crear / editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id_especialidad'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } elseif ($id > 0) {
        $stmt = $conexion->prepare('UPDATE especialidad SET nombre = ? WHERE id_especialidad = ?');
        $stmt->bind_param('si', $nombre, $id);
        $stmt->execute();
        $stmt->close();
        trasCambioCatalogo();
        $mensaje = 'Especialidad actualizada.';
    } else {
        $stmt = $conexion->prepare('INSERT INTO especialidad (nombre) VALUES (?)');
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $stmt->close();
        trasCambioCatalogo();
        $mensaje = 'Especialidad creada.';
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $stmt = $conexion->prepare('SELECT id_especialidad, nombre FROM especialidad WHERE id_especialidad = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$lista = [];
$res = $conexion->query('SELECT id_especialidad, nombre FROM especialidad ORDER BY nombre');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $lista[] = $row;
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>

<div class="container">
    <div class="toolbar">
        <h1 class="section-title" style="margin:0">Especialidades</h1>
    </div>

    <?php if ($mensaje !== ''): ?><div class="alert alert-ok"><?= e($mensaje) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form class="form-card" method="post" action="" style="margin-bottom:1.5rem">
        <input type="hidden" name="id_especialidad" value="<?= (int) ($editar['id_especialidad'] ?? 0) ?>">
        <div class="form-group">
            <label for="nombre"><?= $editar ? 'Editar especialidad' : 'Nueva especialidad' ?></label>
            <input type="text" id="nombre" name="nombre" required
                   value="<?= e($editar['nombre'] ?? '') ?>">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $editar ? 'Guardar' : 'Crear' ?></button>
            <?php if ($editar): ?>
                <a class="btn btn-ghost" href="especialidades.php">Cancelar</a>
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
                            <td><?= (int) $row['id_especialidad'] ?></td>
                            <td><?= e($row['nombre']) ?></td>
                            <td>
                                <a class="btn btn-sm btn-ghost" href="?editar=<?= (int) $row['id_especialidad'] ?>">Editar</a>
                                <a class="btn btn-sm btn-danger" href="?eliminar=<?= (int) $row['id_especialidad'] ?>"
                                   onclick="return confirm('¿Eliminar esta especialidad?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
