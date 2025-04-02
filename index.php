<?php
session_start();

require 'db.php';

function renderForm($type, $message = '') {
    if ($type == 'login') {
        echo '
        <h2 class="col-md-12 d-flex justify-content-center h2-cima">Faça Login - Administrador</h2>
        <form action="index.php?action=login" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            
        </form>
        ';
    } else if ($type == 'register') {
        echo '
        <h2 class="col-md-12 d-flex justify-content-center h2-cima">Registrar - Administrador</h2>
        <form action="index.php?action=register" method="post">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar</button>
            <a href="index.php?action=login" class="btn btn-secondary">Já tem uma conta? Faça login</a>
        </form>
        ';
    }
    if ($message != '') {
        echo '<div class="alert alert-danger mt-3 text-center">' . $message . '</div>';
    }
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Verificar se o formulário de login foi enviado
    if (isset($_GET['action']) && $_GET['action'] == 'login') {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Email inválido";
        } else {
            try {
                $stmt = $conn->prepare("SELECT id, senha, nivel FROM usuarios WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                // Remove debug var_dumps
                // var_dump($stmt);
                // var_dump($email);
                // var_dump($senha);

                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($senha, $user['senha'])) {
                        $_SESSION['user_id'] = $user['id']; // Use actual user ID
                        $_SESSION['user_nivel'] = $user['nivel'];
                        
                        if ($_SESSION['user_nivel'] === 1){
                            header("Location: dashboard.php");
                            exit();
                        }

                        header("Location: certificados.php");
                        exit();
                    } else {
                        $message = "Senha incorreta";
                    }
                } else {
                    $message = "Usuário não encontrado";
                }
            } catch(PDOException $e) {
                $message = "Erro na conexão: " . $e->getMessage();
            }
        }
    } else if (isset($_GET['action']) && $_GET['action'] == 'register') {
        $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Email inválido";
        } else {
            $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

            try {
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $hashed_password);

                if ($stmt->execute()) {
                    header("Location: index.php?action=login");
                    exit();
                } else {
                    $message = "Erro ao registrar: " . $stmt->errorInfo()[2];
                }
            } catch(PDOException $e) {
                $message = "Erro na conexão: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificados - Login</title>
  <link rel="stylesheet" href="login.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <!-- Formulário de login -->
    <div class="login-form-container">
      <div class="login-form-wrapper animate-fade-in">
        <div class="login-header">
          <img src="/img/logo-campus-unidas.png" alt="UNIDAS" class="sidebar-logo" width="250">
          <p class="login-subtitle">Sistema de Geração de Certificados</p>
        </div>
        
 
        
        <form id="loginForm" class="login-form" action="index.php?action=login" method="post">
          <div class="form-group">
            <div class="form-label-container">
              <label for="email" class="form-label">Email</label>
            </div>
            <div class="input-container">
              <span class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              </span>
              <input type="email" id="email" name="email" class="form-input" placeholder="Digite seu email" autocomplete="email" required>
            </div>
          </div>
          
          <div class="form-group">
            <div class="form-label-container">
              <label for="password" class="form-label">Senha</label>
              <a href="#" class="forgot-password">Esqueceu a senha?</a>
            </div>
            <div class="input-container">
              <span class="input-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input type="password" id="password" class="form-input" placeholder="Digite sua senha" name="senha" autocomplete="current-password" required>
              <button type="button" id="togglePassword" class="toggle-password" aria-label="Mostrar senha">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>
          
          <div class="remember-me">
            <!-- <input type="checkbox" id="remember" class="checkbox">
            <label for="remember" class="checkbox-label">Lembrar-me</label> -->
          </div>
          
          <?php if (!empty($message)): ?>
            <?php 
              // Determine if the message is an error or success
              $isError = strpos(strtolower($message), 'erro') !== false || 
                         strpos(strtolower($message), 'inválido') !== false || 
                         strpos(strtolower($message), 'incorreta') !== false || 
                         strpos(strtolower($message), 'não encontrado') !== false;
              $alertClass = $isError ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700';
              $iconClass = $isError ? 'text-red-500' : 'text-green-500';
            ?>
            <div class="alert-message <?php echo $alertClass; ?> p-4 rounded-lg mb-4 flex items-center">
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
          
          <button type="submit" id="loginButton" class="login-button">Entrar</button>
        </form>
        
        <div class="footer">
          <p>© <?php echo date('Y'); ?> Unidas - Todos os direitos reservados</p>
        </div>
      </div>
    </div>
    
    <!-- Background animado -->
    <div class="animated-background">
      <!-- <div class="stars" id="stars-container"></div> -->
    </div>
  </div>
  <div id="toast-container" class="toast-container"></div>
  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('svg');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.outerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-off"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>';
      } else {
        passwordInput.type = 'password';
        icon.outerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
      }
    });


    // Create shooting stars
    function createShootingStars() {
      const container = document.getElementById('stars-container');
      
      // Clear existing stars
      const existingStars = container.querySelectorAll('.shooting-star');
      existingStars.forEach(star => star.remove());
      
      // Create new stars
      for (let i = 0; i < 4; i++) {
        const star = document.createElement('div');
        star.className = 'shooting-star';
        
        const delay = Math.random() * 15;
        const duration = 1 + Math.random() * 2;
        const top = Math.random() * 50;
        const left = Math.random() * 20;
        
        star.style.top = `${top}%`;
        star.style.left = `${left}%`;
        star.style.animationDelay = `${delay}s`;
        star.style.animationDuration = `${duration}s`;
        
        container.appendChild(star);
      }
    }
    
    // Create regular stars
    function createStars() {
      const container = document.getElementById('stars-container');
      
      for (let i = 0; i < 100; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        
        const size = Math.random() * 2 + 1;
        const top = Math.random() * 100;
        const left = Math.random() * 100;
        const opacity = Math.random() * 0.7 + 0.3;
        const animationDuration = Math.random() * 3 + 2;
        
        star.style.width = `${size}px`;
        star.style.height = `${size}px`;
        star.style.top = `${top}%`;
        star.style.left = `${left}%`;
        star.style.opacity = opacity;
        star.style.animationDuration = `${animationDuration}s`;
        
        container.appendChild(star);
      }
    }
    
    // Toast notification function
    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.textContent = message;
      
      document.getElementById('toast-container').appendChild(toast);
      
      // Show toast
      setTimeout(() => {
        toast.classList.add('show');
      }, 10);
      
      // Remove toast after 3 seconds
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
          toast.remove();
        }, 300);
      }, 3000);
    }
    
    // Initialize
    createStars();
    createShootingStars();
    
    // Recreate shooting stars every 15 seconds
    setInterval(createShootingStars, 15000);
  </script>
</body>
</html>
