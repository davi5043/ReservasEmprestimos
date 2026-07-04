<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Recurso.php';
require_once __DIR__ . '/../classes/Categoria.php';
require_once __DIR__ . '/../classes/Setor.php';

Auth::exigirAdmin('../index.php');

$recursoModel = new Recurso();
$categoriaModel = new Categoria();
$setorModel = new Setor();

$mensagem = '';
$erro = '';
$emEdicao = null;

// ---------- EXCLUIR ----------
if (isset($_GET['excluir'])) {
    $recursoModel->excluir((int) $_GET['excluir']);
    header('Location: recursos.php?msg=excluido');
    exit;
}

// ---------- SALVAR (criar ou atualizar) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoriaId = !empty($_POST['categoria_id']) ? (int) $_POST['categoria_id'] : null;
    $setorId = !empty($_POST['setor_id']) ? (int) $_POST['setor_id'] : null;
    $disponivel = isset($_POST['disponivel']);
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;

    if ($nome === '') {
        $erro = 'O nome do recurso é obrigatório.';
    } else {
        try {
            $nomeFoto = $recursoModel->tratarUploadFoto($_FILES['foto'] ?? []);

            $recursoModel->setId($id);
            $recursoModel->setNome($nome);
            $recursoModel->setDescricao($descricao);
            $recursoModel->setCategoriaId($categoriaId);
            $recursoModel->setSetorId($setorId);
            $recursoModel->setDisponivel($disponivel);

            // Se está editando e não enviou nova foto, mantém a foto atual
            if ($nomeFoto) {
                $recursoModel->setFoto($nomeFoto);
            } elseif ($id) {
                $atual = $recursoModel->buscarPorId($id);
                $recursoModel->setFoto($atual['foto'] ?? null);
            }

            if ($recursoModel->salvar()) {
                header('Location: recursos.php?msg=' . ($id ? 'atualizado' : 'criado'));
                exit;
            }
            $erro = 'Não foi possível salvar o recurso.';
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}

// ---------- CARREGAR PARA EDIÇÃO ----------
if (isset($_GET['editar'])) {
    $emEdicao = $recursoModel->buscarPorId((int) $_GET['editar']);
}

$mensagens = [
    'criado' => 'Recurso cadastrado com sucesso.',
    'atualizado' => 'Recurso atualizado com sucesso.',
    'excluido' => 'Recurso removido com sucesso.',
];
if (isset($_GET['msg'], $mensagens[$_GET['msg']])) {
    $mensagem = $mensagens[$_GET['msg']];
}

$recursos = $recursoModel->listarTodos();
$categorias = $categoriaModel->listarTodas();
$setores = $setorModel->listarTodos();

$tituloPagina = 'Recursos';
include __DIR__ . '/_header.php';
?>
<div class="topo-pagina">
    <div>
        <div class="eyebrow">Cadastro principal</div>
        <h1 class="mt-0">Recursos disponíveis para reserva</h1>
        <p>Cadastre laboratórios, projetores, kits e outros itens que poderão ser reservados.</p>
    </div>
</div>

<?php if ($mensagem): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<?php if (empty($categorias) || empty($setores)): ?>
    <div class="alerta alerta-info">
        Cadastre pelo menos uma <a href="categorias.php">categoria</a> e um <a href="setores.php">setor</a> antes de adicionar recursos.
    </div>
<?php endif; ?>

<div class="painel">
    <div class="painel-cabecalho">
        <h2><?= $emEdicao ? 'Editar recurso' : 'Novo recurso' ?></h2>
    </div>
    <div class="painel-corpo">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $emEdicao['id'] ?? '' ?>">

            <div class="campo">
                <label for="nome">Nome do recurso</label>
                <input type="text" id="nome" name="nome" required placeholder="Ex: Laboratório de Informática 1"
                       value="<?= htmlspecialchars($emEdicao['nome'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" placeholder="Detalhes úteis sobre o recurso"><?= htmlspecialchars($emEdicao['descricao'] ?? '') ?></textarea>
            </div>

            <div class="campo-linha">
                <div class="campo">
                    <label for="categoria_id">Categoria</label>
                    <select id="categoria_id" name="categoria_id">
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($emEdicao['categoria_id']) && $emEdicao['categoria_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="setor_id">Setor responsável</label>
                    <select id="setor_id" name="setor_id">
                        <option value="">Selecione...</option>
                        <?php foreach ($setores as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (isset($emEdicao['setor_id']) && $emEdicao['setor_id'] == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="campo">
                <label for="foto">Foto do recurso <?= $emEdicao ? '(opcional - deixe em branco para manter a atual)' : '' ?></label>
                <input type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/webp">
                <div class="dica-campo">JPG, PNG ou WEBP · até 3MB</div>
                <?php if (!empty($emEdicao['foto'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($emEdicao['foto']) ?>" class="miniatura-tabela" style="margin-top:10px;">
                <?php endif; ?>
            </div>

            <div class="campo">
                <label style="display:flex; align-items:center; gap:8px; font-weight:500;">
                    <input type="checkbox" name="disponivel" style="width:auto;"
                           <?= (!isset($emEdicao) || !empty($emEdicao['disponivel']) || !isset($emEdicao['disponivel'])) ? 'checked' : '' ?>>
                    Disponível para reserva
                </label>
            </div>

            <div class="acoes-tabela">
                <button type="submit" class="btn btn-primario"><?= $emEdicao ? 'Salvar alterações' : 'Cadastrar recurso' ?></button>
                <?php if ($emEdicao): ?>
                    <a href="recursos.php" class="btn btn-secundario">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="painel">
    <div class="painel-cabecalho">
        <h2>Recursos cadastrados (<?= count($recursos) ?>)</h2>
    </div>
    <?php if (empty($recursos)): ?>
        <div class="vazio">
            <div class="icone-vazio">📷</div>
            <p>Nenhum recurso cadastrado ainda.</p>
        </div>
    <?php else: ?>
    <table>
        <thead><tr><th>Foto</th><th>Nome</th><th>Categoria</th><th>Setor</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recursos as $r): ?>
            <tr>
                <td>
                    <?php if ($r['foto']): ?>
                        <img src="../uploads/<?= htmlspecialchars($r['foto']) ?>" class="miniatura-tabela">
                    <?php else: ?>
                        <div class="miniatura-tabela" style="display:flex;align-items:center;justify-content:center;color:#245652;">—</div>
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($r['nome']) ?></strong></td>
                <td class="texto-suave"><?= htmlspecialchars($r['categoria_nome'] ?? '—') ?></td>
                <td class="texto-suave"><?= htmlspecialchars($r['setor_nome'] ?? '—') ?></td>
                <td>
                    <?php if ($r['disponivel']): ?>
                        <span class="selo selo-disponivel">Disponível</span>
                    <?php else: ?>
                        <span class="selo selo-indisponivel">Indisponível</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="acoes-tabela">
                        <a href="recursos.php?editar=<?= $r['id'] ?>" class="btn btn-secundario btn-pequeno">Editar</a>
                        <a href="recursos.php?excluir=<?= $r['id'] ?>" class="btn btn-perigo btn-pequeno"
                           onclick="return confirm('Excluir este recurso? Esta ação não pode ser desfeita.');">Excluir</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
