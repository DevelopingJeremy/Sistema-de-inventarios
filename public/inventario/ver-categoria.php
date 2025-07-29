<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    $id_usuario = $_SESSION['id_usuario'];
    $id_categoria = $_GET['id'] ?? null;
    
    if (!$id_categoria) {
        header('Location: categorias.php?error=ID de categoría no proporcionado');
        exit;
    }
    
    // Obtener el ID de la empresa del usuario
    $stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_empresa WHERE ID_DUEÑO = ?");
    $stmt_empresa->bind_param("i", $id_usuario);
    $stmt_empresa->execute();
    $empresa_result = $stmt_empresa->get_result()->fetch_assoc();
    $id_empresa = $empresa_result['ID_EMPRESA'];
    
    // Obtener información de la categoría
    $stmt_categoria = $conn->prepare("
        SELECT * FROM t_categorias 
        WHERE ID_CATEGORIA = ? AND ID_EMPRESA = ?
    ");
    $stmt_categoria->bind_param("ii", $id_categoria, $id_empresa);
    $stmt_categoria->execute();
    $result = $stmt_categoria->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: categorias.php?error=Categoría no encontrada');
        exit;
    }
    
    $categoria = $result->fetch_assoc();
    
    // Obtener productos de esta categoría
    $stmt_productos = $conn->prepare("
        SELECT 
            ID_PRODUCTO,
            nombre_producto,
            precio,
            stock,
            estado,
            fecha_creacion
        FROM t_productos 
        WHERE categoria = ? AND ID_EMPRESA = ?
        ORDER BY nombre_producto ASC
        LIMIT 10
    ");
    $stmt_productos->bind_param("si", $categoria['nombre_categoria'], $id_empresa);
    $stmt_productos->execute();
    $productos_result = $stmt_productos->get_result();
    
    // Estadísticas de la categoría
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(*) as total_productos,
            AVG(precio) as precio_promedio,
            SUM(stock) as stock_total,
            COUNT(CASE WHEN stock <= stock_minimo THEN 1 END) as stock_bajo
        FROM t_productos 
        WHERE categoria = ? AND ID_EMPRESA = ?
    ");
    $stmt_stats->bind_param("si", $categoria['nombre_categoria'], $id_empresa);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Categoría - HyBox Cloud</title>
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
                    <h1 class="page-title">Detalles de Categoría</h1>
                    <p class="page-subtitle">Información completa de la categoría</p>
                </div>

                <!-- Información de la categoría -->
                <div class="detail-section">
                    <div class="section-title">Información General</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Nombre:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Descripción:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($categoria['descripcion'] ?: 'Sin descripción'); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Estado:</span>
                        <span class="detail-value">
                            <span class="status-badge <?php echo $stats['total_productos'] > 0 ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $stats['total_productos'] > 0 ? 'Activo' : 'Inactiva'; ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Fecha de creación:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($categoria['fecha_creacion'])); ?></span>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-header">
                            <i class="fas fa-boxes metric-icon"></i>
                            <span class="metric-label">Total Productos</span>
                        </div>
                        <div class="metric-value"><?php echo $stats['total_productos']; ?></div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <i class="fas fa-dollar-sign metric-icon"></i>
                            <span class="metric-label">Precio Promedio</span>
                        </div>
                        <div class="metric-value">$<?php echo number_format($stats['precio_promedio'] ?? 0, 2); ?></div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <i class="fas fa-warehouse metric-icon"></i>
                            <span class="metric-label">Stock Total</span>
                        </div>
                        <div class="metric-value"><?php echo $stats['stock_total'] ?? 0; ?></div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <i class="fas fa-exclamation-triangle metric-icon"></i>
                            <span class="metric-label">Stock Bajo</span>
                        </div>
                        <div class="metric-value"><?php echo $stats['stock_bajo'] ?? 0; ?></div>
                    </div>
                </div>

                <!-- Productos de la categoría -->
                <?php if ($productos_result->num_rows > 0): ?>
                <div class="detail-section">
                    <div class="section-title">Productos en esta categoría</div>
                    
                    <div class="table-container">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($producto = $productos_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td><?php echo $producto['stock']; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $producto['estado'] === 'activo' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo ucfirst($producto['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($stats['total_productos'] > 10): ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <p>Mostrando 10 de <?php echo $stats['total_productos']; ?> productos</p>
                        <a href="productos.php?categoria=<?php echo urlencode($categoria['nombre_categoria']); ?>" class="btn-primary">
                            Ver todos los productos
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="detail-section">
                    <div class="section-title">Productos en esta categoría</div>
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        <div>No hay productos en esta categoría</div>
                        <div style="font-size: 0.9rem; margin-top: 8px;">Los productos aparecerán aquí cuando los agregues</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botones de acción -->
                <div class="btn-container">
                    <button class="btn-secondary" onclick="window.location.href='categorias.php'">
                        <i class="fas fa-arrow-left"></i> Volver
                    </button>
                    <button class="btn-primary" onclick="window.location.href='editar-categoria.php?id=<?php echo $categoria['ID_CATEGORIA']; ?>'">
                        <i class="fas fa-edit"></i> Editar Categoría
                    </button>
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
    </script>
</body>
</html> 