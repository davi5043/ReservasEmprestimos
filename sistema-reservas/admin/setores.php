<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Setor.php';

Auth::exigirAdmin('../index.php');

$setorModel = new Setor();
$mensagem = '';
$erro = '';
$emEdicao = null;

if (isset($_GET['excluir'])) {
    $setorModel->excluir((int) $_GET['excluir']);
    header('Location: setores.php?msg=excluido');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;

    if ($nome === '') {
        $erro = 'O nome do setor é obrigatório.';
    } else {
        $setorModel->setId($id);
        $setorModel->setNome($nome);
        $setorModel->setDescricao($descricao);

        if ($setorModel->salvar()) {
            header('Location: setores.php?msg=' . ($id ? 'atualizado' : 'criado'));
            exit;
        }
        $erro = 'Não foi possível salvar o setor.';
    }
}

if (isset($_GET['editar'])) {
    $emEdicao = $setorModel->buscarPorId((int) $_GET['editar']);
}

$mensagens = [
    'criado' => 'Setor criado com sucesso.',
    'atualizado' => 'Setor atualizado com sucesso.',
    'excluido' => 'Setor removido com sucesso.',
];
if (isset($_GET['msg'], $mensagens[$_GET['msg']])) {
    $mensagem = $mensagens[$_GET['msg']];
}

$setores = $setorModel->listarTodos();

$tituloPagina = 'Setores';
include __DIR__ . '/_header.php';
?>
<div class="topo-pagina">
    <div>
        <div class="eyebrow">Tabela auxiliar</div>
        <h1 class="mt-0">Setores responsáveis</h1>
        <p>Defina quem é responsável pela retirada e manutenção de cada recurso.</p>
    </div>
</div>

<?php if ($mensagem): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="painel">
    <div class="painel-cabecalho">
        <h2><?= $emEdicao ? 'Editar setor' : 'Novo setor' ?></h2>
    </div>
    <div class="painel-corpo">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $emEdicao['id'] ?? '' ?>">
            <div class="campo-linha">
                <div class="campo">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" required placeholder="Ex: TI"
                           value="<?= htmlspecialchars($emEdicao['nome'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label for="descricao">Descrição (opcional)</label>
                    <input type="text" id="descricao" name="descricao" placeholder="Breve descrição"
                           value="<?= htmlspecialchars($emEdicao['descricao'] ?? '') ?>">
                </div>
            </div>
            <div class="acoes-tabela">
                <button type="submit" class="btn btn-primario"><?= $emEdicao ? 'Salvar alterações' : 'Adicionar setor' ?></button>
                <?php if ($emEdicao): ?>
                    <a href="setores.php" class="btn btn-secundario">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="painel">
    <div class="painel-cabecalho">
        <h2>Setores cadastrados (<?= count($setores) ?>)</h2>
    </div>
    <?php if (empty($setores)): ?>
        <div class="vazio">
            <div class="icone-vazio">🧩</div>
            <p>Nenhum setor cadastrado ainda.</p>
        </div>
    <?php else: ?>
    <table>
        <thead><tr><th>Nome</th><th>Descrição</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($setores as $s): ?>
            <tr>
                <td><strong><?= htmlspecialchars($s['nome']) ?></strong></td>
                <td class="texto-suave"><?= htmlspecialchars($s['descricao'] ?: '—') ?></td>
                <td>
                    <div class="acoes-tabela">
                        <a href="setores.php?editar=<?= $s['id'] ?>" class="btn btn-secundario btn-pequeno">Editar</a>
                        <a href="setores.php?excluir=<?= $s['id'] ?>" class="btn btn-perigo btn-pequeno"
                           onclick="return confirm('Excluir este setor? Recursos vinculados ficarão sem setor.');">Excluir</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
