<?php
    include('../../config/db.php');
    
    // Verificar sesión simple
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: ../sesion/iniciar-sesion.php');
        exit;
    }
    
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
    
    // Verificar si hay mensajes de éxito o error
    $mensaje_exito = '';
    $mensaje_error = '';
    
    if (isset($_GET['success']) && $_GET['success'] == '1') {
        $mensaje_exito = $_GET['mensaje'] ?? 'Operación realizada con éxito';
    }
    
    if (isset($_GET['error']) && $_GET['error'] == '1') {
        $mensaje_error = $_GET['mensaje'] ?? 'Ha ocurrido un error';
    }
    
    // Obtener estadísticas dinámicas de clientes
    try {
        $stmt_stats = $conn->prepare("
            SELECT 
                COUNT(*) as total_clientes,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as clientes_activos,
                SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as clientes_inactivos,
                SUM(CASE WHEN fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as nuevos_mes,
                COALESCE(AVG(total_compras), 0) as promedio_compras,
                COALESCE(SUM(total_compras), 0) as total_compras_general
            FROM t_clientes 
            WHERE ID_EMPRESA = ?
        ");
        $stmt_stats->bind_param("i", $id_empresa);
        $stmt_stats->execute();
        $stats_clientes = $stmt_stats->get_result()->fetch_assoc();
        
        // Obtener clientes más frecuentes
        $stmt_top_clientes = $conn->prepare("
            SELECT 
                c.nombre_cliente,
                c.email,
                c.telefono,
                c.total_compras,
                c.total_gastado,
                COUNT(v.ID_VENTA) as ventas_realizadas,
                COALESCE(SUM(v.total_venta), 0) as valor_total_ventas,
                MAX(v.fecha_venta) as ultima_compra
            FROM t_clientes c
            LEFT JOIN t_ventas v ON c.ID_CLIENTE = v.ID_CLIENTE AND v.estado = 'completada'
            WHERE c.ID_EMPRESA = ?
            GROUP BY c.ID_CLIENTE, c.nombre_cliente, c.email, c.telefono, c.total_compras, c.total_gastado
            ORDER BY ventas_realizadas DESC, valor_total_ventas DESC
            LIMIT 5
        ");
        $stmt_top_clientes->bind_param("i", $id_empresa);
        $stmt_top_clientes->execute();
        $top_clientes = $stmt_top_clientes->get_result();
        
        // Obtener distribución por tipo de cliente
        $stmt_tipos_cliente = $conn->prepare("
            SELECT 
                tipo_cliente,
                COUNT(*) as total_clientes,
                COALESCE(SUM(total_gastado), 0) as valor_total
            FROM t_clientes 
            WHERE ID_EMPRESA = ? 
            AND estado = 'activo'
            GROUP BY tipo_cliente
            ORDER BY valor_total DESC
        ");
        $stmt_tipos_cliente->bind_param("i", $id_empresa);
        $stmt_tipos_cliente->execute();
        $tipos_cliente = $stmt_tipos_cliente->get_result();
        
        // Calcular total de clientes activos
        $total_clientes_activos = 0;
        $total_valor_clientes = 0;
        if ($tipos_cliente) {
            while ($row = $tipos_cliente->fetch_assoc()) {
                $total_clientes_activos += $row['total_clientes'];
                $total_valor_clientes += $row['valor_total'];
            }
        }
        
    } catch (Exception $e) {
        // En caso de error, establecer valores por defecto
        $stats_clientes = [
            'total_clientes' => 0,
            'clientes_activos' => 0,
            'clientes_inactivos' => 0,
            'nuevos_mes' => 0,
            'promedio_compras' => 0,
            'total_compras_general' => 0
        ];
        $top_clientes = null;
        $tipos_cliente = null;
        $total_clientes_activos = 0;
        $total_valor_clientes = 0;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - HYBOX</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Verificar que SweetAlert2 se cargó correctamente
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 no se cargó correctamente');
        } else {
            console.log('SweetAlert2 cargado correctamente');
        }
    </script>
</head>
<body>
    <div class="dashboard">
        <?php include('../../includes/sidebar.php'); ?>
        
        <div class="main-content">
        <?php include("../../includes/header.php"); ?>
            
            <div class="content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Gestión de Clientes</h1>
                            <p class="page-subtitle">Administra y mantén la información de todos tus clientes</p>
                        </div>
                    </div>
                </div>

                <!-- Métricas de Clientes -->
                <div class="dashboard-metrics">
                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title-dashboard">Total Clientes</h3>
                                <div class="metric-value-dashboard"><?php echo number_format($stats_clientes['total_clientes']); ?></div>
                                <div class="metric-indicator-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $stats_clientes['nuevos_mes']; ?> nuevos este mes</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title-dashboard">Clientes Activos</h3>
                                <div class="metric-value-dashboard"><?php echo number_format($stats_clientes['clientes_activos']); ?></div>
                                <div class="metric-indicator-dashboard positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $stats_clientes['total_clientes'] > 0 ? round(($stats_clientes['clientes_activos'] / $stats_clientes['total_clientes']) * 100, 1) : 0; ?>% del total</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title-dashboard">Total Compras</h3>
                                <div class="metric-value-dashboard"><?php echo number_format($stats_clientes['total_compras_general']); ?></div>
                                <div class="metric-indicator-dashboard neutral">
                                    <i class="fas fa-minus"></i>
                                    <span><?php echo number_format($stats_clientes['promedio_compras'], 1); ?> promedio por cliente</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="metric-card-dashboard">
                        <div class="metric-header-dashboard">
                            <div class="metric-icon-dashboard">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title-dashboard">Valor Promedio</h3>
                                <div class="metric-value-dashboard">$<?php echo number_format($stats_clientes['promedio_compras'], 2); ?></div>
                                <div class="metric-indicator-dashboard neutral">
                                    <i class="fas fa-minus"></i>
                                    <span>Por cliente</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-content">
                    <!-- Clientes Más Frecuentes -->
                    <div class="top-selling-products">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">Clientes Más Frecuentes</h2>
                                <p class="section-subtitle">Por número de compras</p>
                            </div>
                            <a href="clientes.php" class="view-all-link">
                                Ver todos <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <div class="top-products">
                            <?php if ($top_clientes && $top_clientes->num_rows > 0): ?>
                                <?php $rank = 1; ?>
                                <?php while ($cliente = $top_clientes->fetch_assoc()): ?>
                                    <div class="product-item-dashboard">
                                        <div class="product-rank-dashboard">#<?php echo $rank; ?></div>
                                        <div class="product-icon-dashboard">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="product-info-dashboard">
                                            <div class="product-name-dashboard"><?php echo htmlspecialchars($cliente['nombre_cliente']); ?></div>
                                            <div class="product-category-dashboard"><?php echo htmlspecialchars($cliente['email']); ?></div>
                                        </div>
                                        <div class="product-stats-dashboard">
                                            <div class="product-sales-dashboard">
                                                <span class="sales-label">Compras:</span>
                                                <span class="sales-value"><?php echo number_format($cliente['ventas_realizadas']); ?> ventas</span>
                                            </div>
                                            <div class="product-revenue-dashboard">
                                                <span class="revenue-label">Total:</span>
                                                <span class="revenue-value">$<?php echo number_format($cliente['valor_total_ventas'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $rank++; ?>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <p>No hay datos de clientes disponibles</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Distribución por Tipo de Cliente -->
                    <div class="sales-by-category">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">Distribución por Tipo de Cliente</h2>
                                <p class="section-subtitle">Clientes activos</p>
                            </div>
                        </div>
                        
                        <div class="category-sales">
                            <?php if ($tipos_cliente && $tipos_cliente->num_rows > 0): ?>
                                <?php while ($tipo = $tipos_cliente->fetch_assoc()): ?>
                                    <?php 
                                        $porcentaje = $total_clientes_activos > 0 ? ($tipo['total_clientes'] / $total_clientes_activos) * 100 : 0;
                                        $iconos = [
                                            'regular' => 'fas fa-user',
                                            'premium' => 'fas fa-crown',
                                            'vip' => 'fas fa-star',
                                            'corporativo' => 'fas fa-building'
                                        ];
                                        $icono = $iconos[$tipo['tipo_cliente']] ?? 'fas fa-user';
                                    ?>
                                    <div class="category-sales-item">
                                        <div class="category-sales-header">
                                            <div class="category-sales-icon">
                                                <i class="<?php echo $icono; ?>"></i>
                                            </div>
                                            <div class="category-sales-info">
                                                <div class="category-sales-name"><?php echo ucfirst($tipo['tipo_cliente']); ?></div>
                                                <div class="category-sales-meta"><?php echo $tipo['total_clientes']; ?> clientes</div>
                                            </div>
                                        </div>
                                        <div class="category-sales-stats">
                                            <div class="category-sales-value">$<?php echo number_format($tipo['valor_total'], 2); ?></div>
                                            <div class="category-sales-percentage">
                                                <span><?php echo round($porcentaje, 1); ?>%</span>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-chart-pie"></i>
                                    <p>No hay datos de tipos de cliente</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Clientes -->
                <div class="products-section">
                    <div class="products-header">
                        <div class="section-title">Lista de Clientes</div>
                        <div class="header-buttons">
                            <button class="btn-primary" onclick="descargarPDFClientes()">
                                <i class="fas fa-download"></i>
                                Descargar PDF
                            </button>
                        </div>
                    </div>

                    <div class="filters-section">
                        <div class="search-box">
                            <input type="text" id="searchInput" class="search-input" placeholder="Buscar clientes...">
                            <i class="fas fa-search search-icon"></i>
                        </div>

                        <div class="filter-group">
                            <select id="tipoClienteFilter" class="filter-select">
                                <option value="">Todos los tipos</option>
                                <option value="regular">Regular</option>
                                <option value="premium">Premium</option>
                                <option value="vip">VIP</option>
                                <option value="corporativo">Corporativo</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <select id="ordenFilter" class="filter-select">
                                <option value="nombre_asc">Nombre A-Z</option>
                                <option value="nombre_desc">Nombre Z-A</option>
                                <option value="fecha_desc">Más recientes</option>
                                <option value="fecha_asc">Más antiguos</option>
                                <option value="compras_desc">Más compras</option>
                                <option value="valor_desc">Mayor valor</option>
                            </select>
                        </div>
                        <button id="clearFiltersBtn" class="btn-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-times"></i>
                            Limpiar
                        </button>
                        <a href="nuevo-cliente.php" class="btn-primary">
                            <i class="fas fa-plus"></i>
                            Agregar Cliente
                        </a>
                    </div>

                    <div class="table-container">
                        <table class="products-table clientes-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Tipo</th>
                                    <th>Compras</th>
                                    <th>Total Gastado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="clientesTableBody">
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMobileMenu();
            initClientesFilters();
            cargarClientes();
        });

        function cargarClientes(filtros = {}) {
            // Construir URL con parámetros de filtro
            const params = new URLSearchParams();
            
            if (filtros.busqueda) params.append('busqueda', filtros.busqueda);
            if (filtros.tipo_cliente) params.append('tipo_cliente', filtros.tipo_cliente);
            if (filtros.orden) {
                const [campo, direccion] = filtros.orden.split('_');
                params.append('orden', campo);
                params.append('direccion', direccion === 'desc' ? 'DESC' : 'ASC');
            }
            
            console.log('URL params:', params.toString());
            
            // Cargar clientes con filtros desde la base de datos
            fetch(`../../src/ventas/obtener-clientes.php?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    const tbody = document.getElementById('clientesTableBody');
                    if (data.success && data.clientes) {
                        tbody.innerHTML = data.clientes.map(cliente => `
                            <tr>
                                <td>#${cliente.ID_CLIENTE.toString().padStart(3, '0')}</td>
                                <td>${cliente.nombre} ${cliente.apellido}</td>
                                <td>${cliente.email || 'No especificado'}</td>
                                <td>${cliente.telefono || 'No especificado'}</td>
                                <td><span class="category-badge">${cliente.tipo_cliente}</span></td>
                                <td>${cliente.total_ventas || 0}</td>
                                <td>$${parseFloat(cliente.total_gastado || 0).toFixed(2)}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" onclick="verCliente(${cliente.ID_CLIENTE})" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-edit" onclick="editarCliente(${cliente.ID_CLIENTE})" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action btn-delete" onclick="eliminarCliente(${cliente.ID_CLIENTE}, '${cliente.nombre} ${cliente.apellido}')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay clientes registrados</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error al cargar clientes:', error);
                    const tbody = document.getElementById('clientesTableBody');
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center">Error al cargar los clientes</td></tr>';
                });
        }

        function initClientesFilters() {
            const searchInput = document.getElementById('searchInput');
            const tipoClienteFilter = document.getElementById('tipoClienteFilter');
            const ordenFilter = document.getElementById('ordenFilter');

            // Aplicar filtros en tiempo real
            [searchInput, tipoClienteFilter, ordenFilter].forEach(element => {
                element.addEventListener('change', aplicarFiltrosClientes);
                element.addEventListener('input', aplicarFiltrosClientes);
            });
        }

        function aplicarFiltrosClientes() {
            const busqueda = document.getElementById('searchInput').value.trim();
            const tipoCliente = document.getElementById('tipoClienteFilter').value;
            const orden = document.getElementById('ordenFilter').value;
            
            // Crear objeto de filtros
            const filtros = {};
            
            if (busqueda) filtros.busqueda = busqueda;
            if (tipoCliente) filtros.tipo_cliente = tipoCliente;
            if (orden) filtros.orden = orden;
            
            console.log('Aplicando filtros:', filtros);
            
            // Aplicar filtros con debounce para la búsqueda
            clearTimeout(window.filtrosTimeout);
            window.filtrosTimeout = setTimeout(() => {
                cargarClientes(filtros);
            }, 300);
        }

        function limpiarFiltros() {
            document.getElementById('searchInput').value = '';
            document.getElementById('tipoClienteFilter').value = '';
            document.getElementById('ordenFilter').value = 'nombre_asc';
            cargarClientes();
        }

        function verCliente(id) {
            window.location.href = `ver-cliente.php?id=${id}`;
        }

        function editarCliente(id) {
            window.location.href = `editar-cliente.php?id=${id}`;
        }

        function eliminarCliente(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Estás seguro de que quieres eliminar al cliente "${nombre}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Eliminando cliente...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Realizar la eliminación
                    fetch('../../src/ventas/eliminar-cliente.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id_cliente=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Cliente eliminado!',
                                text: data.mensaje || 'El cliente ha sido eliminado correctamente',
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                // Recargar la tabla
                                cargarClientes();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.mensaje || 'No se pudo eliminar el cliente',
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al eliminar el cliente',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }

        function descargarPDFClientes() {
            // Mostrar loading
            Swal.fire({
                title: 'Generando PDF...',
                text: 'Por favor espera mientras se genera el reporte',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Abrir el PDF en una nueva ventana
            const pdfUrl = '../../src/ventas/generar-pdf-clientes.php';
            const newWindow = window.open(pdfUrl, '_blank');
            
            if (newWindow) {
                // Cerrar el loading cuando se abra la ventana
                setTimeout(() => {
                    Swal.close();
                }, 1000);
            } else {
                // Si no se pudo abrir la ventana, mostrar error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo abrir el PDF. Verifica que no tengas bloqueado el popup.',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#dc3545'
                });
            }
        }
        
        // Mostrar Sweet Alerts si hay mensajes
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($mensaje_exito)): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?php echo addslashes($mensaje_exito); ?>',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#28a745'
            });
            <?php endif; ?>
            
            <?php if (!empty($mensaje_error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo addslashes($mensaje_error); ?>',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#dc3545'
            });
            <?php endif; ?>
        });
    </script>
</body>
</html> 