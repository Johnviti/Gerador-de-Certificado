<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('header.php');
include('db.php');
require_once(__DIR__ . '/PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/PHPMailer-master/src/SMTP.php');
require_once(__DIR__ . '/PHPMailer-master/src/Exception.php');
require_once('tcpdf/tcpdf.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função para enviar certificado por e-mail
function enviarCertificado($email, $nome, $arquivo, $id, $conn) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.titan.email';
        $mail->SMTPAuth = true;
        $mail->Username = 'sistemas@unidasautogestao.com';
        $mail->Password = 'MLTR@unidas15';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('sistemas@unidasautogestao.com', 'Sistema de Certificados UNIDAS');
        $mail->addAddress($email, $nome);
        $mail->Subject = 'Seu Certificado';
        $mail->Body    = "Olá, {$nome}. Segue anexo o seu certificado.";
        $mail->addAttachment($arquivo);

        $mail->send();

        $stmt = $conn->prepare("UPDATE nomes SET enviado = 1 WHERE id = ?");
        $stmt->execute([$id]);

        return true;
    } catch (Exception $e) {
        echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
        return false;
    }
}

// Função para arquivar certificados
function arquivarCertificados($ids, $conn) {
    foreach ($ids as $id) {
        $stmt = $conn->prepare("SELECT nome FROM nomes WHERE id = ?");
        $stmt->execute([$id]);
        $nome = $stmt->fetch(PDO::FETCH_ASSOC)['nome'] ?? null;

        if ($nome) {
            $arquivo = "SalvarPDF/certificado_" . str_replace(' ', '_', $nome) . ".pdf";
            $arquivado = "Arquivados/certificado_" . str_replace(' ', '_', $nome) . ".pdf";

            if (file_exists($arquivo)) {
                if (!is_dir('Arquivados')) {
                    mkdir('Arquivados', 0777, true);
                }
                rename($arquivo, $arquivado);
                $conn->prepare("DELETE FROM nomes WHERE id = ?")->execute([$id]);
            }
        }
    }
}

// Processa exclusão de certificados
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("SELECT nome FROM nomes WHERE id = ?");
    $stmt->execute([$id]);
    $nome = $stmt->fetch(PDO::FETCH_ASSOC)['nome'] ?? null;

    if ($nome) {
        $arquivo = "SalvarPDF/certificado_" . str_replace(' ', '_', $nome) . ".pdf";
        if (file_exists($arquivo)) unlink($arquivo);
        $conn->prepare("DELETE FROM nomes WHERE id = ?")->execute([$id]);
        echo "<div class='alert alert-success'>Certificado de {$nome} excluído com sucesso.</div>";
    }
}

// Processa o envio dos certificados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'enviar') {
        if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
            foreach ($_POST['selected_ids'] as $id) {
                $stmt = $conn->prepare("SELECT nome, email FROM nomes WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $nome = $row['nome'];
                    $email = $row['email'];
                    $arquivo = "SalvarPDF/certificado_" . str_replace(' ', '_', $nome) . ".pdf";

                    if (file_exists($arquivo)) {
                        enviarCertificado($email, $nome, $arquivo, $id, $conn);
                    } else {
                        echo "<div class='alert alert-warning'>Certificado de {$nome} não encontrado.</div>";
                    }
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'arquivar') {
        if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
            arquivarCertificados($_POST['selected_ids'], $conn);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// Busca participantes, modelos e textos
$query = "SELECT id, nome, email, enviado FROM nomes";
$result = $conn->query($query);

// Atualiza as queries para buscar os modelos e textos
$modelos = $conn->query("SELECT id, nome_modelo AS nome FROM modelos_certificados")->fetchAll(PDO::FETCH_ASSOC);
$textos = $conn->query("SELECT id, nome_modelo AS titulo FROM textos_certificados")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Certificados</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        h1 {
            font-size: 15px;
        }
        .table, .table td, .table th {
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Gerenciar Certificados</h1>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-6">
                <label style="font-size: 12px;">Selecione o Modelo:</label>
                <select name="model_id" class="form-control" required>
                    <option value="">Selecione</option>
                    <?php foreach ($modelos as $modelo): ?>
                        <option value="<?= $modelo['id'] ?>"><?= htmlspecialchars($modelo['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label style="font-size: 12px;">Selecione o Texto:</label>
                <select name="text_id" class="form-control" required>
                    <option value="">Selecione</option>
                    <?php foreach ($textos as $texto): ?>
                        <option value="<?= $texto['id'] ?>"><?= htmlspecialchars($texto['titulo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Enviado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)):
                    $certificado = "SalvarPDF/certificado_" . str_replace(' ', '_', $row['nome']) . ".pdf";
                    $whatsapp_message = urlencode("Olá {$row['nome']}, aqui está o seu certificado: https://seusite.com/{$certificado}");
                ?>
                    <tr>
                        <td><input type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>"></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= $row['enviado'] ? 'Sim' : 'Não' ?></td>
                        <td>
                            <?php if (file_exists($certificado)): ?>
                                <a href="<?= $certificado ?>" target="_blank" class="btn btn-info btn-sm">Visualizar</a>
                                <a href="https://api.whatsapp.com/send?text=<?= $whatsapp_message ?>" 
                                   target="_blank" class="btn btn-success btn-sm">WhatsApp</a>
                            <?php endif; ?>
                            <a href="?delete_id=<?= $row['id'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Deseja excluir este certificado?');">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="text-center">
            <button type="submit" name="action" value="enviar" class="btn btn-primary">Selecionar e Enviar por E-mail</button>
            <button type="submit" name="action" value="arquivar" class="btn btn-secondary">Arquivar Certificados</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('input[name="selected_ids[]"]').forEach(checkbox => checkbox.checked = this.checked);
    });
</script>
</body>
</html>
