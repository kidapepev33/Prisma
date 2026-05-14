<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email y contrasena son obligatorios']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, nombre, apellido, email, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        $stmt->close();
        exit;
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['apellido'] = $user['apellido'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['rol'] = $user['rol'];

    echo json_encode([
        'success' => true,
        'message' => 'Login exitoso',
        'user' => [
            'id' => (int)$user['id'],
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'rol' => $user['rol']
        ]
    ]);

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
}

$conn->close();
?>
