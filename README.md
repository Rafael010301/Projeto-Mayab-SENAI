<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&color=0:121212,50:1A4D34,100:D49934&height=220&section=header&text=MAYAB&fontSize=68&fontColor=F4EBD8&fontAlignY=38&desc=O%20Templo%20Perdido%20%7C%20Aventura%20Narrativa%20em%20PHP%20e%20MySQL&descSize=17&descAlignY=62&descColor=B5A577&animation=fadeIn" width="100%" alt="Banner MAYAB - O Templo Perdido"/>

<img src="https://readme-typing-svg.demolab.com?font=Cinzel&weight=700&size=21&pause=1200&color=D49934&center=true&vCenter=true&width=700&lines=Explore+a+selva+de+Yucat%C3%A1n+e+reabra+o+portal+temporal;Cada+escolha+altera+sua+vida%2C+suas+moedas+e+seu+destino" alt="MAYAB - O Templo Perdido"/>

<p>
  <img src="https://img.shields.io/badge/PHP-8.x-1A4D34?style=for-the-badge&logo=php&logoColor=D49934" alt="PHP 8"/>
  <img src="https://img.shields.io/badge/MySQL-PDO-121212?style=for-the-badge&logo=mysql&logoColor=D49934" alt="MySQL PDO"/>
  <img src="https://img.shields.io/badge/HTML5-Sem%C3%A2ntico-1A4D34?style=for-the-badge&logo=html5&logoColor=F4EBD8" alt="HTML5"/>
  <img src="https://img.shields.io/badge/CSS3-Design%20Tokens-121212?style=for-the-badge&logo=css3&logoColor=3DA66B" alt="CSS3"/>
  <img src="https://img.shields.io/badge/SENAI-F%C3%A1brica%20de%20Jogos%202026-D49934?style=for-the-badge&logoColor=121212" alt="SENAI Fábrica de Jogos 2026"/>
</p>

<p>
  <a href="#sobre-o-projeto">Sobre o Projeto</a> &nbsp;&bull;&nbsp;
  <a href="#identidade-visual-e-design-system">Identidade Visual</a> &nbsp;&bull;&nbsp;
  <a href="#arquitetura-e-estrutura-de-pastas">Arquitetura</a> &nbsp;&bull;&nbsp;
  <a href="#regras-de-negocio-e-pontuacao">Regras e Pontuação</a> &nbsp;&bull;&nbsp;
  <a href="#instalacao-e-execucao">Instalação</a> &nbsp;&bull;&nbsp;
  <a href="#equipe-e-perfis-de-acesso">Equipe e Acesso</a>
</p>

</div>

---

## Sobre o Projeto

**MAYAB — O Templo Perdido** é um jogo web de aventura narrativa interativa desenvolvido para o **Projeto Integrador — Fábrica de Jogos (SENAI Félix Guisard · 2026)**, baseado na ficha **07 · Escolha Seu Destino**.

Ambientado em uma representação ficcional de Yucatán, México, o enredo acompanha uma expedição arqueológica de 2026 que descobre uma relíquia capaz de transportar o protagonista ao passado. O jogador precisa explorar a selva densa, gerenciar seus recursos e alcançar o templo de Kukulcán para decidir entre reabrir o portal temporal ou permanecer como guardião dos ciclos do tempo.

