<?php
require_once '../../config/db.php';

// Verificar sesión simple
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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
    header('Location: clientes.php?error=1&mensaje=' . urlencode('Usuario no tiene empresa registrada'));
    exit;
}
$id_cliente = (int)($_GET['id'] ?? 0);

if ($id_cliente <= 0) {
    header('Location: clientes.php?error=1&mensaje=' . urlencode('ID de cliente inválido'));
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
        header('Location: clientes.php?error=1&mensaje=' . urlencode('Cliente no encontrado'));
        exit;
    }

    // Obtener estadísticas del cliente
    $sql_stats = "SELECT 
                    COUNT(*) as total_ventas,
                    COALESCE(SUM(total), 0) as total_gastado,
                    COALESCE(AVG(total), 0) as promedio_compra,
                    MAX(fecha_venta) as ultima_venta
                  FROM t_ventas 
                  WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?";
    $stmt_stats = $conn->prepare($sql_stats);
    $stmt_stats->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_stats->execute();
    $result_stats = $stmt_stats->get_result();
    $stats = $result_stats->fetch_assoc();

    // Obtener historial de ventas
    $sql_ventas = "SELECT 
                     v.*,
                     COUNT(vd.ID_DETALLE) as total_productos
                   FROM t_ventas v
                   LEFT JOIN t_ventas_detalle vd ON v.ID_VENTA = vd.ID_VENTA
                   WHERE v.ID_CLIENTE = ? AND v.ID_EMPRESA = ?
                   GROUP BY v.ID_VENTA
                   ORDER BY v.fecha_venta DESC
                   LIMIT 10";
    $stmt_ventas = $conn->prepare($sql_ventas);
    $stmt_ventas->bind_param("ii", $id_cliente, $id_empresa);
    $stmt_ventas->execute();
    $result_ventas = $stmt_ventas->get_result();
    $ventas = [];
    while ($row = $result_ventas->fetch_assoc()) {
        $ventas[] = $row;
    }

} catch (Exception $e) {
    $error = 'Error al cargar los datos del cliente';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Cliente - HyBox</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <?php include('../../includes/sidebar.php'); ?>
        
        <div class="main-content">
        <?php include("../../includes/header.php"); ?>
            
            <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h1><i class="fas fa-user"></i> Detalles del Cliente</h1>
                    <p>Información completa y historial de compras: <strong><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></strong></p>
                </div>
                <div class="page-actions">
                    <a href="editar-cliente.php?id=<?= $id_cliente ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar Cliente
                    </a>
                    <a href="clientes.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif (isset($_GET['success']) && isset($_GET['mensaje'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_GET['mensaje']) ?>
                </div>
            <?php else: ?>
                <!-- Información del Cliente -->
                <div class="cliente-details">
                    <div class="details-grid">
                        <!-- Información Personal -->
                        <div class="detail-card">
                            <div class="card-header">
                                <h3><i class="fas fa-user-circle"></i> Información Personal</h3>
                            </div>
                            <div class="card-content">
                                <div class="info-row">
                                    <span class="label">Nombre completo:</span>
                                    <span class="value"><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Email:</span>
                                    <span class="value"><?= htmlspecialchars($cliente['email'] ?: 'No especificado') ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Teléfono:</span>
                                    <span class="value"><?= htmlspecialchars($cliente['telefono'] ?: 'No especificado') ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Dirección:</span>
                                    <span class="value"><?= htmlspecialchars($cliente['direccion'] ?: 'No especificada') ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Fecha de registro:</span>
                                    <span class="value"><?= date('d/m/Y H:i', strtotime($cliente['fecha_registro'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Información Comercial -->
                        <div class="detail-card">
                            <div class="card-header">
                                <h3><i class="fas fa-chart-line"></i> Información Comercial</h3>
                            </div>
                            <div class="card-content">
                                <div class="info-row">
                                    <span class="label">Tipo de cliente:</span>
                                    <span class="value">
                                        <span class="category-badge <?= $cliente['tipo_cliente'] ?>">
                                            <?= ucfirst($cliente['tipo_cliente']) ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Método de pago preferido:</span>
                                    <span class="value"><?= ucfirst($cliente['metodo_pago_preferido']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Límite de crédito:</span>
                                    <span class="value">₡<?= number_format($cliente['limite_credito'], 2) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Descuento:</span>
                                    <span class="value"><?= $cliente['descuento'] ?>%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas -->
                        <div class="detail-card">
                            <div class="card-header">
                                <h3><i class="fas fa-chart-bar"></i> Estadísticas</h3>
                            </div>
                            <div class="card-content">
                                <div class="info-row">
                                    <span class="label">Total de ventas:</span>
                                    <span class="value"><?= $stats['total_ventas'] ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Total gastado:</span>
                                    <span class="value">₡<?= number_format($stats['total_gastado'], 2) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Promedio por compra:</span>
                                    <span class="value">₡<?= number_format($stats['promedio_compra'], 2) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Última compra:</span>
                                    <span class="value">
                                        <?= $stats['ultima_venta'] ? date('d/m/Y H:i', strtotime($stats['ultima_venta'])) : 'Nunca' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <?php if (!empty($cliente['notas'])): ?>
                        <div class="detail-card">
                            <div class="card-header">
                                <h3><i class="fas fa-sticky-note"></i> Notas</h3>
                            </div>
                            <div class="card-content">
                                <p><?= nl2br(htmlspecialchars($cliente['notas'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Historial de Ventas -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Historial de Ventas Recientes</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($ventas)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>Este cliente aún no tiene ventas registradas</p>
                                </div>
                            <?php else: ?>
                                <div class="table-container">
                                    <table class="products-table">
                                        <thead>
                                            <tr>
                                                <th>Número</th>
                                                <th>Fecha</th>
                                                <th>Total</th>
                                                <th>Estado</th>
                                                <th>Método de Pago</th>
                                                <th>Productos</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ventas as $venta): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($venta['numero_venta']) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($venta['fecha_venta'])) ?></td>
                                                    <td>$<?= number_format($venta['total'], 2) ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?= $venta['estado'] ?>">
                                                            <?= ucfirst($venta['estado']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= ucfirst($venta['metodo_pago']) ?></td>
                                                    <td><?= $venta['total_productos'] ?> productos</td>
                                                    <td>
                                                        <a href="ver-venta.php?id=<?= $venta['ID_VENTA'] ?>" 
                                                           class="btn btn-sm btn-primary" title="Ver detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html> 