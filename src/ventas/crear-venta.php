<?php
session_start();
require_once '../../config/db.php';

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
    http_response_code(400);
    echo json_encode(['error' => 'Usuario no tiene empresa registrada']);
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

try {
    // Obtener datos del formulario
    $id_cliente = (int)($_POST['id_cliente'] ?? 0);
    $numero_venta = trim($_POST['numero_venta'] ?? '');
    $fecha_venta = $_POST['fecha_venta'] ?? date('Y-m-d H:i:s');
    $subtotal = (float)($_POST['subtotal'] ?? 0);
    $descuento = (float)($_POST['descuento'] ?? 0);
    $iva = (float)($_POST['iva'] ?? 0);
    $total = (float)($_POST['total'] ?? 0);
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
    $estado = $_POST['estado'] ?? 'pendiente';
    $referencia_pago = trim($_POST['referencia_pago'] ?? '');
    $notas = trim($_POST['notas'] ?? '');
    $productos = json_decode($_POST['productos'] ?? '[]', true);

    // Validaciones
    $errores = [];

    if ($id_cliente <= 0) {
        $errores[] = 'Debe seleccionar un cliente';
    }

    if (empty($numero_venta)) {
        $errores[] = 'El número de venta es obligatorio';
    }

    if (empty($productos) || count($productos) === 0) {
        $errores[] = 'Debe agregar al menos un producto';
    }

    if ($subtotal < 0) {
        $errores[] = 'El subtotal no puede ser negativo';
    }

    if ($descuento < 0) {
        $errores[] = 'El descuento no puede ser negativo';
    }

    if ($iva < 0) {
        $errores[] = 'El IVA no puede ser negativo';
    }

    if ($total < 0) {
        $errores[] = 'El total no puede ser negativo';
    }

    // Verificar que el cliente pertenece a la empresa
    $stmt_cliente = $conn->prepare("SELECT ID_CLIENTE FROM t_clientes WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?");
    $stmt_cliente->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_cliente->execute();
    if (!$stmt_cliente->get_result()->fetch_assoc()) {
        $errores[] = 'Cliente no válido';
    }

    // Verificar que el número de venta no existe
    $stmt_numero = $conn->prepare("SELECT ID_VENTA FROM t_ventas WHERE numero_venta = ? AND ID_EMPRESA = ?");
    $stmt_numero->bind_param("si", $numero_venta, $id_empresa);
    $stmt_numero->execute();
    if ($stmt_numero->get_result()->fetch_assoc()) {
        $errores[] = 'El número de venta ya existe';
    }

    if (!empty($errores)) {
        http_response_code(400);
        echo json_encode(['error' => 'Errores de validación', 'errores' => $errores]);
        exit;
    }

    // Iniciar transacción
    $conn->beginTransaction();

    try {
        // Insertar venta
        $sql_venta = "INSERT INTO t_ventas (
                        ID_EMPRESA, ID_CLIENTE, ID_USUARIO, numero_venta, fecha_venta,
                        subtotal, descuento, iva, total, metodo_pago, estado,
                        referencia_pago, notas
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_venta = $conn->prepare($sql_venta);
        $stmt_venta->bind_param("iiissddddsss", 
            $id_empresa, $id_cliente, $id_usuario, $numero_venta, $fecha_venta,
            $subtotal, $descuento, $iva, $total, $metodo_pago, $estado,
            $referencia_pago, $notas
        );
        $stmt_venta->execute();

        $id_venta = $conn->insert_id;

        // Insertar detalles de venta
        $sql_detalle = "INSERT INTO t_ventas_detalle (
                            ID_VENTA, ID_PRODUCTO, cantidad, precio_unitario, 
                            subtotal, descuento
                        ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt_detalle = $conn->prepare($sql_detalle);

        foreach ($productos as $producto) {
            $stmt_detalle->bind_param("iidddd", 
                $id_venta,
                $producto['id_producto'],
                $producto['cantidad'],
                $producto['precio_unitario'],
                $producto['subtotal'],
                $producto['descuento'] ?? 0
            );
            $stmt_detalle->execute();
        }

        // Actualizar última compra del cliente
        $sql_ultima_compra = "UPDATE t_clientes SET ultima_compra = ? WHERE ID_CLIENTE = ?";
        $stmt_ultima = $conn->prepare($sql_ultima_compra);
        $stmt_ultima->bind_param("si", $fecha_venta, $id_cliente);
        $stmt_ultima->execute();

        // Confirmar transacción
        $conn->commit();

        echo json_encode([
            'success' => true,
            'mensaje' => 'Venta creada exitosamente',
            'id_venta' => $id_venta
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}

$conn->close();
?> 