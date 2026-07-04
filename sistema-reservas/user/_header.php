<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' · ' : '' ?><?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="topo-mobile">
    <span>📦 <?= APP_NAME ?></span>
    <a href="../logout.php">Sair</a>
</div>
<div class="app">
    <aside class="barra-lateral">
        <div class="marca">📦 Reserva<span class="ponto">Já</span></div>

        <div class="grupo-titulo">Menu</div>
        <a href="catalogo.php" class="item-menu <?= $paginaAtual === 'catalogo.php' ? 'ativo' : '' ?>">🗂️ Catálogo</a>
        <a href="minhas_reservas.php" class="item-menu <?= $paginaAtual === 'minhas_reservas.php' ? 'ativo' : '' ?>">📅 Minhas reservas</a>

        <div class="rodape-menu">
            <div class="usuario-nome"><?= htmlspecialchars(Auth::usuarioNome()) ?></div>
            <div class="usuario-tipo">Usuário</div>
            <a href="../logout.php">↪ Sair da conta</a>
        </div>
    </aside>
    <main class="conteudo">
