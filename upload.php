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
            echo "<div class='alert alert-danger text-center'>Erro: Todos os campos obrigatórios devem ser preenchidos.</div>";
        } else {
            // Verifica se o registro já existe com base no CPF e Evento
            $stmt_check = $conn->prepare("SELECT * FROM nomes WHERE cpf = :cpf AND evento = :evento");
            $stmt_check->bindParam(':cpf', $cpf);
            $stmt_check->bindParam(':evento', $evento);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                echo "<div class='alert alert-warning text-center'>Aviso: Já existe um registro com este CPF e Evento.</div>";
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
                echo "<div class='alert alert-success text-center'>Dados inseridos com sucesso!</div>";
            }
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger text-center'>Erro ao inserir dados: " . $e->getMessage() . "</div>";
    }
}
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

                // Configurar conexão com o banco de dados
                $hostname = "localhost";
                $username = "unidas90_admin";
                $password = "4dm1n@2025";
                $database = "unidas90_certificados";

                $conn = new PDO("mysql:host=$hostname;dbname=$database;charset=utf8", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $user_admin = $_SESSION['user_id'];

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
                        echo "Erro: Campos obrigatórios ausentes na linha ($nome, $cpf).<br>";
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
                        echo "Dados do participante $nome inseridos com sucesso!<br>";
                    } catch (PDOException $e) {
                        echo "Erro ao inserir dados de $nome: " . $e->getMessage() . "<br>";
                    }
                }

                fclose($handle);
            } catch (Exception $e) {
                echo "Erro ao processar o arquivo CSV: " . $e->getMessage();
            }
        } else {
            echo "Erro no upload do arquivo.";
        }
    } else {
        echo "Por favor, envie um arquivo CSV (.csv).";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload e Edição de Nomes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px; /* Texto padrão */
        }

        .page-title {
            margin: 10px 0 20px;
            text-align: center;
            font-size: 15px; /* Tamanho do título */
            font-weight: bold;
        }

        table {
            font-size: 12px; /* Fonte das linhas e células */
            margin-bottom: 20px;
        }

        table input, table select {
            width: 100%;
            box-sizing: border-box;
            font-size: 12px; /* Fonte nos inputs */
        }

        table th {
            font-size: 12px; /* Cabeçalho da tabela */
            font-weight: bold;
        }

        .form-container {
            margin-top: 20px;
        }

        .btn-container {
            margin-top: 15px;
            text-align: center;
            margin-bottom: 60px; /* Espaçamento para o rodapé */
        }

        .spacer {
            margin-bottom: 60px; /* Espaçamento extra no final da página */
        }
    </style>
</head>
<body>
<div class="container form-container">
    <h1 class="page-title">Upload e Edição de Nomes</h1>

    <!-- Upload CSV -->
    <form action="upload.php" method="post" enctype="multipart/form-data" class="mb-5">
        <div class="form-group">
            <label for="arquivo_csv">Selecione o arquivo CSV:</label>
            <input type="file" class="form-control" name="arquivo_csv" id="arquivo_csv" accept=".csv" required>
        </div>
        <div class="btn-container">
            <button type="submit" class="btn btn-primary" name="upload_submit">Upload CSV</button>
        </div>
    </form>

    <!-- Adicionar ou Editar Manualmente -->
    <h2 class="text-center mb-3">Adicionar ou Editar Manualmente</h2>
    <form action="" method="POST">
        <table class="table table-bordered table-hover">
            <tr>
                <th>Nome</th>
                <td><input type="text" name="nome" class="form-control" required></td>
            </tr>
            <tr>
                <th>CPF</th>
                <td><input type="text" name="cpf" class="form-control" required></td>
            </tr>
            <tr>
                <th>Evento</th>
                <td><input type="text" name="evento" class="form-control" required></td>
            </tr>
            <tr>
                <th>Instituição</th>
                <td><input type="text" name="instituicao" class="form-control" required></td>
            </tr>
            <tr>
                <th>Data de Início</th>
                <td><input type="date" name="data_inicio" class="form-control" required></td>
            </tr>
            <tr>
                <th>Data Final</th>
                <td><input type="date" name="data_final" class="form-control" required></td>
            </tr>
            <tr>
                <th>Carga Horária</th>
                <td><input type="number" name="carga_horaria" class="form-control" required></td>
            </tr>
            <tr>
                <th>Telefone</th>
                <td><input type="text" name="telefone" class="form-control" placeholder="(XX) XXXXX-XXXX"></td>
            </tr>
            <tr>
                <th>E-mail</th>
                <td><input type="email" name="email" class="form-control" required></td>
            </tr>
        </table>
        <div class="btn-container">
            <button type="submit" class="btn btn-success" name="manual_submit">Adicionar/Editar</button>
        </div>
    </form>

    <!-- Espaçamento extra no final -->
    <div class="spacer"></div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php include('footer.php'); ?>
