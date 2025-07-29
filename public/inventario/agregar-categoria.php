<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    $id_usuario = $_SESSION['id_usuario'];
    $error = '';
    $success = '';
    
    // Procesar el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre_categoria = trim($_POST['nombre_categoria']);
        $descripcion = trim($_POST['descripcion']);
        
        if (empty($nombre_categoria)) {
            $error = 'El nombre de la categoría es obligatorio';
        } else {
            // Obtener el ID de la empresa del usuario
            $stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_empresa WHERE ID_DUEÑO = ?");
            $stmt_empresa->bind_param("i", $id_usuario);
            $stmt_empresa->execute();
            $empresa_result = $stmt_empresa->get_result()->fetch_assoc();
            $id_empresa = $empresa_result['ID_EMPRESA'];
            
            // Verificar si la categoría ya existe
            $stmt_check = $conn->prepare("
                SELECT COUNT(*) as existe
                FROM t_categorias 
                WHERE ID_EMPRESA = ? AND nombre_categoria = ?
            ");
            $stmt_check->bind_param("is", $id_empresa, $nombre_categoria);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result()->fetch_assoc();
            
            if ($result_check['existe'] > 0) {
                $error = 'Ya existe una categoría con ese nombre';
            } else {
                // Crear la categoría en la tabla t_categorias
                $stmt_insert = $conn->prepare("
                    INSERT INTO t_categorias (
                        ID_EMPRESA, 
                        nombre_categoria, 
                        descripcion, 
                        estado, 
                        fecha_creacion
                    ) VALUES (?, ?, ?, 'activo', NOW())
                ");
                $stmt_insert->bind_param("iss", $id_empresa, $nombre_categoria, $descripcion);
                
                if ($stmt_insert->execute()) {
                    $success = 'Categoría creada exitosamente';
                    // Limpiar el formulario
                    $nombre_categoria = '';
                    $descripcion = '';
                } else {
                    $error = 'Error al crear la categoría';
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Categoría - HyBox Cloud</title>
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
                    <h1 class="page-title">Agregar Nueva Categoría</h1>
                    <p class="page-subtitle">Crea una nueva categoría para organizar tus productos</p>
                </div>

                <div class="form-container">
                    <div class="form-section">
                        <h3>Información de la Categoría</h3>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="form-grid">
                            <div class="form-group">
                                <label for="nombre_categoria" class="form-label">Nombre de la Categoría *</label>
                                <input type="text" 
                                       id="nombre_categoria" 
                                       name="nombre_categoria" 
                                       class="form-input" 
                                       value="<?php echo htmlspecialchars($nombre_categoria ?? ''); ?>" 
                                       placeholder="Ej: Electrónicos, Ropa, Hogar..."
                                       required>
                                <small class="form-help">El nombre debe ser único y descriptivo</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea id="descripcion" 
                                          name="descripcion" 
                                          class="form-textarea" 
                                          rows="4" 
                                          placeholder="Describe brevemente qué tipo de productos incluirá esta categoría..."><?php echo htmlspecialchars($descripcion ?? ''); ?></textarea>
                                <small class="form-help">Opcional: ayuda a identificar el propósito de la categoría</small>
                            </div>
                            
                            <div class="btn-container">
                                <button type="button" class="btn-secondary" onclick="window.location.href='categorias.php'">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> Crear Categoría
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="form-section">
                        <h3>Información Importante</h3>
                        <div class="info-box">
                            <div class="info-item">
                                <span class="info-label">Estado inicial:</span>
                                <span class="info-value">Inactiva (sin productos)</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Activación:</span>
                                <span class="info-value">Se activa al agregar productos</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Edición:</span>
                                <span class="info-value">Puedes editar desde la lista de categorías</span>
                            </div>
                        </div>
                        
                        <div class="alert alert-info" style="margin-top: 20px;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> La categoría se creará como "Inactiva" hasta que agregues productos a ella. Esto es normal y ayuda a mantener organizado tu inventario.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Inicializar funcionalidades
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMobileMenu();
            
            // Cambiar texto del botón del header
            const headerButton = document.getElementById('headerAddButton');
            if (headerButton) {
                headerButton.innerHTML = '<i class="fas fa-plus"></i> Nueva Categoría';
                headerButton.onclick = function() {
                    window.location.href = 'agregar-categoria.php';
                };
            }
        });
    </script>
</body>
</html> 