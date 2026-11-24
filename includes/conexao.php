<?php
// Configurações de conexão com o banco de dados Mayab
$host = 'localhost';
$db   = 'mayab';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}

// Inicialização segura da sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função helper para proteção contra XSS (htmlspecialchars com ENT_QUOTES e UTF-8)
if (!function_exists('h')) {
    function h($texto): string {
        return htmlspecialchars((string)($texto ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
