<?php
session_start();
require_once('../../config/db.php');

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
    $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
    $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
    $metodo_pago = isset($_GET['metodo_pago']) ? $_GET['metodo_pago'] : '';
    $orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_desc';
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $por_pagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
    $offset = ($pagina - 1) * $por_pagina;

    // Construir la consulta base
    $sql = "SELECT 
                v.ID_VENTA,
                v.fecha_venta,
                v.total,
                v.metodo_pago,
                v.estado,
                v.referencia_pago,
                v.notas,
                c.nombre as nombre_cliente,
                c.apellido as apellido_cliente,
                c.email as email_cliente,
                c.telefono as telefono_cliente,
                COUNT(vd.ID_VENTA_DETALLE) as cantidad_productos
            FROM t_ventas v
            LEFT JOIN t_clientes c ON v.ID_CLIENTE = c.ID_CLIENTE
            LEFT JOIN t_ventas_detalle vd ON v.ID_VENTA = vd.ID_VENTA
            WHERE v.ID_EMPRESA = ?";

    $params = [$id_empresa];
    $types = "i";

    // Aplicar filtros
    if (!empty($busqueda)) {
        $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.email LIKE ? OR v.ID_VENTA LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param]);
        $types .= "ssss";
    }

    if (!empty($estado)) {
        $sql .= " AND v.estado = ?";
        $params[] = $estado;
        $types .= "s";
    }

    if (!empty($metodo_pago)) {
        $sql .= " AND v.metodo_pago = ?";
        $params[] = $metodo_pago;
        $types .= "s";
    }

    // Agrupar para contar productos
    $sql .= " GROUP BY v.ID_VENTA";

    // Aplicar ordenamiento
    switch ($orden) {
        case 'fecha_asc':
            $sql .= " ORDER BY v.fecha_venta ASC";
            break;
        case 'total_desc':
            $sql .= " ORDER BY v.total DESC";
            break;
        case 'total_asc':
            $sql .= " ORDER BY v.total ASC";
            break;
        default:
            $sql .= " ORDER BY v.fecha_venta DESC";
    }

    // Obtener total de registros para paginación
    $sql_count = "SELECT COUNT(DISTINCT v.ID_VENTA) as total FROM t_ventas v
                  LEFT JOIN t_clientes c ON v.ID_CLIENTE = c.ID_CLIENTE
                  WHERE v.ID_EMPRESA = ?";
    
    $params_count = [$id_empresa];
    
    if (!empty($busqueda)) {
        $sql_count .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.email LIKE ? OR v.ID_VENTA LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $params_count = array_merge($params_count, [$busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param]);
    }
    
    if (!empty($estado)) {
        $sql_count .= " AND v.estado = ?";
        $params_count[] = $estado;
    }
    
    if (!empty($metodo_pago)) {
        $sql_count .= " AND v.metodo_pago = ?";
        $params_count[] = $metodo_pago;
    }

    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param(str_repeat("s", count($params_count)), ...$params_count);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_registros = $result_count->fetch_assoc()['total'];

    // Aplicar paginación
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $por_pagina;
    $params[] = $offset;
    $types .= "ii";

    // Ejecutar consulta principal
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $ventas = [];
    while ($row = $result->fetch_assoc()) {
        $ventas[] = [
            'id' => $row['ID_VENTA'],
            'cliente' => $row['nombre_cliente'] . ' ' . $row['apellido_cliente'],
            'email_cliente' => $row['email_cliente'],
            'telefono_cliente' => $row['telefono_cliente'],
            'fecha' => date('d/m/Y H:i', strtotime($row['fecha_venta'])),
            'fecha_original' => $row['fecha_venta'],
            'productos' => $row['cantidad_productos'] . ' productos',
            'total' => number_format($row['total'], 2),
            'total_original' => $row['total'],
            'metodo_pago' => $row['metodo_pago'],
            'estado' => $row['estado'],
            'referencia' => $row['referencia_pago'],
            'notas' => $row['notas']
        ];
    }

    // Calcular información de paginación
    $total_paginas = ceil($total_registros / $por_pagina);
    $pagina_actual = $pagina;

    echo json_encode([
        'success' => true,
        'ventas' => $ventas,
        'paginacion' => [
            'total_registros' => $total_registros,
            'total_paginas' => $total_paginas,
            'pagina_actual' => $pagina_actual,
            'por_pagina' => $por_pagina
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al obtener las ventas: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 