<?php
session_start();
require_once('../../config/db.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar si es una petición POST
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
$id_venta = isset($_POST['id_venta']) ? (int)$_POST['id_venta'] : 0;

if ($id_venta <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de venta inválido']);
    exit;
}

try {
    // Verificar que la venta pertenece a la empresa del usuario
    $stmt_verificar = $conn->prepare("SELECT ID_VENTA, estado FROM t_ventas WHERE ID_VENTA = ? AND ID_EMPRESA = ?");
    $stmt_verificar->bind_param("ii", $id_venta, $id_empresa);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    
    if ($result_verificar->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Venta no encontrada']);
        exit;
    }
    
    $venta = $result_verificar->fetch_assoc();
    
    // Solo permitir eliminar ventas en estado 'pendiente' o 'cancelada'
    if ($venta['estado'] === 'completada') {
        http_response_code(400);
        echo json_encode(['error' => 'No se puede eliminar una venta completada']);
        exit;
    }
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Eliminar detalles de la venta
        $stmt_detalles = $conn->prepare("DELETE FROM t_ventas_detalle WHERE ID_VENTA = ?");
        $stmt_detalles->bind_param("i", $id_venta);
        $stmt_detalles->execute();
        
        // Eliminar la venta
        $stmt_venta = $conn->prepare("DELETE FROM t_ventas WHERE ID_VENTA = ? AND ID_EMPRESA = ?");
        $stmt_venta->bind_param("ii", $id_venta, $id_empresa);
        $stmt_venta->execute();
        
        if ($stmt_venta->affected_rows === 0) {
            throw new Exception('No se pudo eliminar la venta');
        }
        
        // Confirmar transacción
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Venta eliminada correctamente'
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al eliminar la venta: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 