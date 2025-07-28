<?php
require_once('../../auth/sesion/verificaciones-sesion.php');
validTotales('../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php');
include('../../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    try {
        $id_producto = intval($_POST['id_producto']);
        $id_usuario = $_SESSION['id_usuario'];
        
        // Verificar que el producto pertenece a la empresa del usuario
        $stmt = $conn->prepare("
            SELECT p.*, p.imagen 
            FROM t_productos p 
            INNER JOIN t_empresa e ON p.ID_EMPRESA = e.ID_EMPRESA 
            WHERE p.ID_PRODUCTO = ? AND e.ID_DUEÑO = ?
        ");
        $stmt->bind_param("ii", $id_producto, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Producto no encontrado o no tienes permisos para eliminarlo');
        }
        
        $producto = $result->fetch_assoc();
        
        // Eliminar la imagen si existe y no es una URL externa
        if (!empty($producto['imagen']) && !filter_var($producto['imagen'], FILTER_VALIDATE_URL)) {
            $ruta_imagen = '../../../' . $producto['imagen'];
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
        }
        
        // Eliminar el producto de la base de datos
        $stmt = $conn->prepare("DELETE FROM t_productos WHERE ID_PRODUCTO = ?");
        $stmt->bind_param("i", $id_producto);
        
        if ($stmt->execute()) {
            header("Location: ../../../public/inventario/productos.php?success=1&message=Producto eliminado exitosamente");
            exit();
        } else {
            throw new Exception('Error al eliminar el producto de la base de datos');
        }
        
    } catch (Exception $e) {
        header("Location: ../../../public/inventario/productos.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Si no es POST o no hay ID, redirigir
    header("Location: ../../../public/inventario/productos.php");
    exit();
}
?> 