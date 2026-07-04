<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Usuario.php';

// Se já estiver logado, redireciona direto para a área correta
if (Auth::estaLogado()) {
    header('Location: ' . (Auth::ehAdmin() ? 'admin/dashboard.php' : 'user/catalogo.php'));
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha para continuar.';
    } else {
        $usuarioModel = new Usuario();
        $usuarioAutenticado = $usuarioModel->autenticar($email, $senha);

        if ($usuarioAutenticado) {
            Auth::login(
                $usuarioAutenticado->getId(),
                $usuarioAutenticado->getNome(),
                $usuarioAutenticado->getEmail(),
                $usuarioAutenticado->getTipo()
            );
            header('Location: ' . ($usuarioAutenticado->getTipo() === 'admin' ? 'admin/dashboard.php' : 'user/catalogo.php'));
            exit;
        }

        $erro = 'E-mail ou senha incorretos. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar · <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="tela-auth">
        <div class="auth-lateral">
            <div class="marca">📦 Reserva<span class="ponto">Já</span></div>
            <div class="frase">Reserve laboratórios, projetores e equipamentos da escola em poucos cliques.</div>
            <div class="rodape-lateral">Sistema de Reservas e Empréstimos &middot; Uso interno escolar</div>
        </div>
        <div class="auth-conteudo">
            <div class="cartao-auth">
                <div class="marca-mobile">📦 ReservaJá</div>
                <h1>Bem-vindo de volta</h1>
                <p class="subtitulo">Entre com sua conta para reservar ou gerenciar recursos.</p>

                <?php if ($erro): ?>
                    <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'ok'): ?>
                    <div class="alerta alerta-sucesso">Conta criada com sucesso! Faça login para continuar.</div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="campo">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" placeholder="voce@escola.com" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="campo">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primario btn-bloco">Entrar</button>
                </form>

                <p style="margin-top:22px; font-size:14px;">
                    Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
