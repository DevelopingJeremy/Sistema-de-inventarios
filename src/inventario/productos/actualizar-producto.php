<?php 
require_once('../../auth/sesion/verificaciones-sesion.php');
validTotales('../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php');
include('../../../config/db.php');

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $id_producto = intval($_POST['id_producto']);
        $nombre_producto = trim($_POST['nombre_producto']);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $precio = floatval($_POST['precio']);
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $stock = intval($_POST['stock']);
        $stock_minimo = intval($_POST['stock_minimo'] ?? 0);
        $codigo_barras = trim($_POST['codigo_barras'] ?? '');
        $codigo_interno = trim($_POST['codigo_interno'] ?? '');
        $proveedor = trim($_POST['proveedor'] ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $estado = $_POST['estado'];
        $imagen_url = '';
        
        // Validaciones básicas
        if (empty($nombre_producto)) {
            throw new Exception('El nombre del producto es obligatorio');
        }
        
        if ($precio < 0) {
            throw new Exception('El precio no puede ser negativo');
        }
        
        if ($stock < 0) {
            throw new Exception('El stock no puede ser negativo');
        }
        
        // Validar que al menos uno de los códigos esté presente
        if (empty($codigo_barras) && empty($codigo_interno)) {
            throw new Exception('Debe especificar al menos un código de barras o código interno');
        }
        
        // Verificar que el producto pertenece a la empresa del usuario
        $id_usuario = $_SESSION['id_usuario'];
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
            throw new Exception('Producto no encontrado o no tienes permisos para editarlo');
        }
        
        $producto_actual = $result->fetch_assoc();
        
        // Obtener ID de la empresa
        $id_empresa = $producto_actual['ID_EMPRESA'];
        
        // Validar que los códigos no estén duplicados en la empresa (excluyendo el producto actual)
        if (!empty($codigo_barras)) {
            $stmt = $conn->prepare("SELECT ID_PRODUCTO FROM t_productos WHERE codigo_barras = ? AND ID_EMPRESA = ? AND ID_PRODUCTO != ?");
            $stmt->bind_param("sii", $codigo_barras, $id_empresa, $id_producto);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception('El código de barras ya existe en su inventario');
            }
        }
        
        if (!empty($codigo_interno)) {
            $stmt = $conn->prepare("SELECT ID_PRODUCTO FROM t_productos WHERE codigo_interno = ? AND ID_EMPRESA = ? AND ID_PRODUCTO != ?");
            $stmt->bind_param("sii", $codigo_interno, $id_empresa, $id_producto);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception('El código interno ya existe en su inventario');
            }
        }
        
        // Procesar imagen solo si se subió un archivo nuevo
        $imagen_final = $producto_actual['imagen']; // Mantener la imagen actual por defecto
        
        if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK && $_FILES['imagen_archivo']['size'] > 0) {
            $archivo = $_FILES['imagen_archivo'];
            $nombre_archivo = $archivo['name'];
            $tipo_archivo = $archivo['type'];
            $tamano_archivo = $archivo['size'];
            $archivo_temporal = $archivo['tmp_name'];
            
            // Validar tipo de archivo
            $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($tipo_archivo, $tipos_permitidos)) {
                throw new Exception('Solo se permiten archivos de imagen (JPG, PNG, GIF)');
            }
            
            // Validar tamaño (5MB máximo)
            if ($tamano_archivo > 5 * 1024 * 1024) {
                throw new Exception('El archivo es demasiado grande. Máximo 5MB');
            }
            
            // Crear directorio si no existe
            $directorio_destino = '../../../uploads/img/fotos-productos/';
            if (!is_dir($directorio_destino)) {
                mkdir($directorio_destino, 0755, true);
            }
            
            // Generar nombre único para el archivo
            $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
            $nombre_unico = uniqid() . '_' . time() . '.' . $extension;
            $ruta_completa = $directorio_destino . $nombre_unico;
            
            // Mover archivo
            if (move_uploaded_file($archivo_temporal, $ruta_completa)) {
                $imagen_final = 'uploads/img/fotos-productos/' . $nombre_unico;
                
                // Eliminar imagen anterior si existe y no es una URL externa
                if (!empty($producto_actual['imagen']) && !filter_var($producto_actual['imagen'], FILTER_VALIDATE_URL)) {
                    $ruta_imagen_anterior = '../../../' . $producto_actual['imagen'];
                    if (file_exists($ruta_imagen_anterior)) {
                        unlink($ruta_imagen_anterior);
                    }
                }
            } else {
                throw new Exception('Error al subir la imagen');
            }
        }
        
        // Preparar consulta SQL de actualización
        $sql = "UPDATE t_productos SET 
            nombre_producto = ?, 
            descripcion = ?, 
            categoria = ?, 
            precio = ?, 
            precio_compra = ?, 
            stock = ?, 
            stock_minimo = ?, 
            codigo_barras = ?, 
            codigo_interno = ?, 
            proveedor = ?, 
            ubicacion = ?, 
            imagen = ?, 
            estado = ?, 
            actualizado_por = ?,
            fecha_actualizacion = CURRENT_TIMESTAMP
            WHERE ID_PRODUCTO = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssddiissssssii", 
            $nombre_producto,
            $descripcion,
            $categoria,
            $precio,
            $precio_compra,
            $stock,
            $stock_minimo,
            $codigo_barras,
            $codigo_interno,
            $proveedor,
            $ubicacion,
            $imagen_final,
            $estado,
            $id_usuario,
            $id_producto
        );
        
        if ($stmt->execute()) {
            // Redirigir con mensaje de éxito
            header("Location: ../../../public/inventario/productos.php?success=1&message=Producto actualizado exitosamente");
            exit();
        } else {
            throw new Exception('Error al actualizar el producto en la base de datos');
        }
        
    } catch (Exception $e) {
        // Redirigir con mensaje de error
        header("Location: ../../../public/inventario/editar-producto.php?id=" . $id_producto . "&error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Si no es POST, redirigir a la página de productos
    header("Location: ../../../public/inventario/productos.php");
    exit();
}
?> 