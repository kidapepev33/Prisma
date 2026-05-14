<?php
session_start();
require_once 'connect.php';
require_once 'admin_helpers.php';
header('Content-Type: application/json');

requireAdminOrExit();

$payload = getJsonBody();
$nombre = trim($payload['nombre'] ?? '');
$apellido = trim($payload['apellido'] ?? '');
$email = trim($payload['email'] ?? '');
$password = $payload['password'] ?? '';

if (!$nombre || !$apellido || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Completa nombre, apellido, email y contrasena']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email invalido']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contrasena debe tener 6+ caracteres']);
    exit;
}

$existsStmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$existsStmt->bind_param("s", $email);
$existsStmt->execute();
$existsStmt->store_result();
if ($existsStmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Ese email ya existe']);
    $existsStmt->close();
    exit;
}
$existsStmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$insertStmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol, fecha_registro) VALUES (?, ?, ?, ?, 'estudiante', NOW())");
$insertStmt->bind_param("ssss", $nombre, $apellido, $email, $hash);

if (!$insertStmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'No se pudo crear el estudiante']);
    $insertStmt->close();
    exit;
}

$newId = $conn->insert_id;
$insertStmt->close();

echo json_encode(['success' => true, 'message' => 'Estudiante creado', 'student_id' => (int)$newId]);
$conn->close();
?>
