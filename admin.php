<?php
require_once __DIR__ . '/includes/protege.php';
exige_admin();

$mensagemSucesso = '';
$mensagemErro = '';

// Mapeamento de mensagens via GET
if (isset($_GET['sucesso'])) {
    switch ($_GET['sucesso']) {
        case 'cena_criada':
            $mensagemSucesso = 'Cena cadastrada com sucesso!';
            break;
        case 'cena_atualizada':
            $mensagemSucesso = 'Cena atualizada com sucesso!';
            break;
        case 'cena_excluida':
            $mensagemSucesso = 'Cena excluída com sucesso!';
            break;
        case 'escolha_criada':
            $mensagemSucesso = 'Escolha cadastrada com sucesso!';
            break;
        case 'escolha_atualizada':
            $mensagemSucesso = 'Escolha atualizada com sucesso!';
            break;
        case 'escolha_excluida':
            $mensagemSucesso = 'Escolha excluída com sucesso!';
            break;
    }
}

// -------------------------------------------------------------
// EXCLUSÕES (via GET com validação de ID)
// -------------------------------------------------------------
if (isset($_GET['excluir_cena'])) {
    $idExcluir = (int)$_GET['excluir_cena'];
    if ($idExcluir > 0) {
        $stmtDel = $pdo->prepare('DELETE FROM cenas WHERE id = ?');
        $stmtDel->execute([$idExcluir]);
        header('Location: admin.php?sucesso=cena_excluida');
        exit;
    }
}

if (isset($_GET['excluir_escolha'])) {
    $idExcluir = (int)$_GET['excluir_escolha'];
    if ($idExcluir > 0) {
        $stmtDel = $pdo->prepare('DELETE FROM escolhas WHERE id = ?');
        $stmtDel->execute([$idExcluir]);
        header('Location: admin.php?sucesso=escolha_excluida');
        exit;
    }
}

