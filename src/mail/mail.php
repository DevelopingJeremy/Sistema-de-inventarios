<?php

require_once('../auth/sesion/verificaciones-sesion.php');
iniSesion();
validarSesion('../../public/sesion/iniciar-sesion.php');
require('../../config/mail.php');

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

////Load Composer's autoloader (created by composer, not included with PHPMailer)
// Por instalar directamente en el sistema se utiliza esto
require '../../vendor/PHPMailer/src/Exception.php';
require '../../vendor/PHPMailer/src/PHPMailer.php';
require '../../vendor/PHPMailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 2;                   //Enable verbose debug output
    $mail->isSMTP();                        //Send using SMTP
    $mail->Host       = $hostMail;          //Set the SMTP server to send through
    $mail->SMTPAuth   = true;               //Enable SMTP authentication
    $mail->Username   = $usernameMail;      //SMTP username
    $mail->Password   = $passMail;          //SMTP password
    $mail->SMTPSecure = $SMTPSecure;        //Enable implicit TLS encryption 
    $mail->Port       = $PortMail;          //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //* Recipients
    $mail->setFrom('noreply@jeremyqg.com', 'Hybox');                   // Enviado desde
    $mail->addAddress($_SESSION['correo']);                      // Enviado hacia

    //* //Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
} catch (Exception $e) {
    echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
}
?>