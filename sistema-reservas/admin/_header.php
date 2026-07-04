<?php
// Este arquivo assume que config.php, Auth.php já foram carregados
// e que Auth::exigirAdmin() já foi chamado pela página que o inclui.
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' · ' : '' ?><?= APP_NAME ?> Admin</title>
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

        <div class="grupo-titulo">Painel</div>
        <a href="dashboard.php" class="item-menu <?= $paginaAtual === 'dashboard.php' ? 'ativo' : '' ?>">🏠 Início</a>

        <div class="grupo-titulo">Cadastros</div>
        <a href="categorias.php" class="item-menu <?= $paginaAtual === 'categorias.php' ? 'ativo' : '' ?>">🏷️ Categorias</a>
        <a href="setores.php" class="item-menu <?= $paginaAtual === 'setores.php' ? 'ativo' : '' ?>">🧩 Setores</a>
        <a href="recursos.php" class="item-menu <?= $paginaAtual === 'recursos.php' ? 'ativo' : '' ?>">📷 Recursos</a>

        <div class="grupo-titulo">Movimentação</div>
        <a href="reservas.php" class="item-menu <?= $paginaAtual === 'reservas.php' ? 'ativo' : '' ?>">📅 Reservas</a>

        <div class="rodape-menu">
            <div class="usuario-nome"><?= htmlspecialchars(Auth::usuarioNome()) ?></div>
            <div class="usuario-tipo">Administrador</div>
            <a href="../logout.php">↪ Sair da conta</a>
        </div>
    </aside>
    <main class="conteudo">
