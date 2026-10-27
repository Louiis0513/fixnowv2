<?php
/**
 * Conexión mysqli a la base de datos Fix Now
 */
$host = 'localhost';
$usuario = 'root';
$password = '';
$basedatos = 'fixnow';

$conexion = new mysqli($host, $usuario, $password, $basedatos);

if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}

$conexion->set_charset('utf8mb4');
