<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Usuario.php';

if (Auth::estaLogado()) {
    header('Location: ' . (Auth::ehAdmin() ? 'admin/dashboard.php' : 'user/catalogo.php'));
    exit;
}

$erro = '';
$nome = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha todos os campos para continuar.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas informadas não coincidem.';
    } else {
        $usuario = new Usuario();

        if ($usuario->emailExiste($email)) {
            $erro = 'Já existe uma conta cadastrada com este e-mail.';
        } else {
            $usuario->setNome($nome);
            $usuario->setEmail($email);
            $usuario->setSenha($senha);
            $usuario->setTipo('usuario'); // Todo cadastro público cria um usuário comum

            if ($usuario->cadastrar()) {
                header('Location: index.php?cadastro=ok');
                exit;
            }
            $erro = 'Não foi possível concluir o cadastro. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta · <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="tela-auth">
        <div class="auth-lateral">
            <div class="marca">📦 Reserva<span class="ponto">Já</span></div>
            <div class="frase">Crie sua conta e comece a reservar os recursos disponíveis na escola.</div>
            <div class="rodape-lateral">Sistema de Reservas e Empréstimos &middot; Uso interno escolar</div>
        </div>
        <div class="auth-conteudo">
            <div class="cartao-auth">
                <div class="marca-mobile">📦 ReservaJá</div>
                <h1>Criar sua conta</h1>
                <p class="subtitulo">Leva menos de um minuto.</p>

                <?php if ($erro): ?>
                    <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="campo">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome" placeholder="Seu nome" required
                               value="<?= htmlspecialchars($nome) ?>">
                    </div>
                    <div class="campo">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" placeholder="voce@escola.com" required
                               value="<?= htmlspecialchars($email) ?>">
                    </div>
                    <div class="campo-linha">
                        <div class="campo">
                            <label for="senha">Senha</label>
                            <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
                        </div>
                        <div class="campo">
                            <label for="confirmar_senha">Confirmar senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primario btn-bloco">Criar conta</button>
                </form>

                <p style="margin-top:22px; font-size:14px;">
                    Já tem conta? <a href="index.php">Fazer login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
