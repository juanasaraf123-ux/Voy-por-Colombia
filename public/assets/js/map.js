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

    const reliefStyle = () => ({ color: 'rgba(255,255,255,0.8)', weight: 1, fillColor: 'url(#vpc-relief)', fillOpacity: 1, opacity: 1 });
    const depLayer = L.geoJSON(deps, {
      style: reliefStyle,
      onEachFeature: (f, layer) => {
        layer.bindTooltip(() => {
          const n = data.counts[f.properties.slug] || 0;
          return `${VPC.esc(f.properties.name)}<small>${n ? n + (n === 1 ? ' lugar' : ' lugares') : 'Por descubrir'}</small>`;
        }, { className: 'dep-tooltip', sticky: true, direction: 'top' });
        layer.on({
          mouseover: () => { if (layer !== selectedDepLayer) layer.setStyle({ fillOpacity: 0.86, weight: 2.5, color: '#fff' }); },
          mouseout: () => { if (layer !== selectedDepLayer) depLayer.resetStyle(layer); },
          click: () => selectDep(f.properties.slug),
        });
      },
    }).addTo(map);
    applyRelief(map, el);
    addMapNotes(map);

    // Rutas animadas
    let routes = null;
    const routesBtn = $('[data-explorer-routes]', root);
    routesBtn?.addEventListener('click', async () => {
      const on = routesBtn.getAttribute('aria-pressed') !== 'true';
      routesBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
      if (!routes) {
        routes = await routesLayer(map, (r) => {
          drawer.innerHTML = `<div class="drawer-body"><button class="icon-btn drawer-close" type="button" data-drawer-close aria-label="Cerrar" style="justify-self:end">${VPC.icon('close')}</button>${routeCardHtml(r)}</div>`;
          drawer.classList.add('is-open');
          drawer.setAttribute('aria-hidden', 'false');
        });
      }
      if (on) { routes.addTo(map); map.flyToBounds(routes.getBounds(), { padding: [40, 40], duration: 0.9 }); } else map.removeLayer(routes);
    });

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
        const mk = L.marker([m.lat, m.lng], { icon: pinIcon(m), title: m.title, riseOnHover: true });
        mk.bindTooltip(`${VPC.esc(m.shortName)}<small>${VPC.esc(m.pointType || m.catName)}</small>`, { className: 'dep-tooltip', direction: 'top', offset: [0, -40] });
        mk.on('click', () => openPlace(m.id, true));
        markerById.set(m.id, { m, mk });
      });
      depLayer.setStyle((f) => (selectedDepLayer && selectedDepLayer.feature === f ? selectedStyle() : reliefStyle()));
      render();
    }

    function selectedStyle() { return { fillOpacity: 1, weight: 3, color: '#ffd447' }; }

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
          style: (f) => ({ color: 'rgba(255,255,255,0.9)', weight: 0.8, opacity: 1, fillColor: '#ffd447', fillOpacity: counts[f.properties.slug] ? 0.3 : 0 }),
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
          <div class="spot-actions drawer-media-actions">${mediaButtons(m)}<a href="https://www.openstreetmap.org/directions?to=${m.lat},${m.lng}" target="_blank" rel="noopener"><span>${VPC.icon('route')}</span>Cómo llegar</a></div>
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

  // =====================================================================
  // Mapa en relieve de la portada (diseño «Colombia te espera»)
  // =====================================================================
  const RELIEF_BOUNDS = [[12.7, -79.2], [-4.4, -66.7]]; // Debe coincidir con tools/geo/build_relief.py
  const SECTION_ALIAS = { gastronomia: 'gastronomy', cultura: 'culture', cafe: 'coffee' };

  /** Textura de relieve ilustrada como patrón SVG anclado a los límites geográficos. */
  function applyRelief(map, el) {
    const svg = el.querySelector('.leaflet-overlay-pane svg');
    if (!svg || svg.querySelector('#vpc-relief')) return;
    const ns = 'http://www.w3.org/2000/svg';
    const defs = document.createElementNS(ns, 'defs');
    const pattern = document.createElementNS(ns, 'pattern');
    pattern.setAttribute('id', 'vpc-relief');
    pattern.setAttribute('patternUnits', 'userSpaceOnUse');
    const image = document.createElementNS(ns, 'image');
    image.setAttribute('href', cfg().base.replace(/\/$/, '') + '/assets/img/relief/colombia-relief.webp');
    image.setAttribute('preserveAspectRatio', 'none');
    pattern.appendChild(image);
    defs.appendChild(pattern);
    svg.insertBefore(defs, svg.firstChild);
    const place = () => {
      const nw = map.latLngToLayerPoint(RELIEF_BOUNDS[0]);
      const se = map.latLngToLayerPoint(RELIEF_BOUNDS[1]);
      for (const [k, v] of [['x', nw.x], ['y', nw.y], ['width', se.x - nw.x], ['height', se.y - nw.y]]) pattern.setAttribute(k, v);
      image.setAttribute('width', se.x - nw.x);
      image.setAttribute('height', se.y - nw.y);
    };
    place();
    map.on('zoomend viewreset', place);
  }

  /** Rótulos manuscritos de mares y países vecinos. */
  function addMapNotes(map) {
    const arrow = (d) => `<svg viewBox="0 0 34 20"><path d="${d}"/></svg>`;
    [
      [[10.6, -77.7], 'Mar Caribe', 'sea', arrow('M4 10 q6 -6 12 0 t12 0')],
      [[5.4, -78.5], 'Océano<br>Pacífico', 'sea', arrow('M4 10 q6 -6 12 0 t12 0')],
      [[8.8, -68.3], 'Venezuela', '', arrow('M30 4 C 22 6, 12 10, 6 16 M6 16 l1 -7 M6 16 l7 -1')],
      [[-0.2, -69.0], 'Brasil', '', arrow('M24 2 C 22 8, 18 12, 12 17 M12 17 l1 -7 M12 17 l7 -2')],
      [[-0.9, -78.0], 'Ecuador', '', arrow('M28 4 C 20 8, 14 12, 6 16 M6 16 l2 -7 M6 16 l7 0')],
      [[-3.3, -73.6], 'Perú', '', arrow('M4 4 C 12 8, 20 12, 28 16 M28 16 l-7 0 M28 16 l-2 -7')],
    ].forEach(([ll, text, cls, svgArrow]) => {
      L.marker(ll, { interactive: false, keyboard: false, icon: L.divIcon({ className: '', iconSize: [120, 50], iconAnchor: [60, 25], html: `<div class="map-note ${cls}">${text}${svgArrow}</div>` }) }).addTo(map);
    });
  }

  function pinIcon(m, selected = false) {
    const { icon } = window.VPC;
    if (m.featured && m.thumb && m.section === 'tourism') {
      return L.divIcon({ className: '', iconSize: [62, 72], iconAnchor: [31, 72], html: `<div class="photo-bubble ${selected ? 'is-selected' : ''}"><img src="${window.VPC.esc(m.thumb)}" alt=""></div>` });
    }
    const c = m.catColor || m.color;
    return L.divIcon({
      className: '', iconSize: [34, 44], iconAnchor: [17, 44],
      html: `<div class="pin-marker ${selected ? 'is-selected' : ''}" style="--pc:${c};--d:${(m.id % 7) * 0.35}s"><svg class="pin-bg" viewBox="0 0 34 44"><path d="M17 43 C 17 43, 2 27, 2 16 A 15 15 0 0 1 32 16 C 32 27, 17 43, 17 43 Z" fill="${c}" stroke="#fff" stroke-width="2"/></svg>${icon(m.pointIcon || m.icon || 'pin')}</div>`,
    });
  }

  /** Botones de multimedia que abren el visor sin salir del mapa. */
  function mediaButtons(m) {
    const { icon } = window.VPC;
    const btn = (kind, ic, label, n) => `<button type="button" data-media-open="${kind}" data-entry="${m.id}" class="${n ? '' : 'is-off'}" ${n ? '' : 'disabled'}><span>${icon(ic)}</span>${label}</button>`;
    return btn('image', 'camera', 'Galería', 1) + btn('video', 'play', 'Video', m.media?.video) + btn('audio', 'volume', 'Audio', m.media?.audio);
  }

  function spotCardHtml(m) {
    const { esc, icon } = window.VPC;
    const loc = m.municipality && m.municipality !== m.department ? `${m.municipality}, ${m.department}` : (m.department || '');
    const dir = m.lat !== null ? `https://www.openstreetmap.org/directions?to=${m.lat},${m.lng}` : m.url;
    return `<article class="spot-card" style="--c:${m.catColor}">
      <div class="spot-media">${m.large ? `<img src="${esc(m.large)}" alt="${esc(m.shortName)}">` : ''}
        <span class="spot-chip">${icon(m.icon)} ${esc(m.catName)}</span>
        <button class="spot-fav fav-btn" type="button" data-fav="${m.id}" aria-label="Guardar en favoritos"><span class="off">${icon('heart')}</span><span class="on">${icon('heart-fill')}</span></button>
        ${m.media.video ? `<button class="spot-play" type="button" data-media-open="video" data-entry="${m.id}" aria-label="Ver video">${icon('play')}</button>` : ''}
        <div class="spot-title"><h3>${esc(m.shortName)}</h3>${loc ? `<span>${icon('pin')} ${esc(loc)}</span>` : ''}</div>
      </div>
      <div class="spot-body">
        ${m.tagline ? `<h4>${esc(m.tagline)}</h4>` : ''}
        <p>${esc(m.summary)}</p>
        <div class="spot-actions">
          ${mediaButtons(m)}
          <a href="${esc(dir)}" target="_blank" rel="noopener"><span>${icon('route')}</span>Cómo llegar</a>
        </div>
        <a class="btn-yellow" href="${esc(m.url)}">Ver más ${icon('chevron-right')}</a>
      </div></article>`;
  }

  function placesListHtml(places) {
    const { esc } = window.VPC;
    return `<div class="spot-places">${places.map((p) => `<button type="button" data-place="${p.id}"><img src="${esc(p.thumb || '')}" alt=""><span><strong>${esc(p.shortName)}</strong><small>${esc(p.pointType || p.catName)}${p.municipality ? ' · ' + esc(p.municipality) : ''}</small></span></button>`).join('')}</div>`;
  }

  function depCardHtml(info, places) {
    const { esc, icon } = window.VPC;
    const photos = (info.photos || []).slice(0, 4);
    return `<article class="spot-card" style="--c:${info.regionColor || '#2bb673'}">
      <div class="spot-media">${info.image ? `<img src="${esc(info.image)}" alt="${esc(info.name)}">` : ''}
        <span class="spot-chip">${icon('map')} Región ${esc(info.region || '')}</span>
        <div class="spot-title"><h3>${esc(info.name)}</h3><span>${icon('pin')} Capital: ${esc(info.capital || '—')}</span></div>
      </div>
      <div class="spot-body">
        <button type="button" class="spot-back" data-spot-back>${icon('arrow-left')} Todo el país</button>
        <p>${esc(info.summary || '')}</p>
        ${photos.length ? `<div class="spot-thumbs" data-gallery-group>${photos.map((ph) => `<button type="button" data-lightbox-item='${esc(JSON.stringify(ph))}' aria-label="Ampliar ${esc(ph.title || '')}"><img src="${esc(ph.thumb)}" alt=""></button>`).join('')}</div>` : ''}
        ${places.length ? placesListHtml(places) : '<p><strong>Muy pronto agregaremos lugares aquí.</strong></p>'}
        <p style="font-size:.8rem;opacity:.75">Toca un municipio en el mapa para acercarte.</p>
        <a class="btn-yellow" href="${esc(info.url)}">Descubrir ${esc(info.name)} ${icon('chevron-right')}</a>
      </div></article>`;
  }

  function munCardHtml(name, depName, places) {
    const { esc, icon } = window.VPC;
    return `<article class="spot-card" style="--c:#ff7a1a">
      <div class="spot-body">
        <button type="button" class="spot-back" data-spot-dep>${icon('arrow-left')} ${esc(depName)}</button>
        <span class="spot-chip" style="position:static;width:max-content">${icon('pin')} Municipio</span>
        <h4 style="font-size:1.6rem">${esc(name)}</h4>
        ${places.length ? placesListHtml(places) : '<p>Aún no hay lugares publicados en este municipio. ¡Pronto!</p>'}
      </div></article>`;
  }

  function routeCardHtml(r) {
    const { esc, icon } = window.VPC;
    return `<article class="spot-card" style="--c:#ff3f8e">
      <div class="spot-media">${r.image ? `<img src="${esc(r.image)}" alt="${esc(r.title)}">` : ''}
        <span class="spot-chip">${icon('route')} Ruta${r.duration ? ' · ' + esc(r.duration) : ''}</span>
        <div class="spot-title"><h3>${esc(r.title)}</h3></div>
      </div>
      <div class="spot-body">
        <p>${esc(r.summary || '')}</p>
        <ol class="spot-stops">${r.stops.map((st) => `<li>${esc(st.title)}</li>`).join('')}</ol>
        <a class="btn-yellow" href="${esc(r.url)}">Ver la ruta ${icon('chevron-right')}</a>
      </div></article>`;
  }

  const ROUTE_COLORS = ['#ff3f8e', '#ffd447', '#12b5b0', '#ff7a1a', '#7b3fe4'];

  /** Dibuja las rutas curadas como líneas animadas. Devuelve la capa. */
  async function routesLayer(map, onPick) {
    const data = await getJSON(apiUrl('/rutas'));
    const group = L.featureGroup();
    data.routes.forEach((r, i) => {
      const c = ROUTE_COLORS[i % ROUTE_COLORS.length];
      const pts = r.stops.map((st) => [st.lat, st.lng]);
      L.polyline(pts, { color: '#05202c', weight: 8, opacity: 0.35, interactive: false }).addTo(group);
      const line = L.polyline(pts, { color: c, weight: 4, opacity: 0.95, className: 'route-anim' }).addTo(group);
      line.bindTooltip(window.VPC.esc(r.title), { className: 'dep-tooltip', sticky: true });
      line.on('click', () => onPick && onPick(r));
      r.stops.forEach((st) => L.marker([st.lat, st.lng], { interactive: false, icon: L.divIcon({ className: '', iconSize: [14, 14], iconAnchor: [7, 7], html: `<div class="route-stop-dot" style="--rc:${c}"></div>` }) }).addTo(group));
    });
    group._routes = data.routes;
    return group;
  }

  async function initHeroMap(el) {
    const { VPC } = window;
    const spot = document.querySelector('[data-spot]');
    Object.assign(REGION_COLORS, JSON.parse(el.dataset.regions || '{}'));
    const map = L.map(el, {
      zoomControl: false, attributionControl: true, scrollWheelZoom: false, zoomSnap: 0.1, minZoom: 4, maxZoom: 12,
      maxBounds: [[-9, -86], [17, -62]], maxBoundsViscosity: 0.8, tap: true,
    });
    map.attributionControl.setPrefix(false);
    map.attributionControl.addAttribution('Límites: <a href="https://www.geoboundaries.org" target="_blank" rel="noopener">geoBoundaries</a> · © OpenStreetMap');
    const fit = () => map.fitBounds(BOUNDS_MAINLAND, { paddingTopLeft: [24, 70], paddingBottomRight: [24, 24] });
    fit();

    const [deps, data] = await Promise.all([
      getJSON(geoUrl('departamentos.geojson')),
      getJSON(apiUrl('/mapa?secciones=tourism,culture,gastronomy,coffee')),
    ]);
    const counts = data.counts || {};
    let selectedDep = null;
    const baseStyle = () => ({ color: 'rgba(255,255,255,0.75)', weight: 1, fillColor: 'url(#vpc-relief)', fillOpacity: 1, opacity: 1 });
    const depLayer = L.geoJSON(deps, {
      style: baseStyle,
      onEachFeature: (f, layer) => {
        const n = counts[f.properties.slug] || 0;
        layer.bindTooltip(`${VPC.esc(f.properties.name)}<small>${n ? n + (n === 1 ? ' lugar' : ' lugares') : 'Por descubrir'}</small>`, { className: 'dep-tooltip', sticky: true, direction: 'top' });
        layer.on({
          mouseover: () => { if (layer !== selectedDep) layer.setStyle({ weight: 2.5, color: '#ffffff', fillOpacity: 0.86 }); },
          mouseout: () => { if (layer !== selectedDep) layer.setStyle(baseStyle()); },
          click: () => selectDep(f, layer),
        });
      },
    }).addTo(map);

    applyRelief(map, el);
    addMapNotes(map);

    // Marcadores
    const markers = new Map();
    const layer = L.layerGroup().addTo(map);
    let selected = null;
    data.markers.forEach((m) => {
      const mk = L.marker([m.lat, m.lng], { icon: pinIcon(m), title: m.shortName, riseOnHover: true, zIndexOffset: m.featured ? 500 : 0 });
      mk.bindTooltip(`${VPC.esc(m.shortName)}<small>${VPC.esc(m.pointType || m.catName)}</small>`, { className: 'dep-tooltip', direction: 'top', offset: [0, -40] });
      mk.on('click', () => showPlace(m.id, true));
      markers.set(m.id, { m, mk });
    });

    let chip = '';
    const visible = (m) => !chip || m.categories.some((c) => c.slug === chip) || (SECTION_ALIAS[chip] && m.section === SECTION_ALIAS[chip]);
    function draw() {
      layer.clearLayers();
      markers.forEach(({ m, mk }) => { if (visible(m)) layer.addLayer(mk); });
    }
    draw();

    function showPlace(id, fromMap = false) {
      const rec = markers.get(id);
      if (!rec || !spot) return;
      if (selected && markers.get(selected)) markers.get(selected).mk.setIcon(pinIcon(markers.get(selected).m));
      selected = id;
      rec.mk.setIcon(pinIcon(rec.m, true));
      spot.innerHTML = spotCardHtml(rec.m);
      VPC.Favs.paint();
      if (!fromMap && map.getZoom() < 7) map.panTo([rec.m.lat, rec.m.lng]);
    }

    let munLayer = null;
    let depInfo = null;
    async function selectDep(f, lyr) {
      if (selectedDep) selectedDep.setStyle(baseStyle());
      selectedDep = lyr;
      lyr.setStyle({ weight: 3, color: '#ffd447', fillOpacity: 1 });
      map.flyToBounds(lyr.getBounds(), { padding: [40, 40], duration: 0.9, maxZoom: 8 });
      resetBtn.hidden = false;
      if (munLayer) { map.removeLayer(munLayer); munLayer = null; }
      const slug = f.properties.slug;
      try {
        const [info, mun] = await Promise.all([getJSON(apiUrl('/departamentos/' + slug)), getJSON(geoUrl('municipios/' + slug + '.geojson'))]);
        depInfo = { info, f, lyr };
        const places = data.markers.filter((m) => m.departmentSlug === slug);
        if (spot) spot.innerHTML = depCardHtml(info, places);
        const counts = info.municipalityCounts || {};
        munLayer = L.geoJSON(mun, {
          style: (mf) => ({ color: 'rgba(255,255,255,0.9)', weight: 0.8, fillColor: '#ffd447', fillOpacity: counts[mf.properties.slug] ? 0.28 : 0 }),
          onEachFeature: (mf, ml) => {
            const n = counts[mf.properties.slug] || 0;
            ml.bindTooltip(`${VPC.esc(mf.properties.name)}${n ? `<small>${n} ${n === 1 ? 'lugar' : 'lugares'}</small>` : ''}`, { className: 'dep-tooltip', sticky: true });
            ml.on('click', (ev) => {
              L.DomEvent.stopPropagation(ev);
              munLayer.eachLayer((x) => munLayer.resetStyle(x));
              ml.setStyle({ weight: 3, color: '#ff3f8e', fillOpacity: 0.15 });
              map.flyToBounds(ml.getBounds(), { padding: [60, 60], duration: 0.8, maxZoom: 11 });
              const mp = data.markers.filter((m) => m.departmentSlug === slug && m.municipalitySlug === mf.properties.slug);
              if (spot) spot.innerHTML = munCardHtml(mf.properties.name, info.name, mp);
            });
          },
        }).addTo(map);
        layer.eachLayer((mk) => mk.bringToFront && mk.bringToFront());
      } catch (e) { /* sin información */ }
    }

    function backToCountry() {
      if (selectedDep) selectedDep.setStyle(baseStyle());
      selectedDep = null;
      if (munLayer) { map.removeLayer(munLayer); munLayer = null; }
      fit();
      resetBtn.hidden = true;
      if (selected) showPlace(selected, true);
    }

    spot?.addEventListener('click', (e) => {
      const p = e.target.closest('[data-place]');
      if (p) { const rec = markers.get(Number(p.dataset.place)); if (rec) { map.flyTo([rec.m.lat, rec.m.lng], Math.max(map.getZoom(), 9), { duration: 0.8 }); showPlace(rec.m.id, true); } }
      if (e.target.closest('[data-spot-back]')) backToCountry();
      if (e.target.closest('[data-spot-dep]') && depInfo) selectDep(depInfo.f, depInfo.lyr);
    });

    // Rutas animadas
    let routes = null;
    const routesBtn = document.querySelector('[data-hroutes]');
    routesBtn?.addEventListener('click', async () => {
      const on = routesBtn.getAttribute('aria-pressed') !== 'true';
      routesBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
      if (!routes) routes = await routesLayer(map, (r) => { if (spot) spot.innerHTML = routeCardHtml(r); });
      if (on) {
        routes.addTo(map);
        map.flyToBounds(routes.getBounds(), { paddingTopLeft: [24, 70], paddingBottomRight: [24, 24], duration: 0.9 });
        resetBtn.hidden = false;
        if (routes._routes[0] && spot) spot.innerHTML = routeCardHtml(routes._routes[0]);
      } else map.removeLayer(routes);
    });

    // Botón para volver a ver todo el país
    const Reset = L.Control.extend({
      options: { position: 'bottomright' },
      onAdd() {
        const b = L.DomUtil.create('button', 'mchip');
        b.type = 'button';
        b.innerHTML = VPC.icon('map') + ' Todo el país';
        b.style.minWidth = '0';
        L.DomEvent.disableClickPropagation(b);
        b.addEventListener('click', () => backToCountry());
        return b;
      },
    });
    const resetBtn = new Reset().addTo(map).getContainer();
    resetBtn.hidden = true;
    map.on('zoomend', () => { if (map.getZoom() > 5.6) resetBtn.hidden = false; });

    // Filtros
    document.querySelectorAll('[data-hchip]').forEach((b) => b.addEventListener('click', () => {
      chip = b.dataset.hchip;
      document.querySelectorAll('[data-hchip]').forEach((x) => { x.classList.toggle('is-active', x === b); x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
      draw();
      const first = [...markers.values()].find(({ m }) => visible(m) && m.featured) || [...markers.values()].find(({ m }) => visible(m));
      if (chip && first) showPlace(first.m.id);
    }));

    const spotId = Number(el.dataset.spotlight);
    if (spotId && markers.has(spotId)) {
      selected = spotId;
      markers.get(spotId).mk.setIcon(pinIcon(markers.get(spotId).m, true));
    }
    window.addEventListener('resize', () => { map.invalidateSize(); });
    setTimeout(() => { map.invalidateSize(); fit(); }, 250);
  }

  window.VPCMap = { initRouteMap };

  document.addEventListener('DOMContentLoaded', () => {
    if (!window.L || !window.VPC) return;
    const heroMap = $('[data-hero-map]');
    if (heroMap) initHeroMap(heroMap).catch((e) => console.error(e));
    const explorer = $('[data-explorer]');
    if (explorer) initExplorer(explorer).catch((e) => console.error(e));
    $$('[data-mini-map]').forEach((el) => initMiniMap(el).catch((e) => console.error(e)));
    // El mapa de «Mi ruta» lo crea pages.js con los datos del navegador.
    $$('[data-route-map]').forEach((el) => {
      if (!el.closest('[data-my-route]') && el.dataset.stops) initRouteMap(el, JSON.parse(el.dataset.stops)).catch((e) => console.error(e));
    });
  });
})();
