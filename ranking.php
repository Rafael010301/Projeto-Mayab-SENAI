<?php
declare(strict_types=1);

/**
 * Classificação global (Top 10) e histórico recente do jogador autenticado.
 */
require_once __DIR__ . '/includes/conexao.php';

$pdo = conectar_bd();

$stmtRanking = $pdo->prepare(
    'SELECT p.id, p.pontos, p.vida_final, p.moedas_final, p.jogada_em,
            u.apelido, c.titulo AS cena_final, c.tipo AS tipo_final
     FROM partidas p
     INNER JOIN usuarios u ON u.id = p.usuario_id
     INNER JOIN cenas c ON c.id = p.cena_final_id
     ORDER BY p.pontos DESC, p.vida_final DESC, p.moedas_final DESC, p.jogada_em ASC
     LIMIT 10'
);
$stmtRanking->execute();
$topPartidas = $stmtRanking->fetchAll();

$historicoPessoal = [];
if (!empty($_SESSION['usuario_id'])) {
    $stmtPessoal = $pdo->prepare(
        'SELECT p.id, p.pontos, p.vida_final, p.moedas_final, p.jogada_em,
                c.titulo AS cena_final, c.tipo AS tipo_final
         FROM partidas p
         INNER JOIN cenas c ON c.id = p.cena_final_id
         WHERE p.usuario_id = :usuario_id
         ORDER BY p.jogada_em DESC, p.id DESC
         LIMIT 5'
    );
    $stmtPessoal->execute([':usuario_id' => (int) $_SESSION['usuario_id']]);
    $historicoPessoal = $stmtPessoal->fetchAll();
}

$rotulosFinal = [
    'normal'     => 'Derrota',
    'final_bom'  => 'Final Bom',
    'final_ruim' => 'Final Ruim',
];

/**
 * Formata data/hora da partida evitando TypeError em modo estrito.
 */
function formatar_data_partida(?string $data): string
{
    if ($data === null || trim($data) === '') {
        return '-';
    }

    $timestamp = strtotime($data);
    return ($timestamp !== false) ? date('d/m/Y H:i', $timestamp) : '-';
}

$titulo    = 'Ranking - Mayab';
$cssPagina = 'assets/css/pages/jogo.css';
require_once __DIR__ . '/includes/topo.php';
?>

<section class="game-wrap" aria-label="Classificação e histórico de partidas">
    <div class="ranking-head">
        <h1>Ranking dos Exploradores</h1>
        <?php if (!empty($_SESSION['usuario_id'])): ?>
            <a href="jogo.php" class="btn">Jogar Agora</a>
        <?php else: ?>
            <a href="login.php" class="btn">Entrar para Jogar</a>
        <?php endif; ?>
    </div>

    <section class="card" aria-labelledby="t-top10">
        <h2 id="t-top10">Top 10 Melhores Pontuações</h2>
        <div class="tabela-responsiva">
            <table class="tabela-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Jogador</th>
                        <th>Desfecho</th>
                        <th>Vida</th>
                        <th>Moedas</th>
                        <th>Pontos</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topPartidas)): ?>
                        <tr>
                            <td colspan="7">Nenhuma partida registrada até o momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topPartidas as $indice => $partida): ?>
                            <?php
                            $tipoFinal = (string) $partida['tipo_final'];
                            $classeBadge = ($tipoFinal === 'final_bom' && (int) $partida['vida_final'] > 0)
                                ? 'badge-final_bom'
                                : 'badge-final_ruim';
                            $rotulo = ((int) $partida['vida_final'] <= 0)
                                ? 'Vida Esgotada'
                                : ($rotulosFinal[$tipoFinal] ?? $tipoFinal);
                            ?>
                            <tr>
                                <td class="ranking-pos">#<?= $indice + 1 ?></td>
                                <td><strong><?= h($partida['apelido']) ?></strong></td>
                                <td>
                                    <span class="badge <?= h($classeBadge) ?>"><?= h($rotulo) ?></span>
                                    <div><?= h($partida['cena_final']) ?></div>
                                </td>
                                <td><?= (int) $partida['vida_final'] ?></td>
                                <td><?= (int) $partida['moedas_final'] ?></td>
                                <td><strong><?= (int) $partida['pontos'] ?></strong></td>
                                <td><?= h(formatar_data_partida($partida['jogada_em'] ?? null)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if (!empty($_SESSION['usuario_id'])): ?>
        <section class="card" aria-labelledby="t-historico">
            <h2 id="t-historico">Suas Últimas 5 Partidas</h2>
            <div class="tabela-responsiva">
                <table class="tabela-admin">
                    <thead>
                        <tr>
                            <th>Cena Final</th>
                            <th>Desfecho</th>
                            <th>Vida</th>
                            <th>Moedas</th>
                            <th>Pontos</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historicoPessoal)): ?>
                            <tr>
                                <td colspan="6">Você ainda não concluiu nenhuma partida.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historicoPessoal as $item): ?>
                                <?php
                                $tipoFinal = (string) $item['tipo_final'];
                                $classeBadge = ($tipoFinal === 'final_bom' && (int) $item['vida_final'] > 0)
                                    ? 'badge-final_bom'
                                    : 'badge-final_ruim';
                                $rotulo = ((int) $item['vida_final'] <= 0)
                                    ? 'Vida Esgotada'
                                    : ($rotulosFinal[$tipoFinal] ?? $tipoFinal);
                                ?>
                                <tr>
                                    <td><strong><?= h($item['cena_final']) ?></strong></td>
                                    <td><span class="badge <?= h($classeBadge) ?>"><?= h($rotulo) ?></span></td>
                                    <td><?= (int) $item['vida_final'] ?></td>
                                    <td><?= (int) $item['moedas_final'] ?></td>
                                    <td><strong><?= (int) $item['pontos'] ?></strong></td>
                                    <td><?= h(formatar_data_partida($item['jogada_em'] ?? null)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/includes/rodape.php';
