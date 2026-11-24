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
  <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
  <a class="skip" href="#sobre">Ir para o conteúdo</a>
  <!-- SEÇÃO 01 · Hero -->
  <header class="hero" id="inicio">
    <div class="hero__inner">
      <img class="hero__logo" src="assets/img/logo.png" alt="MAYAB: O Templo Perdido, com a cabeça dourada de Kukulcán" width="1024" height="290">
      <button class="btn btn--glow" type="button" id="btn-start">Clique para começar</button>
    </div>
  </header>

  <main>
    <!-- SEÇÃO 02 · Sobre, Personagens e Chamada -->
    <section class="info" id="sobre" aria-labelledby="t-sobre">
      <div class="info__grid">
        <article class="card reveal" id="card-sobre">
          <h2 id="t-sobre">Sobre o jogo</h2>
          <p>Em 2026, uma expedição arqueológica em Yucatán encontra uma relíquia esquecida sob as raízes de um templo coberto pela selva.</p>
          <p>Ao ser tocada, a relíquia abre um portal temporal para o passado maia. Do outro lado, cidades vivas, rituais e enigmas esperam por quem ousar atravessar.</p>
          <p>Decifre os glifos, sobreviva ao templo e descubra o que Kukulcán guarda.</p>
        </article>

        <div class="info__side">
          <section class="card reveal" aria-labelledby="t-pers">
            <h2 id="t-pers">Personagens</h2>
            <div class="tabs" role="tablist" aria-label="Escolha um personagem">
              <button class="tab" role="tab" id="tab-explorador" aria-selected="true" aria-controls="painel-pers" data-char="explorador" type="button">Explorador</button>
              <button class="tab" role="tab" id="tab-estudante" aria-selected="false" aria-controls="painel-pers" data-char="estudante" tabindex="-1" type="button">Estudante</button>
              <button class="tab" role="tab" id="tab-arqueologo" aria-selected="false" aria-controls="painel-pers" data-char="arqueologo" tabindex="-1" type="button">Arqueólogo</button>
            </div>
            <div class="char-panel" id="painel-pers" role="tabpanel" aria-live="polite" aria-labelledby="tab-explorador">
              <h3 id="char-name"></h3>
              <p id="char-desc"></p>
              <p class="char-skill" id="char-skill"></p>
            </div>
          </section>

          <section class="card card--call reveal" aria-labelledby="t-call">
            <h2 id="t-call">Chamada do jogo</h2>
            <p class="call__quote">O portal está aberto. O passado não vai esperar.</p>
            <button class="btn" type="button" id="btn-explore">Explorar o Templo</button>
          </section>
        </div>
      </div>
    </section>
  </main>

  <script src="assets/js/script.js"></script>
</body>
</html>