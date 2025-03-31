<?php
include('header.php');
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mensagem de feedback
$message = '';
$messageType = '';

// Processa o formulário manual
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['manual_submit'])) {
    try {
        $nome = trim($_POST['nome']);
        $cpf = trim($_POST['cpf']);
        $evento = trim($_POST['evento']);
        $instituicao = trim($_POST['instituicao']);
        $data_inicio = date('Y-m-d', strtotime($_POST['data_inicio']));
        $data_final = date('Y-m-d', strtotime($_POST['data_final']));
        $carga_horaria = (int)$_POST['carga_horaria'];
        $email = trim($_POST['email']);
        $telefone = trim($_POST['telefone']);
        $user_admin = $_SESSION['user_id'];

        if (empty($nome) || empty($cpf) || empty($evento) || empty($instituicao) || empty($email)) {
            $message = "Erro: Todos os campos obrigatórios devem ser preenchidos.";
            $messageType = "error";
        } else {
            // Verifica se o registro já existe com base no CPF e Evento
            $stmt_check = $conn->prepare("SELECT * FROM nomes WHERE cpf = :cpf AND evento = :evento");
            $stmt_check->bindParam(':cpf', $cpf);
            $stmt_check->bindParam(':evento', $evento);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                $message = "Aviso: Já existe um registro com este CPF e Evento.";
                $messageType = "warning";
            } else {
                $stmt = $conn->prepare("INSERT INTO nomes 
                    (nome, cpf, evento, instituicao, data_inicio, data_final, carga_horaria, email, telefone, admin_id) 
                    VALUES 
                    (:nome, :cpf, :evento, :instituicao, :data_inicio, :data_final, :carga_horaria, :email, :telefone, :admin_id)");

                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':cpf', $cpf);
                $stmt->bindParam(':evento', $evento);
                $stmt->bindParam(':instituicao', $instituicao);
                $stmt->bindParam(':data_inicio', $data_inicio);
                $stmt->bindParam(':data_final', $data_final);
                $stmt->bindParam(':carga_horaria', $carga_horaria);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':telefone', $telefone);
                $stmt->bindParam(':admin_id', $user_admin);

                $stmt->execute();
                $message = "Dados inseridos com sucesso!";
                $messageType = "success";
            }
        }
    } catch (PDOException $e) {
        $message = "Erro ao inserir dados: " . $e->getMessage();
        $messageType = "error";
    }
}

