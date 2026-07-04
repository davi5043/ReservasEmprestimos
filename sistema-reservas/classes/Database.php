<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Classe Database
 * Responsável por criar e fornecer uma única conexão PDO para toda a aplicação
 * (Padrão de projeto Singleton).
 */
class Database
{
    private static ?PDO $instance = null;

    // Impede a criação de instâncias externas (private constructor)
    private function __construct()
    {
    }

    /**
     * Retorna a instância única de conexão PDO.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die('Erro de conexão com o banco de dados: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
