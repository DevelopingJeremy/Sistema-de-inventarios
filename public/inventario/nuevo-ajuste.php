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
    $tipo_ajuste = $_GET['tipo'] ?? 'positivo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ajuste - HyBox</title>
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
                        <h1 class="page-title">Nuevo Ajuste de Inventario</h1>
                        <p class="page-subtitle">Ajusta el stock de productos por diferencias físicas</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn-secondary" onclick="window.location.href='ajustes.php'">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                    </div>
                </div>

                <!-- Mensajes de éxito/error -->
                <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($_GET['message'] ?? 'Ajuste registrado exitosamente'); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($_GET['message'] ?? 'Ha ocurrido un error'); ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form id="adjustmentForm" action="../../src/inventario/ajustes/crear-ajuste.php" method="POST">
                        
                        <!-- Información del Ajuste -->
                        <div class="form-section">
                            <h3 class="section-title">Información del Ajuste</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Tipo de Ajuste <span class="required">*</span></label>
                                    <div class="adjustment-type-selector">
                                        <label class="adjustment-type-option">
                                            <input type="radio" name="tipo_ajuste" value="positivo" <?php echo $tipo_ajuste === 'positivo' ? 'checked' : ''; ?>>
                                            <div class="adjustment-type-content">
                                                <i class="fas fa-plus"></i>
                                                <span>Ajuste Positivo</span>
                                                <small>Aumentar stock</small>
                                            </div>
                                        </label>
                                        <label class="adjustment-type-option">
                                            <input type="radio" name="tipo_ajuste" value="negativo" <?php echo $tipo_ajuste === 'negativo' ? 'checked' : ''; ?>>
                                            <div class="adjustment-type-content">
                                                <i class="fas fa-minus"></i>
                                                <span>Ajuste Negativo</span>
                                                <small>Reducir stock</small>
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
                                    <label class="form-label">Cantidad a Ajustar <span class="required">*</span></label>
                                    <input type="number" class="form-input" name="cantidad_ajustada" id="cantidadInput" min="1" required>
                                    <div class="help-text">Stock actual: <span id="stockActual">0</span></div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Valor del Ajuste</label>
                                    <input type="text" class="form-input" id="valorAjuste" readonly>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">Motivo del Ajuste <span class="required">*</span></label>
                                    <textarea class="form-input form-textarea" name="motivo_ajuste" rows="3" placeholder="Describe el motivo del ajuste (ej: Diferencia en conteo físico, Producto dañado, etc.)..." required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Fecha del Ajuste</label>
                                    <input type="datetime-local" class="form-input" name="fecha_ajuste" value="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Responsable</label>
                                    <input type="text" class="form-input" name="responsable" placeholder="Nombre de quien realiza el ajuste">
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="form-section">
                            <h3 class="section-title">Información Adicional</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Tipo de Diferencia</label>
                                    <select class="form-select" name="tipo_diferencia">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="conteo_fisico">Conteo físico</option>
                                        <option value="producto_danado">Producto dañado</option>
                                        <option value="merma">Merma</option>
                                        <option value="robo">Robo</option>
                                        <option value="caducidad">Caducidad</option>
                                        <option value="error_sistema">Error de sistema</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Documento de Respaldo</label>
                                    <input type="text" class="form-input" name="documento_respaldo" placeholder="Número de documento de respaldo">
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">Observaciones</label>
                                    <textarea class="form-input form-textarea" name="observaciones" rows="3" placeholder="Observaciones adicionales sobre el ajuste..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="window.location.href='ajustes.php'">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Registrar Ajuste
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
            initAdjustmentForm();
        });

        function initAdjustmentForm() {
            const productSelect = document.getElementById('productSelect');
            const cantidadInput = document.getElementById('cantidadInput');
            const valorAjusteInput = document.getElementById('valorAjuste');
            const stockActualSpan = document.getElementById('stockActual');
            const tipoAjusteInputs = document.querySelectorAll('input[name="tipo_ajuste"]');

            // Función para actualizar información del producto
            function updateProductInfo() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                if (selectedOption.value) {
                    const stock = parseInt(selectedOption.dataset.stock);
                    const precio = parseFloat(selectedOption.dataset.precio);
                    
                    stockActualSpan.textContent = stock;
                } else {
                    stockActualSpan.textContent = '0';
                }
                calculateAdjustmentValue();
            }

            // Función para calcular el valor del ajuste
            function calculateAdjustmentValue() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const cantidad = parseInt(cantidadInput.value) || 0;
                const tipoAjuste = document.querySelector('input[name="tipo_ajuste"]:checked').value;
                
                if (selectedOption.value && cantidad > 0) {
                    const precio = parseFloat(selectedOption.dataset.precio);
                    const valor = cantidad * precio;
                    const signo = tipoAjuste === 'positivo' ? '+' : '-';
                    valorAjusteInput.value = `${signo}₡${valor.toLocaleString()}`;
                } else {
                    valorAjusteInput.value = '';
                }
            }

            // Función para validar ajuste negativo
            function validateNegativeAdjustment() {
                const tipoAjuste = document.querySelector('input[name="tipo_ajuste"]:checked').value;
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                
                if (tipoAjuste === 'negativo' && selectedOption.value) {
                    const stock = parseInt(selectedOption.dataset.stock);
                    const cantidad = parseInt(cantidadInput.value) || 0;
                    
                    if (cantidad > stock) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock insuficiente',
                            text: `No puedes ajustar más unidades de las que tienes en stock. Disponible: ${stock} unidades`,
                            confirmButtonText: 'Entendido'
                        });
                        cantidadInput.value = stock;
                        calculateAdjustmentValue();
                    }
                }
            }

            // Event listeners
            productSelect.addEventListener('change', updateProductInfo);
            cantidadInput.addEventListener('input', calculateAdjustmentValue);
            cantidadInput.addEventListener('blur', validateNegativeAdjustment);
            
            tipoAjusteInputs.forEach(input => {
                input.addEventListener('change', function() {
                    calculateAdjustmentValue();
                    validateNegativeAdjustment();
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