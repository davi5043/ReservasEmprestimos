<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Classe Auth
 * Centraliza o controle de sessão: login, logout e proteção de páginas
 * por tipo de usuário (admin ou usuario comum).
 */
class Auth
{
    /**
     * Grava os dados do usuário autenticado na sessão.
     */
    public static function login(int $id, string $nome, string $email, string $tipo): void
    {
        $_SESSION['usuario_id']    = $id;
        $_SESSION['usuario_nome']  = $nome;
        $_SESSION['usuario_email'] = $email;
        $_SESSION['usuario_tipo']  = $tipo;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain']);
        }
        session_destroy();
    }

    public static function estaLogado(): bool
    {
        return isset($_SESSION['usuario_id']);
    }

    public static function ehAdmin(): bool
    {
        return self::estaLogado() && $_SESSION['usuario_tipo'] === 'admin';
    }

    public static function usuarioId(): ?int
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function usuarioNome(): string
    {
        return $_SESSION['usuario_nome'] ?? '';
    }

    /**
     * Bloqueia o acesso à página caso o visitante não esteja logado.
     */
    public static function exigirLogin(string $redirecionarPara = '../index.php'): void
    {
        if (!self::estaLogado()) {
            header('Location: ' . $redirecionarPara);
            exit;
        }
    }

    /**
     * Bloqueia o acesso à página caso o visitante não seja administrador.
     */
    public static function exigirAdmin(string $redirecionarPara = '../index.php'): void
    {
        self::exigirLogin($redirecionarPara);
        if (!self::ehAdmin()) {
            header('Location: ' . $redirecionarPara);
            exit;
        }
    }
}
