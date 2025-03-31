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
  <title>Pesquisar - UNIDAS Certificados</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">
  <div class="dashboard-container">
    <div class="sidebar">
      <div class="sidebar-header">
        <img src="/img/logo-unidas.png" alt="UNIDAS" class="sidebar-logo">
      </div>
      <nav class="sidebar-nav">
        <ul class="nav-list">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link" data-page="dashboard">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="upload-nomes.php" class="nav-link" data-page="upload">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
              <span>Upload Nomes</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="modelos-certificados.php" class="nav-link" data-page="models">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
              </svg>
              <span>Modelos Certificados</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="textos-certificados.php" class="nav-link" data-page="texts">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              <span>Textos Certificados</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="gerar-certificados.php" class="nav-link" data-page="generate">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
              </svg>
              <span>Gerar Certificados</span>
            </a>
          </li>
          <li class="nav-item active">
            <a href="pesquisar.php" class="nav-link" data-page="search">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
              <span>Pesquisar</span>
            </a>
          </li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="logout-button" id="logout-btn">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Sair</span>
        </a>
      </div>
    </div>

    <div class="main-content">
      <header class="header">
        <div class="header-title">
          <h1 id="page-title">Pesquisar Participantes</h1>
        </div>
        <div class="user-menu">
          <span class="user-name">Administrador</span>
          <div class="user-avatar">A</div>
        </div>
      </header>

      <main class="content-area">
        <div class="page-content" id="search-content">
          <!-- Mensagens de Erro ou Sucesso -->
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
          <?php endif; ?>

          <div class="search-container">
            <p class="search-description">Digite o Nome, CPF ou Email do participante para buscar</p>
            
            <form class="search-form" method="POST">
              <div class="search-input-group">
                <input 
                  type="text"
                  name="search_term"
                  class="search-input" 
                  placeholder="Ex: João Silva, 123.456.789-00 ou email@email.com"
                  value="<?= isset($_POST['search_term']) ? htmlspecialchars($_POST['search_term']) : '' ?>"
                />
                <button type="submit" class="search-button">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  </svg>
                  Pesquisar
                </button>
              </div>
            </form>
            
            <?php if (!empty($participantes)): ?>
            <div class="search-results">
              <h2 class="results-title">Resultados da pesquisa</h2>
              
              <div class="result-cards">
                <?php foreach ($participantes as $participante): ?>
                <div class="result-card">
                  <div class="result-info">
                    <h3 class="participant-name"><?= htmlspecialchars($participante['nome']) ?></h3>
                    <p class="participant-details">
                      CPF: <?= htmlspecialchars($participante['cpf'] ?? 'Não informado') ?> | 
                      Email: <?= htmlspecialchars($participante['email']) ?>
                    </p>
                    <p class="participant-details">
                      Evento: <?= htmlspecialchars($participante['evento'] ?? 'Não informado') ?> | 
                      Emitido por: <?= htmlspecialchars($participante['admin_nome'] ?? 'Não informado') ?>
                    </p>
                    <p class="participant-details">
                      Data: <?= htmlspecialchars($participante['data_inicio'] ?? 'Não informado') ?> ~ 
                      <?= htmlspecialchars($participante['data_final'] ?? 'Não informado') ?>
                    </p>
                  </div>
                  <div class="result-actions">
                    <?php if ($participante['certificado_gerado']): ?>
                      <span class="certificate-count">Certificado disponível</span>
                      <form method="POST" style="display: inline;">
                        <input type="hidden" name="search_term" value="<?= htmlspecialchars($participante['email']) ?>">
                        <button type="submit" name="enviar_email" class="view-button">
                          Enviar por Email
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="certificate-count certificate-not-available">Certificado não gerado</span>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Logout button functionality
      document.getElementById('logout-btn').addEventListener('click', function() {
        window.location.href = 'logout.php';
      });
    });
  </script>
</body>
</html>
