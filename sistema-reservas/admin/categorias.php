<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Categoria.php';

Auth::exigirAdmin('../index.php');

$categoriaModel = new Categoria();
$mensagem = '';
$erro = '';
$emEdicao = null;

// ---------- EXCLUIR ----------
if (isset($_GET['excluir'])) {
    $categoriaModel->excluir((int) $_GET['excluir']);
    header('Location: categorias.php?msg=excluido');
    exit;
}

// ---------- SALVAR (criar ou atualizar) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;

    if ($nome === '') {
        $erro = 'O nome da categoria é obrigatório.';
    } else {
        $categoriaModel->setId($id);
        $categoriaModel->setNome($nome);
        $categoriaModel->setDescricao($descricao);

        if ($categoriaModel->salvar()) {
            header('Location: categorias.php?msg=' . ($id ? 'atualizado' : 'criado'));
            exit;
        }
        $erro = 'Não foi possível salvar a categoria.';
    }
}

// ---------- CARREGAR PARA EDIÇÃO ----------
if (isset($_GET['editar'])) {
    $emEdicao = $categoriaModel->buscarPorId((int) $_GET['editar']);
}

$mensagens = [
    'criado' => 'Categoria criada com sucesso.',
    'atualizado' => 'Categoria atualizada com sucesso.',
    'excluido' => 'Categoria removida com sucesso.',
];
if (isset($_GET['msg'], $mensagens[$_GET['msg']])) {
    $mensagem = $mensagens[$_GET['msg']];
}

$categorias = $categoriaModel->listarTodas();

$tituloPagina = 'Categorias';
include __DIR__ . '/_header.php';
?>
<div class="topo-pagina">
    <div>
        <div class="eyebrow">Tabela auxiliar</div>
        <h1 class="mt-0">Categorias de recursos</h1>
        <p>Organize os recursos em categorias como "Laboratórios" ou "Projetores".</p>
    </div>
</div>

<?php if ($mensagem): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="painel">
    <div class="painel-cabecalho">
        <h2><?= $emEdicao ? 'Editar categoria' : 'Nova categoria' ?></h2>
    </div>
    <div class="painel-corpo">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $emEdicao['id'] ?? '' ?>">
            <div class="campo-linha">
                <div class="campo">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" required placeholder="Ex: Laboratórios"
                           value="<?= htmlspecialchars($emEdicao['nome'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label for="descricao">Descrição (opcional)</label>
                    <input type="text" id="descricao" name="descricao" placeholder="Breve descrição"
                           value="<?= htmlspecialchars($emEdicao['descricao'] ?? '') ?>">
                </div>
            </div>
            <div class="acoes-tabela">
                <button type="submit" class="btn btn-primario"><?= $emEdicao ? 'Salvar alterações' : 'Adicionar categoria' ?></button>
                <?php if ($emEdicao): ?>
                    <a href="categorias.php" class="btn btn-secundario">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="painel">
    <div class="painel-cabecalho">
        <h2>Categorias cadastradas (<?= count($categorias) ?>)</h2>
    </div>
    <?php if (empty($categorias)): ?>
        <div class="vazio">
            <div class="icone-vazio">🏷️</div>
            <p>Nenhuma categoria cadastrada ainda.</p>
        </div>
    <?php else: ?>
    <table>
        <thead><tr><th>Nome</th><th>Descrição</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($categorias as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['nome']) ?></strong></td>
                <td class="texto-suave"><?= htmlspecialchars($c['descricao'] ?: '—') ?></td>
                <td>
                    <div class="acoes-tabela">
                        <a href="categorias.php?editar=<?= $c['id'] ?>" class="btn btn-secundario btn-pequeno">Editar</a>
                        <a href="categorias.php?excluir=<?= $c['id'] ?>" class="btn btn-perigo btn-pequeno"
                           onclick="return confirm('Excluir esta categoria? Recursos vinculados ficarão sem categoria.');">Excluir</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
