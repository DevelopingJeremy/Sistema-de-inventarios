<?php
require_once('../../auth/sesion/verificaciones-sesion.php');
validTotales('../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php');
include('../../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $tipo_movimiento = trim($_POST['tipo_movimiento']);
        $id_producto = intval($_POST['id_producto']);
        $cantidad = intval($_POST['cantidad']);
        $precio_unitario = floatval($_POST['precio_unitario'] ?? 0);
        $motivo = trim($_POST['motivo']);
        $fecha_movimiento = $_POST['fecha_movimiento'] ?: date('Y-m-d H:i:s');
        $referencia = trim($_POST['referencia'] ?? '');
        $proveedor_cliente = trim($_POST['proveedor_cliente'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');
        
        // Validaciones básicas
        if (empty($tipo_movimiento) || !in_array($tipo_movimiento, ['entrada', 'salida'])) {
            throw new Exception('Tipo de movimiento inválido');
        }
        
        if (empty($id_producto)) {
            throw new Exception('Debe seleccionar un producto');
        }
        
        if ($cantidad <= 0) {
            throw new Exception('La cantidad debe ser mayor a 0');
        }
        
        if ($precio_unitario < 0) {
            throw new Exception('El precio no puede ser negativo');
        }
        
        if (empty($motivo)) {
            throw new Exception('El motivo es obligatorio');
        }
        
        // Obtener ID de la empresa del usuario
        $id_usuario = $_SESSION['id_usuario'];
        $stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_empresa WHERE ID_DUEÑO = ?");
        $stmt_empresa->bind_param("i", $id_usuario);
        $stmt_empresa->execute();
        $empresa_result = $stmt_empresa->get_result()->fetch_assoc();
        $id_empresa = $empresa_result['ID_EMPRESA'];
        
        // Verificar que el producto pertenece a la empresa
        $stmt_producto = $conn->prepare("
            SELECT nombre_producto, stock, precio 
            FROM t_productos 
            WHERE ID_PRODUCTO = ? AND ID_EMPRESA = ?
        ");
        $stmt_producto->bind_param("ii", $id_producto, $id_empresa);
        $stmt_producto->execute();
        $producto = $stmt_producto->get_result()->fetch_assoc();
        
        if (!$producto) {
            throw new Exception('Producto no encontrado o no tienes permisos para acceder a él');
        }
        
        // Verificar stock disponible para salidas
        if ($tipo_movimiento === 'salida' && $producto['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente. Disponible: {$producto['stock']}, Solicitado: {$cantidad}");
        }
        
        // Calcular nuevo stock
        $nuevo_stock = $tipo_movimiento === 'entrada' ? 
            $producto['stock'] + $cantidad : 
            $producto['stock'] - $cantidad;
        
        // Calcular valor del movimiento
        $valor_movimiento = $cantidad * $precio_unitario;
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Registrar el movimiento
            $stmt_movimiento = $conn->prepare("
                INSERT INTO t_movimientos_inventario (
                    ID_EMPRESA, ID_PRODUCTO, ID_USUARIO, tipo_movimiento, 
                    cantidad, precio_unitario, valor_movimiento, motivo, 
                    fecha_movimiento, referencia, proveedor_cliente, documento, observaciones
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_movimiento->bind_param("iiisiddssssss", 
                $id_empresa, $id_producto, $id_usuario, $tipo_movimiento,
                $cantidad, $precio_unitario, $valor_movimiento, $motivo, 
                $fecha_movimiento, $referencia, $proveedor_cliente, $documento, $observaciones
            );
            $stmt_movimiento->execute();
            
            // Actualizar stock del producto
            $stmt_update_stock = $conn->prepare("
                UPDATE t_productos 
                SET stock = ?, fecha_actualizacion = NOW()
                WHERE ID_PRODUCTO = ?
            ");
            $stmt_update_stock->bind_param("ii", $nuevo_stock, $id_producto);
            $stmt_update_stock->execute();
            
            $conn->commit();
            
            // Redirigir con mensaje de éxito
            header("Location: ../../../public/inventario/movimientos.php?success=1&message=Movimiento registrado exitosamente. Stock actualizado a: {$nuevo_stock}");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception('Error al registrar el movimiento: ' . $e->getMessage());
        }
        
    } catch (Exception $e) {
        // Redirigir con mensaje de error
        header("Location: ../../../public/inventario/nuevo-movimiento.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Si no es POST, redirigir a la página de movimientos
    header("Location: ../../../public/inventario/movimientos.php");
    exit();
}
?> 