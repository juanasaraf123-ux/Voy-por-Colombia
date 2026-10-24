/* Voy por Colombia · lógica de páginas específicas (favoritos, mi ruta, galería). */
(function () {
  'use strict';
  const { VPC } = window;
  if (!VPC) return;
  const { CONFIG, esc, icon } = VPC;
  const $ = (s, el = document) => el.querySelector(s);
  const api = (p) => CONFIG.api.replace(/\/$/, '') + p;

  async function fetchEntries(ids) {
    if (!ids.length) return [];
    const r = await fetch(api('/publicaciones?ids=' + ids.join(',')), { headers: { Accept: 'application/json' } });
    return (await r.json()).items || [];
  }

  function cardHtml(m) {
    return `<article class="card" style="--c:${m.color}">
      <div class="card-media">${m.image ? `<img src="${esc(m.image)}" alt="${esc(m.title)}" loading="lazy">` : ''}
        <div class="badges"><span class="badge badge-section">${esc(m.sectionLabel)}</span></div>
        <button class="fav-btn" type="button" data-fav="${m.id}" aria-label="Quitar de favoritos"><span class="off">${icon('heart')}</span><span class="on">${icon('heart-fill')}</span></button></div>
      <div class="card-body"><div class="card-meta">${m.department ? `<span>${icon('pin')} ${esc([m.municipality, m.department].filter(Boolean).join(', '))}</span>` : ''}</div>
        <h3><a href="${esc(m.url)}">${esc(m.title)}</a></h3><p>${esc(m.summary)}</p></div></article>`;
  }

  // ---------- Favoritos ----------
  const favList = $('[data-favorites-list]');
  if (favList) {
    const render = async () => {
      const items = await fetchEntries(VPC.Favs.all());
      favList.innerHTML = items.map(cardHtml).join('');
      $('[data-favorites-empty]').hidden = items.length > 0;
      VPC.Favs.paint();
    };
    render();
    document.addEventListener('favs:change', render);
  }

  // ---------- Usar una ruta curada como ruta personal ----------
  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-route-copy]');
    if (!b) return;
    VPC.MyRoute.set(b.dataset.routeCopy.split(',').map(Number).filter(Boolean));
    VPC.toast('Ruta copiada. Puedes editarla en «Mi ruta».', 'route');
  });

  // ---------- Mi ruta ----------
  const root = $('[data-my-route]');
  if (root) {
    // Ruta compartida por enlace: /mi-ruta?ids=1,2,3
    const shared = new URLSearchParams(location.search).get('ids');
    if (shared) {
      const ids = shared.split(',').map(Number).filter((n) => Number.isInteger(n) && n > 0);
      if (ids.length && window.confirm('¿Quieres cargar la ruta compartida? Reemplazará tu ruta actual.')) VPC.MyRoute.set(ids);
      history.replaceState(null, '', location.pathname);
    }
    const list = $('[data-stop-list]', root);
    const summary = $('[data-route-summary]', root);
    const mapEl = $('[data-route-map]', root);
    let mapApi = null;
    const km = (a, b) => {
      const R = 6371, dLat = ((b.lat - a.lat) * Math.PI) / 180, dLng = ((b.lng - a.lng) * Math.PI) / 180;
      const x = Math.sin(dLat / 2) ** 2 + Math.cos((a.lat * Math.PI) / 180) * Math.cos((b.lat * Math.PI) / 180) * Math.sin(dLng / 2) ** 2;
      return 2 * R * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
    };
    const render = async () => {
      const ids = VPC.MyRoute.all();
      const items = await fetchEntries(ids);
      $('[data-route-empty]', root).hidden = items.length > 0;
      $('[data-route-share]', root).hidden = $('[data-route-clear]', root).hidden = !items.length;
      let total = 0;
      const geo = items.filter((m) => m.lat !== null);
      for (let i = 1; i < geo.length; i++) total += km(geo[i - 1], geo[i]);
      summary.innerHTML = items.length ? `<div><b>${items.length}</b>paradas</div><div><b>${Math.round(total).toLocaleString('es-CO')} km</b>en línea recta</div><div><b>${new Set(items.map((m) => m.department).filter(Boolean)).size}</b>departamentos</div>` : '';
      list.innerHTML = items.map((m, i) => `
        <div class="stop" data-id="${m.id}">
          <img src="${esc(m.thumb || '')}" alt="">
          <span><strong><a href="${esc(m.url)}">${esc(m.title)}</a></strong><small>${esc([m.municipality, m.department].filter(Boolean).join(', '))}</small></span>
          <span class="stop-actions">
            <button type="button" data-move="-1" aria-label="Subir" ${i === 0 ? 'disabled' : ''}>${icon('chevron-down', 'icon')}</button>
            <button type="button" data-move="1" aria-label="Bajar" ${i === items.length - 1 ? 'disabled' : ''}>${icon('chevron-down', 'icon')}</button>
            <button type="button" data-remove aria-label="Quitar">${icon('close')}</button>
          </span>
        </div>`).join('');
      list.querySelectorAll('[data-move="-1"] svg').forEach((s) => { s.style.transform = 'rotate(180deg)'; });
      const stops = items.map((m) => ({ title: m.title, lat: m.lat, lng: m.lng }));
      if (window.VPCMap && window.L) {
        if (!mapApi) mapApi = await window.VPCMap.initRouteMap(mapEl, stops);
        else mapApi.draw(stops);
      }
    };
    list.addEventListener('click', (e) => {
      const row = e.target.closest('.stop');
      if (!row) return;
      const id = Number(row.dataset.id);
      const ids = VPC.MyRoute.all();
      const i = ids.indexOf(id);
      if (e.target.closest('[data-remove]')) VPC.MyRoute.set(ids.filter((x) => x !== id));
      const mv = e.target.closest('[data-move]');
      if (mv) {
        const j = i + Number(mv.dataset.move);
        if (j < 0 || j >= ids.length) return;
        [ids[i], ids[j]] = [ids[j], ids[i]];
        VPC.MyRoute.set(ids);
      }
    });
    $('[data-route-clear]', root).addEventListener('click', () => {
      if (window.confirm('¿Vaciar tu ruta?')) VPC.MyRoute.set([]);
    });
    $('[data-route-share]', root).addEventListener('click', async () => {
      const url = location.origin + location.pathname + '?ids=' + VPC.MyRoute.all().join(',');
      if (navigator.share) { try { await navigator.share({ title: 'Mi ruta por Colombia', url }); return; } catch (er) { return; } }
      try { await navigator.clipboard.writeText(url); VPC.toast('Enlace de tu ruta copiado'); } catch (er) { window.prompt('Copia este enlace:', url); }
    });
    document.addEventListener('route:change', render);
    if (document.readyState === 'complete') render(); else window.addEventListener('load', render);
  }

  // ---------- Galería: cargar más ----------
  const grid = $('[data-gallery-grid]');
  const more = $('[data-gallery-more]');
  if (grid && more) {
    more.addEventListener('click', async () => {
      more.disabled = true;
      more.textContent = 'Cargando…';
      try {
        const offset = grid.children.length;
        const sep = grid.dataset.endpoint.includes('?') ? '&' : '?';
        const r = await fetch(grid.dataset.endpoint + sep + 'desde=' + offset, { headers: { Accept: 'application/json' } });
        const data = await r.json();
        data.items.forEach((it) => {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = 'masonry-item';
          b.dataset.lightboxItem = JSON.stringify(it);
          b.setAttribute('aria-label', 'Ampliar: ' + (it.title || 'imagen'));
          b.innerHTML = `<img src="${esc(it.medium)}" alt="${esc(it.alt || '')}" loading="lazy" ${it.width ? `width="${it.width}" height="${it.height}"` : ''}>${it.kind !== 'image' ? `<span class="play">${icon('play')}</span>` : ''}<span class="cap"><strong>${esc(it.title || '')}</strong><small>${esc([it.municipality || it.department, it.credit].filter(Boolean).join(' · '))}</small></span>`;
          grid.appendChild(b);
        });
        const done = grid.children.length >= data.total || !data.items.length;
        more.hidden = done;
      } catch (e) {
        VPC.toast('No fue posible cargar más imágenes', 'alert');
      }
      more.disabled = false;
      more.innerHTML = icon('plus') + ' Cargar más';
    });
  }
})();
