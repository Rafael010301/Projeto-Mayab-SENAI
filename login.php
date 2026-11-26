<?php
declare(strict_types=1);

/**
 * Autenticação de usuários via e-mail e senha com password_verify()
 * e regeneração do identificador de sessão.
 */
require_once __DIR__ . '/includes/conexao.php';

// Redireciona se o usuário já possui sessão ativa
if (!empty($_SESSION['usuario_id'])) {
    header('Location: jogo.php');
    exit;
}

$pdo = conectar_bd();

$erro    = '';
$email   = '';
$sucesso = (($_GET['cadastrado'] ?? '') === '1')
    ? 'Cadastro realizado com sucesso! Faça login para começar a jogar.'
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Informe o e-mail e a senha.';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $erro = 'Informe um endereço de e-mail válido.';
    } else {
        // Seleciona apenas as colunas necessárias para autenticação e sessão
        $stmt = $pdo->prepare(
            'SELECT id, apelido, senha, perfil
             FROM usuarios
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario !== false && password_verify($senha, $usuario['senha'])) {
            // Atualiza o hash automaticamente caso o algoritmo/custo padrão do PHP evolua
            if (password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
                $stmtRehash = $pdo->prepare(
                    'UPDATE usuarios SET senha = :senha WHERE id = :id'
                );
                $stmtRehash->execute([
                    ':senha' => password_hash($senha, PASSWORD_DEFAULT),
                    ':id'    => (int) $usuario['id'],
                ]);
            }

            // Previne ataque de fixação de sessão ao autenticar
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = (int) $usuario['id'];
            $_SESSION['apelido']    = $usuario['apelido'];
            $_SESSION['perfil']     = $usuario['perfil'];

            header('Location: jogo.php');
            exit;
        }

        $erro = 'E-mail ou senha incorretos.';
    }
}

$titulo = 'Login - Mayab';
$paginaAuth = true;
$cssPagina = 'assets/css/pages/auth.css';
require_once __DIR__ . '/includes/topo.php';
?>

<section class="auth-page" aria-labelledby="auth-titulo">
    <div class="card auth-card">
        <header class="auth-header">
            <img class="auth-logo" src="assets/img/logo.png" alt="MAYAB: O Templo Perdido" width="1024" height="290">
            <h1 class="auth-title" id="auth-titulo">ACESSAR EXPEDIÇÃO</h1>
            <p class="auth-subtitle">Acesse sua conta para atravessar o portal e continuar sua jornada.</p>
        </header>

        <?php if ($sucesso !== ''): ?>
            <div class="ok"><?= h($sucesso) ?></div>
        <?php endif; ?>

        <?php if ($erro !== ''): ?>
            <div class="erro"><?= h($erro) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="form">
            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?= h($email) ?>" placeholder="expedicao@mayab.com" required maxlength="100" autocomplete="email">
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn--full">Entrar</button>
        </form>
        <p class="form-status" id="google-auth-status" role="status" aria-live="polite"></p>
        <p class="auth-footer">
            Ainda não tem conta? <a href="cadastro.php">Criar conta</a>
        </p>
        <p class="auth-footer">
            <a class="auth-back" href="index.php">&larr; Voltar ao início</a>
        </p>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/rodape.php';
