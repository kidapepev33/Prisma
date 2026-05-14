<?php
session_start();
require_once 'connect.php';
require_once 'admin_helpers.php';
header('Content-Type: application/json');

requireAdminOrExit();

$payload = getJsonBody();
$courseId = isset($payload['course_id']) ? (int)$payload['course_id'] : 0;
$title = trim($payload['title'] ?? '');
$description = trim($payload['description'] ?? '');
$templateName = trim($payload['template_name'] ?? '');
$content = $payload['content'] ?? null;

if (!$title || !is_array($content)) {
    echo json_encode(['success' => false, 'message' => 'Titulo y contenido del curso son obligatorios']);
    exit;
}

$contentJson = json_encode($content, JSON_UNESCAPED_UNICODE);
if ($contentJson === false) {
    echo json_encode(['success' => false, 'message' => 'No se pudo serializar el contenido']);
    exit;
}

if ($courseId > 0) {
    $stmt = $conn->prepare("UPDATE cursos SET titulo = ?, descripcion = ?, contenido_json = ?, plantilla_nombre = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $description, $contentJson, $templateName, $courseId);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el curso']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Curso actualizado', 'course_id' => $courseId]);
    exit;
}

$creatorId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO cursos (creator_id, titulo, descripcion, contenido_json, plantilla_nombre) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $creatorId, $title, $description, $contentJson, $templateName);
$ok = $stmt->execute();
$newId = $conn->insert_id;
$stmt->close();

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'No se pudo crear el curso']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Curso creado', 'course_id' => (int)$newId]);
$conn->close();
?>
