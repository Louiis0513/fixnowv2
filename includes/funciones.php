<?php
/**
 * Funciones auxiliares Fix Now
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/cache.php';

/**
 * Escapa HTML para prevenir XSS
 */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Exige sesión de administrador; redirige al login si no hay
 */
function requiereAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function tecnicoLogueado(): bool
{
    return !empty($_SESSION['tecnico_id']);
}

function requiereTecnico(): void
{
    if (!tecnicoLogueado()) {
        header('Location: /FixNow/login.php');
        exit;
    }
}

function iniciarSesionTecnico(array $row): void
{
    $_SESSION['tecnico_id'] = (int) $row['id_tecnico'];
    $_SESSION['tecnico_nombre'] = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? ''));
    $_SESSION['tecnico_correo'] = (string) ($row['correo'] ?? '');
    unset($_SESSION['admin_id'], $_SESSION['admin_nombre'], $_SESSION['admin_correo']);
}

function cerrarSesionTecnico(): void
{
    unset($_SESSION['tecnico_id'], $_SESSION['tecnico_nombre'], $_SESSION['tecnico_correo']);
}

/** Solo dígitos de cédula colombiana (6–12). */
function normalizarCedula(string $cc): string
{
    return preg_replace('/\D+/', '', $cc) ?? '';
}

function cedulaValida(string $cc): bool
{
    $n = normalizarCedula($cc);
    return strlen($n) >= 6 && strlen($n) <= 12;
}

/**
 * Normaliza teléfono para wa.me (solo dígitos, con 57 si es celular local)
 */
function telefonoWhatsApp(string $telefono): string
{
    $digitos = preg_replace('/\D+/', '', $telefono);
    if (strlen($digitos) === 10 && str_starts_with($digitos, '3')) {
        return '57' . $digitos;
    }
    return $digitos;
}

