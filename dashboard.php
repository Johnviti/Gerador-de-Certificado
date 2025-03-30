<?php
session_start();
include('header.php');

require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['user_nivel'] != 1) {
    header('Location: dashboard_users.php');
    exit;
}

// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Adicionar usuário
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nivel = $_POST['nivel'];

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (:name, :email, :password, :nivel)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':nivel', $nivel);

    if ($stmt->execute()) {
        $message = "Usuário adicionado com sucesso!";
    } else {
        $message = "Erro ao adicionar usuário.";
    }
}

// Excluir usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_usuario'])) {
    $id_usuario = $_POST['excluir_usuario'];
    try {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id_usuario);

        if ($stmt->execute()) {
            $message = "Usuário excluído com sucesso!";
        } else {
            $message = "Erro ao excluir usuário.";
        }
    } catch (PDOException $e) {
        $message = "Erro ao excluir usuário: " . $e->getMessage();
    }
}

// Buscar usuários cadastrados
$stmt = $conn->query("SELECT id, nome, email, nivel FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tabela de certificados gerados
try {
    $query = $conn->query("
       SELECT 
            usuarios.nome AS user_name, 
            nomes.data_inicio AS date, 
            nomes.evento AS event_name,
            COUNT(DISTINCT certificados_gerados.id) AS certificados_gerados
        FROM 
            usuarios
        JOIN 
            certificados_gerados ON certificados_gerados.usuario_id = usuarios.id
        JOIN 
            nomes ON certificados_gerados.nome_evento = nomes.evento
        WHERE
            certificados_gerados.data_evento = nomes.data_inicio
        GROUP BY 
            usuarios.nome, nomes.evento, nomes.data_inicio;
    ");

    $data = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular total de certificados
    $totalCertificados = 0;
    foreach ($data as $row) {
        $totalCertificados += $row['certificados_gerados'];
    }

} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    $data = []; 
    $totalCertificados = 0;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - UNIDAS Certificados</title>
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
          <li class="nav-item active">
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
          <h1 id="page-title">Dashboard</h1>
        </div>
        <div class="user-menu">
          <span class="user-name">Administrador</span>
          <div class="user-avatar">A</div>
        </div>
      </header>

      <main class="content-area">
        <!-- Mensagem de alerta -->
        <?php if (!empty($message)): ?>
          <div class="alert-message">
            <?php echo $message; ?>
          </div>
        <?php endif; ?>

        <div class="page-content" id="dashboard-content">
          <div class="dashboard-cards">
            <div class="stat-card">
              <div class="stat-header">
                <h3>Total de Certificados</h3>
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                  <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                </svg>
              </div>
              <div class="stat-content">
                <p class="stat-value"><?php echo $totalCertificados; ?></p>
                <p class="stat-trend positive">Certificados gerados</p>
              </div>
            </div>
            
            <div class="stat-card">
              <div class="stat-header">
                <h3>Usuários Cadastrados</h3>
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
              </div>
              <div class="stat-content">
                <p class="stat-value"><?php echo count($usuarios); ?></p>
                <p class="stat-trend">Total de usuários</p>
              </div>
            </div>
          </div>

          <!-- Relatório de Certificados -->
          <div class="dashboard-section">
            <div class="section-header">
              <h2>Relatório de Certificados</h2>
            </div>
            <div class="section-content">
              <div class="data-table-container">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Nome do Usuário</th>
                      <th>Data de Emissão</th>
                      <th>Nome do Evento</th>
                      <th>Certificados Gerados</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($data)): ?>
                      <?php foreach ($data as $row): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                          <td>
                            <?php 
                            // Formatar a data para dd/mm/aa
                            $formattedDate = DateTime::createFromFormat('Y-m-d', $row['date'])->format('d/m/Y');
                            echo htmlspecialchars($formattedDate); 
                            ?>
                          </td>
                          <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                          <td><?php echo htmlspecialchars($row['certificados_gerados']); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="4">Nenhum dado encontrado.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Formulário para gerar relatório -->
          <div class="dashboard-section">
            <div class="section-header">
              <h2>Gerar Relatório de Certificados</h2>
            </div>
            <div class="section-content">
              <form method="POST" action="/gerar_excel.php" class="form-container">
                <div class="form-group">
                  <label for="user_id">Selecione o Usuário</label>
                  <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($usuarios as $usuario): ?>
                      <option value="<?php echo htmlspecialchars($usuario['id']); ?>">
                        <?php echo htmlspecialchars($usuario['nome']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <button type="submit" class="btn-primary">Gerar Relatório</button>
              </form>
            </div>
          </div>

          <!-- Formulário Adicionar Usuário -->
          <div class="dashboard-section">
            <div class="section-header">
              <h2>Adicionar Usuário</h2>
            </div>
            <div class="section-content">
              <form method="POST" class="form-container">
                <div class="form-row">
                  <div class="form-group">
                    <label for="name">Nome</label>
                    <input type="text" class="form-input" name="name" required>
                  </div>
                  <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" class="form-input" name="email" required>
                  </div>
                  <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" class="form-input" name="password" required>
                  </div>
                  <div class="form-group">
                    <label for="nivel">Nível</label>
                    <select name="nivel" class="form-select" required>
                      <option value="1">Administrador</option>
                      <option value="2">Usuário</option>
                    </select>
                  </div>
                </div>
                <button type="submit" name="add_user" class="btn-primary">Adicionar Usuário</button>
              </form>
            </div>
          </div>

          <!-- Tabela de Usuários -->
          <div class="dashboard-section">
            <div class="section-header">
              <h2>Usuários Cadastrados</h2>
            </div>
            <div class="section-content">
              <div class="data-table-container">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>E-mail</th>
                      <th>Nível</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo $usuario['nivel'] == 1 ? 'Administrador' : 'Usuário'; ?></td>
                        <td>
                          <form method="POST" style="display:inline;">
                            <input type="hidden" name="excluir_usuario" value="<?php echo $usuario['id']; ?>">
                            <button type="submit" class="btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
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

<?php include('footer.php'); ?>
