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

// Obtener movimientos de la empresa
try {
    $stmt_movimientos = $conn->prepare("
        SELECT 
            m.ID_MOVIMIENTO,
            m.ID_EMPRESA,
            m.ID_PRODUCTO,
            m.ID_USUARIO,
            m.tipo_movimiento,
            m.cantidad,
            m.valor_movimiento,
            m.motivo,
            m.fecha_movimiento,
            m.fecha_creacion,
            p.nombre_producto, 
            p.categoria, 
            u.nombre_completo as nombre_usuario,
            COALESCE(m.precio_unitario, 0) as precio_unitario,
            COALESCE(m.referencia, '') as referencia,
            COALESCE(m.proveedor_cliente, '') as proveedor_cliente,
            COALESCE(m.documento, '') as documento,
            COALESCE(m.observaciones, '') as observaciones
        FROM t_movimientos_inventario m
        LEFT JOIN t_productos p ON m.ID_PRODUCTO = p.ID_PRODUCTO
        LEFT JOIN t_usuarios u ON m.ID_USUARIO = u.ID_USUARIO
        WHERE m.ID_EMPRESA = ?
        ORDER BY m.fecha_movimiento DESC
    ");
    $stmt_movimientos->bind_param("i", $id_empresa);
    $stmt_movimientos->execute();
    $movimientos = $stmt_movimientos->get_result();

    // Obtener estadísticas
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(*) as total_movimientos,
            COUNT(CASE WHEN tipo_movimiento = 'entrada' THEN 1 END) as total_entradas,
            COUNT(CASE WHEN tipo_movimiento = 'salida' THEN 1 END) as total_salidas,
            SUM(valor_movimiento) as valor_total,
            SUM(CASE WHEN tipo_movimiento = 'entrada' THEN valor_movimiento ELSE 0 END) as valor_entradas,
            SUM(CASE WHEN tipo_movimiento = 'salida' THEN valor_movimiento ELSE 0 END) as valor_salidas
        FROM t_movimientos_inventario 
        WHERE ID_EMPRESA = ?
    ");
    $stmt_stats->bind_param("i", $id_empresa);
    $stmt_stats->execute();
    $stats_movimientos = $stmt_stats->get_result()->fetch_assoc();

} catch (Exception $e) {
    $movimientos = null;
    $stats_movimientos = [
        'total_movimientos' => 0,
        'total_entradas' => 0,
        'total_salidas' => 0,
        'valor_total' => 0,
        'valor_entradas' => 0,
        'valor_salidas' => 0
    ];
}

// Crear clase PDF personalizada
class MovimientosPDF extends FPDF {
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
        $this->Cell(0, 12, utf8_decode('Reporte de Movimientos'), 0, 1, 'C');
        
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
$pdf = new MovimientosPDF();
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

$pdf->Cell(95, 8, utf8_decode('Total de Movimientos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_movimientos['total_movimientos']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Entradas'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_movimientos['total_entradas']), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Salidas'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_movimientos['total_salidas']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Valor Total de Entradas'), 1, 0, 'L', true);
$pdf->Cell(95, 8, 'CRC ' . number_format($stats_movimientos['valor_entradas']), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Valor Total de Salidas'), 1, 0, 'L', true);
$pdf->Cell(95, 8, 'CRC ' . number_format($stats_movimientos['valor_salidas']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Valor Total General'), 1, 0, 'L', true);
$pdf->Cell(95, 8, 'CRC ' . number_format($stats_movimientos['valor_total']), 1, 1, 'C', true);

$pdf->Ln(10);

// Lista de movimientos
$pdf->ChapterTitle('Lista de Movimientos');

if ($movimientos && $movimientos->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(59, 130, 246); // Azul
    $pdf->SetTextColor(255, 255, 255); // Blanco
    
    // Encabezados de tabla
    $pdf->Cell(25, 8, utf8_decode('Fecha'), 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode('Tipo'), 1, 0, 'C', true);
    $pdf->Cell(40, 8, utf8_decode('Producto'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Categoría'), 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode('Cantidad'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Precio Unit.'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Valor Total'), 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode('Usuario'), 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(0, 0, 0); // Negro
    
    $rowCount = 0;
    while ($movimiento = $movimientos->fetch_assoc()) {
        $fill = ($rowCount % 2 == 0) ? true : false;
        $fillColor = ($rowCount % 2 == 0) ? 248 : 255; // Gris claro o blanco
        
        $pdf->SetFillColor($fillColor, $fillColor, $fillColor);
        
        // Fecha
        $fecha = date('d/m/Y H:i', strtotime($movimiento['fecha_movimiento']));
        $pdf->Cell(25, 8, $fecha, 1, 0, 'C', $fill);
        
        // Tipo
        $tipo = ucfirst($movimiento['tipo_movimiento']);
        $pdf->Cell(20, 8, utf8_decode($tipo), 1, 0, 'C', $fill);
        
        // Producto (nombre truncado)
        $pdf->Cell(40, 8, utf8_decode(substr($movimiento['nombre_producto'] ?? 'Producto eliminado', 0, 18)), 1, 0, 'L', $fill);
        
        // Categoría
        $pdf->Cell(25, 8, utf8_decode(substr($movimiento['categoria'] ?? 'Sin categoría', 0, 12)), 1, 0, 'C', $fill);
        
        // Cantidad
        $pdf->Cell(20, 8, number_format($movimiento['cantidad']), 1, 0, 'C', $fill);
        
        // Precio Unitario
        $pdf->Cell(25, 8, 'CRC ' . number_format($movimiento['precio_unitario'], 2), 1, 0, 'C', $fill);
        
        // Valor Total
        $pdf->Cell(25, 8, 'CRC ' . number_format($movimiento['valor_movimiento']), 1, 0, 'C', $fill);
        
        // Usuario
        $pdf->Cell(20, 8, utf8_decode(substr($movimiento['nombre_usuario'] ?? 'Usuario', 0, 10)), 1, 1, 'C', $fill);
        
        $rowCount++;
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode('No hay movimientos registrados'), 0, 1, 'C');
}

$pdf->Ln(10);

// Resumen
$pdf->ChapterTitle('Resumen');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 6, utf8_decode('Este reporte muestra todos los movimientos de inventario registrados en el sistema.'), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(240, 248, 255); // Azul muy claro

$pdf->Cell(0, 8, '  ' . utf8_decode('Información del Reporte'), 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(248, 250, 252); // Gris muy claro

$pdf->Cell(0, 6, '  • ' . utf8_decode('Total de movimientos en el sistema: ') . number_format($stats_movimientos['total_movimientos']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Movimientos de entrada: ') . number_format($stats_movimientos['total_entradas']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Movimientos de salida: ') . number_format($stats_movimientos['total_salidas']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Valor total de entradas: ') . 'CRC ' . number_format($stats_movimientos['valor_entradas']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Valor total de salidas: ') . 'CRC ' . number_format($stats_movimientos['valor_salidas']), 0, 1, 'L', true);

// Generar y mostrar el PDF en el navegador
$empresa_nombre = $empresa_info['nombre_empresa'] ?? 'Empresa';
$empresa_nombre = preg_replace('/[^a-zA-Z0-9\s]/', '', $empresa_nombre); // Limpiar caracteres especiales
$empresa_nombre = preg_replace('/\s+/', '_', trim($empresa_nombre)); // Reemplazar espacios con guiones bajos

$filename = 'Movimientos_' . $empresa_nombre . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Configurar headers para mostrar PDF en el navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');

$pdf->Output('I', $filename);
?> 