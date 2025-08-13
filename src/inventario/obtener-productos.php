<?php
session_start();
require_once '../../config/db.php';
require_once '../auth/verificaciones-sesion.php';

// Verificar sesión
if (!verificarSesion()) {
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
    $categoria = $_GET['categoria'] ?? '';
    $estado = $_GET['estado'] ?? '';
    $orden = $_GET['orden'] ?? 'nombre';
    $direccion = $_GET['direccion'] ?? 'ASC';
    $pagina = (int)($_GET['pagina'] ?? 1);
    $por_pagina = (int)($_GET['por_pagina'] ?? 50);
    $offset = ($pagina - 1) * $por_pagina;

    // Construir la consulta base
    $sql = "SELECT 
                p.*,
                c.nombre as categoria_nombre
            FROM t_productos p
            LEFT JOIN t_categorias c ON p.ID_CATEGORIA = c.ID_CATEGORIA
            WHERE p.ID_EMPRESA = ?";
    
    $params = [$id_empresa];

    // Aplicar filtros
    if (!empty($busqueda)) {
        $sql .= " AND (p.nombre LIKE ? OR p.codigo LIKE ? OR p.descripcion LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param]);
    }

    if (!empty($categoria)) {
        $sql .= " AND p.ID_CATEGORIA = ?";
        $params[] = $categoria;
    }

    if (!empty($estado)) {
        $sql .= " AND p.estado = ?";
        $params[] = $estado;
    }

    // Contar total de registros
    $sql_count = "SELECT COUNT(*) as total FROM t_productos p WHERE p.ID_EMPRESA = ?";
    $count_params = [$id_empresa];

    if (!empty($busqueda)) {
        $sql_count .= " AND (p.nombre LIKE ? OR p.codigo LIKE ? OR p.descripcion LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $count_params = array_merge($count_params, [$busqueda_param, $busqueda_param, $busqueda_param]);
    }

    if (!empty($categoria)) {
        $sql_count .= " AND p.ID_CATEGORIA = ?";
        $count_params[] = $categoria;
    }

    if (!empty($estado)) {
        $sql_count .= " AND p.estado = ?";
        $count_params[] = $estado;
    }

    // Ejecutar consulta de conteo
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute($count_params);
    $total_registros = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

    // Ordenar y limitar
    $sql .= " ORDER BY p.$orden $direccion LIMIT $offset, $por_pagina";

    // Ejecutar consulta principal
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        'productos' => $productos,
        'paginacion' => $paginacion
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?> 