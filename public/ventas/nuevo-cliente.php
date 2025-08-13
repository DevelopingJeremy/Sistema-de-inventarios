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
    <title>Nuevo Cliente - HYBOX</title>
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
                        <a href="clientes.php">Clientes</a>
                        <span>/</span>
                        <span>Nuevo Cliente</span>
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
                            <h1 class="page-title">Agregar Nuevo Cliente</h1>
                            <p class="page-subtitle">Completa la información del cliente para registrarlo en el sistema</p>
                        </div>
                    </div>
                </div>

                <form class="form-container" id="clienteForm" action="../../src/ventas/crear-cliente.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_empresa" value="<?php echo $id_empresa; ?>">
                    
                    <!-- Información Personal -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-user"></i> Información Personal</h3>
                        </div>
                        <div class="form-section-description">
                            <p>Datos básicos del cliente para su identificación</p>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nombre" class="form-label">Nombre <span class="required">*</span></label>
                                <input type="text" id="nombre" name="nombre" class="form-input" required>
                                <div class="help-text">Ingresa el nombre del cliente</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="apellido" class="form-label">Apellido <span class="required">*</span></label>
                                <input type="text" id="apellido" name="apellido" class="form-input" required>
                                <div class="help-text">Ingresa el apellido del cliente</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Correo Electrónico <span class="required">*</span></label>
                                <input type="email" id="email" name="email" class="form-input" required>
                                <div class="help-text">Correo electrónico para contacto</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono" class="form-label">Teléfono <span class="required">*</span></label>
                                <input type="tel" id="telefono" name="telefono" class="form-input" required>
                                <div class="help-text">Número de teléfono con código de país</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-address-book"></i> Información de Contacto</h3>
                        </div>
                        <div class="form-section-description">
                            <p>Dirección y datos adicionales de contacto</p>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea id="direccion" name="direccion" class="form-textarea" rows="3"></textarea>
                                <div class="help-text">Dirección completa del cliente</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Comercial -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-chart-line"></i> Información Comercial</h3>
                        </div>
                        <div class="form-section-description">
                            <p>Configuración comercial y preferencias del cliente</p>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tipo_cliente" class="form-label">Tipo de Cliente <span class="required">*</span></label>
                                <select id="tipo_cliente" name="tipo_cliente" class="form-select" required>
                                    <option value="">Selecciona un tipo</option>
                                    <option value="regular">Regular</option>
                                    <option value="premium">Premium</option>
                                    <option value="vip">VIP</option>
                                    <option value="corporativo">Corporativo</option>
                                </select>
                                <div class="help-text">Categoría del cliente para descuentos y beneficios</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="metodo_pago_preferido" class="form-label">Método de Pago Preferido</label>
                                <select id="metodo_pago_preferido" name="metodo_pago_preferido" class="form-select">
                                    <option value="">Sin preferencia</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                    <option value="digital">Pago Digital</option>
                                </select>
                                <div class="help-text">Método de pago que prefiere el cliente</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="limite_credito" class="form-label">Límite de Crédito</label>
                                <input type="number" id="limite_credito" name="limite_credito" class="form-input" step="0.01" min="0">
                                <div class="help-text">Límite de crédito disponible (si aplica)</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="descuento" class="form-label">Descuento (%)</label>
                                <input type="number" id="descuento" name="descuento" class="form-input" step="0.01" min="0" max="100">
                                <div class="help-text">Porcentaje de descuento aplicable</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h3><i class="fas fa-info-circle"></i> Información Adicional</h3>
                        </div>
                        <div class="form-section-description">
                            <p>Datos adicionales y notas sobre el cliente</p>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="notas" class="form-label">Notas</label>
                                <textarea id="notas" name="notas" class="form-textarea" rows="4" placeholder="Información adicional sobre el cliente, preferencias, alergias, etc."></textarea>
                                <div class="help-text">Información adicional relevante sobre el cliente</div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="btn-container">
                        <button type="button" class="btn-secondary" onclick="window.location.href='clientes.php'">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMobileMenu();
            initClienteFormValidation();
        });

        function initClienteFormValidation() {
            const form = document.getElementById('clienteForm');
            const emailInput = document.getElementById('email');
            const telefonoInput = document.getElementById('telefono');
            const nombreInput = document.getElementById('nombre_cliente');

            // Validación de email
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                if (email && !isValidEmail(email)) {
                    showFieldError(this, 'Por favor ingresa un email válido');
                } else {
                    hideFieldError(this);
                }
            });

            // Validación de teléfono
            telefonoInput.addEventListener('blur', function() {
                const telefono = this.value.trim();
                if (telefono && !isValidPhone(telefono)) {
                    showFieldError(this, 'Por favor ingresa un teléfono válido');
                } else {
                    hideFieldError(this);
                }
            });

            // Validación de nombre
            nombreInput.addEventListener('blur', function() {
                const nombre = this.value.trim();
                if (nombre && nombre.length < 2) {
                    showFieldError(this, 'El nombre debe tener al menos 2 caracteres');
                } else {
                    hideFieldError(this);
                }
            });

            // Validación del formulario antes de enviar
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validar campos requeridos
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        showFieldError(field, 'Este campo es obligatorio');
                        isValid = false;
                    } else {
                        hideFieldError(field);
                    }
                });

                // Validar email
                if (emailInput.value.trim() && !isValidEmail(emailInput.value.trim())) {
                    showFieldError(emailInput, 'Por favor ingresa un email válido');
                    isValid = false;
                }

                // Validar teléfono
                if (telefonoInput.value.trim() && !isValidPhone(telefonoInput.value.trim())) {
                    showFieldError(telefonoInput, 'Por favor ingresa un teléfono válido');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    showErrorAlert('Por favor corrige los errores en el formulario');
                }
            });
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidPhone(phone) {
            // Validar teléfono con formato internacional
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
        }

        function showFieldError(input, message) {
            hideFieldError(input);
            input.classList.add('error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            input.parentNode.appendChild(errorDiv);
        }

        function hideFieldError(input) {
            input.classList.remove('error');
            const errorDiv = input.parentNode.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        function showErrorAlert(message) {
            // Crear alerta de error
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <span>${message}</span>
            `;
            
            // Insertar al inicio del contenido
            const content = document.querySelector('.content');
            content.insertBefore(alertDiv, content.firstChild);
            
            // Remover después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html> 