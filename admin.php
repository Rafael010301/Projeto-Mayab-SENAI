<?php
declare(strict_types=1);

/**
 * Painel administrativo para gerenciamento (CRUD) de cenas e escolhas do jogo.
 * Acesso restrito a usuários autenticados com perfil `admin`.
 */
require_once __DIR__ . '/includes/protege.php';
exige_admin();

$pdo = conectar_bd();

const TIPOS_CENA = [
    'normal'     => 'Normal',
    'final_bom'  => 'Final Bom',
    'final_ruim' => 'Final Ruim',
];

/**
 * Valida e normaliza os dados enviados no formulário de cena.
 *
 * @param array<string, mixed> $entrada
 * @return array{dados: array{titulo: string, texto: string, imagem: ?string, tipo: string}, erro: ?string}
 */
function validar_dados_cena(array $entrada): array
{
    $titulo    = trim((string) ($entrada['titulo'] ?? ''));
    $texto     = trim((string) ($entrada['texto'] ?? ''));
    $imagemRaw = trim((string) ($entrada['imagem'] ?? ''));
    $tipo      = (string) ($entrada['tipo'] ?? 'normal');
    $imagem    = ($imagemRaw === '') ? null : $imagemRaw;

    $erro = null;
    if ($titulo === '' || $texto === '') {
        $erro = 'Título e texto da cena são obrigatórios.';
    } elseif (mb_strlen($titulo) > 150) {
        $erro = 'O título da cena deve ter no máximo 150 caracteres.';
    } elseif ($imagem !== null && mb_strlen($imagem) > 255) {
        $erro = 'O caminho da imagem deve ter no máximo 255 caracteres.';
    } elseif (!array_key_exists($tipo, TIPOS_CENA)) {
        $erro = 'Tipo de cena inválido.';
    }

    return [
        'dados' => [
            'titulo' => $titulo,
            'texto'  => $texto,
            'imagem' => $imagem,
            'tipo'   => $tipo,
        ],
        'erro' => $erro,
    ];
}

/**
 * Verifica em uma única consulta se os IDs de cena origem e destino existem no banco.
 */
function cenas_existem(PDO $pdo, int $origemId, int $destinoId): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM cenas
         WHERE id IN (:origem_id, :destino_id)'
    );
    $stmt->execute([
        ':origem_id'  => $origemId,
        ':destino_id' => $destinoId,
    ]);

    // Se origem e destino apontarem para a mesma cena, espera-se 1 registro; caso contrário, 2
    $esperado = ($origemId === $destinoId) ? 1 : 2;
    return (int) $stmt->fetchColumn() === $esperado;
}

/**
 * Valida e normaliza os dados enviados no formulário de escolha.
 *
 * @param array<string, mixed> $entrada
 * @return array{
 *     dados: array{cena_id: int, texto: string, destino_id: int, efeito_vida: int, efeito_moedas: int},
 *     erro: ?string
 * }
 */
function validar_dados_escolha(PDO $pdo, array $entrada): array
{
    $cenaId       = filter_var($entrada['cena_id'] ?? null, FILTER_VALIDATE_INT);
    $destinoId    = filter_var($entrada['destino_id'] ?? null, FILTER_VALIDATE_INT);
    $efeitoVida   = filter_var(trim((string) ($entrada['efeito_vida'] ?? '0')), FILTER_VALIDATE_INT);
    $efeitoMoedas = filter_var(trim((string) ($entrada['efeito_moedas'] ?? '0')), FILTER_VALIDATE_INT);
    $texto        = trim((string) ($entrada['texto'] ?? ''));

    $erro = null;
    if ($cenaId === false || $cenaId <= 0 || $destinoId === false || $destinoId <= 0 || $texto === '') {
        $erro = 'Cena de origem, cena de destino e texto da escolha são obrigatórios.';
    } elseif (mb_strlen($texto) > 255) {
        $erro = 'O texto da escolha deve ter no máximo 255 caracteres.';
    } elseif ($efeitoVida === false || $efeitoMoedas === false) {
        $erro = 'Os efeitos de vida e moedas devem ser números inteiros válidos.';
    } elseif (!cenas_existem($pdo, $cenaId, $destinoId)) {
        $erro = 'A cena de origem ou a cena de destino informada não existe.';
    }

    return [
        'dados' => [
            'cena_id'       => ($cenaId !== false) ? $cenaId : 0,
            'texto'         => $texto,
            'destino_id'    => ($destinoId !== false) ? $destinoId : 0,
            'efeito_vida'   => ($efeitoVida !== false) ? $efeitoVida : 0,
            'efeito_moedas' => ($efeitoMoedas !== false) ? $efeitoMoedas : 0,
        ],
        'erro' => $erro,
    ];
}

