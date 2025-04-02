<?php
// session_start();

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
      <header class="header shadow-sm bg-white">
        <div class="header-title">
          <h1 id="page-title" class="text-xl font-semibold text-gray-800">Modelos de Certificados</h1>
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

        <div class="page-content px-6 py-8">
          <!-- Formulário de Upload -->
          <div class="filter-section bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Upload de Modelo de Certificado</h2>
            <form method="POST" enctype="multipart/form-data" class="form-grid grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-group">
                <label for="nome_evento" class="block text-sm font-medium text-gray-700 mb-2">Nome do Evento</label>
                <input type="text" name="nome_evento" id="nome_evento" class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
              </div>
              <div class="form-group">
                <label for="arquivo" class="block text-sm font-medium text-gray-700 mb-2">Arquivo PDF (Modelo)</label>
                <input type="file" name="arquivo" id="arquivo" accept=".pdf" class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
              </div>
              <div class="col-span-2">
                <button type="submit" class="primary-button inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <svg class="icon w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                  </svg>
                  Fazer Upload
                </button>
              </div>
            </form>
          </div>

          <!-- Lista de Modelos -->
          <div class="certificates-table-container bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Modelos Cadastrados</h2>
            
            <div class="certificates-table overflow-x-auto">
              <table class="data-table w-full">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Evento</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Modelo</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arquivo</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <?php if (!empty($modelos)): ?>
                    <?php foreach ($modelos as $modelo): ?>
                      <tr class="hover:bg-gray-50">
                        <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($modelo['id']); ?></td>
                        <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($modelo['nome_evento']); ?></td>
                        <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($modelo['nome_modelo']); ?></td>
                        <td class="p-3 text-sm text-gray-700">
                          <a href="certificados/<?php echo htmlspecialchars($modelo['arquivo_nome']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                            Ver PDF
                          </a>
                        </td>
                        <td class="p-3 text-sm actions-column">
                          <a href="textos-certificados.php?modelo_id=<?php echo $modelo['id']; ?>" class="action-button edit inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium hover:bg-blue-100 mr-2">
                            <svg class="icon w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Editar Texto
                          </a>
                          <a href="?delete_id=<?php echo $modelo['id']; ?>" class="action-button delete inline-flex items-center px-3 py-1 bg-red-50 text-red-700 rounded-md text-xs font-medium hover:bg-red-100" onclick="return confirm('Tem certeza que deseja excluir este modelo?');">
                            <svg class="icon w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                              <line x1="10" y1="11" x2="10" y2="17"></line>
                              <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                            Excluir
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="p-3 text-sm text-gray-500 text-center">Nenhum modelo cadastrado.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Visualizador de PDF -->
          <div class="pdf-preview-container bg-white p-6 rounded-xl shadow-sm mt-6 border border-gray-100" style="display: none;">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Visualização do Modelo</h2>
            <div id="pdf-viewer" class="pdf-viewer border border-gray-200 rounded-lg overflow-hidden" style="height: 500px;"></div>
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
      
      // PDF preview functionality
      const pdfLinks = document.querySelectorAll('a[href$=".pdf"]');
      const pdfPreviewContainer = document.querySelector('.pdf-preview-container');
      const pdfViewer = document.getElementById('pdf-viewer');
      
      pdfLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const pdfUrl = this.getAttribute('href');
          
          // Show the preview container
          pdfPreviewContainer.style.display = 'block';
          
          // Scroll to the preview
          pdfPreviewContainer.scrollIntoView({ behavior: 'smooth' });
          
          // Load the PDF
          pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
            // Clear previous content
            pdfViewer.innerHTML = '';
            
            // Get the first page
            pdf.getPage(1).then(function(page) {
              const viewport = page.getViewport({ scale: 1.5 });
              
              // Prepare canvas
              const canvas = document.createElement('canvas');
              const context = canvas.getContext('2d');
              canvas.height = viewport.height;
              canvas.width = viewport.width;
              
              // Render PDF page
              pdfViewer.appendChild(canvas);
              page.render({
                canvasContext: context,
                viewport: viewport
              });
            });
          }).catch(function(error) {
            console.error('Error loading PDF:', error);
            pdfViewer.innerHTML = '<p class="text-red-500 p-4">Erro ao carregar o PDF. Por favor, tente novamente.</p>';
          });
        });
      });
      
      // Form validation
      const uploadForm = document.querySelector('form[enctype="multipart/form-data"]');
      if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
          const fileInput = this.querySelector('input[type="file"]');
          const nameInput = this.querySelector('input[name="nome_evento"]');
          
          if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('Por favor, selecione um arquivo PDF.');
            return false;
          }
          
          const fileName = fileInput.files[0].name;
          if (!fileName.toLowerCase().endsWith('.pdf')) {
            e.preventDefault();
            alert('Por favor, selecione apenas arquivos PDF.');
            return false;
          }
          
          if (nameInput.value.trim() === '') {
            e.preventDefault();
            alert('Por favor, insira o nome do evento.');
            return false;
          }
          
          return true;
        });
      }
    });
  </script>
</body>
</html>

<?php include('footer.php'); ?>