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

try {
    // Obtener datos del formulario
    $id_venta = (int)($_POST['id_venta'] ?? 0);
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

    if ($id_venta <= 0) {
        $errores[] = 'ID de venta inválido';
    }

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

    if (!empty($errores)) {
        http_response_code(400);
        echo json_encode(['error' => 'Errores de validación', 'errores' => $errores]);
        exit;
    }

    // Verificar que la venta pertenece a la empresa
    $stmt_venta = $conn->prepare("SELECT ID_VENTA, estado FROM t_ventas WHERE ID_VENTA = ? AND ID_EMPRESA = ?");
    $stmt_venta->bind_param("ii", $id_venta, $id_empresa);
    $stmt_venta->execute();
    $result_venta = $stmt_venta->get_result();
    $venta_existente = $result_venta->fetch_assoc();

    if (!$venta_existente) {
        http_response_code(404);
        echo json_encode(['error' => 'Venta no encontrada']);
        exit;
    }
    $stmt_venta->close();

    // No permitir editar ventas completadas
    if ($venta_existente['estado'] === 'completada') {
        http_response_code(400);
        echo json_encode(['error' => 'No se puede editar una venta completada']);
        exit;
    }

    // Verificar que el cliente pertenece a la empresa
    $stmt_cliente = $conn->prepare("SELECT ID_CLIENTE FROM t_clientes WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?");
    $stmt_cliente->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    if ($result_cliente->num_rows === 0) {
        $errores[] = 'Cliente no válido';
    }
    $stmt_cliente->close();

    // Verificar que el número de venta no existe (excluyendo la venta actual)
    $stmt_numero = $conn->prepare("SELECT ID_VENTA FROM t_ventas WHERE numero_venta = ? AND ID_EMPRESA = ? AND ID_VENTA != ?");
    $stmt_numero->bind_param("sii", $numero_venta, $id_empresa, $id_venta);
    $stmt_numero->execute();
    $result_numero = $stmt_numero->get_result();
    if ($result_numero->num_rows > 0) {
        $errores[] = 'El número de venta ya existe';
    }
    $stmt_numero->close();

    if (!empty($errores)) {
        http_response_code(400);
        echo json_encode(['error' => 'Errores de validación', 'errores' => $errores]);
        exit;
    }

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Actualizar venta
        $sql_venta = "UPDATE t_ventas SET 
                        ID_CLIENTE = ?, numero_venta = ?, fecha_venta = ?,
                        subtotal = ?, descuento = ?, iva = ?, total = ?, 
                        metodo_pago = ?, estado = ?, referencia_pago = ?, notas = ?
                    WHERE ID_VENTA = ? AND ID_EMPRESA = ?";

        $stmt_venta = $conn->prepare($sql_venta);
        $stmt_venta->bind_param("issddddsssii", 
            $id_cliente, $numero_venta, $fecha_venta,
            $subtotal, $descuento, $iva, $total,
            $metodo_pago, $estado, $referencia_pago, $notas,
            $id_venta, $id_empresa
        );
        $stmt_venta->execute();
        $stmt_venta->close();

        // Eliminar detalles existentes
        $sql_delete_detalle = "DELETE FROM t_ventas_detalle WHERE ID_VENTA = ?";
        $stmt_delete = $conn->prepare($sql_delete_detalle);
        $stmt_delete->bind_param("i", $id_venta);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Insertar nuevos detalles de venta
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
        $stmt_detalle->close();

        // Actualizar última compra del cliente
        $sql_ultima_compra = "UPDATE t_clientes SET ultima_compra = ? WHERE ID_CLIENTE = ?";
        $stmt_ultima = $conn->prepare($sql_ultima_compra);
        $stmt_ultima->bind_param("si", $fecha_venta, $id_cliente);
        $stmt_ultima->execute();
        $stmt_ultima->close();

        // Confirmar transacción
        $conn->commit();

        echo json_encode([
            'success' => true,
            'mensaje' => 'Venta actualizada exitosamente'
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}

$conn->close();
?> 