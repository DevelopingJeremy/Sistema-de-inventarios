<?php
    include('../../config/db.php');
    require_once('../../src/auth/sesion/verificaciones-sesion.php');
    validTotales('../sesion/iniciar-sesion.php', '../sesion/envio-correo.php', '../empresa/registrar-empresa.php');
    
    $id_usuario = $_SESSION['id_usuario'];
    $id_categoria = $_GET['id'] ?? null;
    $error = '';
    $success = '';
    
    if (!$id_categoria) {
        header('Location: categorias.php?error=ID de categoría no proporcionado');
        exit;
    }
    
    // Obtener el ID de la empresa del usuario
    $stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_empresa WHERE ID_DUEÑO = ?");
    $stmt_empresa->bind_param("i", $id_usuario);
    $stmt_empresa->execute();
    $empresa_result = $stmt_empresa->get_result()->fetch_assoc();
    $id_empresa = $empresa_result['ID_EMPRESA'];
    
    // Obtener información de la categoría
    $stmt_categoria = $conn->prepare("
        SELECT * FROM t_categorias 
        WHERE ID_CATEGORIA = ? AND ID_EMPRESA = ?
    ");
    $stmt_categoria->bind_param("ii", $id_categoria, $id_empresa);
    $stmt_categoria->execute();
    $result = $stmt_categoria->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: categorias.php?error=Categoría no encontrada');
        exit;
    }
    
    $categoria = $result->fetch_assoc();
    
    // Procesar el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre_categoria = trim($_POST['nombre_categoria']);
        $descripcion = trim($_POST['descripcion']);
        
        if (empty($nombre_categoria)) {
            $error = 'El nombre de la categoría es obligatorio';
        } else {
            // Verificar si el nuevo nombre ya existe (excluyendo la categoría actual)
            $stmt_check = $conn->prepare("
                SELECT COUNT(*) as existe
                FROM t_categorias 
                WHERE ID_EMPRESA = ? AND nombre_categoria = ? AND ID_CATEGORIA != ?
            ");
            $stmt_check->bind_param("isi", $id_empresa, $nombre_categoria, $id_categoria);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result()->fetch_assoc();
            
            if ($result_check['existe'] > 0) {
                $error = 'Ya existe una categoría con ese nombre';
            } else {
                // Actualizar la categoría
                $stmt_update = $conn->prepare("
                    UPDATE t_categorias 
                    SET nombre_categoria = ?, descripcion = ?, fecha_actualizacion = NOW()
                    WHERE ID_CATEGORIA = ?
                ");
                $stmt_update->bind_param("ssi", $nombre_categoria, $descripcion, $id_categoria);
                
                if ($stmt_update->execute()) {
                    // Si cambió el nombre, actualizar los productos asociados
                    if ($nombre_categoria !== $categoria['nombre_categoria']) {
                        $stmt_update_productos = $conn->prepare("
                            UPDATE t_productos 
                            SET categoria = ? 
                            WHERE categoria = ? AND ID_EMPRESA = ?
                        ");
                        $stmt_update_productos->bind_param("ssi", $nombre_categoria, $categoria['nombre_categoria'], $id_empresa);
                        $stmt_update_productos->execute();
                    }
                    
                    $success = 'Categoría actualizada exitosamente';
                    $categoria['nombre_categoria'] = $nombre_categoria;
                    $categoria['descripcion'] = $descripcion;
                } else {
                    $error = 'Error al actualizar la categoría';
                }
            }
        }
    }
    
    // Obtener estadísticas de la categoría
    $stmt_stats = $conn->prepare("
        SELECT COUNT(*) as total_productos
        FROM t_productos 
        WHERE categoria = ? AND ID_EMPRESA = ?
    ");
    $stmt_stats->bind_param("si", $categoria['nombre_categoria'], $id_empresa);
    $stmt_stats->execute();
    $stats = $stmt_stats->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría - HyBox Cloud</title>
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
                    <h1 class="page-title">Editar Categoría</h1>
                    <p class="page-subtitle">Modifica la información de la categoría</p>
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
                                       value="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>" 
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
                                          placeholder="Describe brevemente qué tipo de productos incluirá esta categoría..."><?php echo htmlspecialchars($categoria['descripcion']); ?></textarea>
                                <small class="form-help">Opcional: ayuda a identificar el propósito de la categoría</small>
                            </div>
                            

                            
                            <div class="btn-container">
                                <button type="button" class="btn-secondary" onclick="window.location.href='categorias.php'">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="form-section">
                        <h3>Información de la Categoría</h3>
                        <div class="info-box">
                            <div class="info-item">
                                <span class="info-label">Productos asociados:</span>
                                <span class="info-value"><?php echo $stats['total_productos']; ?> productos</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Estado:</span>
                                <span class="info-value">
                                    <span class="status-badge <?php echo $stats['total_productos'] > 0 ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $stats['total_productos'] > 0 ? 'Activo' : 'Inactiva'; ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha de creación:</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($categoria['fecha_creacion'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Última actualización:</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($categoria['fecha_actualizacion'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="alert alert-info" style="margin-top: 20px;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Información:</strong> El estado de la categoría se determina automáticamente según si tiene productos asociados. No puedes modificar el estado manualmente.
                        </div>
                        
                        <?php if ($stats['total_productos'] > 0): ?>
                        <div class="alert alert-warning" style="margin-top: 20px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Advertencia:</strong> Esta categoría tiene <?php echo $stats['total_productos']; ?> productos asociados. Si cambias el nombre de la categoría, todos los productos se actualizarán automáticamente.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            initMobileMenu();
        });
    </script>
</body>
</html> 