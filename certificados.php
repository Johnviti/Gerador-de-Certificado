<?php
include('header.php');
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = null;
$success = null;

// Salvar o upload no banco de dados e diretório
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'], $_POST['nome_evento'])) {
    $nomeEvento = trim($_POST['nome_evento']);
    $nomeModelo = $nomeEvento; // Pode ser o nome do evento ou um valor padrão
    $textoCertificado = ''; // Texto padrão inicial
    $diretorio = 'certificados/';
    $arquivo = $_FILES['arquivo'];
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    // Validação apenas para arquivos PDF
    if ($extensao !== 'pdf') {
        $error = "Erro: Apenas arquivos PDF são permitidos.";
    } else {
        $novoNome = uniqid() . '.' . $extensao;
        if (move_uploaded_file($arquivo['tmp_name'], $diretorio . $novoNome)) {
            try {
                $stmt = $conn->prepare("INSERT INTO modelos_certificados (nome_evento, nome_modelo, texto_certificado, arquivo_nome) 
                                        VALUES (:nome_evento, :nome_modelo, :texto_certificado, :arquivo_nome)");
                $stmt->bindParam(':nome_evento', $nomeEvento);
                $stmt->bindParam(':nome_modelo', $nomeModelo);
                $stmt->bindParam(':texto_certificado', $textoCertificado);
                $stmt->bindParam(':arquivo_nome', $novoNome);
                $stmt->execute();
                $success = "Upload realizado e salvo com sucesso!";
            } catch (PDOException $e) {
                $error = "Erro ao salvar no banco: " . $e->getMessage();
            }
        } else {
            $error = "Erro ao fazer upload do arquivo.";
        }
    }
}

// Excluir modelo
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        // Buscar o arquivo associado
        $stmt = $conn->prepare("SELECT arquivo_nome FROM modelos_certificados WHERE id = :id");
        $stmt->bindParam(':id', $deleteId);
        $stmt->execute();
        $modelo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($modelo) {
            $caminhoArquivo = 'certificados/' . $modelo['arquivo_nome'];
            if (file_exists($caminhoArquivo)) {
                unlink($caminhoArquivo); // Deletar arquivo
            }

            // Deletar do banco de dados
            $deleteStmt = $conn->prepare("DELETE FROM modelos_certificados WHERE id = :id");
            $deleteStmt->bindParam(':id', $deleteId);
            $deleteStmt->execute();
            $success = "Modelo excluído com sucesso!";
        } else {
            $error = "Erro: Modelo não encontrado.";
        }
    } catch (PDOException $e) {
        $error = "Erro ao excluir o modelo: " . $e->getMessage();
    }
}

// Função para listar os modelos
function listarModelos($conn) {
    $stmt = $conn->query("SELECT * FROM modelos_certificados");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$modelos = listarModelos($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modelos de Certificados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h1 class="text-center mt-4">Modelos de Certificados</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Formulário de Upload -->
    <div class="upload-section">
        <h2 class="text-center">Upload de Certificado em Branco</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome_evento" class="form-label">Nome do Evento:</label>
                <input type="text" class="form-control" name="nome_evento" id="nome_evento" placeholder="Digite o nome do evento" required>
            </div>
            <div class="mb-3">
                <label for="arquivo" class="form-label">Escolha um arquivo PDF:</label>
                <input type="file" class="form-control" id="arquivo" name="arquivo" accept=".pdf" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Fazer Upload</button>
        </form>
    </div>

    <!-- Exibir Modelos -->
    <h2 class="text-center mt-5 mb-4">Modelos Salvos</h2>
    <div class="row row-cols-1 row-cols-md-5 g-4">
        <?php foreach ($modelos as $modelo): ?>
            <div class="col">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="thumbnails/<?= htmlspecialchars($modelo['thumb_nome']); ?>" class="card-img-top" alt="Miniatura do PDF">
                        <h5 class="card-title"><?= htmlspecialchars($modelo['nome_evento']); ?></h5>
                        <a href="certificados/<?= htmlspecialchars($modelo['arquivo_nome']); ?>" class="btn btn-info btn-sm" target="_blank">Visualizar</a>
                        <a href="?delete_id=<?= $modelo['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja excluir este modelo?');">Excluir</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include('footer.php'); ?>
