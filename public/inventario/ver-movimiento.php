<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    $id_usuario = $_SESSION['id_usuario'];
    $id_movimiento = $_GET['id'] ?? 0;
    
    if (!$id_movimiento) {
        header('Location: movimientos.php?error=1&message=ID de movimiento no válido');
        exit();
    }
    
    // Obtener el ID de la empresa del usuario
    $stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_empresa WHERE ID_DUEÑO = ?");
    $stmt_empresa->bind_param("i", $id_usuario);
    $stmt_empresa->execute();
    $empresa_result = $stmt_empresa->get_result()->fetch_assoc();
    $id_empresa = $empresa_result['ID_EMPRESA'];
    
    // Obtener detalles del movimiento
    $stmt_movimiento = $conn->prepare("
        SELECT 
            m.*,
            p.nombre_producto, 
            p.categoria,
            p.precio as precio_actual,
            p.stock as stock_actual,
            u.nombre_completo as nombre_usuario,
            COALESCE(m.precio_unitario, 0) as precio_unitario,
            COALESCE(m.referencia, '') as referencia,
            COALESCE(m.proveedor_cliente, '') as proveedor_cliente,
            COALESCE(m.documento, '') as documento,
            COALESCE(m.observaciones, '') as observaciones
        FROM t_movimientos_inventario m
        LEFT JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
        LEFT JOIN t_usuarios u ON m.ID_USUARIO = u.ID_USUARIO
        WHERE m.ID_MOVIMIENTO = ? AND m.ID_EMPRESA = ?
    ");
    
    $stmt_movimiento->bind_param("ii", $id_movimiento, $id_empresa);
    $stmt_movimiento->execute();
    $result = $stmt_movimiento->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: movimientos.php?error=1&message=Movimiento no encontrado');
        exit();
    }
    
    $movimiento = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Movimiento - HyBox Cloud</title>
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
                            <h1 class="page-title">Detalles del Movimiento</h1>
                            <p class="page-subtitle">Información completa del movimiento de inventario</p>
                        </div>
                        <div class="page-header-actions">
                            <button class="btn-secondary btn-large" onclick="window.location.href='movimientos.php'">
                                <i class="fas fa-arrow-left"></i> Volver
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Información del Movimiento -->
                <div class="form-container">
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-exchange-alt"></i> Información General</h3>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">ID del Movimiento</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    #<?php echo $movimiento['ID_MOVIMIENTO']; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Tipo de Movimiento</label>
                                <div class="form-input">
                                    <span class="status-badge <?php echo $movimiento['tipo_movimiento'] === 'entrada' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($movimiento['tipo_movimiento']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Fecha y Hora</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo date('d/m/Y H:i:s', strtotime($movimiento['fecha_movimiento'])); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Responsable</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo htmlspecialchars($movimiento['nombre_usuario'] ?? 'Sistema'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-box"></i> Información del Producto</h3>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Producto</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo htmlspecialchars($movimiento['nombre_producto'] ?? 'Producto eliminado'); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Categoría</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo htmlspecialchars($movimiento['categoria'] ?? 'Sin categoría'); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Cantidad Movida</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo number_format($movimiento['cantidad']); ?> unidades
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Precio Unitario</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    ₡<?php echo number_format(isset($movimiento['precio_unitario']) ? $movimiento['precio_unitario'] : 0, 2); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Valor Total</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    ₡<?php echo number_format($movimiento['valor_movimiento']); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Stock Actual</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo number_format($movimiento['stock_actual'] ?? 0); ?> unidades
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-file-alt"></i> Información Adicional</h3>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Referencia</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo htmlspecialchars(isset($movimiento['referencia']) ? $movimiento['referencia'] : 'Sin referencia'); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Proveedor/Cliente</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo htmlspecialchars(isset($movimiento['proveedor_cliente']) ? $movimiento['proveedor_cliente'] : 'No especificado'); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Documento</label>
                                <div class="form-input" style="background: #f8f9fa; color: #6c757d;">
                                    <?php echo htmlspecialchars(isset($movimiento['documento']) ? $movimiento['documento'] : 'Sin documento'); ?>
                                </div>
                            </div>
                            
                            <div class="form-group full-width">
                                <label class="form-label">Motivo</label>
                                <div class="form-textarea" style="background: #f8f9fa; color: #6c757d; min-height: 60px;">
                                    <?php echo htmlspecialchars($movimiento['motivo']); ?>
                                </div>
                            </div>
                            
                            <div class="form-group full-width">
                                <label class="form-label">Observaciones</label>
                                <div class="form-textarea" style="background: #f8f9fa; color: #6c757d; min-height: 60px;">
                                    <?php echo htmlspecialchars(isset($movimiento['observaciones']) ? $movimiento['observaciones'] : 'Sin observaciones'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-container">
                        <button class="btn-secondary" onclick="window.location.href='movimientos.php'">
                            <i class="fas fa-arrow-left"></i> Volver a Movimientos
                        </button>
                        <button class="btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
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
    </script>
</body>
</html> 