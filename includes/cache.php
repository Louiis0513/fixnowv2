<?php
declare(strict_types=1);

/**
 * Caché en archivos (hosting compartido).
 * TTL largo + parche write-through al cambiar técnicos / catálogos.
 */
const CACHE_DIR = __DIR__ . '/../cache';
const CACHE_TTL_DEFAULT = 14400; // 4 h
const CACHE_TTL_CATALOGO = 14400;
const CACHE_TTL_LISTA = 3600;    // 1 h — listados filtrados
const CACHE_TTL_FICHA = 14400;
const CACHE_TTL_HOME = 3600;

function cacheDir(): string
{
    if (!is_dir(CACHE_DIR)) {
        @mkdir(CACHE_DIR, 0775, true);
    }
    return CACHE_DIR;
}

function cachePath(string $key): string
{
    $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $key) ?: 'key';
    return cacheDir() . '/' . $safe . '.cache';
}

function cacheGet(string $key): mixed
{
    $path = cachePath($key);
    if (!is_file($path)) {
        return null;
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return null;
    }
    $payload = @unserialize($raw, ['allowed_classes' => false]);
    if (!is_array($payload) || !array_key_exists('expires', $payload) || !array_key_exists('data', $payload)) {
        @unlink($path);
        return null;
    }
    if ((int) $payload['expires'] < time()) {
        @unlink($path);
        return null;
    }
    return $payload['data'];
}

function cacheSet(string $key, mixed $data, int $ttl = CACHE_TTL_DEFAULT): void
{
    $path = cachePath($key);
    $payload = serialize([
        'expires' => time() + max(1, $ttl),
        'data' => $data,
    ]);
    $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, $payload, LOCK_EX) === false) {
        return;
    }
    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        @file_put_contents($path, $payload, LOCK_EX);
    }
}

function cacheForget(string $key): void
{
    $path = cachePath($key);
    if (is_file($path)) {
        @unlink($path);
    }
}

function cacheForgetPrefix(string $prefix): void
{
    $dir = cacheDir();
    $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $prefix) ?: '';
    if ($safe === '' || !is_dir($dir)) {
        return;
    }
    foreach (glob($dir . '/' . $safe . '*.cache') ?: [] as $file) {
        @unlink($file);
    }
}

/** Clave de listado público filtrado. */
function cacheTecnicosListaKey(int $idEsp, int $idBarrio, string $q): string
{
    $qNorm = mb_strtolower(trim($q));
    return 'tecnicos_lista_' . $idEsp . '_' . $idBarrio . '_' . ($qNorm === '' ? 'all' : md5($qNorm));
}

function cacheTecnicosMapaKey(int $idEsp, int $idBarrio, string $q): string
{
    $qNorm = mb_strtolower(trim($q));
    return 'tecnicos_mapa_' . $idEsp . '_' . $idBarrio . '_' . ($qNorm === '' ? 'all' : md5($qNorm));
}

/**
 * Tras crear/editar/verificar/eliminar un técnico:
 * invalida listados/mapa/home y refresca o borra la ficha.
 */
function cacheInvalidarTecnicosPublicos(?int $idTecnico = null): void
{
    cacheForget('especialidades');
    cacheForget('barrios');
    cacheForget('home_destacados');
    cacheForgetPrefix('tecnicos_lista_');
    cacheForgetPrefix('tecnicos_mapa_');
    if ($idTecnico !== null && $idTecnico > 0) {
        cacheForget('tecnico_' . $idTecnico);
    } else {
        cacheForgetPrefix('tecnico_');
    }
}

/** Parche puntual de ficha (si está verificada) o forget si ya no es pública. */
function cacheParcheFichaTecnico(mysqli $db, int $idTecnico): void
{
    if ($idTecnico <= 0) {
        return;
    }
    $stmt = $db->prepare(
        'SELECT t.*, e.nombre AS especialidad, b.nombre AS barrio
         FROM tecnico t
         INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad
         INNER JOIN barrio b ON b.id_barrio = t.id_barrio
         WHERE t.id_tecnico = ? AND t.verificado = 1
         LIMIT 1'
    );
    $stmt->bind_param('i', $idTecnico);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $key = 'tecnico_' . $idTecnico;
    if ($row) {
        cacheSet($key, $row, CACHE_TTL_FICHA);
    } else {
        cacheForget($key);
    }
    // Listados dependen de filtros: invalidar agregados (parche inteligente = no wipe de fichas ajenas)
    cacheForget('home_destacados');
    cacheForgetPrefix('tecnicos_lista_');
    cacheForgetPrefix('tecnicos_mapa_');
}
