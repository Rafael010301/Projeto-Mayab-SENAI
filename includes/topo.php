<?php
// Garante que conexao.php está carregado caso não tenha sido incluído antes
require_once __DIR__ . '/conexao.php';
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
<main>
