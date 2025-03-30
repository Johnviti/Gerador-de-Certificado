<?php
session_start();
include('header.php');

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

// Configuração da conexão com o banco de dados
//banco desenvolvimento
$servername = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

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

} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    $data = []; 
}





?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        h1, .card-header {
            font-size: 15px; /* Fonte para títulos */
            font-weight: bold;
        }
        table th, table td {
            font-size: 12px; /* Fonte para linhas e textos */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Painel Administrativo - <?php echo $nome_usuario; ?></h1>

    <!-- Mensagem -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Relatório de Certificados -->
    <div class="card mb-4">
        <div class="card-header">Relatório de Certificados</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Nome do Usuário</th>
                        <th>Data do Evento</th>
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

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php include('footer.php'); ?>
