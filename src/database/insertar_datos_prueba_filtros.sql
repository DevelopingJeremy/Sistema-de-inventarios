-- Script para insertar datos de prueba para los filtros
-- Productos con diferentes categorías, estados y proveedores

-- Productos para empresa 13 (Tienda de Electrónicos)
INSERT INTO t_productos (ID_EMPRESA, nombre_producto, categoria, descripcion, precio, precio_compra, stock, stock_minimo, codigo_barras, codigo_interno, proveedor, ubicacion, estado, imagen, creado_por, fecha_creacion) VALUES
(13, 'Laptop HP Pavilion', 'Electrónicos', 'Laptop HP Pavilion 15.6" Intel Core i5', 450000, 380000, 15, 5, '7891234567890', 'LAP001', 'ElectroMax S.A.', 'Estante A-1', 'activo', NULL, NULL, NOW()),
(13, 'Mouse Inalámbrico Logitech', 'Accesorios', 'Mouse inalámbrico Logitech M185', 15000, 12000, 50, 10, '7891234567891', 'MOU001', 'TechCorp', 'Estante B-2', 'activo', NULL, NULL, NOW()),
(13, 'Teclado Mecánico Corsair', 'Accesorios', 'Teclado mecánico Corsair K70 RGB', 85000, 70000, 25, 8, '7891234567892', 'TEC001', 'Importadora Central', 'Estante B-3', 'activo', NULL, NULL, NOW()),
(13, 'Monitor Samsung 24"', 'Electrónicos', 'Monitor Samsung 24" Full HD', 120000, 100000, 0, 4, '7891234567893', 'MON001', 'Distribuidora Norte', 'Estante A-2', 'agotado', NULL, NULL, NOW()),
(13, 'Webcam HD Logitech', 'Accesorios', 'Webcam Logitech C920 HD 1080p', 45000, 38000, 3, 8, '7891234567894', 'WEB001', 'Proveedor Express', 'Estante C-1', 'stock_bajo', NULL, NULL, NOW()),
(13, 'Auriculares Gaming Razer', 'Accesorios', 'Auriculares gaming Razer Kraken X', 65000, 55000, 20, 6, '7891234567895', 'AUR001', 'ElectroMax S.A.', 'Estante C-2', 'activo', NULL, NULL, NOW()),
(13, 'Disco Duro Externo 1TB', 'Accesorios', 'Disco duro externo Seagate 1TB', 35000, 28000, 40, 12, '7891234567896', 'DD001', 'TechCorp', 'Estante D-1', 'activo', NULL, NULL, NOW()),
(13, 'Cable HDMI 2m', 'Accesorios', 'Cable HDMI 2 metros, alta velocidad', 8000, 6000, 100, 20, '7891234567897', 'CAB001', 'Importadora Central', 'Estante D-2', 'activo', NULL, NULL, NOW()),
(13, 'Cargador USB-C', 'Accesorios', 'Cargador USB-C 65W, carga rápida', 25000, 20000, 35, 10, '7891234567898', 'CAR001', 'Distribuidora Norte', 'Estante D-3', 'activo', NULL, NULL, NOW()),
(13, 'Pendrive 32GB Kingston', 'Accesorios', 'Pendrive Kingston 32GB USB 3.0', 12000, 10000, 60, 15, '7891234567899', 'PEN001', 'Proveedor Express', 'Estante E-1', 'activo', NULL, NULL, NOW()),
(13, 'Laptop Dell Inspiron', 'Electrónicos', 'Laptop Dell Inspiron 14" AMD Ryzen 5', 380000, 320000, 0, 3, '7891234567900', 'LAP002', 'ElectroMax S.A.', 'Estante A-3', 'agotado', NULL, NULL, NOW()),
(13, 'Tablet Samsung Galaxy', 'Electrónicos', 'Tablet Samsung Galaxy Tab A 10.1"', 180000, 150000, 2, 5, '7891234567901', 'TAB001', 'TechCorp', 'Estante A-4', 'stock_bajo', NULL, NULL, NOW()),
(13, 'Impresora HP LaserJet', 'Electrónicos', 'Impresora HP LaserJet Pro M404n', 220000, 180000, 6, 2, '7891234567902', 'IMP001', 'Importadora Central', 'Estante F-1', 'activo', NULL, NULL, NOW()),
(13, 'Router WiFi TP-Link', 'Electrónicos', 'Router WiFi TP-Link Archer C6', 45000, 38000, 25, 8, '7891234567903', 'ROU001', 'Distribuidora Norte', 'Estante F-2', 'activo', NULL, NULL, NOW()),
(13, 'Altavoces Bluetooth JBL', 'Accesorios', 'Altavoces Bluetooth JBL Flip 5', 55000, 45000, 18, 6, '7891234567904', 'ALT001', 'Proveedor Express', 'Estante C-3', 'activo', NULL, NULL, NOW());

