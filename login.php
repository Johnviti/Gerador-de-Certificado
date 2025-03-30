<?php
session_start();
require 'config.php'; // Arquivo com configurações do banco de dados

// Função para exibir erros amigáveis (opcional)
function showError($message) {
    echo "<p style='color:red;'>$message</p>";
}

// Verifica se a requisição é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitização e validação do email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        showError("Usuário ou senha incorretos.");
        exit();
    }

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

    // Consulta preparada
    $stmt = $conn->prepare("SELECT id, senha FROM users WHERE email = ? AND tipo = 'coordenador'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_senha);
        $stmt->fetch();

        // Verifica a senha
        if (password_verify($senha, $hashed_senha)) {
            // Salva o ID do usuário na sessão
            $_SESSION['user_id'] = $id;

            // Redireciona para o dashboard
            header("Location: dashboard.php");
            exit();
        }
    }

    // Caso usuário ou senha estejam incorretos
    showError("Usuário ou senha incorretos.");

    $stmt->close();
    $conn->close();
} else {
    showError("Método de requisição inválido.");
}
?>
