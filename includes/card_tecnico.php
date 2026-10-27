<?php
/**
 * Partial: tarjeta de técnico
 * Requiere $t con: id_tecnico, nombre, apellido, descripcion, foto_url, especialidad, barrio
 * Opcional: $mostrar_wa (bool)
 */
$iniciales = mb_strtoupper(mb_substr($t['nombre'], 0, 1) . mb_substr($t['apellido'], 0, 1));
$mostrar_wa = $mostrar_wa ?? false;
?>
<article class="card-tecnico">
    <div class="card-foto">
        <?php if (!empty($t['foto_url'])): ?>
            <img src="<?= e($t['foto_url']) ?>" alt="<?= e($t['nombre'] . ' ' . $t['apellido']) ?>" loading="lazy">
        <?php else: ?>
            <div class="avatar-fallback"><?= e($iniciales) ?></div>
        <?php endif; ?>
        <span class="card-badge-check">Verificado</span>
    </div>
    <div class="card-body">
        <h3><?= e($t['nombre'] . ' ' . $t['apellido']) ?></h3>
        <div class="card-meta-row">
            <span class="badge"><?= e($t['especialidad']) ?></span>
            <span class="meta meta-loc">📍 <?= e($t['barrio']) ?></span>
        </div>
        <p class="card-desc"><?= e($t['descripcion'] ?? '') ?></p>
        <div class="card-actions">
            <a class="btn btn-primary btn-sm" href="/FixNow/tecnico.php?id=<?= (int) $t['id_tecnico'] ?>">Ver perfil</a>
            <?php if ($mostrar_wa): ?>
                <a class="btn btn-whatsapp btn-sm" href="/FixNow/tecnico.php?id=<?= (int) $t['id_tecnico'] ?>&amp;wa=1">WhatsApp</a>
            <?php endif; ?>
        </div>
    </div>
</article>
