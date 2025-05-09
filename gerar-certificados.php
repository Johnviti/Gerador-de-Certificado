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
$mail->Body = "Olá, $nome!<br><br>
Agradecemos pela sua participação nas COMISSÕES TÉCNICAS UNIDAS. Anexo, você encontrará o seu Certificado de Reconhecimento.<br><br>

Compartilhe em suas redes sociais e marque a UNIDAS.<br><br>

<a href='https://www.linkedin.com/company/unidas-autogestao' target='_blank' style='text-decoration:none; color:#000;'>
  <img src='https://cdn-icons-png.flaticon.com/512/174/174857.png' alt='LinkedIn' style='vertical-align:middle; width:20px; height:20px;'>
  unidas-autogestao
</a><br><br>

<a href='https://www.instagram.com/unidasautogestao' target='_blank' style='text-decoration:none; color:#000;'>
  <img src='https://cdn-icons-png.flaticon.com/512/2111/2111463.png' alt='Instagram' style='vertical-align:middle; width:20px; height:20px;'>
 @unidasautogestao
</a><br><br>

Atenciosamente,<br>
<strong>Equipe UNIDAS</strong><br><br>

<img src='https://www.unidas.org.br/wp-content/uploads/2024/04/pic-logo-unidas-1.webp' alt='Logo UNIDAS' style='width:150px;'><br><br>

<small><strong>Obs:</strong> Não responder a este e-mail. Este e-mail está programado apenas para envio.</small>";


        $mail->AltBody = "Olá, $nome!\n\n
                                                Agradecemos pela sua participação nas COMISSÕES TÉCNICAS UNIDAS. Anexo, você encontrará o seu Certificado de Reconhecimento.<br><br>
