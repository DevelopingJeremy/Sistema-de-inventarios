<?php
    ob_start();

    require('mail.php');
    require_once('../auth/sesion/verificaciones-sesion.php');
    iniSesion();

    $correo = $_SESSION['correo_verificado'];



    // Corroborar si el correo ya está verificado
    if (!$correo) {

        // Token de correo
        $token = $_SESSION['token_correo'];

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Verifica tu correo en Hybox';
        $mail->Body    = '<!DOCTYPE html>
                            <html lang="es">
                            <head>
                            <meta charset="UTF-8">
                            <title>Verifica tu correo | Hybox - Hydra Software</title>
                            <style>
                                body {
                                font-family: Arial, sans-serif;
                                background-color: #f4f4f4;
                                margin: 0;
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
                                .btn {
                                display: inline-block;
                                padding: 12px 24px;
                                background-color: #007BFF;
                                color: white !important;
                                text-decoration: none;
                                border-radius: 5px;
                                font-weight: bold;
                                }
                                .btn:hover {
                                background-color: #0056b3;
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
                                <h1 class="title">Bienvenido a Hybox</h1>
                                <p class="subtitle">El sistema de gestión inteligente de <span class="brand">Hydra Software</span>.</p>
                                
                                <p>Gracias por registrarte. Para comenzar, necesitamos que verifiques tu dirección de correo electrónico.</p>
                                
                                <a href=http://localhost/hybox.cloud/src/mail/verificar-token.php?id=' . urlencode($_SESSION['id_usuario']) . '&token=' . urlencode($token) . ' class="btn">Verificar Correo</a>
                                
                                <p class="footer">
                                Si no creaste una cuenta en Hybox, puedes ignorar este mensaje.<br><br>
                                &copy; 2025 <span class="brand">Hydra Software</span>. Todos los derechos reservados.
                                </p>
                            </div>
                            </body>
                            </html>
                            ';

        $mail->send();
        header('Location: ../../public/sesion/verificar-correo.php');
        exit;
    } else {
        echo 'Correo ya verificado';
    }
    ob_end_flush();
?>