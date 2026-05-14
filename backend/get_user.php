<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => (int)$_SESSION['user_id'],
            'nombre' => $_SESSION['nombre'],
            'apellido' => $_SESSION['apellido'],
            'email' => $_SESSION['email'],
            'rol' => $_SESSION['rol'] ?? 'estudiante'
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesion activa'
    ]);
}
?>
