<?php
declare(strict_types=1);

/**
 * Middleware de proteção de rotas autenticadas e controle de perfil administrativo.
 */
require_once __DIR__ . '/conexao.php';

if (!function_exists('eh_admin')) {
    /**
     * Verifica se o usuário autenticado na sessão possui perfil de administrador.
     */
    function eh_admin(): bool
    {
        return !empty($_SESSION['usuario_id'])
            && ($_SESSION['perfil'] ?? '') === 'admin';
    }
}

if (!function_exists('exige_admin')) {
    /**
     * Bloqueia o acesso de usuários não administradores, redirecionando para a página inicial.
     */
    function exige_admin(): void
    {
        if (!eh_admin()) {
            header('Location: index.php');
            exit;
        }
    }
}

// Exige sessão autenticada com ID válido antes de renderizar páginas protegidas
if (empty($_SESSION['usuario_id']) || (int) $_SESSION['usuario_id'] <= 0) {
    header('Location: login.php');
    exit;
}
