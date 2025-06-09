<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener variables de entorno de Railway
$host = $_ENV['MYSQLHOST'] ?? 'mysql.railway.internal';
$port = $_ENV['MYSQLPORT'] ?? '3306';
$username = $_ENV['MYSQLUSER'] ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? 'SfMgBkpQmMNTodyyLNfgDYHqUDxuBXZm';
$database = $_ENV['MYSQLDATABASE'] ?? 'railway';

// Crear conexión
$conn = new mysqli($host, $username, $password, $database, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

echo "Conexión exitosa a la base de datos!";

echo "<h2>🛠️ Creando tabla usuarios_db</h2>";

// Eliminar tabla si existe
$sql_drop = "DROP TABLE IF EXISTS usuarios_db";
if ($conn->query($sql_drop) === TRUE) {
    echo "✅ Tabla anterior eliminada<br>";
} else {
    echo "⚠️ No había tabla anterior<br>";
}

// Crear nueva tabla
$sql_create = "CREATE TABLE usuarios_db (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fecha_registro DATE DEFAULT (CURRENT_DATE)
)";

if ($conn->query($sql_create) === TRUE) {
    echo "✅ ¡Tabla usuarios_db creada exitosamente!<br><br>";
    
    // Verificar estructura
    echo "<h3>📋 Estructura de la tabla:</h3>";
    $result = $conn->query("DESCRIBE usuarios_db");
    while ($row = $result->fetch_assoc()) {
        echo "• <strong>" . $row['Field'] . "</strong>: " . $row['Type'] . "<br>";
    }
    
    echo "<br>🎉 <strong>¡La tabla está lista para registros!</strong>";
    
} else {
    echo "❌ Error creando tabla: " . $conn->error;
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
</style>