<table>
  <tr>
    <td width="50%" valign="top">
      <h3><img src="https://api.iconify.design/lucide/compass.svg?color=%23D49934" width="18" height="18" alt="Narrativa"/> &nbsp;Narrativa Ramificada</h3>
      <p>Progressão dinâmica através de 12 cenas interconectadas (de <em>O Despertar</em> até <em>O Portal</em>), com múltiplas rotas, consequências imediatas em vida e moedas e três desfechos distintos.</p>
    </td>
    <td width="50%" valign="top">
      <h3><img src="https://api.iconify.design/lucide/shield-check.svg?color=%233DA66B" width="18" height="18" alt="Segurança"/> &nbsp;Segurança Server-Side</h3>
      <p>Toda a lógica de estado, validação de escolhas e cálculo de pontuação ocorre exclusivamente no PHP via <code>$_SESSION</code>, com consultas parametrizadas em PDO e proteção contra XSS e reenvio de formulário.</p>
    </td>
  </tr>
  <tr>
    <td width="50%" valign="top">
      <h3><img src="https://api.iconify.design/lucide/sliders-horizontal.svg?color=%23248799" width="18" height="18" alt="Administração"/> &nbsp;Painel Administrativo</h3>
      <p>Gerenciamento completo (CRUD) de cenas e escolhas restrito a usuários com perfil <code>admin</code>, permitindo expandir a árvore narrativa e ajustar efeitos sem alterar o código-fonte.</p>
    </td>
    <td width="50%" valign="top">
      <h3><img src="https://api.iconify.design/lucide/trophy.svg?color=%23B5A577" width="18" height="18" alt="Ranking"/> &nbsp;Ranking e Histórico</h3>
      <p>Classificação global com as 10 maiores pontuações registradas no banco de dados e histórico individual das 5 últimas partidas do jogador autenticado.</p>
    </td>
  </tr>
</table>

<div align="center">
  <br/>
  <img src="https://skillicons.dev/icons?i=php,mysql,html,css,git,github,vscode&theme=dark" alt="Stack Tecnológica"/>
</div>

---

## Identidade Visual e Design System

A interface segue as diretrizes do **Manual de Marca MAYAB (v1.0)**, combinando pedra escura, dourado envelhecido e vegetação tropical com foco em legibilidade, contraste mínimo de `4.5:1` e acessibilidade por teclado.

### Paleta Cromática

