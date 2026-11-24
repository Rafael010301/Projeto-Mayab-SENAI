<?php
// Garante que conexao.php está carregado caso não tenha sido incluído antes
require_once __DIR__ . '/conexao.php';
$ehPaginaAuth = !empty($paginaAuth);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($titulo ?? 'Mayab') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/base/global.css">
    <link rel="stylesheet" href="assets/css/components/components.css">
    <?php if (!empty($cssPagina)): ?>
        <link rel="stylesheet" href="<?= h($cssPagina) ?>">
    <?php endif; ?>
</head>
<body<?= $ehPaginaAuth ? ' class="page-auth"' : '' ?>>
<?php if (!$ehPaginaAuth): ?>
<header class="site-header">
    <div class="nav-container">
        <a href="index.php" class="logo">Mayab</a>
        <nav>
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="ranking.php">Ranking</a></li>
                <?php if (function_exists('eh_admin') ? eh_admin() : (!empty($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin')): ?>
                    <li><a href="admin.php">Administração</a></li>
                <?php endif; ?>
                <?php if (!empty($_SESSION['usuario_id'])): ?>
                    <li class="nav-user">Jogador: <strong><?= h($_SESSION['apelido'] ?? '') ?></strong></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php">Entrar</a></li>
                    <li><a href="cadastro.php">Cadastrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<?php endif; ?>
<main>
