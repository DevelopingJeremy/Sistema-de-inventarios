
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-users"></i>
            <span>HYBOX</span>
        </div>
    </div>
    
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-details">
            <span class="user-name"><?php echo $_SESSION['nombre_usuario'] ?></span>
            <span class="user-role">Administrador</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <a href="http://localhost/hybox.cloud/public/dashboard/dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-category">INVENTARIO</div>
            <a href="http://localhost/hybox.cloud/public/inventario/productos.php" class="nav-item">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </a>
            <a href="http://localhost/hybox.cloud/public/inventario/categorias.php" class="nav-item">
                <i class="fas fa-tags"></i>
                <span>Categorías</span>
            </a>
            <a href="http://localhost/hybox.cloud/public/inventario/movimientos.php" class="nav-item">
                <i class="fas fa-exchange-alt"></i>
                <span>Movimientos</span>
            </a>
            <a href="http://localhost/hybox.cloud/public/inventario/ajustes.php" class="nav-item">
                <i class="fas fa-tools"></i>
                <span>Ajustes</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-category">VENTAS</div>
            <a href="ventas/ventas.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Ventas</span>
            </a>
            <a href="ventas/clientes.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-category">COMPRAS</div>
            <a href="compras/compras.php" class="nav-item">
                <i class="fas fa-shopping-bag"></i>
                <span>Compras</span>
            </a>
            <a href="compras/proveedores.php" class="nav-item">
                <i class="fas fa-truck"></i>
                <span>Proveedores</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-category">REPORTES</div>
            <a href="reportes/reportes.php" class="nav-item">
                <i class="fas fa-file-alt"></i>
                <span>Reportes</span>
            </a>
            <a href="reportes/historial.php" class="nav-item">
                <i class="fas fa-clock"></i>
                <span>Historial</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-category">SISTEMA</div>
            <a href="sistema/usuarios.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
        </div>
    </nav>
</div> 