/* Voy por Colombia · interacciones del panel administrativo. */
(function () {
  'use strict';
  const CFG = JSON.parse(document.getElementById('admin-config')?.textContent || '{}');
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));
  const base = (p) => CFG.base.replace(/\/$/, '') + p;
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const icon = (n) => `<svg class="icon" aria-hidden="true"><use href="${CFG.icons}#${n}"></use></svg>`;
  const getJSON = async (url) => {
    const r = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  };

  // ---------- Menú lateral y avisos ----------
  $('[data-sidebar-toggle]')?.addEventListener('click', () => $('[data-sidebar]').classList.toggle('is-open'));
  document.addEventListener('click', (e) => { const d = e.target.closest('[data-dismiss]'); if (d) d.closest('.alert')?.remove(); });

  // ---------- Confirmaciones ----------
  document.addEventListener('submit', (e) => {
    const f = e.target.closest('[data-confirm]');
    if (f && !window.confirm(f.dataset.confirm)) e.preventDefault();
  }, true);

  // ---------- Cambios sin guardar ----------
  $$('form[data-unsaved]').forEach((form) => {
    let dirty = false;
    form.addEventListener('input', () => { dirty = true; });
    form.addEventListener('change', () => { dirty = true; });
    form.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  });

  // ---------- Contadores de caracteres ----------
  $$('[data-counter]').forEach((el) => {
    const max = Number(el.dataset.counter);
    const c = document.createElement('div');
    c.className = 'counter';
    el.after(c);
    const upd = () => { c.textContent = `${el.value.length} / ${max}`; c.classList.toggle('over', el.value.length > max); };
    el.addEventListener('input', upd);
    upd();
  });

  // ---------- Dirección web sugerida ----------
  const slugSrc = $('[data-slug-source]');
  const slugDst = $('[data-slug-target]');
  if (slugSrc && slugDst) {
    const slugify = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    slugSrc.addEventListener('input', () => { if (!slugDst.value) slugDst.placeholder = slugify(slugSrc.value) || 'se genera a partir del título'; });
  }

  // ---------- Editor de texto con formato sencillo ----------
  function mdInline(t) {
    t = esc(t);
    t = t.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+|\/(?!\/)[^\s)]*)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
    return t.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/(^|[^*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/g, '$1<em>$2</em>');
  }
  function mdRender(text) {
    return text.replace(/\r/g, '').trim().split(/\n{2,}/).map((block) => {
      const lines = block.split('\n');
      const h = lines[0].match(/^(#{2,4})\s+(.+)$/);
      if (h) return `<h${h[1].length}>${mdInline(h[2])}</h${h[1].length}>` + (lines.length > 1 ? mdRender(lines.slice(1).join('\n')) : '');
      if (lines.every((l) => /^\s*[-*]\s+/.test(l))) return '<ul>' + lines.map((l) => `<li>${mdInline(l.replace(/^\s*[-*]\s+/, ''))}</li>`).join('') + '</ul>';
      if (lines.every((l) => /^\s*\d+[.)]\s+/.test(l))) return '<ol>' + lines.map((l) => `<li>${mdInline(l.replace(/^\s*\d+[.)]\s+/, ''))}</li>`).join('') + '</ol>';
      if (lines.every((l) => /^>\s?/.test(l))) return '<blockquote><p>' + lines.map((l) => mdInline(l.replace(/^>\s?/, ''))).join('<br>') + '</p></blockquote>';
      return '<p>' + lines.map(mdInline).join('<br>') + '</p>';
    }).join('');
  }
  $$('[data-md-editor]').forEach((ed) => {
    const ta = $('[data-md-input]', ed);
    const out = $('[data-md-output]', ed);
    const wrap = (before, after = before, placeholder = 'texto') => {
      const s = ta.selectionStart, en = ta.selectionEnd;
      const sel = ta.value.slice(s, en) || placeholder;
      ta.setRangeText(before + sel + after, s, en, 'select');
      ta.focus();
      ta.dispatchEvent(new Event('input', { bubbles: true }));
    };
    const linePrefix = (prefix) => {
      const s = ta.selectionStart;
      const start = ta.value.lastIndexOf('\n', s - 1) + 1;
      ta.setRangeText(prefix, start, start, 'end');
      ta.focus();
      ta.dispatchEvent(new Event('input', { bubbles: true }));
    };
    $$('[data-md]', ed).forEach((b) => b.addEventListener('click', () => {
      const a = b.dataset.md;
      if (a === 'bold') wrap('**');
      if (a === 'italic') wrap('*');
      if (a === 'h2') linePrefix('## ');
      if (a === 'list') linePrefix('- ');
      if (a === 'quote') linePrefix('> ');
      if (a === 'link') { const url = window.prompt('Dirección del enlace (https://…)', 'https://'); if (url && /^https?:\/\//.test(url)) wrap('[', `](${url})`, 'texto del enlace'); }
    }));
    $('[data-md-preview]', ed)?.addEventListener('click', (e) => {
      const on = out.hidden;
      out.hidden = !on;
      ta.hidden = on;
      e.currentTarget.setAttribute('aria-pressed', on ? 'true' : 'false');
      if (on) out.innerHTML = mdRender(ta.value) || '<p class="hint">Sin contenido.</p>';
    });
  });

  // ---------- Departamento → municipio ----------
  $$('[data-dep-select]').forEach((dep) => {
    const mun = $(dep.dataset.target);
    if (!mun) return;
    dep.addEventListener('change', async () => {
      mun.innerHTML = '<option value="">Cargando…</option>';
      if (!dep.value) { mun.innerHTML = '<option value="">Sin municipio</option>'; return; }
      try {
        const data = await getJSON(base('/admin/api/municipios?departamento=' + dep.value));
        mun.innerHTML = '<option value="">Sin municipio</option>' + data.items.map((m) => `<option value="${m.id}" data-lat="${m.lat ?? ''}" data-lng="${m.lng ?? ''}">${esc(m.name)}</option>`).join('');
      } catch (er) { mun.innerHTML = '<option value="">No se pudieron cargar los municipios</option>'; }
      document.dispatchEvent(new CustomEvent('dep:changed', { detail: dep }));
    });
  });

  // ---------- Selector de coordenadas ----------
  const picker = $('[data-map-picker]');
  if (picker && window.L) {
    const latIn = $('[data-lat]');
    const lngIn = $('[data-lng]');
    const map = L.map(picker, { zoomControl: true, minZoom: 4, maxZoom: 16 }).fitBounds([[-4.3, -79.1], [12.6, -66.8]]);
    map.attributionControl.setPrefix(false);
    map.attributionControl.addAttribution('Límites: geoBoundaries · © OpenStreetMap');
    let depLayer = null;
    fetch(CFG.geo + '/departamentos.geojson').then((r) => r.json()).then((geo) => {
      depLayer = L.geoJSON(geo, { style: { color: '#fff', weight: 1, fillColor: '#2bb673', fillOpacity: 0.3 } }).addTo(map);
      depLayer.eachLayer((l) => l.on('click', (ev) => setPoint(ev.latlng.lat, ev.latlng.lng)));
      focusDep();
    });
    let marker = null;
    const setPoint = (lat, lng, fly = false) => {
      lat = Math.round(lat * 1e6) / 1e6; lng = Math.round(lng * 1e6) / 1e6;
      latIn.value = lat; lngIn.value = lng;
      latIn.dispatchEvent(new Event('input', { bubbles: true }));
      if (!marker) {
        marker = L.marker([lat, lng], { draggable: true, icon: L.divIcon({ className: '', html: '<div class="pin"></div>', iconSize: [22, 22], iconAnchor: [11, 11] }) }).addTo(map);
        marker.on('dragend', () => { const p = marker.getLatLng(); setPoint(p.lat, p.lng); });
      } else marker.setLatLng([lat, lng]);
      if (fly) map.setView([lat, lng], Math.max(map.getZoom(), 9));
    };
    map.on('click', (ev) => setPoint(ev.latlng.lat, ev.latlng.lng));
    const fromInputs = () => {
      const la = parseFloat(String(latIn.value).replace(',', '.')), ln = parseFloat(String(lngIn.value).replace(',', '.'));
      if (!Number.isNaN(la) && !Number.isNaN(ln)) setPoint(la, ln, true);
    };
    [latIn, lngIn].forEach((i) => i.addEventListener('change', fromInputs));
    function focusDep() {
      const sel = $('[data-dep-select]');
      const slug = sel?.selectedOptions[0]?.dataset.slug;
      if (!slug || !depLayer || marker) return;
      const l = depLayer.getLayers().find((x) => x.feature.properties.slug === slug);
      if (l) map.fitBounds(l.getBounds());
    }
    document.addEventListener('dep:changed', focusDep);
    $('[data-use-municipality]')?.addEventListener('click', () => {
      const o = $('[data-mun-select]')?.selectedOptions[0];
      if (!o || !o.dataset.lat) { window.alert('Primero selecciona un municipio.'); return; }
      setPoint(parseFloat(o.dataset.lat), parseFloat(o.dataset.lng), true);
    });
    $('[data-clear-coords]')?.addEventListener('click', () => {
      latIn.value = ''; lngIn.value = '';
      if (marker) { map.removeLayer(marker); marker = null; }
    });
    fromInputs();
    setTimeout(() => map.invalidateSize(), 300);
  }

  // ---------- Mapa de gestión: arrastrar marcadores ----------
  const adminMap = $('[data-admin-map]');
  if (adminMap && window.L) {
    const map = L.map(adminMap, { minZoom: 4 }).fitBounds([[-4.3, -79.1], [12.6, -66.8]]);
    map.attributionControl.setPrefix(false);
    fetch(CFG.geo + '/departamentos.geojson').then((r) => r.json()).then((geo) => {
      L.geoJSON(geo, { style: { color: '#fff', weight: 1, fillColor: '#2bb673', fillOpacity: 0.28 }, interactive: false }).addTo(map);
    });
    JSON.parse(adminMap.dataset.markers || '[]').forEach((m) => {
      const mk = L.marker([m.lat, m.lng], { draggable: true, title: m.title, icon: L.divIcon({ className: '', html: `<div class="pin ${m.status}"></div>`, iconSize: [22, 22], iconAnchor: [11, 11] }) }).addTo(map);
      mk.bindPopup(`<strong>${esc(m.title)}</strong><br><a href="${esc(m.edit)}">Editar publicación</a>`);
      const original = mk.getLatLng();
      mk.on('dragend', async () => {
        const p = mk.getLatLng();
        if (!window.confirm(`¿Mover «${m.title}» a ${p.lat.toFixed(5)}, ${p.lng.toFixed(5)}?`)) { mk.setLatLng(original); return; }
        const body = new URLSearchParams({ lat: p.lat, lng: p.lng, _token: CSRF });
        try {
          const r = await fetch(adminMap.dataset.updateUrl + m.id + '/ubicacion', { method: 'POST', body, headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
          const data = await r.json();
          if (!r.ok) throw new Error(data.error || 'Error');
          mk.bindPopup(`<strong>${esc(m.title)}</strong><br>Ubicación guardada ✔<br><a href="${esc(m.edit)}">Editar publicación</a>`).openPopup();
        } catch (er) {
          window.alert('No se pudo guardar: ' + er.message);
          mk.setLatLng(original);
        }
      });
    });
  }

  // ---------- Selector multimedia (modal) ----------
  const modal = $('[data-modal]');
  function openPicker({ multiple, kind, onPick }) {
    const tipo = kind === 'image' ? 'image' : kind === 'video' ? 'video' : '';
    const state = { page: 1, q: '', tipo, selected: new Map(), total: 0 };
    modal.innerHTML = `
      <div class="modal-panel" role="dialog" aria-modal="true" aria-label="Biblioteca multimedia">
        <div class="modal-head">
          <h2>Biblioteca multimedia</h2>
          ${kind && kind.includes(',') ? `<select class="select" style="width:auto" data-p-tipo aria-label="Tipo"><option value="">Todo</option><option value="image">Imágenes</option><option value="video">Videos</option><option value="audio">Audios</option><option value="document">Documentos</option></select>` : ''}
          <input class="input" style="max-width:260px" type="search" placeholder="Buscar…" data-p-q aria-label="Buscar">
          <label class="btn btn-outline btn-sm">${icon('upload')} Subir<input class="sr-only" type="file" multiple data-p-file accept="${kind === 'image' ? '.jpg,.jpeg,.png,.webp,.gif' : kind === 'video' ? '.mp4,.webm' : '.jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.mp3,.ogg,.wav,.m4a,.pdf'}"></label>
          <button class="icon-btn" type="button" data-p-close aria-label="Cerrar">${icon('close')}</button>
        </div>
        <div class="modal-body"><div class="progress" hidden data-p-progress><span></span></div><p class="hint" data-p-msg></p><div class="picker-grid" data-p-grid></div>
          <div style="text-align:center;margin-top:14px"><button class="btn btn-outline btn-sm" type="button" data-p-more hidden>Cargar más</button></div></div>
        <div class="modal-foot"><span class="hint" data-p-count style="margin-right:auto"></span>${kind === 'video' ? '<span class="hint">Solo archivos MP4/WebM. Los videos de YouTube se agregan en la galería de la publicación.</span>' : ''}
          <button class="btn btn-outline" type="button" data-p-close>Cancelar</button><button class="btn btn-primary" type="button" data-p-ok disabled>${multiple ? 'Agregar seleccionados' : 'Usar este archivo'}</button></div>
      </div>`;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    const grid = $('[data-p-grid]', modal);
    const ok = $('[data-p-ok]', modal);
    const count = $('[data-p-count]', modal);
    const more = $('[data-p-more]', modal);
    const msg = $('[data-p-msg]', modal);
    const close = () => { modal.hidden = true; modal.innerHTML = ''; document.body.style.overflow = ''; };
    const refreshSel = () => {
      $$('.pick', grid).forEach((p) => {
        const keys = [...state.selected.keys()];
        const i = keys.indexOf(Number(p.dataset.id));
        p.classList.toggle('is-selected', i >= 0);
        p.querySelector('.badge-n').textContent = i + 1;
      });
      ok.disabled = state.selected.size === 0;
      count.textContent = state.selected.size ? `${state.selected.size} seleccionado(s)` : '';
    };
    const tile = (it) => `<button type="button" class="pick" data-id="${it.id}" title="${esc(it.title || '')}"><img src="${esc(it.thumb)}" alt="" loading="lazy"><span class="badge-n"></span><span class="cap">${esc(it.title || it.kind)}</span></button>`;
    const items = new Map();
    async function load(reset = false) {
      if (reset) { state.page = 1; grid.innerHTML = ''; }
      msg.textContent = 'Cargando…';
      const p = new URLSearchParams({ pagina: state.page, q: state.q, tipo: state.tipo });
      try {
        const data = await getJSON(base('/admin/api/multimedia?' + p));
        data.items.filter((it) => !kind || kind.split(',').includes(it.kind) || (kind === 'video' && it.kind === 'video')).forEach((it) => { items.set(it.id, it); grid.insertAdjacentHTML('beforeend', tile(it)); });
        state.total = data.total;
        msg.textContent = data.total ? '' : 'No hay archivos. Sube uno con el botón «Subir».';
        more.hidden = grid.children.length >= data.total;
        refreshSel();
      } catch (er) { msg.textContent = 'No se pudo cargar la biblioteca.'; }
    }
    grid.addEventListener('click', (e) => {
      const p = e.target.closest('.pick');
      if (!p) return;
      const id = Number(p.dataset.id);
      if (state.selected.has(id)) state.selected.delete(id);
      else { if (!multiple) state.selected.clear(); state.selected.set(id, items.get(id)); }
      refreshSel();
    });
    grid.addEventListener('dblclick', (e) => { if (!multiple && e.target.closest('.pick')) ok.click(); });
    more.addEventListener('click', () => { state.page++; load(); });
    let t;
    $('[data-p-q]', modal).addEventListener('input', (e) => { clearTimeout(t); t = setTimeout(() => { state.q = e.target.value; load(true); }, 250); });
    $('[data-p-tipo]', modal)?.addEventListener('change', (e) => { state.tipo = e.target.value; load(true); });
    $$('[data-p-close]', modal).forEach((b) => b.addEventListener('click', close));
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); }, { once: true });
    ok.addEventListener('click', () => { onPick([...state.selected.values()]); close(); });
    $('[data-p-file]', modal).addEventListener('change', (e) => {
      const files = [...e.target.files];
      if (!files.length) return;
      upload(files, $('[data-p-progress]', modal), (data) => {
        (data.errors || []).forEach((er) => { msg.textContent = er; });
        data.items.forEach((it) => { items.set(it.id, it); grid.insertAdjacentHTML('afterbegin', tile(it)); if (!multiple) state.selected.clear(); state.selected.set(it.id, it); });
        refreshSel();
      });
    });
    load(true);
    setTimeout(() => $('[data-p-q]', modal).focus(), 50);
  }

  function upload(files, progress, done) {
    const fd = new FormData();
    files.forEach((f) => fd.append('files[]', f));
    fd.append('_token', CSRF);
    fd.append('in_gallery', '0');
    const xhr = new XMLHttpRequest();
    xhr.open('POST', base('/admin/api/multimedia'));
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    progress.hidden = false;
    xhr.upload.onprogress = (e) => { if (e.lengthComputable) progress.firstElementChild.style.width = Math.round((100 * e.loaded) / e.total) + '%'; };
    xhr.onload = () => {
      progress.hidden = true;
      let data = { items: [], errors: ['Error al subir (' + xhr.status + ').'] };
      try { data = JSON.parse(xhr.responseText); } catch (er) { /* respuesta no JSON */ }
      done(data);
    };
    xhr.onerror = () => { progress.hidden = true; done({ items: [], errors: ['Error de conexión al subir.'] }); };
    xhr.send(fd);
  }

  // Campo de archivo único
  $$('[data-media-single]').forEach((field) => {
    const input = $('[data-media-input]', field);
    const preview = $('[data-media-preview]', field);
    const clear = $('[data-media-clear]', field);
    const kind = field.dataset.kind || 'image';
    if (clear) clear.hidden = !input.value;
    $('[data-media-choose]', field).addEventListener('click', () => openPicker({
      multiple: false, kind,
      onPick: ([it]) => {
        if (!it) return;
        input.value = it.id;
        preview.innerHTML = it.kind === 'image' ? `<img src="${esc(it.medium)}" alt="">` : `<span class="empty-label">${icon('video')} ${esc(it.title || 'Video')}</span>`;
        if (clear) clear.hidden = false;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      },
    }));
    clear?.addEventListener('click', () => {
      input.value = '';
      preview.innerHTML = `<span class="empty-label">${icon(kind === 'video' ? 'video' : 'image')} Sin ${kind === 'video' ? 'video' : 'imagen'}</span>`;
      clear.hidden = true;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  // Campo de varios archivos, ordenable
  $$('[data-media-multi]').forEach((field) => {
    const thumbs = $('[data-thumbs]', field);
    const name = field.dataset.name;
    const kindLabel = { video: 'Video', embed: 'Video', audio: 'Audio', document: 'PDF' };
    const add = (it) => {
      if ($(`.thumb[data-id="${it.id}"]`, thumbs)) return;
      thumbs.insertAdjacentHTML('beforeend', `<div class="thumb" draggable="true" data-id="${it.id}"><img src="${esc(it.thumb)}" alt="">${it.kind !== 'image' ? `<span class="kind">${kindLabel[it.kind] || it.kind}</span>` : ''}<input type="hidden" name="${esc(name)}[]" value="${it.id}"><button type="button" data-remove aria-label="Quitar">${icon('close')}</button></div>`);
      thumbs.dispatchEvent(new Event('change', { bubbles: true }));
    };
    $('[data-media-choose]', field).addEventListener('click', () => openPicker({ multiple: true, kind: field.dataset.kind, onPick: (list) => list.forEach(add) }));
    thumbs.addEventListener('click', (e) => { const b = e.target.closest('[data-remove]'); if (b) { b.closest('.thumb').remove(); thumbs.dispatchEvent(new Event('change', { bubbles: true })); } });
    let dragEl = null;
    thumbs.addEventListener('dragstart', (e) => { dragEl = e.target.closest('.thumb'); dragEl?.classList.add('dragging'); });
    thumbs.addEventListener('dragend', () => { dragEl?.classList.remove('dragging'); $$('.drag-over', thumbs).forEach((x) => x.classList.remove('drag-over')); dragEl = null; });
    thumbs.addEventListener('dragover', (e) => {
      e.preventDefault();
      const over = e.target.closest('.thumb');
      $$('.drag-over', thumbs).forEach((x) => x.classList.remove('drag-over'));
      if (over && over !== dragEl) over.classList.add('drag-over');
    });
    thumbs.addEventListener('drop', (e) => {
      e.preventDefault();
      const over = e.target.closest('.thumb');
      if (over && dragEl && over !== dragEl) {
        const after = [...thumbs.children].indexOf(dragEl) < [...thumbs.children].indexOf(over);
        over[after ? 'after' : 'before'](dragEl);
        thumbs.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });

  // ---------- Formulario de carga con arrastrar y soltar ----------
  const upForm = $('[data-upload-form]');
  if (upForm) {
    const dz = $('[data-dropzone]', upForm);
    const fi = $('[data-file-input]', upForm);
    const names = $('[data-file-names]', upForm);
    const show = () => { names.textContent = [...fi.files].map((f) => `${f.name} (${(f.size / 1048576).toFixed(1)} MB)`).join(' · '); };
    fi.addEventListener('change', show);
    ['dragenter', 'dragover'].forEach((ev) => dz.addEventListener(ev, (e) => { e.preventDefault(); dz.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach((ev) => dz.addEventListener(ev, (e) => { e.preventDefault(); dz.classList.remove('is-over'); }));
    dz.addEventListener('drop', (e) => { fi.files = e.dataTransfer.files; show(); });
    upForm.addEventListener('submit', (e) => {
      if (!fi.files.length) { e.preventDefault(); window.alert('Selecciona al menos un archivo.'); return; }
      e.preventDefault();
      const prog = $('[data-progress]', upForm);
      const fd = new FormData(upForm);
      const xhr = new XMLHttpRequest();
      xhr.open('POST', upForm.action);
      prog.hidden = false;
      upForm.querySelector('button[type="submit"]').disabled = true;
      xhr.upload.onprogress = (ev) => { if (ev.lengthComputable) prog.firstElementChild.style.width = Math.round((100 * ev.loaded) / ev.total) + '%'; };
      xhr.onload = () => { window.location.href = xhr.responseURL || window.location.href; };
      xhr.onerror = () => { window.alert('Error de conexión al subir los archivos.'); upForm.querySelector('button[type="submit"]').disabled = false; };
      xhr.send(fd);
    });
  }

  // ---------- Buscador de publicaciones (paradas de rutas y destacados) ----------
  $$('[data-entry-search]').forEach((box) => {
    const input = $('[data-entry-search-input]', box);
    const results = $('[data-entry-search-results]', box);
    let t;
    input.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(async () => {
        if (input.value.trim().length < 2) { results.hidden = true; return; }
        const data = await getJSON(base('/admin/api/publicaciones?q=' + encodeURIComponent(input.value.trim())));
        results.hidden = false;
        results.innerHTML = data.items.length ? data.items.map((it) => `<button type="button" data-pick='${esc(JSON.stringify(it))}'><img src="${esc(it.thumb || '')}" alt=""><span><strong>${esc(it.title)}</strong><br><small class="muted">${esc(it.section)} · ${esc(it.place || 'sin ubicación')} · ${esc(it.status)}${it.hasCoords ? '' : ' · sin coordenadas'}</small></span></button>`).join('') : '<p class="hint" style="padding:10px">Sin resultados.</p>';
      }, 250);
    });
    results.addEventListener('click', (e) => {
      const b = e.target.closest('[data-pick]');
      if (!b) return;
      const it = JSON.parse(b.dataset.pick);
      results.hidden = true;
      input.value = '';
      if (box.dataset.mode === 'stops') addStop(it);
      else {
        $('[data-entry-search-value]', box).value = it.id;
        $('[data-entry-search-picked]', box).textContent = `Se agregará «${it.title}» al guardar.`;
      }
    });
  });

  const stopsList = $('[data-stops-list]');
  function renumber() { $$('[data-stop]', stopsList).forEach((r, i) => { r.querySelector('.n').textContent = i + 1; }); }
  function addStop(it) {
    if ($(`input[name="stops[]"][value="${it.id}"]`, stopsList)) return;
    const node = document.getElementById('stop-template').content.firstElementChild.cloneNode(true);
    node.querySelector('img').src = it.thumb || '';
    node.querySelector('strong').textContent = it.title;
    node.querySelector('small').textContent = it.place || '';
    node.querySelector('input[type="hidden"]').value = it.id;
    stopsList.appendChild(node);
    renumber();
    stopsList.dispatchEvent(new Event('change', { bubbles: true }));
  }
  stopsList?.addEventListener('click', (e) => {
    const row = e.target.closest('[data-stop]');
    if (!row) return;
    if (e.target.closest('[data-stop-remove]')) row.remove();
    if (e.target.closest('[data-stop-up]') && row.previousElementSibling) row.previousElementSibling.before(row);
    if (e.target.closest('[data-stop-down]') && row.nextElementSibling) row.nextElementSibling.after(row);
    renumber();
  });
})();
