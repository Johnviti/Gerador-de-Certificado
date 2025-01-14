<?php
include('header.php');
include('db.php');
require_once(__DIR__ . '/PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/PHPMailer-master/src/SMTP.php');
require_once(__DIR__ . '/PHPMailer-master/src/Exception.php');
require_once('vendor/autoload.php'); 
require_once('tcpdf/tcpdf.php');
require_once('tcpdf/tcpdf_import.php');

use setasign\Fpdi\Tcpdf\Fpdi;


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

        // Configurações de codificação
        $mail->CharSet = 'UTF-8'; // Para suportar acentuação e caracteres especiais
        $mail->Encoding = 'base64'; // Codificação para e-mail

        $mail->isHTML(true);
        $mail->Subject = 'Certificado de Participação – Evento UNIDAS';
        $mail->Body    = "Olá, $nome!<br><br>
                        Agradecemos pela sua presença em nosso evento. Em anexo, você encontrará o seu certificado de participação.<br><br>
                        Caso tenha dúvidas ou sugestões, fique à vontade para entrar em contato conosco. Será um prazer atender você.<br><br>
                        📧 E-mail: institucional@unidas.org.br<br>
                        📞 Telefone: (11) 3289-0855<br>
                        📍 Endereço: Alameda Santos, 1000 – 8º Andar – São Paulo, SP<br><br>
                        Agradecemos mais uma vez e esperamos vê-lo(a) em futuros eventos da UNIDAS!<br><br>
                        Atenciosamente,<br>
                        <strong>Equipe UNIDAS</strong><br><br>
                        <img src='https://unidas.digital/wp-content/uploads/2024/08/logo-1.png' alt='Logo UNIDAS' style='width:150px;'><br><br>
                        <small><strong>Obs:</strong> Não responder a este e-mail. Este e-mail está programado apenas para envio. 
                        Se tiver dúvidas, entre em contato pelo e-mail institucional@unidas.org.br ou telefone (11) 3289-0855.</small>";
        $mail->AltBody = "Olá, $nome!\n\n
                        Agradecemos pela sua presença em nosso evento. Em anexo, você encontrará o seu certificado de participação.\n\n
                        Caso tenha dúvidas ou sugestões, fique à vontade para entrar em contato conosco. Será um prazer atender você.\n\n
                        E-mail: institucional@unidas.org.br\n
                        Telefone: (11) 3289-0855\n
                        Endereço: Alameda Santos, 1000 – 8º Andar – São Paulo, SP\n\n
                        Agradecemos mais uma vez e esperamos vê-lo(a) em futuros eventos da UNIDAS!\n\n
                        Atenciosamente,\n
                        Equipe UNIDAS\n\n
                        Obs: Não responder a este e-mail. Este e-mail está programado apenas para envio. Se tiver dúvidas, entre em contato pelo e-mail institucional@unidas.org.br ou telefone (11) 3289-0855.";

        // Anexar o certificado
        if (file_exists($arquivo)) {
            $mail->addAttachment($arquivo);
        } else {
            throw new Exception('Erro: O arquivo de certificado não foi encontrado.');
        }

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
                $conn->prepare("UPDATE nomes SET arquivado = TRUE WHERE id = ?")->execute([$id]);
            }
        }
    }
}

// Função para gerar certificado em PDF
function gerarCertificadoPDF($nome, $modelo, $texto_certificado) {
    $output_dir = __DIR__ . '/SalvarPDF';
    
    if (!is_dir($output_dir)) {
        if (!mkdir($output_dir, 0777, true)) {
            throw new Exception('Falha ao criar diretório: ' . $output_dir);
        }
    }

    if (!is_writable($output_dir)) {
        if (!chmod($output_dir, 0777)) {
            throw new Exception('Sem permissão de escrita para o diretório: ' . $output_dir);
        }
    }

    // Criar o PDF
    $pdf = new Fpdi('L', 'mm', 'A4');  // Use Fpdi aqui ao invés de TCPDF diretamente
    $pdf->SetAutoPageBreak(FALSE, 0);

    // Importar o modelo de fundo
    $pageCount = $pdf->setSourceFile($modelo);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($templateId, 0, 0, 297, 210);
    }


    // Adicionar textos e informações
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->SetTextColor(0, 0, 0);

    // $pdf->SetXY(16.5, 75);
    // $pdf->MultiCell(156.9, 14.3, $nome, 0, 'C', 0, 1);

    $pdf->SetFont('helvetica', '', 14);
    $pdf->SetXY(16.5, 89.6);
    $pdf->MultiCell(156.9, 47.4, $texto_certificado, 0, 'C', 0, 1);

    $meses = array(
        '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março',
        '04' => 'abril', '05' => 'maio', '06' => 'junho',
        '07' => 'julho', '08' => 'agosto', '09' => 'setembro',
        '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
    );
    $data_atual = 'São Paulo, ' . date('d') . ' de ' . $meses[date('m')] . ' de ' . date('Y');
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->SetXY(21.0, 157.3);
    $pdf->MultiCell(131.0, 8.1, $data_atual, 0, 'C', 0, 1);

    // Salvar o PDF
    $output_file = $output_dir . '/certificado_' . str_replace(' ', '_', $nome) . '.pdf';
    $pdf->Output($output_file, 'F');

    return $output_file;
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

$error = null;
$output = null;
$output_url = null;