.\n\n
                        
                        Atenciosamente,\n
                        Equipe UNIDAS\n\n
                        Obs: Não responder a este e-mail. Este e-mail está programado apenas para envio.";

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

    $pdf = new Fpdi('L', 'mm', 'A4');  
    $pdf->SetAutoPageBreak(FALSE, 0);

    $pageCount = $pdf->setSourceFile($modelo);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($templateId, 0, 0, 297, 210);
    }

    $pdf->SetTextColor(0, 26, 113);

    // **Preservar as quebras de linha do texto original**
    $texto_certificado = nl2br($texto_certificado);

    // **Aplicar negrito apenas nas palavras em CAIXA ALTA**
    $textoFormatado = preg_replace_callback('/\b([A-ZÀ-Ú]+(?:\s+[A-ZÀ-Ú]+)*)\b/u', function ($matches) {
        return "<b>{$matches[1]}</b>"; // Aplica negrito corretamente
    }, $texto_certificado);

    // **Definir posição correta**
    $pdf->SetXY(50, 60);
    $pdf->SetFont('helvetica', '', 17);
    $largura_texto = 220;

    // **Exibir texto com formatação preservada**
    $pdf->writeHTMLCell($largura_texto, 0, 50, 60, $textoFormatado, 0, 1, false, true, 'C');

    // **Data formatada e centralizada**
    $meses = array(
        '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março',
        '04' => 'abril', '05' => 'maio', '06' => 'junho',
        '07' => 'julho', '08' => 'agosto', '09' => 'setembro',
        '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
    );
    $data_atual = 'São Paulo, ' . date('d') . ' de ' . $meses[date('m')] . ' de ' . date('Y');

    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->SetXY(90, 140);
    $pdf->MultiCell(131.0, 8.1, $data_atual, 0, 'C', 0, 1);

    // **Salvar o PDF**
    $output_file = $output_dir . '/certificado_' . str_replace(' ', '_', $nome) . '.pdf';
    $pdf->Output($output_file, 'F');

    return $output_file;
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
                        INSERT INTO certificados_gerados (usuario_id, nome_evento, data_evento, id_nome, data_geracao)
                        VALUES (:usuario_id, :evento, :data_evento, :id_nome, NOW())
                    ");
                    $stmt->bindParam(':usuario_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->bindParam(':evento', $participante['evento'], PDO::PARAM_STR);
                    $stmt->bindParam(':data_evento', $participante['data_inicio'], PDO::PARAM_STR);
                    $stmt->bindParam(':id_nome', $participante_id, PDO::PARAM_STR);
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

// Obter nome do usuário logado
$usuario_logado = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_logado]);
$nome_usuario = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerar Certificados - UNIDAS</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">
  <div class="dashboard-container">
    <div class="sidebar">
      <div class="sidebar-header">
        <img src="/img/logo-unidas.png" alt="UNIDAS" class="sidebar-logo">
      </div>
      <nav class="sidebar-nav">
        <ul class="nav-list">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link" data-page="dashboard">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="upload-nomes.php" class="nav-link" data-page="upload">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
              <span>Upload Nomes</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="modelos-certificados.php" class="nav-link" data-page="models">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
              </svg>
              <span>Modelos Certificados</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="textos-certificados.php" class="nav-link" data-page="texts">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              <span>Textos Certificados</span>
            </a>
          </li>
          <li class="nav-item active">
            <a href="gerar-certificados.php" class="nav-link" data-page="generate">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
              </svg>
              <span>Gerar Certificados</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="pesquisar.php" class="nav-link" data-page="search">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
              <span>Pesquisar</span>
            </a>
          </li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php" class="logout-button" id="logout-btn">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Sair</span>
        </a>
      </div>
    </div>

    <div class="main-content">
      <header class="header shadow-sm bg-white">
        <div class="header-title">
          <h1 id="page-title" class="text-xl font-semibold text-gray-800">Gerenciar Certificados</h1>
        </div>
        <div class="user-menu">
          <span class="user-name"><?php echo htmlspecialchars($nome_usuario); ?></span>
          <div class="user-avatar"><?php echo substr($nome_usuario, 0, 1); ?></div>
        </div>
      </header>

      <main class="content-area">
        <!-- <div class="page-content px-6 py-8">
          <!-- Stats Cards Row -->
            <!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                <div class="stat-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                  <div class="p-5">
                    <div class="flex items-center mb-1">
                      <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                      </div>
                      <span class="text-gray-500 text-sm">Total Certificados</span>
                    </div>
                    <div class="flex items-end justify-between">
                      <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalCertificados; ?></h3>
                      <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 mr-1">
                          <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        18.2%
                      </span>
                    </div>
                  </div>
                </div>
                <div class="stat-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                  <div class="p-5">
                    <div class="flex items-center mb-1">
                      <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                      </div>
                      <span class="text-gray-500 text-sm">Taxa de Download</span>
                    </div>
                    <div class="flex items-end justify-between">
                      <h3 class="text-2xl font-bold text-gray-800"><?php echo $downloadRatePercent; ?>%</h3>
                      <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 mr-1">
                          <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        5.3%
                      </span>
                    </div>
                  </div>
                </div>
                <div class="stat-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                  <div class="p-5">
                    <div class="flex items-center mb-1">
                      <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                          <line x1="3" y1="9" x2="21" y2="9"></line>
                          <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                      </div>
                      <span class="text-gray-500 text-sm">Participantes</span>
                    </div>
                    <div class="flex items-end justify-between">
                      <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalParticipantes; ?></h3>
                      <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3 mr-1">
                          <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        12.5%
                      </span>
                    </div>
                  </div>
                </div>
              </div> -->
          
          <?php if ($error): ?>
            <div class="alert-message bg-red-50 text-red-700 p-4 rounded-lg mb-6">
              Erro: <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>
          
          <form method="POST" id="certificate-form">
            <!-- Filter Section -->
            <div class="filter-section bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100">
              <h2 class="text-lg font-semibold text-gray-800 mb-4">Filtros</h2>
              <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o Modelo:</label>
                  <select name="model_id" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="" selected disabled>Selecione</option>
                    <?php foreach ($modelos as $modelo): ?>
                      <option value="<?php echo $modelo['id']; ?>"><?php echo htmlspecialchars($modelo['nome']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o Texto:</label>
                  <select name="text_id" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="" selected disabled>Selecione</option>
                    <?php foreach ($textos as $texto): ?>
                      <option value="<?php echo $texto['id']; ?>"><?php echo htmlspecialchars($texto['titulo']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            
            <!-- Certificates Table -->
            <div class="certificates-table-container bg-white p-6 rounded-xl shadow-sm border border-gray-100">
              <h2 class="text-lg font-semibold text-gray-800 mb-4">Certificados Disponíveis</h2>
              
              <div class="certificates-table overflow-x-auto">
                <table class="data-table w-full">
                  <thead>
                    <tr class="bg-gray-50">
                      <th class="check-column p-3 text-left">
                        <input type="checkbox" id="select-all" class="checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                      </th>
                      <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                      <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                      <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enviado</th>
                      <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)):
                      $certificado = "SalvarPDF/certificado_" . str_replace(' ', '_', $row['nome']) . ".pdf";
                      $whatsapp_message = urlencode("Olá {$row['nome']}, aqui está o seu certificado: https://certificados.unidasautogestao.com/{$certificado}");
                    ?>
                      <tr class="hover:bg-gray-50">
                        <td class="check-column p-3">
                          <input type="checkbox" name="selected_ids[]" value="<?php echo $row['id']; ?>" class="checkbox cert-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </td>
                        <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="p-3 text-sm">
                          <?php if ($row['enviado']): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sim</span>
                          <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Não</span>
                          <?php endif; ?>
                        </td>
                        <td class="p-3 text-sm actions-column">
                          <?php if (file_exists($certificado)): ?>
                            <a href="<?php echo $certificado; ?>" target="_blank" class="action-button view inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium hover:bg-blue-100 mr-2">
                              <svg class="icon w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                              </svg>
                              Visualizar
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?php echo $whatsapp_message; ?>" target="_blank" class="action-button whatsapp inline-flex items-center px-3 py-1 bg-green-50 text-green-700 rounded-md text-xs font-medium hover:bg-green-100 mr-2">
                              <svg class="icon w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                              </svg>
                              WhatsApp
                            </a>
                          <?php endif; ?>
                          <a href="?delete_id=<?php echo $row['id']; ?>" class="action-button delete inline-flex items-center px-3 py-1 bg-red-50 text-red-700 rounded-md text-xs font-medium hover:bg-red-100" onclick="return confirm('Deseja excluir este certificado?');">
                            <svg class="icon w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                              <line x1="10" y1="11" x2="10" y2="17"></line>
                              <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                            Excluir
                          </a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
              
              <div class="action-buttons mt-6 flex flex-wrap gap-4">
                <button type="submit" name="action" value="enviar" class="primary-button inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <svg class="icon w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                  </svg>
                  Selecionar e Enviar por E-mail
                </button>
                <button type="submit" name="action" value="arquivar" class="secondary-button inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                  <svg class="icon w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="21 8 21 21 3 21 3 8"></polyline>
                    <rect x="1" y="3" width="22" height="5"></rect>
                    <line x1="10" y1="12" x2="14" y2="12"></line>
                  </svg>
                  Arquivar Certificados
                </button>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Select all checkbox functionality
      document.getElementById('select-all').addEventListener('change', function() {
        document.querySelectorAll('.cert-checkbox').forEach(checkbox => {
          checkbox.checked = this.checked;
        });
      });
      
      // Form validation
      document.getElementById('certificate-form').addEventListener('submit', function(e) {
        const modelId = document.querySelector('select[name="model_id"]').value;
        const textId = document.querySelector('select[name="text_id"]').value;
        const selectedIds = document.querySelectorAll('input[name="selected_ids[]"]:checked');
        
        if (!modelId || !textId) {
          e.preventDefault();
          alert('Por favor, selecione um modelo e um texto para continuar.');
          return false;
        }
        
        if (selectedIds.length === 0) {
          e.preventDefault();
          alert('Por favor, selecione pelo menos um participante.');
          return false;
        }
        
        return true;
      });
      
      // Logout button functionality
      document.getElementById('logout-btn').addEventListener('click', function() {
        window.location.href = 'logout.php';
      });
    });
  </script>
</body>
</html>
