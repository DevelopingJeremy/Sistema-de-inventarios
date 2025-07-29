<?php
require_once('../../vendor/fpdf186/fpdf.php');
include('../../config/db.php');
require_once('../../src/auth/sesion/verificaciones-sesion.php');

// Verificar sesión usando la misma función que el dashboard
validTotales('../../public/sesion/iniciar-sesion.php', '../../public/sesion/envio-correo.php', '../../public/empresa/registrar-empresa.php');

// Obtener ID de la empresa del usuario
$id_usuario = $_SESSION['id_usuario'];
$stmt_empresa = $conn->prepare("SELECT ID_EMPRESA FROM t_usuarios WHERE ID_USUARIO = ?");
$stmt_empresa->bind_param("i", $id_usuario);
$stmt_empresa->execute();
$result_empresa = $stmt_empresa->get_result();

if ($result_empresa->num_rows > 0) {
    $empresa_data = $result_empresa->fetch_assoc();
    $id_empresa = $empresa_data['ID_EMPRESA'];
} else {
    // Esto no debería pasar porque validTotales() ya verifica la empresa
    header('Location: ../../public/empresa/registrar-empresa.php');
    exit();
}

// Obtener datos de la empresa
$stmt_empresa_info = $conn->prepare("SELECT nombre_empresa FROM t_empresa WHERE ID_EMPRESA = ?");
$stmt_empresa_info->bind_param("i", $id_empresa);
$stmt_empresa_info->execute();
$empresa_info = $stmt_empresa_info->get_result()->fetch_assoc();

