<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Recurso.php';
require_once __DIR__ . '/../classes/Categoria.php';

Auth::exigirLogin('../index.php');

$categoriaModel = new Categoria();
$recursoModel = new Recurso();

$categoriaFiltro = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
$categorias = $categoriaModel->listarTodas();
$recursos = $recursoModel->listarTodos($categoriaFiltro);

$tituloPagina = 'Catálogo';
include __DIR__ . '/_header.php';
?>
<div class="topo-pagina">
    <div>
        <div class="eyebrow">Catálogo</div>
        <h1 class="mt-0">Recursos disponíveis</h1>
        <p>Escolha um item, defina a data e o turno e confirme sua reserva na hora.</p>
    </div>
</div>

<div class="filtros-categoria">
    <a href="catalogo.php" class="<?= !$categoriaFiltro ? 'ativo' : '' ?>">Todos</a>
    <?php foreach ($categorias as $c): ?>
        <a href="catalogo.php?categoria=<?= $c['id'] ?>" class="<?= $categoriaFiltro === (int)$c['id'] ? 'ativo' : '' ?>">
            <?= htmlspecialchars($c['nome']) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($recursos)): ?>
    <div class="vazio">
        <div class="icone-vazio">🗂️</div>
        <p>Nenhum recurso encontrado para este filtro.</p>
    </div>
<?php else: ?>
<div class="grade-recursos">
    <?php foreach ($recursos as $r): ?>
        <div class="cartao-recurso">
            <div class="imagem-wrap">
                <?php if ($r['disponivel']): ?>
                    <span class="selo selo-disponivel">Disponível</span>
                <?php else: ?>
                    <span class="selo selo-indisponivel">Indisponível</span>
                <?php endif; ?>
                <?php if ($r['foto']): ?>
                    <img src="../uploads/<?= htmlspecialchars($r['foto']) ?>" alt="<?= htmlspecialchars($r['nome']) ?>">
                <?php else: ?>
                    <div class="imagem-placeholder"><?= htmlspecialchars(mb_substr($r['nome'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="corpo">
                <div class="categoria-tag"><?= htmlspecialchars($r['categoria_nome'] ?? 'Sem categoria') ?></div>
                <h3><?= htmlspecialchars($r['nome']) ?></h3>
                <p class="descricao-curta"><?= htmlspecialchars(mb_strimwidth($r['descricao'] ?: 'Sem descrição adicional.', 0, 90, '…')) ?></p>
                <div class="rodape-cartao">
                    <a href="reservar.php?id=<?= $r['id'] ?>" class="btn btn-acento btn-pequeno">Ver e reservar</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/_footer.php'; ?>
