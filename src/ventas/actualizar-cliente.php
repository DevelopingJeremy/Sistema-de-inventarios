<?php
session_start();
require_once '../../config/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../public/ventas/editar-cliente.php');
    $_SESSION['mensaje_error']= 'No autorizado';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/ventas/editar-cliente.php');
    $_SESSION['mensaje_error']= 'Metodo no permitido';
    exit;
}

// Obtener el ID de la empresa del usuario
$stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_usuarios WHERE ID_USUARIO = ?");
$stmt_empresa->bind_param("i", $_SESSION['id_usuario']);
$stmt_empresa->execute();
$result_empresa = $stmt_empresa->get_result();

if ($result_empresa->num_rows > 0) {
    $empresa_data = $result_empresa->fetch_assoc();
    $id_empresa = $empresa_data['ID_EMPRESA'];
} else {
    header('Location: ../../public/ventas/editar-cliente.php');
    $_SESSION['mensaje_error'] = 'Usuario no tiene empresa registrada';
    exit;
}

try {
    // Obtener datos del formulario
    $id_cliente = (int)($_POST['id_cliente'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $tipo_cliente = $_POST['tipo_cliente'] ?? 'regular';
    $metodo_pago_preferido = $_POST['metodo_pago_preferido'] ?? 'efectivo';
    $limite_credito = (float)($_POST['limite_credito'] ?? 0);
    $descuento = (float)($_POST['descuento'] ?? 0);
    $notas = trim($_POST['notas'] ?? '');
    $estado = 'activo'; // Mantener activo por defecto

    // Validaciones
    $errores = [];

    if ($id_cliente <= 0) {
        $errores[] = 'ID de cliente inválido';
    }

    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio';
    }

    if (empty($apellido)) {
        $errores[] = 'El apellido es obligatorio';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no es válido';
    }

    if ($limite_credito < 0) {
        $errores[] = 'El límite de crédito no puede ser negativo';
    }

    if ($descuento < 0 || $descuento > 100) {
        $errores[] = 'El descuento debe estar entre 0 y 100%';
    }

    if (!empty($errores)) {
        $mensaje_error = implode(', ', $errores);
        header('Location: ../../public/ventas/editar-cliente.php');
        $_SESSION['mensaje_error'] = $mensaje_error;
        exit;
    }

    // Verificar que el cliente pertenece a la empresa
    $stmt_check = $conn->prepare("SELECT ID_CLIENTE FROM t_clientes WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?");
    $stmt_check->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows === 0) {
        header('Location: ../../public/ventas/editar-cliente.php');
        $_SESSION['mensaje_error'] = 'Cliente no encontrado';
        exit;
    }
    $stmt_check->close();

    // Verificar si el email ya existe (excluyendo el cliente actual)
    if (!empty($email)) {
        $stmt_email = $conn->prepare("SELECT ID_CLIENTE FROM t_clientes WHERE email = ? AND ID_EMPRESA = ? AND ID_CLIENTE != ?");
        $stmt_email->bind_param("sii", $email, $id_empresa, $id_cliente);
        $stmt_email->execute();
        $result_email = $stmt_email->get_result();
        if ($result_email->num_rows > 0) {
            $_SESSION['mensaje_error'] = 'El email ya está registrado por otro cliente';
            header('Location: ../../public/ventas/editar-cliente.php?id=' . $id_cliente);
            exit;
        }
        $stmt_email->close();
    }

    // Actualizar cliente
    $sql = "UPDATE t_clientes SET 
                nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ?,
                tipo_cliente = ?, metodo_pago_preferido = ?, limite_credito = ?, 
                descuento = ?, notas = ?
            WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssddsii", 
        $nombre, $apellido, $email, $telefono, $direccion,
        $tipo_cliente, $metodo_pago_preferido, $limite_credito, $descuento,
        $notas, $id_cliente, $id_empresa
    );
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        header('Location: ../../public/ventas/editar-cliente.php');
        $_SESSION['mensaje_error'] = 'No se pudo actualizar el cliente';
        exit;
    }
    $stmt->close();

    // Redirigir a la página de clientes con mensaje de éxito
    header('Location: ../../public/ventas/clientes.php?success=1&mensaje=' . urlencode('Cliente actualizado exitosamente'));
    exit;

} catch (Exception $e) {
    header('Location: ../../public/ventas/editar-cliente.php');
    $_SESSION['mensaje_error'] = 'Error en la base de datos: ' . $e->getMessage();
    exit;
}

$conn->close();
?> 