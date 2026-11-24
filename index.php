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
            <div class="char-card__media char-card__media--explorador" aria-hidden="true">
              <svg class="char-card__art" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="48" cy="48" r="38" stroke="#3DA66B" stroke-width="2" stroke-dasharray="6 4" opacity=".65"/>
                <circle cx="48" cy="48" r="28" fill="#102A1D" stroke="#D49934" stroke-width="2.5"/>
                <path d="M48 24L55 43L72 48L55 53L48 72L41 53L24 48L41 43L48 24Z" fill="#D49934" opacity=".9"/>
                <circle cx="48" cy="48" r="5" fill="#121212" stroke="#F4EBD8" stroke-width="2"/>
              </svg>
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
            <div class="char-card__media char-card__media--estudante" aria-hidden="true">
              <svg class="char-card__art" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="20" y="18" width="56" height="60" rx="8" fill="#102A1D" stroke="#248799" stroke-width="2.5"/>
                <path d="M32 34H64M32 46H58M32 58H50" stroke="#D49934" stroke-width="3" stroke-linecap="round"/>
                <rect x="34" y="65" width="28" height="6" rx="3" fill="#3DA66B" opacity=".8"/>
                <circle cx="66" cy="28" r="7" fill="#D49934"/>
              </svg>
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
            <div class="char-card__media char-card__media--arqueologo" aria-hidden="true">
              <svg class="char-card__art" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polygon points="48,16 74,34 64,74 32,74 22,34" fill="#102A1D" stroke="#D49934" stroke-width="2.5"/>
                <circle cx="48" cy="48" r="14" stroke="#E8B65B" stroke-width="2.5"/>
                <circle cx="48" cy="48" r="6" fill="#3DA66B"/>
                <path d="M48 24V30M48 66V72M26 48H32M64 48H70" stroke="#D49934" stroke-width="2.5" stroke-linecap="round"/>
              </svg>
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
          <a href="login.php" class="btn btn--glow" id="btn-explore">Entrar no Portal</a>
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
