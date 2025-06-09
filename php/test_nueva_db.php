<?php
session_start(); // Iniciar sesión

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener variables de entorno de Railway
$host = $_ENV['MYSQLHOST'] ?? 'mysql.railway.internal';
$port = $_ENV['MYSQLPORT'] ?? '3306';
$username = $_ENV['MYSQLUSER'] ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? 'HSAMHafjuMemQqRQCwWHAGepZqdYOweQ';
$database = $_ENV['MYSQLDATABASE'] ?? 'railway';

// Crear conexión
$conn = new mysqli($host, $username, $password, $database, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

echo "Conexión exitosa a la base de datos!";
// Verificar qué tablas existen
echo "<h3>📋 Tablas disponibles:</h3>";
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    echo "• " . $row[0] . "<br>";
}

// Verificar estructura de la tabla usuarios_db
echo "<br><h3>🏗️ Estructura de usuarios_db:</h3>";
$result = $conn->query("DESCRIBE usuarios_db");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "• <strong>" . $row['Field'] . "</strong>: " . $row['Type'] . "<br>";
    }
} else {
    echo "❌ Error: La tabla usuarios_db no existe<br>";
}

// Probar inserción de prueba
echo "<br><h3>🧪 Prueba de inserción:</h3>";
$test_name = "Daniel Test";
$test_email = "test" . time() . "@example.com"; // Email único
$test_password = password_hash("123456", PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios_db (nombre, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sss", $test_name, $test_email, $test_password);
    
    if ($stmt->execute()) {
        echo "✅ ¡Inserción exitosa! La nueva base de datos funciona correctamente<br>";
        echo "📧 Email de prueba usado: $test_email";
    } else {
        echo "❌ Error en inserción: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "❌ Error preparando consulta: " . $conn->error;
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
</style>
