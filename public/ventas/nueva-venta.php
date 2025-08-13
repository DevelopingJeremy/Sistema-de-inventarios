<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    // Obtener el ID de la empresa del usuario
    $id_usuario = $_SESSION['id_usuario'];
    
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Venta - HYBOX</title>
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
                        <span>Nueva Venta</span>
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
                </div>
            </div>
            
            <div class="content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Crear Nueva Venta</h1>
                            <p class="page-subtitle">Selecciona el cliente y los productos para crear la venta</p>
                        </div>
                    </div>
                </div>

                <form class="form-container" id="ventaForm" method="POST">
                    <input type="hidden" name="id_empresa" value="<?php echo $id_empresa; ?>">
                    
                    <!-- Información del Cliente -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-user"></i> Información del Cliente</h3>
                        </div>
                        <div class="form-section-description">
                            <p>Selecciona el cliente para esta venta</p>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="cliente_id" class="form-label">Cliente <span class="required">*</span></label>
                                <select id="cliente_id" name="id_cliente" class="form-select" required>
                                    <option value="">Selecciona un cliente</option>
                                    <option value="nuevo">+ Agregar nuevo cliente</option>
                                </select>
                                <div class="help-text">Selecciona el cliente o crea uno nuevo</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="fecha_venta" class="form-label">Fecha de Venta <span class="required">*</span></label>
                                <input type="datetime-local" id="fecha_venta" name="fecha_venta" class="form-input" required>
                                <div class="help-text">Fecha y hora de la venta</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="numero_venta" class="form-label">Número de Venta <span class="required">*</span></label>
                                <input type="text" id="numero_venta" name="numero_venta" class="form-input" required>
                                <div class="help-text">Número único de la venta</div>
                            </div>
                        </div>
                    </div>

                    <!-- Selección de Productos -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-shopping-cart"></i> Productos de la Venta</h3>
                        </div>
                        <div class="form-section-description">
                            <p>Agrega los productos que se van a vender</p>
                        </div>
                        
                        <div class="product-selection-container">
                            <div class="product-search">
                                <div class="search-box">
                                    <input type="text" id="productSearch" class="search-input" placeholder="Buscar productos...">
                                    <i class="fas fa-search search-icon"></i>
                                </div>
                                <button type="button" class="btn-primary" onclick="agregarProducto()">
                                    <i class="fas fa-plus"></i>
                                    Agregar Producto
                                </button>
                            </div>
                            
                            <div class="products-table-container">
                                <table class="products-table venta-products-table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Precio Unitario</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productosVentaBody">
                                        <!-- Los productos se agregarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de la Venta -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-calculator"></i> Resumen de la Venta</h3>
                        </div>
                        <div class="form-section-description">
                            <p>Detalles finales y totales de la venta</p>
                        </div>
                        
                        <div class="venta-summary">
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <label>Subtotal:</label>
                                    <span id="subtotal">$0.00</span>
                                </div>
                                <div class="summary-item">
                                    <label>Descuento:</label>
                                    <span id="descuento">$0.00</span>
                                </div>
                                <div class="summary-item">
                                    <label>IVA (16%):</label>
                                    <span id="iva">$0.00</span>
                                </div>
                                <div class="summary-item total">
                                    <label>Total:</label>
                                    <span id="total">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Pago -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-credit-card"></i> Información de Pago</h3>
                        </div>
                        <div class="form-section-description">
                            <p>Método de pago y detalles de la transacción</p>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="metodo_pago" class="form-label">Método de Pago <span class="required">*</span></label>
                                <select id="metodo_pago" name="metodo_pago" class="form-select" required>
                                    <option value="">Selecciona método de pago</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                    <option value="digital">Pago Digital</option>
                                </select>
                                <div class="help-text">Método de pago utilizado</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="estado_venta" class="form-label">Estado de la Venta <span class="required">*</span></label>
                                <select id="estado_venta" name="estado" class="form-select" required>
                                    <option value="completada">Completada</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                                <div class="help-text">Estado actual de la venta</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="referencia_pago" class="form-label">Referencia de Pago</label>
                                <input type="text" id="referencia_pago" name="referencia_pago" class="form-input">
                                <div class="help-text">Número de referencia o comprobante</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notas_venta" class="form-label">Notas de la Venta</label>
                                <textarea id="notas_venta" name="notas" class="form-textarea" rows="3" placeholder="Notas adicionales sobre la venta..."></textarea>
                                <div class="help-text">Información adicional sobre la venta</div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="btn-container">
                        <button type="button" class="btn-secondary" onclick="window.location.href='ventas.php'">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary" id="btnGuardarVenta">
                            <i class="fas fa-save"></i>
                            Guardar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar producto -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Seleccionar Producto</h3>
                <button class="modal-close" onclick="closeProductModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="search-box">
                    <input type="text" id="modalProductSearch" class="search-input" placeholder="Buscar productos...">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div class="products-list" id="modalProductsList">
                    <!-- Lista de productos -->
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        let productosVenta = [];
        let productosDisponibles = [];

        // Inicializar funcionalidades
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMobileMenu();
            initVentaForm();
            cargarClientes();
            cargarProductos();
            setFechaActual();
        });

        function initVentaForm() {
            const form = document.getElementById('ventaForm');
            const clienteSelect = document.getElementById('cliente_id');
            const fechaVenta = document.getElementById('fecha_venta');

            // Manejar selección de nuevo cliente
            clienteSelect.addEventListener('change', function() {
                if (this.value === 'nuevo') {
                    window.open('nuevo-cliente.php', '_blank');
                    this.value = '';
                }
            });

            // Validación del formulario
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (productosVenta.length === 0) {
                    showErrorAlert('Debes agregar al menos un producto a la venta');
                    return;
                }

                if (!clienteSelect.value) {
                    showErrorAlert('Debes seleccionar un cliente');
                    return;
                }

                // Preparar datos para envío
                const formData = new FormData(form);
                
                // Agregar productos al formData
                formData.append('productos', JSON.stringify(productosVenta));
                
                // Agregar totales
                const subtotal = productosVenta.reduce((sum, producto) => sum + producto.subtotal, 0);
                const descuento = 0;
                const iva = subtotal * 0.16;
                const total = subtotal - descuento + iva;
                
                formData.append('subtotal', subtotal);
                formData.append('descuento', descuento);
                formData.append('iva', iva);
                formData.append('total', total);

                // Enviar formulario via AJAX
                fetch('../../src/ventas/crear-venta.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessAlert('Venta creada exitosamente');
                        setTimeout(() => {
                            window.location.href = 'ventas.php';
                        }, 2000);
                    } else {
                        showErrorAlert(data.error || 'Error al crear la venta');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorAlert('Error al procesar la venta');
                });
            });
        }

        function setFechaActual() {
            const now = new Date();
            const fechaVenta = document.getElementById('fecha_venta');
            const fechaString = now.toISOString().slice(0, 16);
            fechaVenta.value = fechaString;
            
            // Generar número de venta automáticamente
            const numeroVenta = document.getElementById('numero_venta');
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hour = String(now.getHours()).padStart(2, '0');
            const minute = String(now.getMinutes()).padStart(2, '0');
            const second = String(now.getSeconds()).padStart(2, '0');
            
            numeroVenta.value = `V-${year}${month}${day}-${hour}${minute}${second}`;
        }

        function cargarClientes() {
            const clienteSelect = document.getElementById('cliente_id');
            
            // Limpiar opciones existentes excepto la primera
            while (clienteSelect.children.length > 2) {
                clienteSelect.removeChild(clienteSelect.lastChild);
            }
            
            fetch('../../src/ventas/obtener-clientes-select.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.clientes.forEach(cliente => {
                            const option = document.createElement('option');
                            option.value = cliente.id;
                            option.textContent = `${cliente.nombre} ${cliente.apellido}`;
                            clienteSelect.appendChild(option);
                        });
                    } else {
                        console.error('Error al cargar clientes:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function cargarProductos() {
            fetch('../../src/inventario/obtener-productos.php?estado=activo')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        productosDisponibles = data.productos.map(producto => ({
                            id: producto.id,
                            nombre: producto.nombre,
                            precio: parseFloat(producto.precio),
                            stock: parseInt(producto.stock),
                            categoria: producto.categoria
                        }));
                    } else {
                        console.error('Error al cargar productos:', data.error);
                        productosDisponibles = [];
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    productosDisponibles = [];
                });
        }

        function agregarProducto() {
            document.getElementById('productModal').style.display = 'flex';
            mostrarProductosEnModal();
        }

        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function mostrarProductosEnModal() {
            const modalList = document.getElementById('modalProductsList');
            modalList.innerHTML = '';

            productosDisponibles.forEach(producto => {
                const productDiv = document.createElement('div');
                productDiv.className = 'product-item-modal';
                productDiv.innerHTML = `
                    <div class="product-info">
                        <div class="product-name">${producto.nombre}</div>
                        <div class="product-category">${producto.categoria}</div>
                        <div class="product-price">$${producto.precio.toFixed(2)}</div>
                        <div class="product-stock">Stock: ${producto.stock}</div>
                    </div>
                    <button type="button" class="btn-primary btn-sm" onclick="seleccionarProducto(${producto.id})">
                        <i class="fas fa-plus"></i>
                        Agregar
                    </button>
                `;
                modalList.appendChild(productDiv);
            });
        }

        function seleccionarProducto(productoId) {
            const producto = productosDisponibles.find(p => p.id === productoId);
            if (producto) {
                // Verificar si el producto ya está en la venta
                const productoExistente = productosVenta.find(p => p.id_producto === productoId);
                if (productoExistente) {
                    productoExistente.cantidad += 1;
                    productoExistente.subtotal = productoExistente.cantidad * productoExistente.precio_unitario;
                } else {
                    productosVenta.push({
                        id_producto: producto.id,
                        nombre: producto.nombre,
                        precio_unitario: producto.precio,
                        cantidad: 1,
                        subtotal: producto.precio,
                        stock: producto.stock
                    });
                }
                actualizarTablaProductos();
                actualizarTotales();
                closeProductModal();
            }
        }

        function actualizarTablaProductos() {
            const tbody = document.getElementById('productosVentaBody');
            tbody.innerHTML = '';

            productosVenta.forEach((producto, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="product-info-cell">
                            <div class="product-name">${producto.nombre}</div>
                        </div>
                    </td>
                    <td>$${producto.precio_unitario.toFixed(2)}</td>
                    <td>
                        <input type="number" value="${producto.cantidad}" min="1" max="${producto.stock}" 
                               onchange="actualizarCantidad(${index}, this.value)" class="quantity-input">
                    </td>
                    <td>$${producto.subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn-action btn-delete" onclick="eliminarProducto(${index})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function actualizarCantidad(index, nuevaCantidad) {
            const cantidad = parseInt(nuevaCantidad);
            if (cantidad > 0 && cantidad <= productosVenta[index].stock) {
                productosVenta[index].cantidad = cantidad;
                productosVenta[index].subtotal = cantidad * productosVenta[index].precio_unitario;
                actualizarTablaProductos();
                actualizarTotales();
            }
        }

        function eliminarProducto(index) {
            productosVenta.splice(index, 1);
            actualizarTablaProductos();
            actualizarTotales();
        }

        function actualizarTotales() {
            const subtotal = productosVenta.reduce((sum, producto) => sum + producto.subtotal, 0);
            const descuento = 0; // Aquí se calcularía el descuento
            const iva = subtotal * 0.16;
            const total = subtotal - descuento + iva;

            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('descuento').textContent = `$${descuento.toFixed(2)}`;
            document.getElementById('iva').textContent = `$${iva.toFixed(2)}`;
            document.getElementById('total').textContent = `$${total.toFixed(2)}`;
        }

        function showSuccessAlert(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            `;
            
            const content = document.querySelector('.content');
            content.insertBefore(alertDiv, content.firstChild);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        function showErrorAlert(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <span>${message}</span>
            `;
            
            const content = document.querySelector('.content');
            content.insertBefore(alertDiv, content.firstChild);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                closeProductModal();
            }
        }
    </script>

    <style>
        .product-selection-container {
            margin-top: 20px;
        }

        .product-search {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .product-search .search-box {
            flex: 1;
        }

        .venta-products-table th,
        .venta-products-table td {
            padding: 12px;
            text-align: left;
        }

        .product-info-cell {
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-weight: 500;
            color: var(--text-primary);
        }

        .quantity-input {
            width: 80px;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            text-align: center;
        }

        .venta-summary {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-item.total {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1em;
            color: var(--primary-color);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .products-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .product-item-modal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .product-item-modal:last-child {
            border-bottom: none;
        }

        .product-item-modal .product-info {
            flex: 1;
        }

        .product-item-modal .product-name {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .product-item-modal .product-category {
            font-size: 0.9em;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .product-item-modal .product-price {
            font-weight: 600;
            color: var(--primary-color);
        }

        .product-item-modal .product-stock {
            font-size: 0.8em;
            color: var(--text-muted);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .product-search {
                flex-direction: column;
                align-items: stretch;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 20px;
            }
        }
    </style>
</body>
</html> 