<header class="header">
    <div class="header-left">
        <div class="breadcrumb">
            <span>Dashboard</span>
            <i class="fas fa-chevron-right"></i>
            <span>Dashboard</span>
        </div>
    </div>
    
    <div class="header-right">
        <div class="header-actions">
            <button class="btn-icon" id="darkModeToggle">
                <i class="fas fa-moon"></i>
            </button>
            <div class="user-menu">
                <button class="user-dropdown">
                    <i class="fas fa-user"></i>
                    <span>Administrador</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        Mi Perfil
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Configuración
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="http://localhost/hybox.cloud/src/auth/sesion/salir.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
        
        <button class="btn-primary" onclick="window.location.href='../inventario/agregar-producto.php'">
            <i class="fas fa-plus"></i>
            + Nuevo Producto
        </button>
    </div>
</header> 