| Token CSS | Amostra | Nome | HEX | RGB | Aplicação na Interface |
| :--- | :---: | :--- | :--- | :--- | :--- |
| `--bg` | ![#121212](https://img.shields.io/badge/-%23121212-121212?style=flat-square) | **Preto Profundo** | `#121212` | `18, 18, 18` | Fundo principal e texto sobre botões dourados |
| `--surface` | ![#1A4D34](https://img.shields.io/badge/-%231A4D34-1A4D34?style=flat-square) | **Verde Selva** | `#1A4D34` | `26, 77, 52` | Superfícies secundárias, cabeçalho e cards temáticos |
| `--gold` | ![#D49934](https://img.shields.io/badge/-%23D49934-D49934?style=flat-square) | **Dourado Maia** | `#D49934` | `212, 153, 52` | Títulos, botões primários (hover `#E8B65B`), moedas e destaque |
| `--bronze` | ![#8B621B](https://img.shields.io/badge/-%238B621B-8B621B?style=flat-square) | **Bronze Antigo** | `#8B621B` | `139, 98, 27` | Ornamentos, molduras e detalhes decorativos |
| `--jade` | ![#3DA66B](https://img.shields.io/badge/-%233DA66B-3DA66B?style=flat-square) | **Verde-Jade** | `#3DA66B` | `61, 166, 107` | Indicadores de sucesso, recuperação de vida e natureza |
| `--sand` | ![#B5A577](https://img.shields.io/badge/-%23B5A577-B5A577?style=flat-square) | **Areia Antiga** | `#B5A577` | `181, 165, 119` | Texto secundário, bordas funcionais e contorno de foco |
| `--info` | ![#248799](https://img.shields.io/badge/-%23248799-248799?style=flat-square) | **Turquesa** | `#248799` | `36, 135, 153` | Elementos do portal temporal, avisos e efeitos místicos |
| `--stone` | ![#545454](https://img.shields.io/badge/-%23545454-545454?style=flat-square) | **Cinza Pedra** | `#545454` | `84, 84, 84` | Divisórias, texturas e superfícies neutras |
| `--text` | ![#F4EBD8](https://img.shields.io/badge/-%23F4EBD8-F4EBD8?style=flat-square) | **Texto Claro** | `#F4EBD8` | `244, 235, 216` | Tipografia principal sobre fundos escuros |
| `--error` | ![#F08B80](https://img.shields.io/badge/-%23F08B80-F08B80?style=flat-square) | **Alerta de Erro** | `#F08B80` | `240, 139, 128` | Mensagens de validação e perda de recursos |

### Tipografia e Tokens Base

* **Títulos (`--title`):** `Cinzel` (fallback `Georgia, serif`), peso `700`, escala de `40–48 px` (desktop) e `28 px` (títulos de cena).
* **Corpo e Controles (`--body`):** `Montserrat` (fallback `Arial, sans-serif`), `16–18 px` com entrelinha `1.6` e limite de largura de leitura em `1200 px`.

<details>
<summary><strong>Ver Tokens CSS de Referência</strong></summary>

```css
:root {
  --bg: #121212;
  --surface: #1A4D34;
  --gold: #D49934;
  --bronze: #8B621B;
  --jade: #3DA66B;
  --sand: #B5A577;
  --stone: #545454;
  --info: #248799;
  --text: #F4EBD8;
  --error: #F08B80;
  --radius: 8px;
  --gap: 16px;
  --title: 'Cinzel', Georgia, serif;
  --body: 'Montserrat', Arial, sans-serif;
}
```

</details>

---

## Arquitetura e Estrutura de Pastas

O projeto utiliza arquitetura PHP estruturada com separação entre núcleo de conexão/segurança (`includes/`), camada de apresentação (`css/`, `img/`) e controladores de página na raiz:

```text
mayab/
├── .antigravity/              # Diretrizes técnicas e especificações do jogo para agentes
├── includes/
│   ├── conexao.php            # Instância PDO (utf8mb4), session_start() e helper h()
│   ├── protege.php            # Middleware de autenticação, eh_admin() e exige_admin()
│   ├── topo.php               # Cabeçalho HTML5, carregamento de fontes/CSS e menu dinâmico
│   └── rodape.php             # Encerramento estrutural do documento HTML
├── css/
│   └── style.css              # Implementação dos tokens visuais, grid responsivo e componentes
├── img/                       # Logotipo, cenários (scene-01 a scene-12) e ícones SVG
├── banco.sql                  # Script DDL/DML completo (tabelas, FKs, usuários e 12 cenas)
├── index.php                  # Tela inicial autenticada e início de nova expedição
├── cadastro.php               # Registro de jogador com hash de senha e login automático
├── login.php                  # Autenticação via password_verify() e regeneração de sessão
├── logout.php                 # Encerramento seguro da sessão
├── jogo.php                   # Motor da partida: leitura de cena, validação de escolha e cálculo
├── ranking.php                # Top 10 pontuações gerais e histórico pessoal (últimas 5)
├── admin.php                  # Painel CRUD de cenas e escolhas (restrito ao administrador)
└── README.md                  # Documentação técnica e guia de execução
```

---

## Regras de Negócio e Pontuação

### Mecânica da Partida (`jogo.php`)

| Etapa | Regra Implementada no Servidor (PHP) |
| :--- | :--- |
| **1. Inicialização** | A partida inicia na **Cena 1 (`O Despertar`)** com `10` pontos de vida e `0` moedas armazenados em `$_SESSION['partida']`, aplicando redirecionamento PRG (`header('Location: jogo.php')`). |
| **2. Validação Anti-Trapaça** | Ao submeter uma escolha, o servidor consulta `SELECT * FROM escolhas WHERE id = ? AND cena_id = ?` usando o `cena_id` atual da sessão. IDs manipulados ou de outras cenas são ignorados. |
| **3. Atualização de Recursos** | Os valores de `efeito_vida` e `efeito_moedas` da escolha válida são somados ao estado atual da sessão e o jogador avança para `destino_id`. |
| **4. Condição de Encerramento** | A aventura termina imediatamente se `vida <= 0` ou quando a cena de destino possui `tipo` igual a `final_bom` ou `final_ruim`. |
| **5. Persistência Única** | No momento em que `fim` torna-se verdadeiro, o PHP calcula os pontos e executa um único `INSERT` na tabela `partidas`, bloqueando novas submissões ou duplicação ao recarregar (`F5`). |

### Fórmula de Pontuação

| Desfecho da Partida | Condição | Fórmula Calculada no PHP |
| :--- | :--- | :--- |
| **Final Bom** | `tipo === 'final_bom'` e `vida > 0` | `500 + (moedas * 10) + (vida * 20)` |
| **Final Ruim ou Derrota** | `tipo === 'final_ruim'` ou `vida <= 0` | `max(0, moedas * 5)` |

<details>
<summary><strong>Ver Modelagem Relacional do Banco de Dados (<code>mayab</code>)</strong></summary>

* **`usuarios`:** `id`, `nome`, `apelido` (único), `email` (único), `senha` (`password_hash`), `perfil` (`ENUM('admin', 'jogador')`).
* **`cenas`:** `id`, `titulo`, `texto`, `imagem`, `tipo` (`ENUM('normal', 'final_bom', 'final_ruim')`).
* **`escolhas`:** `id`, `cena_id` (FK `cenas.id`), `texto`, `destino_id` (FK `cenas.id`), `efeito_vida`, `efeito_moedas`.
* **`partidas`:** `id`, `usuario_id` (FK `usuarios.id`), `cena_final_id` (FK `cenas.id`), `vida_final`, `moedas_final`, `pontos`, `jogada_em`.

</details>

---

## Instalação e Execução

### Pré-requisitos
* **XAMPP** (Apache + MySQL/MariaDB + phpMyAdmin) com **PHP 8.0 ou superior**.

### Passo a Passo

1. **Inicie os serviços:** Abra o painel de controle do **XAMPP** e inicie os módulos **Apache** e **MySQL**.
2. **Posicione os arquivos:** Clone ou copie a pasta do projeto para o diretório público do servidor local:
   ```bash
   C:\xampp\htdocs\mayab
   ```
3. **Importe o banco de dados:**
   * Acesse `http://localhost/phpmyadmin`.
   * Clique na aba **SQL** (ou **Importar**), carregue o arquivo `banco.sql` localizado na raiz do projeto e clique em **Executar**.
   * O script criará automaticamente o banco `mayab` (`utf8mb4`), todas as tabelas, os usuários de teste e as 12 cenas iniciais.
4. **Acesse a aplicação:** Abra o navegador e acesse:
   ```text
   http://localhost/mayab
   ```

---

## Equipe e Perfis de Acesso

### Usuários de Teste (`banco.sql`)

| Perfil | E-mail | Senha | Permissões |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@jogo.com` | `admin123` | Acesso total ao jogo, ranking e painel `admin.php` (CRUD de cenas e escolhas) |
| **Jogador** | `jogador@jogo.com` | `jogador123` | Acesso ao jogo (`index.php`, `jogo.php`) e visualização de `ranking.php` |

### Integrantes do Projeto

| Integrante | Turma | Frente de Atuação |
| :--- | :--- | :--- |
| **Rafael de Oliveira Pereira** | DS A | Arquitetura do banco (`banco.sql`), autenticação, segurança e painel administrativo |
| **Integrante 2** | DS A | Requisitos, modelagem de dados e testes de acesso/administração |
| **Macedo** | DS B | Motor do jogo (`jogo.php`), cálculo de pontuação, ranking e integração visual |
| **Integrante 4** | DS B | Estilização CSS, responsividade, captura de telas e testes funcionais |
| **Integrante 5** | Multimídia | Identidade visual, manual de marca, logotipo e ilustrações das cenas |
| **Integrante 6** | Multimídia | Tipografia, direção de arte, roteiro e edição do vídeo pitch (30s) |

<div align="center">
  <br/>
  <img src="https://capsule-render.vercel.app/api?type=waving&color=0:121212,50:1A4D34,100:D49934&height=110&section=footer" width="100%" alt="Rodapé MAYAB"/>
</div>