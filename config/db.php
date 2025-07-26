<?php

// Datos para la conexión
$servername = "localhost";
$username = "root";
$password = '';
$dbname = "hybox";

global $conn;
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer codificación UTF-8
$conn->set_charset("utf8");


?>