// Obtener datos del dashboard
try {
    // Estadísticas de productos
    $stmt_productos = $conn->prepare("
        SELECT 
            COUNT(*) as total_productos,
            COALESCE(SUM(stock), 0) as stock_total,
            SUM(CASE WHEN stock <= 5 AND stock > 0 THEN 1 ELSE 0 END) as productos_criticos,
            SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as productos_sin_stock,
            COALESCE(SUM(stock * precio), 0) as valor_total_inventario,
            COALESCE(AVG(precio), 0) as precio_promedio,
            COUNT(DISTINCT categoria) as total_categorias
        FROM t_productos 
        WHERE ID_EMPRESA = ?
    ");
    $stmt_productos->bind_param("i", $id_empresa);
    $stmt_productos->execute();
    $stats_productos = $stmt_productos->get_result()->fetch_assoc();

    // Estadísticas de categorías
    $stmt_categorias = $conn->prepare("
        SELECT 
            COUNT(DISTINCT categoria) as total_categorias,
            COUNT(DISTINCT CASE WHEN categoria IS NOT NULL AND categoria != '' THEN categoria END) as categorias_activas
        FROM t_productos 
        WHERE ID_EMPRESA = ? AND categoria IS NOT NULL AND categoria != ''
    ");
    $stmt_categorias->bind_param("i", $id_empresa);
    $stmt_categorias->execute();
    $stats_categorias = $stmt_categorias->get_result()->fetch_assoc();

    // Movimientos (últimos 30 días)
    $stmt_movimientos = $conn->prepare("
        SELECT 
            COUNT(*) as total_movimientos,
            SUM(CASE WHEN tipo_movimiento = 'entrada' THEN 1 ELSE 0 END) as entradas,
            SUM(CASE WHEN tipo_movimiento = 'salida' THEN 1 ELSE 0 END) as salidas,
            COALESCE(SUM(valor_movimiento), 0) as valor_total,
            COALESCE(AVG(valor_movimiento), 0) as valor_promedio
        FROM t_movimientos_inventario 
        WHERE ID_EMPRESA = ? 
        AND fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt_movimientos->bind_param("i", $id_empresa);
    $stmt_movimientos->execute();
    $stats_movimientos = $stmt_movimientos->get_result()->fetch_assoc();

    // Ajustes (últimos 30 días)
    $stmt_ajustes = $conn->prepare("
        SELECT 
            COUNT(*) as total_ajustes,
            SUM(CASE WHEN tipo_ajuste = 'positivo' THEN 1 ELSE 0 END) as ajustes_positivos,
            SUM(CASE WHEN tipo_ajuste = 'negativo' THEN 1 ELSE 0 END) as ajustes_negativos,
            COALESCE(SUM(valor_ajuste), 0) as valor_total_ajustes
        FROM t_ajustes_inventario 
        WHERE ID_EMPRESA = ? 
        AND fecha_ajuste >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt_ajustes->bind_param("i", $id_empresa);
    $stmt_ajustes->execute();
    $stats_ajustes = $stmt_ajustes->get_result()->fetch_assoc();

    // Productos más vendidos
    $stmt_top_productos = $conn->prepare("
        SELECT 
            p.nombre_producto,
            p.categoria,
            p.precio,
            COUNT(m.ID_MOVIMIENTO) as total_movimientos,
            SUM(m.cantidad) as cantidad_total,
            SUM(m.valor_movimiento) as valor_total,
            AVG(m.valor_movimiento) as valor_promedio
        FROM t_productos p
        LEFT JOIN t_movimientos_inventario m ON p.ID_PRODUCTO = m.ID_PRODUCTO 
            AND m.tipo_movimiento = 'salida'
            AND m.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        WHERE p.ID_EMPRESA = ?
        GROUP BY p.ID_PRODUCTO, p.nombre_producto, p.categoria, p.precio
        HAVING COUNT(m.ID_MOVIMIENTO) > 0
        ORDER BY COUNT(m.ID_MOVIMIENTO) DESC, SUM(m.cantidad) DESC
        LIMIT 5
    ");
    $stmt_top_productos->bind_param("i", $id_empresa);
    $stmt_top_productos->execute();
    $top_productos = $stmt_top_productos->get_result();

    // Datos para el gráfico (últimos 7 días)
    $stmt_ventas_grafico = $conn->prepare("
        SELECT 
            DATE(fecha_movimiento) as fecha,
            SUM(CASE WHEN tipo_movimiento = 'salida' THEN valor_movimiento ELSE 0 END) as ventas,
            SUM(CASE WHEN tipo_movimiento = 'entrada' THEN valor_movimiento ELSE 0 END) as compras,
            COUNT(CASE WHEN tipo_movimiento = 'salida' THEN 1 END) as num_ventas,
            COUNT(CASE WHEN tipo_movimiento = 'entrada' THEN 1 END) as num_compras
        FROM t_movimientos_inventario 
        WHERE ID_EMPRESA = ? 
        AND fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(fecha_movimiento)
        ORDER BY fecha ASC
    ");
    $stmt_ventas_grafico->bind_param("i", $id_empresa);
    $stmt_ventas_grafico->execute();
    $datos_grafico = $stmt_ventas_grafico->get_result();

} catch (Exception $e) {
    // En caso de error, usar valores por defecto
    $stats_productos = [
        'total_productos' => 0,
        'stock_total' => 0,
        'productos_criticos' => 0,
        'productos_sin_stock' => 0,
        'valor_total_inventario' => 0,
        'precio_promedio' => 0,
        'total_categorias' => 0
    ];
    $stats_categorias = ['total_categorias' => 0, 'categorias_activas' => 0];
    $stats_movimientos = [
        'total_movimientos' => 0,
        'entradas' => 0,
        'salidas' => 0,
        'valor_total' => 0,
        'valor_promedio' => 0
    ];
    $stats_ajustes = [
        'total_ajustes' => 0,
        'ajustes_positivos' => 0,
        'ajustes_negativos' => 0,
        'valor_total_ajustes' => 0
    ];
    $top_productos = null;
    $datos_grafico = null;
}

// Crear clase PDF personalizada
class DashboardPDF extends FPDF {
    function __construct() {
        parent::__construct();
        // Configurar para UTF-8
        $this->SetAutoPageBreak(true, 20);
        $this->SetMargins(10, 10, 10);
    }
    
    function Header() {
        global $empresa_info;
        
        // Logo (si existe)
        // $this->Image('logo.png', 10, 6, 30);
        
        // Título principal
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(59, 130, 246); // Azul
        $this->Cell(0, 12, utf8_decode('Dashboard de Inventario'), 0, 1, 'C');
        
        // Subtítulo
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 8, utf8_decode($empresa_info['nombre_empresa'] ?? 'Empresa'), 0, 1, 'C');
        
        // Fecha y hora
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, utf8_decode('Generado el: ' . date('d/m/Y H:i:s') . ' - Hybox Cloud'), 0, 1, 'C');
        
        // Línea separadora
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY() + 5, 200, $this->GetY() + 5);
        $this->Ln(15);
    }
    
    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        
        // Línea separadora
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        
        // Información del pie de página
        $this->Cell(0, 4, utf8_decode('Hybox Cloud - Sistema de Gestión de Inventario'), 0, 1, 'C');
        $this->Cell(0, 4, utf8_decode('Página ' . $this->PageNo() . '/{nb} - Generado automáticamente'), 0, 1, 'C');
        $this->Cell(0, 4, utf8_decode('Para soporte técnico, contacte a nuestro equipo'), 0, 1, 'C');
    }
    
    function ChapterTitle($title) {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(59, 130, 246); // Azul
        $this->SetFillColor(240, 248, 255); // Azul muy claro
        $this->Cell(0, 10, '  ' . utf8_decode($title), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(5);
    }
    
    function MetricCard($title, $value, $subtitle = '') {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, $title, 0, 1);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, $value, 0, 1);
        if ($subtitle) {
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 4, $subtitle, 0, 1);
        }
        $this->Ln(2);
    }
}

