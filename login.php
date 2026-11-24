<?php
require_once __DIR__ . '/includes/conexao.php';

// Se já estiver logado, redireciona para index
if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$email = '';
$sucesso = '';

if (isset($_GET['cadastrado']) && $_GET['cadastrado'] === '1') {
    $sucesso = 'Cadastro realizado com sucesso! Faça login para começar a jogar.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Informe o e-mail e a senha.';
    } else {
        // Busca usuário por e-mail com Prepared Statement
        $stmt = $pdo->prepare('SELECT id, nome, apelido, email, senha, perfil FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Valida senha com password_verify()
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Regenera ID de sessão para prevenir fixação de sessão
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = (int)$usuario['id'];
            $_SESSION['apelido']    = $usuario['apelido'];
            $_SESSION['perfil']     = $usuario['perfil'];

            header('Location: index.php');
            exit;
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}

$titulo = 'Login - Mayab';
require_once __DIR__ . '/includes/topo.php';
?>

<div class="tela-entrada">
    <div class="caixa">
        <h2>Entrar em Mayab</h2>
        <p>Acesse sua conta para continuar sua jornada.</p>

        <?php if ($sucesso !== ''): ?>
            <div class="ok"><?= h($sucesso) ?></div>
        <?php endif; ?>

        <?php if ($erro !== ''): ?>
            <div class="erro"><?= h($erro) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="campo">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?= h($email) ?>" required maxlength="100">
            </div>

            <div class="campo">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button type="submit" class="btn">Entrar</button>
        </form>

        <p class="link-alternativo">
            Ainda não tem conta? <a href="cadastro.php">Cadastre-se aqui</a>
        </p>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/rodape.php';
