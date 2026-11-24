<?php
declare(strict_types=1);

/**
 * Encerra a sessão do usuário de forma segura e redireciona para o login.
 */
require_once __DIR__ . '/includes/conexao.php';

// Esvazia todos os dados armazenados na sessão atual
$_SESSION = [];

// Invalida o cookie de sessão no navegador, se configurado
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool) $params['secure'],
        (bool) $params['httponly']
    );
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

header('Location: login.php');
exit;
