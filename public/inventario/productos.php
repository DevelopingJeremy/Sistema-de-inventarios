<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    // Obtener datos para los filtros
    $id_usuario = $_SESSION['id_usuario'];
    
    // Obtener categorías de la empresa
    $stmt_categorias = $conn->prepare("
        SELECT DISTINCT p.categoria 
        FROM t_productos p 
        INNER JOIN t_empresa e ON p.ID_EMPRESA = e.ID_EMPRESA 
        WHERE e.ID_DUEÑO = ? AND p.categoria IS NOT NULL AND p.categoria != ''
        ORDER BY p.categoria
    ");
    $stmt_categorias->bind_param("i", $id_usuario);
    $stmt_categorias->execute();
    $categorias = $stmt_categorias->get_result();
    

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestión de Productos</title>
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
                    <h1 class="page-title">Gestión de Productos</h1>
                    <p class="page-subtitle">Administra tu catálogo de productos y servicios</p>
                </div>

                <!-- Metrics Cards -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-mouse"></i>
                            </div>
                        </div>
                        <div class="metric-value">1,250</div>
                        <div class="metric-details">Total Productos</div>
                        <div class="metric-indicator">+45 este mes</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #e83e8c;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="metric-value">1,180</div>
                        <div class="metric-details">Productos Activos</div>
                        <div class="metric-indicator">94% del total</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #007bff;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="metric-value">15</div>
                        <div class="metric-details">Stock Bajo</div>
                        <div class="metric-indicator">Requieren atención</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #28a745;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="metric-value">₡15.2M</div>
                        <div class="metric-details">Valor Total</div>
                        <div class="metric-indicator">+8.5% vs mes anterior</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #007bff;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="metric-value">₡2.8M</div>
                        <div class="metric-details">Ventas del Mes</div>
                        <div class="metric-indicator">↑+12.5% +₡320K vs mes anterior</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="metric-value">156</div>
                        <div class="metric-details">Productos Vendidos</div>
                        <div class="metric-indicator">↑+8.2% +12 vs mes anterior</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div class="metric-value">4.8</div>
                        <div class="metric-details">Calificación Promedio</div>
                        <div class="metric-indicator">↑+5.1% +0.2 vs mes anterior</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                        <div class="metric-value">2,450</div>
                        <div class="metric-details">Visualizaciones</div>
                        <div class="metric-indicator">↑+15.3% +325 vs mes anterior</div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Ventas por Categoría</h3>
                            <div class="time-filters">
                                <button class="time-filter active">Semana</button>
                                <button class="time-filter">Mes</button>
                                <button class="time-filter">Año</button>
                            </div>
                        </div>
                        
                        <div class="chart-metrics">
                            <div class="chart-metric">
                                <div class="chart-metric-value">₡2.8M</div>
                                <div class="chart-metric-label">Total</div>
                            </div>
                            <div class="chart-metric">
                                <div class="chart-metric-value">₡280K</div>
                                <div class="chart-metric-label">Promedio</div>
                            </div>
                            <div class="chart-metric growth">
                                <div class="chart-metric-value">+12.5%</div>
                                <div class="chart-metric-label">Crecimiento</div>
                            </div>
                        </div>
                        
                        <div style="height: 200px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            Gráfico de Ventas por Categoría
                        </div>
                    </div>

                    <div class="top-products">
                        <div class="top-products-header">
                            <h3 class="chart-title">Top Productos</h3>
                            <button class="btn-icon">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-rank">1</div>
                            <div class="product-icon" style="background: #6f42c1;">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Laptop HP Pavilion</div>
                                <div class="product-category">Electrónicos</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">25</div>
                                <div class="product-revenue">₡6.25M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +15%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-rank">2</div>
                            <div class="product-icon" style="background: #28a745;">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">iPhone 13 Pro</div>
                                <div class="product-category">Electrónicos</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">18</div>
                                <div class="product-revenue">₡4.5M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +12%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-rank">3</div>
                            <div class="product-icon" style="background: #007bff;">
                                <i class="fas fa-headphones"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">AirPods Pro</div>
                                <div class="product-category">Accesorios</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">32</div>
                                <div class="product-revenue">₡2.88M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +8%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table Section -->
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="section-title">Lista de Productos</h2>
                        <button class="btn-primary" onclick="window.location.href='agregar-producto.php'">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>

                    <!-- Mensajes de éxito/error -->
                    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($_GET['message'] ?? 'Operación completada exitosamente'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($_GET['message'] ?? 'Ha ocurrido un error'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Search and Filters -->
                    <div class="filters-section">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="search-input" placeholder="Buscar por nombre, código de barras, código interno, categoría, proveedor..." id="searchInput">
                        </div>
                        
                        <div class="filter-group">
                            <select class="filter-select" id="categoryFilter">
                                <option value="">Todas las categorías</option>
                                <?php 
                                if ($categorias->num_rows > 0) {
                                    while ($categoria = $categorias->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($categoria['categoria']); ?>">
                                            <?php echo htmlspecialchars($categoria['categoria']); ?>
                                        </option>
                                    <?php endwhile;
                                } else { ?>
                                    <option value="" disabled>No hay categorías</option>
                                <?php } ?>
                            </select>
                            
                            <select class="filter-select" id="statusFilter">
                                <option value="">Todos los estados</option>
                                <option value="Activo">Activo</option>
                                <option value="Agotado">Agotado</option>
                                <option value="Stock Bajo">Stock Bajo</option>
                            </select>
                            

                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="table-container">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th>Códigos</th>
                                    <th>Proveedor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <?php
                                // Obtener productos de la empresa actual
                                $id_usuario = $_SESSION['id_usuario'];
                                $stmt = $conn->prepare("
                                    SELECT p.*, e.ID_EMPRESA 
                                    FROM t_productos p 
                                    INNER JOIN t_empresa e ON p.ID_EMPRESA = e.ID_EMPRESA 
                                    WHERE e.ID_DUEÑO = ? 
                                    ORDER BY p.fecha_creacion DESC
                                ");
                                $stmt->bind_param("i", $id_usuario);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    while ($producto = $result->fetch_assoc()) {
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
                                        
                                        // Imagen del producto
                                        $imagen_url = !empty($producto['imagen']) ? "../../" . $producto['imagen'] : 'https://via.placeholder.com/40x40/6c757d/ffffff?text=P';
                                        
                                        // Formatear precio
                                        $precio_formateado = '₡' . number_format($producto['precio'], 0, ',', '.');
                                ?>
                                <tr data-product-id="<?php echo $producto['ID_PRODUCTO']; ?>">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="<?php echo htmlspecialchars($imagen_url); ?>" class="product-image" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                            <div>
                                                <div style="font-weight: 600;"><?php echo htmlspecialchars($producto['nombre_producto']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($producto['categoria'] ?: 'Sin categoría'); ?></td>
                                    <td><?php echo $precio_formateado; ?></td>
                                    <td><?php echo $producto['stock']; ?></td>
                                    <td><span class="status-badge <?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></span></td>
                                    <td>
                                        <div style="font-size: 0.85rem;">
                                            <?php if (!empty($producto['codigo_interno'])): ?>
                                                <div style="margin-bottom: 4px;">
                                                    <strong>Interno:</strong> <?php echo htmlspecialchars($producto['codigo_interno']); ?>
                                        </div>
                                            <?php endif; ?>
                                            <?php if (!empty($producto['codigo_barras'])): ?>
                                            <div>
                                                    <strong>Barras:</strong> <?php echo htmlspecialchars($producto['codigo_barras']); ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (empty($producto['codigo_interno']) && empty($producto['codigo_barras'])): ?>
                                                <div style="color: #6c757d; font-style: italic;">
                                                    Sin códigos
                                        </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($producto['proveedor'] ?: 'Sin proveedor'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Ver" onclick="verProducto(<?php echo $producto['ID_PRODUCTO']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action btn-edit" title="Editar" onclick="editarProducto(<?php echo $producto['ID_PRODUCTO']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Eliminar" onclick="eliminarProducto(<?php echo $producto['ID_PRODUCTO']; ?>, '<?php echo htmlspecialchars($producto['nombre_producto']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px; color: #6c757d;">
                                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                                        <div>No hay productos registrados</div>
                                        <div style="font-size: 0.9rem; margin-top: 8px;">Haz clic en "Agregar Producto" para comenzar</div>
                                    </td>
                                </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades específicas de productos
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initProductFilters();
            checkUrlMessages();
        });
    </script>
</body>
</html> 