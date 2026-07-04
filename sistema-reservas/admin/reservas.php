<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Reserva.php';

Auth::exigirAdmin('../index.php');

$reservaModel = new Reserva();
$mensagem = '';

if (isset($_GET['cancelar'])) {
    $reservaModel->cancelar((int) $_GET['cancelar']);
    header('Location: reservas.php?msg=cancelado');
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] === 'cancelado') {
    $mensagem = 'Reserva cancelada com sucesso.';
}

$reservas = $reservaModel->listarTodas();

$tituloPagina = 'Reservas';
include __DIR__ . '/_header.php';
?>
<div class="topo-pagina">
    <div>
        <div class="eyebrow">Movimentação</div>
        <h1 class="mt-0">Todas as reservas</h1>
        <p>Acompanhe quem reservou o quê, e cancele reservas quando necessário.</p>
    </div>
</div>

<?php if ($mensagem): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>

<div class="painel">
    <div class="painel-cabecalho">
        <h2>Histórico de reservas (<?= count($reservas) ?>)</h2>
    </div>
    <?php if (empty($reservas)): ?>
        <div class="vazio">
            <div class="icone-vazio">📅</div>
            <p>Ainda não há reservas registradas no sistema.</p>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Recurso</th><th>Usuário</th><th>Data</th><th>Turno</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($reservas as $r): ?>
            <tr>
                <td><strong><?= htmlspecialchars($r['recurso_nome']) ?></strong></td>
                <td>
                    <?= htmlspecialchars($r['usuario_nome']) ?>
                    <div class="texto-suave" style="font-size:12px;"><?= htmlspecialchars($r['usuario_email']) ?></div>
                </td>
                <td><?= date('d/m/Y', strtotime($r['data_reserva'])) ?></td>
                <td><span class="selo selo-neutro"><?= Reserva::rotuloTurno($r['turno']) ?></span></td>
                <td><span class="selo selo-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                <td>
                    <?php if ($r['status'] === 'ativa'): ?>
                        <a href="reservas.php?cancelar=<?= $r['id'] ?>" class="btn btn-perigo btn-pequeno"
                           onclick="return confirm('Cancelar esta reserva?');">Cancelar</a>
                    <?php else: ?>
                        <span class="texto-suave" style="font-size:13px;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