function guardarFlash(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function tomarFlash(): ?array
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

/** @return list<array{id_especialidad:int|string,nombre:string}> */
function obtenerEspecialidades(mysqli $db): array
{
    $cached = cacheGet('especialidades');
    if (is_array($cached)) {
        return $cached;
    }
    $rows = [];
    $res = $db->query('SELECT id_especialidad, nombre FROM especialidad ORDER BY nombre');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    cacheSet('especialidades', $rows, CACHE_TTL_CATALOGO);
    return $rows;
}

/** @return list<array{id_barrio:int|string,nombre:string}> */
function obtenerBarrios(mysqli $db): array
{
    $cached = cacheGet('barrios');
    if (is_array($cached)) {
        return $cached;
    }
    $rows = [];
    $res = $db->query('SELECT id_barrio, nombre FROM barrio ORDER BY nombre');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    cacheSet('barrios', $rows, CACHE_TTL_CATALOGO);
    return $rows;
}

/** @return list<array<string,mixed>> */
function obtenerDestacadosHome(mysqli $db, int $limit = 6): array
{
    $key = 'home_destacados';
    $cached = cacheGet($key);
    if (is_array($cached)) {
        return $cached;
    }
    $rows = [];
    $sql = 'SELECT t.id_tecnico, t.nombre, t.apellido, t.descripcion, t.foto_url,
                   e.nombre AS especialidad, b.nombre AS barrio
            FROM tecnico t
            INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad
            INNER JOIN barrio b ON b.id_barrio = t.id_barrio
            WHERE t.verificado = 1
            ORDER BY t.fecha_registro DESC
            LIMIT ' . (int) $limit;
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    cacheSet($key, $rows, CACHE_TTL_HOME);
    return $rows;
}

/**
 * @return list<array<string,mixed>>
 */
function obtenerTecnicosPublicos(mysqli $db, int $idEsp, int $idBarrio, string $q): array
{
    $key = cacheTecnicosListaKey($idEsp, $idBarrio, $q);
    $cached = cacheGet($key);
    if (is_array($cached)) {
        return $cached;
    }

    $sql = 'SELECT t.id_tecnico, t.nombre, t.apellido, t.descripcion, t.foto_url, t.telefono,
                   e.nombre AS especialidad, b.nombre AS barrio
            FROM tecnico t
            INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad
            INNER JOIN barrio b ON b.id_barrio = t.id_barrio
            WHERE t.verificado = 1';
    $params = [];
    $types = '';

    if ($idEsp > 0) {
        $sql .= ' AND t.id_especialidad = ?';
        $types .= 'i';
        $params[] = $idEsp;
    }
    if ($idBarrio > 0) {
        $sql .= ' AND t.id_barrio = ?';
        $types .= 'i';
        $params[] = $idBarrio;
    }
    if ($q !== '') {
        $sql .= ' AND (t.nombre LIKE ? OR t.apellido LIKE ? OR t.descripcion LIKE ? OR e.nombre LIKE ? OR b.nombre LIKE ?)';
        $like = '%' . $q . '%';
        $types .= 'sssss';
        array_push($params, $like, $like, $like, $like, $like);
    }
    $sql .= ' ORDER BY t.nombre, t.apellido';

    $rows = [];
    $stmt = $db->prepare($sql);
    if ($stmt) {
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    }
    cacheSet($key, $rows, CACHE_TTL_LISTA);
    return $rows;
}

/**
 * @return list<array<string,mixed>>
 */
function obtenerTecnicosMapa(mysqli $db, int $idEsp, int $idBarrio, string $q): array
{
    $key = cacheTecnicosMapaKey($idEsp, $idBarrio, $q);
    $cached = cacheGet($key);
    if (is_array($cached)) {
        return $cached;
    }

    $sql = 'SELECT t.id_tecnico, t.nombre, t.apellido, t.descripcion, t.foto_url, t.telefono,
                   t.latitud, t.longitud,
                   e.nombre AS especialidad, e.id_especialidad,
                   b.nombre AS barrio, b.id_barrio
            FROM tecnico t
            INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad
            INNER JOIN barrio b ON b.id_barrio = t.id_barrio
            WHERE t.verificado = 1
              AND t.latitud IS NOT NULL
              AND t.longitud IS NOT NULL';
    $params = [];
    $types = '';

    if ($idEsp > 0) {
        $sql .= ' AND t.id_especialidad = ?';
        $types .= 'i';
        $params[] = $idEsp;
    }
    if ($idBarrio > 0) {
        $sql .= ' AND t.id_barrio = ?';
        $types .= 'i';
        $params[] = $idBarrio;
    }
    if ($q !== '') {
        $sql .= ' AND (t.nombre LIKE ? OR t.apellido LIKE ? OR t.descripcion LIKE ? OR e.nombre LIKE ? OR b.nombre LIKE ?)';
        $like = '%' . $q . '%';
        $types .= 'sssss';
        array_push($params, $like, $like, $like, $like, $like);
    }
    $sql .= ' ORDER BY t.nombre, t.apellido';

    $rows = [];
    $stmt = $db->prepare($sql);
    if ($stmt) {
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    }
    cacheSet($key, $rows, CACHE_TTL_LISTA);
    return $rows;
}

/** @return array<string,mixed>|null */
function obtenerTecnicoPublico(mysqli $db, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    $key = 'tecnico_' . $id;
    $cached = cacheGet($key);
    if (is_array($cached)) {
        return $cached;
    }
    if ($cached === false) {
        return null;
    }

    $stmt = $db->prepare(
        'SELECT t.*, e.nombre AS especialidad, b.nombre AS barrio
         FROM tecnico t
         INNER JOIN especialidad e ON e.id_especialidad = t.id_especialidad
         INNER JOIN barrio b ON b.id_barrio = t.id_barrio
         WHERE t.id_tecnico = ? AND t.verificado = 1
         LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if ($row) {
        cacheSet($key, $row, CACHE_TTL_FICHA);
    } else {
        // miss breve para no martillar BD con IDs inventados
        cacheSet($key, false, 300);
    }
    return $row;
}

/**
 * Tras mutación de técnico: parche ficha + invalidar listados.
 */
function trasCambioTecnico(mysqli $db, int $idTecnico): void
{
    cacheParcheFichaTecnico($db, $idTecnico);
}

function trasCambioCatalogo(): void
{
    cacheForget('especialidades');
    cacheForget('barrios');
    cacheForget('home_destacados');
    cacheForgetPrefix('tecnicos_lista_');
    cacheForgetPrefix('tecnicos_mapa_');
}
