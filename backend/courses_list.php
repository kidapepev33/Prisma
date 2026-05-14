<?php
session_start();
require_once 'connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesion']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$role = $_SESSION['rol'] ?? 'estudiante';

if ($role === 'admin') {
    $query = "SELECT id, titulo, descripcion, fecha_actualizacion FROM cursos ORDER BY fecha_actualizacion DESC";
    $result = $conn->query($query);
} else {
    $stmt = $conn->prepare("SELECT c.id, c.titulo, c.descripcion, c.fecha_actualizacion
                            FROM cursos c
                            JOIN course_access ca ON c.id = ca.course_id
                            WHERE ca.user_id = ?
                            ORDER BY c.fecha_actualizacion DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
}

$courses = [];
while ($row = $result->fetch_assoc()) {
    $row['id'] = (int)$row['id'];
    $courses[] = $row;
}

echo json_encode(['success' => true, 'courses' => $courses, 'role' => $role]);
$conn->close();
?>