// Processa o envio dos certificados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'enviar') {
        if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
            foreach ($_POST['selected_ids'] as $id) {
                try {
                    if (isset($_POST['model_id'])) {
                        $modelo_id = $_POST['model_id'];
                    } else {
                        throw new Exception('Modelo de certificado não foi selecionado.');
                    }


                    $participante_id =  $id;
                    $acao = $_POST['action'];

                    // Obter o modelo de certificado
                    $texto_id = $_POST['text_id'];

                    $stmt = $conn->prepare("
                        SELECT 
                            modelos_certificados.arquivo_nome, 
                            textos_certificados.texto_certificado
                        FROM 
                            modelos_certificados
                        JOIN 
                            textos_certificados
                        ON 
                            textos_certificados.id = :text_id
                        WHERE 
                            modelos_certificados.id = :model_id
                    ");
                    $stmt->bindParam(':model_id', $modelo_id, PDO::PARAM_INT);
                    $stmt->bindParam(':text_id', $texto_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $certificado = $stmt->fetch(PDO::FETCH_ASSOC);


                    if (!$certificado) {
                        throw new Exception('Modelo de certificado não encontrado.');
                    }

                    $modelo = __DIR__ . '/certificados/' . $certificado['arquivo_nome'];
                    $texto_certificado = $certificado['texto_certificado'];

                    if (!file_exists($modelo)) {
                        throw new Exception('Arquivo de modelo não encontrado: ' . $modelo);
                    }

                    // Obter o participante
                    $stmt = $conn->prepare("SELECT nome, email, certificado_gerado FROM nomes WHERE id = ?");
                    $stmt->execute([$id]);
                    $participante = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$participante) {
                        throw new Exception('Participante não encontrado.');
                    }

                    $nome = $participante['nome'];
                    $email = $participante['email'];


                    $stmt = $conn->prepare("
                        SELECT nome, cpf, evento, instituicao, data_inicio, data_final, carga_horaria 
                        FROM nomes 
                        WHERE id = ?
                    ");
                    $stmt->execute([$id]);
                    $participante = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$participante) {
                        throw new Exception('Participante não encontrado.');
                    }

                    $dataInicio = date('d/m/Y', strtotime($participante['data_inicio']));
                    $dataFinal = date('d/m/Y', strtotime($participante['data_final']));
                    $cargaHoraria = $participante['carga_horaria'];
                    $cidade = 'Cidade Exemplo'; // Ajuste conforme necessário
                    $dataEmissao = date('d/m/Y');

                    // Substituir as variáveis no texto do certificado
                    $texto_certificado = str_replace(
                        [
                            '[Nome do Participante]',
                            '[Nome do Evento]',
                            '[Data do Evento]',
                            '[Local do Evento]',
                            '[Duração do Evento]',
                        ],
                        [
                            $nome,
                            $participante['evento'],
                            "$dataInicio a $dataFinal",
                            $participante['instituicao'],
                            $cargaHoraria,
                        ],
                        $texto_certificado
                    );

                    // Gerar o certificado em PDF
                    $output = gerarCertificadoPDF($nome, $modelo, $texto_certificado);

                    
                    $stmt = $conn->prepare("UPDATE nomes SET certificado_gerado = certificado_gerado + 1 WHERE id = :id");
                    $stmt->bindParam(':id', $participante_id, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Inserir um registro na tabela de certificados gerados
                    $stmt = $conn->prepare("
                        INSERT INTO certificados_gerados (usuario_id, nome_evento, data_evento, data_geracao)
                        VALUES (:usuario_id, :evento, :data_evento, NOW())
                    ");
                    $stmt->bindParam(':usuario_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->bindParam(':evento', $participante['evento'], PDO::PARAM_STR);
                    $stmt->bindParam(':data_evento', $participante['data_inicio'], PDO::PARAM_STR);
                    $stmt->execute();
                                   

                    // Gerar o URL do certificado
                    $output_url = str_replace(__DIR__, 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']), $output);

                    // Enviar e-mail com o certificado se a ação for "enviar"
                    if (file_exists($output)) {
                        enviarCertificado($email, $nome, $output, $id, $conn);
                    } else {
                        echo "<div class='alert alert-warning'>Certificado de {$nome} não encontrado.</div>";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'arquivar') {
        if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
            arquivarCertificados($_POST['selected_ids'], $conn);
            echo '<script type="text/javascript">window.location.href = "/gerar_certificado.php";</script>';
            
        }
    }
}

if ($_SESSION['user_nivel'] === 2) {
    $query = "SELECT id, nome, email, enviado FROM nomes WHERE arquivado = FALSE AND admin_id = {$_SESSION['user_id']}";
    $result = $conn->query($query);
}else{
    $query = "SELECT id, nome, email, enviado FROM nomes WHERE arquivado = FALSE";
    $result = $conn->query($query);
}
// Busca participantes, modelos e textos


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

    <?php if ($error): ?>
        <div class="error">Erro: <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-6">
                <label style="font-size: 12px;">Selecione o Modelo:</label>
                <select name="model_id" class="form-control">
                    <option value="">Selecione</option>
                    <?php foreach ($modelos as $modelo): ?>
                        <option value="<?= $modelo['id'] ?>"><?= htmlspecialchars($modelo['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label style="font-size: 12px;">Selecione o Texto:</label>
                <select name="text_id" class="form-control">
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
                    $whatsapp_message = urlencode("Olá {$row['nome']}, aqui está o seu certificado: https://certificados.unidasautogestao.com/{$certificado}");
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
