<?php
require_once __DIR__ . '/includes/conexao.php';

// Se já estiver logado, redireciona para index
if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$nome = '';
$apelido = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $apelido = trim($_POST['apelido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Validação de campos obrigatórios
    if ($nome === '' || $apelido === '' || $email === '' || $senha === '') {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor, informe um endereço de e-mail válido.';
    } elseif (mb_strlen($senha) < 6) {
        $erro = 'A senha deve conter pelo menos 6 caracteres.';
    } else {
        // Verifica duplicidade de email ou apelido usando Prepared Statement
        $stmt = $pdo->prepare('SELECT id, email, apelido FROM usuarios WHERE email = ? OR apelido = ? LIMIT 1');
        $stmt->execute([$email, $apelido]);
        $usuarioExistente = $stmt->fetch();

        if ($usuarioExistente) {
            if (strcasecmp($usuarioExistente['email'], $email) === 0) {
                $erro = 'O e-mail informado já está cadastrado.';
            } else {
                $erro = 'O apelido informado já está em uso.';
            }
        } else {
            // Salva novo usuário com password_hash()
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
            $stmtInsert = $pdo->prepare('INSERT INTO usuarios (nome, apelido, email, senha, perfil) VALUES (?, ?, ?, ?, "jogador")');
            $stmtInsert->execute([$nome, $apelido, $email, $hashSenha]);

            // Login automático após cadastro
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$pdo->lastInsertId();
            $_SESSION['apelido']    = $apelido;
            $_SESSION['perfil']     = 'jogador';

            header('Location: index.php');
            exit;
        }
    }
}

$titulo = 'Cadastro - Mayab';
require_once __DIR__ . '/includes/topo.php';
?>

<div class="tela-entrada">
    <div class="caixa">
        <h2>Criar Conta</h2>
        <p>Cadastre-se para iniciar sua jornada nos mistérios de Mayab.</p>

        <?php if ($erro !== ''): ?>
            <div class="erro"><?= h($erro) ?></div>
        <?php endif; ?>

        <form action="cadastro.php" method="POST">
            <div class="campo">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" value="<?= h($nome) ?>" required maxlength="100">
            </div>

            <div class="campo">
                <label for="apelido">Apelido (como será visto no jogo):</label>
                <input type="text" id="apelido" name="apelido" value="<?= h($apelido) ?>" required maxlength="50">
            </div>

            <div class="campo">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?= h($email) ?>" required maxlength="100">
            </div>

            <div class="campo">
                <label for="senha">Senha (mínimo 6 caracteres):</label>
                <input type="password" id="senha" name="senha" required minlength="6">
            </div>

            <button type="submit" class="btn">Cadastrar</button>
        </form>

        <p class="link-alternativo">
            Já possui uma conta? <a href="login.php">Faça login</a>
        </p>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/rodape.php';
