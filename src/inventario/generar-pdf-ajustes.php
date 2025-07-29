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

// Obtener ajustes de la empresa
try {
    $stmt_ajustes = $conn->prepare("
        SELECT a.*, p.nombre_producto, p.categoria, u.nombre_completo as nombre_usuario
        FROM t_ajustes_inventario a
        LEFT JOIN t_productos p ON a.ID_PRODUCTO = p.ID_PRODUCTO
        LEFT JOIN t_usuarios u ON a.ID_USUARIO = u.ID_USUARIO
        WHERE a.ID_EMPRESA = ?
        ORDER BY a.fecha_ajuste DESC
    ");
    $stmt_ajustes->bind_param("i", $id_empresa);
    $stmt_ajustes->execute();
    $ajustes = $stmt_ajustes->get_result();

    // Obtener estadísticas
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(*) as total_ajustes,
            COUNT(CASE WHEN tipo_ajuste = 'positivo' THEN 1 END) as total_positivos,
            COUNT(CASE WHEN tipo_ajuste = 'negativo' THEN 1 END) as total_negativos,
            SUM(COALESCE(valor_ajuste, 0)) as valor_total,
            SUM(CASE WHEN tipo_ajuste = 'positivo' THEN COALESCE(valor_ajuste, 0) ELSE 0 END) as valor_positivos,
            SUM(CASE WHEN tipo_ajuste = 'negativo' THEN COALESCE(valor_ajuste, 0) ELSE 0 END) as valor_negativos
        FROM t_ajustes_inventario 
        WHERE ID_EMPRESA = ?
    ");
    $stmt_stats->bind_param("i", $id_empresa);
    $stmt_stats->execute();
    $stats_ajustes = $stmt_stats->get_result()->fetch_assoc();

} catch (Exception $e) {
    $ajustes = null;
    $stats_ajustes = [
        'total_ajustes' => 0,
        'total_positivos' => 0,
        'total_negativos' => 0,
        'valor_total' => 0,
        'valor_positivos' => 0,
        'valor_negativos' => 0
    ];
}

// Crear clase PDF personalizada
class AjustesPDF extends FPDF {
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
        $this->Cell(0, 12, utf8_decode('Reporte de Ajustes'), 0, 1, 'C');
        
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
$pdf = new AjustesPDF();
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

$pdf->Cell(95, 8, utf8_decode('Total de Ajustes'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_ajustes['total_ajustes']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Ajustes Positivos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_ajustes['total_positivos']), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Ajustes Negativos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_ajustes['total_negativos']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Valor Total de Ajustes Positivos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, 'CRC ' . number_format($stats_ajustes['valor_positivos']), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Valor Total de Ajustes Negativos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, 'CRC ' . number_format($stats_ajustes['valor_negativos']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Valor Total General'), 1, 0, 'L', true);
$pdf->Cell(95, 8, 'CRC ' . number_format($stats_ajustes['valor_total']), 1, 1, 'C', true);

$pdf->Ln(10);

// Lista de ajustes
$pdf->ChapterTitle('Lista de Ajustes');

if ($ajustes && $ajustes->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(59, 130, 246); // Azul
    $pdf->SetTextColor(255, 255, 255); // Blanco
    
    // Encabezados de tabla
    $pdf->Cell(25, 8, utf8_decode('Fecha'), 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode('Tipo'), 1, 0, 'C', true);
    $pdf->Cell(40, 8, utf8_decode('Producto'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Categoría'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Cantidad'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode('Valor'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Motivo'), 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(0, 0, 0); // Negro
    
    $rowCount = 0;
    while ($ajuste = $ajustes->fetch_assoc()) {
        $fill = ($rowCount % 2 == 0) ? true : false;
        $fillColor = ($rowCount % 2 == 0) ? 248 : 255; // Gris claro o blanco
        
        $pdf->SetFillColor($fillColor, $fillColor, $fillColor);
        
        // Fecha
        $fecha = date('d/m/Y H:i', strtotime($ajuste['fecha_ajuste']));
        $pdf->Cell(25, 8, $fecha, 1, 0, 'C', $fill);
        
        // Tipo
        $tipo = $ajuste['tipo_ajuste'] === 'positivo' ? 'Positivo' : 'Negativo';
        $pdf->Cell(20, 8, utf8_decode($tipo), 1, 0, 'C', $fill);
        
        // Producto (nombre truncado)
        $pdf->Cell(40, 8, utf8_decode(substr($ajuste['nombre_producto'] ?? 'Producto eliminado', 0, 18)), 1, 0, 'L', $fill);
        
        // Categoría
        $pdf->Cell(25, 8, utf8_decode(substr($ajuste['categoria'] ?? 'Sin categoría', 0, 12)), 1, 0, 'C', $fill);
        
        // Cantidad
        $pdf->Cell(25, 8, number_format($ajuste['cantidad_ajustada']), 1, 0, 'C', $fill);
        
        // Valor
        $pdf->Cell(30, 8, 'CRC ' . number_format($ajuste['valor_ajuste']), 1, 0, 'C', $fill);
        
        // Motivo (truncado)
        $pdf->Cell(35, 8, utf8_decode(substr($ajuste['motivo_ajuste'], 0, 20)), 1, 1, 'L', $fill);
        
        $rowCount++;
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode('No hay ajustes registrados'), 0, 1, 'C');
}

$pdf->Ln(10);

// Resumen
$pdf->ChapterTitle('Resumen');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 6, utf8_decode('Este reporte muestra todos los ajustes de inventario registrados en el sistema.'), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(240, 248, 255); // Azul muy claro

$pdf->Cell(0, 8, '  ' . utf8_decode('Información del Reporte'), 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(248, 250, 252); // Gris muy claro

$pdf->Cell(0, 6, '  • ' . utf8_decode('Total de ajustes en el sistema: ') . number_format($stats_ajustes['total_ajustes']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Ajustes positivos: ') . number_format($stats_ajustes['total_positivos']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Ajustes negativos: ') . number_format($stats_ajustes['total_negativos']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Valor total de ajustes positivos: ') . 'CRC ' . number_format($stats_ajustes['valor_positivos']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Valor total de ajustes negativos: ') . 'CRC ' . number_format($stats_ajustes['valor_negativos']), 0, 1, 'L', true);

// Generar y mostrar el PDF en el navegador
$empresa_nombre = $empresa_info['nombre_empresa'] ?? 'Empresa';
$empresa_nombre = preg_replace('/[^a-zA-Z0-9\s]/', '', $empresa_nombre); // Limpiar caracteres especiales
$empresa_nombre = preg_replace('/\s+/', '_', trim($empresa_nombre)); // Reemplazar espacios con guiones bajos

$filename = 'Ajustes_' . $empresa_nombre . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Configurar headers para mostrar PDF en el navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');

$pdf->Output('I', $filename);
?> 