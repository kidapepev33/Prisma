<?php
function requireAdminOrExit() {
    if (!isset($_SESSION['user_id']) || (($_SESSION['rol'] ?? 'estudiante') !== 'admin')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso restringido a administradores']);
        exit;
    }
}

function getJsonBody() {
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}
?>