/**
 * Busca uma cena pelo ID com colunas explícitas.
 *
 * @return array<string, mixed>|null
 */
function buscar_cena_por_id(PDO $pdo, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT id, titulo, texto, imagem, tipo
         FROM cenas
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $registro = $stmt->fetch();

    return ($registro !== false) ? $registro : null;
}

/**
 * Busca uma escolha pelo ID com colunas explícitas.
 *
 * @return array<string, mixed>|null
 */
function buscar_escolha_por_id(PDO $pdo, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT id, cena_id, texto, destino_id, efeito_vida, efeito_moedas
         FROM escolhas
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $registro = $stmt->fetch();

    return ($registro !== false) ? $registro : null;
}

/**
 * Formata modificadores numéricos exibindo sinal explícito quando positivos.
 */
function formatar_efeito(int $valor): string
{
    return ($valor > 0) ? "+{$valor}" : (string) $valor;
}

/**
 * Renderiza as opções de `<select>` com todas as cenas disponíveis.
 *
 * @param array<int, array<string, mixed>> $cenas
 */
function renderizar_opcoes_cenas(array $cenas, int $selecionadoId): void
{
    foreach ($cenas as $cena) {
        $idCena   = (int) $cena['id'];
        $selected = ($selecionadoId === $idCena) ? ' selected' : '';
        $rotulo   = TIPOS_CENA[$cena['tipo']] ?? (string) $cena['tipo'];
        echo '<option value="' . $idCena . '"' . $selected . '>'
            . '#' . $idCena . ' - ' . h($cena['titulo']) . ' (' . h($rotulo) . ')'
            . '</option>' . "\n";
    }
}

$mensagemErro    = '';
$mensagemSucesso = match ($_GET['sucesso'] ?? '') {
    'cena_criada'        => 'Cena cadastrada com sucesso!',
    'cena_atualizada'    => 'Cena atualizada com sucesso!',
    'cena_excluida'      => 'Cena excluída com sucesso!',
    'escolha_criada'     => 'Escolha cadastrada com sucesso!',
    'escolha_atualizada' => 'Escolha atualizada com sucesso!',
    'escolha_excluida'   => 'Escolha excluída com sucesso!',
    default              => '',
};

$formCena    = null;
$formEscolha = null;

