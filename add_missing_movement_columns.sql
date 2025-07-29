-- Agregar columnas faltantes a la tabla t_movimientos_inventario
ALTER TABLE t_movimientos_inventario 
ADD COLUMN precio_unitario DECIMAL(10,2) DEFAULT 0.00 AFTER cantidad,
ADD COLUMN referencia VARCHAR(100) DEFAULT NULL AFTER valor_movimiento,
ADD COLUMN proveedor_cliente VARCHAR(100) DEFAULT NULL AFTER referencia,
ADD COLUMN documento VARCHAR(100) DEFAULT NULL AFTER proveedor_cliente,
ADD COLUMN observaciones TEXT DEFAULT NULL AFTER documento;

-- Agregar columnas faltantes a la tabla t_ajustes_inventario
ALTER TABLE t_ajustes_inventario 
ADD COLUMN responsable VARCHAR(100) DEFAULT NULL AFTER cantidad,
ADD COLUMN tipo_diferencia VARCHAR(50) DEFAULT NULL AFTER responsable,
ADD COLUMN documento_respaldo VARCHAR(100) DEFAULT NULL AFTER tipo_diferencia,
ADD COLUMN observaciones TEXT DEFAULT NULL AFTER documento_respaldo; 