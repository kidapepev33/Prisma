<?php
session_start();
require_once 'connect.php';
require_once 'admin_helpers.php';
header('Content-Type: application/json');

requireAdminOrExit();

$payload = getJsonBody();
$name = trim($payload['name'] ?? '');
$content = $payload['content'] ?? null;

if (!$name || !is_array($content)) {
    echo json_encode(['success' => false, 'message' => 'Nombre y contenido son obligatorios']);
    exit;
}

$contentJson = json_encode($content, JSON_UNESCAPED_UNICODE);
$createdBy = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO course_templates (nombre, contenido_json, created_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE contenido_json = VALUES(contenido_json), created_by = VALUES(created_by)");
$stmt->bind_param("ssi", $name, $contentJson, $createdBy);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar el template']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Template guardado']);
$conn->close();
?>
