<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';
$usuarioLogado = !empty($_SESSION['usuario_id']);
$destinoPortal = $usuarioLogado ? 'jogo.php' : 'login.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MAYAB: O Templo Perdido</title>
  <meta name="description" content="Em 2026, uma relíquia maia abre um portal para o passado. Explore o templo.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/base/global.css">
  <link rel="stylesheet" href="assets/css/components/components.css">
  <link rel="stylesheet" href="assets/css/pages/home.css">
</head>
<body>
  <a class="skip" href="#sobre">Ir para o conteúdo</a>

  <!-- SEÇÃO 01 · Hero -->
  <header class="hero" id="inicio">
    <div class="hero__inner">
      <img class="hero__logo" src="assets/img/logo.png" alt="MAYAB: O Templo Perdido, com a cabeça dourada de Kukulcán" width="1024" height="290">
      <p class="hero__intro">
        Em 2026, uma expedição arqueológica em Yucatán desperta um portal maia adormecido sob a selva. Decifre glifos ancestrais e sobreviva aos segredos de Kukulcán.
      </p>
      <button class="btn btn--glow" type="button" id="btn-start">Clique para começar</button>
    </div>
  </header>

  <main>
    <!-- SEÇÃO 02 · Sobre o jogo -->
    <section class="landing-section" id="sobre" aria-labelledby="t-sobre">
      <div class="container">
        <article class="card about-card reveal" id="card-sobre">
          <h2 id="t-sobre">Sobre o jogo</h2>
          <div class="about-card__text">
            <p>Em 2026, uma expedição arqueológica em Yucatán encontra uma relíquia esquecida sob as raízes de um templo coberto pela selva.</p>
            <p>Ao ser tocada, a relíquia abre um portal temporal para o passado maia. Do outro lado, cidades vivas, rituais e enigmas esperam por quem ousar atravessar.</p>
            <p>Decifre os glifos, sobreviva ao templo e descubra o que Kukulcán guarda.</p>
          </div>
        </article>
      </div>  
    </section>

    <!-- SEÇÃO 03 · Personagens -->
    <section class="landing-section landing-section--alt" id="personagens" aria-labelledby="t-pers">
      <div class="container">
        <div class="section-head reveal">
          <h2 id="t-pers">Personagens</h2>
          <p>Escolha o perfil ideal para enfrentar as armadilhas, enigmas e relíquias ocultas no coração do templo.</p>
        </div>

        <div class="char-grid">
          <article class="card card--interactive char-card reveal is-selected" data-char="explorador" tabindex="0" aria-pressed="true">
            <div class="char-card__media char-card__media--explorador">
              <img class="char-card__img" src="assets/img/Explorador.jpeg" alt="Explorador: aventureira de campo preparada para travessia na selva" width="2400" height="1792" loading="lazy">
            </div>
            <div class="char-card__body">
              <div class="char-card__top">
                <h3>Explorador</h3>
                <span class="badge">Travessia &amp; Ação</span>
              </div>
              <p class="char-card__desc">Aventureiro de campo, ágil e curioso. Abre caminho pela selva e encontra passagens que os mapas não mostram.</p>
              <p class="char-card__skill">Habilidade: orientação e travessia de terreno</p>
            </div>
          </article>

          <article class="card card--interactive char-card reveal" data-char="estudante" tabindex="0" aria-pressed="false">
            <div class="char-card__media char-card__media--estudante">
              <img class="char-card__img" src="assets/img/Estudante.jpeg" alt="Estudante: jovem pesquisador com caderno de glifos maias" width="2400" height="1792" loading="lazy">
            </div>
            <div class="char-card__body">
              <div class="char-card__top">
                <h3>Estudante</h3>
                <span class="badge badge--turquoise">Glifos &amp; Enigmas</span>
              </div>
              <p class="char-card__desc">Jovem pesquisador de história maia. Sabe ler glifos e conecta pistas que parecem não ter relação.</p>
              <p class="char-card__skill">Habilidade: decifrar glifos e enigmas</p>
            </div>
          </article>

          <article class="card card--interactive char-card reveal" data-char="arqueologo" tabindex="0" aria-pressed="false">
            <div class="char-card__media char-card__media--arqueologo">
              <img class="char-card__img" src="assets/img/Arque%C3%B3logo.jpeg" alt="Arqueólogo: líder experiente da expedição segurando uma relíquia" width="2400" height="1792" loading="lazy">
            </div>
            <div class="char-card__body">
              <div class="char-card__top">
                <h3>Arqueólogo</h3>
                <span class="badge badge--gold">Relíquias &amp; Liderança</span>
              </div>
              <p class="char-card__desc">Líder da expedição, com anos de escavação. Reconhece a origem das relíquias e o perigo que elas carregam.</p>
              <p class="char-card__skill">Habilidade: identificar e manusear relíquias</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- SEÇÃO 04 · Chamada do jogo -->
    <section class="call-section" id="chamada" aria-labelledby="t-call">
      <div class="container">
        <div class="card card--call reveal">
          <h2 id="t-call">Chamada do jogo</h2>
          <p class="call__quote">O portal está aberto. O passado não vai esperar.</p>
          <p class="call__sub">Prepare sua expedição, faça suas escolhas e teste sua sobrevivência nas ruínas de Mayab.</p>
          <div class="call__actions">
            <a href="<?= h($destinoPortal) ?>" class="btn btn--glow" id="btn-explore"><?= $usuarioLogado ? 'Jogar Agora' : 'Entrar no Portal' ?></a>
            <a href="ranking.php" class="btn btn--secondary">Ver Ranking</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-container">
      <p>&copy; <?= date('Y') ?> MAYAB: O Templo Perdido. Todos os direitos reservados.</p>
    </div>
  </footer>

  <script src="assets/js/script.js"></script>
</body>
</html>
