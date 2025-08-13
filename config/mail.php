<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar variables de entorno desde la raíz
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Datos de configuración de correo
$hostMail     = $_ENV['HOSTMAIL'];
$usernameMail = $_ENV['USERMAILNAME'];
$passMail     = $_ENV['PASSMAIL'];
$SMTPSecure   = $_ENV['SMTPSECURE'];
$PortMail     = $_ENV['PORTMAIL'];

$mailAlias = $_ENV['MAILALIAS'];
$nameMail= $_ENV['NAME'];
?>
