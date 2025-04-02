<?php
// session_start();

include('header.php');

require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['user_nivel'] != 2) {
    header('Location: dashboard.php');
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

$usuario_logado = $_SESSION['user_id'];

$nome_usuario = $conn->query("SELECT nome FROM usuarios WHERE id = $usuario_logado");
$nome_usuario = $nome_usuario->fetchColumn();

// Tabela de certificados gerados
try {
    $query = $conn->prepare("
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
            AND usuarios.id = :usuario_id
        GROUP BY 
            usuarios.nome, nomes.evento, nomes.data_inicio;
    ");

    $query->bindParam(':usuario_id', $usuario_logado, PDO::PARAM_INT);
    $query->execute();
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

$totalParticipantes = $conn->query("SELECT COUNT(DISTINCT nome) FROM nomes")->fetchColumn();

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
  <title>Dashboard - UNIDAS Certificados</title>
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
      <header class="header shadow-sm bg-white">
        <div class="header-title">
          <h1 id="page-title" class="text-xl font-semibold text-gray-800">Dashboard</h1>
        </div>
        <div class="user-menu">
          <span class="user-name text-gray-700"><?php echo htmlspecialchars($nome_usuario); ?></span>
          <div class="user-avatar shadow-md bg-blue-600"><?php echo substr($nome_usuario, 0, 1); ?></div>
        </div>
      </header>

      <main class="content-area">
        <?php if (!empty($message)): ?>
          <?php 
            // Determine if the message is an error or success
            $isError = strpos(strtolower($message), 'erro') !== false;
            $alertClass = $isError ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700';
            $iconClass = $isError ? 'text-red-500' : 'text-green-500';
          ?>
          <div class="alert-message <?php echo $alertClass; ?> p-4 rounded-lg mb-6 flex items-center">
            <svg class="w-5 h-5 mr-3 <?php echo $iconClass; ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <?php if ($isError): ?>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              <?php else: ?>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              <?php endif; ?>
            </svg>
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <div class="page-content px-6 py-8">
          <!-- Stats Cards Row -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="stat-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
              <div class="p-5">
                <div class="flex items-center mb-1">
                  <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2 2v6a2 2 0 0 1 2 2h2"></path>
                      <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                  </div>
                  <span class="text-gray-500 text-sm">Total de Certificados</span>
                </div>
                <div class="flex items-end justify-between">
                  <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalCertificados; ?></h3>
                  <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 mr-1">
                      <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    18.2%
                  </span>
                </div>
              </div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
              <div class="p-5">
                <div class="flex items-center mb-1">
                  <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                      <circle cx="9" cy="7" r="4"></circle>
                      <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                      <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                  </div>
                  <span class="text-gray-500 text-sm">Usuários Cadastrados</span>
                </div>
                <div class="flex items-end justify-between">
                  <h3 class="text-2xl font-bold text-gray-800"><?php echo count($usuarios); ?></h3>
                  <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 mr-1">
                      <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    5.3%
                  </span>
                </div>
              </div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
              <div class="p-5">
                <div class="flex items-center mb-1">
                  <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                      <line x1="3" y1="9" x2="21" y2="9"></line>
                      <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                  </div>
                  <span class="text-gray-500 text-sm">Participantes</span>
                </div>
                <div class="flex items-end justify-between">
                  <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalParticipantes; ?></h3>
                  <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 mr-1">
                      <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    12.5%
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Relatório de Certificados -->
          <div class="certificates-table-container bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Relatório de Certificados</h2>
            
            <div class="certificates-table overflow-x-auto">
              <table class="data-table w-full">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Usuário</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Emissão</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Evento</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificados Gerados</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <?php if (!empty($data)): ?>
                    <?php foreach ($data as $row): ?>
                      <tr class="hover:bg-gray-50">
                        <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td class="p-3 text-sm text-gray-700">
                          <?php 
                          // Formatar a data para dd/mm/aa
                          $formattedDate = DateTime::createFromFormat('Y-m-d', $row['date'])->format('d/m/Y');
                          echo htmlspecialchars($formattedDate); 
                          ?>
                        </td>
                        <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($row['event_name']); ?></td>
                        <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($row['certificados_gerados']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="4" class="p-3 text-sm text-gray-500 text-center">Nenhum dado encontrado.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Formulário para gerar relatório -->
          <div class="filter-section bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Gerar Relatório de Certificados</h2>
            <form method="POST" action="/gerar_excel.php" class="form-grid grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-group">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Selecione o Usuário</label>
                <select name="user_id" id="user_id" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                  <option value="">Selecione...</option>
                  <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo htmlspecialchars($usuario['id']); ?>">
                      <?php echo htmlspecialchars($usuario['nome']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group flex items-end">
                <button type="submit" class="primary-button inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <svg class="icon w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                  </svg>
                  Gerar Relatório
                </button>
              </div>
            </form>
          </div>

          <!-- Formulário Adicionar Usuário -->
          <div class="filter-section bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Adicionar Usuário</h2>
            <form method="POST" class="form-grid grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-group">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                <input type="text" class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" name="name" required>
              </div>
              <div class="form-group">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                <input type="email" class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" name="email" required>
              </div>
              <div class="form-group">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                <input type="password" class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" name="password" required>
              </div>
              <div class="form-group">
                <label for="nivel" class="block text-sm font-medium text-gray-700 mb-2">Nível</label>
                <select name="nivel" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                  <option value="1">Administrador</option>
                  <option value="2">Usuário</option>
                </select>
              </div>
              <div class="col-span-2">
                <button type="submit" name="add_user" class="primary-button inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <svg class="icon w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                  </svg>
                  Adicionar Usuário
                </button>
              </div>
            </form>
          </div>

          <!-- Tabela de Usuários -->
          <div class="certificates-table-container bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Usuários Cadastrados</h2>
            
            <div class="certificates-table overflow-x-auto">
              <table class="data-table w-full">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <?php foreach ($usuarios as $usuario): ?>
                    <tr class="hover:bg-gray-50">
                      <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($usuario['id']); ?></td>
                      <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($usuario['nome']); ?></td>
                      <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($usuario['email']); ?></td>
                      <td class="p-3 text-sm">
                        <?php if ($usuario['nivel'] == 1): ?>
                          <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Administrador</span>
                        <?php else: ?>
                          <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Usuário</span>
                        <?php endif; ?>
                      </td>
                      <td class="p-3 text-sm actions-column">
                        <form method="POST" style="display:inline;">
                          <input type="hidden" name="excluir_usuario" value="<?php echo $usuario['id']; ?>">
                          <button type="submit" class="action-button delete inline-flex items-center px-3 py-1 bg-red-50 text-red-700 rounded-md text-xs font-medium hover:bg-red-100" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                            <svg class="icon w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                              <line x1="10" y1="11" x2="10" y2="17"></line>
                              <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                            Excluir
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
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
      
      // Add any additional JavaScript functionality here
      // For example, you could add form validation, dynamic content loading, etc.
      
      // Example: Add a confirmation dialog for form submissions
      const forms = document.querySelectorAll('form');
      forms.forEach(form => {
        if (form.querySelector('button[name="add_user"]')) {
          form.addEventListener('submit', function(e) {
            const emailInput = this.querySelector('input[name="email"]');
            const passwordInput = this.querySelector('input[name="password"]');
            
            if (passwordInput.value.length < 6) {
              e.preventDefault();
              alert('A senha deve ter pelo menos 6 caracteres.');
              return false;
            }
            
            if (!emailInput.value.includes('@')) {
              e.preventDefault();
              alert('Por favor, insira um email válido.');
              return false;
            }
            
            return true;
          });
        }
      });
    });
  </script>
</body>
</html>

 