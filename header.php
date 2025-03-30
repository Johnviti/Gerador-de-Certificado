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
            <a href="modelos-certificados.php">Modelos de Certificados</a>
            <a href="textos-certificados.php">Texto dos Certificados</a>
            <a href="upload-nomes.php">Upload de Nomes</a>
            <a href="gerar-certificados.php">Gerar Certificados</a>
            <a href="enviar.php">Pesquisar</a>
            <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Registrar</a>
        <?php endif; ?>
    </div>
    <div class="container-fluid">
        <div class="content">
