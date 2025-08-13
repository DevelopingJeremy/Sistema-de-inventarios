<?php
require_once '../../config/db.php';

// Verificar sesión simple
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
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
    header('Location: ../../public/ventas/nuevo-cliente.php?error=1&mensaje=' . urlencode('Usuario no tiene empresa registrada'));
    exit;
}

try {
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $tipo_cliente = $_POST['tipo_cliente'] ?? 'regular';
    $metodo_pago_preferido = $_POST['metodo_pago_preferido'] ?? 'efectivo';
    
    // Si el método de pago está vacío, usar efectivo por defecto
    if (empty($metodo_pago_preferido)) {
        $metodo_pago_preferido = 'efectivo';
    }
    $limite_credito = (float)($_POST['limite_credito'] ?? 0);
    $descuento = (float)($_POST['descuento'] ?? 0);
    $notas = trim($_POST['notas'] ?? '');
    $estado = 'activo'; // Por defecto activo

    // Validaciones
    $errores = [];

    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio';
    }

    if (empty($apellido)) {
        $errores[] = 'El apellido es obligatorio';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no es válido';
    }

    if (!empty($email)) {
        // Verificar si el email ya existe
        $stmt_check = $conn->prepare("SELECT ID_CLIENTE FROM t_clientes WHERE email = ? AND ID_EMPRESA = ?");
        $stmt_check->bind_param("si", $email, $id_empresa);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $errores[] = 'El email ya está registrado';
        }
        $stmt_check->close();
    }

    if ($limite_credito < 0) {
        $errores[] = 'El límite de crédito no puede ser negativo';
    }

    if ($descuento < 0 || $descuento > 100) {
        $errores[] = 'El descuento debe estar entre 0 y 100%';
    }

    if (!empty($errores)) {
        $errores_str = implode(', ', $errores);
        header('Location: ../../public/ventas/nuevo-cliente.php?error=1&mensaje=' . urlencode($errores_str));
        exit;
    }

    // Insertar cliente (solo campos básicos por ahora)
    $sql = "INSERT INTO t_clientes (
                ID_EMPRESA, nombre, apellido, email, telefono, direccion, 
                tipo_cliente, metodo_pago_preferido, limite_credito, descuento, 
                notas, estado, fecha_registro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssddss", 
        $id_empresa, $nombre, $apellido, $email, $telefono, $direccion,
        $tipo_cliente, $metodo_pago_preferido, $limite_credito, $descuento,
        $notas, $estado
    );
    $stmt->execute();

    $id_cliente = $conn->insert_id;
    $stmt->close();

    // Redirigir a la página de clientes con mensaje de éxito
    header('Location: ../../public/ventas/clientes.php?success=1&mensaje=Cliente creado exitosamente');
    exit;

} catch (Exception $e) {
    header('Location: ../../public/ventas/nuevo-cliente.php?error=1&mensaje=' . urlencode('Error en la base de datos: ' . $e->getMessage()));
    exit;
}

$conn->close();
?> 