<?php
require_once __DIR__ . '/Database.php';

/**
 * Classe Categoria
 * Representa uma categoria de recurso (Ex: "Laboratórios", "Projetores").
 * Tabela auxiliar usada pela classe Recurso.
 */
class Categoria
{
    private PDO $conn;

    private ?int $id = null;
    private string $nome = '';
    private string $descricao = '';

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // ---------------- GETTERS / SETTERS ----------------
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getNome(): string { return $this->nome; }
    public function setNome(string $nome): void { $this->nome = trim($nome); }

    public function getDescricao(): string { return $this->descricao; }
    public function setDescricao(string $descricao): void { $this->descricao = trim($descricao); }

    // ---------------- MÉTODOS DE NEGÓCIO (CRUD) ----------------

    public function salvar(): bool
    {
        if ($this->id) {
            $stmt = $this->conn->prepare(
                'UPDATE categorias SET nome = :nome, descricao = :descricao WHERE id = :id'
            );
            return $stmt->execute([
                'nome'      => $this->nome,
                'descricao' => $this->descricao,
                'id'        => $this->id,
            ]);
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO categorias (nome, descricao) VALUES (:nome, :descricao)'
        );
        $ok = $stmt->execute(['nome' => $this->nome, 'descricao' => $this->descricao]);
        if ($ok) {
            $this->id = (int) $this->conn->lastInsertId();
        }
        return $ok;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM categorias WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM categorias WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $dados = $stmt->fetch();
        return $dados ?: null;
    }

    public function listarTodas(): array
    {
        return $this->conn->query('SELECT * FROM categorias ORDER BY nome ASC')->fetchAll();
    }

    public function contarTotal(): int
    {
        return (int) $this->conn->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
    }
}
