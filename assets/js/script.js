(() => {
  'use strict';
  document.documentElement.classList.add('js');

  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];

  /* ---------- Navegação suave ---------- */
  const goTo = (id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'start' });
  };

  $('#btn-start')?.addEventListener('click', () => goTo('sobre'));

  /* ---------- Scroll reveal ---------- */
  const revealEls = $$('.reveal');
  if (revealEls.length) {
    if ('IntersectionObserver' in window && !reduced) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add('is-visible');
            io.unobserve(e.target);
          }
        });
      }, { threshold: 0.15 });
      revealEls.forEach((el) => io.observe(el));
    } else {
      revealEls.forEach((el) => el.classList.add('is-visible'));
    }
  }

  /* ---------- Seleção visual de cards de personagens ---------- */
  const charCards = $$('.char-card');
  if (charCards.length) {
    const selectCard = (target) => {
      charCards.forEach((card) => {
        const active = card === target;
        card.classList.toggle('is-selected', active);
        card.setAttribute('aria-pressed', String(active));
      });
    };

    charCards.forEach((card) => {
      card.addEventListener('click', () => selectCard(card));
      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          selectCard(card);
        }
      });
    });
  }

  /* ---------- Feedback visual do botão Google (apenas frontend) ---------- */
  const googleBtn = $('#btn-google-auth');
  const googleStatus = $('#google-auth-status');
  if (googleBtn && googleStatus) {
    googleBtn.addEventListener('click', () => {
      googleStatus.textContent = 'Login com Google disponível em breve. Utilize seu e-mail e senha para acessar.';
    });
  }

  /* ---------- Prevenção visual de duplo clique em formulários do jogo ---------- */
  $$('.js-single-submit').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (form.dataset.submitting === '1') {
        e.preventDefault();
        return;
      }
      form.dataset.submitting = '1';
      setTimeout(() => {
        $$('button[type="submit"]', form).forEach((btn) => {
          btn.disabled = true;
        });
      }, 0);
    });
  });

  /* ---------- Introdução cinematográfica em vídeo (jogo.php) ---------- */
  const introCinema = $('#intro-cinema');
  const introVideo = $('#intro-video');

  if (introCinema && introVideo) {
    let finalizado = false;
    let startupTimer = null;
    let safetyEndTimer = null;

    const limparTimers = () => {
      if (startupTimer !== null) {
        clearTimeout(startupTimer);
        startupTimer = null;
      }
      if (safetyEndTimer !== null) {
        clearTimeout(safetyEndTimer);
        safetyEndTimer = null;
      }
    };

    const finalizarIntro = (imediato = false) => {
      if (finalizado) return;
      finalizado = true;
      limparTimers();

      try {
        introVideo.pause();
      } catch (_) {
        // Ignora erro ao pausar vídeo em caso de falha de mídia
      }

      document.body.classList.remove('intro-video-active');

      const removerCamada = () => {
        introCinema.remove();
        // Garantia extra: remove classe de body caso ainda esteja presente
        document.body.classList.remove('intro-video-active');
        // Restaura scroll do body (bloqueado durante o overlay)
        document.body.style.overflow = '';
        // Rola suavemente até a área de gameplay
        const gameArea = document.querySelector('.game-wrap');
        if (gameArea) {
          gameArea.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'start' });
        }
        // Foco acessível no título da cena
        const tituloCena = $('.scene-header h1');
        if (tituloCena) {
          tituloCena.setAttribute('tabindex', '-1');
          tituloCena.focus({ preventScroll: true });
        }
      };

      if (imediato || reduced) {
        removerCamada();
        return;
      }

      introCinema.classList.add('is-closing');
      let removido = false;
      const concluir = () => {
        if (removido) return;
        removido = true;
        removerCamada();
      };
      introCinema.addEventListener('transitionend', concluir, { once: true });
      setTimeout(concluir, 420);
    };

    const agendarLimiteDuracao = () => {
      if (safetyEndTimer !== null) return;
      if (!Number.isFinite(introVideo.duration) || introVideo.duration <= 0) {
        finalizarIntro(true);
        return;
      }
      const limiteMs = Math.ceil((introVideo.duration + 8) * 1000);
      safetyEndTimer = setTimeout(() => finalizarIntro(false), limiteMs);
    };

    introVideo.loop = false;
    introVideo.controls = false;

    if (introVideo.error) {
      finalizarIntro(true);
      return;
    }

    if (introVideo.ended) {
      finalizarIntro(true);
      return;
    }

    introVideo.addEventListener('ended', () => finalizarIntro(false), { once: true });
    introVideo.addEventListener('error', () => finalizarIntro(true), { once: true });
    $$('source', introVideo).forEach((srcEl) => {
      srcEl.addEventListener('error', () => finalizarIntro(true), { once: true });
    });

    if (introVideo.readyState >= 1) {
      agendarLimiteDuracao();
    } else {
      introVideo.addEventListener('loadedmetadata', agendarLimiteDuracao, { once: true });
    }

    introVideo.addEventListener('playing', () => {
      if (startupTimer !== null) {
        clearTimeout(startupTimer);
        startupTimer = null;
      }
    }, { once: true });

    // Watchdog caso o navegador trave o carregamento ou bloqueie reprodução
    startupTimer = setTimeout(() => {
      if (introVideo.paused || introVideo.readyState < 2) {
        finalizarIntro(true);
      }
    }, 8000);

    const iniciarReproducao = async () => {
      try {
        introVideo.muted = false;
        await introVideo.play();
      } catch (_) {
        // Fallback para política de autoplay com áudio bloqueado: tenta mudo
        try {
          introVideo.muted = true;
          await introVideo.play();
        } catch (__) {
          finalizarIntro(true);
        }
      }
    };

    iniciarReproducao();

    // Botão "Pular vídeo": chama finalizarIntro() apenas uma vez (once: true)
    const skipBtn = document.getElementById('intro-skip');
    if (skipBtn) {
      skipBtn.addEventListener('click', () => finalizarIntro(false), { once: true });
    }
  } else {
    document.body.classList.remove('intro-video-active');
  }
})();
