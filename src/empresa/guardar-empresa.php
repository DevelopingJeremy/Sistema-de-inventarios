<?php

    require_once('../auth/sesion/verificaciones-sesion.php');
    iniSesion();
    validarSesion('../../../public/sesion/iniciar-sesion.php');
    inactividad('../../../public/sesion/iniciar-sesion.php');
    verifiCorreo('../../../public/sesion/envio-correo.php');
    include('../../config/db.php');

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        // Obtener datos de registrar-empresa.php
        $nombre = $_POST['nombre'];
        $empleados = $_POST['empleados'];
        $categoria = $_POST['categoria'];
        // Si el usuario escoge otra categoria
        if ($categoria == 'Otro') {
            $categoria = $_POST['otra_categoria'];
        }
        $moneda = $_POST['moneda'];
        $pais = $_POST['pais'];
        $idDueño = $_SESSION['id_usuario'];

        // Guardar Logo y obtener path
        // Verificar que el directorio exista
        $uploadDir = '../../uploads/img/logos-empresa/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Validar si se subió un archivo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['logo']['tmp_name'];
            $originalName = $_FILES['logo']['name'];
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);

            // Generar un nombre único para evitar conflictos
            $newFileName = uniqid('logo_', true) . '.' . $extension;
            $destinationPath = $uploadDir . $newFileName;

            // Mover el archivo al destino
            if (move_uploaded_file($tmpName, $destinationPath)) {
                // Guardar la ruta parcial en la variable
                $logo = 'logos-empresa/' . $newFileName;
            } else {
                // Fallo al mover el archivo
                $_SESSION['mensaje_error'] = "Error al subir el logo.";
                header("Location: ../../public/empresa/registrar-empresa.php");
                $logo = null;
            }
        } else {
            // No se subió un archivo
            $logo = null;
        }

        // Registrar empresa en base de datos
        $sql = $conn->prepare("INSERT INTO t_empresa(ID_DUEÑO, nombre_empresa, categoria, empleados, logo, moneda, pais) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $sql->bind_param("issssss", $idDueño, $nombre, $categoria, $empleados, $logo, $moneda, $pais);
        if ($sql->execute()) {

            // Obtener ID insertado
            $idEmpresa = $conn->insert_id;
            // Asignar empresa a usuario
            
            // Asignar empresa al usuario actual
            $correo = $_SESSION['correo'];
            $sqlUpdate = $conn->prepare("UPDATE t_usuarios SET ID_EMPRESA = ? WHERE correo = ?");
            $sqlUpdate->bind_param("is", $idEmpresa, $correo);

            if ($sqlUpdate->execute()) {
                $_SESSION['empresa_creada'] = true;
                $_SESSION['mensaje_exito'] = "Empresa registrada correctamente";
                header("Location: ../../public/dashboard/dashboard.php");
            } else {
                $_SESSION['mensaje_error'] = "Error al registrar la empresa";
                header("Location: ../../public/empresa/registrar-empresa.php");
            }
        } else {
            $_SESSION['mensaje_error'] = "Error al registrar la empresa";
            header("Location: ../../public/empresa/registrar-empresa.php");
        }
    }



?>