# Regras Técnicas e Arquitetura - Projeto Mayab

## Stack Obrigatória
- Backend: PHP 8 puro (estruturado/procedural, sem frameworks ou Composer) e MySQL via PDO[cite: 3, 25, 26].
- Frontend: HTML5 e CSS3 puro (Bootstrap permitido apenas se solicitado)[cite: 3].
- JavaScript: PROIBIDO para lógica de jogo, controle de vida/moedas ou cálculo de pontuação[cite: 3, 4]. Toda regra de negócio ocorre exclusivamente no servidor (PHP)[cite: 3, 4].

## Estrutura de Arquivos
- Raiz: `index.php`, `cadastro.php`, `login.php`, `logout.php`, `jogo.php`, `ranking.php`, `admin.php`, `banco.sql`, `README.md`[cite: 1].
- `includes/`: `conexao.php` (PDO + `session_start()` + helper `h()`), `protege.php` (verificação de sessão + `eh_admin()` + `exige_admin()`), `topo.php` e `rodape.php`[cite: 1].
- `css/style.css` e `img/`[cite: 1].

## Segurança e Padrões (Critérios de Avaliação)
- Queries com parâmetros devem usar obrigatoriamente Prepared Statements (`$pdo->prepare()`)[cite: 4].
- Saídas HTML de dados do banco ou usuário devem usar `h($texto)` (`htmlspecialchars` com `ENT_QUOTES` e `UTF-8`)[cite: 1, 4].
- Senhas: `password_hash()` no cadastro e `password_verify()` no login[cite: 1, 4].
- Estado da partida: armazenado em `$_SESSION['partida']`[cite: 1]. Usar Post/Redirect/Get (`header('Location: jogo.php'); exit;`) ao iniciar ou processar ações para impedir duplicação no banco ou novo sorteio ao apertar F5[cite: 1, 4, 38].