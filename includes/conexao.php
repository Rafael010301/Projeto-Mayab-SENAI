<?php
declare(strict_types=1);

/**
 * Núcleo de conexão com o banco de dados (MySQL via PDO),
 * inicialização segura de sessão e helper de sanitização HTML.
 *
 * Segurança de credenciais:
 * Crie um arquivo `.env` na raiz do projeto (já ignorado pelo `.gitignore`)
 * seguindo o modelo `.env.example` para definir credenciais próprias sem
 * expô-las no repositório. Caso `.env` não exista, utiliza variáveis de
 * ambiente do servidor ou os padrões locais do XAMPP.
 */

if (!function_exists('carregar_env_local')) {
    /**
     * Lê pares CHAVE=VALOR de um arquivo `.env` local sem dependências externas.
     *
     * @return array<string, string>
     */
    function carregar_env_local(string $caminho): array
    {
        // Nota: evita-se `is_readable()` no Windows, pois pode retornar falso em ACLs NTFS válidas
        if (!is_file($caminho)) {
            return [];
        }

        $linhas = @file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($linhas === false) {
            return [];
        }

        $variaveis = [];
        foreach ($linhas as $linha) {
            $linha = trim($linha);

            // Ignora comentários e linhas sem atribuição válida
            if (
                $linha === ''
                || str_starts_with($linha, '#')
                || str_starts_with($linha, ';')
                || !str_contains($linha, '=')
            ) {
                continue;
            }

            [$chave, $valor] = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim($valor);

            // Remove aspas simples ou duplas envolventes, caso presentes
            if (strlen($valor) >= 2) {
                $primeiro = $valor[0];
                $ultimo   = $valor[strlen($valor) - 1];
                if (($primeiro === '"' && $ultimo === '"') || ($primeiro === "'" && $ultimo === "'")) {
                    $valor = substr($valor, 1, -1);
                }
            }

            if ($chave !== '') {
                $variaveis[$chave] = $valor;
            }
        }

        return $variaveis;
    }
}

if (!function_exists('conectar_bd')) {
    /**
     * Retorna uma instância única (estática) do PDO conectada ao banco `mayab`.
     *
     * Utiliza o padrão singleton em abordagem procedural via `static $pdo`,
     * evitando múltiplas conexões na mesma requisição e isolando credenciais
     * do escopo global.
     */
    function conectar_bd(): PDO
    {
        static $pdo = null;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $env = carregar_env_local(dirname(__DIR__) . '/.env');

        // Ordem de prioridade: .env local -> variável de ambiente -> padrão XAMPP
        $obterConfig = static function (string $chave, string $padrao) use ($env): string {
            if (array_key_exists($chave, $env)) {
                return $env[$chave];
            }
            $valorSistema = getenv($chave);
            return ($valorSistema !== false) ? $valorSistema : $padrao;
        };

        // 127.0.0.1 força TCP/IP direto, evitando ambiguidade de socket/DNS no Windows
        $host    = $obterConfig('DB_HOST', '127.0.0.1');
        $port    = $obterConfig('DB_PORT', '3306');
        $db      = $obterConfig('DB_NAME', 'mayab');
        $user    = $obterConfig('DB_USER', 'root');
        $pass    = $obterConfig('DB_PASS', '');
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Registra o erro técnico no log do servidor sem expor detalhes sensíveis na tela
            error_log('Erro de conexão PDO (Mayab): ' . $e->getMessage());
            http_response_code(500);
            exit('Não foi possível conectar ao banco de dados no momento.');
        }

        return $pdo;
    }
}

// Inicialização segura da sessão com flags de proteção de cookie
if (session_status() === PHP_SESSION_NONE) {
    $httpsAtivo = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => $httpsAtivo,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

if (!function_exists('h')) {
    /**
     * Escapa saídas HTML para prevenir ataques XSS (Cross-Site Scripting).
     */
    function h(string|int|float|null $texto): string
    {
        return htmlspecialchars((string) ($texto ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Gera ou retorna o token CSRF único armazenado na sessão atual.
     */
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verificar_csrf')) {
    /**
     * Valida em tempo constante o token CSRF enviado em formulários POST.
     */
    function verificar_csrf(?string $token): bool
    {
        if ($token === null || $token === '' || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals((string) $_SESSION['csrf_token'], $token);
    }
}

// Mantém $pdo disponível para scripts procedurais que esperam a variável pronta
$pdo = conectar_bd();
