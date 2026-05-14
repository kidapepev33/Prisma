<?php
// Configuracion de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'prisma_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Crear conexion
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexion
if ($conn->connect_error) {
    die('Error de conexion: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Asegura compatibilidad con instalaciones antiguas.
$hasRoleColumn = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");
if ($hasRoleColumn && $hasRoleColumn->num_rows === 0) {
    $conn->query("ALTER TABLE usuarios ADD COLUMN rol ENUM('admin', 'estudiante') NOT NULL DEFAULT 'estudiante' AFTER password");
}

// Crea/actualiza el admin solicitado: admin@gmail / 123
$adminEmail = 'admin@gmail';
$adminPassword = '123';
$adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);

$adminStmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
$adminStmt->bind_param('s', $adminEmail);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();
$adminUser = $adminResult->fetch_assoc();
$adminStmt->close();

if ($adminUser) {
    $adminId = (int)$adminUser['id'];
    $updateAdminStmt = $conn->prepare("UPDATE usuarios SET rol = 'admin', password = ? WHERE id = ?");
    $updateAdminStmt->bind_param('si', $adminHash, $adminId);
    $updateAdminStmt->execute();
    $updateAdminStmt->close();
} else {
    $nombre = 'Admin';
    $apellido = 'Prisma';
    $insertAdminStmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol, fecha_registro) VALUES (?, ?, ?, ?, 'admin', NOW())");
    $insertAdminStmt->bind_param('ssss', $nombre, $apellido, $adminEmail, $adminHash);
    $insertAdminStmt->execute();
    $insertAdminStmt->close();
}
?>
