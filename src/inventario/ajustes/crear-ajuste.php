<?php
require_once('../../auth/sesion/verificaciones-sesion.php');
validTotales('../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php');
include('../../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $tipo_ajuste = trim($_POST['tipo_ajuste']);
        $id_producto = intval($_POST['id_producto']);
        $cantidad_ajustada = intval($_POST['cantidad_ajustada']);
        $motivo_ajuste = trim($_POST['motivo_ajuste']);
        $fecha_ajuste = $_POST['fecha_ajuste'] ?: date('Y-m-d H:i:s');
        $responsable = trim($_POST['responsable'] ?? '');
        $tipo_diferencia = trim($_POST['tipo_diferencia'] ?? '');
        $documento_respaldo = trim($_POST['documento_respaldo'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');
        
        // Validaciones básicas
        if (empty($tipo_ajuste) || !in_array($tipo_ajuste, ['positivo', 'negativo'])) {
            throw new Exception('Tipo de ajuste inválido');
        }
        
        if (empty($id_producto)) {
            throw new Exception('Debe seleccionar un producto');
        }
        
        if ($cantidad_ajustada <= 0) {
            throw new Exception('La cantidad debe ser mayor a 0');
        }
        
        if (empty($motivo_ajuste)) {
            throw new Exception('El motivo del ajuste es obligatorio');
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
        
        // Verificar stock disponible para ajustes negativos
        if ($tipo_ajuste === 'negativo' && $producto['stock'] < $cantidad_ajustada) {
            throw new Exception("Stock insuficiente para ajuste negativo. Disponible: {$producto['stock']}, Solicitado: {$cantidad_ajustada}");
        }
        
        // Calcular nuevo stock
        $stock_anterior = $producto['stock'];
        $stock_nuevo = $tipo_ajuste === 'positivo' ? 
            $producto['stock'] + $cantidad_ajustada : 
            $producto['stock'] - $cantidad_ajustada;
        
        // Calcular valor del ajuste
        $valor_ajuste = $cantidad_ajustada * $producto['precio'];
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Registrar el ajuste
            $stmt_ajuste = $conn->prepare("
                INSERT INTO t_ajustes_inventario (
                    ID_EMPRESA, ID_PRODUCTO, ID_USUARIO, tipo_ajuste, 
                    cantidad_ajustada, stock_anterior, stock_nuevo, 
                    valor_ajuste, motivo_ajuste, fecha_ajuste, 
                    responsable, tipo_diferencia, documento_respaldo, observaciones
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_ajuste->bind_param("iiisiidsssssss", 
                $id_empresa, $id_producto, $id_usuario, $tipo_ajuste,
                $cantidad_ajustada, $stock_anterior, $stock_nuevo, 
                $valor_ajuste, $motivo_ajuste, $fecha_ajuste,
                $responsable, $tipo_diferencia, $documento_respaldo, $observaciones
            );
            $stmt_ajuste->execute();
            
            // Actualizar stock del producto
            $stmt_update_stock = $conn->prepare("
                UPDATE t_productos 
                SET stock = ?, fecha_actualizacion = NOW()
                WHERE ID_PRODUCTO = ?
            ");
            $stmt_update_stock->bind_param("ii", $stock_nuevo, $id_producto);
            $stmt_update_stock->execute();
            
            $conn->commit();
            
            // Redirigir con mensaje de éxito
            $mensaje = "Ajuste registrado exitosamente. Stock actualizado de {$stock_anterior} a {$stock_nuevo}";
            header("Location: ../../../public/inventario/ajustes.php?success=1&message=" . urlencode($mensaje));
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception('Error al registrar el ajuste: ' . $e->getMessage());
        }
        
    } catch (Exception $e) {
        // Redirigir con mensaje de error
        header("Location: ../../../public/inventario/nuevo-ajuste.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Si no es POST, redirigir a la página de ajustes
    header("Location: ../../../public/inventario/ajustes.php");
    exit();
}
?> 