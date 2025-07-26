<?php

    include_once('verificaciones-sesion.php');
    iniSesion();
    validarSesion('../../../public/sesion/iniciar-sesion.php');
    include('../../../config/db.php');

    session_destroy();
    session_abort();

    header("Location: ../../../index.html");
    exit;
?>