<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    $id_usuario = $_SESSION['id_usuario'];
    
    // Obtener el ID de la empresa del usuario
    $stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_empresa WHERE ID_DUEÑO = ?");
    $stmt_empresa->bind_param("i", $id_usuario);
    $stmt_empresa->execute();
    $empresa_result = $stmt_empresa->get_result()->fetch_assoc();
    $id_empresa = $empresa_result['ID_EMPRESA'];
    
    // Obtener productos de la empresa
    $stmt_productos = $conn->prepare("
        SELECT ID_PRODUCTO, nombre_producto, categoria, stock, precio 
        FROM t_productos 
        WHERE ID_EMPRESA = ? 
        ORDER BY nombre_producto ASC
    ");
    $stmt_productos->bind_param("i", $id_empresa);
    $stmt_productos->execute();
    $productos = $stmt_productos->get_result();
    
    // Obtener datos predefinidos si se pasan por URL
    $producto_seleccionado = $_GET['producto'] ?? '';
    $tipo_movimiento = $_GET['tipo'] ?? 'entrada';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Movimiento - HyBox</title>
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
                    <div class="header-content">
                        <h1 class="page-title">Nuevo Movimiento de Inventario</h1>
                        <p class="page-subtitle">Registra entradas y salidas de productos</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn-secondary" onclick="window.location.href='movimientos.php'">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                    </div>
                </div>

                <!-- Mensajes de éxito/error -->
                <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($_GET['message'] ?? 'Movimiento registrado exitosamente'); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($_GET['message'] ?? 'Ha ocurrido un error'); ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form id="movementForm" action="../../src/inventario/movimientos/crear-movimiento.php" method="POST">
                        
                        <!-- Información del Movimiento -->
                        <div class="form-section">
                            <h3 class="section-title">Información del Movimiento</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Tipo de Movimiento <span class="required">*</span></label>
                                    <div class="movement-type-selector">
                                        <label class="movement-type-option">
                                            <input type="radio" name="tipo_movimiento" value="entrada" <?php echo $tipo_movimiento === 'entrada' ? 'checked' : ''; ?>>
                                            <div class="movement-type-content">
                                                <i class="fas fa-arrow-down"></i>
                                                <span>Entrada</span>
                                            </div>
                                        </label>
                                        <label class="movement-type-option">
                                            <input type="radio" name="tipo_movimiento" value="salida" <?php echo $tipo_movimiento === 'salida' ? 'checked' : ''; ?>>
                                            <div class="movement-type-content">
                                                <i class="fas fa-arrow-up"></i>
                                                <span>Salida</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Producto <span class="required">*</span></label>
                                    <select class="form-select" name="id_producto" id="productSelect" required>
                                        <option value="">Seleccionar producto</option>
                                        <?php while ($producto = $productos->fetch_assoc()): ?>
                                            <option value="<?php echo $producto['ID_PRODUCTO']; ?>" 
                                                    data-stock="<?php echo $producto['stock']; ?>"
                                                    data-precio="<?php echo $producto['precio']; ?>"
                                                    data-categoria="<?php echo htmlspecialchars($producto['categoria']); ?>"
                                                    <?php echo $producto_seleccionado == $producto['ID_PRODUCTO'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($producto['nombre_producto']); ?> 
                                                (Stock: <?php echo $producto['stock']; ?>) - 
                                                <?php echo htmlspecialchars($producto['categoria'] ?: 'Sin categoría'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Cantidad <span class="required">*</span></label>
                                    <input type="number" class="form-input" name="cantidad" id="cantidadInput" min="1" required>
                                    <div class="help-text">Stock actual: <span id="stockActual">0</span></div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Precio Unitario <span class="required">*</span></label>
                                    <input type="number" class="form-input" name="precio_unitario" id="precioInput" step="0.01" min="0" required>
                                    <div class="help-text">Precio sugerido: ₡<span id="precioSugerido">0</span></div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Valor Total</label>
                                    <input type="text" class="form-input" id="valorTotal" readonly>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">Motivo <span class="required">*</span></label>
                                    <textarea class="form-input form-textarea" name="motivo" rows="3" placeholder="Describe el motivo del movimiento..." required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Fecha del Movimiento</label>
                                    <input type="datetime-local" class="form-input" name="fecha_movimiento" value="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Referencia</label>
                                    <input type="text" class="form-input" name="referencia" placeholder="Número de factura, remisión, etc.">
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="form-section">
                            <h3 class="section-title">Información Adicional</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Proveedor/Cliente</label>
                                    <input type="text" class="form-input" name="proveedor_cliente" placeholder="Nombre del proveedor o cliente">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Documento</label>
                                    <input type="text" class="form-input" name="documento" placeholder="Número de documento">
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">Observaciones</label>
                                    <textarea class="form-input form-textarea" name="observaciones" rows="3" placeholder="Observaciones adicionales..."></textarea>
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>



                        <!-- Botones de Acción -->
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="window.location.href='movimientos.php'">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Registrar Movimiento
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
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMovementForm();
        });

        function initMovementForm() {
            const productSelect = document.getElementById('productSelect');
            const cantidadInput = document.getElementById('cantidadInput');
            const precioInput = document.getElementById('precioInput');
            const valorTotalInput = document.getElementById('valorTotal');
            const stockActualSpan = document.getElementById('stockActual');
            const precioSugeridoSpan = document.getElementById('precioSugerido');
            const tipoMovimientoInputs = document.querySelectorAll('input[name="tipo_movimiento"]');

            // Función para actualizar información del producto
            function updateProductInfo() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                if (selectedOption.value) {
                    const stock = parseInt(selectedOption.dataset.stock);
                    const precio = parseFloat(selectedOption.dataset.precio);
                    
                    stockActualSpan.textContent = stock;
                    precioSugeridoSpan.textContent = precio.toLocaleString();
                    precioInput.value = precio;
                    
                    // Validar cantidad para salidas
                    const tipoMovimiento = document.querySelector('input[name="tipo_movimiento"]:checked').value;
                    if (tipoMovimiento === 'salida') {
                        cantidadInput.max = stock;
                        if (parseInt(cantidadInput.value) > stock) {
                            cantidadInput.value = stock;
                        }
                    }
                } else {
                    stockActualSpan.textContent = '0';
                    precioSugeridoSpan.textContent = '0';
                    precioInput.value = '';
                }
                calculateTotal();
            }

            // Función para calcular el total
            function calculateTotal() {
                const cantidad = parseInt(cantidadInput.value) || 0;
                const precio = parseFloat(precioInput.value) || 0;
                const total = cantidad * precio;
                valorTotalInput.value = '₡' + total.toLocaleString();
            }

            // Función para validar salidas
            function validateOutput() {
                const tipoMovimiento = document.querySelector('input[name="tipo_movimiento"]:checked').value;
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                
                if (tipoMovimiento === 'salida' && selectedOption.value) {
                    const stock = parseInt(selectedOption.dataset.stock);
                    const cantidad = parseInt(cantidadInput.value) || 0;
                    
                    if (cantidad > stock) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock insuficiente',
                            text: `No hay suficiente stock. Disponible: ${stock} unidades`,
                            confirmButtonText: 'Entendido'
                        });
                        cantidadInput.value = stock;
                        calculateTotal();
                    }
                }
            }

            // Event listeners
            productSelect.addEventListener('change', updateProductInfo);
            cantidadInput.addEventListener('input', calculateTotal);
            precioInput.addEventListener('input', calculateTotal);
            cantidadInput.addEventListener('blur', validateOutput);
            
            tipoMovimientoInputs.forEach(input => {
                input.addEventListener('change', function() {
                    updateProductInfo();
                    validateOutput();
                });
            });

            // Inicializar con datos si hay producto seleccionado
            if (productSelect.value) {
                updateProductInfo();
            }
        }
    </script>
</body>
</html> 