<?php
session_start(); // Iniciar sesión

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$conn = new mysqli("mysql.railway.internal", "root", "VXTTjXyzYZzTpkYeWTgwiFYNaNMoyoDI", "railway");

// Verificar conexión
if ($conn->connect_error) {
    die('<div class="error-message">
            <i class="error-icon">❌</i>
            <span>Error de conexión: ' . $conn->connect_error . '</span>
         </div>');
}

// Validar datos recibidos
if (empty($_POST['email']) || empty($_POST['password'])) {
    die('<div class="error-message">
            <i class="error-icon">⚠️</i>
            <span>Por favor, completa todos los campos.</span>
         </div>');
}

// Recibir datos del formulario
$email = $_POST['email'];
$password = $_POST['password'];

// Buscar el usuario por email
$sql = "SELECT id, nombre, email, password FROM usuarios_db WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verificar la contraseña
        if (password_verify($password, $user['password'])) {
            // Login exitoso - crear sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            echo '<div class="success-message">
                    <i class="success-icon">🎉</i>
                    <span>¡Bienvenido ' . htmlspecialchars($user['nombre']) . '! Login exitoso.</span>
                  </div>';
                  
            // Opcional: redirigir a dashboard o página principal
            // header("Location: dashboard.php");
            // exit();
            
        } else {
            // Contraseña incorrecta
            echo '<div class="error-message">
                    <i class="error-icon">🔒</i>
                    <span>Contraseña incorrecta. Por favor, verifica tus datos.</span>
                  </div>';
        }
    } else {
        // Usuario no encontrado
        echo '<div class="error-message">
                <i class="error-icon">👤</i>
                <span>No existe una cuenta con ese email. ¿Ya te registraste?</span>
              </div>';
    }
    
    $stmt->close();
} else {
    echo '<div class="error-message">
            <i class="error-icon">❌</i>
            <span>Error al procesar el login: ' . $conn->error . '</span>
          </div>';
}

$conn->close();
?>