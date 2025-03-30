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
$participantes = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_term'])) {
    try {

        if($_SESSION['user_nivel'] === 2){

            $search_term = '%' . $_POST['search_term'] . '%';
            $user_admin = $_SESSION['user_id'];
            
            $stmt = $conn->prepare("
                SELECT 
                    n.id, 
                    n.nome, 
                    n.cpf, 
                    n.email, 
                    n.certificado_gerado, 
                    n.evento,
                    n.data_inicio, 
                    n.data_final, 
                    n.admin_id,
                    u.nome AS admin_nome 
                FROM nomes n
                LEFT JOIN usuarios u ON n.admin_id = u.id
                WHERE (n.nome LIKE :search_term 
                       OR n.cpf LIKE :search_term 
                       OR n.email LIKE :search_term)
                  AND n.admin_id = :user_admin
            ");
            
            $stmt->bindParam(':search_term', $search_term);
            $stmt->bindParam(':user_admin', $user_admin);
            $stmt->execute();
            $participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
        } else {
            $search_term = '%' . $_POST['search_term'] . '%';

            $stmt = $conn->prepare("
                SELECT 
                    n.id, 
                    n.nome, 
                    n.cpf, 
                    n.email, 
                    n.certificado_gerado, 
                    n.evento,
                    n.data_inicio, 
                    n.data_final, 
                    n.admin_id,
                    u.nome AS admin_nome 
                FROM nomes n
                LEFT JOIN usuarios u ON n.admin_id = u.id
                WHERE n.nome LIKE :search_term 
                   OR n.cpf LIKE :search_term 
                   OR n.email LIKE :search_term
            ");
            
            $stmt->bindParam(':search_term', $search_term);
            $stmt->execute();
            $participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
        }
       
        if (!$participantes) {
            $error = 'Nenhum participante encontrado.';
        } elseif (isset($_POST['enviar_email'])) {
            foreach ($participantes as $participante){
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

        :root {
          --background: #ffffff;
          --foreground: #1a1a1a;
          --card: #ffffff;
          --card-foreground: #1a1a1a;
          --primary: #1a1a1a;
          --primary-foreground: #fafafa;
          --secondary: #f4f4f5;
          --secondary-foreground: #1a1a1a;
          --muted: #f4f4f5;
          --muted-foreground: #757575;
          --accent: #f4f4f5;
          --accent-foreground: #1a1a1a;
          --destructive: #ef4444;
          --destructive-foreground: #fafafa;
          --border: #e5e5e5;
          --input: #e5e5e5;
          --ring: #1a1a1a;
          --radius: 0.75rem;
        }
        
        form{
            width: 100%;
        }
        
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        
        body {
          background: linear-gradient(180deg, #fafafa 0%, #f5f5f5 100%);
          color: var(--foreground);
          font-family: system-ui, -apple-system, sans-serif;
          -webkit-font-smoothing: antialiased;
        }
        
        .container {
          min-height: 100vh;
          padding: 2rem 0;
        }
        
        .participant-search {
          max-width: 42rem;
          margin: 0 auto;
          padding: 1.5rem;
        }
        
        .header {
          text-align: center;
          margin-bottom: 1.5rem;
        }
        
        .header h1 {
          font-size: 1.5rem;
          font-weight: 600;
          letter-spacing: -0.025em;
          margin-bottom: 0.5rem;
        }
        
        .header p {
          font-size: 0.875rem;
          color: var(--muted-foreground);
        }
        
        .search-container {
          display: flex;
          gap: 0.5rem;
          margin-bottom: 1.5rem;
        }
        
        .search-wrapper {
          position: relative;
          flex: 1;
          display: flex;
          gap: 8px;
        }
        
        .search-input {
          width: 100%;
          height: 2.5rem;
          padding: 0.5rem 0.75rem 0.5rem 2.5rem;
          border: 1px solid var(--border);
          border-radius: var(--radius);
          font-size: 0.875rem;
          background-color: var(--background);
          transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .search-input:focus {
          outline: none;
          border-color: var(--ring);
          box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.1);
        }
        
        .search-icon {
          position: absolute;
          left: 0.75rem;
          top: 50%;
          transform: translateY(-50%);
          color: var(--muted-foreground);
          width: 1.25rem;
          height: 1.25rem;
        }
        
        .button {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 0.5rem;
          height: 2.5rem;
          padding: 0 1rem;
          border: none;
          border-radius: var(--radius);
          background-color: var(--primary);
          color: var(--primary-foreground);
          font-size: 0.875rem;
          font-weight: 500;
          cursor: pointer;
          transition: background-color 0.2s;
        }
        
        .button:hover {
          background-color: rgba(26, 26, 26, 0.9);
        }
        
        .button:focus {
          outline: none;
          box-shadow: 0 0 0 2px var(--ring);
        }
        
        .card {
          background-color: var(--card);
          border: 1px solid var(--border);
          border-radius: var(--radius);
          padding: 1.5rem;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
          margin-bottom: 10px;
        }
        
        .card-title {
          font-size: 1.125rem;
          font-weight: 600;
          margin-bottom: 1rem;
        }
        
        .details-grid {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 0.5rem;
          margin-bottom: 1rem;
        }
        
        .detail-item {
          margin-bottom: 0.75rem;
        }
        
        .detail-label {
          font-size: 0.875rem;
          color: var(--muted-foreground);
          margin-bottom: 0.25rem;
        }
        
        .detail-value {
          font-weight: 500;
        }
        
        .send-button {
          width: 100%;
          margin-top: 1rem;
        }
        
        /*@keyframes fadeIn {*/
        /*  from {*/
        /*    opacity: 0;*/
        /*    transform: translateY(10px);*/
        /*  }*/
        /*  to {*/
        /*    opacity: 1;*/
        /*    transform: translateY(0);*/
        /*  }*/
        /*}*/
        
        /*.animate-fade-in {*/
        /*  animation: fadeIn 0.5s ease-out forwards;*/
        /*}*/
        
        @media (max-width: 640px) {
          .participant-search {
            padding: 1rem;
          }
        
          .details-grid {
            grid-template-columns: 1fr;
          }
        }

    </style>
</head>
<body>
<div class="participant-search">
    <div class="header">
        <h1>Pesquisar Participantes</h1>
        <p>Digite o Nome, CPF ou Email do participante para buscar</p>
    </div>

    <div class="search-container">
        <form method="POST">
            <div class="search-wrapper">
                <input
                    type="text"
                    class="search-input"
                    name="search_term"
                    placeholder="Ex: João Silva, 123.456.789-00 ou email@email.com"
                    value="<?= isset($_POST['search_term']) ? htmlspecialchars($_POST['search_term']) : '' ?>"
                />
                <button type="submit" class="button">Pesquisar</button>
            </div>
        </form>
    </div>
    
    <!-- Mensagens de Erro ou Sucesso -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($participantes)): ?>
        <?php foreach ($participantes as $participante): ?>
            <div class="card animate-fade-in">
                <h2 class="card-title">Detalhes do Participante</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <p class="detail-label">Nome</p>
                        <p class="detail-value"><?= htmlspecialchars($participante['nome']) ?></p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-label">CPF</p>
                        <p class="detail-value"><?= htmlspecialchars($participante['cpf'] ?? 'Não informado') ?></p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-label">Email</p>
                        <p class="detail-value"><?= htmlspecialchars($participante['email']) ?></p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-label">Evento</p>
                        <p class="detail-value"><?= htmlspecialchars($participante['evento'] ?? 'Não informado') ?></p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-label">Emitido por</p>
                        <p class="detail-value"><?= htmlspecialchars($participante['admin_nome'] ?? 'Não informado') ?></p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-label">Data do Evento</p>
                        <p class="detail-value"><?= htmlspecialchars($participante['data_inicio'] ?? 'Não informado') ?> ~ <?= htmlspecialchars($participante['data_final'] ?? 'Não informado') ?></p>
                    </div>
                </div>

                <?php if ($participante['certificado_gerado']): ?>
                    <form method="POST">
                        <input type="hidden" name="search_term" value="<?= htmlspecialchars($participante['email']) ?>">
                        <button type="submit" name="enviar_email" class="button send-button">
                            Enviar Certificado por Email
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-danger mt-3"><strong>Certificado:</strong> Não Gerado</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
