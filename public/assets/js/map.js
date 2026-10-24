/* Voy por Colombia · mapas interactivos con Leaflet y límites geográficos reales (geoBoundaries / OSM). */
(function () {
  'use strict';

  const BOUNDS_MAINLAND = [[-4.3, -79.1], [12.55, -66.8]];
  const SAN_ANDRES = [12.95, -81.55];
  const REGION_COLORS = {};
  const geoCache = {};

  function cfg() { return window.VPC.CONFIG; }
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));
  const norm = (s) => String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

  async function getJSON(url) {
    if (geoCache[url]) return geoCache[url];
    const r = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    geoCache[url] = r.json();
    return geoCache[url];
  }
  const geoUrl = (path) => cfg().geo.replace(/\/$/, '') + '/' + path;
  const apiUrl = (path) => cfg().api.replace(/\/$/, '') + path;

  function baseMap(el, opts = {}) {
    const map = L.map(el, {
      zoomControl: false,
      attributionControl: true,
      scrollWheelZoom: opts.scrollWheelZoom ?? true,
      zoomSnap: 0.25,
      minZoom: 4,
      maxZoom: 17,
      maxBounds: [[-12, -90], [20, -58]],
      maxBoundsViscosity: 0.7,
    });
    L.control.zoom({ position: opts.zoomPosition || 'bottomright', zoomInTitle: 'Acercar', zoomOutTitle: 'Alejar' }).addTo(map);
    map.attributionControl.setPrefix(false);
    map.attributionControl.addAttribution('Límites: <a href="https://www.geoboundaries.org" target="_blank" rel="noopener">geoBoundaries</a> · © OpenStreetMap');
    let tiles = null;
    if (cfg().tiles) {
      tiles = L.tileLayer(cfg().tiles, { attribution: cfg().tilesAttribution || '', maxZoom: 18, opacity: 0.55 });
      if (opts.tiles) tiles.addTo(map);
    }
    map.fitBounds(BOUNDS_MAINLAND, { padding: [10, 10] });
    map._vpcTiles = tiles;
    return map;
  }

  function departmentStyle(feature, counts = {}) {
    const color = REGION_COLORS[feature.properties.slug] || '#2bb673';
    const has = (counts[feature.properties.slug] || 0) > 0;
    return { color: '#ffffff', weight: 1.4, fillColor: color, fillOpacity: has ? 0.62 : 0.32, opacity: 1 };
  }

  function photoIcon(m, selected = false) {
    const html = m.thumb
      ? `<div class="photo-marker ${m.hiddenGem ? 'is-gem' : ''} ${selected ? 'is-selected' : ''}" style="--c:${m.color}"><img src="${window.VPC.esc(m.thumb)}" alt="" loading="lazy"></div>`
      : `<div class="photo-marker is-dot" style="background:${m.color}"></div>`;
    return L.divIcon({ html, className: '', iconSize: m.thumb ? [46, 46] : [22, 22], iconAnchor: m.thumb ? [23, 46] : [11, 11] });
  }

  function clusterGroup() {
    return L.markerClusterGroup({
      showCoverageOnHover: false,
      maxClusterRadius: 48,
      spiderfyOnMaxZoom: true,
      iconCreateFunction: (c) => {
        const n = c.getChildCount();
        const size = n < 10 ? 40 : n < 50 ? 48 : 56;
        return L.divIcon({ html: `<div class="marker-cluster-custom" style="width:${size}px;height:${size}px">${n}</div>`, className: '', iconSize: [size, size] });
      },
    });
  }

  function islandsControl(map) {
    const C = L.Control.extend({
      options: { position: 'topleft' },
      onAdd() {
        const b = L.DomUtil.create('button', 'btn btn-sm btn-light');
        b.type = 'button';
        b.innerHTML = window.VPC.icon('wave') + ' San Andrés y Providencia';
        b.style.boxShadow = '0 2px 8px rgba(0,0,0,.12)';
        L.DomEvent.disableClickPropagation(b);
        b.addEventListener('click', () => map.flyTo(SAN_ANDRES, 9, { duration: 1.2 }));
        return b;
      },
    });
    return new C().addTo(map);
  }

  // =====================================================================
  // Mapa de la portada
  // =====================================================================
  async function initHomeMap(el) {
    const panel = $('[data-place-panel]');
    const legend = $('[data-map-legend]', el);
    const { VPC } = window;
    const map = baseMap(el, { scrollWheelZoom: false });
    islandsControl(map);
    const [deps, data] = await Promise.all([
      getJSON(geoUrl('departamentos.geojson')),
      getJSON(apiUrl('/mapa?secciones=tourism,culture,coffee,gastronomy')),
    ]);
    const depInfo = JSON.parse(el.dataset.regions || '{}');
    Object.assign(REGION_COLORS, depInfo);

    const counts = data.counts || {};
    let selectedLayer = null;
    let munLayer = null;
    const depLayer = L.geoJSON(deps, {
      style: (f) => departmentStyle(f, counts),
      onEachFeature: (f, layer) => {
        const n = counts[f.properties.slug] || 0;
        layer.bindTooltip(`${VPC.esc(f.properties.name)}<small>${n ? n + (n === 1 ? ' lugar' : ' lugares') : 'Por descubrir'}</small>`, { className: 'dep-tooltip', sticky: true, direction: 'top' });
        layer.on({
          mouseover: () => { if (layer !== selectedLayer) layer.setStyle({ fillOpacity: 0.85, weight: 2.4 }); },
          mouseout: () => { if (layer !== selectedLayer) depLayer.resetStyle(layer); },
          click: () => selectDepartment(f, layer),
        });
      },
    }).addTo(map);

    const markers = clusterGroup();
    data.markers.forEach((m) => {
      const mk = L.marker([m.lat, m.lng], { icon: photoIcon(m), title: m.title, riseOnHover: true });
      mk.on('click', () => showPlace(m));
      markers.addLayer(mk);
    });
    map.addLayer(markers);

    function showPlace(m) {
      if (!panel) return;
      panel.innerHTML = `
        <div class="place-panel-media">${m.image ? `<img src="${VPC.esc(m.image)}" alt="${VPC.esc(m.title)}">` : ''}
          <div class="badges"><span class="badge badge-section" style="--c:${m.color}">${VPC.esc(m.sectionLabel)}</span>${m.hiddenGem ? `<span class="badge badge-gem">${VPC.icon('sparkle')} Joya escondida</span>` : ''}</div></div>
        <div class="place-panel-body">
          <span class="place-loc">${VPC.icon('pin')} ${VPC.esc([m.municipality, m.department].filter(Boolean).join(', '))}</span>
          <h3>${VPC.esc(m.title)}</h3>
          <p>${VPC.esc(m.summary)}</p>
          ${m.activities.length ? `<div class="drawer-acts">${m.activities.map((a) => `<span>${VPC.esc(a)}</span>`).join('')}</div>` : ''}
          <div class="place-panel-actions">
            <a class="btn btn-primary" href="${VPC.esc(m.url)}">Descubrir ${VPC.icon('arrow-right', 'icon icon-arrow')}</a>
            <button class="btn btn-outline btn-icon fav-btn" type="button" data-fav="${m.id}" aria-label="Guardar en favoritos"><span class="off">${VPC.icon('heart')}</span><span class="on">${VPC.icon('heart-fill')}</span></button>
            <button class="btn btn-outline" type="button" data-route-toggle="${m.id}">${VPC.icon('plus')} <span data-label>Agregar a mi ruta</span></button>
          </div>
        </div>`;
      VPC.Favs.paint();
      VPC.MyRoute.paint();
    }

    async function selectDepartment(f, layer) {
      if (selectedLayer) depLayer.resetStyle(selectedLayer);
      selectedLayer = layer;
      layer.setStyle({ fillOpacity: 0.15, weight: 3, color: '#14231f' });
      map.flyToBounds(layer.getBounds(), { padding: [30, 30], duration: 1 });
      if (munLayer) map.removeLayer(munLayer);
      try {
        const mun = await getJSON(geoUrl('municipios/' + f.properties.slug + '.geojson'));
        munLayer = L.geoJSON(mun, {
          style: { color: '#14231f', weight: 0.7, opacity: 0.5, fillOpacity: 0 },
          onEachFeature: (mf, ml) => ml.bindTooltip(VPC.esc(mf.properties.name), { className: 'dep-tooltip', sticky: true }),
          interactive: true,
        }).addTo(map);
        markers.bringToFront?.();
      } catch (e) { /* sin municipios */ }
      try {
        const info = await getJSON(apiUrl('/departamentos/' + f.properties.slug));
        const places = data.markers.filter((m) => m.departmentSlug === f.properties.slug);
        panel.innerHTML = `
          <div class="place-panel-media">${info.image ? `<img src="${VPC.esc(info.image)}" alt="${VPC.esc(info.name)}">` : ''}<div class="badges"><span class="badge" style="background:${info.regionColor};color:#fff">Región ${VPC.esc(info.region || '')}</span></div></div>
          <div class="place-panel-body">
            <span class="place-loc">${VPC.icon('map')} Capital: ${VPC.esc(info.capital || '—')}</span>
            <h3>${VPC.esc(info.name)}</h3>
            <p>${VPC.esc(info.summary || '')}</p>
            <div class="place-list">${places.slice(0, 4).map((m) => `<button class="place-mini" type="button" data-place="${m.id}"><img src="${VPC.esc(m.thumb || '')}" alt=""><span><strong>${VPC.esc(m.title)}</strong><small>${VPC.esc(m.sectionLabel)}${m.municipality ? ' · ' + VPC.esc(m.municipality) : ''}</small></span></button>`).join('') || '<p class="muted">Aún no hay lugares publicados aquí. ¡Pronto!</p>'}</div>
            <div class="place-panel-actions">
              <a class="btn btn-primary" href="${VPC.esc(info.url)}">Ver ${VPC.esc(info.name)} ${VPC.icon('arrow-right', 'icon icon-arrow')}</a>
              <a class="btn btn-outline" href="${VPC.esc(cfg().base.replace(/\/$/, '') + '/turismo?departamento=' + info.slug)}">${VPC.icon('compass')} Explorar</a>
            </div>
          </div>`;
        $$('[data-place]', panel).forEach((b) => b.addEventListener('click', () => {
          const m = data.markers.find((x) => x.id === Number(b.dataset.place));
          if (m) { map.flyTo([m.lat, m.lng], 10, { duration: 1 }); showPlace(m); }
        }));
      } catch (e) { /* sin información */ }
    }

    // Leyenda de regiones
    if (legend && el.dataset.regionList) {
      const regionsList = JSON.parse(el.dataset.regionList);
      legend.innerHTML = regionsList.map((r) => `<button type="button" style="--c:${r.color}" data-region="${r.slug}"><i></i>${window.VPC.esc(r.name)}</button>`).join('')
        + `<button type="button" data-region="" style="--c:#14231f"><i></i>Todo el país</button>`;
      legend.addEventListener('click', (e) => {
        const b = e.target.closest('[data-region]');
        if (!b) return;
        if (selectedLayer) { depLayer.resetStyle(selectedLayer); selectedLayer = null; }
        if (munLayer) { map.removeLayer(munLayer); munLayer = null; }
        if (!b.dataset.region) { map.flyToBounds(BOUNDS_MAINLAND, { duration: 1 }); return; }
        if (b.dataset.region === 'insular') { map.flyTo(SAN_ANDRES, 9, { duration: 1.2 }); return; }
        const slugs = regionsList.find((r) => r.slug === b.dataset.region)?.departments || [];
        const layers = depLayer.getLayers().filter((l) => slugs.includes(l.feature.properties.slug));
        if (layers.length) map.flyToBounds(L.featureGroup(layers).getBounds(), { padding: [20, 20], duration: 1 });
      });
    }
  }

  // =====================================================================
  // Explorador de Turismo
  // =====================================================================
  async function initExplorer(root) {
    const { VPC } = window;
    const el = $('[data-explorer-map]', root);
    const list = $('[data-explorer-list]', root);
    const crumbs = $('[data-crumbs]', root);
    const drawer = $('[data-drawer]', root);
    const routeBar = $('[data-route-bar]', root);
    const searchInput = $('[data-explorer-q]', root);
    const initial = JSON.parse(root.dataset.initial || '{}');
    Object.assign(REGION_COLORS, JSON.parse(root.dataset.regions || '{}'));
    const regionList = JSON.parse(root.dataset.regionList || '[]');

    const state = { dep: null, mun: null, category: initial.categoria || '', q: initial.q || '', sections: ['tourism'], near: null, selected: null };
    const map = baseMap(el, { tiles: false, zoomPosition: 'bottomright' });
    islandsControl(map);

    let data = { markers: [], counts: {} };
    const deps = await getJSON(geoUrl('departamentos.geojson'));
    let munLayer = null;
    let selectedDepLayer = null;
    let routeLine = null;
    const markerById = new Map();
    const cluster = clusterGroup();
    map.addLayer(cluster);

    const depLayer = L.geoJSON(deps, {
      style: (f) => departmentStyle(f, data.counts),
      onEachFeature: (f, layer) => {
        layer.bindTooltip(() => {
          const n = data.counts[f.properties.slug] || 0;
          return `${VPC.esc(f.properties.name)}<small>${n ? n + (n === 1 ? ' lugar' : ' lugares') : 'Por descubrir'}</small>`;
        }, { className: 'dep-tooltip', sticky: true, direction: 'top' });
        layer.on({
          mouseover: () => { if (layer !== selectedDepLayer) layer.setStyle({ fillOpacity: 0.85, weight: 2.4 }); },
          mouseout: () => { if (layer !== selectedDepLayer) depLayer.resetStyle(layer); },
          click: () => selectDep(f.properties.slug),
        });
      },
    }).addTo(map);

    // Capa base opcional
    const tilesBtn = $('[data-tiles-toggle]', root);
    if (tilesBtn) {
      if (!map._vpcTiles) tilesBtn.hidden = true;
      tilesBtn.addEventListener('click', () => {
        const on = map.hasLayer(map._vpcTiles);
        on ? map.removeLayer(map._vpcTiles) : map._vpcTiles.addTo(map);
        tilesBtn.classList.toggle('is-active', !on);
      });
    }

    async function loadMarkers() {
      data = await getJSON(apiUrl('/mapa?secciones=' + state.sections.join(',')));
      cluster.clearLayers();
      markerById.clear();
      data.markers.forEach((m) => {
        const mk = L.marker([m.lat, m.lng], { icon: photoIcon(m), title: m.title, riseOnHover: true });
        mk.on('click', () => openPlace(m.id, true));
        markerById.set(m.id, { m, mk });
      });
      depLayer.setStyle((f) => (selectedDepLayer && selectedDepLayer.feature === f ? selectedStyle() : departmentStyle(f, data.counts)));
      render();
    }

    function selectedStyle() { return { fillOpacity: 0.08, weight: 3, color: '#14231f' }; }

    function matches(m) {
      if (state.dep && m.departmentSlug !== state.dep.slug) return false;
      if (state.mun && m.municipalitySlug !== state.mun.slug) return false;
      if (state.category && !m.categories.some((c) => c.slug === state.category)) return false;
      if (state.q) {
        const hay = norm([m.title, m.summary, m.department, m.municipality, m.categories.map((c) => c.name).join(' ')].join(' '));
        if (!norm(state.q).split(/\s+/).every((t) => hay.includes(t))) return false;
      }
      return true;
    }

    function dist(a, b) {
      const R = 6371, dLat = ((b[0] - a[0]) * Math.PI) / 180, dLng = ((b[1] - a[1]) * Math.PI) / 180;
      const x = Math.sin(dLat / 2) ** 2 + Math.cos((a[0] * Math.PI) / 180) * Math.cos((b[0] * Math.PI) / 180) * Math.sin(dLng / 2) ** 2;
      return 2 * R * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
    }

    function render() {
      let items = data.markers.filter(matches);
      if (state.near) items = items.map((m) => ({ ...m, _d: dist(state.near, [m.lat, m.lng]) })).sort((a, b) => a._d - b._d);
      cluster.clearLayers();
      items.forEach((m) => cluster.addLayer(markerById.get(m.id).mk));
      // Migas de pan
      const parts = [`<button type="button" data-crumb="country">${VPC.icon('map')} Colombia</button>`];
      if (state.dep) parts.push(VPC.icon('chevron-right'), state.mun ? `<button type="button" data-crumb="dep">${VPC.esc(state.dep.name)}</button>` : `<span>${VPC.esc(state.dep.name)}</span>`);
      if (state.mun) parts.push(VPC.icon('chevron-right'), `<span>${VPC.esc(state.mun.name)}</span>`);
      crumbs.innerHTML = parts.join('');
      // Lista
      let ctx = '';
      if (state.dep && !state.mun && state.dep.info) {
        const i = state.dep.info;
        ctx = `<div class="explorer-ctx">${i.image ? `<img src="${VPC.esc(i.image)}" alt="">` : ''}<span class="eyebrow" style="--c:${i.regionColor}">Región ${VPC.esc(i.region || '')}</span><h2>${VPC.esc(i.name)}</h2><p>${VPC.esc(i.summary || '')}</p><a class="link-arrow" href="${VPC.esc(i.url)}">Todo sobre ${VPC.esc(i.name)} ${VPC.icon('arrow-right')}</a><small class="muted">Toca un municipio en el mapa para acercarte.</small></div>`;
      } else if (state.mun) {
        ctx = `<div class="explorer-ctx"><span class="eyebrow">Municipio</span><h2>${VPC.esc(state.mun.name)}</h2><p>${VPC.esc(state.dep?.name || '')}</p></div>`;
      }
      const label = `<p class="muted" style="margin:4px 4px 0;font-weight:600">${items.length} ${items.length === 1 ? 'lugar' : 'lugares'}${state.near ? ' · ordenados por cercanía' : ''}</p>`;
      list.innerHTML = ctx + label + (items.length ? items.map((m) => `
        <div class="result-item ${state.selected === m.id ? 'is-active' : ''}" style="--c:${m.color}" data-open="${m.id}" role="button" tabindex="0">
          <img src="${VPC.esc(m.thumb || '')}" alt="" loading="lazy">
          <span><strong>${VPC.esc(m.title)}</strong><small>${VPC.esc([m.municipality, m.department].filter(Boolean).join(', '))}${m._d ? ' · ' + Math.round(m._d) + ' km' : ''}</small>
          ${m.hiddenGem ? `<small style="color:var(--morado);font-weight:700">${VPC.icon('sparkle')} Joya escondida</small>` : ''}</span>
          <button class="add-route" type="button" data-route-toggle="${m.id}" aria-label="Agregar ${VPC.esc(m.title)} a mi ruta">${VPC.icon('plus')}</button>
        </div>`).join('') : `<div class="empty" style="padding:30px 16px">${VPC.icon('compass')}<h3>Sin resultados</h3><p class="muted">Prueba con otra categoría o vuelve al mapa completo.</p></div>`);
      VPC.MyRoute.paint();
      syncUrl();
      drawRoute();
    }

    function syncUrl() {
      const p = new URLSearchParams();
      if (state.dep) p.set('departamento', state.dep.slug);
      if (state.mun) p.set('municipio', state.mun.slug);
      if (state.category) p.set('categoria', state.category);
      if (state.q) p.set('q', state.q);
      if (state.selected) p.set('lugar', state.selected);
      history.replaceState(null, '', location.pathname + (p.toString() ? '?' + p : ''));
    }

    async function selectDep(slug, munSlug = null, fly = true) {
      const layer = depLayer.getLayers().find((l) => l.feature.properties.slug === slug);
      if (!layer) return;
      if (selectedDepLayer) depLayer.resetStyle(selectedDepLayer);
      selectedDepLayer = layer;
      layer.setStyle(selectedStyle());
      state.dep = { slug, name: layer.feature.properties.name, info: null };
      state.mun = null;
      if (fly) map.flyToBounds(layer.getBounds(), { padding: [30, 30], duration: 0.9 });
      if (munLayer) map.removeLayer(munLayer);
      render();
      try {
        const [mun, info] = await Promise.all([getJSON(geoUrl('municipios/' + slug + '.geojson')), getJSON(apiUrl('/departamentos/' + slug))]);
        state.dep.info = info;
        const counts = info.municipalityCounts || {};
        munLayer = L.geoJSON(mun, {
          style: (f) => ({ color: '#14231f', weight: 0.8, opacity: 0.55, fillColor: '#ffc531', fillOpacity: counts[f.properties.slug] ? 0.35 : 0.04 }),
          onEachFeature: (f, ml) => {
            const n = counts[f.properties.slug] || 0;
            ml.bindTooltip(`${VPC.esc(f.properties.name)}${n ? `<small>${n} ${n === 1 ? 'lugar' : 'lugares'}</small>` : ''}`, { className: 'dep-tooltip', sticky: true });
            ml.on('click', (ev) => { L.DomEvent.stopPropagation(ev); selectMun(f, ml); });
            ml.on('mouseover', () => ml.setStyle({ fillOpacity: 0.55 }));
            ml.on('mouseout', () => munLayer.resetStyle(ml));
          },
        }).addTo(map);
        if (munSlug) {
          const ml = munLayer.getLayers().find((l) => l.feature.properties.slug === munSlug);
          if (ml) selectMun(ml.feature, ml);
        } else {
          render();
        }
      } catch (e) { render(); }
    }

    function selectMun(f, layer) {
      state.mun = { slug: f.properties.slug, name: f.properties.name };
      munLayer.eachLayer((l) => munLayer.resetStyle(l));
      layer.setStyle({ weight: 3, color: '#ff3f8e', fillOpacity: 0.12 });
      map.flyToBounds(layer.getBounds(), { padding: [60, 60], duration: 0.8, maxZoom: 12 });
      render();
    }

    function resetCountry() {
      state.dep = null; state.mun = null;
      if (selectedDepLayer) depLayer.resetStyle(selectedDepLayer);
      selectedDepLayer = null;
      if (munLayer) { map.removeLayer(munLayer); munLayer = null; }
      map.flyToBounds(BOUNDS_MAINLAND, { duration: 0.9 });
      render();
    }

    crumbs.addEventListener('click', (e) => {
      const b = e.target.closest('[data-crumb]');
      if (!b) return;
      if (b.dataset.crumb === 'country') resetCountry();
      if (b.dataset.crumb === 'dep' && state.dep) selectDep(state.dep.slug);
    });

    function openPlace(id, fromMap = false) {
      const rec = markerById.get(id);
      if (!rec) return;
      const m = rec.m;
      state.selected = id;
      if (!fromMap || map.getZoom() < 9) map.flyTo([m.lat, m.lng], Math.max(map.getZoom(), 10), { duration: 0.9 });
      drawer.innerHTML = `
        <div class="drawer-media">${m.image ? `<img src="${VPC.esc(m.image)}" alt="${VPC.esc(m.title)}">` : ''}
          <button class="icon-btn drawer-close" type="button" data-drawer-close aria-label="Cerrar">${VPC.icon('close')}</button>
          <div class="badges"><span class="badge badge-section" style="--c:${m.color}">${VPC.esc(m.sectionLabel)}</span>${m.hiddenGem ? `<span class="badge badge-gem">${VPC.icon('sparkle')} Joya escondida</span>` : ''}${m.categories.slice(0, 2).map((c) => `<span class="badge">${VPC.esc(c.name)}</span>`).join('')}</div>
        </div>
        <div class="drawer-body">
          <span class="place-loc">${VPC.icon('pin')} ${VPC.esc([m.municipality, m.department].filter(Boolean).join(', '))}</span>
          <h2>${VPC.esc(m.title)}</h2>
          <p>${VPC.esc(m.summary)}</p>
          ${m.activities.length ? `<div><strong style="font-size:.85rem">Actividades</strong><div class="drawer-acts" style="margin-top:8px">${m.activities.map((a) => `<span>${VPC.esc(a)}</span>`).join('')}</div></div>` : ''}
          <div class="drawer-actions">
            <a class="btn btn-primary" href="${VPC.esc(m.url)}">Ver ficha completa ${VPC.icon('arrow-right', 'icon icon-arrow')}</a>
            <button class="btn btn-outline btn-icon fav-btn" type="button" data-fav="${m.id}" aria-label="Guardar en favoritos"><span class="off">${VPC.icon('heart')}</span><span class="on">${VPC.icon('heart-fill')}</span></button>
            <button class="btn btn-outline" type="button" data-route-toggle="${m.id}">${VPC.icon('plus')} <span data-label>Agregar a mi ruta</span></button>
            <a class="btn btn-outline btn-sm" href="https://www.openstreetmap.org/?mlat=${m.lat}&mlon=${m.lng}#map=13/${m.lat}/${m.lng}" target="_blank" rel="noopener">${VPC.icon('globe')} Ver ubicación</a>
          </div>
        </div>`;
      drawer.classList.add('is-open');
      drawer.setAttribute('aria-hidden', 'false');
      VPC.Favs.paint();
      VPC.MyRoute.paint();
      $$('.result-item', list).forEach((r) => r.classList.toggle('is-active', Number(r.dataset.open) === id));
      syncUrl();
    }
    function closeDrawer() {
      drawer.classList.remove('is-open');
      drawer.setAttribute('aria-hidden', 'true');
      state.selected = null;
      syncUrl();
    }
    drawer.addEventListener('click', (e) => { if (e.target.closest('[data-drawer-close]')) closeDrawer(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && drawer.classList.contains('is-open')) closeDrawer(); });

    list.addEventListener('click', (e) => {
      if (e.target.closest('[data-route-toggle]')) return;
      const it = e.target.closest('[data-open]');
      if (it) openPlace(Number(it.dataset.open));
    });
    list.addEventListener('keydown', (e) => {
      const it = e.target.closest('[data-open]');
      if (it && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); openPlace(Number(it.dataset.open)); }
    });

    // Filtros
    $$('[data-cat]', root).forEach((b) => b.addEventListener('click', () => {
      state.category = state.category === b.dataset.cat ? '' : b.dataset.cat;
      $$('[data-cat]', root).forEach((x) => x.setAttribute('aria-pressed', x.dataset.cat === state.category ? 'true' : 'false'));
      render();
    }));
    $$('[data-cat]', root).forEach((x) => x.setAttribute('aria-pressed', x.dataset.cat === state.category ? 'true' : 'false'));
    $$('[data-sec]', root).forEach((b) => b.addEventListener('click', () => {
      const s = b.dataset.sec;
      state.sections = state.sections.includes(s) ? state.sections.filter((x) => x !== s) : [...state.sections, s];
      if (!state.sections.length) state.sections = ['tourism'];
      $$('[data-sec]', root).forEach((x) => x.setAttribute('aria-pressed', state.sections.includes(x.dataset.sec) ? 'true' : 'false'));
      loadMarkers();
    }));
    if (searchInput) {
      searchInput.value = state.q;
      let t;
      searchInput.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => { state.q = searchInput.value.trim(); render(); }, 200); });
    }
    const depSelect = $('[data-explorer-dep]', root);
    depSelect?.addEventListener('change', () => (depSelect.value ? selectDep(depSelect.value) : resetCountry()));

    $('[data-near]', root)?.addEventListener('click', (e) => {
      const btn = e.currentTarget;
      if (state.near) { state.near = null; btn.classList.remove('is-active'); render(); return; }
      if (!navigator.geolocation) { VPC.toast('Tu navegador no permite ubicarte', 'info'); return; }
      navigator.geolocation.getCurrentPosition((pos) => {
        state.near = [pos.coords.latitude, pos.coords.longitude];
        btn.classList.add('is-active');
        L.circleMarker(state.near, { radius: 9, color: '#fff', weight: 3, fillColor: '#1d6fe0', fillOpacity: 1 }).addTo(map).bindTooltip('Estás aquí');
        map.flyTo(state.near, 8);
        render();
      }, () => VPC.toast('No pudimos obtener tu ubicación', 'info'), { timeout: 8000 });
    });

    // Ruta personal sobre el mapa
    function drawRoute() {
      const ids = VPC.MyRoute.all();
      const pts = ids.map((id) => markerById.get(id)?.m).filter(Boolean);
      if (routeLine) { map.removeLayer(routeLine); routeLine = null; }
      if (pts.length > 1) {
        routeLine = L.polyline(pts.map((m) => [m.lat, m.lng]), { color: '#ff3f8e', weight: 4, dashArray: '8 10', opacity: 0.9 }).addTo(map);
      }
      if (routeBar) {
        routeBar.classList.toggle('is-visible', ids.length > 0);
        $('[data-route-stops]', routeBar).innerHTML = pts.slice(0, 5).map((m) => `<i style="background-image:url('${VPC.esc(m.thumb || '')}')"></i>`).join('');
        $('[data-route-label]', routeBar).textContent = ids.length + (ids.length === 1 ? ' parada' : ' paradas');
      }
    }
    document.addEventListener('route:change', drawRoute);

    // Estado inicial desde la URL
    await loadMarkers();
    const params = new URLSearchParams(location.search);
    if (params.get('region')) {
      const r = regionList.find((x) => x.slug === params.get('region'));
      if (r?.slug === 'insular') map.flyTo(SAN_ANDRES, 9);
      else if (r) {
        const layers = depLayer.getLayers().filter((l) => r.departments.includes(l.feature.properties.slug));
        if (layers.length) map.fitBounds(L.featureGroup(layers).getBounds(), { padding: [20, 20] });
      }
    }
    if (initial.departamento || params.get('departamento')) await selectDep(params.get('departamento') || initial.departamento, params.get('municipio'));
    if (initial.lugar) openPlace(Number(initial.lugar));
    setTimeout(() => map.invalidateSize(), 200);
  }

  // =====================================================================
  // Mapa pequeño de una ficha y mapa de rutas
  // =====================================================================
  async function initMiniMap(el) {
    const lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
    const map = baseMap(el, { scrollWheelZoom: false, tiles: true });
    const deps = await getJSON(geoUrl('departamentos.geojson'));
    L.geoJSON(deps, { style: (f) => ({ color: '#fff', weight: 1, fillColor: f.properties.slug === el.dataset.dep ? (el.dataset.color || '#ff7a1a') : '#2bb673', fillOpacity: f.properties.slug === el.dataset.dep ? 0.55 : 0.18 }), interactive: false }).addTo(map);
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
      L.marker([lat, lng], { icon: photoIcon({ thumb: el.dataset.thumb, color: el.dataset.color || '#ff7a1a' }) }).addTo(map);
      map.setView([lat, lng], 7);
    } else if (el.dataset.dep) {
      const layer = L.geoJSON(deps.features.filter((f) => f.properties.slug === el.dataset.dep));
      map.fitBounds(layer.getBounds(), { padding: [20, 20] });
    }
  }

  async function initRouteMap(el, stops) {
    const map = baseMap(el, { scrollWheelZoom: true, tiles: true });
    const deps = await getJSON(geoUrl('departamentos.geojson'));
    L.geoJSON(deps, { style: { color: '#fff', weight: 1, fillColor: '#2bb673', fillOpacity: 0.22 }, interactive: false }).addTo(map);
    const layer = L.featureGroup().addTo(map);
    const pointsOnly = el.dataset.mode === 'points';
    const draw = (list) => {
      layer.clearLayers();
      const pts = list.filter((m) => m.lat !== null && m.lng !== null);
      if (pointsOnly) {
        pts.forEach((m) => L.marker([m.lat, m.lng], { icon: photoIcon({ thumb: m.thumb, color: m.color || '#8a4b2a' }) }).bindTooltip(window.VPC.esc(m.title), { className: 'dep-tooltip' }).addTo(layer));
        if (pts.length) map.fitBounds(layer.getBounds(), { padding: [60, 60], maxZoom: 9 });
        return;
      }
      pts.forEach((m, i) => {
        L.marker([m.lat, m.lng], { icon: L.divIcon({ className: '', html: `<div class="marker-cluster-custom" style="width:34px;height:34px;background:linear-gradient(135deg,#ff7a1a,#ff3f8e)">${i + 1}</div>`, iconSize: [34, 34], iconAnchor: [17, 17] }) })
          .bindTooltip(window.VPC.esc(m.title), { className: 'dep-tooltip' }).addTo(layer);
      });
      if (pts.length > 1) L.polyline(pts.map((m) => [m.lat, m.lng]), { color: '#ff3f8e', weight: 4, dashArray: '8 10' }).addTo(layer);
      if (pts.length) map.fitBounds(layer.getBounds(), { padding: [50, 50], maxZoom: 9 });
    };
    draw(stops);
    return { draw, map };
  }

  window.VPCMap = { initRouteMap };

  document.addEventListener('DOMContentLoaded', () => {
    if (!window.L || !window.VPC) return;
    const home = $('[data-home-map]');
    if (home) initHomeMap(home).catch((e) => console.error(e));
    const explorer = $('[data-explorer]');
    if (explorer) initExplorer(explorer).catch((e) => console.error(e));
    $$('[data-mini-map]').forEach((el) => initMiniMap(el).catch((e) => console.error(e)));
    // El mapa de «Mi ruta» lo crea pages.js con los datos del navegador.
    $$('[data-route-map]').forEach((el) => {
      if (!el.closest('[data-my-route]') && el.dataset.stops) initRouteMap(el, JSON.parse(el.dataset.stops)).catch((e) => console.error(e));
    });
  });
})();
