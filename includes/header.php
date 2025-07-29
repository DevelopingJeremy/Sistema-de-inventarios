<header class="header">
    <div class="header-left">
        <button class="btn-icon mobile-menu-toggle" id="mobileMenuToggle" style="display: none;">
            <i class="fas fa-bars"></i>
        </button>
        <div class="breadcrumb">
            <!-- Aqui se puede agregar el titulo de la pagina -->
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
        
        <!-- Botón dinámico que se oculta en móvil -->
        <button class="btn-primary header-add-button" id="headerAddButton" onclick="window.location.href='../inventario/agregar-producto.php'">
            <i class="fas fa-plus"></i>
            Nuevo Producto
        </button>
    </div>
</header> 