<?php
    session_start();
    include('../../../config/db.php');
    require_once('../../../src/auth/sesion/verificaciones-sesion.php');
    
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../../../public/inventario/categorias.php?error=1&message=" . urlencode('Método no permitido'));
        exit();
    }
    
    // Obtener el ID de la categoría del formulario POST
    $id_categoria = $_POST['id_categoria'] ?? null;
    
    // Log para depuración
    error_log("Eliminar categoría - ID recibido: " . ($id_categoria ?? 'null'));
    error_log("Eliminar categoría - POST completo: " . json_encode($_POST));
    
    if (!$id_categoria) {
        header("Location: ../../../public/inventario/categorias.php?error=1&message=" . urlencode('ID de categoría no proporcionado'));
        exit();
    }
    
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: ../../../public/inventario/categorias.php?error=1&message=" . urlencode('Usuario no autenticado'));
        exit();
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
            header("Location: ../../../public/inventario/categorias.php?error=1&message=" . urlencode('Categoría no encontrada o no tienes permisos para eliminarla'));
            exit();
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
        
        // Si hay productos asociados, actualizarlos para que queden sin categoría
        if ($productos_result['total_productos'] > 0) {
            error_log("Eliminar categoría - Actualizando productos para quitar categoría");
            
            $stmt_update_productos = $conn->prepare("
                UPDATE t_productos 
                SET categoria = NULL, fecha_actualizacion = NOW()
                WHERE categoria = ? AND ID_EMPRESA = ?
            ");
            $stmt_update_productos->bind_param("si", $nombre_categoria, $id_empresa);
            
            if (!$stmt_update_productos->execute()) {
                error_log("Eliminar categoría - Error actualizando productos: " . $stmt_update_productos->error);
                $mensaje = "Error al actualizar productos asociados: " . $stmt_update_productos->error;
                header("Location: ../../../public/inventario/categorias.php?error=1&message=" . urlencode($mensaje));
                exit();
            }
            
            $productos_actualizados = $stmt_update_productos->affected_rows;
            error_log("Eliminar categoría - Productos actualizados: " . $productos_actualizados);
        }
        
        // Eliminar la categoría
        error_log("Eliminar categoría - Intentando eliminar categoría ID: " . $id_categoria);
        
        $stmt_eliminar = $conn->prepare("DELETE FROM t_categorias WHERE ID_CATEGORIA = ?");
        $stmt_eliminar->bind_param("i", $id_categoria);
        
        if ($stmt_eliminar->execute()) {
            error_log("Eliminar categoría - Categoría eliminada exitosamente");
            
            if (isset($productos_actualizados) && $productos_actualizados > 0) {
                $mensaje = "Categoría '$nombre_categoria' eliminada exitosamente. $productos_actualizados productos quedaron sin categoría.";
            } else {
                $mensaje = "Categoría '$nombre_categoria' eliminada exitosamente";
            }
            
            header("Location: ../../../public/inventario/categorias.php?success=1&message=" . urlencode($mensaje));
            exit();
        } else {
            error_log("Eliminar categoría - Error en la eliminación: " . $stmt_eliminar->error);
            $mensaje = 'Error al eliminar la categoría: ' . $stmt_eliminar->error;
            header("Location: ../../../public/inventario/categorias.php?error=1&message=" . urlencode($mensaje));
            exit();
        }
        
    } catch (Exception $e) {
        error_log("Eliminar categoría - Excepción capturada: " . $e->getMessage());
        header("Location: ../../../public/inventario/categorias.php?error=1&message=" . urlencode('Error: ' . $e->getMessage()));
        exit();
    }
    
    $conn->close();
?> 