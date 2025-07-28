<?php 
require_once('../../auth/sesion/verificaciones-sesion.php');
validTotales('../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php', '../../../public/sesion/iniciar-sesion.php');
include('../../../config/db.php');

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
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
        
        // Obtener ID de la empresa del usuario actual
        $id_usuario = $_SESSION['id_usuario'];
        $stmt = $conn->prepare("SELECT ID_EMPRESA FROM t_empresa WHERE ID_DUEÑO = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('No se encontró la empresa asociada al usuario');
        }
        
        $empresa = $result->fetch_assoc();
        $id_empresa = $empresa['ID_EMPRESA'];
        
        // Validar que los códigos no estén duplicados en la empresa
        if (!empty($codigo_barras)) {
            $stmt = $conn->prepare("SELECT ID_PRODUCTO FROM t_productos WHERE codigo_barras = ? AND ID_EMPRESA = ?");
            $stmt->bind_param("si", $codigo_barras, $id_empresa);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception('El código de barras ya existe en su inventario');
            }
        }
        
        if (!empty($codigo_interno)) {
            $stmt = $conn->prepare("SELECT ID_PRODUCTO FROM t_productos WHERE codigo_interno = ? AND ID_EMPRESA = ?");
            $stmt->bind_param("si", $codigo_interno, $id_empresa);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception('El código interno ya existe en su inventario');
            }
        }
        
        // Procesar imagen si se subió un archivo
        $imagen_final = $imagen_url;
        if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
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
            } else {
                throw new Exception('Error al subir la imagen');
            }
        }
        
        // Preparar consulta SQL
        $sql = "INSERT INTO t_productos (
            ID_EMPRESA, 
            nombre_producto, 
            descripcion, 
            categoria, 
            precio, 
            precio_compra, 
            stock, 
            stock_minimo, 
            codigo_barras, 
            codigo_interno, 
            proveedor, 
            ubicacion, 
            imagen, 
            estado, 
            creado_por
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssddiissssssi", 
            $id_empresa,
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
            $id_usuario
        );
        
        if ($stmt->execute()) {
            $id_producto = $conn->insert_id;
            
            // Redirigir con mensaje de éxito
            header("Location: ../../../public/inventario/productos.php?success=1&message=Producto creado exitosamente");
            exit();
        } else {
            throw new Exception('Error al insertar el producto en la base de datos');
        }
        
    } catch (Exception $e) {
        // Redirigir con mensaje de error
        header("Location: ../../../public/inventario/agregar-producto.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Si no es POST, redirigir a la página de productos
    header("Location: ../../../public/inventario/productos.php");
    exit();
}
?>