// -------------------------------------------------------------
// PROCESSAMENTO DE AÇÕES MUTÁVEIS VIA POST (PADRÃO PRG)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = (string) ($_POST['acao'] ?? '');

    if ($acao === 'cadastrar_cena') {
        $validacao = validar_dados_cena($_POST);
        if ($validacao['erro'] !== null) {
            $mensagemErro = $validacao['erro'];
            $formCena     = $validacao['dados'];
        } else {
            $dados = $validacao['dados'];
            $stmt  = $pdo->prepare(
                'INSERT INTO cenas (titulo, texto, imagem, tipo)
                 VALUES (:titulo, :texto, :imagem, :tipo)'
            );
            $stmt->execute([
                ':titulo' => $dados['titulo'],
                ':texto'  => $dados['texto'],
                ':imagem' => $dados['imagem'],
                ':tipo'   => $dados['tipo'],
            ]);

            header('Location: admin.php?sucesso=cena_criada');
            exit;
        }
    } elseif ($acao === 'editar_cena') {
        $id        = (int) ($_POST['id'] ?? 0);
        $validacao = validar_dados_cena($_POST);

        if ($id <= 0 || buscar_cena_por_id($pdo, $id) === null) {
            $mensagemErro = 'A cena informada para edição não foi encontrada.';
        } elseif ($validacao['erro'] !== null) {
            $mensagemErro = $validacao['erro'];
            $formCena     = ['id' => $id] + $validacao['dados'];
        } else {
            $dados = $validacao['dados'];
            $stmt  = $pdo->prepare(
                'UPDATE cenas
                 SET titulo = :titulo, texto = :texto, imagem = :imagem, tipo = :tipo
                 WHERE id = :id'
            );
            $stmt->execute([
                ':titulo' => $dados['titulo'],
                ':texto'  => $dados['texto'],
                ':imagem' => $dados['imagem'],
                ':tipo'   => $dados['tipo'],
                ':id'     => $id,
            ]);

            header('Location: admin.php?sucesso=cena_atualizada');
            exit;
        }
    } elseif ($acao === 'excluir_cena') {
        // Exclusão restrita a POST para evitar acionamento acidental ou via links GET
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $mensagemErro = 'ID de cena inválido para exclusão.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM cenas WHERE id = :id');
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                header('Location: admin.php?sucesso=cena_excluida');
                exit;
            }
            $mensagemErro = 'Cena não encontrada para exclusão.';
        }
    } elseif ($acao === 'cadastrar_escolha') {
        $validacao = validar_dados_escolha($pdo, $_POST);
        if ($validacao['erro'] !== null) {
            $mensagemErro = $validacao['erro'];
            $formEscolha  = $validacao['dados'];
        } else {
            $dados = $validacao['dados'];
            $stmt  = $pdo->prepare(
                'INSERT INTO escolhas (cena_id, texto, destino_id, efeito_vida, efeito_moedas)
                 VALUES (:cena_id, :texto, :destino_id, :efeito_vida, :efeito_moedas)'
            );
            $stmt->execute([
                ':cena_id'       => $dados['cena_id'],
                ':texto'         => $dados['texto'],
                ':destino_id'    => $dados['destino_id'],
                ':efeito_vida'   => $dados['efeito_vida'],
                ':efeito_moedas' => $dados['efeito_moedas'],
            ]);

            header('Location: admin.php?sucesso=escolha_criada');
            exit;
        }
    } elseif ($acao === 'editar_escolha') {
        $id        = (int) ($_POST['id'] ?? 0);
        $validacao = validar_dados_escolha($pdo, $_POST);

        if ($id <= 0 || buscar_escolha_por_id($pdo, $id) === null) {
            $mensagemErro = 'A escolha informada para edição não foi encontrada.';
        } elseif ($validacao['erro'] !== null) {
            $mensagemErro = $validacao['erro'];
            $formEscolha  = ['id' => $id] + $validacao['dados'];
        } else {
            $dados = $validacao['dados'];
            $stmt  = $pdo->prepare(
                'UPDATE escolhas
                 SET cena_id = :cena_id,
                     texto = :texto,
                     destino_id = :destino_id,
                     efeito_vida = :efeito_vida,
                     efeito_moedas = :efeito_moedas
                 WHERE id = :id'
            );
            $stmt->execute([
                ':cena_id'       => $dados['cena_id'],
                ':texto'         => $dados['texto'],
                ':destino_id'    => $dados['destino_id'],
                ':efeito_vida'   => $dados['efeito_vida'],
                ':efeito_moedas' => $dados['efeito_moedas'],
                ':id'            => $id,
            ]);

            header('Location: admin.php?sucesso=escolha_atualizada');
            exit;
        }
    } elseif ($acao === 'excluir_escolha') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $mensagemErro = 'ID de escolha inválido para exclusão.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM escolhas WHERE id = :id');
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                header('Location: admin.php?sucesso=escolha_excluida');
                exit;
            }
            $mensagemErro = 'Escolha não encontrada para exclusão.';
        }
    } else {
        $mensagemErro = 'Ação administrativa desconhecida.';
    }
}

// -------------------------------------------------------------
// CARREGAMENTO PARA EDIÇÃO VIA GET (?editar_cena=ID / ?editar_escolha=ID)
// -------------------------------------------------------------
$cenaEdicao = $formCena;
if ($cenaEdicao === null && isset($_GET['editar_cena'])) {
    $idEditCena = (int) $_GET['editar_cena'];
    $cenaEdicao = buscar_cena_por_id($pdo, $idEditCena);
    if ($cenaEdicao === null && $mensagemErro === '') {
        $mensagemErro = 'A cena solicitada para edição não existe.';
    }
}

$escolhaEdicao = $formEscolha;
if ($escolhaEdicao === null && isset($_GET['editar_escolha'])) {
    $idEditEscolha = (int) $_GET['editar_escolha'];
    $escolhaEdicao = buscar_escolha_por_id($pdo, $idEditEscolha);
    if ($escolhaEdicao === null && $mensagemErro === '') {
        $mensagemErro = 'A escolha solicitada para edição não existe.';
    }
}

$modoEdicaoCena    = $cenaEdicao !== null && isset($cenaEdicao['id']);
$modoEdicaoEscolha = $escolhaEdicao !== null && isset($escolhaEdicao['id']);

// -------------------------------------------------------------
// LISTAGEM DE CENAS E ESCOLHAS (PREPARED STATEMENTS COM COLUNAS EXPLÍCITAS)
// -------------------------------------------------------------
$stmtCenas = $pdo->prepare(
    'SELECT id, titulo, texto, imagem, tipo
     FROM cenas
     ORDER BY id ASC'
);
$stmtCenas->execute();
$todasCenas = $stmtCenas->fetchAll();

