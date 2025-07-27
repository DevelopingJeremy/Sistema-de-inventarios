<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestión de Productos</title>
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
                    <h1 class="page-title">Gestión de Productos</h1>
                    <p class="page-subtitle">Administra tu catálogo de productos y servicios</p>
                </div>

                <!-- Metrics Cards -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-mouse"></i>
                            </div>
                        </div>
                        <div class="metric-value">1,250</div>
                        <div class="metric-indicator">+45 este mes</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #e83e8c;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="metric-value">1,180</div>
                        <div class="metric-indicator">94% del total</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #007bff;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="metric-value">15</div>
                        <div class="metric-indicator">Requieren atención</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #28a745;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="metric-value">₡15.2M</div>
                        <div class="metric-indicator">+8.5% vs mes anterior</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #007bff;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="metric-value">₡2.8M</div>
                        <div class="metric-indicator">↑+12.5% +₡320K vs mes anterior</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="metric-value">156</div>
                        <div class="metric-indicator">↑+8.2% +12 vs mes anterior</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div class="metric-value">4.8</div>
                        <div class="metric-indicator">↑+5.1% +0.2 vs mes anterior</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon" style="background: #6f42c1;">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                        <div class="metric-value">2,450</div>
                        <div class="metric-indicator">↑+15.3% +325 vs mes anterior</div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Ventas por Categoría</h3>
                            <div class="time-filters">
                                <button class="time-filter active">Semana</button>
                                <button class="time-filter">Mes</button>
                                <button class="time-filter">Año</button>
                            </div>
                        </div>
                        
                        <div class="chart-metrics">
                            <div class="chart-metric">
                                <div class="chart-metric-value">₡2.8M</div>
                                <div class="chart-metric-label">Total</div>
                            </div>
                            <div class="chart-metric">
                                <div class="chart-metric-value">₡280K</div>
                                <div class="chart-metric-label">Promedio</div>
                            </div>
                            <div class="chart-metric growth">
                                <div class="chart-metric-value">+12.5%</div>
                                <div class="chart-metric-label">Crecimiento</div>
                            </div>
                        </div>
                        
                        <div style="height: 200px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            Gráfico de Ventas por Categoría
                        </div>
                    </div>

                    <div class="top-products">
                        <div class="top-products-header">
                            <h3 class="chart-title">Top Productos</h3>
                            <button class="btn-icon">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-rank">1</div>
                            <div class="product-icon" style="background: #6f42c1;">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Laptop HP Pavilion</div>
                                <div class="product-category">Electrónicos</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">25</div>
                                <div class="product-revenue">₡6.25M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +15%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-rank">2</div>
                            <div class="product-icon" style="background: #28a745;">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">iPhone 13 Pro</div>
                                <div class="product-category">Electrónicos</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">18</div>
                                <div class="product-revenue">₡4.5M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +12%
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-rank">3</div>
                            <div class="product-icon" style="background: #007bff;">
                                <i class="fas fa-headphones"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">AirPods Pro</div>
                                <div class="product-category">Accesorios</div>
                            </div>
                            <div class="product-stats">
                                <div class="product-sales">32</div>
                                <div class="product-revenue">₡2.88M</div>
                                <div class="product-growth">
                                    <i class="fas fa-arrow-up"></i>
                                    +8%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table Section -->
                <div class="products-section">
                    <div class="products-header">
                        <h2 class="section-title">Lista de Productos</h2>
                    </div>

                    <!-- Search and Filters -->
                    <div class="filters-section">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="search-input" placeholder="Buscar productos..." id="searchInput">
                        </div>
                        
                        <div class="filter-group">
                            <select class="filter-select" id="categoryFilter">
                                <option value="">Todas las categorías</option>
                                <option value="Electrónicos">Electrónicos</option>
                                <option value="Accesorios">Accesorios</option>
                                <option value="Ropa">Ropa</option>
                                <option value="Hogar">Hogar</option>
                            </select>
                            
                            <select class="filter-select" id="statusFilter">
                                <option value="">Todos los estados</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                                <option value="Stock Bajo">Stock Bajo</option>
                            </select>
                            
                            <select class="filter-select" id="supplierFilter">
                                <option value="">Todos los proveedores</option>
                                <option value="Apple">Apple</option>
                                <option value="Samsung">Samsung</option>
                                <option value="HP">HP</option>
                                <option value="Dell">Dell</option>
                            </select>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="table-container">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th>Proveedor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="https://via.placeholder.com/40x40/6f42c1/ffffff?text=L" class="product-image" alt="Laptop">
                                            <div>
                                                <div style="font-weight: 600;">Laptop HP Pavilion</div>
                                                <div style="font-size: 0.8rem; color: #6c757d;">#PROD001</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Electrónicos</td>
                                    <td>₡250,000</td>
                                    <td>45</td>
                                    <td><span class="status-badge status-active">Activo</span></td>
                                    <td>HP</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Ver"><i class="fas fa-eye"></i></button>
                                            <button class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></button>
                                            <button class="btn-action btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="https://via.placeholder.com/40x40/28a745/ffffff?text=i" class="product-image" alt="iPhone">
                                            <div>
                                                <div style="font-weight: 600;">iPhone 13 Pro</div>
                                                <div style="font-size: 0.8rem; color: #6c757d;">#PROD002</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Electrónicos</td>
                                    <td>₡450,000</td>
                                    <td>12</td>
                                    <td><span class="status-badge status-low-stock">Stock Bajo</span></td>
                                    <td>Apple</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Ver"><i class="fas fa-eye"></i></button>
                                            <button class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></button>
                                            <button class="btn-action btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="https://via.placeholder.com/40x40/007bff/ffffff?text=A" class="product-image" alt="AirPods">
                                            <div>
                                                <div style="font-weight: 600;">AirPods Pro</div>
                                                <div style="font-size: 0.8rem; color: #6c757d;">#PROD003</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Accesorios</td>
                                    <td>₡90,000</td>
                                    <td>78</td>
                                    <td><span class="status-badge status-active">Activo</span></td>
                                    <td>Apple</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Ver"><i class="fas fa-eye"></i></button>
                                            <button class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></button>
                                            <button class="btn-action btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="https://via.placeholder.com/40x40/e83e8c/ffffff?text=S" class="product-image" alt="Samsung">
                                            <div>
                                                <div style="font-weight: 600;">Samsung Galaxy S21</div>
                                                <div style="font-size: 0.8rem; color: #6c757d;">#PROD004</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Electrónicos</td>
                                    <td>₡380,000</td>
                                    <td>23</td>
                                    <td><span class="status-badge status-active">Activo</span></td>
                                    <td>Samsung</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Ver"><i class="fas fa-eye"></i></button>
                                            <button class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></button>
                                            <button class="btn-action btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="https://via.placeholder.com/40x40/dc3545/ffffff?text=D" class="product-image" alt="Dell">
                                            <div>
                                                <div style="font-weight: 600;">Dell XPS 13</div>
                                                <div style="font-size: 0.8rem; color: #6c757d;">#PROD005</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Electrónicos</td>
                                    <td>₡520,000</td>
                                    <td>8</td>
                                    <td><span class="status-badge status-inactive">Inactivo</span></td>
                                    <td>Dell</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Ver"><i class="fas fa-eye"></i></button>
                                            <button class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></button>
                                            <button class="btn-action btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Añadir Nuevo Producto</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-input" name="productName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Código del Producto</label>
                            <input type="text" class="form-input" name="productCode" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Categoría</label>
                            <select class="form-input" name="category" required>
                                <option value="">Seleccionar categoría</option>
                                <option value="Electrónicos">Electrónicos</option>
                                <option value="Accesorios">Accesorios</option>
                                <option value="Ropa">Ropa</option>
                                <option value="Hogar">Hogar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Precio</label>
                            <input type="number" class="form-input" name="price" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-input" name="stock" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Proveedor</label>
                            <select class="form-input" name="supplier" required>
                                <option value="">Seleccionar proveedor</option>
                                <option value="Apple">Apple</option>
                                <option value="Samsung">Samsung</option>
                                <option value="HP">HP</option>
                                <option value="Dell">Dell</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-input form-textarea" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">URL de la Imagen</label>
                        <input type="url" class="form-input" name="imageUrl" placeholder="https://ejemplo.com/imagen.jpg">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="button" class="btn-primary" onclick="saveProduct()">Guardar Producto</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html> 