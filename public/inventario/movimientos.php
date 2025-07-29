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
    
    // Filtros
    $filtro_tipo = $_GET['tipo'] ?? '';
    $filtro_producto = $_GET['producto'] ?? '';
    $filtro_fecha_desde = $_GET['fecha_desde'] ?? '';
    $filtro_fecha_hasta = $_GET['fecha_hasta'] ?? '';
    
    // Construir consulta con filtros
    $where_conditions = ["m.ID_EMPRESA = ?"];
    $params = [$id_empresa];
    $param_types = "i";
    
    if (!empty($filtro_tipo)) {
        $where_conditions[] = "m.tipo_movimiento = ?";
        $params[] = $filtro_tipo;
        $param_types .= "s";
    }
    
    if (!empty($filtro_producto)) {
        $where_conditions[] = "p.nombre_producto LIKE ?";
        $params[] = "%$filtro_producto%";
        $param_types .= "s";
    }
    
    if (!empty($filtro_fecha_desde)) {
        $where_conditions[] = "DATE(m.fecha_movimiento) >= ?";
        $params[] = $filtro_fecha_desde;
        $param_types .= "s";
    }
    
    if (!empty($filtro_fecha_hasta)) {
        $where_conditions[] = "DATE(m.fecha_movimiento) <= ?";
        $params[] = $filtro_fecha_hasta;
        $param_types .= "s";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Obtener movimientos
    $stmt_movimientos = $conn->prepare("
        SELECT 
            m.ID_MOVIMIENTO,
            m.ID_EMPRESA,
            m.ID_PRODUCTO,
            m.ID_USUARIO,
            m.tipo_movimiento,
            m.cantidad,
            m.valor_movimiento,
            m.motivo,
            m.fecha_movimiento,
            m.fecha_creacion,
            p.nombre_producto, 
            p.categoria, 
            u.nombre_completo as nombre_usuario,
            COALESCE(m.precio_unitario, 0) as precio_unitario,
            COALESCE(m.referencia, '') as referencia,
            COALESCE(m.proveedor_cliente, '') as proveedor_cliente,
            COALESCE(m.documento, '') as documento,
            COALESCE(m.observaciones, '') as observaciones
        FROM t_movimientos_inventario m
        LEFT JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
        LEFT JOIN t_usuarios u ON m.ID_USUARIO = u.ID_USUARIO
        WHERE $where_clause
        ORDER BY m.fecha_movimiento DESC
    ");
    
    if (!$stmt_movimientos) {
        // Si hay error, usar consulta simplificada
        $stmt_movimientos = $conn->prepare("
            SELECT 
                m.ID_MOVIMIENTO,
                m.ID_EMPRESA,
                m.ID_PRODUCTO,
                m.ID_USUARIO,
                m.tipo_movimiento,
                m.cantidad,
                m.valor_movimiento,
                m.motivo,
                m.fecha_movimiento,
                m.fecha_creacion,
                p.nombre_producto, 
                p.categoria, 
                u.nombre_completo as nombre_usuario
            FROM t_movimientos_inventario m
            LEFT JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
            LEFT JOIN t_usuarios u ON m.ID_USUARIO = u.ID_USUARIO
            WHERE $where_clause
            ORDER BY m.fecha_movimiento DESC
        ");
        
        if (!$stmt_movimientos) {
            die("Error en la preparación de la consulta de movimientos: " . $conn->error);
        }
    }
    
    $stmt_movimientos->bind_param($param_types, ...$params);
    $stmt_movimientos->execute();
    $movimientos = $stmt_movimientos->get_result();
    
    // Estadísticas
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(*) as total_movimientos,
            SUM(CASE WHEN m.tipo_movimiento = 'entrada' THEN 1 ELSE 0 END) as total_entradas,
            SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN 1 ELSE 0 END) as total_salidas,
            SUM(COALESCE(m.valor_movimiento, 0)) as valor_total
        FROM t_movimientos_inventario m
        LEFT JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
        WHERE $where_clause
    ");
    
    if (!$stmt_stats) {
        die("Error en la preparación de la consulta de estadísticas: " . $conn->error);
    }
    
    $stmt_stats->bind_param($param_types, ...$params);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Inventario - HyBox Cloud</title>
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
                            <h1 class="page-title">Movimientos de Inventario</h1>
                            <p class="page-subtitle">Controla las entradas y salidas de productos</p>
                        </div>
                        <div class="page-header-actions">
                            <button class="btn-primary btn-large" onclick="window.location.href='nuevo-movimiento.php'">
                                <i class="fas fa-plus"></i> Nuevo Movimiento
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Métricas -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #007bff;">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['total_movimientos']); ?></div>
                        <div class="metric-details">Total Movimientos</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #28a745;">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['total_entradas']); ?></div>
                        <div class="metric-details">Entradas</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #dc3545;">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['total_salidas']); ?></div>
                        <div class="metric-details">Salidas</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="metric-value">₡<?php echo number_format($stats['valor_total'] ?? 0); ?></div>
                        <div class="metric-details">Valor Total</div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="section-title">Filtros</h2>
                    </div>

                    <form method="GET" class="filters-section">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="search-input" 
                                   name="producto" 
                                   placeholder="Buscar por producto..." 
                                   value="<?php echo htmlspecialchars($filtro_producto); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <select class="filter-select" name="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="entrada" <?php echo $filtro_tipo === 'entrada' ? 'selected' : ''; ?>>Entrada</option>
                                <option value="salida" <?php echo $filtro_tipo === 'salida' ? 'selected' : ''; ?>>Salida</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <input type="date" 
                                   class="form-input" 
                                   name="fecha_desde" 
                                   placeholder="Desde" 
                                   value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <input type="date" 
                                   class="form-input" 
                                   name="fecha_hasta" 
                                   placeholder="Hasta" 
                                   value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>">
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        
                        <button type="button" class="btn-secondary" onclick="window.location.href='movimientos.php'">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </form>
                </div>

                <!-- Tabla de movimientos -->
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="section-title">Lista de Movimientos</h2>
                        <div class="header-buttons">
                            <button class="btn-secondary" onclick="descargarPDFMovimientos()" title="Descargar PDF de Movimientos">
                                <i class="fas fa-download"></i> Descargar PDF
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="products-table movimientos-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar"></i> Fecha y Hora</th>
                                    <th><i class="fas fa-exchange-alt"></i> Tipo</th>
                                    <th><i class="fas fa-eye"></i> relleno</th>
                                    <th><i class="fas fa-eye"></i> relleno</th>
                                    <th><i class="fas fa-eye"></i> relleno</th>
                                    <th><i class="fas fa-eye"></i> relleno</th>
                                    <th><i class="fas fa-eye"></i> relleno</th>
                                    <th><i class="fas fa-eye"></i> relleno</th>
                                    <th><i class="fas fa-eye"></i> Producto</th>
                                    <th><i class="fas fa-eye"></i> Categoría</th>
                                    <th><i class="fas fa-eye"></i> Cantidad</th>
                                    <th><i class="fas fa-eye"></i> Valor total</th>
                                    <th><i class="fas fa-eye"></i> Precio Unit.</th>
                                    <th><i class="fas fa-hashtag"></i> Referencia</th>
                                    <th><i class="fas fa-eye"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($movimientos->num_rows > 0): ?>
                                    <?php while ($movimiento = $movimientos->fetch_assoc()): ?>
                                        <?php
                                            $tipo_clase = $movimiento['tipo_movimiento'] === 'entrada' ? 'status-active' : 'status-inactive';
                                            $tipo_texto = ucfirst($movimiento['tipo_movimiento']);
                                        ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha_movimiento'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $tipo_clase; ?>">
                                                    <?php echo $tipo_texto; ?>
                                                </span>
                                            </td>
                                            <td title="<?php echo htmlspecialchars($movimiento['nombre_producto'] ?? 'Producto eliminado'); ?>">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 40px; height: 40px; border-radius: 6px; background: #6f42c1; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-box" style="font-size: 16px; color: white;"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($movimiento['nombre_producto'] ?? 'Producto eliminado'); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td title="<?php echo htmlspecialchars($movimiento['categoria'] ?? 'Sin categoría'); ?>"><?php echo htmlspecialchars($movimiento['categoria'] ?? 'Sin categoría'); ?></td>
                                            <td><?php echo number_format($movimiento['cantidad']); ?></td>
                                            <td>₡<?php echo number_format(isset($movimiento['precio_unitario']) ? $movimiento['precio_unitario'] : 0, 2); ?></td>
                                            <td>₡<?php echo number_format($movimiento['valor_movimiento']); ?></td>
                                            <td title="<?php echo htmlspecialchars(isset($movimiento['referencia']) ? $movimiento['referencia'] : '-'); ?>"><?php echo htmlspecialchars(isset($movimiento['referencia']) ? $movimiento['referencia'] : '-'); ?></td>
                                            <td>
                                                <button class="btn-action btn-view" title="Ver detalles" onclick="verMovimiento(<?php echo $movimiento['ID_MOVIMIENTO']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 40px; color: #6c757d;">
                                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                                            <div>No se encontraron movimientos</div>
                                            <div style="font-size: 0.9rem; margin-top: 8px;">Intenta ajustar los filtros o registra un nuevo movimiento</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMobileMenu();
        });

        // Función para ver detalles del movimiento
        function verMovimiento(id) {
            window.location.href = `ver-movimiento.php?id=${id}`;
        }

        // Función para descargar PDF de movimientos
        function descargarPDFMovimientos() {
            // Abrir el PDF directamente en una nueva pestaña
            window.open('../../src/inventario/generar-pdf-movimientos.php', '_blank');
        }
    </script>
</body>
</html> 