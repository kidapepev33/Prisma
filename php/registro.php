<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$conn = new mysqli("mysql.railway.internal", "root", "VXTTjXyzYZzTpkYeWTgwiFYNaNMoyoDI", "railway");

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Validar datos recibidos
if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['password'])) {
    die("Por favor, completa todos los campos.");
}

// Recibir datos del formulario
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = $_POST['password'];

// Verificar si el email ya existe
$checkEmail = "SELECT id FROM usuarios_db WHERE email = ?";
$checkStmt = $conn->prepare($checkEmail);

if ($checkStmt) {
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // El email ya existe clases van entren los echo tambien con die entre parentensis
        echo "❌ Error: Ese email ya está en uso. Por favor, usa otro email.";
        $checkStmt->close();
        $conn->close();
        exit(); // Detener la ejecución
    }
    
    $checkStmt->close();
} else {
    echo "❌ Error al verificar el email: " . $conn->error;
    $conn->close();
    exit();
}

// Si llegamos aquí, el email no existe, proceder con el registro
// Encriptar la contraseña
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insertar en la base de datos
$sql = "INSERT INTO usuarios_db (nombre, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sss", $nombre, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        echo "✅ Registro exitoso";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
} else {
    echo "❌ Error en la preparación de la consulta: " . $conn->error;
}

$stmt->close();
$conn->close();
?>