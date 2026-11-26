<?php
declare(strict_types=1);

/**
 * Motor principal da partida do jogo Mayab.
 * Toda lógica de estado, validação de escolhas, efeitos e pontuação
 * ocorre exclusivamente no servidor (PHP) via $_SESSION['partida'] e PDO.
 */
require_once __DIR__ . '/includes/protege.php';

$pdo = conectar_bd();

const VIDA_INICIAL      = 10;
const VIDA_MAX          = 20;
const MOEDAS_INICIAIS   = 0;
const CENA_INICIAL_ID   = 1;
const VIDEO_INTRO_PATH  = 'assets/img/video-mayab-final.mp4';

/**
 * Busca os dados de uma cena pelo ID.
 *
 * @return array<string, mixed>|null
 */
function buscar_cena_jogo(PDO $pdo, int $cenaId): ?array
{
    if ($cenaId <= 0) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT id, titulo, texto, imagem, tipo
         FROM cenas
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $cenaId]);
    $cena = $stmt->fetch();

    return ($cena !== false) ? $cena : null;
}

/**
 * Lista todas as escolhas pertencentes a uma cena específica.
 *
 * @return array<int, array<string, mixed>>
 */
function buscar_escolhas_cena(PDO $pdo, int $cenaId): array
{
    $stmt = $pdo->prepare(
        'SELECT id, texto, efeito_vida, efeito_moedas
         FROM escolhas
         WHERE cena_id = :cena_id
         ORDER BY id ASC'
    );
    $stmt->execute([':cena_id' => $cenaId]);
    return $stmt->fetchAll();
}

/**
 * Calcula a pontuação final conforme a especificação do jogo.
 */
function calcular_pontuacao_final(string $tipoCena, int $vida, int $moedas): int
{
    if ($tipoCena === 'final_bom' && $vida > 0) {
        return 500 + ($moedas * 10) + ($vida * 20);
    }

    return max(0, $moedas * 5);
}

/**
 * Garante que o caminho da imagem cadastrado é relativo ao projeto e existe em disco.
 */
function resolver_imagem_cena(?string $caminho): ?string
{
    if ($caminho === null) {
        return null;
    }

    $limpo = ltrim(str_replace('\\', '/', trim($caminho)), '/');
    if (
        $limpo === ''
        || str_contains($limpo, '..')
        || str_contains($limpo, '://')
        || str_starts_with($limpo, '//')
    ) {
        return null;
    }

    $absoluto = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $limpo);
    return is_file($absoluto) ? $limpo : null;
}

/**
 * Formata variação numérica de vida ou moedas.
 */
function formatar_variacao(int $valor, string $unidade): string
{
    $sinal = ($valor > 0) ? "+{$valor}" : (string) $valor;
    return "{$sinal} {$unidade}";
}

$usuarioId = (int) $_SESSION['usuario_id'];

// Isola partidas por usuário autenticado na sessão
if (isset($_SESSION['partida']) && (int) ($_SESSION['partida']['usuario_id'] ?? 0) !== $usuarioId) {
    unset($_SESSION['partida']);
}


