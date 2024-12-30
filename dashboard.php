<?php
session_start();
include('header.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuração da conexão com o banco de dados
$servername = "localhost";
$username = "unidas90_Leandro";
$password = "Le@ndro2101";
$dbname = "unidas90_certificados";

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

// Dados simulados do relatório
// $data = [
//     ['user_name' => 'João Silva', 'date' => '2024-12-10', 'event_name' => 'Workshop PHP', 'certificates' => 15],
//     ['user_name' => 'Maria Oliveira', 'date' => '2024-12-12', 'event_name' => 'Curso Laravel', 'certificates' => 20],
//     ['user_name' => 'Pedro Santos', 'date' => '2024-12-15', 'event_name' => 'Palestra Segurança', 'certificates' => 10],
// ];

try {
    // Consulta SQL
    $query = $conn->query("SELECT nome AS user_name, data_inicio AS date, evento AS event_name, certificado_gerado AS certificates FROM nomes");

    // Prepara e executa a consulta
    $data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    $data = []; // Garante que $data está definido, mesmo em caso de erro
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
    <h1 class="text-center mb-4">Painel Administrativo</h1>

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
                        <th>Data</th>
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
                            <td><?php echo htmlspecialchars($row['certificates']); ?></td>
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

    <!-- Formulário Adicionar Usuário -->
    <div class="card mb-4">
        <div class="card-header">Adicionar Usuário</div>
        <div class="card-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="name">Nome</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="password">Senha</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="nivel">Nível</label>
                        <select name="nivel" class="form-control" required>
                            <option value="1">Administrador</option>
                            <option value="2">Usuário</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary">Adicionar Usuário</button>
            </form>
        </div>
    </div>

    <!-- Tabela de Usuários -->
    <div class="card">
        <div class="card-header">Usuários Cadastrados</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
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
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
