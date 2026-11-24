<?php
declare(strict_types=1);

/**
 * Cadastro de novos jogadores com validação server-side,
 * verificação de duplicidade, hash seguro de senha e login automático.
 */
require_once __DIR__ . '/includes/conexao.php';

// Usuário já autenticado não precisa acessar a tela de cadastro
if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
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

                header('Location: index.php');
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
