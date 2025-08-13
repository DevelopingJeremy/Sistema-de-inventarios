<?php
session_start();
require_once '../../config/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
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
    http_response_code(400);
    echo json_encode(['error' => 'Usuario no tiene empresa registrada']);
    exit;
}

try {
    // Parámetros de filtrado y paginación
    $busqueda = $_GET['busqueda'] ?? '';
    $tipo_cliente = $_GET['tipo_cliente'] ?? '';
    $estado = $_GET['estado'] ?? '';
    $orden = $_GET['orden'] ?? 'fecha_registro';
    $direccion = $_GET['direccion'] ?? 'DESC';
    $pagina = (int)($_GET['pagina'] ?? 1);
    $por_pagina = (int)($_GET['por_pagina'] ?? 10);
    $offset = ($pagina - 1) * $por_pagina;

    // Mapear campos de ordenamiento
    $orden_campos = [
        'nombre' => 'c.nombre',
        'fecha' => 'c.fecha_registro',
        'compras' => 'total_ventas',
        'valor' => 'total_gastado'
    ];
    
    $campo_orden = $orden_campos[$orden] ?? 'c.fecha_registro';

    // Construir la consulta base
    $sql = "SELECT 
                c.*,
                COUNT(v.ID_VENTA) as total_ventas,
                COALESCE(SUM(v.total), 0) as total_gastado
            FROM t_clientes c
            LEFT JOIN t_ventas v ON c.ID_CLIENTE = v.ID_CLIENTE
            WHERE c.ID_EMPRESA = ?";
    
    $params = [$id_empresa];

    // Aplicar filtros
    if (!empty($busqueda)) {
        $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.email LIKE ? OR c.telefono LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param]);
    }

    if (!empty($tipo_cliente)) {
        $sql .= " AND c.tipo_cliente = ?";
        $params[] = $tipo_cliente;
    }

    if (!empty($estado)) {
        $sql .= " AND c.estado = ?";
        $params[] = $estado;
    }

    // Agrupar para obtener estadísticas
    $sql .= " GROUP BY c.ID_CLIENTE";

    // Contar total de registros
    $sql_count = "SELECT COUNT(DISTINCT c.ID_CLIENTE) as total FROM t_clientes c WHERE c.ID_EMPRESA = ?";
    $count_params = [$id_empresa];

    if (!empty($busqueda)) {
        $sql_count .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.email LIKE ? OR c.telefono LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $count_params = array_merge($count_params, [$busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param]);
    }

    if (!empty($tipo_cliente)) {
        $sql_count .= " AND c.tipo_cliente = ?";
        $count_params[] = $tipo_cliente;
    }

    if (!empty($estado)) {
        $sql_count .= " AND c.estado = ?";
        $count_params[] = $estado;
    }

    // Ejecutar consulta de conteo
    $stmt_count = $conn->prepare($sql_count);
    
    // Crear tipos de parámetros para la consulta de conteo
    $tipos_count = 'i' . str_repeat('s', count($count_params) - 1);
    $stmt_count->bind_param($tipos_count, ...$count_params);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_registros = $result_count->fetch_assoc()['total'];
    $stmt_count->close();

    // Ordenar y limitar
    $sql .= " ORDER BY $campo_orden $direccion LIMIT $offset, $por_pagina";

    // Ejecutar consulta principal
    $stmt = $conn->prepare($sql);
    
    // Crear tipos de parámetros (primero 'i' para id_empresa, luego 's' para los demás)
    $tipos = 'i' . str_repeat('s', count($params) - 1);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
    $stmt->close();

    // Calcular información de paginación
    $total_paginas = ceil($total_registros / $por_pagina);
    $paginacion = [
        'pagina_actual' => $pagina,
        'total_paginas' => $total_paginas,
        'total_registros' => $total_registros,
        'por_pagina' => $por_pagina,
        'desde' => $offset + 1,
        'hasta' => min($offset + $por_pagina, $total_registros)
    ];

    echo json_encode([
        'success' => true,
        'clientes' => $clientes,
        'paginacion' => $paginacion
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}

$conn->close();
?> 