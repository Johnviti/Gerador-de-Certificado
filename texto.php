<?php
include('header.php');
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Mensagens de feedback
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $textos_editados = [];
        
        // Coletar dados enviados
        foreach ($_POST['textos'] as $index => $texto) {
            $nome_modelo = trim($_POST['nomes_modelos'][$index]);
            
            if (!empty($nome_modelo) && !empty($texto)) {
                $textos_editados[$nome_modelo] = trim($texto);
            }
        }

        foreach ($textos_editados as $nome_modelo => $texto) {
            // Verificar se o modelo já existe pelo nome
            $stmt = $conn->prepare("
                SELECT * FROM textos_certificados 
                WHERE nome_modelo = :nome_modelo
            ");
            $stmt->bindParam(':nome_modelo', $nome_modelo);
            $stmt->execute();
            $existingModel = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingModel) {
                // Atualizar se já existir
                $stmt = $conn->prepare("
                    UPDATE textos_certificados 
                    SET texto_certificado = :texto_certificado
                    WHERE nome_modelo = :nome_modelo
                ");
                $stmt->bindParam(':nome_modelo', $nome_modelo);
                $stmt->bindParam(':texto_certificado', $texto);
                $stmt->execute();
            } else {
                // Inserir novo registro se não existir
                $stmt = $conn->prepare("
                    INSERT INTO textos_certificados (nome_modelo, texto_certificado) 
                    VALUES (:nome_modelo, :texto_certificado)
                ");
                $stmt->bindParam(':nome_modelo', $nome_modelo);
                $stmt->bindParam(':texto_certificado', $texto);
                $stmt->execute();
            }
        }
        $success = "Textos salvos com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao salvar os textos: " . $e->getMessage();
    }
}

// Recupera os textos do banco de dados
try {
    $stmt = $conn->query("SELECT nome_modelo, texto_certificado FROM textos_certificados");
    $textos_predefinidos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $textos_predefinidos = [];
    $error = "Erro ao carregar textos: " . $e->getMessage();
}

// Adiciona uma nova linha vazia ao final do array
$textos_predefinidos[''] = '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar ou Editar Modelos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        h2 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 1rem;
        }
        .table th {
            font-size: 1rem;
            text-align: center;
            background-color: #e9ecef;
            color: #495057;
        }
        .table td {
            vertical-align: middle;
        }
        .table input, .table textarea {
            font-size: 0.9rem;
        }
        .form-control {
            border-radius: 5px;
        }
        .btn-submit {
            margin-top: 20px;
            font-size: 1rem;
            font-weight: bold;
        }
        .container {
            margin-top: 50px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Selecionar ou Editar Modelos</h2>
    
    <!-- Alertas de sucesso ou erro -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Formul�rio Din�mico -->
    <form method="POST">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nome do Modelo</th>
                    <th>Texto do Certificado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($textos_predefinidos)): ?>
                    <?php $index = 0; ?>
                    <?php foreach ($textos_predefinidos as $nome_modelo => $texto): ?>
                        <tr>
                            <td>
                                <input type="text" name="nomes_modelos[<?= $index ?>]" class="form-control"
                                    value="<?= htmlspecialchars($nome_modelo) ?>">
                            </td>
                            <td>
                                <textarea name="textos[<?= $index ?>]" class="form-control"
                                        rows="3"><?= htmlspecialchars($texto) ?></textarea>
                            </td>
                        </tr>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>
                            <input type="text" name="nomes_modelos[0]" class="form-control" placeholder="Novo Modelo">
                        </td>
                        <td>
                            <textarea name="textos[0]" class="form-control" placeholder="Texto do Certificado" rows="3"></textarea>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-submit">Salvar</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
