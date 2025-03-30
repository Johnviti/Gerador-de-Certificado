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

                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($senha, $user['senha'])) {
                        $_SESSION['user_id'] = $user['id'];
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
    <meta charset="utf-8">
    <meta name="author" content="Adriano Pina">
    <title>Login - Gerador de Certificados</title>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="col-md-12 d-flex justify-content-center bordas">
                    <a href="./index.php"><img src="img/logo.png" width="100px"></a>
                </div>
                <?php
                if (isset($_GET['action']) && $_GET['action'] == 'register') {
                    renderForm('register', $message);
                } else {
                    renderForm('login', $message);
                }
                ?>
            </div>
        </div>
    </div>
</body>
<footer class="footer text-center mt-5"> 
    <h6>&copy; 2023 | <?php echo date("Y")?> - <a href="./index.php">Gerador de Certificados</a> - Todos os direitos reservados.</h6>
</footer>
</html>
