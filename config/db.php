<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar variables de entorno desde la raíz
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Conexión a la base de datos
global $conn;
$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer codificación UTF-8
$conn->set_charset("utf8");
?>
