<?php
require_once __DIR__ . '/Database.php';

/**
 * Classe Usuario
 * Representa um usuário do sistema (administrador ou usuário comum) e concentra
 * as operações de CRUD e autenticação relacionadas a ele.
 *
 * Atributos:
 * - id, nome, email, senha (hash), tipo ('admin' | 'usuario'), criadoEm
 */
class Usuario
{
    private PDO $conn;

    private ?int $id = null;
    private string $nome = '';
    private string $email = '';
    private string $senha = '';
    private string $tipo = 'usuario';
    private ?string $criadoEm = null;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // ---------------- GETTERS / SETTERS ----------------
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): void { $this->nome = trim($nome); }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = strtolower(trim($email)); }

    public function getTipo(): string { return $this->tipo; }
    public function setTipo(string $tipo): void { $this->tipo = $tipo === 'admin' ? 'admin' : 'usuario'; }

    public function getCriadoEm(): ?string { return $this->criadoEm; }

    public function setSenha(string $senhaTextoPuro): void
    {
        $this->senha = password_hash($senhaTextoPuro, PASSWORD_DEFAULT);
    }

    // ---------------- MÉTODOS DE NEGÓCIO ----------------

    /**
     * Verifica se já existe um usuário cadastrado com o e-mail informado.
     */
    public function emailExiste(string $email): bool
    {
        $stmt = $this->conn->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        return (bool) $stmt->fetch();
    }

    /**
     * Persiste um novo usuário no banco de dados.
     */
    public function cadastrar(): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO usuarios (nome, email, senha, tipo, criado_em)
             VALUES (:nome, :email, :senha, :tipo, NOW())'
        );

        $ok = $stmt->execute([
            'nome'  => $this->nome,
            'email' => $this->email,
            'senha' => $this->senha,
            'tipo'  => $this->tipo,
        ]);

        if ($ok) {
            $this->id = (int) $this->conn->lastInsertId();
        }

        return $ok;
    }

    /**
     * Autentica um usuário pelo e-mail e senha em texto puro.
     * Retorna a instância de Usuario preenchida em caso de sucesso, ou null.
     */
    public function autenticar(string $email, string $senhaTextoPuro): ?Usuario
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $dados = $stmt->fetch();

        if ($dados && password_verify($senhaTextoPuro, $dados['senha'])) {
            return self::hidratarDeArray($dados);
        }

        return null;
    }

    /**
     * Busca um usuário pelo ID.
     */
    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->conn->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $dados = $stmt->fetch();
        return $dados ? self::hidratarDeArray($dados) : null;
    }

    /**
     * Lista todos os usuários cadastrados (uso administrativo).
     */
    public function listarTodos(): array
    {
        $stmt = $this->conn->query('SELECT id, nome, email, tipo, criado_em FROM usuarios ORDER BY nome ASC');
        return $stmt->fetchAll();
    }

    /**
     * Converte uma linha do banco em um objeto Usuario totalmente preenchido.
     */
    private static function hidratarDeArray(array $dados): Usuario
    {
        $u = new Usuario();
        $u->id = (int) $dados['id'];
        $u->nome = $dados['nome'];
        $u->email = $dados['email'];
        $u->senha = $dados['senha'];
        $u->tipo = $dados['tipo'];
        $u->criadoEm = $dados['criado_em'];
        return $u;
    }
}