// Crear PDF
$pdf = new DashboardPDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Métricas principales
$pdf->ChapterTitle('Métricas Principales');

// Crear tabla de métricas
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(59, 130, 246); // Azul
$pdf->SetTextColor(255, 255, 255); // Blanco
$pdf->Cell(47, 8, utf8_decode('Métrica'), 1, 0, 'C', true);
$pdf->Cell(47, 8, utf8_decode('Valor'), 1, 0, 'C', true);
$pdf->Cell(47, 8, utf8_decode('Detalle'), 1, 0, 'C', true);
$pdf->Cell(47, 8, utf8_decode('Estado'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0); // Negro
$pdf->SetFillColor(248, 250, 252); // Gris muy claro

// Movimientos
$pdf->Cell(47, 8, utf8_decode('Movimientos'), 1, 0, 'L', true);
$pdf->Cell(47, 8, number_format($stats_movimientos['total_movimientos']), 1, 0, 'C', true);
$pdf->Cell(47, 8, 'CRC ' . number_format($stats_movimientos['valor_total']), 1, 0, 'C', true);
$pdf->Cell(47, 8, $stats_movimientos['salidas'] . ' ' . utf8_decode('salidas'), 1, 1, 'C', true);

// Inventario
$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(47, 8, utf8_decode('Inventario'), 1, 0, 'L', true);
$pdf->Cell(47, 8, number_format($stats_productos['stock_total']), 1, 0, 'C', true);
$pdf->Cell(47, 8, 'CRC ' . number_format($stats_productos['valor_total_inventario']), 1, 0, 'C', true);
$pdf->Cell(47, 8, $stats_productos['total_productos'] . ' ' . utf8_decode('productos'), 1, 1, 'C', true);

// Alertas
$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(47, 8, utf8_decode('Alertas'), 1, 0, 'L', true);
$pdf->Cell(47, 8, number_format($stats_productos['productos_criticos']), 1, 0, 'C', true);
$pdf->Cell(47, 8, $stats_productos['productos_sin_stock'] . ' ' . utf8_decode('sin stock'), 1, 0, 'C', true);
$pdf->Cell(47, 8, utf8_decode('Requieren atención'), 1, 1, 'C', true);

// Ajustes
$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(47, 8, utf8_decode('Ajustes'), 1, 0, 'L', true);
$pdf->Cell(47, 8, number_format($stats_ajustes['total_ajustes']), 1, 0, 'C', true);
$pdf->Cell(47, 8, 'CRC ' . number_format($stats_ajustes['valor_total_ajustes']), 1, 0, 'C', true);
$pdf->Cell(47, 8, $stats_ajustes['ajustes_positivos'] . ' ' . utf8_decode('positivos'), 1, 1, 'C', true);

$pdf->Ln(10);

// Productos más vendidos
$pdf->ChapterTitle('Productos Más Vendidos');

if ($top_productos && $top_productos->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(59, 130, 246); // Azul
    $pdf->SetTextColor(255, 255, 255); // Blanco
    $pdf->Cell(60, 8, utf8_decode('Producto'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode('Categoría'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode('Movimientos'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Cantidad'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Valor Total'), 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0); // Negro
    $rank = 1;
    while ($producto = $top_productos->fetch_assoc()) {
        $fill = ($rank % 2 == 0) ? true : false;
        $fillColor = ($rank % 2 == 0) ? 248 : 255; // Gris claro o blanco
        
        $pdf->SetFillColor($fillColor, $fillColor, $fillColor);
        $pdf->Cell(60, 8, $rank . '. ' . utf8_decode(substr($producto['nombre_producto'], 0, 25)), 1, 0, 'L', $fill);
        $pdf->Cell(30, 8, utf8_decode(substr($producto['categoria'] ?? 'Sin categoría', 0, 15)), 1, 0, 'C', $fill);
        $pdf->Cell(30, 8, number_format($producto['total_movimientos']), 1, 0, 'C', $fill);
        $pdf->Cell(35, 8, number_format($producto['cantidad_total']), 1, 0, 'C', $fill);
        $pdf->Cell(35, 8, 'CRC ' . number_format($producto['valor_total']), 1, 1, 'C', $fill);
        $rank++;
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode('No hay datos de productos vendidos disponibles'), 0, 1, 'C');
}

$pdf->Ln(10);

// Datos del gráfico
$pdf->ChapterTitle('Análisis de Ventas (Últimos 7 días)');

if ($datos_grafico && $datos_grafico->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(59, 130, 246); // Azul
    $pdf->SetTextColor(255, 255, 255); // Blanco
    $pdf->Cell(40, 8, utf8_decode('Fecha'), 1, 0, 'C', true);
    $pdf->Cell(40, 8, utf8_decode('Ventas'), 1, 0, 'C', true);
    $pdf->Cell(40, 8, utf8_decode('Compras'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Trans. Ventas'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Trans. Compras'), 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0); // Negro
    $rowCount = 0;
    while ($row = $datos_grafico->fetch_assoc()) {
        $fill = ($rowCount % 2 == 0) ? true : false;
        $fillColor = ($rowCount % 2 == 0) ? 248 : 255; // Gris claro o blanco
        
        $pdf->SetFillColor($fillColor, $fillColor, $fillColor);
        $pdf->Cell(40, 8, date('d/m/Y', strtotime($row['fecha'])), 1, 0, 'C', $fill);
        $pdf->Cell(40, 8, 'CRC ' . number_format($row['ventas']), 1, 0, 'C', $fill);
        $pdf->Cell(40, 8, 'CRC ' . number_format($row['compras']), 1, 0, 'C', $fill);
        $pdf->Cell(35, 8, number_format($row['num_ventas']), 1, 0, 'C', $fill);
        $pdf->Cell(35, 8, number_format($row['num_compras']), 1, 1, 'C', $fill);
        $rowCount++;
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode('No hay datos de ventas disponibles para el período'), 0, 1, 'C');
}

$pdf->Ln(10);

// Resumen ejecutivo
$pdf->ChapterTitle('Resumen Ejecutivo');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 6, utf8_decode('Este reporte muestra el estado actual del inventario y las métricas de rendimiento de la empresa. Los datos incluyen:'), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(240, 248, 255); // Azul muy claro

// Crear tabla de resumen
$pdf->Cell(0, 8, '  ' . utf8_decode('Resumen de Métricas Clave'), 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(248, 250, 252); // Gris muy claro

$pdf->Cell(0, 6, '  • ' . utf8_decode('Total de productos en inventario: ') . number_format($stats_productos['total_productos']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Valor total del inventario: ') . 'CRC ' . number_format($stats_productos['valor_total_inventario']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Movimientos en los últimos 30 días: ') . number_format($stats_movimientos['total_movimientos']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Productos que requieren atención: ') . number_format($stats_productos['productos_criticos']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Categorías activas: ') . number_format($stats_categorias['categorias_activas']), 0, 1, 'L', true);

// Generar y mostrar el PDF en el navegador
$empresa_nombre = $empresa_info['nombre_empresa'] ?? 'Empresa';
$empresa_nombre = preg_replace('/[^a-zA-Z0-9\s]/', '', $empresa_nombre); // Limpiar caracteres especiales
$empresa_nombre = preg_replace('/\s+/', '_', trim($empresa_nombre)); // Reemplazar espacios con guiones bajos

$filename = 'Dashboard_' . $empresa_nombre . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Configurar headers para mostrar PDF en el navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');

$pdf->Output('I', $filename);
?> 