<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    // Verificar que se proporcionó un ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: productos.php?error=1&message=ID de producto no proporcionado");
        exit();
    }
    
    $id_producto = intval($_GET['id']);
    $id_usuario = $_SESSION['id_usuario'];
    
    // Obtener datos del producto
    $stmt = $conn->prepare("
        SELECT p.*, e.nombre_empresa 
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
    
    // Determinar el estado del producto
    $estado_clase = '';
    $estado_texto = '';
    
    if ($producto['stock'] <= 0) {
        $estado_clase = 'status-inactive';
        $estado_texto = 'Agotado';
    } elseif ($producto['stock'] <= $producto['stock_minimo']) {
        $estado_clase = 'status-low-stock';
        $estado_texto = 'Stock Bajo';
    } else {
        $estado_clase = 'status-active';
        $estado_texto = 'Activo';
    }
    
    // Formatear precios
    $precio_formateado = '₡' . number_format($producto['precio'], 0, ',', '.');
    $precio_compra_formateado = $producto['precio_compra'] > 0 ? '₡' . number_format($producto['precio_compra'], 0, ',', '.') : 'No especificado';
    
    // Calcular ganancia por producto
    $ganancia_por_producto = 0;
    if ($producto['precio_compra'] > 0) {
        $ganancia_por_producto = $producto['precio'] - $producto['precio_compra'];
    }
    
    // Formatear ganancia por producto
    $ganancia_formateada = $ganancia_por_producto > 0 ? '₡' . number_format($ganancia_por_producto, 0, ',', '.') : '₡0';
    
    // Cálculos analíticos adicionales
    $valor_total_inventario = $producto['stock'] * $producto['precio'];
    $valor_total_inventario_formateado = '₡' . number_format($valor_total_inventario, 0, ',', '.');
    
    $ganancia_total_potencial = $producto['stock'] * $ganancia_por_producto;
    $ganancia_total_potencial_formateada = $ganancia_total_potencial > 0 ? '₡' . number_format($ganancia_total_potencial, 0, ',', '.') : '₡0';
    

    

    

    
    // Cálculos analíticos adicionales
    $valor_compra_total = $producto['stock'] * $producto['precio_compra'];
    $valor_compra_total_formateado = $producto['precio_compra'] > 0 ? '₡' . number_format($valor_compra_total, 0, ',', '.') : 'No calculable';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Producto - <?php echo htmlspecialchars($producto['nombre_producto']); ?> - HyBox</title>
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
                    <h1 class="page-title">Detalles del Producto</h1>
                    <p class="page-subtitle">Información completa del producto</p>
                </div>

                <div class="product-detail-container">
                    <!-- Header del Producto -->
                    <div class="product-header">
                        <?php if (!empty($producto['imagen'])): ?>
                            <img src="<?php echo "../../" . htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" class="product-image">
                        <?php else: ?>
                            <div class="product-image-placeholder">
                                <i class="fas fa-image" style="font-size: 48px; color: #ddd;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h1 class="product-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h1>
                            <div class="product-code">
                                <?php echo !empty($producto['codigo_interno']) ? 'Código: ' . htmlspecialchars($producto['codigo_interno']) : 'ID: ' . $producto['ID_PRODUCTO']; ?>
                                <?php if (!empty($producto['codigo_barras'])): ?>
                                    | Barras: <?php echo htmlspecialchars($producto['codigo_barras']); ?>
                                <?php endif; ?>
                            </div>
                            <span class="product-status <?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></span>
                        </div>
                        
                        <div class="product-actions">
                            <a href="editar-producto.php?id=<?php echo $producto['ID_PRODUCTO']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="productos.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>

                    <!-- Alertas de Stock -->
                    <?php if ($producto['stock'] <= 0): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Producto Agotado:</strong> No hay stock disponible.
                        </div>
                    <?php elseif ($producto['stock'] <= $producto['stock_minimo']): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Stock Bajo:</strong> Solo quedan <?php echo $producto['stock']; ?> unidades disponibles.
                        </div>
                    <?php endif; ?>

                    <!-- Métricas Rápidas -->
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $producto['stock']; ?></div>
                            <div class="metric-label">
                                Stock Actual
                                <i class="fas fa-question-circle tooltip-icon" data-tooltip="Cantidad total de unidades disponibles para la venta. Es el inventario físico que tienes en este momento."></i>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $precio_formateado; ?></div>
                            <div class="metric-label">
                                Precio de Venta
                                <i class="fas fa-question-circle tooltip-icon" data-tooltip="Precio al que vendes el producto a tus clientes. Es el precio final que pagan los consumidores."></i>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value <?php 
                                if ($ganancia_por_producto > 0) {
                                    echo 'ganancia-positiva';
                                } elseif ($ganancia_por_producto < 0) {
                                    echo 'ganancia-negativa';
                                } else {
                                    echo 'ganancia-cero';
                                }
                            ?>"><?php echo $ganancia_formateada; ?></div>
                            <div class="metric-label">
                                Ganancia por Unidad
                                <i class="fas fa-question-circle tooltip-icon" data-tooltip="Dinero que ganas por cada unidad vendida. Se calcula restando el precio de compra del precio de venta. Si es negativo, estás perdiendo dinero por unidad."></i>
                            </div>
                        </div>

                    </div>

                    <!-- Métricas Avanzadas -->
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $valor_total_inventario_formateado; ?></div>
                            <div class="metric-label">
                                Valor Total Inventario
                                <i class="fas fa-question-circle tooltip-icon" data-tooltip="Valor total de todo tu stock actual si lo vendieras al precio de venta. Representa el potencial de ingresos de tu inventario."></i>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value <?php 
                                if ($ganancia_total_potencial > 0) {
                                    echo 'ganancia-positiva';
                                } elseif ($ganancia_total_potencial < 0) {
                                    echo 'ganancia-negativa';
                                } else {
                                    echo 'ganancia-cero';
                                }
                            ?>"><?php echo $ganancia_total_potencial_formateada; ?></div>
                            <div class="metric-label">
                                Ganancia Total Potencial
                                <i class="fas fa-question-circle tooltip-icon" data-tooltip="Ganancia total que obtendrías si vendieras todo el stock actual. Se calcula multiplicando la ganancia por unidad por el número total de unidades."></i>
                            </div>
                        </div>

                    </div>

                    <!-- Métricas Especializadas -->
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-value"><?php echo $valor_compra_total_formateado; ?></div>
                            <div class="metric-label">
                                Valor Compra Total
                                <i class="fas fa-question-circle tooltip-icon" data-tooltip="Dinero total que gastaste para comprar todo el stock actual. Es el capital que tienes comprometido en este producto."></i>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value <?php 
                                if ($producto['stock'] >= $producto['stock_minimo'] * 2) {
                                    echo 'sugerencia-mantener';
                                } elseif ($producto['stock'] >= $producto['stock_minimo']) {
                                    echo 'sugerencia-observar';
                                } elseif ($producto['stock'] >= $producto['stock_minimo'] * 0.5) {
                                    echo 'sugerencia-reponer';
                                } else {
                                    echo 'sugerencia-urgente';
                                }
                            ?>"><?php 
                                if ($producto['stock'] >= $producto['stock_minimo'] * 2) {
                                    echo 'Mantener';
                                } elseif ($producto['stock'] >= $producto['stock_minimo']) {
                                    echo 'Observar';
                                } elseif ($producto['stock'] >= $producto['stock_minimo'] * 0.5) {
                                    echo 'Reponer';
                                } else {
                                    echo 'Urgente';
                                }
                            ?></div>
                            <div class="metric-label">
                                Sugerencia Acción
                                <i class="fas fa-question-circle tooltip-icon" data-tooltip="Recomendación de qué hacer con el stock: Mantener (excelente), Observar (bueno), Reponer (regular), o Urgente (crítico). Basado en la relación entre stock actual y mínimo."></i>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Producto -->
                    <div class="detail-grid">
                        <!-- Información Básica -->
                        <div class="detail-section">
                            <h3 class="section-title">Información Básica</h3>
                            <div class="detail-row">
                                <span class="detail-label">Nombre</span>
                                <span class="detail-value"><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Categoría</span>
                                <span class="detail-value"><?php echo htmlspecialchars($producto['categoria'] ?: 'Sin categoría'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Empresa</span>
                                <span class="detail-value"><?php echo htmlspecialchars($producto['nombre_empresa']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Estado</span>
                                <span class="detail-value">
                                    <span class="product-status <?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></span>
                                </span>
                            </div>
                        </div>

                        <!-- Información de Precios -->
                        <div class="detail-section">
                            <h3 class="section-title">Información de Precios</h3>
                            <div class="detail-row">
                                <span class="detail-label">
                                    Precio de Venta
                                    <i class="fas fa-question-circle tooltip-icon" data-tooltip="Precio al que vendes el producto a tus clientes. Es el precio final que pagan los consumidores por cada unidad."></i>
                                </span>
                                <span class="detail-value"><?php echo $precio_formateado; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">
                                    Precio de Compra
                                    <i class="fas fa-question-circle tooltip-icon" data-tooltip="Precio que pagaste al proveedor por cada unidad. Es el costo de adquisición del producto."></i>
                                </span>
                                <span class="detail-value"><?php echo $precio_compra_formateado; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">
                                    Ganancia por Unidad
                                    <i class="fas fa-question-circle tooltip-icon" data-tooltip="Dinero que ganas por cada unidad vendida. Se calcula restando el precio de compra del precio de venta. Si es negativo, estás perdiendo dinero por unidad."></i>
                                </span>
                                <span class="detail-value <?php 
                                    if ($ganancia_por_producto > 0) {
                                        echo 'ganancia-positiva';
                                    } elseif ($ganancia_por_producto < 0) {
                                        echo 'ganancia-negativa';
                                    } else {
                                        echo 'ganancia-cero';
                                    }
                                ?>"><?php echo $ganancia_formateada; ?></span>
                            </div>

                        </div>

                        <!-- Información de Inventario -->
                        <div class="detail-section">
                            <h3 class="section-title">Información de Inventario</h3>
                            <div class="detail-row">
                                <span class="detail-label">Stock Actual</span>
                                <span class="detail-value"><?php echo $producto['stock']; ?> unidades</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Stock Mínimo</span>
                                <span class="detail-value"><?php echo $producto['stock_minimo']; ?> unidades</span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Ubicación</span>
                                <span class="detail-value"><?php echo htmlspecialchars($producto['ubicacion'] ?: 'No especificada'); ?></span>
                            </div>
                        </div>

                        <!-- Información Analítica -->
                        <div class="detail-section">
                            <h3 class="section-title">Información Analítica</h3>
                            <div class="detail-row">
                                <span class="detail-label">
                                    Valor Total Inventario
                                    <i class="fas fa-question-circle tooltip-icon" data-tooltip="Valor total de todo tu stock actual si lo vendieras al precio de venta. Representa el potencial de ingresos de tu inventario."></i>
                                </span>
                                <span class="detail-value"><?php echo $valor_total_inventario_formateado; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">
                                    Ganancia Total Potencial
                                    <i class="fas fa-question-circle tooltip-icon" data-tooltip="Ganancia total que obtendrías si vendieras todo el stock actual. Se calcula multiplicando la ganancia por unidad por el número total de unidades."></i>
                                </span>
                                <span class="detail-value <?php 
                                    if ($ganancia_total_potencial > 0) {
                                        echo 'ganancia-positiva';
                                    } elseif ($ganancia_total_potencial < 0) {
                                        echo 'ganancia-negativa';
                                    } else {
                                        echo 'ganancia-cero';
                                    }
                                ?>"><?php echo $ganancia_total_potencial_formateada; ?></span>
                            </div>



                            <div class="detail-row">
                                <span class="detail-label">
                                    Valor Compra Total
                                    <i class="fas fa-question-circle tooltip-icon" data-tooltip="Dinero total que gastaste para comprar todo el stock actual. Es el capital que tienes comprometido en este producto."></i>
                                </span>
                                <span class="detail-value"><?php echo $valor_compra_total_formateado; ?></span>
                            </div>

                        </div>

                        <!-- Información del Proveedor -->
                        <div class="detail-section">
                            <h3 class="section-title">Información del Proveedor</h3>
                            <div class="detail-row">
                                <span class="detail-label">Proveedor</span>
                                <span class="detail-value"><?php echo htmlspecialchars($producto['proveedor'] ?: 'No especificado'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Código de Barras</span>
                                <span class="detail-value"><?php echo htmlspecialchars($producto['codigo_barras'] ?: 'No especificado'); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Código Interno</span>
                                <span class="detail-value"><?php echo htmlspecialchars($producto['codigo_interno'] ?: 'No especificado'); ?></span>
                            </div>
                        </div>

                        <!-- Información del Sistema -->
                        <div class="detail-section">
                            <h3 class="section-title">Información del Sistema</h3>
                            <div class="detail-row">
                                <span class="detail-label">ID del Producto</span>
                                <span class="detail-value">#<?php echo $producto['ID_PRODUCTO']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Fecha de Creación</span>
                                <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($producto['fecha_creacion'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Última Actualización</span>
                                <span class="detail-value">
                                    <?php echo $producto['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($producto['fecha_actualizacion'])) : 'No actualizado'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <?php if (!empty($producto['descripcion'])): ?>
                        <div class="detail-section detail-description">
                            <h3 class="section-title">Descripción</h3>
                            <div class="description-content">
                                <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades específicas de ver producto
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initTooltips();
            
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