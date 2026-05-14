<?php
session_start();
require_once 'connect.php';
require_once 'admin_helpers.php';
header('Content-Type: application/json');

requireAdminOrExit();

$studentsResult = $conn->query("SELECT id, nombre, apellido, email, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
$students = [];
while ($row = $studentsResult->fetch_assoc()) {
    $row['id'] = (int)$row['id'];
    $students[] = $row;
}

$coursesQuery = "
SELECT c.id, c.titulo, c.descripcion, c.contenido_json, c.plantilla_nombre, c.fecha_actualizacion,
       u.nombre AS creator_nombre, u.apellido AS creator_apellido
FROM cursos c
JOIN usuarios u ON c.creator_id = u.id
ORDER BY c.fecha_actualizacion DESC";

$coursesResult = $conn->query($coursesQuery);
$courses = [];
while ($row = $coursesResult->fetch_assoc()) {
    $courseId = (int)$row['id'];

    $accessStmt = $conn->prepare("SELECT user_id FROM course_access WHERE course_id = ?");
    $accessStmt->bind_param("i", $courseId);
    $accessStmt->execute();
    $accessResult = $accessStmt->get_result();

    $access = [];
    while ($accessRow = $accessResult->fetch_assoc()) {
        $access[] = (int)$accessRow['user_id'];
    }
    $accessStmt->close();

    $row['id'] = $courseId;
    $row['allowed_users'] = $access;
    $courses[] = $row;
}

$templatesResult = $conn->query("SELECT id, nombre, contenido_json FROM course_templates ORDER BY fecha_creacion DESC");
$templates = [];
while ($row = $templatesResult->fetch_assoc()) {
    $row['id'] = (int)$row['id'];
    $templates[] = $row;
}

echo json_encode([
    'success' => true,
    'students' => $students,
    'courses' => $courses,
    'templates' => $templates
]);

$conn->close();
?>
