<?php
include('header.php');
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = null;
$success = null;

// Salvar o upload no banco de dados e diretório
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'], $_POST['nome_evento'])) {
    $nomeEvento = trim($_POST['nome_evento']);
    $nomeModelo = $nomeEvento; // Pode ser o nome do evento ou um valor padrão
    $textoCertificado = ''; // Texto padrão inicial
    $diretorio = 'certificados/';
    $arquivo = $_FILES['arquivo'];
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    // Validação apenas para arquivos PDF
    if ($extensao !== 'pdf') {
        $error = "Erro: Apenas arquivos PDF são permitidos.";
    } else {
        $novoNome = uniqid() . '.' . $extensao;
        if (move_uploaded_file($arquivo['tmp_name'], $diretorio . $novoNome)) {
            try {
                $stmt = $conn->prepare("INSERT INTO modelos_certificados (nome_evento, nome_modelo, texto_certificado, arquivo_nome) 
                                        VALUES (:nome_evento, :nome_modelo, :texto_certificado, :arquivo_nome)");
                $stmt->bindParam(':nome_evento', $nomeEvento);
                $stmt->bindParam(':nome_modelo', $nomeModelo);
                $stmt->bindParam(':texto_certificado', $textoCertificado);
                $stmt->bindParam(':arquivo_nome', $novoNome);
                $stmt->execute();
                $success = "Upload realizado e salvo com sucesso!";
            } catch (PDOException $e) {
                $error = "Erro ao salvar no banco: " . $e->getMessage();
            }
        } else {
            $error = "Erro ao fazer upload do arquivo.";
        }
    }
}

// Excluir modelo
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        // Buscar o arquivo associado
        $stmt = $conn->prepare("SELECT arquivo_nome FROM modelos_certificados WHERE id = :id");
        $stmt->bindParam(':id', $deleteId);
        $stmt->execute();
        $modelo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($modelo) {
            $caminhoArquivo = 'certificados/' . $modelo['arquivo_nome'];
            if (file_exists($caminhoArquivo)) {
                unlink($caminhoArquivo); // Deletar arquivo
            }

            // Deletar do banco de dados
            $deleteStmt = $conn->prepare("DELETE FROM modelos_certificados WHERE id = :id");
            $deleteStmt->bindParam(':id', $deleteId);
            $deleteStmt->execute();
            $success = "Modelo excluído com sucesso!";
        } else {
            $error = "Erro: Modelo não encontrado.";
        }
    } catch (PDOException $e) {
        $error = "Erro ao excluir o modelo: " . $e->getMessage();
    }
}

// Função para listar os modelos
function listarModelos($conn) {
    $stmt = $conn->query("SELECT * FROM modelos_certificados");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$modelos = listarModelos($conn);

$baseUrl = 'https://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/';

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
  <title>Modelos de Certificados - UNIDAS</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
  <script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
  </script>
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
            <a href="upload.php" class="nav-link" data-page="upload">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
              <span>Upload Nomes</span>
            </a>
          </li>
          <li class="nav-item active">
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
          <h1 id="page-title">Modelos de Certificados</h1>
        </div>
        <div class="user-menu">
          <span class="user-name"><?php echo htmlspecialchars($nome_usuario); ?></span>
          <div class="user-avatar"><?php echo substr($nome_usuario, 0, 1); ?></div>
        </div>
      </header>

      <main class="content-area">
        <div class="page-content">
          <?php if ($error): ?>
            <div class="alert-message error">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php elseif ($success): ?>
            <div class="alert-message success">
              <?php echo htmlspecialchars($success); ?>
            </div>
          <?php endif; ?>
          
          <h2 class="section-title">Inserir Novos Modelos</h2>
          
          <!-- Upload Form -->
          <div class="model-upload-form">
            <form action="" method="POST" enctype="multipart/form-data">
              <div class="form-group">
                <label for="nome_evento">Nome do Evento:</label>
                <input id="nome_evento" name="nome_evento" type="text" placeholder="Digite o nome do evento" class="form-input" required />
              </div>
              
              <div class="form-group">
                <label>Escolha um arquivo PDF:</label>
                <div class="file-upload-group">
                  <input id="arquivo" name="arquivo" type="file" accept=".pdf" class="file-input" required />
                </div>
                <p class="upload-help-text">Apenas arquivos PDF são permitidos</p>
              </div>
              
              <button type="submit" class="upload-button">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Fazer Upload
              </button>
            </form>
          </div>
          
          <!-- Saved Models -->
          <h2 class="section-title mt-8">Modelos Salvos</h2>
          <div class="models-grid">
            <?php foreach ($modelos as $modelo): ?>
              <div class="model-card">
                <div class="model-image">
                  <canvas id="pdf-canvas-<?= $modelo['id']; ?>" width="150" height="80"></canvas>
                </div>
                <div class="model-content">
                  <h3 class="model-title"><?= htmlspecialchars($modelo['nome_evento']); ?></h3>
                  <div class="model-actions">
                    <a href="certificados/<?= htmlspecialchars($modelo['arquivo_nome']); ?>" target="_blank" class="view-button">
                      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                      Visualizar
                    </a>
                    <a href="?delete_id=<?= $modelo['id']; ?>" class="delete-button" onclick="return confirm('Deseja excluir este modelo?');">
                      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                      </svg>
                      Excluir
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <footer class="page-footer">
            <p>© 2023 - 2025 | Gerador de Certificados - Todos os direitos reservados.</p>
          </footer>
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
      
      // PDF rendering
      const pdfDirectory = "<?= $baseUrl; ?>certificados/";
      const modelos = <?= json_encode($modelos); ?>;

      modelos.forEach(modelo => {
        const canvas = document.getElementById(`pdf-canvas-${modelo.id}`);
        const pdfUrl = pdfDirectory + modelo.arquivo_nome;

        if (canvas) {
          const context = canvas.getContext('2d');
          const scale = 1.5;

          pdfjsLib.getDocument(pdfUrl).promise.then((pdf) => {
            pdf.getPage(1).then((page) => {
              const viewport = page.getViewport({ scale: 1.5 });
              canvas.width = viewport.width;
              canvas.height = viewport.height;

              const renderContext = {
                canvasContext: context,
                viewport: viewport,
              };
              page.render(renderContext).promise.then(() => {
                console.log('PDF renderizado com sucesso!');
              }).catch((err) => {
                console.error('Erro ao renderizar a página:', err);
              });
            });
          }).catch((err) => {
            console.error('Erro ao carregar o PDF:', err);
          });
        }
      });
      
      // File input change event
      const fileInput = document.getElementById('arquivo');
      const fileHelpText = document.querySelector('.upload-help-text');
      
      if (fileInput && fileHelpText) {
        fileInput.addEventListener('change', function() {
          if (this.files.length > 0) {
            fileHelpText.textContent = `Arquivo selecionado: ${this.files[0].name}`;
          } else {
            fileHelpText.textContent = 'Nenhum arquivo escolhido';
          }
        });
      }
    });
  </script>
</body>
</html>