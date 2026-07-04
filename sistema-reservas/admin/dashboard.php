<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Categoria.php';
require_once __DIR__ . '/../classes/Setor.php';
require_once __DIR__ . '/../classes/Recurso.php';
require_once __DIR__ . '/../classes/Reserva.php';

Auth::exigirAdmin('../index.php');

$categoria = new Categoria();
$setor = new Setor();
$recurso = new Recurso();
$reserva = new Reserva();

$totalRecursos = $recurso->contarTotal();
$totalDisponiveis = $recurso->contarDisponiveis();
$totalCategorias = $categoria->contarTotal();
$totalReservasAtivas = $reserva->contarAtivas();

$ultimasReservas = array_slice($reserva->listarTodas(), 0, 6);

$tituloPagina = 'Painel';
include __DIR__ . '/_header.php';
?>
<div class="topo-pagina">
    <div>
        <div class="eyebrow">Painel administrativo</div>
        <h1 class="mt-0">Olá, <?= htmlspecialchars(Auth::usuarioNome()) ?> 👋</h1>
        <p>Aqui está um resumo dos recursos e reservas cadastrados na escola.</p>
    </div>
</div>

<div class="grade-estat">
    <div class="cartao-estat">
        <div class="numero"><?= $totalRecursos ?></div>
        <div class="rotulo">Recursos catalogados</div>
    </div>
    <div class="cartao-estat">
        <div class="numero"><?= $totalDisponiveis ?></div>
        <div class="rotulo">Disponíveis agora</div>
    </div>
    <div class="cartao-estat">
        <div class="numero"><?= $totalCategorias ?></div>
        <div class="rotulo">Categorias criadas</div>
    </div>
    <div class="cartao-estat acento">
        <div class="numero"><?= $totalReservasAtivas ?></div>
        <div class="rotulo">Reservas ativas</div>
    </div>
</div>

<div class="painel">
    <div class="painel-cabecalho">
        <h2>Últimas reservas</h2>
        <a href="reservas.php" class="btn btn-secundario btn-pequeno">Ver todas</a>
    </div>
    <?php if (empty($ultimasReservas)): ?>
        <div class="vazio">
            <div class="icone-vazio">📭</div>
            <p>Nenhuma reserva foi feita ainda.</p>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Recurso</th>
                <th>Usuário</th>
                <th>Data</th>
                <th>Turno</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ultimasReservas as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['recurso_nome']) ?></td>
                <td><?= htmlspecialchars($r['usuario_nome']) ?></td>
                <td><?= date('d/m/Y', strtotime($r['data_reserva'])) ?></td>
                <td><span class="selo selo-neutro"><?= Reserva::rotuloTurno($r['turno']) ?></span></td>
                <td><span class="selo selo-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