// -------------------------------------------------------------
// PROCESSAMENTO DE POST (CENAS E ESCOLHAS)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // Cadastrar Cena
    if ($acao === 'cadastrar_cena') {
        $titulo = trim($_POST['titulo'] ?? '');
        $texto  = trim($_POST['texto'] ?? '');
        $imagem = trim($_POST['imagem'] ?? '');
        $tipo   = $_POST['tipo'] ?? 'normal';
        $imagemVal = ($imagem === '') ? null : $imagem;

        $tiposValidos = ['normal', 'final_bom', 'final_ruim'];
        if ($titulo === '' || $texto === '') {
            $mensagemErro = 'Título e texto da cena são obrigatórios.';
        } elseif (!in_array($tipo, $tiposValidos, true)) {
            $mensagemErro = 'Tipo de cena inválido.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO cenas (titulo, texto, imagem, tipo) VALUES (?, ?, ?, ?)');
            $stmt->execute([$titulo, $texto, $imagemVal, $tipo]);
            header('Location: admin.php?sucesso=cena_criada');
            exit;
        }
    }

    // Editar Cena
    if ($acao === 'editar_cena') {
        $id     = (int)($_POST['id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $texto  = trim($_POST['texto'] ?? '');
        $imagem = trim($_POST['imagem'] ?? '');
        $tipo   = $_POST['tipo'] ?? 'normal';
        $imagemVal = ($imagem === '') ? null : $imagem;

        $tiposValidos = ['normal', 'final_bom', 'final_ruim'];
        if ($id <= 0 || $titulo === '' || $texto === '') {
            $mensagemErro = 'Dados inválidos para edição de cena.';
        } elseif (!in_array($tipo, $tiposValidos, true)) {
            $mensagemErro = 'Tipo de cena inválido.';
        } else {
            $stmt = $pdo->prepare('UPDATE cenas SET titulo = ?, texto = ?, imagem = ?, tipo = ? WHERE id = ?');
            $stmt->execute([$titulo, $texto, $imagemVal, $tipo, $id]);
            header('Location: admin.php?sucesso=cena_atualizada');
            exit;
        }
    }

    // Cadastrar Escolha
    if ($acao === 'cadastrar_escolha') {
        $cena_id       = (int)($_POST['cena_id'] ?? 0);
        $texto         = trim($_POST['texto'] ?? '');
        $destino_id    = (int)($_POST['destino_id'] ?? 0);
        $efeito_vida   = (int)($_POST['efeito_vida'] ?? 0);
        $efeito_moedas = (int)($_POST['efeito_moedas'] ?? 0);

        if ($cena_id <= 0 || $destino_id <= 0 || $texto === '') {
            $mensagemErro = 'Cena de origem, cena de destino e texto da escolha são obrigatórios.';
        } else {
            // Valida existência de cena de origem e destino
            $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM cenas WHERE id IN (?, ?)');
            $stmtCheck->execute([$cena_id, $destino_id]);
            $qtd = (int)$stmtCheck->fetchColumn();

            // Se origem e destino forem a mesma cena existente, qtd é 1; se diferentes, deve ser 2
            $necessario = ($cena_id === $destino_id) ? 1 : 2;
            if ($qtd < $necessario) {
                $mensagemErro = 'A cena de origem ou de destino informada não existe.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO escolhas (cena_id, texto, destino_id, efeito_vida, efeito_moedas) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$cena_id, $texto, $destino_id, $efeito_vida, $efeito_moedas]);
                header('Location: admin.php?sucesso=escolha_criada');
                exit;
            }
        }
    }

    // Editar Escolha
    if ($acao === 'editar_escolha') {
        $id            = (int)($_POST['id'] ?? 0);
        $cena_id       = (int)($_POST['cena_id'] ?? 0);
        $texto         = trim($_POST['texto'] ?? '');
        $destino_id    = (int)($_POST['destino_id'] ?? 0);
        $efeito_vida   = (int)($_POST['efeito_vida'] ?? 0);
        $efeito_moedas = (int)($_POST['efeito_moedas'] ?? 0);

        if ($id <= 0 || $cena_id <= 0 || $destino_id <= 0 || $texto === '') {
            $mensagemErro = 'Dados inválidos para edição de escolha.';
        } else {
            $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM cenas WHERE id IN (?, ?)');
            $stmtCheck->execute([$cena_id, $destino_id]);
            $qtd = (int)$stmtCheck->fetchColumn();
            $necessario = ($cena_id === $destino_id) ? 1 : 2;

            if ($qtd < $necessario) {
                $mensagemErro = 'A cena de origem ou destino selecionada não existe.';
            } else {
                $stmt = $pdo->prepare('UPDATE escolhas SET cena_id = ?, texto = ?, destino_id = ?, efeito_vida = ?, efeito_moedas = ? WHERE id = ?');
                $stmt->execute([$cena_id, $texto, $destino_id, $efeito_vida, $efeito_moedas, $id]);
                header('Location: admin.php?sucesso=escolha_atualizada');
                exit;
            }
        }
    }
}

// -------------------------------------------------------------
// DADOS PARA EDIÇÃO (SE SOLICITADO VIA GET)
// -------------------------------------------------------------
$cenaEdicao = null;
if (isset($_GET['editar_cena'])) {
    $idEdit = (int)$_GET['editar_cena'];
    if ($idEdit > 0) {
        $stmt = $pdo->prepare('SELECT * FROM cenas WHERE id = ?');
        $stmt->execute([$idEdit]);
        $cenaEdicao = $stmt->fetch();
    }
}

$escolhaEdicao = null;
if (isset($_GET['editar_escolha'])) {
    $idEdit = (int)$_GET['editar_escolha'];
    if ($idEdit > 0) {
        $stmt = $pdo->prepare('SELECT * FROM escolhas WHERE id = ?');
        $stmt->execute([$idEdit]);
        $escolhaEdicao = $stmt->fetch();
    }
}

// -------------------------------------------------------------
// LISTAGEM DE DADOS
// -------------------------------------------------------------
$stmtCenas = $pdo->query('SELECT * FROM cenas ORDER BY id ASC');
$todasCenas = $stmtCenas->fetchAll();

$stmtEscolhas = $pdo->query('
    SELECT e.*, c_origem.titulo AS origem_titulo, c_dest.titulo AS destino_titulo 
    FROM escolhas e 
    JOIN cenas c_origem ON e.cena_id = c_origem.id 
    JOIN cenas c_dest ON e.destino_id = c_dest.id 
    ORDER BY e.cena_id ASC, e.id ASC
');
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
        <h3><?= $cenaEdicao ? 'Editar Cena #' . h($cenaEdicao['id']) : 'Cadastrar Nova Cena' ?></h3>

        <form action="admin.php" method="POST" class="form-admin">
            <input type="hidden" name="acao" value="<?= $cenaEdicao ? 'editar_cena' : 'cadastrar_cena' ?>">
            <?php if ($cenaEdicao): ?>
                <input type="hidden" name="id" value="<?= (int)$cenaEdicao['id'] ?>">
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
                    $tipos = ['normal' => 'Normal', 'final_bom' => 'Final Bom', 'final_ruim' => 'Final Ruim'];
                    $tipoAtual = $cenaEdicao['tipo'] ?? 'normal';
                    foreach ($tipos as $val => $label):
                    ?>
                        <option value="<?= h($val) ?>" <?= ($tipoAtual === $val) ? 'selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="botoes-form">
                <button type="submit" class="btn"><?= $cenaEdicao ? 'Salvar Alterações' : 'Cadastrar Cena' ?></button>
                <?php if ($cenaEdicao): ?>
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
                        <?php foreach ($todasCenas as $c): ?>
                            <tr>
                                <td><?= (int)$c['id'] ?></td>
                                <td><strong><?= h($c['titulo']) ?></strong></td>
                                <td><span class="badge badge-<?= h($c['tipo']) ?>"><?= h($c['tipo']) ?></span></td>
                                <td><?= h($c['imagem'] ?? 'Nenhuma') ?></td>
                                <td>
                                    <a href="admin.php?editar_cena=<?= (int)$c['id'] ?>" class="btn-acao">Editar</a>
                                    <a href="admin.php?excluir_cena=<?= (int)$c['id'] ?>" class="btn-acao btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta cena? Todas as escolhas associadas serão excluídas.');">Excluir</a>
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
        <h3><?= $escolhaEdicao ? 'Editar Escolha #' . h($escolhaEdicao['id']) : 'Cadastrar Nova Escolha' ?></h3>

        <form action="admin.php" method="POST" class="form-admin">
            <input type="hidden" name="acao" value="<?= $escolhaEdicao ? 'editar_escolha' : 'cadastrar_escolha' ?>">
            <?php if ($escolhaEdicao): ?>
                <input type="hidden" name="id" value="<?= (int)$escolhaEdicao['id'] ?>">
            <?php endif; ?>

            <div class="campo">
                <label for="cena_id">Cena de Origem:</label>
                <select id="cena_id" name="cena_id" required>
                    <option value="">Selecione a cena de origem</option>
                    <?php
                    $origemAtual = (int)($escolhaEdicao['cena_id'] ?? 0);
                    foreach ($todasCenas as $c):
                    ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ($origemAtual === (int)$c['id']) ? 'selected' : '' ?>>
                            #<?= (int)$c['id'] ?> - <?= h($c['titulo']) ?> (<?= h($c['tipo']) ?>)
                        </option>
                    <?php endforeach; ?>
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
                    <?php
                    $destinoAtual = (int)($escolhaEdicao['destino_id'] ?? 0);
                    foreach ($todasCenas as $c):
                    ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ($destinoAtual === (int)$c['id']) ? 'selected' : '' ?>>
                            #<?= (int)$c['id'] ?> - <?= h($c['titulo']) ?> (<?= h($c['tipo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo-grupo">
                <div class="campo">
                    <label for="efeito_vida">Efeito na Vida (ex: -2, 0, 3):</label>
                    <input type="number" id="efeito_vida" name="efeito_vida" value="<?= (int)($escolhaEdicao['efeito_vida'] ?? 0) ?>" required>
                </div>

                <div class="campo">
                    <label for="efeito_moedas">Efeito nas Moedas (ex: -5, 0, 10):</label>
                    <input type="number" id="efeito_moedas" name="efeito_moedas" value="<?= (int)($escolhaEdicao['efeito_moedas'] ?? 0) ?>" required>
                </div>
            </div>

            <div class="botoes-form">
                <button type="submit" class="btn"><?= $escolhaEdicao ? 'Salvar Escolha' : 'Cadastrar Escolha' ?></button>
                <?php if ($escolhaEdicao): ?>
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
                        <?php foreach ($todasEscolhas as $e): ?>
                            <tr>
                                <td><?= (int)$e['id'] ?></td>
                                <td>#<?= (int)$e['cena_id'] ?> - <?= h($e['origem_titulo']) ?></td>
                                <td><?= h($e['texto']) ?></td>
                                <td>#<?= (int)$e['destino_id'] ?> - <?= h($e['destino_titulo']) ?></td>
                                <td><strong><?= (int)$e['efeito_vida'] > 0 ? '+' . (int)$e['efeito_vida'] : (int)$e['efeito_vida'] ?></strong></td>
                                <td><strong><?= (int)$e['efeito_moedas'] > 0 ? '+' . (int)$e['efeito_moedas'] : (int)$e['efeito_moedas'] ?></strong></td>
                                <td>
                                    <a href="admin.php?editar_escolha=<?= (int)$e['id'] ?>" class="btn-acao">Editar</a>
                                    <a href="admin.php?excluir_escolha=<?= (int)$e['id'] ?>" class="btn-acao btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta escolha?');">Excluir</a>
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
