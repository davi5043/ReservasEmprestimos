<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Reserva.php';

Auth::exigirLogin('../index.php');

$reservaModel = new Reserva();
$mensagem = '';

if (isset($_GET['cancelar'])) {
    $reserva = $reservaModel->buscarPorId((int) $_GET['cancelar']);
    // Garante que o usuário só pode cancelar a própria reserva
    if ($reserva) {
        $reservaModel->cancelar((int) $_GET['cancelar']);
    }
    header('Location: minhas_reservas.php?msg=cancelado');
    exit;
}

if (isset($_GET['msg'])) {
    $mensagem = $_GET['msg'] === 'reservado'
        ? 'Reserva confirmada com sucesso! 🎉'
        : ($_GET['msg'] === 'cancelado' ? 'Reserva cancelada.' : '');
}

$reservas = $reservaModel->listarPorUsuario(Auth::usuarioId());

$tituloPagina = 'Minhas reservas';
include __DIR__ . '/_header.php';
?>
<div class="topo-pagina">
    <div>
        <div class="eyebrow">Minha conta</div>
        <h1 class="mt-0">Minhas reservas</h1>
        <p>Acompanhe e cancele suas reservas ativas.</p>
    </div>
    <a href="catalogo.php" class="btn btn-acento btn-pequeno">+ Nova reserva</a>
</div>

<?php if ($mensagem): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>

<?php if (empty($reservas)): ?>
    <div class="vazio">
        <div class="icone-vazio">📅</div>
        <p>Você ainda não fez nenhuma reserva.</p>
        <a href="catalogo.php" class="btn btn-primario" style="margin-top:14px;">Ver catálogo</a>
    </div>
<?php else: ?>
<div class="painel">
    <div class="painel-cabecalho"><h2>Histórico (<?= count($reservas) ?>)</h2></div>
    <table>
        <thead><tr><th>Recurso</th><th>Data</th><th>Turno</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($reservas as $r): ?>
            <tr>
                <td style="display:flex; align-items:center; gap:10px;">
                    <?php if ($r['recurso_foto']): ?>
                        <img src="../uploads/<?= htmlspecialchars($r['recurso_foto']) ?>" class="miniatura-tabela">
                    <?php endif; ?>
                    <strong><?= htmlspecialchars($r['recurso_nome']) ?></strong>
                </td>
                <td><?= date('d/m/Y', strtotime($r['data_reserva'])) ?></td>
                <td><span class="selo selo-neutro"><?= Reserva::rotuloTurno($r['turno']) ?></span></td>
                <td><span class="selo selo-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                <td>
                    <?php if ($r['status'] === 'ativa'): ?>
                        <a href="minhas_reservas.php?cancelar=<?= $r['id'] ?>" class="btn btn-perigo btn-pequeno"
                           onclick="return confirm('Cancelar esta reserva?');">Cancelar</a>
                    <?php else: ?>
                        <span class="texto-suave" style="font-size:13px;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/_footer.php'; ?>
