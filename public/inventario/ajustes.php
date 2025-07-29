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
    $where_conditions = ["a.ID_EMPRESA = ?"];
    $params = [$id_empresa];
    $param_types = "i";
    
    if (!empty($filtro_tipo)) {
        $where_conditions[] = "a.tipo_ajuste = ?";
        $params[] = $filtro_tipo;
        $param_types .= "s";
    }
    
    if (!empty($filtro_producto)) {
        $where_conditions[] = "p.nombre_producto LIKE ?";
        $params[] = "%$filtro_producto%";
        $param_types .= "s";
    }
    
    if (!empty($filtro_fecha_desde)) {
        $where_conditions[] = "DATE(a.fecha_ajuste) >= ?";
        $params[] = $filtro_fecha_desde;
        $param_types .= "s";
    }
    
    if (!empty($filtro_fecha_hasta)) {
        $where_conditions[] = "DATE(a.fecha_ajuste) <= ?";
        $params[] = $filtro_fecha_hasta;
        $param_types .= "s";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Obtener ajustes de la tabla específica
    $stmt_ajustes = $conn->prepare("
        SELECT a.*, p.nombre_producto, p.categoria, u.nombre_completo as nombre_usuario
        FROM t_ajustes_inventario a
        LEFT JOIN t_productos p ON a.ID_PRODUCTO = p.ID_PRODUCTO
        LEFT JOIN t_usuarios u ON a.ID_USUARIO = u.ID_USUARIO
        WHERE $where_clause 
        ORDER BY a.fecha_ajuste DESC
    ");
    
    if (!$stmt_ajustes) {
        die("Error en la preparación de la consulta de ajustes: " . $conn->error);
    }
    
    $stmt_ajustes->bind_param($param_types, ...$params);
    $stmt_ajustes->execute();
    $ajustes = $stmt_ajustes->get_result();
    
    // Estadísticas de ajustes
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(*) as total_ajustes,
            SUM(CASE WHEN a.tipo_ajuste = 'positivo' THEN 1 ELSE 0 END) as total_entradas,
            SUM(CASE WHEN a.tipo_ajuste = 'negativo' THEN 1 ELSE 0 END) as total_salidas,
            SUM(COALESCE(a.valor_ajuste, 0)) as valor_total
        FROM t_ajustes_inventario a
        LEFT JOIN t_productos p ON a.ID_PRODUCTO = p.ID_PRODUCTO
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
    <title>Ajustes de Inventario - HyBox Cloud</title>
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
                            <h1 class="page-title">Ajustes de Inventario</h1>
                            <p class="page-subtitle">Correcciones e inventarios físicos</p>
                        </div>
                        <div class="page-header-actions">
                            <button class="btn-primary btn-large" onclick="window.location.href='nuevo-ajuste.php'">
                                <i class="fas fa-plus"></i> Nuevo Ajuste
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Métricas -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #fd7e14;">
                                <i class="fas fa-tools"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['total_ajustes']); ?></div>
                        <div class="metric-details">Total Ajustes</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #28a745;">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['total_entradas']); ?></div>
                        <div class="metric-details">Ajustes Positivos</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #dc3545;">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo number_format($stats['total_salidas']); ?></div>
                        <div class="metric-details">Ajustes Negativos</div>
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
                        <h2 class="section-title">Filtros de Búsqueda</h2>
                        <p class="section-subtitle">Encuentra ajustes específicos usando los filtros. La búsqueda se actualiza automáticamente.</p>
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
                                <option value="positivo" <?php echo $filtro_tipo === 'positivo' ? 'selected' : ''; ?>>Ajuste Positivo</option>
                                <option value="negativo" <?php echo $filtro_tipo === 'negativo' ? 'selected' : ''; ?>>Ajuste Negativo</option>
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
                        
                        <button type="button" class="btn-secondary" onclick="window.location.href='ajustes.php'">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </form>
                </div>

                <!-- Tabla de ajustes -->
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="section-title">Lista de Ajustes</h2>
                        <div class="header-buttons">
                            <button class="btn-secondary" onclick="descargarPDFAjustes()" title="Descargar PDF de Ajustes">
                                <i class="fas fa-download"></i> Descargar PDF
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="products-table ajustes-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar"></i> Fecha y Hora</th>
                                    <th><i class="fas fa-tools"></i> Tipo de Ajuste</th>
                                    <th><i class="fas fa-box"></i> Producto</th>
                                    <th><i class="fas fa-tags"></i> Categoría</th>
                                    <th><i class="fas fa-sort-numeric-up"></i> Cantidad Ajustada</th>
                                    <th><i class="fas fa-dollar-sign"></i> Valor del Ajuste</th>
                                    <th><i class="fas fa-comment"></i> Motivo del Ajuste</th>
                                    <th><i class="fas fa-user"></i> Responsable</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($ajustes->num_rows > 0): ?>
                                    <?php while ($ajuste = $ajustes->fetch_assoc()): ?>
                                        <?php
                                            $tipo_clase = $ajuste['tipo_ajuste'] === 'positivo' ? 'status-active' : 'status-inactive';
                                            $tipo_texto = $ajuste['tipo_ajuste'] === 'positivo' ? 'Positivo' : 'Negativo';
                                        ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($ajuste['fecha_ajuste'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $tipo_clase; ?>">
                                                    <?php echo $tipo_texto; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 40px; height: 40px; border-radius: 6px; background: #fd7e14; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-tools" style="font-size: 16px; color: white;"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($ajuste['nombre_producto'] ?? 'Producto eliminado'); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($ajuste['categoria'] ?? 'Sin categoría'); ?></td>
                                            <td><?php echo number_format($ajuste['cantidad_ajustada']); ?></td>
                                            <td>₡<?php echo number_format($ajuste['valor_ajuste']); ?></td>
                                            <td><?php echo htmlspecialchars($ajuste['motivo_ajuste']); ?></td>
                                            <td><?php echo htmlspecialchars($ajuste['nombre_usuario'] ?? 'Sistema'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 40px; color: #6c757d;">
                                            <i class="fas fa-tools" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                                            <div>No se encontraron ajustes</div>
                                            <div style="font-size: 0.9rem; margin-top: 8px;">Los ajustes aparecen cuando registras movimientos con motivo de corrección o inventario físico</div>
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
            
            // Funcionalidad de búsqueda en tiempo real
            const searchInput = document.querySelector('input[name="producto"]');
            const filterSelect = document.querySelector('select[name="tipo"]');
            const dateDesde = document.querySelector('input[name="fecha_desde"]');
            const dateHasta = document.querySelector('input[name="fecha_hasta"]');
            
            // Función para aplicar filtros automáticamente
            function applyFilters() {
                const form = document.querySelector('.filters-section');
                if (form) {
                    form.submit();
                }
            }
            
            // Event listeners para filtros automáticos
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(applyFilters, 500); // Esperar 500ms después de que el usuario deje de escribir
                });
            }
            
            if (filterSelect) {
                filterSelect.addEventListener('change', applyFilters);
            }
            
            if (dateDesde) {
                dateDesde.addEventListener('change', applyFilters);
            }
            
            if (dateHasta) {
                dateHasta.addEventListener('change', applyFilters);
            }
        });

        // Función para descargar PDF de ajustes
        function descargarPDFAjustes() {
            // Abrir el PDF directamente en una nueva pestaña
            window.open('../../src/inventario/generar-pdf-ajustes.php', '_blank');
        }
    </script>
</body>
</html> 