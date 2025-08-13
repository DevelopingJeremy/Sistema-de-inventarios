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

// Obtener clientes de la empresa
try {
    // Obtener todos los clientes con sus estadísticas de ventas
    $stmt_clientes = $conn->prepare("
        SELECT 
            c.*,
            COUNT(v.ID_VENTA) as total_ventas,
            COALESCE(SUM(v.total), 0) as total_gastado
        FROM t_clientes c
        LEFT JOIN t_ventas v ON c.ID_CLIENTE = v.ID_CLIENTE
        WHERE c.ID_EMPRESA = ?
        GROUP BY c.ID_CLIENTE
        ORDER BY c.fecha_registro DESC
    ");
    $stmt_clientes->bind_param("i", $id_empresa);
    $stmt_clientes->execute();
    $clientes = $stmt_clientes->get_result();

    // Obtener estadísticas generales
    $stmt_stats = $conn->prepare("
        SELECT 
            COUNT(*) as total_clientes,
            SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as clientes_activos,
            SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as clientes_inactivos,
            SUM(CASE WHEN fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as nuevos_mes
        FROM t_clientes 
        WHERE ID_EMPRESA = ?
    ");
    $stmt_stats->bind_param("i", $id_empresa);
    $stmt_stats->execute();
    $stats_basicas = $stmt_stats->get_result()->fetch_assoc();

    // Obtener estadísticas de ventas
    $stmt_ventas_stats = $conn->prepare("
        SELECT 
            COALESCE(SUM(v.total), 0) as valor_total_gastado,
            COALESCE(COUNT(v.ID_VENTA), 0) as total_compras_general,
            COALESCE(AVG(ventas_por_cliente.total_ventas), 0) as promedio_compras
        FROM t_clientes c
        LEFT JOIN (
            SELECT 
                ID_CLIENTE,
                COUNT(ID_VENTA) as total_ventas,
                SUM(total) as total_gastado
            FROM t_ventas 
            WHERE ID_EMPRESA = ?
            GROUP BY ID_CLIENTE
        ) ventas_por_cliente ON c.ID_CLIENTE = ventas_por_cliente.ID_CLIENTE
        LEFT JOIN t_ventas v ON c.ID_CLIENTE = v.ID_CLIENTE
        WHERE c.ID_EMPRESA = ?
    ");
    $stmt_ventas_stats->bind_param("ii", $id_empresa, $id_empresa);
    $stmt_ventas_stats->execute();
    $stats_ventas = $stmt_ventas_stats->get_result()->fetch_assoc();

    // Combinar estadísticas
    $stats_clientes = array_merge($stats_basicas, $stats_ventas);

    // Obtener distribución por tipo de cliente
    $stmt_tipos = $conn->prepare("
        SELECT 
            c.tipo_cliente,
            COUNT(c.ID_CLIENTE) as total_clientes,
            COALESCE(SUM(v.total), 0) as valor_total
        FROM t_clientes c
        LEFT JOIN t_ventas v ON c.ID_CLIENTE = v.ID_CLIENTE
        WHERE c.ID_EMPRESA = ? 
        AND c.estado = 'activo'
        GROUP BY c.tipo_cliente
        ORDER BY valor_total DESC
    ");
    $stmt_tipos->bind_param("i", $id_empresa);
    $stmt_tipos->execute();
    $tipos_cliente = $stmt_tipos->get_result();

} catch (Exception $e) {
    $clientes = null;
    $stats_clientes = [
        'total_clientes' => 0,
        'clientes_activos' => 0,
        'clientes_inactivos' => 0,
        'nuevos_mes' => 0,
        'promedio_compras' => 0,
        'total_compras_general' => 0,
        'valor_total_gastado' => 0
    ];
    $tipos_cliente = null;
}

// Crear clase PDF personalizada
class ClientesPDF extends FPDF {
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
        $this->Cell(0, 12, utf8_decode('Reporte de Clientes'), 0, 1, 'C');
        
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
        $this->Cell(0, 4, utf8_decode('Hybox Cloud - Sistema de Gestión de Ventas'), 0, 1, 'C');
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
$pdf = new ClientesPDF();
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

$pdf->Cell(95, 8, utf8_decode('Total de Clientes'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_clientes['total_clientes']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Clientes Activos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_clientes['clientes_activos']), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Clientes Inactivos'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_clientes['clientes_inactivos']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Nuevos este Mes'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_clientes['nuevos_mes']), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Total de Compras'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_clientes['total_compras_general']), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Blanco
$pdf->Cell(95, 8, utf8_decode('Promedio de Compras por Cliente'), 1, 0, 'L', true);
$pdf->Cell(95, 8, number_format($stats_clientes['promedio_compras'], 1), 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 252); // Gris muy claro
$pdf->Cell(95, 8, utf8_decode('Valor Total Gastado'), 1, 0, 'L', true);
$pdf->Cell(95, 8, 'CRC ' . number_format($stats_clientes['valor_total_gastado'], 2), 1, 1, 'C', true);

$pdf->Ln(10);

// Distribución por tipo de cliente
if ($tipos_cliente && $tipos_cliente->num_rows > 0) {
    $pdf->ChapterTitle('Distribución por Tipo de Cliente');
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(59, 130, 246); // Azul
    $pdf->SetTextColor(255, 255, 255); // Blanco
    
    // Encabezados de tabla
    $pdf->Cell(50, 8, utf8_decode('Tipo de Cliente'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Cantidad'), 1, 0, 'C', true);
    $pdf->Cell(45, 8, utf8_decode('Valor Total'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Porcentaje'), 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(0, 0, 0); // Negro
    
    $total_clientes_activos = 0;
    $total_valor = 0;
    
    // Calcular totales
    $tipos_cliente->data_seek(0);
    while ($tipo = $tipos_cliente->fetch_assoc()) {
        $total_clientes_activos += $tipo['total_clientes'];
        $total_valor += $tipo['valor_total'];
    }
    
    // Mostrar datos
    $tipos_cliente->data_seek(0);
    $rowCount = 0;
    while ($tipo = $tipos_cliente->fetch_assoc()) {
        $porcentaje = $total_clientes_activos > 0 ? ($tipo['total_clientes'] / $total_clientes_activos) * 100 : 0;
        
        $fill = ($rowCount % 2 == 0) ? true : false;
        $fillColor = ($rowCount % 2 == 0) ? 248 : 255; // Gris claro o blanco
        
        $pdf->SetFillColor($fillColor, $fillColor, $fillColor);
        
        $pdf->Cell(50, 8, utf8_decode(ucfirst($tipo['tipo_cliente'])), 1, 0, 'L', $fill);
        $pdf->Cell(35, 8, number_format($tipo['total_clientes']), 1, 0, 'C', $fill);
        $pdf->Cell(45, 8, 'CRC ' . number_format($tipo['valor_total'], 2), 1, 0, 'C', $fill);
        $pdf->Cell(35, 8, round($porcentaje, 1) . '%', 1, 1, 'C', $fill);
        
        $rowCount++;
    }
    
    $pdf->Ln(10);
}

// Lista de clientes
$pdf->ChapterTitle('Lista de Clientes');

if ($clientes && $clientes->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(59, 130, 246); // Azul
    $pdf->SetTextColor(255, 255, 255); // Blanco
    
    // Encabezados de tabla
    $pdf->Cell(25, 8, utf8_decode('ID'), 1, 0, 'C', true);
    $pdf->Cell(40, 8, utf8_decode('Cliente'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('Email'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Teléfono'), 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode('Tipo'), 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode('Compras'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Total'), 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetTextColor(0, 0, 0); // Negro
    
    $rowCount = 0;
    while ($cliente = $clientes->fetch_assoc()) {
        $fill = ($rowCount % 2 == 0) ? true : false;
        $fillColor = ($rowCount % 2 == 0) ? 248 : 255; // Gris claro o blanco
        
        $pdf->SetFillColor($fillColor, $fillColor, $fillColor);
        
        // ID
        $pdf->Cell(25, 8, '#' . str_pad($cliente['ID_CLIENTE'], 3, '0', STR_PAD_LEFT), 1, 0, 'C', $fill);
        
        // Nombre completo
        $nombre_completo = $cliente['nombre'] . ' ' . $cliente['apellido'];
        $pdf->Cell(40, 8, utf8_decode(substr($nombre_completo, 0, 18)), 1, 0, 'L', $fill);
        
        // Email
        $pdf->Cell(35, 8, utf8_decode(substr($cliente['email'] ?? '-', 0, 15)), 1, 0, 'L', $fill);
        
        // Teléfono
        $pdf->Cell(25, 8, utf8_decode(substr($cliente['telefono'] ?? '-', 0, 10)), 1, 0, 'C', $fill);
        
        // Tipo de cliente
        $pdf->Cell(20, 8, utf8_decode(substr($cliente['tipo_cliente'], 0, 8)), 1, 0, 'C', $fill);
        
        // Total de compras
        $pdf->Cell(20, 8, number_format($cliente['total_ventas'] ?? 0), 1, 0, 'C', $fill);
        
        // Total gastado
        $pdf->Cell(25, 8, 'CRC ' . number_format($cliente['total_gastado'] ?? 0, 0), 1, 1, 'C', $fill);
        
        $rowCount++;
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode('No hay clientes registrados'), 0, 1, 'C');
}

$pdf->Ln(10);

// Resumen
$pdf->ChapterTitle('Resumen');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 6, utf8_decode('Este reporte muestra todos los clientes registrados en el sistema con sus detalles principales y estadísticas de compras.'), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(240, 248, 255); // Azul muy claro

$pdf->Cell(0, 8, '  ' . utf8_decode('Información del Reporte'), 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(248, 250, 252); // Gris muy claro

$pdf->Cell(0, 6, '  • ' . utf8_decode('Total de clientes en el sistema: ') . number_format($stats_clientes['total_clientes']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Clientes activos: ') . number_format($stats_clientes['clientes_activos']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Nuevos clientes este mes: ') . number_format($stats_clientes['nuevos_mes']), 0, 1, 'L', true);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Total de compras realizadas: ') . number_format($stats_clientes['total_compras_general']), 0, 1, 'L', true);
$pdf->SetFillColor(248, 250, 252);
$pdf->Cell(0, 6, '  • ' . utf8_decode('Valor total gastado por clientes: ') . 'CRC ' . number_format($stats_clientes['valor_total_gastado'], 2), 0, 1, 'L', true);

// Generar y mostrar el PDF en el navegador
$empresa_nombre = $empresa_info['nombre_empresa'] ?? 'Empresa';
$empresa_nombre = preg_replace('/[^a-zA-Z0-9\s]/', '', $empresa_nombre); // Limpiar caracteres especiales
$empresa_nombre = preg_replace('/\s+/', '_', trim($empresa_nombre)); // Reemplazar espacios con guiones bajos

$filename = 'Clientes_' . $empresa_nombre . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Configurar headers para mostrar PDF en el navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');

$pdf->Output('I', $filename);
?> 