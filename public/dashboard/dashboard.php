<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    // Obtener ID de la empresa del usuario
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
        // Si no hay empresa, redirigir
        header('Location: ../empresa/registrar-empresa.php');
        exit();
    }
    
    // Debug: Verificar que tenemos el ID de empresa correcto
    // echo "ID Usuario: " . $id_usuario . "<br>";
    // echo "ID Empresa: " . $id_empresa . "<br>";
    
    // Consultas mejoradas para obtener datos dinámicos
    try {
        // 1. Estadísticas mejoradas de productos
        $stmt_productos = $conn->prepare("
            SELECT 
                COUNT(*) as total_productos,
                COALESCE(SUM(stock), 0) as stock_total,
                SUM(CASE WHEN stock <= 5 AND stock > 0 THEN 1 ELSE 0 END) as productos_criticos,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as productos_sin_stock,
                COALESCE(SUM(stock * precio), 0) as valor_total_inventario,
                COALESCE(AVG(precio), 0) as precio_promedio,
                COUNT(DISTINCT categoria) as total_categorias
            FROM t_productos 
            WHERE ID_EMPRESA = ?
        ");
        
        // Debug: Verificar si hay productos
        // $debug_productos = $conn->prepare("SELECT COUNT(*) as total FROM t_productos WHERE ID_EMPRESA = ?");
        // $debug_productos->bind_param("i", $id_empresa);
        // $debug_productos->execute();
        // $debug_result = $debug_productos->get_result()->fetch_assoc();
        // echo "Total productos en empresa: " . $debug_result['total'] . "<br>";
        $stmt_productos->bind_param("i", $id_empresa);
        $stmt_productos->execute();
        $stats_productos = $stmt_productos->get_result()->fetch_assoc();
        
        // Debug: Verificar datos de productos
        // echo "Stats Productos: ";
        // print_r($stats_productos);
        // echo "<br>";
        
        // 2. Estadísticas mejoradas de categorías
        $stmt_categorias = $conn->prepare("
            SELECT 
                COUNT(DISTINCT categoria) as total_categorias,
                COUNT(DISTINCT CASE WHEN categoria IS NOT NULL AND categoria != '' THEN categoria END) as categorias_activas
            FROM t_productos 
            WHERE ID_EMPRESA = ? AND categoria IS NOT NULL AND categoria != ''
        ");
        $stmt_categorias->bind_param("i", $id_empresa);
        $stmt_categorias->execute();
        $stats_categorias = $stmt_categorias->get_result()->fetch_assoc();
        
        // Debug: Verificar datos de categorías
        // echo "Stats Categorías: ";
        // print_r($stats_categorias);
        // echo "<br>";
        
        // 3. Movimientos mejorados (últimos 30 días)
        $stmt_movimientos = $conn->prepare("
            SELECT 
                COUNT(*) as total_movimientos,
                SUM(CASE WHEN tipo_movimiento = 'entrada' THEN 1 ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo_movimiento = 'salida' THEN 1 ELSE 0 END) as salidas,
                COALESCE(SUM(valor_movimiento), 0) as valor_total,
                COALESCE(AVG(valor_movimiento), 0) as valor_promedio
            FROM t_movimientos_inventario 
            WHERE ID_EMPRESA = ? 
            AND fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt_movimientos->bind_param("i", $id_empresa);
        $stmt_movimientos->execute();
        $stats_movimientos = $stmt_movimientos->get_result()->fetch_assoc();
        
        // Debug: Verificar datos de movimientos
        // echo "Stats Movimientos: ";
        // print_r($stats_movimientos);
        // echo "<br>";
        
        // 4. Ajustes mejorados (verificar si la tabla existe)
        $stmt_ajustes = $conn->prepare("
            SELECT 
                COUNT(*) as total_ajustes,
                SUM(CASE WHEN tipo_ajuste = 'positivo' THEN 1 ELSE 0 END) as ajustes_positivos,
                SUM(CASE WHEN tipo_ajuste = 'negativo' THEN 1 ELSE 0 END) as ajustes_negativos,
                COALESCE(SUM(valor_ajuste), 0) as valor_total_ajustes
            FROM t_ajustes_inventario 
            WHERE ID_EMPRESA = ? 
            AND fecha_ajuste >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt_ajustes->bind_param("i", $id_empresa);
        $stmt_ajustes->execute();
        $stats_ajustes = $stmt_ajustes->get_result()->fetch_assoc();
        
        // Debug: Verificar datos de ajustes
        // echo "Stats Ajustes: ";
        // print_r($stats_ajustes);
        // echo "<br>";
        
        // 5. Productos más vendidos mejorados (por movimientos de salida)
        $stmt_top_productos = $conn->prepare("
            SELECT 
                p.nombre_producto,
                p.categoria,
                p.precio,
                COUNT(m.ID_MOVIMIENTO) as total_movimientos,
                SUM(m.cantidad) as cantidad_total,
                SUM(m.valor_movimiento) as valor_total,
                AVG(m.valor_movimiento) as valor_promedio
            FROM t_productos p
            LEFT JOIN t_movimientos_inventario m ON p.ID_PRODUCTO = m.ID_PRODUCTO 
                AND m.tipo_movimiento = 'salida'
                AND m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            WHERE p.ID_EMPRESA = ?
            GROUP BY p.ID_PRODUCTO, p.nombre_producto, p.categoria, p.precio
            HAVING COUNT(m.ID_MOVIMIENTO) > 0
            ORDER BY COUNT(m.ID_MOVIMIENTO) DESC, SUM(m.cantidad) DESC
            LIMIT 5
        ");
        $stmt_top_productos->bind_param("i", $id_empresa);
        $stmt_top_productos->execute();
        $top_productos = $stmt_top_productos->get_result();
        
        // 6. Actividad reciente simplificada (solo movimientos y ajustes)
        $stmt_actividad = $conn->prepare("
            (SELECT 
                'movimiento' as tipo,
                m.fecha_movimiento as fecha,
                'movimiento' as tipo_entidad,
                m.tipo_movimiento as accion,
                m.cantidad,
                m.valor_movimiento,
                CONCAT('Movimiento de ', m.tipo_movimiento, ': ', p.nombre_producto) as descripcion,
                p.nombre_producto as nombre_entidad,
                p.categoria,
                u.nombre_completo as usuario,
                m.ID_MOVIMIENTO as id_registro
            FROM t_movimientos_inventario m
            JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
            LEFT JOIN t_usuarios u ON m.ID_USUARIO = u.ID_USUARIO
            WHERE m.ID_EMPRESA = ?)
            UNION ALL
            (SELECT 
                'ajuste' as tipo,
                a.fecha_ajuste as fecha,
                'ajuste' as tipo_entidad,
                a.tipo_ajuste as accion,
                a.cantidad_ajustada as cantidad,
                a.valor_ajuste as valor_movimiento,
                CONCAT('Ajuste ', a.tipo_ajuste, ': ', p.nombre_producto) as descripcion,
                p.nombre_producto as nombre_entidad,
                p.categoria,
                u.nombre_completo as usuario,
                a.ID_AJUSTE as id_registro
            FROM t_ajustes_inventario a
            JOIN t_productos p ON a.ID_PRODUCTO = p.ID_PRODUCTO
            LEFT JOIN t_usuarios u ON a.ID_USUARIO = u.ID_USUARIO
            WHERE a.ID_EMPRESA = ?)
            ORDER BY fecha DESC
            LIMIT 5
        ");
        $stmt_actividad->bind_param("ii", $id_empresa, $id_empresa);
        $stmt_actividad->execute();
        $actividad_reciente = $stmt_actividad->get_result();
        

        
        // 8. Datos para el gráfico de ventas (últimos 7 días)
        $stmt_ventas_grafico = $conn->prepare("
            SELECT 
                DATE(fecha_movimiento) as fecha,
                SUM(CASE WHEN tipo_movimiento = 'salida' THEN valor_movimiento ELSE 0 END) as ventas,
                SUM(CASE WHEN tipo_movimiento = 'entrada' THEN valor_movimiento ELSE 0 END) as compras,
                COUNT(CASE WHEN tipo_movimiento = 'salida' THEN 1 END) as num_ventas,
                COUNT(CASE WHEN tipo_movimiento = 'entrada' THEN 1 END) as num_compras
            FROM t_movimientos_inventario 
            WHERE ID_EMPRESA = ? 
            AND fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(fecha_movimiento)
            ORDER BY fecha ASC
        ");
        $stmt_ventas_grafico->bind_param("i", $id_empresa);
        $stmt_ventas_grafico->execute();
        $datos_grafico = $stmt_ventas_grafico->get_result();
        
        // Preparar datos para el gráfico
        $labels_grafico = [];
        $ventas_grafico = [];
        $compras_grafico = [];
        
        while ($row = $datos_grafico->fetch_assoc()) {
            $labels_grafico[] = date('d/m', strtotime($row['fecha']));
            $ventas_grafico[] = $row['ventas'];
            $compras_grafico[] = $row['compras'];
        }
        
    } catch (Exception $e) {
        // Debug: Mostrar el error
        // echo "Error en consultas: " . $e->getMessage() . "<br>";
        
        // En caso de error, usar valores por defecto
        $stats_productos = [
            'total_productos' => 0,
            'stock_total' => 0,
            'productos_criticos' => 0,
            'productos_sin_stock' => 0,
            'valor_total_inventario' => 0,
            'precio_promedio' => 0,
            'total_categorias' => 0
        ];
        $stats_categorias = ['total_categorias' => 0, 'categorias_activas' => 0];
        $stats_movimientos = [
            'total_movimientos' => 0,
            'entradas' => 0,
            'salidas' => 0,
            'valor_total' => 0,
            'valor_promedio' => 0
        ];
        $stats_ajustes = [
            'total_ajustes' => 0,
            'ajustes_positivos' => 0,
            'ajustes_negativos' => 0,
            'valor_total_ajustes' => 0
        ];
        $top_productos = null;
        $actividad_reciente = null;
        $labels_grafico = [];
        $ventas_grafico = [];
        $compras_grafico = [];
    }
    
    // Mensajes desactivados de manera predeterminada
    $error = false;
    $exito = false;

    // Activar PopUps
    if (isset($_SESSION['mensaje_exito'])) {
        $exito = true;
        $mensajeExito = $_SESSION['mensaje_exito'];
        unset($_SESSION['mensaje_exito']);
    }

    if (isset($_SESSION['mensaje_error'])) {
        $error = true;
        $mensajeError = $_SESSION['mensaje_error'];
        unset($_SESSION['mensaje_error']);
    }
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Hybox</title>
    <!-- CDN Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../../includes/header.php'; ?>
            
            <div class="content">
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <div class="welcome-content">
                        <h1 class="welcome-title">¡Bienvenido de vuelta, <?php echo $_SESSION['nombre_usuario'] ?>! 👋</h1>
                        <p class="welcome-subtitle">Aquí tienes un resumen completo de tu negocio hoy</p>
                        <div class="welcome-date">
                            <i class="fas fa-calendar"></i>
                            <span>Hoy es <?php echo date('d/m/Y'); ?></span>
                            <i class="fas fa-clock"></i>
                            <span><?php date_default_timezone_set('America/Costa_Rica'); echo date('H:i') . " - UTC-6"; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Metrics -->
                <div class="dashboard-metrics">
                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard" style="background: #28a745;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="metric-title-dashboard">Movimientos</div>
                        </div>
                        <div class="metric-value-dashboard"><?php echo number_format($stats_movimientos['total_movimientos']); ?></div>
                        <div class="metric-indicator-dashboard <?php echo $stats_movimientos['total_movimientos'] > 0 ? 'positive' : 'neutral'; ?>">
                            <i class="fas fa-arrow-up"></i>
                            <?php echo $stats_movimientos['salidas']; ?> salidas
                        </div>
                        <div class="metric-details">₡<?php echo number_format($stats_movimientos['valor_total']); ?> • <?php echo $stats_movimientos['entradas']; ?> entradas</div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard" style="background: #3b82f6;">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="metric-title-dashboard">Inventario</div>
                        </div>
                        <div class="metric-value-dashboard"><?php echo number_format($stats_productos['stock_total']); ?></div>
                        <div class="metric-indicator-dashboard positive">
                            <i class="fas fa-arrow-up"></i>
                            <?php echo $stats_productos['total_productos']; ?> productos
                        </div>
                        <div class="metric-details">₡<?php echo number_format($stats_productos['valor_total_inventario']); ?> • <?php echo $stats_categorias['categorias_activas']; ?> categorías</div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard" style="background: #fd7e14;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="metric-title-dashboard">Alertas</div>
                        </div>
                        <div class="metric-value-dashboard"><?php echo number_format($stats_productos['productos_criticos']); ?></div>
                        <div class="metric-indicator-dashboard <?php echo $stats_productos['productos_criticos'] > 0 ? 'negative' : 'positive'; ?>">
                            <i class="fas fa-<?php echo $stats_productos['productos_criticos'] > 0 ? 'arrow-down' : 'check'; ?>"></i>
                            <?php echo $stats_productos['productos_criticos']; ?> críticos
                        </div>
                        <div class="metric-details"><?php echo $stats_productos['productos_sin_stock']; ?> sin stock • Requieren atención</div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard" style="background: #6f42c1;">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="metric-title-dashboard">Ajustes</div>
                        </div>
                        <div class="metric-value-dashboard"><?php echo number_format($stats_ajustes['total_ajustes']); ?></div>
                        <div class="metric-indicator-dashboard <?php echo $stats_ajustes['total_ajustes'] > 0 ? 'positive' : 'neutral'; ?>">
                            <i class="fas fa-arrow-up"></i>
                            <?php echo $stats_ajustes['ajustes_positivos']; ?> positivos
                        </div>
                        <div class="metric-details">₡<?php echo number_format($stats_ajustes['valor_total_ajustes']); ?> • <?php echo $stats_ajustes['ajustes_negativos']; ?> negativos</div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard" style="background: #e83e8c;">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="metric-title-dashboard">Ventas</div>
                        </div>
                        <div class="metric-value-dashboard"><?php echo number_format($stats_movimientos['salidas']); ?></div>
                        <div class="metric-indicator-dashboard positive">
                            <i class="fas fa-arrow-up"></i>
                            Productos vendidos
                        </div>
                        <div class="metric-details">₡<?php echo number_format($stats_movimientos['valor_total']); ?> • <?php echo $stats_movimientos['total_movimientos']; ?> transacciones</div>
                    </div>


                </div>

                <!-- Dashboard Content -->
                <div class="dashboard-content">
                    <!-- Top Selling Products -->
                    <div class="top-selling-products">
                        <div class="section-header">
                            <div>
                                <h3 class="section-title">Productos Más Vendidos</h3>
                                <p class="section-subtitle">Top 5 productos con mejor rendimiento</p>
                            </div>
                            <a href="../inventario/productos.php" class="view-all-link">Ver todos →</a>
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
                                        <div class="product-sales-dashboard"><?php echo number_format($producto['total_movimientos']); ?> movimientos</div>
                                        <div class="product-revenue-dashboard">₡<?php echo number_format($producto['valor_total']); ?></div>
                                        <div class="product-growth-dashboard positive">
                                            <i class="fas fa-arrow-up"></i>
                                            <?php echo number_format($producto['cantidad_total']); ?> unidades
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



                    <!-- Recent Activity -->
                    <div class="recent-activity">
                        <div class="section-header">
                            <div>
                                <h3 class="section-title">Actividad Reciente</h3>
                                <p class="section-subtitle">Últimas actividades del sistema</p>
                            </div>
                            <a href="../inventario/movimientos.php" class="view-all-link">Ver historial →</a>
                        </div>
                        
                        <?php if ($actividad_reciente && $actividad_reciente->num_rows > 0): ?>
                            <?php while ($actividad = $actividad_reciente->fetch_assoc()): ?>
                                <?php
                                    $tipo_actividad = $actividad['tipo'];
                                    $tipo_entidad = $actividad['tipo_entidad'];
                                    $accion = $actividad['accion'];
                                    $icono = '';
                                    $color = '';
                                    $titulo = '';
                                    
                                    // Determinar icono y color según el tipo de actividad
                                    switch ($tipo_actividad) {
                                        case 'producto_creado':
                                            $icono = 'fas fa-box';
                                            $color = '#28a745';
                                            $titulo = 'Producto Creado';
                                            break;
                                        case 'producto_editado':
                                            $icono = 'fas fa-edit';
                                            $color = '#17a2b8';
                                            $titulo = 'Producto Editado';
                                            break;
                                        case 'categoria_creada':
                                            $icono = 'fas fa-tags';
                                            $color = '#6f42c1';
                                            $titulo = 'Categoría Creada';
                                            break;
                                        case 'producto_eliminado':
                                            $icono = 'fas fa-trash';
                                            $color = '#dc3545';
                                            $titulo = 'Producto Eliminado';
                                            break;
                                        case 'movimiento':
                                            if ($accion === 'entrada') {
                                                $icono = 'fas fa-arrow-down';
                                                $color = '#28a745';
                                                $titulo = 'Entrada de Inventario';
                                            } else {
                                                $icono = 'fas fa-arrow-up';
                                                $color = '#dc3545';
                                                $titulo = 'Salida de Inventario';
                                            }
                                            break;
                                        case 'ajuste':
                                            if ($accion === 'positivo') {
                                                $icono = 'fas fa-tools';
                                                $color = '#6f42c1';
                                                $titulo = 'Ajuste Positivo';
                                            } else {
                                                $icono = 'fas fa-tools';
                                                $color = '#fd7e14';
                                                $titulo = 'Ajuste Negativo';
                                            }
                                            break;
                                        default:
                                            $icono = 'fas fa-info-circle';
                                            $color = '#6c757d';
                                            $titulo = 'Actividad';
                                    }
                                    
                                    // Calcular tiempo transcurrido
                                    $tiempo_transcurrido = time() - strtotime($actividad['fecha']);
                                    if ($tiempo_transcurrido < 3600) {
                                        $tiempo = round($tiempo_transcurrido / 60) . ' min';
                                    } elseif ($tiempo_transcurrido < 86400) {
                                        $tiempo = round($tiempo_transcurrido / 3600) . ' hora' . (round($tiempo_transcurrido / 3600) > 1 ? 's' : '');
                                    } else {
                                        $tiempo = round($tiempo_transcurrido / 86400) . ' día' . (round($tiempo_transcurrido / 86400) > 1 ? 's' : '');
                                    }
                                    
                                    // Preparar detalles según el tipo de actividad
                                    $detalles = '';
                                    $meta = '';
                                    
                                    if ($tipo_actividad === 'producto_creado' || $tipo_actividad === 'producto_editado') {
                                        $detalles = htmlspecialchars($actividad['nombre_entidad']);
                                        $meta = htmlspecialchars($actividad['categoria']) . ' • ' . htmlspecialchars($actividad['usuario'] ?? 'Sistema');
                                    } elseif ($tipo_actividad === 'categoria_creada') {
                                        $detalles = htmlspecialchars($actividad['nombre_entidad']);
                                        $meta = htmlspecialchars($actividad['usuario'] ?? 'Sistema');
                                    } elseif ($tipo_actividad === 'producto_eliminado') {
                                        $detalles = htmlspecialchars($actividad['nombre_entidad']);
                                        $meta = htmlspecialchars($actividad['categoria']) . ' • ' . htmlspecialchars($actividad['usuario'] ?? 'Sistema');
                                    } elseif ($tipo_actividad === 'movimiento') {
                                        $detalles = htmlspecialchars($actividad['nombre_entidad']) . ' - ' . number_format($actividad['cantidad']) . ' unidades';
                                        if ($actividad['valor_movimiento'] > 0) {
                                            $detalles .= ' por ₡' . number_format($actividad['valor_movimiento']);
                                        }
                                        $meta = htmlspecialchars($actividad['categoria']) . ' • ' . htmlspecialchars($actividad['usuario'] ?? 'Sistema');
                                    } elseif ($tipo_actividad === 'ajuste') {
                                        $detalles = htmlspecialchars($actividad['nombre_entidad']) . ' - ' . number_format($actividad['cantidad']) . ' unidades';
                                        if ($actividad['valor_movimiento'] > 0) {
                                            $detalles .= ' por ₡' . number_format($actividad['valor_movimiento']);
                                        }
                                        $meta = htmlspecialchars($actividad['categoria']) . ' • ' . htmlspecialchars($actividad['usuario'] ?? 'Sistema');
                                    }
                                ?>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: <?php echo $color; ?>;">
                                        <i class="<?php echo $icono; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?php echo $titulo; ?></div>
                                        <div class="activity-details"><?php echo $detalles; ?></div>
                                        <div class="activity-meta"><?php echo $meta; ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <div class="activity-content" style="text-align: center; width: 100%;">
                                    <div class="activity-title">No hay actividad reciente</div>
                                    <div class="activity-details">La actividad aparecerá cuando registres movimientos o ajustes</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sales Performance -->
                <div class="sales-performance">
                    <div class="performance-header">
                        <div>
                            <h3 class="section-title">Rendimiento de Ventas</h3>
                            <p class="section-subtitle">Análisis de ventas por período</p>
                        </div>
                        <div class="performance-filters">
                            <button class="performance-filter active" data-period="7">Semana</button>
                            <button class="performance-filter" data-period="30">Mes</button>
                            <button class="performance-filter" data-period="90">Trimestre</button>
                            <button class="btn-icon">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="performance-metrics">
                        <div class="performance-metric">
                            <div class="performance-value">₡<?php echo number_format($stats_movimientos['valor_total']); ?></div>
                            <div class="performance-label">Total Movimientos</div>
                        </div>
                        <div class="performance-metric">
                            <div class="performance-value"><?php echo number_format($stats_movimientos['total_movimientos']); ?></div>
                            <div class="performance-label">Total Transacciones</div>
                        </div>
                        <div class="performance-metric">
                            <div class="performance-value performance-growth"><?php echo $stats_movimientos['salidas']; ?> salidas</div>
                            <div class="performance-label">Movimientos de Salida</div>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <canvas id="salesChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <div class="section-header">
                        <div>
                            <h3 class="section-title">Acciones Rápidas</h3>
                            <p class="section-subtitle">Accede rápidamente a las funciones más utilizadas</p>
                        </div>
                    </div>
                    
                    <div class="quick-actions-grid">
                        <div class="quick-action-card" style="--card-color: #28a745;" onclick="window.location.href='../inventario/nuevo-movimiento.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="quick-action-title">Nuevo Movimiento</div>
                            <div class="quick-action-description">Registrar entrada o salida de inventario</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                        
                        <div class="quick-action-card" style="--card-color: #3b82f6;" onclick="window.location.href='../inventario/agregar-producto.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="quick-action-title">Agregar Producto</div>
                            <div class="quick-action-description">Registrar un nuevo producto</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                        
                        <div class="quick-action-card" style="--card-color: #fd7e14;" onclick="window.location.href='../inventario/nuevo-ajuste.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="quick-action-title">Nuevo Ajuste</div>
                            <div class="quick-action-description">Ajustar stock de productos</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                        
                        <div class="quick-action-card" style="--card-color: #6f42c1;" onclick="window.location.href='../inventario/agregar-categoria.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="quick-action-title">Nueva Categoría</div>
                            <div class="quick-action-description">Crear nueva categoría de productos</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                        
                        <div class="quick-action-card" style="--card-color: #dc3545;" onclick="window.location.href='../inventario/movimientos.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="quick-action-title">Ver Movimientos</div>
                            <div class="quick-action-description">Revisar historial de movimientos</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                        
                        <div class="quick-action-card" style="--card-color: #17a2b8;" onclick="window.location.href='../inventario/ajustes.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="quick-action-title">Ver Ajustes</div>
                            <div class="quick-action-description">Revisar historial de ajustes</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Datos para el gráfico
        const chartData = {
            labels: <?php echo json_encode($labels_grafico); ?>,
            ventas: <?php echo json_encode($ventas_grafico); ?>,
            compras: <?php echo json_encode($compras_grafico); ?>
        };

        // Inicializar funcionalidades del dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initSalesChart();
            initPerformanceFilters();
        });
        
        // Función para inicializar el gráfico de ventas
        function initSalesChart() {
            const ctx = document.getElementById('salesChart');
            if (!ctx) {
                console.error('Canvas salesChart no encontrado');
                return;
            }
            
            const chartData = {
                labels: <?php echo json_encode($labels_grafico); ?>,
                ventas: <?php echo json_encode($ventas_grafico); ?>,
                compras: <?php echo json_encode($compras_grafico); ?>
            };
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Ventas',
                            data: chartData.ventas,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Compras',
                            data: chartData.compras,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₡' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Función para inicializar filtros de rendimiento
        function initPerformanceFilters() {
            const filters = document.querySelectorAll('.performance-filter');
            filters.forEach(filter => {
                filter.addEventListener('click', function() {
                    // Remover clase active de todos los filtros
                    filters.forEach(f => f.classList.remove('active'));
                    // Agregar clase active al filtro clickeado
                    this.classList.add('active');
                    
                    // Aquí puedes agregar lógica para cambiar los datos del gráfico
                    // según el período seleccionado
                    const period = this.dataset.period;
                    console.log('Período seleccionado:', period);
                });
            });
        }
        
        <?php if($exito): ?>
        // Mostrar mensaje de éxito
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                position: "center",
                icon: "success",
                title: "<?php echo $mensajeExito ?>",
                showConfirmButton: false,
                timer: 1500
            });
        });
        <?php endif; ?>

        <?php if ($error): ?>
        // Mostrar mensaje de error
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "<?php echo $mensajeError ?>"
            });
        });
        <?php endif; ?>
    </script>
</body>

</html>