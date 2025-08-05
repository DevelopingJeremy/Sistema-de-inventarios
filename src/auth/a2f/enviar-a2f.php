<?php
    ob_start();
    require_once('../sesion/verificaciones-sesion.php');
    iniSesion();
    include('../../../config/db.php');
    require('../../../config/mail.php');

    
    //Import PHPMailer classes into the global namespace
    //These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;


    // Verificar que el usuario tenga sesión básica iniciada
    if (isset($_SESSION['id_usuario']) && isset($_SESSION['correo'])) {

        $id_usuario = $_SESSION['id_usuario'];
        $codigo = rand(1000, 9999);
        $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Insertar en BD
        $stmt = $conn->prepare("INSERT INTO codigos_2fa (ID_USUARIO, codigo, expiracion) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_usuario, $codigo, $expiracion);
        $stmt->execute();


        ////Load Composer's autoloader (created by composer, not included with PHPMailer)
        // Por instalar directamente en el sistema se utiliza esto
        require '../../../vendor/PHPMailer/src/Exception.php';
        require '../../../vendor/PHPMailer/src/PHPMailer.php';
        require '../../../vendor/PHPMailer/src/SMTP.php';

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
            exit;
        }

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Verificacion A2F - Hybox';
        $mail->Body    = '<!DOCTYPE html>
                            <html lang="es">
                            <head>
                                <meta charset="UTF-8">
                                <title>Código A2F | Hybox - Hydra Software</title>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        background-color: #f4f4f4;
                                        padding: 20px;
                                    }
                                    .container {
                                        background-color: #ffffff;
                                        max-width: 600px;
                                        margin: auto;
                                        padding: 30px;
                                        border-radius: 8px;
                                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                                        text-align: center;
                                    }
                                    .title {
                                        font-size: 24px;
                                        color: #333333;
                                        margin-bottom: 10px;
                                    }
                                    .subtitle {
                                        font-size: 16px;
                                        color: #555555;
                                        margin-bottom: 30px;
                                    }
                                    .code {
                                        font-size: 38px;
                                        font-weight: bold;
                                        color: #007BFF;
                                        letter-spacing: 8px;
                                        margin-bottom: 10px;
                                    }
                                    .expiration {
                                        font-size: 14px;
                                        color: #777777;
                                        margin-bottom: 20px;
                                    }
                                    .footer {
                                        margin-top: 40px;
                                        font-size: 12px;
                                        color: #999999;
                                    }
                                    .brand {
                                        font-weight: bold;
                                        color: #007BFF;
                                    }
                                </style>
                            </head>
                            <body>
                            <div class="container">
                                <h1 class="title">Autenticación en Dos Factores (A2F)</h1>
                                <p class="subtitle">Código de seguridad para tu cuenta en <span class="brand">Hybox</span>.</p>

                                <p>Usá el siguiente código para activar o acceder con A2F:</p>
                                <div class="code">' . $codigo . '</div>
                                <p class="expiration">Este código expira en 15 minutos.</p>

                                <p class="footer">
                                    Si no solicitaste este código, ignorá este mensaje.<br><br>
                                    &copy; 2025 <span class="brand">Hydra Software</span>. Todos los derechos reservados.
                                </p>
                            </div>
                            </body>
                            </html>
';

        $mail->send();
        header('Location: ../../../public/sesion/a2f/iniciar-a2f.php');
        exit;
    } else {
        // Usuario no tiene sesión válida
        header("Location: ../../../public/sesion/iniciar-sesion.php");
        exit();
    }
    ob_end_flush();
?>