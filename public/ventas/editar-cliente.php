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
    header('Location: clientes.php');
    $_SESSION['mensaje_error'] = 'Usuario no tiene empresa registrada';
    exit;
}
$id_cliente = (int)($_GET['id'] ?? 0);

if ($id_cliente <= 0) {
    header('Location: clientes.php');
    exit;
}

try {
    // Obtener información del cliente
    $sql_cliente = "SELECT * FROM t_clientes WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    $cliente = $result_cliente->fetch_assoc();

    if (!$cliente) {
        header('Location: clientes.php');
        exit;
    }

} catch (Exception $e) {
    $_SESSION['mensaje_error'] = 'Error al cargar los datos del cliente';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - HyBox</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Verificar que SweetAlert2 se cargó correctamente
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 no se cargó correctamente, intentando CDN alternativo...');
            // Cargar CDN alternativo
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/sweetalert2@11/dist/sweetalert2.min.js';
            script.onload = function() {
                console.log('SweetAlert2 cargado desde CDN alternativo');
            };
            script.onerror = function() {
                console.error('No se pudo cargar SweetAlert2 desde ningún CDN');
            };
            document.head.appendChild(script);
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
                <div class="page-title">
                    <h1><i class="fas fa-user-edit"></i> Editar Cliente</h1>
                    <p>Modificar información del cliente: <strong><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></strong></p>
                </div>
                <div class="page-actions">
                    <a href="ver-cliente.php?id=<?= $id_cliente ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

                <div class="form-container">
                    <form id="clienteForm" class="form" method="POST" action="../../src/ventas/actualizar-cliente.php">
                        <input type="hidden" name="id_cliente" value="<?= $id_cliente ?>">
                        
                        <!-- Información Personal -->
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Información Personal</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nombre" class="form-label">Nombre *</label>
                                    <input type="text" id="nombre" name="nombre" class="form-input" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
                                    <span class="error-message" id="nombre-error"></span>
                                </div>
                                <div class="form-group">
                                    <label for="apellido" class="form-label">Apellido *</label>
                                    <input type="text" id="apellido" name="apellido" class="form-input" value="<?= htmlspecialchars($cliente['apellido']) ?>" required>
                                    <span class="error-message" id="apellido-error"></span>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($cliente['email']) ?>">
                                    <span class="error-message" id="email-error"></span>
                                </div>
                                <div class="form-group">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" id="telefono" name="telefono" class="form-input" value="<?= htmlspecialchars($cliente['telefono']) ?>">
                                    <span class="error-message" id="telefono-error"></span>
                                </div>
                                <div class="form-group full-width">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <textarea id="direccion" name="direccion" class="form-textarea" rows="3"><?= htmlspecialchars($cliente['direccion']) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Información Comercial -->
                        <div class="form-section">
                            <h3><i class="fas fa-chart-line"></i> Información Comercial</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="tipo_cliente" class="form-label">Tipo de Cliente</label>
                                    <select id="tipo_cliente" name="tipo_cliente" class="form-select">
                                        <option value="regular" <?= $cliente['tipo_cliente'] === 'regular' ? 'selected' : '' ?>>Regular</option>
                                        <option value="premium" <?= $cliente['tipo_cliente'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                                        <option value="vip" <?= $cliente['tipo_cliente'] === 'vip' ? 'selected' : '' ?>>VIP</option>
                                        <option value="corporativo" <?= $cliente['tipo_cliente'] === 'corporativo' ? 'selected' : '' ?>>Corporativo</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="metodo_pago_preferido" class="form-label">Método de Pago Preferido</label>
                                    <select id="metodo_pago_preferido" name="metodo_pago_preferido" class="form-select">
                                        <option value="efectivo" <?= $cliente['metodo_pago_preferido'] === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                        <option value="tarjeta" <?= $cliente['metodo_pago_preferido'] === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                                        <option value="transferencia" <?= $cliente['metodo_pago_preferido'] === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                        <option value="cheque" <?= $cliente['metodo_pago_preferido'] === 'cheque' ? 'selected' : '' ?>>Cheque</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="limite_credito" class="form-label">Límite de Crédito (₡)</label>
                                    <input type="number" id="limite_credito" name="limite_credito" class="form-input" step="0.01" min="0" value="<?= $cliente['limite_credito'] ?>">
                                    <span class="error-message" id="limite_credito-error"></span>
                                </div>
                                <div class="form-group">
                                    <label for="descuento" class="form-label">Descuento (%)</label>
                                    <input type="number" id="descuento" name="descuento" class="form-input" step="0.01" min="0" max="100" value="<?= $cliente['descuento'] ?>">
                                    <span class="error-message" id="descuento-error"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="form-section">
                            <h3><i class="fas fa-sticky-note"></i> Notas Adicionales</h3>
                            <div class="form-group">
                                <label for="notas" class="form-label">Notas</label>
                                <textarea id="notas" name="notas" class="form-textarea" rows="4" placeholder="Información adicional sobre el cliente..."><?= htmlspecialchars($cliente['notas']) ?></textarea>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="ver-cliente.php?id=<?= $id_cliente ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Funciones de validación
        function initClienteFormValidation() {
            // Limpiar mensajes de error al escribir
            const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    const errorElement = document.getElementById(this.id + '-error');
                    if (errorElement) {
                        errorElement.textContent = '';
                    }
                });
            });
        }

        function validateClienteForm() {
            let isValid = true;
            
            // Validar nombre
            const nombre = document.getElementById('nombre').value.trim();
            if (!nombre) {
                showFieldError('nombre', 'El nombre es obligatorio');
                isValid = false;
            }
            
            // Validar apellido
            const apellido = document.getElementById('apellido').value.trim();
            if (!apellido) {
                showFieldError('apellido', 'El apellido es obligatorio');
                isValid = false;
            }
            
            // Validar email si está presente
            const email = document.getElementById('email').value.trim();
            if (email && !isValidEmail(email)) {
                showFieldError('email', 'El email no es válido');
                isValid = false;
            }
            
            // Validar límite de crédito
            const limiteCredito = parseFloat(document.getElementById('limite_credito').value);
            if (limiteCredito < 0) {
                showFieldError('limite_credito', 'El límite de crédito no puede ser negativo');
                isValid = false;
            }
            
            // Validar descuento
            const descuento = parseFloat(document.getElementById('descuento').value);
            if (descuento < 0 || descuento > 100) {
                showFieldError('descuento', 'El descuento debe estar entre 0 y 100%');
                isValid = false;
            }
            
            return isValid;
        }

        function showFieldError(fieldId, message) {
            const errorElement = document.getElementById(fieldId + '-error');
            if (errorElement) {
                errorElement.textContent = message;
            }
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showSuccessAlert(message) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: message,
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#28a745'
            });
        }

        function showErrorAlert(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#dc3545'
            });
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            initClienteFormValidation();
            
            // Manejar envío del formulario
            const form = document.getElementById('clienteForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateClienteForm()) {
                        e.preventDefault();
                        showErrorAlert('Por favor, corrige los errores en el formulario');
                    }
                });
            }

            // Mostrar mensaje de error si existe
            <?php if (isset($_SESSION['mensaje_error']) && $_SESSION['mensaje_error']): ?>
            showErrorAlert('<?= addslashes($_SESSION['mensaje_error']) ?>');
            <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>
        });

    </script>
</body>
</html> 