<?php
/**
 * api_recursos.php
 * Endpoint JSON consumido pelo aplicativo Android.
 * Retorna a lista de recursos cadastrados (com filtro opcional por categoria).
 *
 * Uso:
 *   GET /api/api_recursos.php               -> lista todos os recursos
 *   GET /api/api_recursos.php?categoria=2    -> filtra por categoria
 *   GET /api/api_recursos.php?id=5           -> detalhe de um único recurso
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Recurso.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Permite acesso do app mobile

$recursoModel = new Recurso();

try {
    if (isset($_GET['id'])) {
        $recurso = $recursoModel->buscarPorId((int) $_GET['id']);

        if (!$recurso) {
            http_response_code(404);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Recurso não encontrado.']);
            exit;
        }

        echo json_encode(['sucesso' => true, 'dados' => formatarRecurso($recurso)], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $categoriaId = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
    $recursos = $recursoModel->listarTodos($categoriaId);

    $dados = array_map('formatarRecurso', $recursos);

    echo json_encode(['sucesso' => true, 'total' => count($dados), 'dados' => $dados], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao consultar recursos.']);
}

/**
 * Formata a linha do banco em um array pronto para o app consumir,
 * já com a URL completa da foto.
 */
function formatarRecurso(array $r): array
{
    return [
        'id'            => (int) $r['id'],
        'nome'          => $r['nome'],
        'descricao'     => $r['descricao'],
        'categoria'     => $r['categoria_nome'] ?? null,
        'categoria_id'  => $r['categoria_id'] ? (int) $r['categoria_id'] : null,
        'setor'         => $r['setor_nome'] ?? null,
        'disponivel'    => (bool) $r['disponivel'],
        'foto_url'      => $r['foto'] ? rtrim(APP_URL, '/') . '/uploads/' . $r['foto'] : null,
    ];
}