// PROCESSAMENTO DE AÇÕES VIA POST (PRG + CSRF)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verificar_csrf($_POST['csrf_token'] ?? null)) {
        $_SESSION['jogo_erro'] = 'Sessão do formulário expirada. Tente novamente.';
        header('Location: jogo.php');
        exit;
    }

    $acao = (string) ($_POST['acao'] ?? '');

    if ($acao === 'iniciar') {
        $cenaInicial = buscar_cena_jogo($pdo, CENA_INICIAL_ID);
        if ($cenaInicial === null) {
            $_SESSION['jogo_erro'] = 'A cena inicial (#1) não foi encontrada no banco de dados.';
            header('Location: jogo.php');
            exit;
        }

        $_SESSION['partida'] = [
            'usuario_id'   => $usuarioId,
            'cena_id'      => (int) $cenaInicial['id'],
            'vida'         => VIDA_INICIAL,
            'moedas'       => MOEDAS_INICIAIS,
            'pontos'       => 0,
            'finalizada'   => false,
            'salva_bd'     => false,
            'motivo_fim'   => '',
            'ultimo_delta' => null,
            'passos'       => 0,
        ];
        $_SESSION['mostrar_intro_video'] = true;

        header('Location: jogo.php');
        exit;
    }

    if ($acao === 'reiniciar') {
        unset($_SESSION['partida'], $_SESSION['mostrar_intro_video']);
        header('Location: jogo.php');
        exit;
    }

    if ($acao === 'escolher') {
        $partida = $_SESSION['partida'] ?? null;

        if (!is_array($partida) || (int) ($partida['usuario_id'] ?? 0) !== $usuarioId) {
            $_SESSION['jogo_erro'] = 'Nenhuma partida ativa encontrada. Inicie uma nova expedição.';
            header('Location: jogo.php');
            exit;
        }

        if (!empty($partida['finalizada'])) {
            $_SESSION['jogo_erro'] = 'Esta partida já foi finalizada.';
            header('Location: jogo.php');
            exit;
        }

        $escolhaId = filter_var($_POST['escolha_id'] ?? null, FILTER_VALIDATE_INT);
        $cenaAtual = (int) ($partida['cena_id'] ?? 0);

        if ($escolhaId === false || $escolhaId <= 0 || $cenaAtual <= 0) {
            $_SESSION['jogo_erro'] = 'Escolha inválida.';
            header('Location: jogo.php');
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Validação anti-trapaça: a escolha precisa pertencer à cena atual e ter destino válido
            $stmtEscolha = $pdo->prepare(
                'SELECT e.id, e.cena_id, e.texto, e.destino_id, e.efeito_vida, e.efeito_moedas,
                        c.tipo AS destino_tipo
                 FROM escolhas e
                 INNER JOIN cenas c ON c.id = e.destino_id
                 WHERE e.id = :escolha_id AND e.cena_id = :cena_id
                 LIMIT 1'
            );
            $stmtEscolha->execute([
                ':escolha_id' => $escolhaId,
                ':cena_id'    => $cenaAtual,
            ]);
            $escolha = $stmtEscolha->fetch();

            if ($escolha === false) {
                $pdo->rollBack();
                $_SESSION['jogo_erro'] = 'A escolha selecionada não pertence à cena atual.';
                header('Location: jogo.php');
                exit;
            }

            $vidaAnterior   = (int) $partida['vida'];
            $moedasAnterior = (int) $partida['moedas'];
            $vidaNova       = max(0, min(VIDA_MAX, $vidaAnterior + (int) $escolha['efeito_vida']));
            $moedasNova     = max(0, $moedasAnterior + (int) $escolha['efeito_moedas']);
            $destinoId      = (int) $escolha['destino_id'];
            $destinoTipo    = (string) $escolha['destino_tipo'];

            $vidaZerada  = ($vidaNova <= 0);
            $ehCenaFinal = ($destinoTipo !== 'normal');
            $finalizada  = ($vidaZerada || $ehCenaFinal);
            $motivoFim   = $vidaZerada ? 'derrota_vida' : ($ehCenaFinal ? $destinoTipo : '');
            $pontos      = $finalizada ? calcular_pontuacao_final($destinoTipo, $vidaNova, $moedasNova) : 0;
            $salvaBd     = !empty($partida['salva_bd']);

            if ($finalizada && !$salvaBd) {
                $stmtPartida = $pdo->prepare(
                    'INSERT INTO partidas (usuario_id, cena_final_id, vida_final, moedas_final, pontos)
                     VALUES (:usuario_id, :cena_final_id, :vida_final, :moedas_final, :pontos)'
                );
                $stmtPartida->execute([
                    ':usuario_id'    => $usuarioId,
                    ':cena_final_id' => $destinoId,
                    ':vida_final'    => $vidaNova,
                    ':moedas_final'  => $moedasNova,
                    ':pontos'        => $pontos,
                ]);
                $salvaBd = true;
            }

            $pdo->commit();

            $_SESSION['partida'] = [
                'usuario_id'   => $usuarioId,
                'cena_id'      => $destinoId,
                'vida'         => $vidaNova,
                'moedas'       => $moedasNova,
                'pontos'       => $pontos,
                'finalizada'   => $finalizada,
                'salva_bd'     => $salvaBd,
                'motivo_fim'   => $motivoFim,
                'ultimo_delta' => [
                    'texto'  => (string) $escolha['texto'],
                    'vida'   => $vidaNova - $vidaAnterior,
                    'moedas' => $moedasNova - $moedasAnterior,
                ],
                'passos'       => (int) ($partida['passos'] ?? 0) + 1,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Erro ao processar escolha em jogo.php: ' . $e->getMessage());
            $_SESSION['jogo_erro'] = 'Não foi possível processar sua escolha. Tente novamente.';
        }

        header('Location: jogo.php');
        exit;
    }

    $_SESSION['jogo_erro'] = 'Ação inválida.';
    header('Location: jogo.php');
    exit;
}


// CARREGAMENTO DO ESTADO ATUAL PARA EXIBIÇÃO (GET)

$mensagemErro = (string) ($_SESSION['jogo_erro'] ?? '');
unset($_SESSION['jogo_erro']);

