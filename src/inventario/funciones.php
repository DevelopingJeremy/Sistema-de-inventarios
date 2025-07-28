<?php
/**
 * Funciones para obtener categorías y proveedores de la empresa actual
 */

/**
 * Obtiene las categorías de la empresa actual
 * @param mysqli $conn Conexión a la base de datos
 * @param int $id_usuario ID del usuario actual
 * @return array Array de categorías
 */
function obtenerCategoriasEmpresa($conn, $id_usuario) {
    $stmt = $conn->prepare("
        SELECT c.ID_CATEGORIA, c.nombre_categoria, c.descripcion, c.color
        FROM t_categorias c
        INNER JOIN t_empresa e ON c.ID_EMPRESA = e.ID_EMPRESA
        WHERE e.ID_DUEÑO = ? AND c.estado = 'activo'
        ORDER BY c.nombre_categoria
    ");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    
    return $categorias;
}

/**
 * Obtiene los proveedores de la empresa actual
 * @param mysqli $conn Conexión a la base de datos
 * @param int $id_usuario ID del usuario actual
 * @return array Array de proveedores
 */
function obtenerProveedoresEmpresa($conn, $id_usuario) {
    $stmt = $conn->prepare("
        SELECT p.ID_PROVEEDOR, p.nombre_proveedor, p.contacto, p.telefono, p.email
        FROM t_proveedores p
        INNER JOIN t_empresa e ON p.ID_EMPRESA = e.ID_EMPRESA
        WHERE e.ID_DUEÑO = ? AND p.estado = 'activo'
        ORDER BY p.nombre_proveedor
    ");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $proveedores = [];
    while ($row = $result->fetch_assoc()) {
        $proveedores[] = $row;
    }
    
    return $proveedores;
}

/**
 * Obtiene una categoría específica por ID
 * @param mysqli $conn Conexión a la base de datos
 * @param int $id_categoria ID de la categoría
 * @param int $id_usuario ID del usuario actual
 * @return array|null Datos de la categoría o null si no existe
 */
function obtenerCategoriaPorId($conn, $id_categoria, $id_usuario) {
    $stmt = $conn->prepare("
        SELECT c.ID_CATEGORIA, c.nombre_categoria, c.descripcion, c.color
        FROM t_categorias c
        INNER JOIN t_empresa e ON c.ID_EMPRESA = e.ID_EMPRESA
        WHERE c.ID_CATEGORIA = ? AND e.ID_DUEÑO = ? AND c.estado = 'activo'
    ");
    $stmt->bind_param("ii", $id_categoria, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Obtiene un proveedor específico por ID
 * @param mysqli $conn Conexión a la base de datos
 * @param int $id_proveedor ID del proveedor
 * @param int $id_usuario ID del usuario actual
 * @return array|null Datos del proveedor o null si no existe
 */
function obtenerProveedorPorId($conn, $id_proveedor, $id_usuario) {
    $stmt = $conn->prepare("
        SELECT p.ID_PROVEEDOR, p.nombre_proveedor, p.contacto, p.telefono, p.email
        FROM t_proveedores p
        INNER JOIN t_empresa e ON p.ID_EMPRESA = e.ID_EMPRESA
        WHERE p.ID_PROVEEDOR = ? AND e.ID_DUEÑO = ? AND p.estado = 'activo'
    ");
    $stmt->bind_param("ii", $id_proveedor, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}
?> 