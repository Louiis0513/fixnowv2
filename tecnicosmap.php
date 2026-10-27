<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo_pagina = 'Mapa de técnicos';

$q = trim($_GET['q'] ?? '');
$id_especialidad = (int) ($_GET['especialidad'] ?? 0);
$id_barrio = (int) ($_GET['barrio'] ?? 0);

$especialidades = obtenerEspecialidades($conexion);
$barrios = obtenerBarrios($conexion);
$tecnicos = obtenerTecnicosMapa($conexion, $id_especialidad, $id_barrio, $q);

$total = count($tecnicos);
$hay_filtros = $q !== '' || $id_especialidad > 0 || $id_barrio > 0;

$pins = [];
foreach ($tecnicos as $t) {
    $wa = telefonoWhatsApp($t['telefono']);
    $pins[] = [
        'id' => (int) $t['id_tecnico'],
        'nombre' => $t['nombre'] . ' ' . $t['apellido'],
        'especialidad' => $t['especialidad'],
        'barrio' => $t['barrio'],
        'foto' => $t['foto_url'] ?: '',
        'lat' => (float) $t['latitud'],
        'lng' => (float) $t['longitud'],
        'perfil' => '/FixNow/tecnico.php?id=' . (int) $t['id_tecnico'],
        'whatsapp' => '/FixNow/tecnico.php?id=' . (int) $t['id_tecnico'] . '&wa=1',
        'wa_direct' => 'https://wa.me/' . $wa,
    ];
}

require __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">

<div class="container map-page">
    <div class="section-head section-head--views">
        <div>
            <h1 class="section-title">Mapa de técnicos</h1>
            <p class="section-sub">Ubicaciones aproximadas en Bogotá — elige Lista o Mapa</p>
        </div>
        <div class="view-toggle" role="navigation" aria-label="Vista de técnicos">
            <a class="view-toggle-btn" href="/FixNow/tecnicos.php<?= $hay_filtros ? '?' . http_build_query(array_filter(['q' => $q, 'especialidad' => $id_especialidad ?: null, 'barrio' => $id_barrio ?: null])) : '' ?>">Lista</a>
            <a class="view-toggle-btn is-active" href="/FixNow/tecnicosmap.php<?= $hay_filtros ? '?' . http_build_query(array_filter(['q' => $q, 'especialidad' => $id_especialidad ?: null, 'barrio' => $id_barrio ?: null])) : '' ?>" aria-current="page">Mapa</a>
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
                <a href="/FixNow/tecnicosmap.php" class="btn btn-ghost">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="resultados-bar">
        <p>
            <?php if ($total === 0): ?>
                Sin ubicaciones
            <?php elseif ($total === 1): ?>
                <strong>1</strong> técnico en el mapa
            <?php else: ?>
                <strong><?= $total ?></strong> técnicos en el mapa
            <?php endif; ?>
        </p>
    </div>

    <?php if (empty($tecnicos)): ?>
        <div class="empty">
            <strong>No hay técnicos con ubicación</strong>
            Solo se muestran verificados que hayan colocado su pin. Prueba otros filtros o vuelve al listado.
            <div style="margin-top:1rem">
                <a class="btn btn-primary" href="/FixNow/tecnicos.php">Ver listado</a>
            </div>
        </div>
    <?php else: ?>
        <div class="map-layout">
            <div class="map-sheet-backdrop" id="map-sheet-backdrop" hidden></div>
            <aside class="map-sidebar" id="map-sidebar" aria-label="Lista de técnicos en el mapa">
                <div class="map-sheet-handle" aria-hidden="true"></div>
                <div class="map-sidebar-head">
                    <span>En el mapa · <?= $total ?></span>
                    <button type="button" class="map-sheet-close" id="map-sheet-close" aria-label="Cerrar lista">×</button>
                </div>
                <ul class="map-tech-list" id="map-tech-list">
                    <?php foreach ($tecnicos as $t): ?>
                        <li>
                            <button type="button"
                                    class="map-tech-item"
                                    data-id="<?= (int) $t['id_tecnico'] ?>"
                                    data-lat="<?= e((string) $t['latitud']) ?>"
                                    data-lng="<?= e((string) $t['longitud']) ?>">
                                <span class="map-tech-avatar">
                                    <?php if (!empty($t['foto_url'])): ?>
                                        <img src="<?= e($t['foto_url']) ?>" alt="" loading="lazy">
                                    <?php else: ?>
                                        <span class="map-tech-initials"><?= e(mb_strtoupper(mb_substr($t['nombre'], 0, 1) . mb_substr($t['apellido'], 0, 1))) ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="map-tech-info">
                                    <strong><?= e($t['nombre'] . ' ' . $t['apellido']) ?></strong>
                                    <span class="map-tech-meta"><?= e($t['especialidad']) ?> · <?= e($t['barrio']) ?></span>
                                </span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="map-stage">
                <button type="button" class="btn btn-primary btn-sm map-open-list" id="map-open-list" aria-expanded="false" aria-controls="map-sidebar">
                    Ver lista (<?= $total ?>)
                </button>
                <div id="mapa-tecnicos" class="mapa-tecnicos" role="application" aria-label="Mapa interactivo de técnicos"></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($pins)): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
