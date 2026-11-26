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
})();