$partida           = $_SESSION['partida'] ?? null;
$cenaAtual         = null;
$escolhas          = [];
$imagemCena        = null;
$videoIntro        = null;
$mostrarIntroVideo = false;

$introSolicitada = !empty($_SESSION['mostrar_intro_video']);
unset($_SESSION['mostrar_intro_video']);

if (is_array($partida)) {
    $cenaAtual = buscar_cena_jogo($pdo, (int) ($partida['cena_id'] ?? 0));
    if ($cenaAtual === null) {
        unset($_SESSION['partida']);
        $partida = null;
        $mensagemErro = 'A cena atual não foi encontrada no banco de dados. Inicie uma nova expedição.';
    } else {
        $imagemCena = resolver_imagem_cena($cenaAtual['imagem'] ?? null);
        if (empty($partida['finalizada'])) {
            $escolhas = buscar_escolhas_cena($pdo, (int) $cenaAtual['id']);
        }
        if ($introSolicitada && empty($partida['finalizada']) && (int) ($partida['passos'] ?? 0) === 0) {
            $videoIntro = resolver_imagem_cena(VIDEO_INTRO_PATH);
            $mostrarIntroVideo = ($videoIntro !== null);
        }
    }
}

$tokenCsrf       = csrf_token();
$titulo          = 'Expedição - Mayab';
$cssPagina       = 'assets/css/pages/jogo.css';
$introVideoAtiva = $mostrarIntroVideo;
require_once __DIR__ . '/includes/topo.php';
?>

<?php if ($mostrarIntroVideo && $videoIntro !== null): ?>
    <div class="intro-cinema" id="intro-cinema" role="dialog" aria-modal="true" aria-label="Introdução cinematográfica de Mayab">
        <video
            class="intro-cinema__video"
            id="intro-video"
            preload="auto"
            playsinline
            autoplay
        >
            <source src="<?= h($videoIntro) ?>" type="video/mp4">
        </video>
        <button
            class="intro-cinema__skip"
            id="intro-skip"
            type="button"
            aria-label="Pular introdução e ir para o jogo"
        >Pular vídeo</button>
    </div>
<?php endif; ?>

