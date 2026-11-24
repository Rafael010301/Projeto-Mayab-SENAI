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
$paginaAuth = true;
$cssPagina = 'assets/css/pages/auth.css';
require_once __DIR__ . '/includes/topo.php';
?>

<section class="auth-page" aria-labelledby="auth-titulo">
    <div class="card auth-card">
        <header class="auth-header">
            <img class="auth-logo" src="assets/img/logo.png" alt="MAYAB: O Templo Perdido" width="1024" height="290">
            <h1 class="auth-title" id="auth-titulo">INICIAR EXPEDIÇÃO</h1>
            <p class="auth-subtitle">Crie sua conta para entrar no portal e explorar os mistérios de Mayab.</p>
        </header>

        <?php if ($erro !== ''): ?>
            <div class="erro"><?= h($erro) ?></div>
        <?php endif; ?>

        <form action="cadastro.php" method="POST" class="form">
            <div class="campo">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" value="<?= h($nome) ?>" placeholder="Seu nome completo" required maxlength="100" autocomplete="name">
            </div>

            <div class="campo">
                <label for="apelido">Apelido (como será visto no jogo)</label>
                <input type="text" id="apelido" name="apelido" value="<?= h($apelido) ?>" placeholder="Escolha um apelido" required maxlength="50" autocomplete="nickname">
            </div>

            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= h($email) ?>" placeholder="expedicao@mayab.com" required maxlength="100" autocomplete="email">
            </div>

            <div class="campo">
                <label for="senha">Senha (mínimo 6 caracteres)</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required minlength="6" autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn--full">Cadastrar</button>
        </form>

        <div class="divider" role="separator"><span>ou</span></div>

        <button type="button" class="btn btn--google btn--full" id="btn-google-auth">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.5 3.8-5.5 3.8-3.3 0-6-2.7-6-6s2.7-6 6-6c1.9 0 3.1.8 3.9 1.5l2.7-2.6C16.9 3.2 14.7 2.2 12 2.2 6.6 2.2 2.2 6.6 2.2 12s4.4 9.8 9.8 9.8c5.6 0 9.4-3.9 9.4-9.5 0-.6-.1-1.1-.2-1.6H12z"/>
                <path fill="#34A853" d="M3.3 7.3l3.2 2.3C7.4 7.5 9.5 6 12 6c1.9 0 3.1.8 3.9 1.5l2.7-2.6C16.9 3.2 14.7 2.2 12 2.2 8.2 2.2 4.9 4.3 3.3 7.3z"/>
                <path fill="#FBBC05" d="M12 21.8c2.6 0 4.8-.9 6.4-2.4l-3-2.5c-.8.6-1.9 1-3.4 1-3.9 0-5.2-2.6-5.5-3.9l-3.2 2.5c1.6 3.1 4.9 5.3 8.7 5.3z"/>
                <path fill="#4285F4" d="M21.4 12.3c0-.6-.1-1.1-.2-1.6H12v3.9h5.5c-.3 1.2-1 2.2-2.1 2.9l3 2.5c1.8-1.6 3-4.1 3-7.7z"/>
            </svg>
            Continuar com Google
        </button>
        <p class="form-status" id="google-auth-status" role="status" aria-live="polite"></p>

        <p class="auth-footer">
            Já possui uma conta? <a href="login.php">Faça login</a>
        </p>
        <p class="auth-footer">
            <a class="auth-back" href="index.php">&larr; Voltar ao início</a>
        </p>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/rodape.php';
