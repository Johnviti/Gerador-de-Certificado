<?php
session_start();
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

// Obter nome do usuário logado
$usuario_logado = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_logado]);
$nome_usuario = $stmt->fetchColumn();
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
<body class="bg-gray-50">
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
      <header class="header shadow-sm bg-white">
        <div class="header-title">
          <h1 id="page-title" class="text-xl font-semibold text-gray-800">Textos dos Certificados</h1>
        </div>
        <div class="user-menu">
          <span class="user-name text-gray-700"><?php echo htmlspecialchars($nome_usuario); ?></span>
          <div class="user-avatar shadow-md bg-blue-600"><?php echo substr($nome_usuario, 0, 1); ?></div>
        </div>
      </header>

      <main class="content-area">
        <?php if ($error): ?>
          <div class="alert-message bg-red-50 text-red-700 p-4 rounded-lg mb-6">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
          <div class="alert-message bg-green-50 text-green-700 p-4 rounded-lg mb-6">
            <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>

        <div class="page-content px-6 py-8 ">
          <div class="text-help-container bg-white p-6 mb-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Instruções de Uso</h2>
            <div class="text-sm text-gray-700 space-y-3">
              <p>Os textos dos certificados podem incluir variáveis que serão substituídas automaticamente ao gerar o certificado:</p>
              <ul class="list-disc pl-5 space-y-1">
                <li><strong>[Nome do Participante]</strong> - Nome completo do participante</li>
                <li><strong>[Nome do Evento]</strong> - Nome do evento</li>
                <li><strong>[Data do Evento]</strong> - Data de início e fim do evento</li>
                <li><strong>[Local do Evento]</strong> - Local onde o evento foi realizado</li>
                <li><strong>[Duração do Evento]</strong> - Carga horária total do evento</li>
              </ul>
              <p class="mt-4">Exemplo de texto:</p>
              <div class="bg-gray-50 p-3 rounded border border-gray-200 text-gray-600">
                Certificamos que <strong>[Nome do Participante]</strong> participou do evento <strong>[Nome do Evento]</strong>, realizado no período de <strong>[Data do Evento]</strong>, em <strong>[Local do Evento]</strong>, com carga horária de <strong>[Duração do Evento]</strong>.
              </div>
            </div>
          </div>

          <div class="text-templates-container bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-lg font-semibold text-gray-800">Selecionar ou Editar Modelos de Texto</h2>
              <div class="flex space-x-3">
                <button type="button" id="add-template-btn" class="secondary-button inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                  <svg class="icon w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                  </svg>
                  Adicionar Modelo
                </button>
                <button type="submit" form="text-templates-form" class="primary-button inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <svg class="icon w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                  </svg>
                  Salvar Modelos
                </button>
              </div>
            </div>
            
            <form method="POST" id="text-templates-form">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (!empty($textos_predefinidos)): ?>
                  <?php $index = 0; ?>
                  <?php foreach ($textos_predefinidos as $nome_modelo => $texto): ?>
                    <div class="text-template-item bg-gray-50 p-4 rounded-lg border border-gray-200">
                      <div class="template-header mb-3">
                        <label for="nome_modelo_<?= $index ?>" class="block text-sm font-medium text-gray-700 mb-2">Nome do Modelo</label>
                        <input type="text" id="nome_modelo_<?= $index ?>" name="nomes_modelos[<?= $index ?>]" value="<?= htmlspecialchars($nome_modelo) ?>" placeholder="Nome do Modelo" class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                      </div>
                      <div class="template-body">
                        <label for="texto_<?= $index ?>" class="block text-sm font-medium text-gray-700 mb-2">Texto do Certificado</label>
                        <textarea id="texto_<?= $index ?>" name="textos[<?= $index ?>]" class="form-textarea w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" rows="6" placeholder="Digite o texto do certificado"><?= htmlspecialchars($texto) ?></textarea>
                        <div class="text-xs text-gray-500 mt-2">
                          <p>Variáveis disponíveis: [Nome do Participante], [Nome do Evento], [Data do Evento], [Local do Evento], [Duração do Evento]</p>
                        </div>
                      </div>
                    </div>
                    <?php $index++; ?>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="text-template-item bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="template-header mb-3">
                      <label for="nome_modelo_0" class="block text-sm font-medium text-gray-700 mb-2">Nome do Modelo</label>
                      <input type="text" id="nome_modelo_0" name="nomes_modelos[0]" placeholder="Nome do Modelo" class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                    </div>
                    <div class="template-body">
                      <label for="texto_0" class="block text-sm font-medium text-gray-700 mb-2">Texto do Certificado</label>
                      <textarea id="texto_0" name="textos[0]" class="form-textarea w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" rows="6" placeholder="Digite o texto do certificado"></textarea>
                      <div class="text-xs text-gray-500 mt-2">
                        <p>Variáveis disponíveis: [Nome do Participante], [Nome do Evento], [Data do Evento], [Local do Evento], [Duração do Evento]</p>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </form>
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
      
      // Adicionar novo modelo
      const addNewTemplate = () => {
        const templates = document.querySelector('.text-templates');
        const templateItems = templates.querySelectorAll('.text-template-item');
        const lastIndex = templateItems.length;
        
        const newTemplate = document.createElement('div');
        newTemplate.className = 'text-template-item bg-gray-50 p-4 rounded-lg border border-gray-200';
        newTemplate.innerHTML = `
          <div class="template-header mb-3">
            <label for="nome_modelo_${lastIndex}" class="block text-sm font-medium text-gray-700 mb-2">Nome do Modelo</label>
            <input type="text" id="nome_modelo_${lastIndex}" name="nomes_modelos[${lastIndex}]" placeholder="Nome do Modelo" class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
          </div>
          <div class="template-body">
            <label for="texto_${lastIndex}" class="block text-sm font-medium text-gray-700 mb-2">Texto do Certificado</label>
            <textarea id="texto_${lastIndex}" name="textos[${lastIndex}]" class="form-textarea w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" rows="6" placeholder="Digite o texto do certificado"></textarea>
            <div class="text-xs text-gray-500 mt-2">
              <p>Variáveis disponíveis: [Nome do Participante], [Nome do Evento], [Data do Evento], [Local do Evento], [Duração do Evento]</p>
            </div>
          </div>
        `;
        
        templates.appendChild(newTemplate);
      };
      
      // Adicionar evento ao botão de adicionar modelo
      document.getElementById('add-template-btn').addEventListener('click', addNewTemplate);
    });
  </script>
</body>
</html>
