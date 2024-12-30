<?php
// Habilitar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('header.php');
include('db.php');
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function enviarEmailComCertificado($email, $nome, $caminhoCertificado) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.titan.email';
        $mail->SMTPAuth = true;
        $mail->Username = 'sistemas@unidasautogestao.com';
        $mail->Password = 'MLTR@unidas15';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('sistemas@unidasautogestao.com', 'Unidas Autogestão');
        $mail->addAddress($email, $nome);

        $mail->isHTML(true);
        $mail->Subject = 'Seu Certificado';
        $mail->Body = "Olá {$nome},<br><br>Segue o certificado em anexo.";
        $mail->addAttachment($caminhoCertificado, 'certificado.pdf');

        $mail->send();
        return true;
    } catch (Exception $e) {
        return 'Erro ao enviar email: ' . $mail->ErrorInfo;
    }
}

$error = null;
$success = null;
$participante = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_term'])) {
    try {
        $search_term = '%' . $_POST['search_term'] . '%';
        $stmt = $conn->prepare("
            SELECT id, nome, cpf, email, certificado_gerado 
            FROM nomes 
            WHERE nome LIKE :search_term 
               OR cpf LIKE :search_term 
               OR email LIKE :search_term
        ");
        $stmt->bindParam(':search_term', $search_term);
        $stmt->execute();
        $participante = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$participante) {
            $error = 'Nenhum participante encontrado.';
        } elseif (isset($_POST['enviar_email'])) {
            $output_dir = __DIR__ . '/SalvarPDF';
            $caminhoCertificado = $output_dir . '/certificado_' . str_replace(' ', '_', $participante['nome']) . '.pdf';

            if (file_exists($caminhoCertificado)) {
                $resultadoEnvio = enviarEmailComCertificado($participante['email'], $participante['nome'], $caminhoCertificado);
                if ($resultadoEnvio === true) {
                    $success = 'Certificado enviado com sucesso para ' . htmlspecialchars($participante['nome']) . '.';
                } else {
                    $error = $resultadoEnvio;
                }
            } else {
                $error = 'Certificado não encontrado para ' . htmlspecialchars($participante['nome']) . '.';
            }
        }
    } catch (Exception $e) {
        $error = 'Erro ao processar a solicitação: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar Participantes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        h1 {
            font-size: 15px; /* Título */
        }
        .form-group label, .card-header h5, .card-body p, button {
            font-size: 12px; /* Texto e botões */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Pesquisar Participantes</h1>

    <!-- Formulário de Pesquisa -->
    <form method="POST" class="mb-4">
        <div class="form-group">
            <label for="search_term">Digite o Nome, CPF ou Email:</label>
            <input type="text" name="search_term" id="search_term" class="form-control" placeholder="Ex: João Silva, 123.456.789-00 ou email@email.com" required>
        </div>
        <button type="submit" class="btn btn-primary">Pesquisar</button>
    </form>

    <!-- Mensagens de Erro ou Sucesso -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Resultado da Pesquisa -->
    <?php if ($participante): ?>
        <div class="card">
            <div class="card-header">
                <h5>Detalhes do Participante</h5>
            </div>
            <div class="card-body">
                <p><strong>Nome:</strong> <?= htmlspecialchars($participante['nome']) ?></p>
                <p><strong>CPF:</strong> <?= htmlspecialchars($participante['cpf'] ?? 'Não informado') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($participante['email']) ?></p>

                <!-- Botão de Enviar Certificado -->
                <?php if ($participante['certificado_gerado']): ?>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="search_term" value="<?= htmlspecialchars($participante['email']) ?>">
                        <button type="submit" name="enviar_email" class="btn btn-success">Enviar Certificado por Email</button>
                    </form>
                <?php else: ?>
                    <p class="text-danger mt-3"><strong>Certificado:</strong> Não Gerado</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
