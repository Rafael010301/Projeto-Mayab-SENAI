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
$paginaAtual     = basename($_SERVER['SCRIPT_NAME'] ?? '');
$ehPaginaAuth    = !empty($paginaAuth);
$introVideoAtiva = !empty($introVideoAtiva);

$classesBody = [];
if ($ehPaginaAuth) {
    $classesBody[] = 'page-auth';
}
if ($introVideoAtiva) {
    $classesBody[] = 'intro-video-active';
}
$attrClasseBody = !empty($classesBody) ? ' class="' . h(implode(' ', $classesBody)) . '"' : '';
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
    <?php if (!empty($cssPagina) && is_string($cssPagina)): ?>
        <link rel="stylesheet" href="<?= h($cssPagina) ?>">
    <?php endif; ?>
    <?php if ($introVideoAtiva): ?>
        <noscript>
            <style>
                .intro-cinema { display: none !important; }
                body.intro-video-active { overflow: auto !important; }
                body.intro-video-active .site-header,
                body.intro-video-active .site-footer,
                body.intro-video-active .game-wrap { visibility: visible !important; pointer-events: auto !important; }
            </style>
        </noscript>
    <?php endif; ?>
</head>
<body<?= $attrClasseBody ?>>
<?php if (!$ehPaginaAuth): ?>
<header class="site-header">
    <div class="nav-container">
        <a href="index.php" class="logo">Mayab</a>
        <nav aria-label="Navegação principal">
            <ul class="nav-links">
                <li>
                    <a href="index.php"<?= $paginaAtual === 'index.php' ? ' aria-current="page"' : '' ?>>Início</a>
                </li>
                <?php if ($usuarioLogado): ?>
                    <li>
                        <a href="jogo.php"<?= $paginaAtual === 'jogo.php' ? ' aria-current="page"' : '' ?>>Jogar</a>
                    </li>
                <?php endif; ?>
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
                    <li><a href="logout.php" onclick="return confirm('Tem deseja que quer sair ?');">Sair</a></li>
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
<?php endif; ?>
<main>
