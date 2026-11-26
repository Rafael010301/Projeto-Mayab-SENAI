<?php
declare(strict_types=1);

/**
 * Cadastro de novos jogadores com validação server-side,
 * verificação de duplicidade, hash seguro de senha e login automático.
 */
require_once __DIR__ . '/includes/conexao.php';

// Usuário já autenticado não precisa acessar a tela de cadastro
if (!empty($_SESSION['usuario_id'])) {
    header('Location: jogo.php');
    exit;
}

$pdo = conectar_bd();

$erro    = '';
$nome    = '';
$apelido = '';
$email   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = trim((string) ($_POST['nome'] ?? ''));
    $apelido = trim((string) ($_POST['apelido'] ?? ''));
    $email   = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha   = (string) ($_POST['senha'] ?? '');

    // Validações obrigatórias e limites compatíveis com as colunas do banco
    if ($nome === '' || $apelido === '' || $email === '' || $senha === '') {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif (mb_strlen($nome) > 100) {
        $erro = 'O nome deve ter no máximo 100 caracteres.';
    } elseif (mb_strlen($apelido) > 50) {
        $erro = 'O apelido deve ter no máximo 50 caracteres.';
    } elseif (mb_strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $erro = 'Por favor, informe um endereço de e-mail válido.';
    } elseif (mb_strlen($senha) < 6) {
        $erro = 'A senha deve conter pelo menos 6 caracteres.';
    } else {
        // Verifica previamente se e-mail ou apelido já pertencem a outro jogador
        $stmtBusca = $pdo->prepare(
            'SELECT email, apelido
             FROM usuarios
             WHERE email = :email OR apelido = :apelido
             LIMIT 1'
        );
        $stmtBusca->execute([
            ':email'   => $email,
            ':apelido' => $apelido,
        ]);
        $usuarioExistente = $stmtBusca->fetch();

        if ($usuarioExistente !== false) {
            $erro = strcasecmp($usuarioExistente['email'], $email) === 0
                ? 'O e-mail informado já está cadastrado.'
                : 'O apelido informado já está em uso.';
        } else {
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

            try {
                // Insere utilizando placeholders nomeados para todos os valores (incluindo perfil)
                $stmtInsert = $pdo->prepare(
                    'INSERT INTO usuarios (nome, apelido, email, senha, perfil)
                     VALUES (:nome, :apelido, :email, :senha, :perfil)'
                );
                $stmtInsert->execute([
                    ':nome'    => $nome,
                    ':apelido' => $apelido,
                    ':email'   => $email,
                    ':senha'   => $hashSenha,
                    ':perfil'  => 'jogador',
                ]);

                // Login automático com regeneração do ID de sessão contra Session Fixation
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int) $pdo->lastInsertId();
                $_SESSION['apelido']    = $apelido;
                $_SESSION['perfil']     = 'jogador';

                header('Location: jogo.php');
                exit;
            } catch (PDOException $e) {
                // Trata violação de chave única (ex.: requisições simultâneas) sem expor erro SQL
                if ($e->getCode() === '23000') {
                    $erro = 'O e-mail ou apelido informado já está em uso.';
                } else {
                    error_log('Erro ao cadastrar usuário: ' . $e->getMessage());
                    $erro = 'Não foi possível concluir o cadastro. Tente novamente.';
                }
            }
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
