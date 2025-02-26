<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Certificados</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .sidebar {
            height: 100%;
            width: 200px; /* Largura ajustada */
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center; /* Centraliza horizontalmente */
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 14px; /* Tamanho da fonte ajustado */
            color: white;
            display: block;
            text-align: center; /* Centraliza o texto dos links */
        }
        .sidebar a:hover {
            background-color: #575d63;
        }
        .content {
            margin-left: 200px; /* Ajuste para coincidir com a largura da sidebar */
            padding: 20px;
            flex: 1;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 10px 0;
            text-align: center;
            width: 100%;
            position: fixed;
            bottom: 0;
        }
        .navbar {
            margin-left: 200px; /* Ajuste para coincidir com a largura da sidebar */
        }
        .logo-painel {
            width: 135%; /* Ajuste para manter um pouco de margem */
            height: auto;
            background-color: white; /* Mantendo o fundo branco */
            padding: 10px; /* Adicionando um pouco de padding para visualização */
            box-sizing: border-box; /* Inclui padding na largura total */
            margin-bottom: 20px; /* Espaço inferior para separar do menu */
            margin-left: 10px; /* Movendo o logo para a direita */
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a class="navbar-brand d-flex align-items-center justify-content-center" href="./dashboard.php">
            <img class="logo-painel" src="./img/logo.png" alt="logo">
        </a>
        <!-- <a href="index.php">Home</a> --> <!-- Linha removida -->
         <?php if (isset($_SESSION['user_id'])): ?>
            <?php if(isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === 1): ?>
                <a href="dashboard.php">Painel de Controle</a>
            <?php else: ?>
                <a href="dashboard_users.php">Painel de Controle</a>
            <?php endif; ?>
            <a href="certificados.php">Modelos de Certificados</a>
            <a href="texto.php">Texto dos Certificados</a>
            <a href="upload.php">Upload de Nomes</a>
            <a href="gerar_certificado.php">Gerar Certificados</a>
            <a href="enviar.php">Pesquisar</a>
            <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Registrar</a>
        <?php endif; ?>
    </div>
    <div class="container-fluid">
        <div class="content">
