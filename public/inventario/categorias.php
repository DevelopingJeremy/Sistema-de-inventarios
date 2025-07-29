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
        $stmt_stats = $conn->prepare("
            SELECT 
                COUNT(DISTINCT categoria) as total_categorias,
                COUNT(DISTINCT CASE WHEN categoria IS NOT NULL AND categoria != '' THEN categoria END) as categorias_activas,
                COUNT(*) as total_productos,
                SUM(stock) as stock_total,
                SUM(stock * precio) as valor_total,
                AVG(productos_por_categoria.cantidad) as promedio_productos
            FROM t_productos p
            LEFT JOIN (
                SELECT categoria, COUNT(*) as cantidad 
                FROM t_productos 
                WHERE ID_EMPRESA = ? AND categoria IS NOT NULL AND categoria != ''
                GROUP BY categoria
            ) productos_por_categoria ON p.categoria = productos_por_categoria.categoria
            WHERE p.ID_EMPRESA = ?
        ");
        $stmt_stats->bind_param("ii", $id_empresa, $id_empresa);
        $stmt_stats->execute();
        $stats = $stmt_stats->get_result()->fetch_assoc();
        
        // Obtener categorías con más productos
        $stmt_top_categorias = $conn->prepare("
            SELECT 
                categoria,
                COUNT(*) as cantidad_productos,
                SUM(stock) as stock_total,
                SUM(stock * precio) as valor_total
            FROM t_productos 
            WHERE ID_EMPRESA = ? AND categoria IS NOT NULL AND categoria != ''
            GROUP BY categoria
            ORDER BY COUNT(*) DESC
            LIMIT 5
        ");
        $stmt_top_categorias->bind_param("i", $id_empresa);
        $stmt_top_categorias->execute();
        $top_categorias = $stmt_top_categorias->get_result();
        
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
                            <div class="metric-icon" style="background: #e83e8c;">
                                <i class="fas fa-check-square"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['categorias_activas']); ?></div>
                        <div class="metric-details">Categorías Activas</div>
                        <div class="metric-indicator"><?php echo $porcentaje_activas; ?>% del total</div>
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
                                <div class="chart-metric-value"><?php echo number_format($stats['total_productos']); ?></div>
                                <div class="chart-metric-label">Total Productos</div>
                            </div>
                            <div class="chart-metric">
                                <div class="chart-metric-value"><?php echo $promedio_productos; ?></div>
                                <div class="chart-metric-label">Promedio</div>
                            </div>
                            <div class="chart-metric growth">
                                <div class="chart-metric-value">+12.5%</div>
                                <div class="chart-metric-label">Crecimiento</div>
                            </div>
                        </div>
                        
                        <div style="height: 200px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            Gráfico de Distribución de Productos por Categoría
                        </div>
                    </div>

                    <div class="top-products">
                        <div class="top-products-header">
                            <h3 class="chart-title">Top Categorías</h3>
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
                                <div class="product-name">Electrónicos</div>
                                <div class="product-category">45 productos</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">156</div>
                                <div class="product-revenue">₡3.2M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +18%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-rank">2</div>
                            <div class="product-icon" style="background: #28a745;">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Ropa</div>
                                <div class="product-category">38 productos</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">89</div>
                                <div class="product-revenue">₡1.8M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +12%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-rank">3</div>
                            <div class="product-icon" style="background: #007bff;">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Hogar</div>
                                <div class="product-category">32 productos</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">67</div>
                                <div class="product-revenue">₡1.2M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +8%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories Table Section -->
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="section-title">Lista de Categorías</h2>
                        <button class="btn-primary" onclick="window.location.href='agregar-categoria.php'">
                            <i class="fas fa-plus"></i> Nueva Categoría
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

        // Función para inicializar filtros de categorías
        function initCategoryFilters() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const productFilter = document.getElementById('productFilter');
            const sortFilter = document.getElementById('sortFilter');
            const categoryRows = document.querySelectorAll('.category-row');

            // Función para filtrar categorías
            function filterCategories() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedStatus = statusFilter.value.toLowerCase();
                const selectedProduct = productFilter.value.toLowerCase();
                const selectedSort = sortFilter.value;

                let visibleCategories = [];

                categoryRows.forEach(row => {
                    const nombre = row.dataset.nombre;
                    const descripcion = row.dataset.descripcion;
                    const estado = row.dataset.estado.toLowerCase();
                    const productos = parseInt(row.dataset.productos);

                    // Filtros
                    let showRow = true;

                    // Filtro de búsqueda
                    if (searchTerm && !(
                        nombre.includes(searchTerm) ||
                        descripcion.includes(searchTerm)
                    )) {
                        showRow = false;
                    }

                    // Filtro de estado
                    if (selectedStatus && estado !== selectedStatus) {
                        showRow = false;
                    }

                    // Filtro de productos
                    if (selectedProduct) {
                        if (selectedProduct === 'con_productos' && productos === 0) {
                            showRow = false;
                        } else if (selectedProduct === 'sin_productos' && productos > 0) {
                            showRow = false;
                        }
                    }

                    if (showRow) {
                        row.style.display = '';
                        visibleCategories.push(row);
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Ordenar categorías
                sortCategories(visibleCategories, selectedSort);
            }

            // Función para ordenar categorías
            function sortCategories(categories, sortType) {
                const tbody = document.getElementById('categoriesTableBody');
                
                categories.sort((a, b) => {
                    switch (sortType) {
                        case 'nombre_asc':
                            return a.dataset.nombre.localeCompare(b.dataset.nombre);
                        case 'nombre_desc':
                            return b.dataset.nombre.localeCompare(a.dataset.nombre);
                        case 'fecha_desc':
                            return parseInt(b.dataset.fecha) - parseInt(a.dataset.fecha);
                        case 'fecha_asc':
                            return parseInt(a.dataset.fecha) - parseInt(b.dataset.fecha);
                        case 'productos_desc':
                            return parseInt(b.dataset.productos) - parseInt(a.dataset.productos);
                        case 'productos_asc':
                            return parseInt(a.dataset.productos) - parseInt(b.dataset.productos);
                        default:
                            return 0;
                    }
                });

                // Reordenar en el DOM
                categories.forEach(category => {
                    tbody.appendChild(category);
                });
            }

            // Event listeners
            searchInput.addEventListener('input', filterCategories);
            statusFilter.addEventListener('change', filterCategories);
            productFilter.addEventListener('change', filterCategories);
            sortFilter.addEventListener('change', filterCategories);
        }

        // Funciones de acciones
        function verCategoria(id) {
            window.location.href = `ver-categoria.php?id=${id}`;
        }

        function editarCategoria(id) {
            window.location.href = `editar-categoria.php?id=${id}`;
        }

        function eliminarCategoria(id, nombre) {
            Swal.fire({
                title: '¿Eliminar categoría?',
                text: `¿Estás seguro de que deseas eliminar "${nombre}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear formulario para enviar la solicitud
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../../src/inventario/categorias/eliminar-categoria.php';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'id_categoria';
                    input.value = id;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function checkUrlMessages() {
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            const error = urlParams.get('error');
            const message = urlParams.get('message');

            if (success === '1') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: message || 'Operación completada exitosamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else if (error === '1') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message || 'Ha ocurrido un error'
                });
            }
        }
    </script>
</body>
</html> 