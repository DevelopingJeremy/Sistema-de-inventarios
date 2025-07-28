<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
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
                        </div>
                        <div class="metric-value-dashboard">45</div>
                        <div class="metric-indicator-dashboard positive">
                            <i class="fas fa-arrow-up"></i>
                            +12.5%
                        </div>
                        <div class="metric-details">vs ₡111,000 ayer • 45 transacciones</div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard" style="background: #3b82f6;">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="metric-value-dashboard">1,250</div>
                        <div class="metric-indicator-dashboard positive">
                            <i class="fas fa-arrow-up"></i>
                            +8.2%
                        </div>
                        <div class="metric-details">+95 este mes • 15 categorías</div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard" style="background: #fd7e14;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="metric-value-dashboard">15</div>
                        <div class="metric-indicator-dashboard negative">
                            <i class="fas fa-arrow-down"></i>
                            -3
                        </div>
                        <div class="metric-details">3 productos críticos • Requieren atención</div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard" style="background: #6f42c1;">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="metric-value-dashboard">342</div>
                        <div class="metric-indicator-dashboard positive">
                            <i class="fas fa-arrow-up"></i>
                            +5.3%
                        </div>
                        <div class="metric-details">+18 este mes • 12 nuevos hoy</div>
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
                            <a href="inventario/productos.php" class="view-all-link">Ver todos →</a>
                        </div>
                        
                        <div class="product-item-dashboard">
                            <div class="product-rank-dashboard">1</div>
                            <div class="product-icon-dashboard" style="background: #3b82f6;">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="product-info-dashboard">
                                <div class="product-name-dashboard">Laptop HP Pavilion</div>
                                <div class="product-category-dashboard">Electrónicos</div>
                            </div>
                            <div class="product-stats-dashboard">
                                <div class="product-sales-dashboard">125 ventas</div>
                                <div class="product-revenue-dashboard">₡2,500,000</div>
                                <div class="product-growth-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    +18%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item-dashboard">
                            <div class="product-rank-dashboard">2</div>
                            <div class="product-icon-dashboard" style="background: #6f42c1;">
                                <i class="fas fa-mouse"></i>
                            </div>
                            <div class="product-info-dashboard">
                                <div class="product-name-dashboard">Mouse Inalámbrico</div>
                                <div class="product-category-dashboard">Accesorios</div>
                            </div>
                            <div class="product-stats-dashboard">
                                <div class="product-sales-dashboard">89 ventas</div>
                                <div class="product-revenue-dashboard">₡445,000</div>
                                <div class="product-growth-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    +12%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item-dashboard">
                            <div class="product-rank-dashboard">3</div>
                            <div class="product-icon-dashboard" style="background: #6f42c1;">
                                <i class="fas fa-keyboard"></i>
                            </div>
                            <div class="product-info-dashboard">
                                <div class="product-name-dashboard">Teclado Mecánico</div>
                                <div class="product-category-dashboard">Accesorios</div>
                            </div>
                            <div class="product-stats-dashboard">
                                <div class="product-sales-dashboard">67 ventas</div>
                                <div class="product-revenue-dashboard">₡1,340,000</div>
                                <div class="product-growth-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    +8%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item-dashboard">
                            <div class="product-rank-dashboard">4</div>
                            <div class="product-icon-dashboard" style="background: #3b82f6;">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <div class="product-info-dashboard">
                                <div class="product-name-dashboard">Monitor 24"</div>
                                <div class="product-category-dashboard">Electrónicos</div>
                            </div>
                            <div class="product-stats-dashboard">
                                <div class="product-sales-dashboard">45 ventas</div>
                                <div class="product-revenue-dashboard">₡1,800,000</div>
                                <div class="product-growth-dashboard negative">
                                    <i class="fas fa-arrow-down"></i>
                                    -3%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item-dashboard">
                            <div class="product-rank-dashboard">5</div>
                            <div class="product-icon-dashboard" style="background: #6f42c1;">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="product-info-dashboard">
                                <div class="product-name-dashboard">Webcam HD</div>
                                <div class="product-category-dashboard">Accesorios</div>
                            </div>
                            <div class="product-stats-dashboard">
                                <div class="product-sales-dashboard">38 ventas</div>
                                <div class="product-revenue-dashboard">₡380,000</div>
                                <div class="product-growth-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    +5%
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="recent-activity">
                        <div class="section-header">
                            <div>
                                <h3 class="section-title">Actividad Reciente</h3>
                                <p class="section-subtitle">Últimas actividades del sistema</p>
                            </div>
                            <a href="#" class="view-all-link">Ver historial →</a>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #28a745;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Nueva venta registrada</div>
                                <div class="activity-details">Laptop HP Pavilion vendida por ₡250,000</div>
                                <div class="activity-meta">María González • Venta #1234</div>
                                <div class="activity-time">Hace 5 min</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #3b82f6;">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Stock actualizado</div>
                                <div class="activity-details">Se agregaron 50 unidades de Mouse Inalámbrico</div>
                                <div class="activity-meta">Carlos Ruiz • Compra #567</div>
                                <div class="activity-time">Hace 15 min</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #fd7e14;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Stock bajo detectado</div>
                                <div class="activity-details">Webcam HD tiene solo 3 unidades en stock</div>
                                <div class="activity-meta">! Requiere atención • Crítico</div>
                                <div class="activity-time">Hace 1 hora</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #6f42c1;">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Nueva compra registrada</div>
                                <div class="activity-details">Compra de 100 teclados mecánicos por ₡2,000,000</div>
                                <div class="activity-meta">Ana Martínez • Compra #568</div>
                                <div class="activity-time">Hace 2 horas</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: #dc3545;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Nuevo cliente registrado</div>
                                <div class="activity-details">Juan Pérez se registró como nuevo cliente</div>
                                <div class="activity-meta">juan.perez@email.com • Cliente #789</div>
                                <div class="activity-time">Hace 3 horas</div>
                            </div>
                        </div>
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
                            <button class="performance-filter active">Semana</button>
                            <button class="performance-filter">Mes</button>
                            <button class="performance-filter">Año</button>
                            <button class="btn-icon">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="performance-metrics">
                        <div class="performance-metric">
                            <div class="performance-value">₡875,000</div>
                            <div class="performance-label">Total Ventas</div>
                        </div>
                        <div class="performance-metric">
                            <div class="performance-value">₡125,000</div>
                            <div class="performance-label">Promedio</div>
                        </div>
                        <div class="performance-metric">
                            <div class="performance-value performance-growth">+15.2%</div>
                            <div class="performance-label">Crecimiento</div>
                        </div>
                    </div>
                    
                    <div style="height: 200px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                        Gráfico de Rendimiento de Ventas
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
                        <div class="quick-action-card" style="--card-color: #28a745;" onclick="window.location.href='ventas/nueva-venta.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="quick-action-title">Nueva Venta</div>
                            <div class="quick-action-description">Registrar una nueva venta</div>
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
                        
                        <div class="quick-action-card" style="--card-color: #fd7e14;" onclick="window.location.href='compras/nueva-compra.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="quick-action-title">Nueva Compra</div>
                            <div class="quick-action-description">Registrar una nueva compra</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                        
                        <div class="quick-action-card" style="--card-color: #6f42c1;" onclick="window.location.href='ventas/clientes.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="quick-action-title">Nuevo Cliente</div>
                            <div class="quick-action-description">Registrar un nuevo cliente</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                        
                        <div class="quick-action-card" style="--card-color: #dc3545;" onclick="window.location.href='reportes/generar-reporte.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="quick-action-title">Generar Reporte</div>
                            <div class="quick-action-description">Crear reportes detallados</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                        
                        <div class="quick-action-card" style="--card-color: #17a2b8;" onclick="window.location.href='inventario/movimientos.php'">
                            <div class="quick-action-icon">
                                <i class="fas fa-warehouse"></i>
                            </div>
                            <div class="quick-action-title">Movimiento de Inventario</div>
                            <div class="quick-action-description">Gestionar movimientos de stock</div>
                            <i class="fas fa-arrow-right quick-action-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades del dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
        });
        
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