<?php
    include('../../../config/db.php');
    require_once('../../../src/auth/sesion/verificaciones-sesion.php');
    
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }
    
    // Obtener el ID de la categoría del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    $id_categoria = $input['id'] ?? null;
    
    // Log para depuración
    error_log("Eliminar categoría - ID recibido: " . ($id_categoria ?? 'null'));
    error_log("Eliminar categoría - Input completo: " . json_encode($input));
    
    if (!$id_categoria) {
        echo json_encode(['success' => false, 'message' => 'ID de categoría no proporcionado']);
        exit;
    }
    
    $id_usuario = $_SESSION['id_usuario'];
    
    try {
        error_log("Eliminar categoría - Usuario ID: " . $id_usuario);
        
        // Verificar que la categoría pertenece a la empresa del usuario
        $stmt_verificar = $conn->prepare("
            SELECT c.ID_CATEGORIA, c.nombre_categoria, e.ID_EMPRESA
            FROM t_categorias c
            INNER JOIN t_empresa e ON c.ID_EMPRESA = e.ID_EMPRESA
            WHERE c.ID_CATEGORIA = ? AND e.ID_DUEÑO = ?
        ");
        $stmt_verificar->bind_param("ii", $id_categoria, $id_usuario);
        $stmt_verificar->execute();
        $result = $stmt_verificar->get_result();
        
        error_log("Eliminar categoría - Categorías encontradas: " . $result->num_rows);
        
        if ($result->num_rows === 0) {
            error_log("Eliminar categoría - Categoría no encontrada para ID: " . $id_categoria);
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada o no tienes permisos para eliminarla']);
            exit;
        }
        
        $categoria = $result->fetch_assoc();
        $nombre_categoria = $categoria['nombre_categoria'];
        $id_empresa = $categoria['ID_EMPRESA'];
        
        error_log("Eliminar categoría - Categoría encontrada: " . $nombre_categoria . " (Empresa ID: " . $id_empresa . ")");
        
        // Verificar si hay productos asociados a esta categoría
        $stmt_productos = $conn->prepare("
            SELECT COUNT(*) as total_productos
            FROM t_productos 
            WHERE categoria = ? AND ID_EMPRESA = ?
        ");
        $stmt_productos->bind_param("si", $nombre_categoria, $id_empresa);
        $stmt_productos->execute();
        $productos_result = $stmt_productos->get_result()->fetch_assoc();
        
        error_log("Eliminar categoría - Productos asociados: " . $productos_result['total_productos']);
        
        if ($productos_result['total_productos'] > 0) {
            error_log("Eliminar categoría - No se puede eliminar, tiene productos asociados");
            echo json_encode([
                'success' => false, 
                'message' => "No se puede eliminar la categoría '$nombre_categoria' porque tiene {$productos_result['total_productos']} productos asociados. Primero mueve o elimina los productos."
            ]);
            exit;
        }
        
        // Eliminar la categoría
        error_log("Eliminar categoría - Intentando eliminar categoría ID: " . $id_categoria);
        
        $stmt_eliminar = $conn->prepare("DELETE FROM t_categorias WHERE ID_CATEGORIA = ?");
        $stmt_eliminar->bind_param("i", $id_categoria);
        
        if ($stmt_eliminar->execute()) {
            error_log("Eliminar categoría - Categoría eliminada exitosamente");
            echo json_encode([
                'success' => true, 
                'message' => "Categoría '$nombre_categoria' eliminada exitosamente"
            ]);
        } else {
            error_log("Eliminar categoría - Error en la eliminación: " . $stmt_eliminar->error);
            echo json_encode([
                'success' => false, 
                'message' => 'Error al eliminar la categoría: ' . $stmt_eliminar->error
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Eliminar categoría - Excepción capturada: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    
    $conn->close();
?> 