// Processa o upload de CSV
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivo_csv'])) {
    $arquivo_temp = $_FILES['arquivo_csv']['tmp_name'];
    $arquivo_nome = $_FILES['arquivo_csv']['name'];
    $extensao = pathinfo($arquivo_nome, PATHINFO_EXTENSION);

    if ($extensao == 'csv') {
        if (is_uploaded_file($arquivo_temp)) {
            try {
                // Configurações iniciais
                $handle = fopen($arquivo_temp, 'r');
                $linha_teste = fgets($handle); // Lê a primeira linha para detectar o delimitador
                fclose($handle);

                $delimitador = (strpos($linha_teste, ';') !== false) ? ';' : ',';

                // Reabre o arquivo com a codificação correta
                $handle = fopen($arquivo_temp, 'r');
                if (!$handle) {
                    throw new Exception("Erro ao abrir o arquivo CSV.");
                }

                // Ignorar cabeçalho
                fgetcsv($handle, 1000, $delimitador);

                $user_admin = $_SESSION['user_id'];
                $successCount = 0;
                $errorCount = 0;

                $stmt = $conn->prepare("
                    INSERT INTO nomes 
                    (nome, cpf, evento, instituicao, data_inicio, data_final, carga_horaria, email, telefone, admin_id) 
                    VALUES 
                    (:nome, :cpf, :evento, :instituicao, :data_inicio, :data_final, :carga_horaria, :email, :telefone, :admin_id)
                ");

                while (($dados = fgetcsv($handle, 1000, $delimitador)) !== false) {
                    $nome = trim($dados[0]);
                    $cpf = preg_replace('/\D/', '', $dados[1]);
                    $instituicao = trim($dados[2]);
                    $evento = trim($dados[3]);
                    $data_inicio = date('Y-m-d', strtotime($dados[5]));
                    $data_final = date('Y-m-d', strtotime($dados[6]));
                    $carga_horaria = (int)$dados[7];
                    $email = trim($dados[8]);
                    $telefone = isset($dados[9]) ? trim($dados[9]) : null;

                    if (empty($nome) || empty($cpf) || empty($email)) {
                        $errorCount++;
                        continue;
                    }

                    try {
                        $stmt->bindParam(':nome', $nome);
                        $stmt->bindParam(':cpf', $cpf);
                        $stmt->bindParam(':evento', $evento);
                        $stmt->bindParam(':instituicao', $instituicao);
                        $stmt->bindParam(':data_inicio', $data_inicio);
                        $stmt->bindParam(':data_final', $data_final);
                        $stmt->bindParam(':carga_horaria', $carga_horaria);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':telefone', $telefone);
                        $stmt->bindParam(':admin_id', $user_admin); 

                        $stmt->execute();
                        $successCount++;
                    } catch (PDOException $e) {
                        $errorCount++;
                    }
                }

                fclose($handle);
                
                if ($successCount > 0) {
                    $message = "Importação concluída: $successCount registros importados com sucesso" . ($errorCount > 0 ? ", $errorCount com erro." : ".");
                    $messageType = $errorCount > 0 ? "warning" : "success";
                } else {
                    $message = "Nenhum registro foi importado. Verifique o formato do arquivo.";
                    $messageType = "error";
                }
                
            } catch (Exception $e) {
                $message = "Erro ao processar o arquivo CSV: " . $e->getMessage();
                $messageType = "error";
            }
        } else {
            $message = "Erro no upload do arquivo.";
            $messageType = "error";
        }
    } else {
        $message = "Por favor, envie um arquivo CSV (.csv).";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Nomes - UNIDAS Certificados</title>
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
          <li class="nav-item active">
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
          <h1 id="page-title">Upload e Edição de Nomes</h1>
        </div>
        <div class="user-menu">
          <span class="user-name">Administrador</span>
          <div class="user-avatar">A</div>
        </div>
      </header>

      <main class="content-area">
        <?php if (!empty($message)): ?>
          <div class="alert <?php echo $messageType === 'success' ? 'alert-success' : ($messageType === 'warning' ? 'alert-warning' : 'alert-danger'); ?>">
            <?php echo $message; ?>
          </div>
        <?php endif; ?>
        
        <div class="page-content">
          <div class="mb-6">
            <a href="https://unidas.digital/wp-content/uploads/2025/03/Modelo.csv" class="download-button" download>
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Baixar Modelo Planilha Oficial
            </a>
          </div>

          <div class="tab-container">
            <div class="tab-header">
              <button class="tab-button active" data-tab="upload">Upload CSV</button>
              <button class="tab-button" data-tab="manual">Adicionar Manualmente</button>
            </div>

            <div class="tab-content">
              <div class="tab-pane active" id="upload-tab">
                <div class="upload-container">
                  <h2 class="section-title">Selecione o arquivo CSV</h2>
                  
                  <form action="upload-nomes.php" method="post" enctype="multipart/form-data" class="file-upload-section">
                    <div class="file-upload-group">
                      <input type="file" accept=".csv" id="arquivo_csv" name="arquivo_csv" class="file-input" required />
                      <button type="submit" class="upload-button">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                          <polyline points="17 8 12 3 7 8"></polyline>
                          <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        Upload CSV
                      </button>
                    </div>
                    <p class="upload-help-text">
                      Formato esperado: Nome, CPF, Instituição, Evento, Data Início, Data Fim, Carga Horária, Email, Telefone
                    </p>
                  </form>
                </div>
              </div>

              <div class="tab-pane" id="manual-tab">
                <div class="manual-form-container">
                  <h2 class="section-title">Adicionar ou Editar Manualmente</h2>
                  
                  <form action="upload-nomes.php" method="post" class="manual-form">
                    <div class="form-grid">
                      <div class="form-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" placeholder="Nome completo" class="form-input" required />
                      </div>
                      
                      <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" class="form-input" required />
                      </div>
                      
                      <div class="form-group">
                        <label for="evento">Evento</label>
                        <input type="text" id="evento" name="evento" placeholder="Nome do evento" class="form-input" required />
                      </div>
                      
                      <div class="form-group">
                        <label for="instituicao">Instituição</label>
                        <input type="text" id="instituicao" name="instituicao" placeholder="Nome da instituição" class="form-input" required />
                      </div>
                      
                      <div class="form-group">
                        <label for="data_inicio">Data de Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-input" required />
                      </div>
                      
                      <div class="form-group">
                        <label for="data_final">Data Final</label>
                        <input type="date" id="data_final" name="data_final" class="form-input" required />
                      </div>
                      
                      <div class="form-group">
                        <label for="carga_horaria">Carga Horária</label>
                        <input type="number" id="carga_horaria" name="carga_horaria" placeholder="Ex: 40" class="form-input" required />
                      </div>
                      
                      <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" placeholder="(XX) XXXXX-XXXX" class="form-input" />
                      </div>
                      
                      <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" placeholder="email@exemplo.com" class="form-input" required />
                      </div>
                    </div>
                    
                    <div class="form-actions">
                      <button type="submit" name="manual_submit" class="submit-button">Adicionar/Editar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <footer class="page-footer">
            <p>© 2023 - <?php echo date('Y'); ?> | Gerador de Certificados - Todos os direitos reservados.</p>
          </footer>
        </div>
      </main>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Tab switching functionality
      const tabButtons = document.querySelectorAll('.tab-button');
      const tabPanes = document.querySelectorAll('.tab-pane');
      
      tabButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Remove active class from all buttons and panes
          tabButtons.forEach(btn => btn.classList.remove('active'));
          tabPanes.forEach(pane => pane.classList.remove('active'));
          
          // Add active class to clicked button
          this.classList.add('active');
          
          // Show corresponding tab pane
          const tabId = this.getAttribute('data-tab');
          document.getElementById(tabId + '-tab').classList.add('active');
        });
      });
      
      // Logout button functionality
      document.getElementById('logout-btn').addEventListener('click', function() {
        window.location.href = 'logout.php';
      });
    });
  </script>
</body>
</html>

<?php include('footer.php'); ?>
