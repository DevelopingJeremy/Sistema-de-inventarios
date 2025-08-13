<?php
require_once '../../config/db.php';

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
$id_venta = (int)($_GET['id'] ?? 0);

if ($id_venta <= 0) {
    header('Location: ventas.php');
    exit;
}

try {
    // Obtener información de la venta
    $sql_venta = "SELECT v.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido 
                  FROM t_ventas v 
                  LEFT JOIN t_clientes c ON v.ID_CLIENTE = c.ID_CLIENTE 
                  WHERE v.ID_VENTA = ? AND v.ID_EMPRESA = ?";
    $stmt_venta = $conn->prepare($sql_venta);
    $stmt_venta->bind_param("ii", $id_venta, $id_empresa);
    $stmt_venta->execute();
    $result_venta = $stmt_venta->get_result();
    $venta = $result_venta->fetch_assoc();

    if (!$venta) {
        header('Location: ventas.php');
        exit;
    }

    // No permitir editar ventas completadas
    if ($venta['estado'] === 'completada') {
        header('Location: ver-venta.php?id=' . $id_venta);
        exit;
    }

    // Obtener detalles de la venta
    $sql_detalle = "SELECT vd.*, p.nombre as producto_nombre, p.precio_venta, p.imagen
                    FROM t_ventas_detalle vd
                    LEFT JOIN t_productos p ON vd.ID_PRODUCTO = p.ID_PRODUCTO
                    WHERE vd.ID_VENTA = ?";
    $stmt_detalle = $conn->prepare($sql_detalle);
    $stmt_detalle->bind_param("i", $id_venta);
    $stmt_detalle->execute();
    $result_detalle = $stmt_detalle->get_result();
    $detalles = [];
    while ($row = $result_detalle->fetch_assoc()) {
        $detalles[] = $row;
    }

    // Obtener clientes para el select
    $sql_clientes = "SELECT ID_CLIENTE, nombre, apellido FROM t_clientes WHERE ID_EMPRESA = ? AND estado = 'activo' ORDER BY nombre, apellido";
    $stmt_clientes = $conn->prepare($sql_clientes);
    $stmt_clientes->bind_param("i", $id_empresa);
    $stmt_clientes->execute();
    $result_clientes = $stmt_clientes->get_result();
    $clientes = [];
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }

} catch (Exception $e) {
    $error = 'Error al cargar los datos de la venta';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Venta - HyBox</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h1><i class="fas fa-edit"></i> Editar Venta</h1>
                    <p>Modificar información de la venta</p>
                </div>
                <div class="page-actions">
                    <a href="ver-venta.php?id=<?= $id_venta ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php else: ?>
                <div class="form-container">
                    <form id="ventaForm" class="form">
                        <input type="hidden" name="id_venta" value="<?= $id_venta ?>">
                        
                        <!-- Información de la Venta -->
                        <div class="form-section">
                            <h3><i class="fas fa-shopping-cart"></i> Información de la Venta</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="numero_venta">Número de Venta *</label>
                                    <input type="text" id="numero_venta" name="numero_venta" value="<?= htmlspecialchars($venta['numero_venta']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="fecha_venta">Fecha de Venta *</label>
                                    <input type="datetime-local" id="fecha_venta" name="fecha_venta" value="<?= date('Y-m-d\TH:i', strtotime($venta['fecha_venta'])) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="id_cliente">Cliente *</label>
                                    <select id="id_cliente" name="id_cliente" required>
                                        <option value="">Seleccionar cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente['ID_CLIENTE'] ?>" <?= $cliente['ID_CLIENTE'] == $venta['ID_CLIENTE'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div class="form-section">
                            <h3><i class="fas fa-box"></i> Productos</h3>
                            <div class="product-selection-container">
                                <div class="product-search">
                                    <input type="text" id="productoBusqueda" placeholder="Buscar productos..." class="form-control">
                                    <button type="button" id="btnAgregarProducto" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Agregar Producto
                                    </button>
                                </div>
                                
                                <div class="table-container">
                                    <table class="products-table" id="productosTable">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Precio Unitario</th>
                                                <th>Cantidad</th>
                                                <th>Descuento</th>
                                                <th>Subtotal</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productosTableBody">
                                            <?php foreach ($detalles as $detalle): ?>
                                                <tr data-producto-id="<?= $detalle['ID_PRODUCTO'] ?>">
                                                    <td>
                                                        <div class="product-info">
                                                            <?php if ($detalle['imagen']): ?>
                                                                <img src="../../uploads/img/fotos-productos/<?= htmlspecialchars($detalle['imagen']) ?>" alt="Producto" class="product-image">
                                                            <?php endif; ?>
                                                            <span><?= htmlspecialchars($detalle['producto_nombre']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                                                    <td>
                                                        <input type="number" class="quantity-input" value="<?= $detalle['cantidad'] ?>" min="1" data-precio="<?= $detalle['precio_unitario'] ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="discount-input" value="<?= $detalle['descuento'] ?>" min="0" step="0.01">
                                                    </td>
                                                    <td class="subtotal-cell">$<?= number_format($detalle['subtotal'], 2) ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-product">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de Venta -->
                        <div class="form-section">
                            <h3><i class="fas fa-calculator"></i> Resumen de Venta</h3>
                            <div class="venta-summary">
                                <div class="summary-grid">
                                    <div class="summary-item">
                                        <label>Subtotal:</label>
                                        <span id="subtotal">$<?= number_format($venta['subtotal'], 2) ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <label>Descuento:</label>
                                        <span id="descuento">$<?= number_format($venta['descuento'], 2) ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <label>IVA (16%):</label>
                                        <span id="iva">$<?= number_format($venta['iva'], 2) ?></span>
                                    </div>
                                    <div class="summary-item total">
                                        <label>Total:</label>
                                        <span id="total">$<?= number_format($venta['total'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Pago -->
                        <div class="form-section">
                            <h3><i class="fas fa-credit-card"></i> Información de Pago</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="metodo_pago">Método de Pago</label>
                                    <select id="metodo_pago" name="metodo_pago">
                                        <option value="efectivo" <?= $venta['metodo_pago'] === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                        <option value="tarjeta" <?= $venta['metodo_pago'] === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                                        <option value="transferencia" <?= $venta['metodo_pago'] === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                        <option value="cheque" <?= $venta['metodo_pago'] === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select id="estado" name="estado">
                                        <option value="pendiente" <?= $venta['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="completada" <?= $venta['estado'] === 'completada' ? 'selected' : '' ?>>Completada</option>
                                        <option value="cancelada" <?= $venta['estado'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="referencia_pago">Referencia de Pago</label>
                                    <input type="text" id="referencia_pago" name="referencia_pago" value="<?= htmlspecialchars($venta['referencia_pago']) ?>">
                                </div>
                                <div class="form-group full-width">
                                    <label for="notas">Notas</label>
                                    <textarea id="notas" name="notas" rows="3"><?= htmlspecialchars($venta['notas']) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="ver-venta.php?id=<?= $id_venta ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Modal de Productos -->
                <div id="productModal" class="product-modal">
                    <div class="product-modal-content">
                        <div class="modal-header">
                            <h3>Seleccionar Producto</h3>
                            <button type="button" class="close-modal" onclick="closeProductModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="text" id="modalProductoBusqueda" placeholder="Buscar productos..." class="form-control">
                            <div class="products-list" id="modalProductsList">
                                <!-- Los productos se cargarán aquí -->
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initVentaForm();
            actualizarTotales();
            
            // Manejar envío del formulario
            document.getElementById('ventaForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const productos = [];
                document.querySelectorAll('#productosTableBody tr').forEach(row => {
                    const productoId = row.dataset.productoId;
                    const cantidad = row.querySelector('.quantity-input').value;
                    const precioUnitario = parseFloat(row.querySelector('.quantity-input').dataset.precio);
                    const descuento = parseFloat(row.querySelector('.discount-input').value) || 0;
                    const subtotal = parseFloat(row.querySelector('.subtotal-cell').textContent.replace('$', '').replace(',', ''));
                    
                    productos.push({
                        id_producto: productoId,
                        cantidad: parseInt(cantidad),
                        precio_unitario: precioUnitario,
                        descuento: descuento,
                        subtotal: subtotal
                    });
                });
                
                const formData = new FormData(this);
                formData.append('productos', JSON.stringify(productos));
                
                fetch('../../src/ventas/actualizar-venta.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessAlert(data.mensaje);
                        setTimeout(() => {
                            window.location.href = 'ver-venta.php?id=<?= $id_venta ?>';
                        }, 1500);
                    } else {
                        showErrorAlert(data.error || 'Error al actualizar la venta');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorAlert('Error de conexión');
                });
            });
        });
    </script>
</body>
</html> 