<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Recurso.php';
require_once __DIR__ . '/../classes/Reserva.php';

Auth::exigirLogin('../index.php');

$recursoModel = new Recurso();
$reservaModel = new Reserva();

$recursoId = (int) ($_GET['id'] ?? $_POST['recurso_id'] ?? 0);
$recurso = $recursoModel->buscarPorId($recursoId);

if (!$recurso) {
    header('Location: catalogo.php');
    exit;
}

$erro = '';

// Data selecionada para consultar/realizar a reserva (hoje por padrão)
$dataSelecionada = $_POST['data_reserva'] ?? $_GET['data'] ?? date('Y-m-d');

// ---------- CONFIRMAR RESERVA ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['turno'])) {
    $data = trim($_POST['data_reserva'] ?? '');
    $turno = trim($_POST['turno'] ?? '');
    $turnosValidos = array_keys(Reserva::TURNOS);

    if (!$recurso['disponivel']) {
        $erro = 'Este recurso não está disponível para reserva no momento.';
    } elseif ($data === '' || !in_array($turno, $turnosValidos, true)) {
        $erro = 'Selecione uma data e um turno válido.';
    } elseif (strtotime($data) < strtotime(date('Y-m-d'))) {
        $erro = 'A data da reserva não pode ser no passado.';
    } elseif ($reservaModel->existeConflito($recursoId, $data, $turno)) {
        $erro = 'Este turno já está indisponível para este recurso. Escolha outro turno ou outra data.';
    } else {
        $reservaModel->setUsuarioId(Auth::usuarioId());
        $reservaModel->setRecursoId($recursoId);
        $reservaModel->setDataReserva($data);
        $reservaModel->setTurno($turno);

        if ($reservaModel->reservar()) {
            header('Location: minhas_reservas.php?msg=reservado');
            exit;
        }
        $erro = 'Não foi possível concluir a reserva. Tente novamente.';
    }

    $dataSelecionada = $data ?: $dataSelecionada;
}

// Turnos já ocupados (reserva ativa) para a data selecionada — visível para todos
$turnosOcupados = $reservaModel->turnosOcupados($recursoId, $dataSelecionada);

$tituloPagina = $recurso['nome'];
include __DIR__ . '/_header.php';
?>
<div class="topo-pagina">
    <div>
        <div class="eyebrow"><?= htmlspecialchars($recurso['categoria_nome'] ?? 'Recurso') ?></div>
        <h1 class="mt-0"><?= htmlspecialchars($recurso['nome']) ?></h1>
    </div>
    <a href="catalogo.php" class="btn btn-secundario btn-pequeno">← Voltar ao catálogo</a>
</div>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="grade-detalhe">
    <div>
        <?php if ($recurso['foto']): ?>
            <img src="../uploads/<?= htmlspecialchars($recurso['foto']) ?>" class="imagem-detalhe" alt="<?= htmlspecialchars($recurso['nome']) ?>">
        <?php else: ?>
            <div class="imagem-detalhe imagem-placeholder" style="display:flex;align-items:center;justify-content:center;">
                <span style="font-size:52px; color:var(--cor-primaria);"><?= htmlspecialchars(mb_substr($recurso['nome'], 0, 1)) ?></span>
            </div>
        <?php endif; ?>

        <h3 style="margin-top:22px;">Sobre este recurso</h3>
        <p><?= nl2br(htmlspecialchars($recurso['descricao'] ?: 'Nenhuma descrição adicional foi cadastrada para este recurso.')) ?></p>

        <ul class="lista-info-detalhe">
            <li><span class="rotulo">Categoria</span><span class="valor"><?= htmlspecialchars($recurso['categoria_nome'] ?? '—') ?></span></li>
            <li><span class="rotulo">Setor responsável</span><span class="valor"><?= htmlspecialchars($recurso['setor_nome'] ?? '—') ?></span></li>
            <li><span class="rotulo">Status</span>
                <span class="valor">
                    <?php if ($recurso['disponivel']): ?>
                        <span class="selo selo-disponivel">Disponível</span>
                    <?php else: ?>
                        <span class="selo selo-indisponivel">Indisponível</span>
                    <?php endif; ?>
                </span>
            </li>
        </ul>
    </div>

    <div class="painel" style="align-self:start;">
        <div class="painel-cabecalho"><h2>Reservar por turno</h2></div>
        <div class="painel-corpo">
            <?php if (!$recurso['disponivel']): ?>
                <p class="texto-suave">Este recurso está indisponível no momento e não pode ser reservado.</p>
            <?php else: ?>

                <!-- Escolha da data: recarrega a página para mostrar os turnos daquele dia -->
                <form method="GET" id="form-data">
                    <input type="hidden" name="id" value="<?= $recurso['id'] ?>">
                    <div class="campo">
                        <label for="data_consulta">Escolha a data</label>
                        <input type="date" id="data_consulta" name="data" required
                               min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($dataSelecionada) ?>"
                               onchange="document.getElementById('form-data').submit();">
                    </div>
                </form>

                <div class="dica-campo" style="margin-bottom:14px;">
                    Disponibilidade para <strong><?= date('d/m/Y', strtotime($dataSelecionada)) ?></strong> — atualizada em tempo real para todos os usuários.
                </div>

                <form method="POST">
                    <input type="hidden" name="recurso_id" value="<?= $recurso['id'] ?>">
                    <input type="hidden" name="data_reserva" value="<?= htmlspecialchars($dataSelecionada) ?>">

                    <div class="campo">
                        <label>Turno</label>
                        <div class="opcoes-turno">
                            <?php foreach (Reserva::TURNOS as $chave => $rotulo):
                                $ocupado = in_array($chave, $turnosOcupados, true);
                            ?>
                                <label class="opcao-turno <?= $ocupado ? 'ocupado' : '' ?>">
                                    <input type="radio" name="turno" value="<?= $chave ?>" <?= $ocupado ? 'disabled' : 'required' ?>>
                                    <span class="opcao-turno-nome"><?= $rotulo ?></span>
                                    <span class="selo <?= $ocupado ? 'selo-indisponivel' : 'selo-disponivel' ?>">
                                        <?= $ocupado ? 'Indisponível' : 'Disponível' ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-acento btn-bloco" <?= count($turnosOcupados) === 3 ? 'disabled' : '' ?>>
                        Confirmar reserva
                    </button>
                    <p class="dica-campo" style="margin-top:12px;">A reserva é confirmada imediatamente. Assim que você reservar, o turno aparece como <strong>indisponível</strong> para todos os outros usuários.</p>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
