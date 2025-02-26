<?php
session_start();
require 'vendor/autoload.php'; // Carrega o autoloader do Composer

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || $_SESSION['user_nivel'] != 1) {
    header('Location: index.php');
    exit;
}

$servername = "localhost";
$username = "unidas90_admin";
$password = "4dm1n@2025";
$dbname = "unidas90_certificados";

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
            nomes.nome AS certificado_nome, 
            nomes.cpf AS certificado_cpf, 
            nomes.instituicao AS certificado_instituicao, 
            nomes.carga_horaria AS certificado_carga_horaria, 
            nomes.email AS certificado_email,
            usuarios.nome AS admin_nome
        FROM 
            certificados_gerados
        JOIN 
            nomes ON certificados_gerados.nome_evento = nomes.evento
            AND certificados_gerados.data_evento = nomes.data_inicio
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

    $sheet->setCellValue('A1', 'Nome do Certificado');
    $sheet->setCellValue('B1', 'CPF');
    $sheet->setCellValue('C1', 'Instituição');
    $sheet->setCellValue('D1', 'Carga Horária');
    $sheet->setCellValue('E1', 'E-mail');
    $sheet->setCellValue('F1', 'Gerado Por (Admin)');

    $row = 2;
    foreach ($dados as $dado) {
        $sheet->setCellValue("A{$row}", $dado['certificado_nome']);
        $sheet->setCellValue("B{$row}", $dado['certificado_cpf']);
        $sheet->setCellValue("C{$row}", $dado['certificado_instituicao']);
        $sheet->setCellValue("D{$row}", $dado['certificado_carga_horaria']);
        $sheet->setCellValue("E{$row}", $dado['certificado_email']);
        $sheet->setCellValue("F{$row}", $dado['admin_nome']);
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