$stmtEscolhas = $pdo->prepare(
    'SELECT e.id,
            e.cena_id,
            e.texto,
            e.destino_id,
            e.efeito_vida,
            e.efeito_moedas,
            c_origem.titulo AS origem_titulo,
            c_dest.titulo   AS destino_titulo
     FROM escolhas e
     INNER JOIN cenas c_origem ON e.cena_id = c_origem.id
     INNER JOIN cenas c_dest   ON e.destino_id = c_dest.id
     ORDER BY e.cena_id ASC, e.id ASC'
);
$stmtEscolhas->execute();
$todasEscolhas = $stmtEscolhas->fetchAll();

$titulo = 'Painel Administrativo - Mayab';
require_once __DIR__ . '/includes/topo.php';
?>

<div class="admin-container">
    <h2>Painel Administrativo - Mayab</h2>
    <p>Gerenciamento completo das cenas e escolhas do jogo.</p>

    <?php if ($mensagemSucesso !== ''): ?>
        <div class="ok"><?= h($mensagemSucesso) ?></div>
    <?php endif; ?>

    <?php if ($mensagemErro !== ''): ?>
        <div class="erro"><?= h($mensagemErro) ?></div>
    <?php endif; ?>

    <!-- ========================================================= -->
    <!-- SEÇÃO DE CENAS -->
    <!-- ========================================================= -->
    <section class="admin-secao">
        <h3><?= $modoEdicaoCena ? 'Editar Cena #' . h((int) $cenaEdicao['id']) : 'Cadastrar Nova Cena' ?></h3>

        <form action="admin.php" method="POST" class="form-admin">
            <input type="hidden" name="acao" value="<?= $modoEdicaoCena ? 'editar_cena' : 'cadastrar_cena' ?>">
            <?php if ($modoEdicaoCena): ?>
                <input type="hidden" name="id" value="<?= (int) $cenaEdicao['id'] ?>">
            <?php endif; ?>

            <div class="campo">
                <label for="titulo">Título da Cena:</label>
                <input type="text" id="titulo" name="titulo" value="<?= h($cenaEdicao['titulo'] ?? '') ?>" required maxlength="150">
            </div>

            <div class="campo">
                <label for="texto">Narrativa / Texto:</label>
                <textarea id="texto" name="texto" rows="4" required><?= h($cenaEdicao['texto'] ?? '') ?></textarea>
            </div>

            <div class="campo">
                <label for="imagem">Caminho da Imagem (opcional):</label>
                <input type="text" id="imagem" name="imagem" value="<?= h($cenaEdicao['imagem'] ?? '') ?>" placeholder="ex: img/cena1.jpg" maxlength="255">
            </div>

            <div class="campo">
                <label for="tipo">Tipo da Cena:</label>
                <select id="tipo" name="tipo" required>
                    <?php
                    $tipoAtual = (string) ($cenaEdicao['tipo'] ?? 'normal');
                    foreach (TIPOS_CENA as $valor => $rotulo):
                    ?>
                        <option value="<?= h($valor) ?>"<?= ($tipoAtual === $valor) ? ' selected' : '' ?>><?= h($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="botoes-form">
                <button type="submit" class="btn"><?= $modoEdicaoCena ? 'Salvar Alterações' : 'Cadastrar Cena' ?></button>
                <?php if ($modoEdicaoCena): ?>
                    <a href="admin.php" class="btn btn-secundario">Cancelar Edição</a>
                <?php endif; ?>
            </div>
        </form>

        <h4>Cenas Cadastradas (<?= count($todasCenas) ?>)</h4>
        <div class="tabela-responsiva">
            <table class="tabela-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Imagem</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($todasCenas)): ?>
                        <tr><td colspan="5">Nenhuma cena cadastrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($todasCenas as $cena): ?>
                            <?php $rotuloTipo = TIPOS_CENA[$cena['tipo']] ?? (string) $cena['tipo']; ?>
                            <tr>
                                <td><?= (int) $cena['id'] ?></td>
                                <td><strong><?= h($cena['titulo']) ?></strong></td>
                                <td><span class="badge badge-<?= h($cena['tipo']) ?>"><?= h($rotuloTipo) ?></span></td>
                                <td><?= h($cena['imagem'] ?? 'Nenhuma') ?></td>
                                <td>
                                    <a href="admin.php?editar_cena=<?= (int) $cena['id'] ?>" class="btn-acao">Editar</a>
                                    <form action="admin.php" method="POST" class="form-inline" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta cena? Todas as escolhas associadas serão excluídas.');">
                                        <input type="hidden" name="acao" value="excluir_cena">
                                        <input type="hidden" name="id" value="<?= (int) $cena['id'] ?>">
                                        <button type="submit" class="btn-acao btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ========================================================= -->
    <!-- SEÇÃO DE ESCOLHAS -->
    <!-- ========================================================= -->
    <section class="admin-secao">
        <h3><?= $modoEdicaoEscolha ? 'Editar Escolha #' . h((int) $escolhaEdicao['id']) : 'Cadastrar Nova Escolha' ?></h3>

        <form action="admin.php" method="POST" class="form-admin">
            <input type="hidden" name="acao" value="<?= $modoEdicaoEscolha ? 'editar_escolha' : 'cadastrar_escolha' ?>">
            <?php if ($modoEdicaoEscolha): ?>
                <input type="hidden" name="id" value="<?= (int) $escolhaEdicao['id'] ?>">
            <?php endif; ?>

            <div class="campo">
                <label for="cena_id">Cena de Origem:</label>
                <select id="cena_id" name="cena_id" required>
                    <option value="">Selecione a cena de origem</option>
                    <?php renderizar_opcoes_cenas($todasCenas, (int) ($escolhaEdicao['cena_id'] ?? 0)); ?>
                </select>
            </div>

            <div class="campo">
                <label for="texto_escolha">Texto da Escolha (Ação do Jogador):</label>
                <input type="text" id="texto_escolha" name="texto" value="<?= h($escolhaEdicao['texto'] ?? '') ?>" required maxlength="255">
            </div>

            <div class="campo">
                <label for="destino_id">Cena de Destino:</label>
                <select id="destino_id" name="destino_id" required>
                    <option value="">Selecione a cena de destino</option>
                    <?php renderizar_opcoes_cenas($todasCenas, (int) ($escolhaEdicao['destino_id'] ?? 0)); ?>
                </select>
            </div>

            <div class="campo-grupo">
                <div class="campo">
                    <label for="efeito_vida">Efeito na Vida (ex: -2, 0, 3):</label>
                    <input type="number" id="efeito_vida" name="efeito_vida" value="<?= (int) ($escolhaEdicao['efeito_vida'] ?? 0) ?>" required>
                </div>

                <div class="campo">
                    <label for="efeito_moedas">Efeito nas Moedas (ex: -5, 0, 10):</label>
                    <input type="number" id="efeito_moedas" name="efeito_moedas" value="<?= (int) ($escolhaEdicao['efeito_moedas'] ?? 0) ?>" required>
                </div>
            </div>

            <div class="botoes-form">
                <button type="submit" class="btn"><?= $modoEdicaoEscolha ? 'Salvar Escolha' : 'Cadastrar Escolha' ?></button>
                <?php if ($modoEdicaoEscolha): ?>
                    <a href="admin.php" class="btn btn-secundario">Cancelar Edição</a>
                <?php endif; ?>
            </div>
        </form>

        <h4>Escolhas Cadastradas (<?= count($todasEscolhas) ?>)</h4>
        <div class="tabela-responsiva">
            <table class="tabela-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Origem</th>
                        <th>Texto da Escolha</th>
                        <th>Destino</th>
                        <th>Vida</th>
                        <th>Moedas</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($todasEscolhas)): ?>
                        <tr><td colspan="7">Nenhuma escolha cadastrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($todasEscolhas as $escolha): ?>
                            <tr>
                                <td><?= (int) $escolha['id'] ?></td>
                                <td>#<?= (int) $escolha['cena_id'] ?> - <?= h($escolha['origem_titulo']) ?></td>
                                <td><?= h($escolha['texto']) ?></td>
                                <td>#<?= (int) $escolha['destino_id'] ?> - <?= h($escolha['destino_titulo']) ?></td>
                                <td><strong><?= h(formatar_efeito((int) $escolha['efeito_vida'])) ?></strong></td>
                                <td><strong><?= h(formatar_efeito((int) $escolha['efeito_moedas'])) ?></strong></td>
                                <td>
                                    <a href="admin.php?editar_escolha=<?= (int) $escolha['id'] ?>" class="btn-acao">Editar</a>
                                    <form action="admin.php" method="POST" class="form-inline" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta escolha?');">
                                        <input type="hidden" name="acao" value="excluir_escolha">
                                        <input type="hidden" name="id" value="<?= (int) $escolha['id'] ?>">
                                        <button type="submit" class="btn-acao btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/includes/rodape.php';
