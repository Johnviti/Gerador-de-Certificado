<?php
$image = htmlspecialchars($_GET['image']);
$imageUrl = __DIR__ . '/' . $image;

if (!file_exists($imageUrl)) {
    die('Imagem não encontrada.');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baixar e Compartilhar Imagem</title>
    <style>
        #actions {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        #actions button {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <h1>Imagem Gerada</h1>
    <img src="<?php echo $image; ?>" alt="Imagem Gerada" style="max-width: 100%; height: auto;">

    <div id="actions">
        <!-- Botão de baixar a imagem -->
        <a href="<?php echo $image; ?>" download="imagem.png">
            <button>Baixar Imagem</button>
        </a>

        <!-- Botão de compartilhar no LinkedIn -->
        <button id="shareLinkedIn">Compartilhar no LinkedIn</button>
    </div>

    <script>
        document.getElementById('shareLinkedIn').addEventListener('click', function() {
            const url = 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(window.location.href) + '&title=Minha%20Imagem&summary=Confira%20esta%20imagem&source=SeuSite';
            window.open(url, '_blank');
        });
    </script>
</body>
</html>
