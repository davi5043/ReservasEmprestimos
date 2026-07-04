<?php
require_once __DIR__ . '/Database.php';

/**
 * Classe Reserva
 * Representa a reserva de um Recurso feita por um Usuário.
 * A reserva é direta e imediata: o usuário escolhe um recurso disponível,
 * define a data e o TURNO (manhã, tarde ou noite) e a reserva é confirmada
 * sem necessidade de aprovação.
 *
 * Assim que um turno é reservado, ele aparece como "Indisponível" para
 * qualquer pessoa (o próprio usuário e os demais) que consultar aquele
 * recurso naquela data.
 *
 * status possíveis: 'ativa', 'concluida', 'cancelada'
 * turno possíveis:  'manha', 'tarde', 'noite'
 */
class Reserva
{
    private PDO $conn;

    private ?int $id = null;
    private ?int $usuarioId = null;
    private ?int $recursoId = null;
    private string $dataReserva = '';
    private string $turno = '';
    private string $status = 'ativa';
    private ?string $criadoEm = null;

    /**
     * Turnos válidos e seus rótulos amigáveis para exibição.
     */
    public const TURNOS = [
        'manha' => 'Manhã',
        'tarde' => 'Tarde',
        'noite' => 'Noite',
    ];

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // ---------------- GETTERS / SETTERS ----------------
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getUsuarioId(): ?int { return $this->usuarioId; }
    public function setUsuarioId(?int $usuarioId): void { $this->usuarioId = $usuarioId; }

    public function getRecursoId(): ?int { return $this->recursoId; }
    public function setRecursoId(?int $recursoId): void { $this->recursoId = $recursoId; }

    public function getDataReserva(): string { return $this->dataReserva; }
    public function setDataReserva(string $data): void { $this->dataReserva = $data; }

    public function getTurno(): string { return $this->turno; }
    public function setTurno(string $turno): void { $this->turno = $turno; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    /**
     * Retorna o rótulo amigável do turno (Ex: "manha" -> "Manhã").
     */
    public static function rotuloTurno(string $turno): string
    {
        return self::TURNOS[$turno] ?? ucfirst($turno);
    }

    // ---------------- MÉTODOS DE NEGÓCIO ----------------

    /**
     * Verifica se o recurso já possui uma reserva ATIVA para a mesma data e
     * o mesmo turno (ou seja, se aquele turno já está indisponível).
     */
    public function existeConflito(int $recursoId, string $data, string $turno, ?int $ignorarReservaId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM reservas
                WHERE recurso_id = :recurso_id
                  AND data_reserva = :data
                  AND turno = :turno
                  AND status = 'ativa'";
        $params = [
            'recurso_id' => $recursoId,
            'data'       => $data,
            'turno'      => $turno,
        ];

        if ($ignorarReservaId) {
            $sql .= ' AND id != :ignorar_id';
            $params['ignorar_id'] = $ignorarReservaId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Retorna quais turnos já estão ocupados (reserva ativa) para um recurso
     * em uma determinada data. Ex: ['manha', 'noite'].
     * Usado para mostrar a disponibilidade por turno na tela de reserva,
     * de forma visível tanto para quem está reservando quanto para os demais.
     */
    public function turnosOcupados(int $recursoId, string $data): array
    {
        $stmt = $this->conn->prepare(
            "SELECT turno FROM reservas
             WHERE recurso_id = :recurso_id AND data_reserva = :data AND status = 'ativa'"
        );
        $stmt->execute(['recurso_id' => $recursoId, 'data' => $data]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Confirma a reserva imediatamente (sem etapa de aprovação).
     */
    public function reservar(): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO reservas (usuario_id, recurso_id, data_reserva, turno, status, criado_em)
             VALUES (:usuario_id, :recurso_id, :data_reserva, :turno, 'ativa', NOW())"
        );
        $ok = $stmt->execute([
            'usuario_id'   => $this->usuarioId,
            'recurso_id'   => $this->recursoId,
            'data_reserva' => $this->dataReserva,
            'turno'        => $this->turno,
        ]);
        if ($ok) {
            $this->id = (int) $this->conn->lastInsertId();
        }
        return $ok;
    }

    /**
     * Cancela uma reserva (usada pelo próprio usuário ou pelo admin).
     * Ao cancelar, o turno volta a ficar disponível para todos.
     */
    public function cancelar(int $id): bool
    {
        $stmt = $this->conn->prepare("UPDATE reservas SET status = 'cancelada' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Lista as reservas de um usuário específico, das mais recentes para as mais antigas.
     */
    public function listarPorUsuario(int $usuarioId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT res.*, r.nome AS recurso_nome, r.foto AS recurso_foto
             FROM reservas res
             JOIN recursos r ON r.id = res.recurso_id
             WHERE res.usuario_id = :usuario_id
             ORDER BY res.data_reserva DESC, res.id DESC'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Lista todas as reservas do sistema (visão administrativa).
     */
    public function listarTodas(): array
    {
        $stmt = $this->conn->query(
            'SELECT res.*, r.nome AS recurso_nome, u.nome AS usuario_nome, u.email AS usuario_email
             FROM reservas res
             JOIN recursos r ON r.id = res.recurso_id
             JOIN usuarios u ON u.id = res.usuario_id
             ORDER BY res.data_reserva DESC, res.id DESC'
        );
        return $stmt->fetchAll();
    }

    public function contarAtivas(): int
    {
        return (int) $this->conn->query("SELECT COUNT(*) FROM reservas WHERE status = 'ativa'")->fetchColumn();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT res.*, r.nome AS recurso_nome
             FROM reservas res JOIN recursos r ON r.id = res.recurso_id
             WHERE res.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $dados = $stmt->fetch();
        return $dados ?: null;
    }
}