<section class="game-wrap" aria-label="Área do jogo Mayab">
    <?php if ($mensagemErro !== ''): ?>
        <div class="erro" role="alert"><?= h($mensagemErro) ?></div>
    <?php endif; ?>

    <?php if (!is_array($partida) || $cenaAtual === null): ?>
        <div class="card intro-panel">
            <div class="scene-header">
                <h1>Expedição ao Templo de Mayab</h1>
                <span class="badge badge--gold">Introdução</span>
            </div>

            <p>
                Você está prestes a cruzar o portal temporal rumo às ruínas maias de Yucatán.
                Cada escolha altera seu destino, sua vitalidade e os tesouros acumulados até o desfecho final.
            </p>

            <div class="intro-panel__rules">
                <div class="intro-stat">
                    <strong><?= VIDA_INICIAL ?> Pontos de Vida</strong>
                    <span>Se sua vida chegar a 0, a expedição termina imediatamente.</span>
                </div>
                <div class="intro-stat">
                    <strong><?= MOEDAS_INICIAIS ?> Moedas Iniciais</strong>
                    <span>Relíquias e decisões estratégicas aumentam sua pontuação final.</span>
                </div>
                <div class="intro-stat">
                    <strong>3 Finais Possíveis</strong>
                    <span>Alcance um final bom sobrevivendo para conquistar o bônus máximo.</span>
                </div>
            </div>

            <form action="jogo.php" method="POST" class="js-single-submit">
                <input type="hidden" name="csrf_token" value="<?= h($tokenCsrf) ?>">
                <input type="hidden" name="acao" value="iniciar">
                <button type="submit" class="btn btn--glow">Iniciar Expedição</button>
            </form>
        </div>
    <?php else: ?>
        <?php
        $vidaAtual   = (int) $partida['vida'];
        $moedasAtual = (int) $partida['moedas'];
        $passosAtual = (int) ($partida['passos'] ?? 0);
        $finalizada  = !empty($partida['finalizada']);
        $motivoFim   = (string) ($partida['motivo_fim'] ?? '');
        ?>

        <div class="card hud" aria-label="Status do jogador">
            <div class="hud__stats">
                <span class="hud__item hud__item--life">Vida: <strong><?= $vidaAtual ?> / <?= VIDA_MAX ?></strong></span>
                <span class="hud__item hud__item--coins">Moedas: <strong><?= $moedasAtual ?></strong></span>
                <span class="hud__item hud__item--steps">Turnos: <strong><?= $passosAtual ?></strong></span>
            </div>

            <form action="jogo.php" method="POST" class="js-single-submit">
                <input type="hidden" name="csrf_token" value="<?= h($tokenCsrf) ?>">
                <input type="hidden" name="acao" value="reiniciar">
                <button type="submit" class="btn btn--secondary">Reiniciar</button>
            </form>
        </div>

        <?php if (!empty($partida['ultimo_delta']) && is_array($partida['ultimo_delta'])): ?>
            <?php
            $delta       = $partida['ultimo_delta'];
            $deltaVida   = (int) ($delta['vida'] ?? 0);
            $deltaMoedas = (int) ($delta['moedas'] ?? 0);
            ?>
            <div class="scene-delta" aria-live="polite">
                <span>Última decisão: <strong><?= h((string) ($delta['texto'] ?? '')) ?></strong></span>
                <div class="scene-delta__tags">
                    <span class="badge <?= $deltaVida >= 0 ? 'badge-final_bom' : 'badge-final_ruim' ?>">
                        <?= h(formatar_variacao($deltaVida, 'Vida')) ?>
                    </span>
                    <span class="badge <?= $deltaMoedas >= 0 ? 'badge--gold' : 'badge-final_ruim' ?>">
                        <?= h(formatar_variacao($deltaMoedas, 'Moedas')) ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <article class="card scene-card">
            <?php if ($imagemCena !== null): ?>
                <div class="scene-media">
                    <img class="scene-img" src="<?= h($imagemCena) ?>" alt="<?= h($cenaAtual['titulo']) ?>">
                </div>
            <?php endif; ?>

            <div class="scene-header">
                <h1><?= h($cenaAtual['titulo']) ?></h1>
                <?php if ($finalizada): ?>
                    <?php if ($motivoFim === 'final_bom'): ?>
                        <span class="badge badge-final_bom">Final Bom</span>
                    <?php elseif ($motivoFim === 'derrota_vida'): ?>
                        <span class="badge badge-final_ruim">Vida Esgotada</span>
                    <?php else: ?>
                        <span class="badge badge-final_ruim">Final Ruim</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="scene-narrative">
                <p><?= nl2br(h($cenaAtual['texto'])) ?></p>
            </div>

            <?php if (!$finalizada): ?>
                <div class="choices-section">
                    <h2 class="choices-title">O que você decide fazer?</h2>
                    <?php if (empty($escolhas)): ?>
                        <div class="erro">
                            Nenhuma escolha foi cadastrada para esta cena. Reinicie a partida ou contate o administrador.
                        </div>
                    <?php else: ?>
                        <div class="choices-list">
                            <?php foreach ($escolhas as $escolha): ?>
                                <form action="jogo.php" method="POST" class="choice-form js-single-submit">
                                    <input type="hidden" name="csrf_token" value="<?= h($tokenCsrf) ?>">
                                    <input type="hidden" name="acao" value="escolher">
                                    <input type="hidden" name="escolha_id" value="<?= (int) $escolha['id'] ?>">
                                    <button type="submit" class="btn choice-btn">
                                        <span><?= h($escolha['texto']) ?></span>
                                        <span class="choice-btn__arrow" aria-hidden="true">&rarr;</span>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php $vitoria = ($motivoFim === 'final_bom'); ?>
                <div class="result-panel <?= $vitoria ? 'result-panel--win' : 'result-panel--loss' ?>">
                    <h2><?= $vitoria ? 'Expedição Concluída com Glória!' : 'Fim da Expedição' ?></h2>
                    <p>
                        <?php if ($motivoFim === 'derrota_vida'): ?>
                            Sua vitalidade chegou a zero antes que você pudesse escapar dos perigos do templo.
                        <?php elseif ($vitoria): ?>
                            Você superou as provações de Mayab com <?= $vidaAtual ?> de vida e <?= $moedasAtual ?> moedas.
                        <?php else: ?>
                            Sua jornada terminou em um desfecho trágico nas profundezas de Mayab.
                        <?php endif; ?>
                    </p>
                    <p class="result-panel__score">Pontuação Final: <?= (int) $partida['pontos'] ?> pts</p>

                    <div class="result-panel__actions">
                        <form action="jogo.php" method="POST" class="js-single-submit">
                            <input type="hidden" name="csrf_token" value="<?= h($tokenCsrf) ?>">
                            <input type="hidden" name="acao" value="iniciar">
                            <button type="submit" class="btn btn--glow">Jogar Novamente</button>
                        </form>
                        <a href="ranking.php" class="btn btn--secondary">Ver Ranking</a>
                    </div>
                </div>
            <?php endif; ?>
        </article>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/includes/rodape.php';
