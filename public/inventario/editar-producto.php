<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    require_once('../../src/inventario/funciones.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    // Verificar que se proporcionó un ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: productos.php?error=1&message=ID de producto no proporcionado");
        exit();
    }
    
    $id_producto = intval($_GET['id']);
    $id_usuario = $_SESSION['id_usuario'];
    
    // Obtener categorías y proveedores de la empresa
    $categorias = obtenerCategoriasEmpresa($conn, $id_usuario);
    $proveedores = obtenerProveedoresEmpresa($conn, $id_usuario);
    
    // Obtener datos del producto
    $stmt = $conn->prepare("
        SELECT p.* 
        FROM t_productos p 
        INNER JOIN t_empresa e ON p.ID_EMPRESA = e.ID_EMPRESA 
        WHERE p.ID_PRODUCTO = ? AND e.ID_DUEÑO = ?
    ");
    $stmt->bind_param("ii", $id_producto, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: productos.php?error=1&message=Producto no encontrado");
        exit();
    }
    
    $producto = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - HyBox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Editar Producto</h1>
                    <p class="page-subtitle">Modifica la información del producto</p>
                </div>

                <!-- Mensajes de error -->
                <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($_GET['message'] ?? 'Ha ocurrido un error'); ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form id="productForm" action="../../src/inventario/productos/actualizar-producto.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_producto" value="<?php echo $producto['ID_PRODUCTO']; ?>">
                        
                        <!-- Información Básica -->
                        <div class="form-section">
                            <h3 class="section-title">Información Básica</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Nombre del Producto <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="nombre_producto" required maxlength="100" value="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                                    <div class="help-text">Máximo 100 caracteres</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="categoria" required>
                                        <option value="">Seleccionar categoría</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>" 
                                                    <?php echo $producto['categoria'] == $categoria['nombre_categoria'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-input form-textarea" name="descripcion" rows="4" placeholder="Describe las características del producto..."><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Precios -->
                        <div class="form-section">
                            <h3 class="section-title">Información de Precios</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Precio de Venta <span class="required">*</span></label>
                                    <input type="number" class="form-input" name="precio" step="0.01" min="0" required value="<?php echo $producto['precio']; ?>">
                                    <div class="help-text">Precio al que se venderá el producto</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Precio de Compra</label>
                                    <input type="number" class="form-input" name="precio_compra" step="0.01" min="0" value="<?php echo $producto['precio_compra']; ?>">
                                    <div class="help-text">Precio al que se compró el producto</div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Inventario -->
                        <div class="form-section">
                            <h3 class="section-title">Información de Inventario</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Stock <span class="required">*</span></label>
                                    <input type="number" class="form-input" name="stock" min="0" required value="<?php echo $producto['stock']; ?>">
                                    <div class="help-text">Cantidad disponible en inventario</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Stock Mínimo</label>
                                    <input type="number" class="form-input" name="stock_minimo" min="0" value="<?php echo $producto['stock_minimo']; ?>">
                                    <div class="help-text">Cantidad mínima antes de alertar</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Estado <span class="required">*</span></label>
                                    <select class="form-select" name="estado" required>
                                        <option value="activo" <?php echo $producto['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="inactivo" <?php echo $producto['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                        <option value="agotado" <?php echo $producto['estado'] == 'agotado' ? 'selected' : ''; ?>>Agotado</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Ubicación</label>
                                    <input type="text" class="form-input" name="ubicacion" maxlength="100" value="<?php echo htmlspecialchars($producto['ubicacion']); ?>">
                                    <div class="help-text">Ubicación física del producto</div>
                                </div>
                            </div>
                        </div>

                        <!-- Códigos y Identificación -->
                        <div class="form-section">
                            <h3 class="section-title">Códigos e Identificación</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Código de Barras</label>
                                    <input type="text" class="form-input" name="codigo_barras" maxlength="50" value="<?php echo htmlspecialchars($producto['codigo_barras']); ?>">
                                    <div class="help-text">Código de barras del producto</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Código Interno</label>
                                    <input type="text" class="form-input" name="codigo_interno" maxlength="50" value="<?php echo htmlspecialchars($producto['codigo_interno']); ?>">
                                    <div class="help-text">Código interno de la empresa</div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Proveedor -->
                        <div class="form-section">
                            <h3 class="section-title">Información del Proveedor</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Proveedor</label>
                                    <select class="form-select" name="proveedor">
                                        <option value="">Seleccionar proveedor</option>
                                        <?php foreach ($proveedores as $proveedor): ?>
                                            <option value="<?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?>" 
                                                    <?php echo $producto['proveedor'] == $proveedor['nombre_proveedor'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?>
                                                <?php if (!empty($proveedor['contacto'])): ?>
                                                    (<?php echo htmlspecialchars($proveedor['contacto']); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="help-text">Selecciona un proveedor de tu lista</div>
                                </div>
                            </div>
                        </div>

                        <!-- Imagen del Producto -->
                        <div class="form-section">
                            <h3 class="section-title">Imagen del Producto</h3>
                            <div class="form-group">
                                <label class="form-label">Subir Nueva Imagen</label>
                                <input type="file" class="form-input" name="imagen_archivo" accept="image/*">
                                <div class="help-text">Formatos: JPG, PNG, GIF (máx. 5MB)</div>
                            </div>
                            
                            <div class="image-preview" id="imagePreview">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?php echo "../../" . htmlspecialchars($producto['imagen']); ?>" alt="Imagen actual">
                                <?php else: ?>
                                    <i class="fas fa-image" style="font-size: 48px; color: #ddd;"></i>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="btn-container">
                            <button type="button" class="btn-secondary" onclick="window.location.href='productos.php'">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Actualizar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades específicas de editar producto
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initAddProductPage();
            checkUrlMessages();
            
            // Forzar actualización de dark mode después de un breve delay
            setTimeout(() => {
                const isDarkMode = document.body.classList.contains('dark-mode');
                if (isDarkMode) {
                    forceDarkModeUpdate(true);
                }
            }, 100);
        });
    </script>
</body>
</html> 