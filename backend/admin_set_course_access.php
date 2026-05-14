<?php
session_start();
require_once 'connect.php';
require_once 'admin_helpers.php';
header('Content-Type: application/json');

requireAdminOrExit();

$payload = getJsonBody();
$courseId = (int)($payload['course_id'] ?? 0);
$studentIds = $payload['student_ids'] ?? [];

if ($courseId <= 0 || !is_array($studentIds)) {
    echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
    exit;
}

$conn->begin_transaction();

try {
    $deleteStmt = $conn->prepare("DELETE FROM course_access WHERE course_id = ?");
    $deleteStmt->bind_param("i", $courseId);
    $deleteStmt->execute();
    $deleteStmt->close();

    if (!empty($studentIds)) {
        $insertStmt = $conn->prepare("INSERT INTO course_access (course_id, user_id) VALUES (?, ?)");
        foreach ($studentIds as $sid) {
            $sid = (int)$sid;
            if ($sid <= 0) {
                continue;
            }
            $insertStmt->bind_param("ii", $courseId, $sid);
            $insertStmt->execute();
        }
        $insertStmt->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Accesos actualizados']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'No se pudieron actualizar los accesos']);
}

$conn->close();
?>
