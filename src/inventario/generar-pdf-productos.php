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
    header('Location: ../../public/empresa/registrar-empresa.php');
    exit();
}

// Obtener datos de la empresa
$stmt_empresa_info = $conn->prepare("SELECT nombre_empresa FROM t_empresa WHERE ID_EMPRESA = ?");
$stmt_empresa_info->bind_param("i", $id_empresa);
$stmt_empresa_info->execute();
$empresa_info = $stmt_empresa_info->get_result()->fetch_assoc();

// Obtener productos de la empresa
try {
    $stmt_productos = $conn->prepare("
        SELECT p.*, e.ID_EMPRESA 
        FROM t_productos p 
        INNER JOIN t_empresa e ON p.ID_EMPRESA = e.ID_EMPRESA 
        WHERE e.ID_DUEÑO = ? 
        ORDER BY p.fecha_creacion DESC
    ");
    $stmt_productos->bind_param("i", $id_usuario);
    $stmt_productos->execute();
    $productos = $stmt_productos->get_result();

    // Obtener estadísticas
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(*) as total_productos,
            SUM(CASE WHEN stock > 0 THEN 1 ELSE 0 END) as productos_activos,
            SUM(CASE WHEN stock <= 5 AND stock > 0 THEN 1 ELSE 0 END) as stock_bajo,
            SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as sin_stock,
            SUM(stock * precio) as valor_total,
            SUM(stock) as stock_total
        FROM t_productos 
        WHERE ID_EMPRESA = ?
    ");
    $stmt_stats->bind_param("i", $id_empresa);
    $stmt_stats->execute();
    $stats_productos = $stmt_stats->get_result()->fetch_assoc();

} catch (Exception $e) {
    $productos = null;
    $stats_productos = [
        'total_productos' => 0,
        'productos_activos' => 0,
        'stock_bajo' => 0,
        'sin_stock' => 0,
        'valor_total' => 0,
        'stock_total' => 0
    ];
}

// Crear clase PDF personalizada
class ProductosPDF extends FPDF {
    function __construct() {
        parent::__construct();
        // Configurar para UTF-8
        $this->SetAutoPageBreak(true, 20);
        $this->SetMargins(10, 10, 10);
    }
    
    function Header() {
        global $empresa_info;
        
        // Título principal
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(59, 130, 246); // Azul
        $this->Cell(0, 12, utf8_decode('Reporte de Productos'), 0, 1, 'C');
        
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
}

// Crear PDF
$pdf = new ProductosPDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Estadísticas generales
$pdf->ChapterTitle('Estadísticas Generales');

// Crear tabla de estadísticas
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(59, 130, 246); // Azul
$pdf->SetTextColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Métrica'), 1, 0, 'C', true);
$pdf->Cell(95, 8, utf8_decode('Valor'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0); // Negro
$pdf->SetFillColor(248, 250, 252); // Gris muy claro

$pdf->Cell(95, 8, utf8_decode('Total de Productos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_productos['total_productos']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Productos Activos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_productos['productos_activos']), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Stock Bajo'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_productos['stock_bajo']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Sin Stock'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_productos['sin_stock']), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Stock Total'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_productos['stock_total']) . ' unidades', 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Valor Total del Inventario'), 1, 0, 'L', true);
$pdf->Cell(95, 8, 'CRC ' . number_format($stats_productos['valor_total']), 1, 1, 'C', true);

$pdf->Ln(10);

// Lista de productos
$pdf->ChapterTitle('Lista de Productos');

if ($productos && $productos->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(59, 130, 246); // Azul
    $pdf->SetTextColor(255, 255, 255); // Blanco
    
    // Encabezados de tabla
    $pdf->Cell(50, 8, utf8_decode('Producto'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Categoría'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Precio'), 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode('Stock'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Estado'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Código'), 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode('Proveedor'), 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0); // Negro
    
    $rowCount = 0;
    while ($producto = $productos->fetch_assoc()) {
        // Determinar el estado del producto
        if ($producto['stock'] <= 0) {
            $estado_texto = 'Agotado';
        } elseif ($producto['stock'] <= $producto['stock_minimo']) {
            $estado_texto = 'Stock Bajo';
        } else {
            $estado_texto = 'Activo';
        }
        
        $fill = ($rowCount % 2 == 0) ? true : false;
        $fillColor = ($rowCount % 2 == 0) ? 248 : 255; // Gris claro o blanco
        
        $pdf->SetFillColor($fillColor, $fillColor, $fillColor);
        
        // Producto (nombre truncado)
        $pdf->Cell(50, 8, utf8_decode(substr($producto['nombre_producto'], 0, 20)), 1, 0, 'L', $fill);
        
        // Categoría
        $pdf->Cell(25, 8, utf8_decode(substr($producto['categoria'] ?? 'Sin categoría', 0, 12)), 1, 0, 'C', $fill);
        
        // Precio
        $pdf->Cell(25, 8, 'CRC ' . number_format($producto['precio']), 1, 0, 'C', $fill);
        
        // Stock
        $pdf->Cell(20, 8, number_format($producto['stock']), 1, 0, 'C', $fill);
        
        // Estado
        $pdf->Cell(25, 8, utf8_decode($estado_texto), 1, 0, 'C', $fill);
        
        // Código (barras o interno)
        $codigo = $producto['codigo_barras'] ?: $producto['codigo_interno'] ?: '-';
        $pdf->Cell(25, 8, utf8_decode(substr($codigo, 0, 12)), 1, 0, 'C', $fill);
        
        // Proveedor
        $pdf->Cell(20, 8, utf8_decode(substr($producto['proveedor'] ?? '-', 0, 10)), 1, 1, 'C', $fill);
        
        $rowCount++;
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode('No hay productos registrados'), 0, 1, 'C');
}

$pdf->Ln(10);

// Resumen
$pdf->ChapterTitle('Resumen');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 6, utf8_decode('Este reporte muestra todos los productos registrados en el sistema con sus detalles principales.'), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(240, 248, 255); // Azul muy claro

$pdf->Cell(0, 8, '  ' . utf8_decode('Información del Reporte'), 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(248, 250, 252); // Gris muy claro

$pdf->Cell(0, 6, '  • ' . utf8_decode('Total de productos en el sistema: ') . number_format($stats_productos['total_productos']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Productos con stock disponible: ') . number_format($stats_productos['productos_activos']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Productos que requieren atención: ') . number_format($stats_productos['stock_bajo'] + $stats_productos['sin_stock']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Valor total del inventario: ') . 'CRC ' . number_format($stats_productos['valor_total']), 0, 1, 'L', true);

// Generar y mostrar el PDF en el navegador
$empresa_nombre = $empresa_info['nombre_empresa'] ?? 'Empresa';
$empresa_nombre = preg_replace('/[^a-zA-Z0-9\s]/', '', $empresa_nombre); // Limpiar caracteres especiales
$empresa_nombre = preg_replace('/\s+/', '_', trim($empresa_nombre)); // Reemplazar espacios con guiones bajos

$filename = 'Productos_' . $empresa_nombre . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Configurar headers para mostrar PDF en el navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');

$pdf->Output('I', $filename);
?> 