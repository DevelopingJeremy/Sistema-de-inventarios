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
    
    // Obtener estadísticas dinámicas de ventas
    try {
        $stmt_stats = $conn->prepare("
            SELECT 
                COUNT(*) as total_ventas,
                SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as ventas_completadas,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as ventas_pendientes,
                SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as ventas_canceladas,
                COALESCE(SUM(total), 0) as total_ingresos,
                COALESCE(AVG(total), 0) as promedio_venta
            FROM t_ventas 
            WHERE ID_EMPRESA = ?
        ");
        $stmt_stats->bind_param("i", $id_empresa);
        $stmt_stats->execute();
        $stats_ventas = $stmt_stats->get_result()->fetch_assoc();
        
        // Obtener ventas del mes actual
        $stmt_ventas_mes = $conn->prepare("
            SELECT 
                COUNT(*) as ventas_mes,
                COALESCE(SUM(total), 0) as ingresos_mes
            FROM t_ventas 
            WHERE ID_EMPRESA = ? 
            AND MONTH(fecha_venta) = MONTH(CURRENT_DATE())
            AND YEAR(fecha_venta) = YEAR(CURRENT_DATE())
        ");
        $stmt_ventas_mes->bind_param("i", $id_empresa);
        $stmt_ventas_mes->execute();
        $ventas_mes = $stmt_ventas_mes->get_result()->fetch_assoc();
        
        // Obtener top productos más vendidos
        $stmt_top_productos = $conn->prepare("
            SELECT 
                p.nombre_producto,
                p.categoria,
                p.precio,
                COUNT(vd.ID_DETALLE) as veces_vendido,
                SUM(vd.cantidad) as unidades_vendidas,
                SUM(vd.subtotal) as valor_ventas
            FROM t_ventas_detalle vd
            JOIN t_productos p ON vd.ID_PRODUCTO = p.ID_PRODUCTO
            JOIN t_ventas v ON vd.ID_VENTA = v.ID_VENTA
            WHERE v.ID_EMPRESA = ? 
            AND v.estado = 'completada'
            AND v.fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p.ID_PRODUCTO, p.nombre_producto, p.categoria, p.precio
            ORDER BY unidades_vendidas DESC, valor_ventas DESC
            LIMIT 5
        ");
        $stmt_top_productos->bind_param("i", $id_empresa);
        $stmt_top_productos->execute();
        $top_productos = $stmt_top_productos->get_result();
        
        // Obtener ventas por método de pago
        $stmt_metodos_pago = $conn->prepare("
            SELECT 
                metodo_pago,
                COUNT(*) as total_ventas,
                COALESCE(SUM(total), 0) as valor_total
            FROM t_ventas 
            WHERE ID_EMPRESA = ? 
            AND estado = 'completada'
            AND fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY metodo_pago
            ORDER BY valor_total DESC
        ");
        $stmt_metodos_pago->bind_param("i", $id_empresa);
        $stmt_metodos_pago->execute();
        $metodos_pago = $stmt_metodos_pago->get_result();
        
        // Calcular total de ventas
        $total_ventas = 0;
        $total_ingresos = 0;
        if ($metodos_pago) {
            while ($row = $metodos_pago->fetch_assoc()) {
                $total_ventas += $row['total_ventas'];
                $total_ingresos += $row['valor_total'];
            }
        }
        
    } catch (Exception $e) {
        // En caso de error, establecer valores por defecto
        $stats_ventas = [
            'total_ventas' => 0,
            'ventas_completadas' => 0,
            'ventas_pendientes' => 0,
            'ventas_canceladas' => 0,
            'total_ingresos' => 0,
            'promedio_venta' => 0
        ];
        $ventas_mes = [
            'ventas_mes' => 0,
            'ingresos_mes' => 0
        ];
        $top_productos = null;
        $metodos_pago = null;
        $total_ventas = 0;
        $total_ingresos = 0;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - HYBOX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <?php include('../../includes/sidebar.php'); ?>
        
        <div class="main-content">
            <?php include("../../includes/header.php"); ?>
            
            <div class="content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Gestión de Ventas</h1>
                            <p class="page-subtitle">Administra y controla todas las ventas de tu empresa</p>
                        </div>
                        <div class="page-header-actions">
                            <a href="nueva-venta.php" class="btn-primary btn-large">
                                <i class="fas fa-plus"></i>
                                Crear Nueva Venta
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Métricas de Ventas -->
                <div class="dashboard-metrics">
                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title-dashboard">Total Ventas</h3>
                                <div class="metric-value-dashboard"><?php echo number_format($stats_ventas['total_ventas']); ?></div>
                                <div class="metric-indicator-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $ventas_mes['ventas_mes']; ?> este mes</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title-dashboard">Ingresos Totales</h3>
                                <div class="metric-value-dashboard">$<?php echo number_format($stats_ventas['total_ingresos'], 2); ?></div>
                                <div class="metric-indicator-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>$<?php echo number_format($ventas_mes['ingresos_mes'], 2); ?> este mes</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title-dashboard">Ventas Completadas</h3>
                                <div class="metric-value-dashboard"><?php echo number_format($stats_ventas['ventas_completadas']); ?></div>
                                <div class="metric-indicator-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $stats_ventas['total_ventas'] > 0 ? round(($stats_ventas['ventas_completadas'] / $stats_ventas['total_ventas']) * 100, 1) : 0; ?>% del total</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title-dashboard">Promedio por Venta</h3>
                                <div class="metric-value-dashboard">$<?php echo number_format($stats_ventas['promedio_venta'], 2); ?></div>
                                <div class="metric-indicator-dashboard neutral">
                                    <i class="fas fa-minus"></i>
                                    <span>Promedio general</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-content">
                    <!-- Productos Más Vendidos -->
                    <div class="top-selling-products">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">Productos Más Vendidos</h2>
                                <p class="section-subtitle">Últimos 30 días</p>
                            </div>
                            <a href="../inventario/productos.php" class="view-all-link">
                                Ver todos <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <div class="top-products">
                            <?php if ($top_productos && $top_productos->num_rows > 0): ?>
                                <?php $rank = 1; ?>
                                <?php while ($producto = $top_productos->fetch_assoc()): ?>
                                    <div class="product-item-dashboard">
                                        <div class="product-rank-dashboard">#<?php echo $rank; ?></div>
                                        <div class="product-icon-dashboard">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="product-info-dashboard">
                                            <div class="product-name-dashboard"><?php echo htmlspecialchars($producto['nombre_producto']); ?></div>
                                            <div class="product-category-dashboard"><?php echo htmlspecialchars($producto['categoria']); ?></div>
                                        </div>
                                        <div class="product-stats-dashboard">
                                            <div class="product-sales-dashboard">
                                                <span class="sales-label">Vendido:</span>
                                                <span class="sales-value"><?php echo number_format($producto['unidades_vendidas']); ?> unidades</span>
                                            </div>
                                            <div class="product-revenue-dashboard">
                                                <span class="revenue-label">Ingresos:</span>
                                                <span class="revenue-value">$<?php echo number_format($producto['valor_ventas'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $rank++; ?>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-chart-bar"></i>
                                    <p>No hay datos de ventas disponibles</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Métodos de Pago -->
                    <div class="sales-by-category">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">Ventas por Método de Pago</h2>
                                <p class="section-subtitle">Últimos 30 días</p>
                            </div>
                        </div>
                        
                        <div class="category-sales">
                            <?php if ($metodos_pago && $metodos_pago->num_rows > 0): ?>
                                <?php while ($metodo = $metodos_pago->fetch_assoc()): ?>
                                    <?php 
                                        $porcentaje = $total_ingresos > 0 ? ($metodo['valor_total'] / $total_ingresos) * 100 : 0;
                                        $iconos = [
                                            'efectivo' => 'fas fa-money-bill-wave',
                                            'tarjeta' => 'fas fa-credit-card',
                                            'transferencia' => 'fas fa-university',
                                            'digital' => 'fas fa-mobile-alt'
                                        ];
                                        $icono = $iconos[$metodo['metodo_pago']] ?? 'fas fa-credit-card';
                                    ?>
                                    <div class="category-sales-item">
                                        <div class="category-sales-header">
                                            <div class="category-sales-icon">
                                                <i class="<?php echo $icono; ?>"></i>
                                            </div>
                                            <div class="category-sales-info">
                                                <div class="category-sales-name"><?php echo ucfirst($metodo['metodo_pago']); ?></div>
                                                <div class="category-sales-meta"><?php echo $metodo['total_ventas']; ?> ventas</div>
                                            </div>
                                        </div>
                                        <div class="category-sales-stats">
                                            <div class="category-sales-value">$<?php echo number_format($metodo['valor_total'], 2); ?></div>
                                            <div class="category-sales-percentage">
                                                <span><?php echo round($porcentaje, 1); ?>%</span>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-credit-card"></i>
                                    <p>No hay datos de métodos de pago</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Ventas -->
                <div class="products-section">
                    <div class="products-header">
                        <div class="section-title">Historial de Ventas</div>
                        <div class="header-buttons">
                            <button class="btn-primary" onclick="descargarPDFVentas()">
                                <i class="fas fa-download"></i>
                                Descargar PDF
                            </button>
                        </div>
                    </div>

                    <div class="filters-section">
                        <div class="search-box">
                            <input type="text" id="searchInput" class="search-input" placeholder="Buscar ventas...">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                        <div class="filter-group">
                            <select id="estadoFilter" class="filter-select">
                                <option value="">Todos los estados</option>
                                <option value="completada">Completada</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <select id="metodoPagoFilter" class="filter-select">
                                <option value="">Todos los métodos</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="digital">Digital</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <select id="ordenFilter" class="filter-select">
                                <option value="fecha_desc">Más recientes</option>
                                <option value="fecha_asc">Más antiguos</option>
                                <option value="total_desc">Mayor monto</option>
                                <option value="total_asc">Menor monto</option>
                            </select>
                        </div>
                        <button id="clearFiltersBtn" class="btn-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-times"></i>
                            Limpiar
                        </button>
                    </div>

                    <div class="table-container">
                        <table class="products-table ventas-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Productos</th>
                                    <th>Total</th>
                                    <th>Método Pago</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="ventasTableBody">
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMobileMenu();
            initVentasFilters();
            cargarVentas();
        });

        function limpiarFiltros() {
            document.getElementById('searchInput').value = '';
            document.getElementById('estadoFilter').value = '';
            document.getElementById('metodoPagoFilter').value = '';
            document.getElementById('ordenFilter').value = 'fecha_desc';
            aplicarFiltrosVentas();
        }
    </script>
</body>
</html> 