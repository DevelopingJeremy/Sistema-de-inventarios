<?php
require_once __DIR__ . '/../../../config/db.php';


// Verificaciones para las sesiones de los usuarios

    //? Verificar sesion, si no iniciar una
    function iniSesion() {
        if (session_status() !== 2) { // Verifica si hay que crear una sesion
            session_start();
        }
    }

    //? Validar si hay algun dato en sesion para saber si hay una sesión activa
    function validarSesion($ubicacion) {
        if (!isset($_SESSION['sesion']) || $_SESSION['sesion'] == false) {
            header("Location: $ubicacion");
            exit;
        }
    }

    function inactividad($ubicacion) {
        $tiempoInactivo = 600; // 600 = 10 minutos

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $tiempoInactivo) {
        session_unset();     // Borra todas las variables de sesión
        session_destroy();   // Destruye la sesión
        header("Location: $ubicacion"); // Redirige al login
        exit;
    }

        $_SESSION['LAST_ACTIVITY'] = time(); // Actualiza la hora de última actividad
    }

    // Corroborar si el correo está verificado
    function verifiCorreo ($pathCorreo) {
        global $conn;

        $sql = $conn->prepare("SELECT correo_verifi FROM t_usuarios WHERE ID_USUARIO = ?");
        $sql->bind_param("s", $_SESSION['id_usuario']);
        $sql->execute();
        $resultado = $sql->get_result();

        if ($resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            if ($fila['correo_verifi'] == 1) {
                $_SESSION['correo_verificado'] = true;
            } else {
                $_SESSION['correo_verificado'] = false;
            }
        }

        if (!$_SESSION['correo_verificado']) {
            header('Location: ' . $pathCorreo . '');
            exit;
        }
    }

    // Corroborar si tiene empresa registrada
    function verifiEmpresa($pathEmpresa) {
        if (!$_SESSION['empresa_creada']) {
            header('Location: ' . $pathEmpresa . '');
            exit;
        }
    }

    function validTotales($ubicacion, $pathCorreo, $pathEmpresa) {
        iniSesion();
        validarSesion($ubicacion);
        inactividad($ubicacion);
        verifiCorreo($pathCorreo);
        verifiEmpresa($pathEmpresa);
    }


?>