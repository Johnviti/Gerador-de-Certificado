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

    // Conexão com o banco de dados
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
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