-- Productos para empresa 14 (Farmacia)
INSERT INTO t_productos (ID_EMPRESA, nombre_producto, categoria, descripcion, precio, precio_compra, stock, stock_minimo, codigo_barras, codigo_interno, proveedor, ubicacion, estado, imagen, creado_por, fecha_creacion) VALUES
(14, 'Paracetamol 500mg', 'Farmacéuticos', 'Paracetamol 500mg, 20 tabletas', 2500, 1800, 100, 20, '7891234568001', 'PAR001', 'Farmacéutica Nacional', 'Estante A-1', 'activo', NULL, NULL, NOW()),
(14, 'Ibuprofeno 400mg', 'Farmacéuticos', 'Ibuprofeno 400mg, 30 tabletas', 3200, 2400, 80, 15, '7891234568002', 'IBU001', 'Distribuidora Médica', 'Estante A-2', 'activo', NULL, NULL, NOW()),
(14, 'Vitamina C 1000mg', 'Suplementos', 'Vitamina C 1000mg, 60 tabletas', 4500, 3500, 60, 12, '7891234568003', 'VIT001', 'Importadora de Salud', 'Estante B-1', 'activo', NULL, NULL, NOW()),
(14, 'Omega 3 1000mg', 'Suplementos', 'Omega 3 1000mg, 90 cápsulas', 8500, 6500, 45, 10, '7891234568004', 'OME001', 'Proveedor Farmacéutico', 'Estante B-2', 'activo', NULL, NULL, NOW()),
(14, 'Shampoo Anticaspa', 'Higiene', 'Shampoo anticaspa Head & Shoulders 400ml', 3500, 2800, 30, 8, '7891234568005', 'SHA001', 'Distribuidora de Cosméticos', 'Estante C-1', 'activo', NULL, NULL, NOW()),
(14, 'Jabón Antibacterial', 'Higiene', 'Jabón antibacterial Dial 90g', 1200, 900, 75, 15, '7891234568006', 'JAB001', 'Farmacéutica Nacional', 'Estante C-2', 'activo', NULL, NULL, NOW()),
(14, 'Pasta Dental Colgate', 'Higiene', 'Pasta dental Colgate Total 150g', 2800, 2200, 50, 12, '7891234568007', 'PAS001', 'Distribuidora Médica', 'Estante C-3', 'activo', NULL, NULL, NOW()),
(14, 'Desodorante Rexona', 'Higiene', 'Desodorante Rexona Clinical 50ml', 4200, 3400, 40, 10, '7891234568008', 'DES001', 'Importadora de Salud', 'Estante C-4', 'activo', NULL, NULL, NOW()),
(14, 'Termómetro Digital', 'Equipos Médicos', 'Termómetro digital Braun', 8500, 6800, 25, 6, '7891234568009', 'TER001', 'Proveedor Farmacéutico', 'Estante D-1', 'activo', NULL, NULL, NOW()),
(14, 'Tensiómetro Digital', 'Equipos Médicos', 'Tensiómetro digital Omron', 45000, 36000, 8, 3, '7891234568010', 'TEN001', 'Distribuidora de Cosméticos', 'Estante D-2', 'activo', NULL, NULL, NOW()),
(14, 'Pañales Huggies T4', 'Cuidado Infantil', 'Pañales Huggies Talla 4, 44 unidades', 8500, 6800, 35, 10, '7891234568011', 'PAÑ001', 'Farmacéutica Nacional', 'Estante E-1', 'activo', NULL, NULL, NOW()),
(14, 'Leche de Fórmula Enfamil', 'Cuidado Infantil', 'Leche de fórmula Enfamil 1, 400g', 12000, 9600, 20, 5, '7891234568012', 'LEC001', 'Distribuidora Médica', 'Estante E-2', 'activo', NULL, NULL, NOW()),
(14, 'Crema Hidratante Nivea', 'Cosméticos', 'Crema hidratante Nivea 200ml', 3800, 3000, 30, 8, '7891234568013', 'CRE001', 'Importadora de Salud', 'Estante F-1', 'activo', NULL, NULL, NOW()),
(14, 'Protector Solar SPF 50', 'Cosméticos', 'Protector solar Neutrogena SPF 50', 6500, 5200, 25, 6, '7891234568014', 'PRO001', 'Proveedor Farmacéutico', 'Estante F-2', 'activo', NULL, NULL, NOW()),
(14, 'Té Verde Natural', 'Bienestar', 'Té verde natural 20 bolsitas', 2800, 2200, 40, 10, '7891234568015', 'TE001', 'Distribuidora de Cosméticos', 'Estante G-1', 'activo', NULL, NULL, NOW()),
(14, 'Miel Pura 500g', 'Bienestar', 'Miel pura de abeja 500g', 4500, 3600, 15, 5, '7891234568016', 'MIE001', 'Farmacéutica Nacional', 'Estante G-2', 'activo', NULL, NULL, NOW()),
(14, 'Aspirina 100mg', 'Farmacéuticos', 'Aspirina 100mg, 30 tabletas', 2800, 2200, 60, 12, '7891234568017', 'ASP001', 'Distribuidora Médica', 'Estante A-3', 'activo', NULL, NULL, NOW()),
(14, 'Calcio + Vitamina D', 'Suplementos', 'Calcio + Vitamina D, 60 tabletas', 5500, 4400, 35, 8, '7891234568018', 'CAL001', 'Importadora de Salud', 'Estante B-3', 'activo', NULL, NULL, NOW()),
(14, 'Toallas Húmedas Huggies', 'Cuidado Infantil', 'Toallas húmedas Huggies 80 unidades', 3200, 2560, 25, 6, '7891234568019', 'TOA001', 'Proveedor Farmacéutico', 'Estante E-3', 'activo', NULL, NULL, NOW()),
(14, 'Perfume Avon', 'Cosméticos', 'Perfume Avon Far Away, 50ml', 8500, 6800, 12, 4, '7891234568020', 'PER001', 'Distribuidora de Cosméticos', 'Estante F-3', 'activo', NULL, NULL, NOW()),
(14, 'Producto Agotado Test', 'Farmacéuticos', 'Producto para probar filtro agotado', 1000, 800, 0, 5, '7891234568021', 'TEST001', 'Farmacéutica Nacional', 'Estante Z-1', 'agotado', NULL, NULL, NOW()),
(14, 'Producto Stock Bajo Test', 'Suplementos', 'Producto para probar filtro stock bajo', 2000, 1600, 2, 10, '7891234568022', 'TEST002', 'Distribuidora Médica', 'Estante Z-2', 'stock_bajo', NULL, NULL, NOW());

-- Verificar los datos insertados
SELECT 
    'Empresa 13' as empresa,
    COUNT(*) as total_productos,
    COUNT(DISTINCT categoria) as categorias_unicas,
    COUNT(DISTINCT proveedor) as proveedores_unicos,
    COUNT(CASE WHEN stock = 0 THEN 1 END) as productos_agotados,
    COUNT(CASE WHEN stock <= stock_minimo AND stock > 0 THEN 1 END) as productos_stock_bajo
FROM t_productos 
WHERE ID_EMPRESA = 13

UNION ALL

SELECT 
    'Empresa 14' as empresa,
    COUNT(*) as total_productos,
    COUNT(DISTINCT categoria) as categorias_unicas,
    COUNT(DISTINCT proveedor) as proveedores_unicos,
    COUNT(CASE WHEN stock = 0 THEN 1 END) as productos_agotados,
    COUNT(CASE WHEN stock <= stock_minimo AND stock > 0 THEN 1 END) as productos_stock_bajo
FROM t_productos 
WHERE ID_EMPRESA = 14; 