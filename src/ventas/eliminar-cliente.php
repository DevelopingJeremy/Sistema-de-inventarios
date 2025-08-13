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
    $id_cliente = (int)($_POST['id_cliente'] ?? 0);

    if ($id_cliente <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de cliente inválido']);
        exit;
    }

    // Verificar que el cliente pertenece a la empresa
    $stmt_check = $conn->prepare("SELECT ID_CLIENTE, nombre, apellido FROM t_clientes WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?");
    $stmt_check->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $cliente = $result_check->fetch_assoc();

    if (!$cliente) {
        http_response_code(404);
        echo json_encode(['error' => 'Cliente no encontrado']);
        exit;
    }
    $stmt_check->close();

    // Verificar si el cliente tiene ventas asociadas
    $stmt_ventas = $conn->prepare("SELECT COUNT(*) as total FROM t_ventas WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?");
    $stmt_ventas->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_ventas->execute();
    $result_ventas = $stmt_ventas->get_result();
    $total_ventas = $result_ventas->fetch_assoc()['total'];
    $stmt_ventas->close();

    if ($total_ventas > 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'No se puede eliminar el cliente',
            'mensaje' => "El cliente {$cliente['nombre']} {$cliente['apellido']} tiene $total_ventas venta(s) asociada(s). Debe eliminar las ventas primero."
        ]);
        exit;
    }

    // Eliminar cliente
    $stmt_delete = $conn->prepare("DELETE FROM t_clientes WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?");
    $stmt_delete->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_delete->execute();

    if ($stmt_delete->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'No se pudo eliminar el cliente']);
        exit;
    }
    $stmt_delete->close();

    echo json_encode([
        'success' => true,
        'mensaje' => 'Cliente eliminado exitosamente'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}

$conn->close();
?> 