<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo_pagina = 'Técnicos';

$q = trim($_GET['q'] ?? '');
$id_especialidad = (int) ($_GET['especialidad'] ?? 0);
$id_barrio = (int) ($_GET['barrio'] ?? 0);

$especialidades = obtenerEspecialidades($conexion);
$barrios = obtenerBarrios($conexion);
$tecnicos = obtenerTecnicosPublicos($conexion, $id_especialidad, $id_barrio, $q);

$total = count($tecnicos);
$hay_filtros = $q !== '' || $id_especialidad > 0 || $id_barrio > 0;

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="section-head section-head--views">
        <div>
            <h1 class="section-title">Técnicos verificados</h1>
            <p class="section-sub">Busca por oficio, barrio o nombre en Bogotá</p>
        </div>
        <div class="view-toggle" role="navigation" aria-label="Vista de técnicos">
            <a class="view-toggle-btn is-active" href="/FixNow/tecnicos.php<?= $hay_filtros ? '?' . http_build_query(array_filter(['q' => $q, 'especialidad' => $id_especialidad ?: null, 'barrio' => $id_barrio ?: null])) : '' ?>" aria-current="page">Lista</a>
            <a class="view-toggle-btn" href="/FixNow/tecnicosmap.php<?= $hay_filtros ? '?' . http_build_query(array_filter(['q' => $q, 'especialidad' => $id_especialidad ?: null, 'barrio' => $id_barrio ?: null])) : '' ?>">Mapa</a>
        </div>
    </div>

    <form class="filtros" method="get" action="" role="search">
        <div class="campo">
            <label>Buscar
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nombre, oficio, zona…" autocomplete="off">
            </label>
        </div>
        <div class="campo">
            <label>Especialidad
                <select name="especialidad">
                    <option value="0">Todas</option>
                    <?php foreach ($especialidades as $esp): ?>
                        <option value="<?= (int) $esp['id_especialidad'] ?>" <?= $id_especialidad === (int) $esp['id_especialidad'] ? 'selected' : '' ?>>
                            <?= e($esp['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="campo">
            <label>Barrio / zona
                <select name="barrio">
                    <option value="0">Todos</option>
                    <?php foreach ($barrios as $b): ?>
                        <option value="<?= (int) $b['id_barrio'] ?>" <?= $id_barrio === (int) $b['id_barrio'] ? 'selected' : '' ?>>
                            <?= e($b['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="filtros-actions">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <?php if ($hay_filtros): ?>
                <a href="/FixNow/tecnicos.php" class="btn btn-ghost">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="resultados-bar">
        <p>
            <?php if ($total === 0): ?>
                Sin resultados
            <?php elseif ($total === 1): ?>
                <strong>1</strong> técnico encontrado
            <?php else: ?>
                <strong><?= $total ?></strong> técnicos encontrados
            <?php endif; ?>
        </p>
    </div>

    <?php if (empty($tecnicos)): ?>
        <div class="empty">
            <strong>No hay coincidencias</strong>
            Prueba otra especialidad, barrio o término de búsqueda.
            <div style="margin-top:1rem">
                <a class="btn btn-primary" href="/FixNow/tecnicos.php">Ver todos los técnicos</a>
            </div>
        </div>
    <?php else: ?>
        <div class="grid-tecnicos">
            <?php foreach ($tecnicos as $t): ?>
                <?php $mostrar_wa = true; require __DIR__ . '/includes/card_tecnico.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
