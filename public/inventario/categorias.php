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
    
    // Obtener estadísticas mejoradas de categorías
    try {
        // Obtener estadísticas de categorías desde la tabla t_categorias
        $stmt_categorias = $conn->prepare("
            SELECT 
                COUNT(*) as total_categorias,
                COUNT(CASE WHEN estado = 'activo' THEN 1 END) as categorias_activas
            FROM t_categorias 
            WHERE ID_EMPRESA = ?
        ");
        $stmt_categorias->bind_param("i", $id_empresa);
        $stmt_categorias->execute();
        $stats_categorias = $stmt_categorias->get_result()->fetch_assoc();
        
        // Obtener estadísticas de productos
        $stmt_productos = $conn->prepare("
            SELECT 
                COUNT(*) as total_productos,
                SUM(stock) as stock_total,
                SUM(stock * precio) as valor_total,
                COUNT(DISTINCT categoria) as categorias_con_productos
            FROM t_productos 
            WHERE ID_EMPRESA = ? AND categoria IS NOT NULL AND categoria != ''
        ");
        $stmt_productos->bind_param("i", $id_empresa);
        $stmt_productos->execute();
        $stats_productos = $stmt_productos->get_result()->fetch_assoc();
        
        // Calcular promedio de productos por categoría
        $promedio_productos = $stats_categorias['total_categorias'] > 0 ? 
            round($stats_productos['total_productos'] / $stats_categorias['total_categorias']) : 0;
        
        // Combinar estadísticas
        $stats = [
            'total_categorias' => $stats_categorias['total_categorias'],
            'categorias_activas' => $stats_categorias['categorias_activas'],
            'total_productos' => $stats_productos['total_productos'],
            'stock_total' => $stats_productos['stock_total'] ?? 0,
            'valor_total' => $stats_productos['valor_total'] ?? 0,
            'promedio_productos' => $promedio_productos,
            'categorias_con_productos' => $stats_productos['categorias_con_productos']
        ];
        

        
        // Obtener categorías con más productos para top categorías
        $stmt_top_categorias = $conn->prepare("
            SELECT 
                c.nombre_categoria as categoria,
                c.color,
                c.estado,
                COUNT(p.ID_PRODUCTO) as cantidad_productos,
                SUM(p.stock) as stock_total,
                SUM(p.stock * p.precio) as valor_total,
                c.fecha_creacion
            FROM t_categorias c
            LEFT JOIN t_productos p ON c.nombre_categoria COLLATE utf8mb4_unicode_ci = p.categoria COLLATE utf8mb4_unicode_ci 
                AND c.ID_EMPRESA = p.ID_EMPRESA
            WHERE c.ID_EMPRESA = ?
            GROUP BY c.ID_CATEGORIA, c.nombre_categoria, c.color, c.estado, c.fecha_creacion
            ORDER BY cantidad_productos DESC, c.fecha_creacion DESC
            LIMIT 5
        ");
        $stmt_top_categorias->bind_param("i", $id_empresa);
        $stmt_top_categorias->execute();
        $top_categorias = $stmt_top_categorias->get_result();
        
        // Obtener ventas por categoría (últimos 30 días)
        $stmt_ventas_categoria = $conn->prepare("
            SELECT 
                p.categoria,
                COUNT(m.ID_MOVIMIENTO) as total_movimientos,
                SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.cantidad ELSE 0 END) as unidades_vendidas,
                SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.valor_movimiento ELSE 0 END) as valor_ventas,
                COUNT(DISTINCT p.ID_PRODUCTO) as productos_vendidos
            FROM t_movimientos_inventario m
            JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
            WHERE m.ID_EMPRESA = ? 
            AND m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND m.tipo_movimiento = 'salida'
            AND p.categoria IS NOT NULL AND p.categoria != ''
            GROUP BY p.categoria
            HAVING unidades_vendidas > 0
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
        
        // Obtener estadísticas de crecimiento (comparar con mes anterior)
        $stmt_crecimiento = $conn->prepare("
            SELECT 
                SUM(CASE 
                    WHEN m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    AND m.tipo_movimiento = 'salida'
                    THEN m.valor_movimiento 
                    ELSE 0 
                END) as ventas_mes_actual,
                SUM(CASE 
                    WHEN m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 60 DAY) 
                    AND m.fecha_movimiento < DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND m.tipo_movimiento = 'salida'
                    THEN m.valor_movimiento 
                    ELSE 0 
                END) as ventas_mes_anterior,
                COUNT(CASE 
                    WHEN m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    AND m.tipo_movimiento = 'salida'
                    THEN 1 
                    ELSE NULL 
                END) as movimientos_mes_actual,
                COUNT(CASE 
                    WHEN m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 60 DAY) 
                    AND m.fecha_movimiento < DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND m.tipo_movimiento = 'salida'
                    THEN 1 
                    ELSE NULL 
                END) as movimientos_mes_anterior
            FROM t_movimientos_inventario m
            WHERE m.ID_EMPRESA = ? 
            AND m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 60 DAY)
        ");
        $stmt_crecimiento->bind_param("i", $id_empresa);
        $stmt_crecimiento->execute();
        $crecimiento_data = $stmt_crecimiento->get_result()->fetch_assoc();
        
        // Calcular porcentaje de crecimiento
        $ventas_mes_actual = $crecimiento_data['ventas_mes_actual'] ?? 0;
        $ventas_mes_anterior = $crecimiento_data['ventas_mes_anterior'] ?? 0;
        $movimientos_mes_actual = $crecimiento_data['movimientos_mes_actual'] ?? 0;
        $movimientos_mes_anterior = $crecimiento_data['movimientos_mes_anterior'] ?? 0;
        
        // Calcular crecimiento basado en ventas
        if ($ventas_mes_anterior > 0) {
            $porcentaje_crecimiento_ventas = round((($ventas_mes_actual - $ventas_mes_anterior) / $ventas_mes_anterior) * 100, 1);
        } else {
            $porcentaje_crecimiento_ventas = $ventas_mes_actual > 0 ? 100 : 0;
        }
        
        // Calcular crecimiento basado en número de movimientos
        if ($movimientos_mes_anterior > 0) {
            $porcentaje_crecimiento_movimientos = round((($movimientos_mes_actual - $movimientos_mes_anterior) / $movimientos_mes_anterior) * 100, 1);
        } else {
            $porcentaje_crecimiento_movimientos = $movimientos_mes_actual > 0 ? 100 : 0;
        }
        
        // Usar el promedio de ambos crecimientos para un cálculo más equilibrado
        $porcentaje_crecimiento = round(($porcentaje_crecimiento_ventas + $porcentaje_crecimiento_movimientos) / 2, 1);
        
        // Obtener estadísticas adicionales para mostrar en las cards
        $stats['ventas_mes_actual'] = $ventas_mes_actual;
        $stats['ventas_mes_anterior'] = $ventas_mes_anterior;
        $stats['movimientos_mes_actual'] = $movimientos_mes_actual;
        $stats['movimientos_mes_anterior'] = $movimientos_mes_anterior;
        $stats['porcentaje_crecimiento'] = $porcentaje_crecimiento;
        
    } catch (Exception $e) {
        $stats = [
            'total_categorias' => 0,
            'categorias_activas' => 0,
            'total_productos' => 0,
            'stock_total' => 0,
            'valor_total' => 0,
            'promedio_productos' => 0
        ];
        $top_categorias = null;
        $ventas_categoria = null;
        $total_ventas = 0;
        $total_unidades_vendidas = 0;
    }
    
    // Inicializar variables por defecto en caso de que no estén definidas
    if (!isset($ventas_categoria)) {
        $ventas_categoria = null;
    }
    if (!isset($total_ventas)) {
        $total_ventas = 0;
    }
    if (!isset($total_unidades_vendidas)) {
        $total_unidades_vendidas = 0;
    }
    
    // Calcular porcentajes
    $porcentaje_activas = $stats['total_categorias'] > 0 ? round(($stats['categorias_activas'] / $stats['total_categorias']) * 100) : 0;
    $promedio_productos = round($stats['promedio_productos'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestión de Categorías</title>
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
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Gestión de Categorías</h1>
                            <p class="page-subtitle">Organiza y administra las categorías de productos</p>
                        </div>
                        <div class="page-header-actions">
                            <button class="btn-primary btn-large" onclick="window.location.href='agregar-categoria.php'">
                                <i class="fas fa-plus"></i> Nueva Categoría
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Metrics Cards -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-tags"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['total_categorias']); ?></div>
                        <div class="metric-details">Total Categorías</div>
                        <div class="metric-indicator"><?php echo number_format($stats['total_productos']); ?> productos</div>
                    </div>



                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #fd7e14;">
                            <i class="fas fa-mouse"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['total_productos']); ?></div>
                        <div class="metric-details">Productos Asignados</div>
                        <div class="metric-indicator">Promedio <?php echo $promedio_productos; ?> por categoría</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #28a745;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="metric-value">₡<?php echo number_format($stats['valor_total'], 0, ',', '.'); ?></div>
                        <div class="metric-details">Valor Total</div>
                        <div class="metric-indicator">Stock × Precio</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #fd7e14;">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['stock_total']); ?></div>
                        <div class="metric-details">Stock Total</div>
                        <div class="metric-indicator">Unidades disponibles</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $promedio_productos; ?></div>
                        <div class="metric-details">Promedio por Categoría</div>
                        <div class="metric-indicator">Productos por categoría</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #e83e8c;">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($total_unidades_vendidas); ?></div>
                        <div class="metric-details">Productos Vendidos</div>
                        <div class="metric-indicator">₡<?php echo number_format($total_ventas); ?> en ventas</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: <?php echo $porcentaje_crecimiento >= 0 ? '#28a745' : '#dc3545'; ?>;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $porcentaje_crecimiento >= 0 ? '+' : ''; ?><?php echo $porcentaje_crecimiento; ?>%</div>
                        <div class="metric-details">Crecimiento Mensual</div>
                        <div class="metric-indicator">vs mes anterior</div>
                    </div>

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
                                
                                // Obtener el color real de la categoría
                                $stmt_color = $conn->prepare("SELECT color FROM t_categorias WHERE nombre_categoria = ? AND ID_EMPRESA = ?");
                                $stmt_color->bind_param("si", $categoria_venta['categoria'], $id_empresa);
                                $stmt_color->execute();
                                $color_result = $stmt_color->get_result()->fetch_assoc();
                                $color_categoria = $color_result['color'] ?: '#6f42c1'; // Color por defecto
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
                    <div class="top-products">
                        <div class="top-products-header">
                            <h3 class="chart-title">
                                Categorías por Valor
                                <i class="fas fa-info-circle tooltip-icon" data-tooltip="Ordenadas por cantidad de productos (más productos primero) y fecha de creación (más recientes primero). Muestra las 5 categorías principales."></i>
                            </h3>
                            <button class="btn-icon">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        
                        <?php 
                        if ($top_categorias && $top_categorias->num_rows > 0): 
                            $rank = 1;
                            while ($categoria = $top_categorias->fetch_assoc()): 
                                // Obtener ventas de esta categoría en los últimos 30 días
                                $stmt_ventas_cat = $conn->prepare("
                                    SELECT 
                                        SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.cantidad ELSE 0 END) as unidades_vendidas,
                                        SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.valor_movimiento ELSE 0 END) as valor_ventas
                                    FROM t_movimientos_inventario m
                                    JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
                                    WHERE m.ID_EMPRESA = ? 
                                    AND p.categoria = ?
                                    AND m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                ");
                                $stmt_ventas_cat->bind_param("is", $id_empresa, $categoria['categoria']);
                                $stmt_ventas_cat->execute();
                                $ventas_cat = $stmt_ventas_cat->get_result()->fetch_assoc();
                                
                                $unidades_vendidas = $ventas_cat['unidades_vendidas'] ?? 0;
                                $valor_ventas = $ventas_cat['valor_ventas'] ?? 0;
                                
                                // Calcular crecimiento real por categoría
                                $stmt_crecimiento_cat = $conn->prepare("
                                    SELECT 
                                        SUM(CASE 
                                            WHEN m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                                            THEN m.valor_movimiento 
                                            ELSE 0 
                                        END) as ventas_mes_actual,
                                        SUM(CASE 
                                            WHEN m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 60 DAY) 
                                            AND m.fecha_movimiento < DATE_SUB(NOW(), INTERVAL 30 DAY)
                                            THEN m.valor_movimiento 
                                            ELSE 0 
                                        END) as ventas_mes_anterior
                                    FROM t_movimientos_inventario m
                                    JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
                                    WHERE m.ID_EMPRESA = ? 
                                    AND p.categoria = ?
                                    AND m.tipo_movimiento = 'salida'
                                    AND m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                                ");
                                $stmt_crecimiento_cat->bind_param("is", $id_empresa, $categoria['categoria']);
                                $stmt_crecimiento_cat->execute();
                                $crecimiento_cat = $stmt_crecimiento_cat->get_result()->fetch_assoc();
                                
                                $ventas_mes_actual_cat = $crecimiento_cat['ventas_mes_actual'] ?? 0;
                                $ventas_mes_anterior_cat = $crecimiento_cat['ventas_mes_anterior'] ?? 0;
                                
                                if ($ventas_mes_anterior_cat > 0) {
                                    $crecimiento = round((($ventas_mes_actual_cat - $ventas_mes_anterior_cat) / $ventas_mes_anterior_cat) * 100, 1);
                                } else {
                                    $crecimiento = $ventas_mes_actual_cat > 0 ? 100 : 0;
                                }
                                
                                // Color de la categoría (usar color real o color por defecto)
                                $color_categoria = $categoria['color'] ?: '#6f42c1';
                                
                                // Icono según el nombre de la categoría
                                $iconos = [
                                    'electrónicos' => 'fas fa-laptop',
                                    'ropa' => 'fas fa-tshirt',
                                    'hogar' => 'fas fa-home',
                                    'alimentos' => 'fas fa-utensils',
                                    'bebidas' => 'fas fa-wine-bottle',
                                    'limpieza' => 'fas fa-broom',
                                    'papelería' => 'fas fa-pencil-alt',
                                    'deportes' => 'fas fa-futbol',
                                    'juguetes' => 'fas fa-gamepad',
                                    'libros' => 'fas fa-book'
                                ];
                                
                                $icono_categoria = 'fas fa-tags'; // Icono por defecto
                                foreach ($iconos as $palabra => $icono) {
                                    if (stripos($categoria['categoria'], $palabra) !== false) {
                                        $icono_categoria = $icono;
                                        break;
                                    }
                                }
                        ?>
                        <div class="product-item">
                            <div class="product-rank"><?php echo $rank; ?></div>
                            <div class="product-icon" style="background: <?php echo $color_categoria; ?>;">
                                <i class="<?php echo $icono_categoria; ?>"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($categoria['categoria']); ?></div>
                                <div class="product-category"><?php echo $categoria['cantidad_productos']; ?> productos</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">₡<?php echo number_format($categoria['valor_total']); ?></div>
                                <div class="product-revenue">₡<?php echo number_format($categoria['valor_total'] / max($categoria['cantidad_productos'], 1)); ?></div>
                                <div class="product-growth">
                                    <i class="fas fa-box"></i>
                                    <?php echo number_format($categoria['stock_total']); ?>
                                </div>
                            </div>
                        </div>
                        <?php 
                                $rank++;
                            endwhile; 
                        else: 
                        ?>
                        <div class="product-item">
                            <div class="product-info" style="text-align: center; width: 100%;">
                                <div class="product-name">No hay categorías con productos</div>
                                <div class="product-category">Los datos aparecerán cuando agregues productos a las categorías</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Categories Table Section -->
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="section-title">Lista de Categorías</h2>
                        <div class="header-buttons">
                            <button class="btn-secondary" onclick="descargarPDFCategorias()" title="Descargar PDF de Categorías">
                                <i class="fas fa-download"></i> Descargar PDF
                            </button>
                            <button class="btn-primary" onclick="window.location.href='agregar-categoria.php'">
                                <i class="fas fa-plus"></i> Nueva Categoría
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
                            <input type="text" class="search-input" placeholder="Buscar por nombre de categoría, descripción..." id="searchInput">
                        </div>
                        
                        <div class="filter-group">
                            <select class="filter-select" id="statusFilter">
                                <option value="">Todos los estados</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactiva">Inactiva</option>
                            </select>
                            
                            <select class="filter-select" id="productFilter">
                                <option value="">Todas las categorías</option>
                                <option value="con_productos">Con productos</option>
                                <option value="sin_productos">Sin productos</option>
                            </select>
                            
                            <select class="filter-select" id="sortFilter">
                                <option value="nombre_asc">Nombre A-Z</option>
                                <option value="nombre_desc">Nombre Z-A</option>
                                <option value="fecha_desc">Más recientes</option>
                                <option value="fecha_asc">Más antiguas</option>
                                <option value="productos_desc">Más productos</option>
                                <option value="productos_asc">Menos productos</option>
                            </select>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="table-container">
                        <table class="products-table categories-table">
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Productos</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                <?php
                                // Obtener todas las categorías de la empresa con estadísticas
                                $stmt = $conn->prepare("
                                    SELECT c.*, 
                                           COUNT(p.ID_PRODUCTO) as total_productos,
                                           SUM(p.stock) as stock_total,
                                           SUM(p.stock * p.precio) as valor_total
                                    FROM t_categorias c
                                    LEFT JOIN t_productos p ON c.nombre_categoria COLLATE utf8mb4_unicode_ci = p.categoria COLLATE utf8mb4_unicode_ci AND c.ID_EMPRESA = p.ID_EMPRESA
                                    WHERE c.ID_EMPRESA = ?
                                    GROUP BY c.ID_CATEGORIA, c.nombre_categoria, c.descripcion, c.estado, c.fecha_creacion
                                    ORDER BY c.nombre_categoria ASC
                                ");
                                
                                if (!$stmt) {
                                    die("Error en la preparación de la consulta de categorías: " . $conn->error);
                                }
                                
                                // Obtener el ID de la empresa del usuario
                                $stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_empresa WHERE ID_DUEÑO = ?");
                                $stmt_empresa->bind_param("i", $id_usuario);
                                $stmt_empresa->execute();
                                $empresa_result = $stmt_empresa->get_result()->fetch_assoc();
                                $id_empresa = $empresa_result['ID_EMPRESA'];
                                
                                $stmt->bind_param("i", $id_empresa);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    while ($categoria = $result->fetch_assoc()) {
                                        // Determinar el estado de la categoría
                                        if ($categoria['total_productos'] > 0) {
                                            $estado_clase = 'status-active';
                                            $estado_texto = 'Activo';
                                        } else {
                                            $estado_clase = 'status-inactive';
                                            $estado_texto = 'Inactiva';
                                        }
                                        
                                        // Formatear fecha
                                        $fecha_formateada = date('d/m/Y', strtotime($categoria['fecha_creacion']));
                                        
                                        // Clases para filtros
                                        $estado_class = strtolower(str_replace(' ', '-', $estado_texto));
                                        $productos_class = $categoria['total_productos'] > 0 ? 'con-productos' : 'sin-productos';
                                        
                                        // Color de la categoría
                                        $color_categoria = $categoria['color'] ?: '#6c757d';
                                ?>
                                <tr data-categoria-id="<?php echo $categoria['ID_CATEGORIA']; ?>"
                                    data-nombre="<?php echo htmlspecialchars(strtolower($categoria['nombre_categoria'])); ?>"
                                    data-descripcion="<?php echo htmlspecialchars(strtolower($categoria['descripcion'] ?: '')); ?>"
                                    data-estado="<?php echo $estado_texto; ?>"
                                    data-productos="<?php echo $categoria['total_productos']; ?>"
                                    data-fecha="<?php echo strtotime($categoria['fecha_creacion']); ?>"
                                    class="category-row <?php echo $estado_class; ?> <?php echo $productos_class; ?>">
                                    <td>
                                        <div class="category-info">
                                            <div class="category-color" style="background: <?php echo $color_categoria; ?>;"></div>
                                            <div>
                                                <div class="category-name"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></div>
                                                <div class="category-meta">
                                                    Creada: <?php echo $fecha_formateada; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="category-description">
                                            <?php echo htmlspecialchars($categoria['descripcion'] ?: 'Sin descripción'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="category-stats">
                                            <div class="products-count">
                                                <i class="fas fa-box"></i>
                                                <?php echo $categoria['total_productos']; ?> productos
                                            </div>
                                            <?php if ($categoria['stock_total'] > 0): ?>
                                                <div class="stock-info">
                                                    <i class="fas fa-warehouse"></i>
                                                    <?php echo number_format($categoria['stock_total']); ?> unidades
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($categoria['valor_total'] > 0): ?>
                                                <div class="value-info">
                                                    <i class="fas fa-dollar-sign"></i>
                                                    ₡<?php echo number_format($categoria['valor_total']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $estado_clase; ?>">
                                            <?php echo $estado_texto; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $fecha_formateada; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Ver categoría" onclick="verCategoria(<?php echo $categoria['ID_CATEGORIA']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action btn-edit" title="Editar categoría" onclick="editarCategoria(<?php echo $categoria['ID_CATEGORIA']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($categoria['total_productos'] == 0): ?>
                                                <button class="btn-action btn-delete" title="Eliminar categoría" onclick="eliminarCategoria(<?php echo $categoria['ID_CATEGORIA']; ?>, '<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-action btn-delete" title="No se puede eliminar (tiene productos)" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                                        <i class="fas fa-tags" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                                        <div>No hay categorías registradas</div>
                                        <div style="font-size: 0.9rem; margin-top: 8px;">Haz clic en "Nueva Categoría" para comenzar</div>
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
        // Inicializar funcionalidades específicas de categorías
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initCategoryFilters();
            checkUrlMessages();
        });
    </script>
</body>
</html> 