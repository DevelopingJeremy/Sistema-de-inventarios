<?php
require_once('../../config/db.php');

// Verificar sesión simple
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../sesion/iniciar-sesion.php');
    exit;
}

// Obtener el ID de la empresa del usuario
$stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_usuarios WHERE ID_USUARIO = ?");
$stmt_empresa->bind_param("i", $_SESSION['id_usuario']);
$stmt_empresa->execute();
$result_empresa = $stmt_empresa->get_result();

if ($result_empresa->num_rows > 0) {
    $empresa_data = $result_empresa->fetch_assoc();
    $id_empresa = $empresa_data['ID_EMPRESA'];
} else {
    header('Location: ventas.php?error=1&mensaje=' . urlencode('Usuario no tiene empresa registrada'));
    exit;
}
$id_venta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_venta <= 0) {
    header('Location: ventas.php');
    exit;
}

try {
    // Obtener información de la venta
    $stmt_venta = $conn->prepare("
        SELECT 
            v.*,
            c.nombre as nombre_cliente,
            c.apellido as apellido_cliente,
            c.email as email_cliente,
            c.telefono as telefono_cliente,
            c.direccion as direccion_cliente,
            c.tipo_cliente as tipo_cliente
        FROM t_ventas v
        LEFT JOIN t_clientes c ON v.ID_CLIENTE = c.ID_CLIENTE
        WHERE v.ID_VENTA = ? AND v.ID_EMPRESA = ?
    ");
    $stmt_venta->bind_param("ii", $id_venta, $id_empresa);
    $stmt_venta->execute();
    $result_venta = $stmt_venta->get_result();
    
    if ($result_venta->num_rows === 0) {
        header('Location: ventas.php');
        exit;
    }
    
    $venta = $result_venta->fetch_assoc();
    
    // Obtener detalles de la venta
    $stmt_detalles = $conn->prepare("
        SELECT 
            vd.*,
            p.nombre as nombre_producto,
            p.descripcion as descripcion_producto,
            p.precio_venta as precio_unitario,
            p.imagen as imagen_producto
        FROM t_ventas_detalle vd
        LEFT JOIN t_productos p ON vd.ID_PRODUCTO = p.ID_PRODUCTO
        WHERE vd.ID_VENTA = ?
    ");
    $stmt_detalles->bind_param("i", $id_venta);
    $stmt_detalles->execute();
    $detalles = $stmt_detalles->get_result();
    
} catch (Exception $e) {
    header('Location: ventas.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Venta #<?php echo $id_venta; ?> - HYBOX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <?php include('../../includes/sidebar.php'); ?>
        
        <div class="main-content">
            <div class="header">
                <div class="header-left">
                    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="breadcrumb">
                        <a href="../dashboard/dashboard.php">Dashboard</a>
                        <span>/</span>
                        <a href="ventas.php">Ventas</a>
                        <span>/</span>
                        <span>Ver Venta #<?php echo $id_venta; ?></span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <button class="btn-icon" id="darkModeToggle" title="Cambiar tema">
                            <i class="fas fa-moon"></i>
                        </button>
                        <div class="user-dropdown">
                            <span><?php echo $_SESSION['nombre_usuario'] ?></span>
                            <i class="fas fa-chevron-down"></i>
                            <div class="dropdown-menu">
                                <a href="../sesion/cambiar-contraseña.php" class="dropdown-item">
                                    <i class="fas fa-key"></i>
                                    Cambiar contraseña
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="../src/auth/sesion/salir.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Cerrar sesión
                                </a>
                            </div>
                        </div>
                    </div>
                    <a href="editar-venta.php?id=<?php echo $id_venta; ?>" class="btn-primary">
                        <i class="fas fa-edit"></i>
                        Editar Venta
                    </a>
                </div>
            </div>
            
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h1>Venta #<?php echo $id_venta; ?></h1>
                        <span class="status-badge status-<?php echo $venta['estado']; ?>">
                            <?php echo ucfirst($venta['estado']); ?>
                        </span>
                    </div>
                    <div class="page-actions">
                        <button class="btn-secondary" onclick="imprimirVenta()">
                            <i class="fas fa-print"></i>
                            Imprimir
                        </button>
                        <button class="btn-secondary" onclick="descargarPDF()">
                            <i class="fas fa-download"></i>
                            Descargar PDF
                        </button>
                    </div>
                </div>

                <div class="venta-details">
                    <div class="details-grid">
                        <!-- Información del Cliente -->
                        <div class="detail-card">
                            <div class="card-header">
                                <h3><i class="fas fa-user"></i> Información del Cliente</h3>
                            </div>
                            <div class="card-content">
                                <div class="info-row">
                                    <span class="label">Nombre:</span>
                                    <span class="value"><?php echo $venta['nombre_cliente'] . ' ' . $venta['apellido_cliente']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Email:</span>
                                    <span class="value"><?php echo $venta['email_cliente']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Teléfono:</span>
                                    <span class="value"><?php echo $venta['telefono_cliente']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Dirección:</span>
                                    <span class="value"><?php echo $venta['direccion_cliente']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Tipo de Cliente:</span>
                                    <span class="value">
                                        <span class="category-badge category-<?php echo $venta['tipo_cliente']; ?>">
                                            <?php echo ucfirst($venta['tipo_cliente']); ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Información de la Venta -->
                        <div class="detail-card">
                            <div class="card-header">
                                <h3><i class="fas fa-shopping-cart"></i> Información de la Venta</h3>
                            </div>
                            <div class="card-content">
                                <div class="info-row">
                                    <span class="label">Fecha:</span>
                                    <span class="value"><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Método de Pago:</span>
                                    <span class="value"><?php echo ucfirst($venta['metodo_pago']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Referencia:</span>
                                    <span class="value"><?php echo $venta['referencia_pago'] ?: 'N/A'; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Estado:</span>
                                    <span class="value">
                                        <span class="status-badge status-<?php echo $venta['estado']; ?>">
                                            <?php echo ucfirst($venta['estado']); ?>
                                        </span>
                                    </span>
                                </div>
                                <?php if ($venta['notas']): ?>
                                <div class="info-row">
                                    <span class="label">Notas:</span>
                                    <span class="value"><?php echo $venta['notas']; ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Productos de la Venta -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-box"></i> Productos</h3>
                        </div>
                        <div class="card-content">
                            <div class="table-container">
                                <table class="products-table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Precio Unitario</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_venta = 0;
                                        while ($detalle = $detalles->fetch_assoc()): 
                                            $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                                            $total_venta += $subtotal;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <?php if ($detalle['imagen_producto']): ?>
                                                    <img src="../../uploads/img/fotos-productos/<?php echo $detalle['imagen_producto']; ?>" 
                                                         alt="<?php echo $detalle['nombre_producto']; ?>" 
                                                         class="product-image">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="product-name"><?php echo $detalle['nombre_producto']; ?></div>
                                                        <div class="product-description"><?php echo $detalle['descripcion_producto']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                                            <td><?php echo $detalle['cantidad']; ?></td>
                                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Resumen de Totales -->
                            <div class="venta-summary">
                                <div class="summary-grid">
                                    <div class="summary-item">
                                        <span class="label">Subtotal:</span>
                                        <span class="value">$<?php echo number_format($total_venta, 2); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">Descuento:</span>
                                        <span class="value">$<?php echo number_format($venta['descuento'] ?? 0, 2); ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">IVA:</span>
                                        <span class="value">$<?php echo number_format($venta['iva'] ?? 0, 2); ?></span>
                                    </div>
                                    <div class="summary-item total">
                                        <span class="label">Total:</span>
                                        <span class="value">$<?php echo number_format($venta['total_venta'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMobileMenu();
        });

        function imprimirVenta() {
            window.print();
        }

        function descargarPDF() {
            // Aquí se implementaría la descarga del PDF
            console.log('Descargando PDF de la venta...');
        }
    </script>
</body>
</html> 