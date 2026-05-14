<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'prisma_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Crear conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset UTF-8
$conn->set_charset("utf8mb4");
?>