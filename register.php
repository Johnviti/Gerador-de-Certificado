<?php
session_start();
require 'config.php'; // arquivo contendo a configuração do banco de dados

function renderForm($type) {
    if ($type == 'login') {
        echo '
        <h2 class="text-center mb-4">Faça Login - Administrador</h2>
        <form action="index.php?action=login" method="post">
            <div class="form-group mb-3">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group mb-3">
                <label for="senha">Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="index.php?action=register" class="btn btn-success">Registre-se</a>
        </form>
        ';
    } else if ($type == 'register') {
        echo '
        <h2 class="text-center mb-4">Registrar - Administrador</h2>
        <form action="index.php?action=register" method="post">
            <div class="form-group mb-3">
                <label for="name">Nome:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group mb-3">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group mb-3">
                <label for="password">Senha:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar</button>
            <a href="index.php?action=login" class="btn btn-secondary">Já tem uma conta? Faça login</a>
        </form>
        ';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_GET['action'] == 'login') {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Email inválido");
        }

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("Erro na conexão: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("SELECT id, senha FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_senha);
            $stmt->fetch();

            if (password_verify($senha, $hashed_senha)) {
                $_SESSION['user_id'] = $id;
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Senha incorreta";
            }
        } else {
            echo "Usuário não encontrado";
        }

        $stmt->close();
        $conn->close();
    } else if ($_GET['action'] == 'register') {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Email inválido");
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("Erro na conexão: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO users (name, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "Registrado com sucesso!";
            header("Location: index.php?action=login");
            exit();
        } else {
            echo "Erro ao registrar: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Adriano Pina">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gerador de Certificados</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" integrity="sha384-WE4QcN87HOeF2KsH4k/pv/V7X7/kSLbKCS7HSsLFmpQZExr0X6Cp0zD6z2Sc3D" crossorigin="anonymous">
        <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="CSS/styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="text-center mb-4">
                        <a href="./index.php"><img src="img/logo.png" width="100px" alt="Logo"></a>
                    </div>
                    <?php
                    if (isset($_GET['action']) && $_GET['action'] == 'register') {
                        renderForm('register');
                    } else {
                        renderForm('login');
                    }
                    ?>
                </div>
            </div>
        </div>

        <footer class="footer text-center mt-5"> 
            <h6>&copy; 2023 | <?php echo date("Y")?> - <a href="./index.php">Gerador de Certificados</a> - Todos os direitos reservados.</h6>
        </footer>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLR/f3k5F1vzt2xCDzLCqFw5RYUIbq4ylW2yTZKQYZ" crossorigin="anonymous"></script>
    </body>
    </html>
    <?php
}
?>
