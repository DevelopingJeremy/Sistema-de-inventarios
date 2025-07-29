<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    // Obtener datos para los filtros
    $id_usuario = $_SESSION['id_usuario'];
    
    // Obtener el ID de la empresa del usuario
    $stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_usuarios WHERE ID_USUARIO = ?");
    $stmt_empresa->bind_param("i", $id_usuario);
    $stmt_empresa->execute();
    $result_empresa = $stmt_empresa->get_result();
    
    if ($result_empresa->num_rows > 0) {
        $empresa_data = $result_empresa->fetch_assoc();
        $id_empresa = $empresa_data['ID_EMPRESA'];
    } else {
        header('Location: ../empresa/registrar-empresa.php');
        exit();
    }
    
    // Obtener estadísticas dinámicas de productos
    try {
        $stmt_stats = $conn->prepare("
            SELECT 
                COUNT(*) as total_productos,
                SUM(CASE WHEN stock > 0 THEN 1 ELSE 0 END) as productos_activos,
                SUM(CASE WHEN stock <= 5 AND stock > 0 THEN 1 ELSE 0 END) as stock_bajo,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as sin_stock,
                SUM(stock * precio) as valor_total,
                SUM(stock) as stock_total
            FROM t_productos 
            WHERE ID_EMPRESA = ?
        ");
        $stmt_stats->bind_param("i", $id_empresa);
        $stmt_stats->execute();
        $stats_productos = $stmt_stats->get_result()->fetch_assoc();
        
        // Obtener ventas del mes (movimientos de salida)
        $stmt_ventas = $conn->prepare("
            SELECT SUM(valor_movimiento) as ventas_mes
            FROM t_movimientos_inventario 
            WHERE ID_EMPRESA = ? 
            AND tipo_movimiento = 'salida'
            AND fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt_ventas->bind_param("i", $id_empresa);
        $stmt_ventas->execute();
        $ventas_mes = $stmt_ventas->get_result()->fetch_assoc();
        
        // Obtener top productos más vendidos
        $stmt_top_productos = $conn->prepare("
            SELECT 
                p.nombre_producto,
                p.categoria,
                p.precio,
                COUNT(m.ID_MOVIMIENTO) as total_movimientos,
                SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.cantidad ELSE 0 END) as unidades_vendidas,
                SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.valor_movimiento ELSE 0 END) as valor_ventas
            FROM t_productos p
            LEFT JOIN t_movimientos_inventario m ON p.ID_PRODUCTO = m.ID_PRODUCTO AND m.tipo_movimiento = 'salida'
            WHERE p.ID_EMPRESA = ? 
            AND m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p.ID_PRODUCTO, p.nombre_producto, p.categoria, p.precio
            ORDER BY unidades_vendidas DESC, valor_ventas DESC
            LIMIT 5
        ");
        $stmt_top_productos->bind_param("i", $id_empresa);
        $stmt_top_productos->execute();
        $top_productos = $stmt_top_productos->get_result();
        
        // Obtener ventas por categoría
        $stmt_ventas_categoria = $conn->prepare("
            SELECT 
                p.categoria,
                COUNT(m.ID_MOVIMIENTO) as total_movimientos,
                SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.cantidad ELSE 0 END) as unidades_vendidas,
                SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.valor_movimiento ELSE 0 END) as valor_ventas
            FROM t_movimientos_inventario m
            JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
            WHERE m.ID_EMPRESA = ? 
            AND m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND p.categoria IS NOT NULL AND p.categoria != ''
            GROUP BY p.categoria
            ORDER BY valor_ventas DESC
        ");
        $stmt_ventas_categoria->bind_param("i", $id_empresa);
        $stmt_ventas_categoria->execute();
        $ventas_categoria = $stmt_ventas_categoria->get_result();
        
        // Calcular total de ventas
        $total_ventas = 0;
        $total_unidades_vendidas = 0;
        if ($ventas_categoria) {
            while ($row = $ventas_categoria->fetch_assoc()) {
                $total_ventas += $row['valor_ventas'];
                $total_unidades_vendidas += $row['unidades_vendidas'];
            }
        }
        
        // Obtener datos para la gráfica de distribución de productos por categoría
        $stmt_distribucion = $conn->prepare("
            SELECT 
                p.categoria,
                COUNT(*) as total_productos,
                SUM(p.stock) as stock_total,
                COALESCE(SUM(p.stock * p.precio), 0) as valor_total
            FROM t_productos p
            WHERE p.ID_EMPRESA = ? 
            AND p.categoria IS NOT NULL 
            AND p.categoria != ''
            AND p.categoria != 'NULL'
            GROUP BY p.categoria
            ORDER BY total_productos DESC
        ");
        $stmt_distribucion->bind_param("i", $id_empresa);
        $stmt_distribucion->execute();
        $distribucion_categorias = $stmt_distribucion->get_result();
        
        // Consulta para la gráfica de distribución
        $stmt_simple = $conn->prepare("
            SELECT 
                p.categoria,
                COUNT(*) as total_productos
            FROM t_productos p
            WHERE p.ID_EMPRESA = ? 
            GROUP BY p.categoria
            ORDER BY total_productos DESC
        ");
        $stmt_simple->bind_param("i", $id_empresa);
        $stmt_simple->execute();
        $simple_result = $stmt_simple->get_result();
        

        

        
        // Obtener top categorías por valor total
        $stmt_top_categorias = $conn->prepare("
            SELECT 
                p.categoria,
                COUNT(p.ID_PRODUCTO) as total_productos,
                SUM(p.stock) as stock_total,
                COALESCE(SUM(p.stock * p.precio), 0) as valor_total
            FROM t_productos p
            WHERE p.ID_EMPRESA = ? 
            AND p.categoria IS NOT NULL AND p.categoria != ''
            GROUP BY p.categoria
            ORDER BY valor_total DESC
            LIMIT 5
        ");
        $stmt_top_categorias->bind_param("i", $id_empresa);
        $stmt_top_categorias->execute();
        $top_categorias = $stmt_top_categorias->get_result();
        
    } catch (Exception $e) {
        $stats_productos = [
            'total_productos' => 0,
            'productos_activos' => 0,
            'stock_bajo' => 0,
            'sin_stock' => 0,
            'valor_total' => 0,
            'stock_total' => 0
        ];
        $ventas_mes = ['ventas_mes' => 0];
        $top_productos = null;
        $ventas_categoria = null;
        $total_ventas = 0;
        $total_unidades_vendidas = 0;
        $distribucion_categorias = null;
        $top_categorias = null;
    }
    
    // Inicializar variables por defecto en caso de que no estén definidas
    if (!isset($top_productos)) {
        $top_productos = null;
    }
    if (!isset($ventas_categoria)) {
        $ventas_categoria = null;
    }
    if (!isset($total_ventas)) {
        $total_ventas = 0;
    }
    if (!isset($total_unidades_vendidas)) {
        $total_unidades_vendidas = 0;
    }
    if (!isset($distribucion_categorias)) {
        $distribucion_categorias = null;
    }
    if (!isset($top_categorias)) {
        $top_categorias = null;
    }
    
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
                            <div class="metric-title">Productos</div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats_productos['total_productos']); ?></div>
                        <div class="metric-details">Total Productos</div>
                        <div class="metric-indicator"><?php echo number_format($stats_productos['stock_total']); ?> unidades</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #e83e8c;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="metric-title">Activos</div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats_productos['productos_activos']); ?></div>
                        <div class="metric-details">Productos Activos</div>
                        <div class="metric-indicator"><?php echo $stats_productos['total_productos'] > 0 ? round(($stats_productos['productos_activos'] / $stats_productos['total_productos']) * 100) : 0; ?>% del total</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #007bff;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="metric-title">Alertas</div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats_productos['stock_bajo']); ?></div>
                        <div class="metric-details">Stock Bajo (≤5)</div>
                        <div class="metric-indicator"><?php echo number_format($stats_productos['sin_stock']); ?> sin stock</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #28a745;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="metric-title">Valor</div>
                        </div>
                        <div class="metric-value">₡<?php echo number_format($stats_productos['valor_total'], 0, ',', '.'); ?></div>
                        <div class="metric-details">Valor Total</div>
                        <div class="metric-indicator">Stock × Precio</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #007bff;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="metric-title">Movimientos</div>
                        </div>
                        <div class="metric-value">₡<?php echo number_format($ventas_mes['ventas_mes'], 0, ',', '.'); ?></div>
                        <div class="metric-details">Movimientos Salida</div>
                        <div class="metric-indicator">Últimos 30 días</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #e83e8c;">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="metric-title">Ventas</div>
                        </div>
                        <div class="metric-value"><?php echo number_format($total_unidades_vendidas); ?></div>
                        <div class="metric-details">Productos Vendidos</div>
                        <div class="metric-indicator">₡<?php echo number_format($total_ventas); ?> en ventas</div>
                    </div>
                </div>

                <!-- Top Products Section -->
                <div class="top-products-section">
                    <div class="section-header">
                        <div>
                            <h3 class="section-title">Top Productos Más Vendidos</h3>
                            <p class="section-subtitle">Productos con mejor rendimiento (últimos 30 días)</p>
                        </div>
                    </div>

                    <?php if ($top_productos && $top_productos->num_rows > 0): ?>
                        <?php $rank = 1; ?>
                        <?php while ($producto = $top_productos->fetch_assoc()): ?>
                            <div class="product-item-dashboard">
                                <div class="product-rank-dashboard"><?php echo $rank; ?></div>
                                <div class="product-icon-dashboard" style="background: #<?php echo $rank % 2 == 0 ? '6f42c1' : '3b82f6'; ?>;">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="product-info-dashboard">
                                    <div class="product-name-dashboard"><?php echo htmlspecialchars($producto['nombre_producto']); ?></div>
                                    <div class="product-category-dashboard"><?php echo htmlspecialchars($producto['categoria'] ?? 'Sin categoría'); ?></div>
                                </div>
                                <div class="product-stats-dashboard">
                                    <div class="product-sales-dashboard"><?php echo number_format($producto['unidades_vendidas']); ?> unidades</div>
                                    <div class="product-revenue-dashboard">₡<?php echo number_format($producto['valor_ventas']); ?></div>
                                    <div class="product-growth-dashboard positive">
                                        <i class="fas fa-arrow-up"></i>
                                        <?php echo $producto['total_movimientos']; ?> movimientos
                                    </div>
                                </div>
                            </div>
                            <?php $rank++; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="product-item-dashboard">
                            <div class="product-info-dashboard" style="text-align: center; width: 100%;">
                                <div class="product-name-dashboard">No hay datos de productos vendidos</div>
                                <div class="product-category-dashboard">Los productos aparecerán cuando haya movimientos de salida</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sales by Category Section -->
                <div class="sales-by-category-section">
                    <div class="section-header">
                        <div>
                            <h3 class="section-title">Ventas por Categoría</h3>
                            <p class="section-subtitle">Rendimiento de ventas por categoría (últimos 30 días)</p>
                        </div>
                    </div>

                    <?php 
                    // Reset result pointer
                    if ($ventas_categoria) {
                        $ventas_categoria->data_seek(0);
                    }
                    ?>
                    
                    <?php if ($ventas_categoria && $ventas_categoria->num_rows > 0): ?>
                        <?php while ($categoria_venta = $ventas_categoria->fetch_assoc()): ?>
                            <?php
                                $porcentaje_ventas = $total_ventas > 0 ? 
                                    ($categoria_venta['valor_ventas'] / $total_ventas) * 100 : 0;
                                $color_categoria = '#6f42c1'; // Color por defecto
                            ?>
                            <div class="category-sales-item">
                                <div class="category-sales-header">
                                    <div class="category-sales-icon" style="background: <?php echo $color_categoria; ?>;">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div class="category-sales-info">
                                        <div class="category-sales-name"><?php echo htmlspecialchars($categoria_venta['categoria']); ?></div>
                                        <div class="category-sales-meta">
                                            <?php echo number_format($categoria_venta['unidades_vendidas']); ?> unidades vendidas
                                        </div>
                                    </div>
                                </div>
                                <div class="category-sales-stats">
                                    <div class="category-sales-value">₡<?php echo number_format($categoria_venta['valor_ventas']); ?></div>
                                    <div class="category-sales-percentage">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo min($porcentaje_ventas, 100); ?>%; background: <?php echo $color_categoria; ?>;"></div>
                                        </div>
                                        <span><?php echo number_format($porcentaje_ventas, 1); ?>%</span>
                                    </div>
                                    <div class="category-sales-details">
                                        <?php echo $categoria_venta['total_movimientos']; ?> movimientos
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="category-sales-item">
                            <div class="category-sales-info" style="text-align: center; width: 100%;">
                                <div class="category-sales-name">No hay datos de ventas por categoría</div>
                                <div class="category-sales-meta">Los datos aparecerán cuando haya movimientos de salida</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Distribución de Productos</h3>
                            <div class="time-filters">
                                <button class="time-filter active">Semana</button>
                                <button class="time-filter">Mes</button>
                                <button class="time-filter">Año</button>
                            </div>
                        </div>
                        
                        <div class="chart-metrics">
                            <div class="chart-metric">
                                <div class="chart-metric-value"><?php echo number_format($stats_productos['total_productos']); ?></div>
                                <div class="chart-metric-label">Total Productos</div>
                            </div>
                            <div class="chart-metric">
                                <div class="chart-metric-value"><?php echo number_format($stats_productos['productos_activos']); ?></div>
                                <div class="chart-metric-label">Activos</div>
                            </div>
                            <div class="chart-metric growth">
                                <div class="chart-metric-value">₡<?php echo number_format($stats_productos['valor_total']); ?></div>
                                <div class="chart-metric-label">Valor Total</div>
                            </div>
                        </div>
                        
                        <canvas id="distribucionChart" width="400" height="200"></canvas>
                    </div>

                    <div class="top-products">
                        <div class="top-products-header">
                            <h3 class="chart-title">Top Categorías</h3>
                            <button class="btn-icon">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        
                        <?php if ($top_categorias && $top_categorias->num_rows > 0): ?>
                            <?php $rank = 1; ?>
                            <?php while ($categoria = $top_categorias->fetch_assoc()): ?>
                                <?php
                                    $colors = ['#6f42c1', '#28a745', '#007bff', '#e83e8c', '#fd7e14'];
                                    $icons = ['fa-tags', 'fa-box', 'fa-cube', 'fa-archive', 'fa-shopping-bag'];
                                    $color = $colors[($rank - 1) % count($colors)];
                                    $icon = $icons[($rank - 1) % count($icons)];
                                ?>
                        <div class="product-item">
                                    <div class="product-rank"><?php echo $rank; ?></div>
                                    <div class="product-icon" style="background: <?php echo $color; ?>;">
                                        <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="product-info">
                                        <div class="product-name"><?php echo htmlspecialchars($categoria['categoria']); ?></div>
                                        <div class="product-category"><?php echo number_format($categoria['total_productos']); ?> productos</div>
                            </div>
                            <div class="product-stats">
                                        <div class="product-sales"><?php echo number_format($categoria['stock_total']); ?></div>
                                        <div class="product-revenue">₡<?php echo number_format($categoria['valor_total']); ?></div>
                            </div>
                                </div>
                                <?php $rank++; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <div class="product-item">
                                <div class="product-info" style="text-align: center; width: 100%;">
                                    <div class="product-name">No hay categorías disponibles</div>
                                    <div class="product-category">Las categorías aparecerán cuando agregues productos</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Products Table Section -->
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="section-title">Lista de Productos</h2>
                        <div class="header-buttons">
                            <button class="btn-secondary" onclick="descargarPDFProductos()" title="Descargar PDF de Productos">
                                <i class="fas fa-download"></i> Descargar PDF
                            </button>
                            <button class="btn-primary" onclick="window.location.href='agregar-producto.php'">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
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
                            
                            <select class="filter-select" id="stockFilter" style="display: none;">
                                <option value="">Todos los stocks</option>
                                <option value="con_stock">Con stock</option>
                                <option value="sin_stock">Sin stock</option>
                                <option value="stock_bajo">Stock bajo</option>
                            </select>
                            
                            <select class="filter-select" id="sortFilter">
                                <option value="fecha_desc">Más recientes</option>
                                <option value="fecha_asc">Más antiguos</option>
                                <option value="nombre_asc">Nombre A-Z</option>
                                <option value="nombre_desc">Nombre Z-A</option>
                                <option value="precio_asc">Precio menor</option>
                                <option value="precio_desc">Precio mayor</option>
                                <option value="stock_asc">Stock menor</option>
                                <option value="stock_desc">Stock mayor</option>
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
                                        $imagen_url = !empty($producto['imagen']) ? "../../" . $producto['imagen'] : '';
                                        
                                        // Formatear precio
                                        $precio_formateado = '₡' . number_format($producto['precio'], 0, ',', '.');
                                        
                                        // Clases para filtros
                                        $categoria_class = strtolower(str_replace(' ', '-', $producto['categoria'] ?: 'sin-categoria'));
                                        $estado_class = strtolower(str_replace(' ', '-', $estado_texto));
                                        $stock_class = $producto['stock'] > 0 ? 'con-stock' : 'sin-stock';
                                        if ($producto['stock'] > 0 && $producto['stock'] <= $producto['stock_minimo']) {
                                            $stock_class = 'stock-bajo';
                                        }
                                ?>
                                <tr data-product-id="<?php echo $producto['ID_PRODUCTO']; ?>" 
                                    data-categoria="<?php echo htmlspecialchars($producto['categoria'] ?: ''); ?>"
                                    data-estado="<?php echo $estado_texto; ?>"
                                    data-stock="<?php echo $producto['stock']; ?>"
                                    data-precio="<?php echo $producto['precio']; ?>"
                                    data-nombre="<?php echo htmlspecialchars(strtolower($producto['nombre_producto'])); ?>"
                                    data-codigo-barras="<?php echo htmlspecialchars($producto['codigo_barras'] ?: ''); ?>"
                                    data-codigo-interno="<?php echo htmlspecialchars($producto['codigo_interno'] ?: ''); ?>"
                                    data-proveedor="<?php echo htmlspecialchars(strtolower($producto['proveedor'] ?: '')); ?>"
                                    class="product-row <?php echo $categoria_class; ?> <?php echo $estado_class; ?> <?php echo $stock_class; ?>">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <?php if (!empty($imagen_url)): ?>
                                                <img src="<?php echo htmlspecialchars($imagen_url); ?>" class="product-image" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div style="width: 80px; height: 80px; border-radius: 4px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border: 1px solid #e9ecef;">
                                                    <i class="fas fa-image" style="font-size: 24px; color: #6c757d;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight: 600;"><?php echo htmlspecialchars($producto['nombre_producto']); ?></div>
                                                <div style="font-size: 0.85rem; color: #6c757d;">
                                                    Creado: <?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge"><?php echo htmlspecialchars($producto['categoria'] ?: 'Sin categoría'); ?></span>
                                    </td>
                                    <td>
                                        <div class="price-info">
                                            <div class="price-main"><?php echo $precio_formateado; ?></div>
                                            <?php if ($producto['precio_compra'] > 0): ?>
                                                <div class="price-cost">Compra: ₡<?php echo number_format($producto['precio_compra'], 0, ',', '.'); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="stock-info">
                                            <div class="stock-main"><?php echo $producto['stock']; ?></div>
                                            <?php if ($producto['stock_minimo'] > 0): ?>
                                                <div class="stock-min">Mín: <?php echo $producto['stock_minimo']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
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
                                    <td>
                                        <div class="supplier-info">
                                            <?php echo htmlspecialchars($producto['proveedor'] ?: 'Sin proveedor'); ?>
                                            <?php if (!empty($producto['ubicacion'])): ?>
                                                <div class="location-info">
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($producto['ubicacion']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Ver detalles" onclick="verProducto(<?php echo $producto['ID_PRODUCTO']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action btn-edit" title="Editar producto" onclick="editarProducto(<?php echo $producto['ID_PRODUCTO']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-movement" title="Nuevo movimiento" onclick="nuevoMovimiento(<?php echo $producto['ID_PRODUCTO']; ?>, '<?php echo htmlspecialchars($producto['nombre_producto']); ?>')">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Eliminar producto" onclick="eliminarProducto(<?php echo $producto['ID_PRODUCTO']; ?>, '<?php echo htmlspecialchars($producto['nombre_producto']); ?>')">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades específicas de productos
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initProductFilters();
            checkUrlMessages();
            initDistribucionChart(); // Agregar inicialización del gráfico
        });

        // Inicializar gráfica de distribución con datos específicos de PHP
        function initDistribucionChart() {
            const ctx = document.getElementById('distribucionChart');
            if (!ctx) {
                console.log('Canvas no encontrado');
                return;
            }

            try {
                const chartData = <?php 
                    if ($simple_result && $simple_result->num_rows > 0) {
                        $labels = [];
                        $data = [];
                        $colors = ['#6f42c1', '#28a745', '#007bff', '#e83e8c', '#fd7e14', '#20c997', '#ffc107', '#dc3545'];
                        
                        $simple_result->data_seek(0);
                        while ($row = $simple_result->fetch_assoc()) {
                            $labels[] = $row['categoria'];
                            $data[] = (int)$row['total_productos'];
                        }
                        
                        echo json_encode([
                            'labels' => $labels,
                            'data' => $data,
                            'colors' => array_slice($colors, 0, count($labels))
                        ]);
                    } else {
                        echo json_encode(['labels' => [], 'data' => [], 'colors' => []]);
                    }
                ?>;

                console.log('Chart Data:', chartData);
                
                if (chartData.labels.length === 0) {
                    ctx.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.style.cssText = 'height: 200px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;';
                    placeholder.textContent = 'No hay datos para mostrar en la gráfica';
                    ctx.parentNode.appendChild(placeholder);
                    return;
                }

                if (typeof Chart === 'undefined') {
                    console.error('Chart.js no está cargado');
                    return;
                }

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: chartData.colors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value} productos (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                
                console.log('Gráfica inicializada correctamente');
            } catch (error) {
                console.error('Error al inicializar la gráfica:', error);
                ctx.style.display = 'none';
                const placeholder = document.createElement('div');
                placeholder.style.cssText = 'height: 200px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;';
                placeholder.textContent = 'Error al cargar la gráfica';
                ctx.parentNode.appendChild(placeholder);
            }
        }
    </script>
</body>
</html> 