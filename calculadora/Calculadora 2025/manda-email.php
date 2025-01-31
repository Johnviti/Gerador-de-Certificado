<?php 

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require("../phpMailer_v2.3/class.phpmailer.php");



$Subject		 = utf8_decode("CCBN - Calculadora Arbitragem 2022");

$id = $_REQUEST['id'];
$email = $_REQUEST['email'];
 

	$body ="";
	 // Inicia a classe PHPMailer
	$mail = new PHPMailer();
	
	// Define os dados do servidor e tipo de conexao
	
	$mail->IsSMTP(); // Define que a mensagem será SMTP
	$mail->Host = "smtp.office365.com"; // Endereço do servidor SMTP
	$mail->SMTPAuth = true; // Autenticaçao
	$mail->Username = 'comunicacao@ccbc.org.br'; // Usuário do servidor SMTP
	$mail->Password = 'ccbc@2016'; // Senha da caixa postal utilizada
	$mail->Port = 587; // Senha da caixa postal utilizada
	$mail->Port       = 587;
	$mail->SMTPSecure = 'tls';

	 // Define o remetente
	
	$mail->From = "comunicacao@ccbc.org.br"; 
	$mail->FromName = "Comunicação";  
	 
	
	$mail->AddAddress( $email );
	
	 
	 // Define os dados técnicos da Mensagem

	$mail->AddAttachment('pdf/Arbitral-'.$id.'.pdf', 'Arbitral-'.$id.'.pdf'); 



	$mail->IsHTML(true); // Define que o e-mail será enviado como HTML
	//$mail->CharSet = 'iso-8859-1'; // Charset da mensagem (opcional)
 
 	// Texto e Assunto

	$mail->Subject  = $Subject; // Assunto da mensagem

	
	$body ="<div style=\"font-family:Tahoma, Geneva, sans-serif;\">
	
Olá, segue em anexo os dados calculados de disputa. 
<br><br>
Para maiores informações, por favor entre em contato.

</div>
	
	";
	
	
	
	$mail->Body = utf8_decode($body);
	
		 // Envio da Mensagem
	$enviado = $mail->Send();
	 
	 // Limpa os destinatários e os anexos
	$mail->ClearAllRecipients();
	$mail->ClearAttachments();
	 
	 // Exibe uma mensagem de resultado
	if ($enviado) {
		
		echo 	$dados['email']." - OK <br />";
		
	
	
	} else {
		echo 	$dados['email']." - Nao - ". $mail->ErrorInfo." <br />";
	}
