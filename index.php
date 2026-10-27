<?php
require_once __DIR__ . '/includes/funciones.php';

$titulo_pagina = 'Inicio';

$especialidades = obtenerEspecialidades($conexion);
$destacados = obtenerDestacadosHome($conexion, 6);

$iconos = [
    'Electricista' => '⚡',
    'Plomero' => '🔧',
    'Carpintero' => '🪵',
    'Cerrajero' => '🔑',
    'Pintor' => '🎨',
    'Gasista' => '🔥',
    'Técnico en aires acondicionados' => '❄️',
    'Albañil' => '🧱',
    'Técnico en electrodomésticos' => '🔌',
];

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <section class="hero">
        <div class="hero-brand">
            <img src="/FixNow/img/logo.png" alt="Fix Now" class="hero-logo">
            <h1>Fix <em>Now</em></h1>
        </div>
        <p class="slogan">Porque cada problema merece una solución rápida. Técnicos verificados en Bogotá, listos para tu hogar.</p>
        <div class="acciones" style="margin-bottom:1rem;justify-content:center">
            <a class="btn btn-primary" href="/FixNow/registro.php">Soy técnico · Crear cuenta</a>
            <a class="btn btn-outline" href="/FixNow/login.php">Login</a>
        </div>
        <form class="hero-search" action="/FixNow/tecnicos.php" method="get" role="search">
            <input type="search" name="q" placeholder="Buscar oficio, nombre o barrio…" aria-label="Buscar técnicos" autocomplete="off">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
        <div class="chips" aria-label="Especialidades">
            <?php foreach ($especialidades as $esp): ?>
                <?php $ico = $iconos[$esp['nombre']] ?? '🛠️'; ?>
                <a class="chip" href="/FixNow/tecnicos.php?especialidad=<?= (int) $esp['id_especialidad'] ?>">
                    <span class="chip-icon" aria-hidden="true"><?= $ico ?></span>
                    <?= e($esp['nombre']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="how-it-works" aria-label="Cómo funciona">
        <div class="step-card">
            <div class="step-num">01</div>
            <h3>Elige el oficio</h3>
            <p>Filtra por especialidad o barrio y encuentra técnicos verificados cerca de ti.</p>
        </div>
        <div class="step-card">
            <div class="step-num">02</div>
            <h3>Revisa el perfil</h3>
            <p>Mira experiencia, zona de cobertura y foto del profesional antes de contactar.</p>
        </div>
        <div class="step-card">
            <div class="step-num">03</div>
            <h3>Contacta al instante</h3>
            <p>Escríbele por WhatsApp o envía una solicitud desde el formulario.</p>
        </div>
    </section>

    <div class="section-head">
        <div>
            <h2 class="section-title">Técnicos verificados</h2>
            <p class="section-sub">Profesionales revisados por Fix Now</p>
        </div>
        <a class="btn btn-ghost" href="/FixNow/tecnicos.php">Ver todos</a>
    </div>

    <?php if (empty($destacados)): ?>
        <div class="empty">
            <strong>Sin técnicos por ahora</strong>
            Aún no hay técnicos verificados disponibles.
        </div>
    <?php else: ?>
        <div class="grid-tecnicos">
            <?php foreach ($destacados as $t): ?>
                <?php $mostrar_wa = true; require __DIR__ . '/includes/card_tecnico.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
