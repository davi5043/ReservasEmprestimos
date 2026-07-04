<?php
require_once __DIR__ . '/Database.php';

/**
 * Classe Setor
 * Representa o setor responsável por um recurso (Ex: "TI", "Manutenção").
 * Tabela auxiliar usada pela classe Recurso.
 */
class Setor
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
                'UPDATE setores SET nome = :nome, descricao = :descricao WHERE id = :id'
            );
            return $stmt->execute([
                'nome'      => $this->nome,
                'descricao' => $this->descricao,
                'id'        => $this->id,
            ]);
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO setores (nome, descricao) VALUES (:nome, :descricao)'
        );
        $ok = $stmt->execute(['nome' => $this->nome, 'descricao' => $this->descricao]);
        if ($ok) {
            $this->id = (int) $this->conn->lastInsertId();
        }
        return $ok;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM setores WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM setores WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $dados = $stmt->fetch();
        return $dados ?: null;
    }

    public function listarTodos(): array
    {
        return $this->conn->query('SELECT * FROM setores ORDER BY nome ASC')->fetchAll();
    }

    public function contarTotal(): int
    {
        return (int) $this->conn->query('SELECT COUNT(*) FROM setores')->fetchColumn();
    }
}
