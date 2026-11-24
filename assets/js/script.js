(() => {
  'use strict';
  document.documentElement.classList.add('js');

  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];

  /* ---------- Navegação entre seções ---------- */
  const goTo = (id, focusSel) => {
    const el = document.getElementById(id);
    el.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'start' });
    if (focusSel) setTimeout(() => $(focusSel)?.focus({ preventScroll: true }), reduced ? 0 : 600);
  };
  $('#btn-start').addEventListener('click', () => goTo('sobre'));
  $('#btn-explore').addEventListener('click', () => goTo('acesso', '#login-email'));

  /* ---------- Scroll reveal ---------- */
  const revealEls = $$('.reveal');
  if ('IntersectionObserver' in window && !reduced) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
      });
    }, { threshold: 0.15 });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  }

  /* ---------- Personagens ---------- */
  const chars = {
    explorador: {
      nome: 'Explorador',
      desc: 'Aventureiro de campo, ágil e curioso. Abre caminho pela selva e encontra passagens que os mapas não mostram.',
      skill: 'Habilidade: orientação e travessia de terreno'
    },
    estudante: {
      nome: 'Estudante',
      desc: 'Jovem pesquisador de história maia. Sabe ler glifos e conecta pistas que parecem não ter relação.',
      skill: 'Habilidade: decifrar glifos e enigmas'
    },
    arqueologo: {
      nome: 'Arqueólogo',
      desc: 'Líder da expedição, com anos de escavação. Reconhece a origem das relíquias e o perigo que elas carregam.',
      skill: 'Habilidade: identificar e manusear relíquias'
    }
  };
  const tabs = $$('.tab');
  const panel = $('#painel-pers');
  const selectChar = (key, focus = false) => {
    tabs.forEach((t) => {
      const on = t.dataset.char === key;
      t.setAttribute('aria-selected', on);
      t.tabIndex = on ? 0 : -1;
      if (on) { panel.setAttribute('aria-labelledby', t.id); if (focus) t.focus(); }
    });
    const c = chars[key];
    $('#char-name').textContent = c.nome;
    $('#char-desc').textContent = c.desc;
    $('#char-skill').textContent = c.skill;
    if (!reduced) { panel.classList.remove('is-changing'); void panel.offsetWidth; panel.classList.add('is-changing'); }
  };
  tabs.forEach((t, i) => {
    t.addEventListener('click', () => selectChar(t.dataset.char));
    t.addEventListener('keydown', (e) => {
      const map = { ArrowRight: 1, ArrowLeft: -1 };
      if (map[e.key]) { e.preventDefault(); selectChar(tabs[(i + map[e.key] + tabs.length) % tabs.length].dataset.char, true); }
    });
  });
  selectChar('explorador');

  /* ---------- Alternância Entrar / Criar conta ---------- */
  const switches = $$('.switch__btn');
  const forms = { login: $('#form-login'), signup: $('#form-signup') };
  const status = $('#form-status');
  const setMode = (mode, focus = false) => {
    switches.forEach((b) => {
      const on = b.dataset.mode === mode;
      b.setAttribute('aria-selected', on);
      b.tabIndex = on ? 0 : -1;
      if (on && focus) b.focus();
    });
    Object.entries(forms).forEach(([k, f]) => {
      f.hidden = k !== mode;
      if (k === mode && !reduced) { f.classList.remove('is-entering'); void f.offsetWidth; f.classList.add('is-entering'); }
    });
    status.textContent = '';
  };
  switches.forEach((b, i) => {
    b.addEventListener('click', () => setMode(b.dataset.mode));
    b.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        e.preventDefault();
        setMode(switches[(i + 1) % switches.length].dataset.mode, true);
      }
    });
  });

  /* ---------- Validação em tempo real ---------- */
  const rules = {
    nome: (v) => v.trim().length >= 2 || 'Informe seu nome.',
    apelido: (v) => v.trim().length >= 2 || 'Escolha um apelido com pelo menos 2 caracteres.',
    email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || 'Digite um e-mail válido, como nome@exemplo.com.',
    senha: (v) => v.length >= 6 || 'A senha precisa ter no mínimo 6 caracteres.'
  };
  const validate = (input) => {
    const field = input.closest('.field');
    const msg = $('.field__msg', field);
    const res = rules[input.name](input.value);
    const ok = res === true;
    field.classList.toggle('is-invalid', !ok);
    field.classList.toggle('is-valid', ok);
    input.setAttribute('aria-invalid', !ok);
    msg.textContent = ok ? '' : res;
    return ok;
  };
  Object.values(forms).forEach((form) => {
    const inputs = $$('input', form);
    inputs.forEach((inp) => {
      inp.addEventListener('blur', () => validate(inp));
      inp.addEventListener('input', () => {
        if (inp.closest('.field').classList.contains('is-invalid') || inp.closest('.field').classList.contains('is-valid')) validate(inp);
      });
    });
    form.addEventListener('submit', (e) => {
      const results = inputs.map(validate);
      if (results.every(Boolean)) {
        /* Para integrar com PHP: remova este preventDefault para enviar o form
           ao "action", ou use fetch(form.action, { method:'POST', body:new FormData(form) }). */
        e.preventDefault();
        status.textContent = form.id === 'form-login'
          ? 'Dados válidos. Pronto para enviar ao servidor.'
          : 'Conta pronta para ser criada. Pronto para enviar ao servidor.';
      } else {
        e.preventDefault();
        status.textContent = '';
        inputs[results.indexOf(false)].focus();
      }
    });
  });
})();