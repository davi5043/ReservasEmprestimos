<?php
/**
 * config.php
 * Configurações gerais do sistema e credenciais do banco de dados.
 * Ajuste os valores abaixo conforme o ambiente (XAMPP, WAMP, servidor, etc.)
 */

// ----------- BANCO DE DADOS -----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_reservas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ----------- APLICAÇÃO -----------
define('APP_NAME', 'ReservaJá');
define('APP_URL', 'http://localhost/sistema-reservas'); // ajuste para a URL real do projeto
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');

// Tamanho máximo de upload de foto (em bytes) - 3MB
define('UPLOAD_MAX_SIZE', 3 * 1024 * 1024);
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// ----------- SESSÃO -----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Exibir erros durante o desenvolvimento (remover/comentar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Sao_Paulo');
