<?php
session_start();
require_once '../../config/db.php';
require_once '../auth/verificaciones-sesion.php';

// Verificar sesión
if (!verificarSesion()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
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
$id_producto = (int)($_GET['id'] ?? 0);

if ($id_producto <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de producto inválido']);
    exit;
}

try {
    // Obtener información del producto
    $sql = "SELECT 
                p.*,
                c.nombre as categoria_nombre
            FROM t_productos p
            LEFT JOIN t_categorias c ON p.ID_CATEGORIA = c.ID_CATEGORIA
            WHERE p.ID_PRODUCTO = ? AND p.ID_EMPRESA = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_producto, $id_empresa]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        http_response_code(404);
        echo json_encode(['error' => 'Producto no encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'producto' => $producto
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?> 