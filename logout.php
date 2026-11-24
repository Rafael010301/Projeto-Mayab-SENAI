<?php
require_once __DIR__ . '/includes/conexao.php';

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Se cookies de sessão forem usados, invalida o cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona para login.php
header('Location: login.php');
exit;
