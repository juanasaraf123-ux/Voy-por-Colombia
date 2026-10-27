/* Voy por Colombia · interacciones del sitio público (sin dependencias). */
(function () {
  'use strict';
  document.documentElement.classList.remove('no-js');

  const CONFIG = JSON.parse(document.getElementById('app-config')?.textContent || '{}');
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const icon = (name, cls = 'icon') => `<svg class="${cls}" aria-hidden="true"><use href="${CONFIG.icons}#${name}"></use></svg>`;

  // ---------- Almacenamiento local seguro ----------
  const store = {
    get(key, fallback) {
      try { const v = JSON.parse(localStorage.getItem(key)); return v ?? fallback; } catch (e) { return fallback; }
    },
    set(key, value) {
      try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* almacenamiento no disponible */ }
    },
  };

  // ---------- Notificaciones ----------
  function toast(message, ic = 'check') {
    const box = $('[data-toasts]');
    if (!box) return;
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = icon(ic) + '<span>' + esc(message) + '</span>';
    box.appendChild(t);
    setTimeout(() => { t.classList.add('is-leaving'); setTimeout(() => t.remove(), 400); }, 2600);
  }

  // ---------- Favoritos (en el navegador del visitante) ----------
  const Favs = {
    key: 'vpc:favoritos',
    all() { return store.get(this.key, []).filter(Number.isInteger); },
    has(id) { return this.all().includes(id); },
    toggle(id) {
      let list = this.all();
      const on = !list.includes(id);
      list = on ? [id, ...list] : list.filter((x) => x !== id);
      store.set(this.key, list.slice(0, 60));
      this.paint();
      document.dispatchEvent(new CustomEvent('favs:change'));
      return on;
    },
    paint() {
      const list = this.all();
      $$('[data-fav]').forEach((b) => {
        const on = list.includes(Number(b.dataset.fav));
        b.classList.toggle('is-fav', on);
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
      $$('[data-fav-count]').forEach((c) => { c.textContent = list.length; c.classList.toggle('has-items', list.length > 0); });
    },
  };

  // ---------- Ruta personal ----------
  const MyRoute = {
    key: 'vpc:ruta',
    all() { return store.get(this.key, []).filter(Number.isInteger); },
    has(id) { return this.all().includes(id); },
    set(list) {
      store.set(this.key, [...new Set(list)].slice(0, 25));
      this.paint();
      document.dispatchEvent(new CustomEvent('route:change'));
    },
    toggle(id) {
      const list = this.all();
      const on = !list.includes(id);
      this.set(on ? [...list, id] : list.filter((x) => x !== id));
      return on;
    },
    paint() {
      const list = this.all();
      $$('[data-route-count]').forEach((c) => { c.textContent = list.length; c.classList.toggle('has-items', list.length > 0); });
      $$('[data-route-toggle]').forEach((b) => {
        const on = list.includes(Number(b.dataset.routeToggle));
        b.classList.toggle('is-in', on);
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
        const label = b.querySelector('[data-label]');
        if (label) label.textContent = on ? 'En mi ruta' : 'Agregar a mi ruta';
        const ic = b.querySelector('use');
        if (ic) ic.setAttribute('href', CONFIG.icons + '#' + (on ? 'check' : 'plus'));
      });
    },
  };

  document.addEventListener('click', (ev) => {
    const fav = ev.target.closest('[data-fav]');
    if (fav) {
      ev.preventDefault();
      ev.stopPropagation();
      const on = Favs.toggle(Number(fav.dataset.fav));
      fav.classList.remove('pop'); void fav.offsetWidth; fav.classList.add('pop');
      toast(on ? 'Guardado en tus favoritos' : 'Eliminado de favoritos', on ? 'heart-fill' : 'heart');
      return;
    }
    const rt = ev.target.closest('[data-route-toggle]');
    if (rt) {
      ev.preventDefault();
      ev.stopPropagation();
      const on = MyRoute.toggle(Number(rt.dataset.routeToggle));
      toast(on ? 'Agregado a tu ruta' : 'Quitado de tu ruta', 'route');
    }
  });

  // ---------- Encabezado ----------
  const header = $('[data-header]');
  let lastY = 0;
  function onScroll() {
    const y = window.scrollY;
    if (header) {
      header.classList.toggle('is-scrolled', y > 40);
      header.classList.toggle('is-hidden', y > 500 && y > lastY + 4 && !document.body.classList.contains('page-tourism'));
      if (y < lastY - 4) header.classList.remove('is-hidden');
    }
    lastY = y;
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // ---------- Menú móvil ----------
  const menu = $('[data-menu]');
  const menuBtn = $('[data-menu-open]');
  function setMenu(open) {
    if (!menu) return;
    menu.classList.toggle('is-open', open);
    menu.setAttribute('aria-hidden', open ? 'false' : 'true');
    menuBtn?.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) setTimeout(() => $('[data-menu-close]')?.focus(), 200);
  }
  menuBtn?.addEventListener('click', () => setMenu(true));
  $('[data-menu-close]')?.addEventListener('click', () => setMenu(false));

  // ---------- Buscador global con resultados en vivo ----------
  const overlay = $('[data-search]');
  const input = $('[data-search-input]');
  const results = $('[data-search-results]');
  const hintHtml = results?.innerHTML || '';
  let searchTimer = null;
  let activeIndex = -1;
  let lastFocus = null;
  function openSearch() {
    if (!overlay) return;
    setMenu(false);
    lastFocus = document.activeElement;
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    setTimeout(() => input?.focus(), 60);
  }
  function closeSearch() {
    overlay?.classList.remove('is-open');
    overlay?.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    lastFocus?.focus?.();
  }
  $$('[data-search-open]').forEach((b) => b.addEventListener('click', openSearch));
  $('[data-search-close]')?.addEventListener('click', closeSearch);
  overlay?.addEventListener('click', (e) => { if (e.target === overlay) closeSearch(); });
  results?.addEventListener('click', (e) => {
    const s = e.target.closest('[data-search-suggest]');
    if (s) { input.value = s.dataset.searchSuggest; runSearch(); input.focus(); }
  });
  async function runSearch() {
    const q = input.value.trim();
    activeIndex = -1;
    if (q.length < 2) { results.innerHTML = hintHtml; return; }
    results.innerHTML = '<div class="search-hint">Buscando…</div>';
    try {
      const r = await fetch(CONFIG.api + '/buscar?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
      const data = await r.json();
      if (!data.results.length) {
        results.innerHTML = `<div class="search-hint">No encontramos resultados para «${esc(q)}». Prueba con un departamento, municipio o tipo de experiencia.</div>`;
        return;
      }
      results.innerHTML = data.results.map((it) => `
        <a class="search-item" href="${esc(it.url)}">
          ${it.thumb ? `<img class="thumb" src="${esc(it.thumb)}" alt="" loading="lazy">` : `<span class="thumb">${icon(it.icon || 'pin')}</span>`}
          <span><strong>${esc(it.title)}</strong><small>${esc(it.subtitle || '')}</small></span>
        </a>`).join('') + `<div class="search-all"><a class="link-arrow" href="${CONFIG.base.replace(/\/$/, '')}/buscar?q=${encodeURIComponent(q)}">Ver todos los resultados ${icon('arrow-right')}</a></div>`;
    } catch (e) {
      results.innerHTML = '<div class="search-hint">No fue posible buscar en este momento. Inténtalo de nuevo.</div>';
    }
  }
  input?.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(runSearch, 220); });
  input?.addEventListener('keydown', (e) => {
    const items = $$('.search-item', results);
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!items.length) return;
      activeIndex = (activeIndex + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
      items.forEach((it, i) => it.classList.toggle('is-active', i === activeIndex));
      items[activeIndex].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
      e.preventDefault();
      window.location.href = items[activeIndex].href;
    }
  });
  document.addEventListener('keydown', (e) => {
    const typing = /INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName || '');
    if (e.key === '/' && !typing) { e.preventDefault(); openSearch(); }
    if (e.key === 'Escape') {
      if (overlay?.classList.contains('is-open')) closeSearch();
      if (menu?.classList.contains('is-open')) setMenu(false);
    }
  });

  // ---------- Aparición progresiva ----------
  const revealEls = $$('.reveal, .reveal-scale');
  if ('IntersectionObserver' in window && !reduceMotion) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); } });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  }

  // ---------- Contadores ----------
  $$('[data-count]').forEach((el) => {
    const target = Number(el.dataset.count) || 0;
    if (reduceMotion || target < 2) return;
    const start = performance.now();
    const dur = 1600;
    el.textContent = '0';
    const step = (t) => {
      const p = Math.min(1, (t - start) / dur);
      el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('es-CO');
      if (p < 1) requestAnimationFrame(step);
    };
    setTimeout(() => requestAnimationFrame(step), 600);
  });

  // ---------- Portada: diapositivas y palabras rotativas ----------
  const hero = $('[data-hero]');
  if (hero) {
    const slides = $$('.hero-slide', hero);
    const dots = $$('[data-hero-dot]', hero);
    const credit = $('[data-hero-credit]', hero);
    let idx = 0;
    let timer = null;
    const show = (n) => {
      idx = (n + slides.length) % slides.length;
      slides.forEach((s, i) => s.classList.toggle('is-active', i === idx));
      dots.forEach((d, i) => d.classList.toggle('is-active', i === idx));
      if (credit) credit.textContent = slides[idx]?.dataset.credit || '';
    };
    const play = () => { clearInterval(timer); if (slides.length > 1 && !reduceMotion) timer = setInterval(() => show(idx + 1), 7000); };
    dots.forEach((d) => d.addEventListener('click', () => { show(Number(d.dataset.heroDot)); play(); }));
    show(0);
    play();
    document.addEventListener('visibilitychange', () => (document.hidden ? clearInterval(timer) : play()));
  }
  const rot = $('[data-rotator]');
  if (rot && !reduceMotion) {
    const words = $$('span', rot);
    let i = 0;
    setInterval(() => {
      const cur = words[i];
      i = (i + 1) % words.length;
      cur.classList.remove('is-in'); cur.classList.add('is-out');
      words[i].classList.remove('is-out'); words[i].classList.add('is-in');
      setTimeout(() => cur.classList.remove('is-out'), 700);
    }, 2200);
  }

  // ---------- Carruseles ----------
  $$('[data-rail-prev], [data-rail-next]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const name = btn.dataset.railPrev || btn.dataset.railNext;
      const rail = $(`[data-rail="${name}"]`);
      if (!rail) return;
      const card = rail.firstElementChild;
      const step = card ? card.getBoundingClientRect().width + 20 : 320;
      rail.scrollBy({ left: btn.dataset.railPrev ? -step : step, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  });

  // ---------- Filtros que se envían solos ----------
  $$('[data-autosubmit]').forEach((sel) => sel.addEventListener('change', () => {
    const form = sel.form;
    if (sel.dataset.reset && form.elements[sel.dataset.reset]) form.elements[sel.dataset.reset].value = '';
    const page = form.querySelector('[name="pagina"]');
    if (page) page.value = '';
    form.requestSubmit ? form.requestSubmit() : form.submit();
  }));

  // ---------- Visor de imágenes y videos ----------
  const Lightbox = (() => {
    const lb = $('[data-lightbox]');
    if (!lb) return { open() {} };
    const stage = $('[data-lb-stage]', lb);
    const cap = $('[data-lb-caption]', lb);
    const count = $('[data-lb-count]', lb);
    let items = [];
    let i = 0;
    let prevFocus = null;
    function render() {
      const it = items[i];
      if (!it) return;
      if (it.kind === 'audio' && it.src) {
        stage.innerHTML = `<div class="audio-stage">${icon('volume', 'icon audio-ico')}<audio src="${esc(it.src)}" controls autoplay></audio></div>`;
      } else if (it.kind === 'embed' && it.embed) {
        stage.innerHTML = `<div class="video-frame"><iframe src="${esc(it.embed)}" title="${esc(it.title || 'Video')}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>`;
      } else if (it.kind === 'video' && it.src) {
        stage.innerHTML = `<div class="video-frame"><video src="${esc(it.src)}" controls autoplay playsinline></video></div>`;
      } else {
        stage.innerHTML = `<img src="${esc(it.large || it.medium || it.thumb)}" alt="${esc(it.alt || it.title || '')}">`;
      }
      cap.innerHTML = `${it.title ? `<strong>${esc(it.title)}</strong>` : ''}${it.caption ? `<p>${esc(it.caption)}</p>` : ''}${it.credit ? `<small>${esc(it.credit)}${it.license ? ' · ' + esc(it.license) : ''}</small>` : ''}`;
      count.textContent = items.length > 1 ? `${i + 1} / ${items.length}` : '';
      $('[data-lb-prev]', lb).hidden = $('[data-lb-next]', lb).hidden = items.length < 2;
    }
    function open(list, start = 0) {
      items = list; i = start; prevFocus = document.activeElement;
      render();
      lb.classList.add('is-open'); lb.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      $('[data-lb-close]', lb).focus();
    }
    function close() {
      lb.classList.remove('is-open'); lb.setAttribute('aria-hidden', 'true');
      stage.innerHTML = '';
      document.body.style.overflow = '';
      prevFocus?.focus?.();
    }
    const go = (d) => { i = (i + d + items.length) % items.length; render(); };
    $('[data-lb-close]', lb).addEventListener('click', close);
    $('[data-lb-prev]', lb).addEventListener('click', () => go(-1));
    $('[data-lb-next]', lb).addEventListener('click', () => go(1));
    lb.addEventListener('click', (e) => { if (e.target === lb || e.target === stage) close(); });
    document.addEventListener('keydown', (e) => {
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowLeft') go(-1);
      if (e.key === 'ArrowRight') go(1);
    });
    let sx = null;
    lb.addEventListener('touchstart', (e) => { sx = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', (e) => { if (sx === null) return; const dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 50) go(dx < 0 ? 1 : -1); sx = null; });
    return { open };
  })();

  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-lightbox-item]');
    if (!el) return;
    e.preventDefault();
    const group = el.closest('[data-gallery-group]') || el.parentElement;
    const els = $$('[data-lightbox-item]', group);
    const items = els.map((x) => { try { return JSON.parse(x.dataset.lightboxItem); } catch (er) { return null; } }).filter(Boolean);
    Lightbox.open(items, Math.max(0, els.indexOf(el)));
  });

  // ---------- Multimedia de un lugar en el visor (sin salir del mapa) ----------
  document.addEventListener('click', async (e) => {
    const b = e.target.closest('[data-media-open]');
    if (!b) return;
    e.preventDefault();
    const kind = b.dataset.mediaOpen;
    try {
      const r = await fetch(CONFIG.api + '/publicaciones/' + encodeURIComponent(b.dataset.entry) + '/multimedia', { headers: { Accept: 'application/json' } });
      const data = await r.json();
      const list = kind === 'video' ? data.videos : kind === 'audio' ? data.audios : data.images;
      if (!list || !list.length) {
        toast(kind === 'video' ? 'Este lugar aún no tiene videos' : kind === 'audio' ? 'Este lugar aún no tiene audios' : 'Este lugar aún no tiene fotos', 'info');
        return;
      }
      Lightbox.open(list, 0);
    } catch (er) {
      toast('No fue posible cargar la multimedia', 'alert');
    }
  });

  // ---------- Compartir ----------
  document.addEventListener('click', async (e) => {
    const b = e.target.closest('[data-share]');
    if (!b) return;
    const url = b.dataset.shareUrl || window.location.href;
    const title = b.dataset.share || document.title;
    if (navigator.share) {
      try { await navigator.share({ title, url }); } catch (er) { /* cancelado */ }
    } else {
      try { await navigator.clipboard.writeText(url); toast('Enlace copiado'); } catch (er) { window.prompt('Copia este enlace:', url); }
    }
  });

  // ---------- Tema claro / oscuro ----------
  const paintTheme = () => {
    const cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    $$('[data-theme-set]').forEach((b) => b.setAttribute('aria-pressed', b.dataset.themeSet === cur ? 'true' : 'false'));
  };
  $$('[data-theme-set]').forEach((b) => b.addEventListener('click', () => {
    document.documentElement.setAttribute('data-theme', b.dataset.themeSet);
    store.set('vpc:tema', b.dataset.themeSet);
    paintTheme();
  }));
  paintTheme();

  // ---------- Tira de destinos destacados ----------
  const strip = $('[data-strip]');
  if (strip) {
    const dots = $('[data-strip-dots]');
    const count = $('[data-strip-count]');
    const cards = strip.children.length;
    const pages = () => Math.max(1, Math.ceil(strip.scrollWidth / strip.clientWidth - 0.05));
    const update = () => {
      const p = pages();
      const cur = Math.min(p, Math.round(strip.scrollLeft / Math.max(1, strip.scrollWidth - strip.clientWidth) * (p - 1)) + 1);
      if (dots) dots.innerHTML = Array.from({ length: Math.min(p, 6) }, (_, i) => `<i class="${i + 1 === cur ? 'on' : ''}"></i>`).join('');
      if (count) {
        const first = Math.round(strip.scrollLeft / ((strip.firstElementChild?.getBoundingClientRect().width || 200) + 14)) + 1;
        count.textContent = `${Math.min(first, cards)} / ${cards}`;
      }
    };
    strip.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
    window.addEventListener('resize', update);
    $('[data-strip-next]')?.addEventListener('click', () => {
      const end = strip.scrollLeft + strip.clientWidth >= strip.scrollWidth - 4;
      strip.scrollTo({ left: end ? 0 : strip.scrollLeft + strip.clientWidth * 0.8, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
    update();
  }

  Favs.paint();
  MyRoute.paint();

  // API mínima para otros scripts (mapa, favoritos, ruta).
  window.VPC = { CONFIG, Favs, MyRoute, toast, icon, esc, Lightbox, store };
  document.dispatchEvent(new CustomEvent('vpc:ready'));
})();