(function () {
    const pins = <?= json_encode($pins, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const mapEl = document.getElementById('mapa-tecnicos');
    if (!mapEl || !pins.length || typeof L === 'undefined') return;

    const map = L.map(mapEl, { scrollWheelZoom: true }).setView([4.65, -74.1], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    const orangeIcon = L.divIcon({
        className: 'fn-marker',
        html: '<span class="fn-marker-pin"></span>',
        iconSize: [32, 42],
        iconAnchor: [16, 40],
        popupAnchor: [0, -36]
    });

    const cluster = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 55,
        iconCreateFunction: function (c) {
            const n = c.getChildCount();
            return L.divIcon({
                html: '<span class="fn-cluster">' + n + '</span>',
                className: 'fn-cluster-wrap',
                iconSize: L.point(40, 40)
            });
        }
    });

    const markersById = {};

    pins.forEach(function (p) {
        const foto = p.foto
            ? '<img class="fn-popup-foto" src="' + p.foto.replace(/"/g, '&quot;') + '" alt="">'
            : '<div class="fn-popup-foto fn-popup-foto--empty"></div>';
        const html =
            '<div class="fn-popup">' +
                foto +
                '<div class="fn-popup-body">' +
                    '<strong class="fn-popup-name">' + escapeHtml(p.nombre) + '</strong>' +
                    '<span class="fn-popup-esp">' + escapeHtml(p.especialidad) + '</span>' +
                    '<span class="fn-popup-barrio">📍 ' + escapeHtml(p.barrio) + '</span>' +
                    '<div class="fn-popup-actions">' +
                        '<a class="btn btn-primary btn-sm" href="' + p.perfil + '">Ver perfil</a>' +
                        '<a class="btn btn-whatsapp btn-sm" href="' + p.whatsapp + '">WhatsApp</a>' +
                    '</div>' +
                '</div>' +
            '</div>';

        const m = L.marker([p.lat, p.lng], { icon: orangeIcon, title: p.nombre });
        m.bindPopup(html, { maxWidth: 280, className: 'fn-popup-wrap' });
        markersById[p.id] = m;
        cluster.addLayer(m);
    });

    map.addLayer(cluster);
    if (pins.length === 1) {
        map.setView([pins[0].lat, pins[0].lng], 14);
    } else {
        map.fitBounds(cluster.getBounds().pad(0.12));
    }

    function focusTech(id, lat, lng) {
        const m = markersById[id];
        if (!m) return;
        map.setView([lat, lng], 15, { animate: true });
        cluster.zoomToShowLayer(m, function () {
            m.openPopup();
        });
        document.querySelectorAll('.map-tech-item.is-active').forEach(function (el) {
            el.classList.remove('is-active');
        });
        const btn = document.querySelector('.map-tech-item[data-id="' + id + '"]');
        if (btn) btn.classList.add('is-active');
        closeSheet();
    }

    document.querySelectorAll('.map-tech-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            focusTech(
                parseInt(btn.getAttribute('data-id'), 10),
                parseFloat(btn.getAttribute('data-lat')),
                parseFloat(btn.getAttribute('data-lng'))
            );
        });
    });

    const sidebar = document.getElementById('map-sidebar');
    const openList = document.getElementById('map-open-list');
    const closeList = document.getElementById('map-sheet-close');
    const backdrop = document.getElementById('map-sheet-backdrop');

    function refreshMapSize() {
        setTimeout(function () { map.invalidateSize(); }, 80);
    }

    function openSheet() {
        if (sidebar) sidebar.classList.add('is-open');
        document.body.classList.add('map-sheet-open');
        if (backdrop) backdrop.hidden = false;
        if (openList) openList.setAttribute('aria-expanded', 'true');
    }
    function closeSheet() {
        if (sidebar) sidebar.classList.remove('is-open');
        document.body.classList.remove('map-sheet-open');
        if (backdrop) backdrop.hidden = true;
        if (openList) openList.setAttribute('aria-expanded', 'false');
        refreshMapSize();
    }

    if (openList) openList.addEventListener('click', openSheet);
    if (closeList) closeList.addEventListener('click', closeSheet);
    if (backdrop) backdrop.addEventListener('click', closeSheet);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSheet();
    });
    window.addEventListener('resize', refreshMapSize);
    window.addEventListener('orientationchange', refreshMapSize);

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    refreshMapSize();
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
