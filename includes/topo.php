<?php
declare(strict_types=1);

/**
 * Cabeçalho HTML5 compartilhado pelas páginas internas e barra de navegação dinâmica.
 */
require_once __DIR__ . '/conexao.php';

$usuarioLogado = !empty($_SESSION['usuario_id']);
$usuarioAdmin  = function_exists('eh_admin')
    ? eh_admin()
    : ($usuarioLogado && ($_SESSION['perfil'] ?? '') === 'admin');
$paginaAtual   = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($titulo ?? 'Mayab') ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="nav-container">
        <a href="index.php" class="logo">Mayab</a>
        <nav aria-label="Navegação principal">
            <ul class="nav-links">
                <li>
                    <a href="index.php"<?= $paginaAtual === 'index.php' ? ' aria-current="page"' : '' ?>>Início</a>
                </li>
                <li>
                    <a href="ranking.php"<?= $paginaAtual === 'ranking.php' ? ' aria-current="page"' : '' ?>>Ranking</a>
                </li>
                <?php if ($usuarioAdmin): ?>
                    <li>
                        <a href="admin.php"<?= $paginaAtual === 'admin.php' ? ' aria-current="page"' : '' ?>>Administração</a>
                    </li>
                <?php endif; ?>
                <?php if ($usuarioLogado): ?>
                    <li class="nav-user">Jogador: <strong><?= h($_SESSION['apelido'] ?? '') ?></strong></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li>
                        <a href="login.php"<?= $paginaAtual === 'login.php' ? ' aria-current="page"' : '' ?>>Entrar</a>
                    </li>
                    <li>
                        <a href="cadastro.php"<?= $paginaAtual === 'cadastro.php' ? ' aria-current="page"' : '' ?>>Cadastrar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main>
