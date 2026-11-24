<?php
require_once __DIR__ . '/conexao.php';

// Define helper para checar se o usuário logado é administrador
if (!function_exists('eh_admin')) {
    function eh_admin(): bool {
        return !empty($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin';
    }
}

// Define helper para restringir páginas exclusivas de administrador
if (!function_exists('exige_admin')) {
    function exige_admin(): void {
        if (!eh_admin()) {
            header('Location: index.php');
            exit;
        }
    }
}

// Redireciona para login.php se não houver usuário autenticado na sessão
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
