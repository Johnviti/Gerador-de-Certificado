<?php
include('header.php');
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Mensagens de feedback
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $textos_editados = [];
        
        // Coletar dados enviados
        foreach ($_POST['textos'] as $index => $texto) {
            $nome_modelo = trim($_POST['nomes_modelos'][$index]);
            
            if (!empty($nome_modelo) && !empty($texto)) {
                $textos_editados[$nome_modelo] = trim($texto);
            }
        }

        foreach ($textos_editados as $nome_modelo => $texto) {
            // Verificar se o modelo já existe pelo nome
            $stmt = $conn->prepare("
                SELECT * FROM textos_certificados 
                WHERE nome_modelo = :nome_modelo
            ");
            $stmt->bindParam(':nome_modelo', $nome_modelo);
            $stmt->execute();
            $existingModel = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingModel) {
                // Atualizar se já existir
                $stmt = $conn->prepare("
                    UPDATE textos_certificados 
                    SET texto_certificado = :texto_certificado
                    WHERE nome_modelo = :nome_modelo
                ");
                $stmt->bindParam(':nome_modelo', $nome_modelo);
                $stmt->bindParam(':texto_certificado', $texto);
                $stmt->execute();
            } else {
                // Inserir novo registro se não existir
                $stmt = $conn->prepare("
                    INSERT INTO textos_certificados (nome_modelo, texto_certificado) 
                    VALUES (:nome_modelo, :texto_certificado)
                ");
                $stmt->bindParam(':nome_modelo', $nome_modelo);
                $stmt->bindParam(':texto_certificado', $texto);
                $stmt->execute();
            }
        }
        $success = "Textos salvos com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao salvar os textos: " . $e->getMessage();
    }
}

// Recupera os textos do banco de dados
try {
    $stmt = $conn->query("SELECT nome_modelo, texto_certificado FROM textos_certificados");
    $textos_predefinidos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $textos_predefinidos = [];
    $error = "Erro ao carregar textos: " . $e->getMessage();
}

// Adiciona uma nova linha vazia ao final do array
$textos_predefinidos[''] = '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Textos Certificados - UNIDAS</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">
  <div class="dashboard-container">
    <div class="sidebar">
      <div class="sidebar-header">
        <img src="/lovable-uploads/7500b379-16df-4ef0-b7f2-92436d4a873e.png" alt="UNIDAS" class="sidebar-logo">
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
          <li class="nav-item active">
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
          <li class="nav-item">
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
          <h1 id="page-title">Textos dos Certificados</h1>
        </div>
        <div class="user-menu">
          <span class="user-name">Administrador</span>
          <div class="user-avatar">A</div>
        </div>
      </header>

      <main class="content-area">
        <!-- Alertas de sucesso ou erro -->
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="page-content">
          <div class="text-templates-container">
            <h2 class="section-title">Selecionar ou Editar Modelos</h2>
            
            <form method="POST">
              <div class="text-templates">
                <?php if (!empty($textos_predefinidos)): ?>
                  <?php $index = 0; ?>
                  <?php foreach ($textos_predefinidos as $nome_modelo => $texto): ?>
                    <div class="text-template-item">
                      <div class="template-header">
                        <input type="text" name="nomes_modelos[<?= $index ?>]" value="<?= htmlspecialchars($nome_modelo) ?>" placeholder="Nome do Modelo" class="template-name-input" />
                      </div>
                      <textarea name="textos[<?= $index ?>]" class="template-text-input" placeholder="Digite o texto do certificado"><?= htmlspecialchars($texto) ?></textarea>
                    </div>
                    <?php $index++; ?>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="text-template-item">
                    <div class="template-header">
                      <input type="text" name="nomes_modelos[0]" placeholder="Nome do Modelo" class="template-name-input" />
                    </div>
                    <textarea name="textos[0]" class="template-text-input" placeholder="Digite o texto do certificado"></textarea>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="form-actions">
                <button type="submit" class="btn-primary">Salvar Modelos</button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script>
    // Script para gerenciar navegação e interações
    document.addEventListener('DOMContentLoaded', function() {
      // Logout button functionality
      document.getElementById('logout-btn').addEventListener('click', function() {
        window.location.href = 'logout.php';
      });
    });
  </script>
</body>
</html>
