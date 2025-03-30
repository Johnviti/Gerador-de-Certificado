<?php
session_start();
require 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id']) || $_SESSION['user_nivel'] != 1) {
    header('Location: index.php');
    exit;
}

// Carrega as variáveis do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Agora use $_ENV ou getenv()
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("
        SELECT 
            certificados_gerados.*,
            nomes.nome AS certificado_nome, 
            nomes.cpf AS certificado_cpf, 
            nomes.instituicao AS certificado_instituicao, 
            nomes.carga_horaria AS certificado_carga_horaria, 
            nomes.email AS certificado_email,
            usuarios.nome AS admin_nome
        FROM 
            certificados_gerados
        JOIN 
            nomes ON certificados_gerados.id_nome = nomes.id
        JOIN 
            usuarios ON nomes.admin_id = usuarios.id
        WHERE 
            certificados_gerados.usuario_id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id);

    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($dados)) {
        die("Nenhum certificado encontrado para este usuário.");
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Relatório de Certificados");

    $headers = ['Nome do Certificado', 'CPF', 'Instituição', 'Carga Horária', 'E-mail', 'Gerado Por (Admin)'];
    $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
    
    foreach ($headers as $index => $header) {
        $sheet->setCellValue("{$columns[$index]}1", $header);
    }
    
    foreach ($columns as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }


    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF343A40']],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFDEE2E6']]],
    ];
    $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

    $row = 2;
    foreach ($dados as $dado) {
        $sheet->setCellValue("A{$row}", $dado['certificado_nome']);
        $sheet->setCellValue("B{$row}", $dado['certificado_cpf']);
        $sheet->setCellValue("C{$row}", $dado['certificado_instituicao']);
        $sheet->setCellValue("D{$row}", $dado['certificado_carga_horaria']);
        $sheet->setCellValue("E{$row}", $dado['certificado_email']);
        $sheet->setCellValue("F{$row}", $dado['admin_nome']);

        $rowStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => ($row % 2 == 0) ? 'FFF8F9FA' : 'FFFFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFDEE2E6'],
                ],
            ],
        ];
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($rowStyle);

        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = "relatorio_certificados_usuario_{$user_id}.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} else {
    die("Requisição inválida.");
}
?>
