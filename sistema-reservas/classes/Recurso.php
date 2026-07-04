<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Classe Recurso
 * Entidade principal do sistema: representa um item que pode ser reservado
 * (laboratório, projetor, kit de robótica, quadra, etc.).
 *
 * Relaciona-se com Categoria (categoria_id) e Setor (setor_id) via chave estrangeira.
 */
class Recurso
{
    private PDO $conn;

    private ?int $id = null;
    private string $nome = '';
    private string $descricao = '';
    private ?int $categoriaId = null;
    private ?int $setorId = null;
    private ?string $foto = null;
    private bool $disponivel = true;
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

    public function getDescricao(): string { return $this->descricao; }
    public function setDescricao(string $descricao): void { $this->descricao = trim($descricao); }

    public function getCategoriaId(): ?int { return $this->categoriaId; }
    public function setCategoriaId(?int $categoriaId): void { $this->categoriaId = $categoriaId; }

    public function getSetorId(): ?int { return $this->setorId; }
    public function setSetorId(?int $setorId): void { $this->setorId = $setorId; }

    public function getFoto(): ?string { return $this->foto; }
    public function setFoto(?string $foto): void { $this->foto = $foto; }

    public function isDisponivel(): bool { return $this->disponivel; }
    public function setDisponivel(bool $disponivel): void { $this->disponivel = $disponivel; }

    // ---------------- MÉTODOS DE NEGÓCIO ----------------

    /**
     * Trata o upload da foto enviada via formulário ($_FILES['foto']).
     * Retorna o nome do arquivo salvo ou null se nenhum arquivo válido foi enviado.
     * Lança uma Exception legível em caso de erro de validação.
     */
    public function tratarUploadFoto(array $arquivo): ?string
    {
        if (empty($arquivo['name'])) {
            return null; // Nenhum arquivo enviado (ex: edição sem trocar a foto)
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Ocorreu um erro ao enviar a imagem. Tente novamente.');
        }

        if ($arquivo['size'] > UPLOAD_MAX_SIZE) {
            throw new Exception('A imagem é muito grande. O tamanho máximo é 3MB.');
        }

        $tipo = mime_content_type($arquivo['tmp_name']);
        if (!in_array($tipo, UPLOAD_ALLOWED_TYPES, true)) {
            throw new Exception('Formato de imagem inválido. Envie JPG, PNG ou WEBP.');
        }

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0775, true);
        }

        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'recurso_' . uniqid() . '.' . strtolower($extensao);

        if (!move_uploaded_file($arquivo['tmp_name'], UPLOAD_DIR . $nomeArquivo)) {
            throw new Exception('Não foi possível salvar a imagem no servidor.');
        }

        return $nomeArquivo;
    }

    public function salvar(): bool
    {
        if ($this->id) {
            $stmt = $this->conn->prepare(
                'UPDATE recursos
                 SET nome = :nome, descricao = :descricao, categoria_id = :categoria_id,
                     setor_id = :setor_id, foto = :foto, disponivel = :disponivel
                 WHERE id = :id'
            );
            return $stmt->execute([
                'nome'         => $this->nome,
                'descricao'    => $this->descricao,
                'categoria_id' => $this->categoriaId,
                'setor_id'     => $this->setorId,
                'foto'         => $this->foto,
                'disponivel'   => $this->disponivel ? 1 : 0,
                'id'           => $this->id,
            ]);
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO recursos (nome, descricao, categoria_id, setor_id, foto, disponivel, criado_em)
             VALUES (:nome, :descricao, :categoria_id, :setor_id, :foto, :disponivel, NOW())'
        );
        $ok = $stmt->execute([
            'nome'         => $this->nome,
            'descricao'    => $this->descricao,
            'categoria_id' => $this->categoriaId,
            'setor_id'     => $this->setorId,
            'foto'         => $this->foto,
            'disponivel'   => $this->disponivel ? 1 : 0,
        ]);
        if ($ok) {
            $this->id = (int) $this->conn->lastInsertId();
        }
        return $ok;
    }

    public function excluir(int $id): bool
    {
        // Remove o arquivo de foto físico, se existir
        $recurso = $this->buscarPorId($id);
        if ($recurso && !empty($recurso['foto']) && file_exists(UPLOAD_DIR . $recurso['foto'])) {
            unlink(UPLOAD_DIR . $recurso['foto']);
        }

        $stmt = $this->conn->prepare('DELETE FROM recursos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT r.*, c.nome AS categoria_nome, s.nome AS setor_nome
             FROM recursos r
             LEFT JOIN categorias c ON c.id = r.categoria_id
             LEFT JOIN setores s ON s.id = r.setor_id
             WHERE r.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $dados = $stmt->fetch();
        return $dados ?: null;
    }

    /**
     * Lista todos os recursos, com filtro opcional por categoria.
     */
    public function listarTodos(?int $categoriaId = null): array
    {
        $sql = 'SELECT r.*, c.nome AS categoria_nome, s.nome AS setor_nome
                FROM recursos r
                LEFT JOIN categorias c ON c.id = r.categoria_id
                LEFT JOIN setores s ON s.id = r.setor_id';

        $params = [];
        if ($categoriaId) {
            $sql .= ' WHERE r.categoria_id = :categoria_id';
            $params['categoria_id'] = $categoriaId;
        }
        $sql .= ' ORDER BY r.nome ASC';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contarTotal(): int
    {
        return (int) $this->conn->query('SELECT COUNT(*) FROM recursos')->fetchColumn();
    }

    public function contarDisponiveis(): int
    {
        return (int) $this->conn->query('SELECT COUNT(*) FROM recursos WHERE disponivel = 1')->fetchColumn();
    }
}
