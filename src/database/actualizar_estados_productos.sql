-- Script para actualizar los estados de productos
-- Cambiar valores de "inactivo" a "stock_bajo" para mayor lógica

-- Primero, actualizar los productos que tengan estado "inactivo" a "stock_bajo"
UPDATE t_productos SET estado = 'stock_bajo' WHERE estado = 'inactivo';

-- Verificar que no queden productos con estado "inactivo"
SELECT COUNT(*) as productos_inactivos FROM t_productos WHERE estado = 'inactivo';

-- Mostrar la distribución actual de estados
SELECT 
    estado,
    COUNT(*) as cantidad
FROM t_productos 
GROUP BY estado 
ORDER BY estado;

-- Si la tabla t_productos no existe, aquí está la estructura recomendada:
/*
CREATE TABLE t_productos (
    ID_PRODUCTO INT AUTO_INCREMENT PRIMARY KEY,
    ID_EMPRESA INT NOT NULL,
    nombre_producto VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(50),
    precio DECIMAL(10,2) NOT NULL,
    precio_compra DECIMAL(10,2) DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT DEFAULT 0,
    codigo_barras VARCHAR(50),
    codigo_interno VARCHAR(50),
    proveedor VARCHAR(100),
    ubicacion VARCHAR(100),
    imagen VARCHAR(255),
    estado ENUM('activo', 'stock_bajo', 'agotado') DEFAULT 'activo',
    creado_por INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_EMPRESA) REFERENCES t_empresa(ID_EMPRESA) ON DELETE CASCADE,
    FOREIGN KEY (creado_por) REFERENCES t_usuarios(ID_USUARIO) ON DELETE SET NULL
);
*/ 