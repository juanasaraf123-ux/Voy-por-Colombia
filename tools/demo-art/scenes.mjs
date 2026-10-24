// Generador de ilustraciones de demostración (SVG) para "Voy por Colombia".
// Son ilustraciones originales creadas por código: no reproducen fotografías ni obras de terceros.
// Se reemplazan por fotografías reales desde el panel administrativo.

export function rng(seed) {
  let a = seed >>> 0;
  return () => {
    a |= 0; a = (a + 0x6d2b79f5) | 0;
    let t = Math.imul(a ^ (a >>> 15), 1 | a);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}

const hex = (c) => c.replace('#', '');
function mix(a, b, t) {
  const pa = hex(a).match(/../g).map((x) => parseInt(x, 16));
  const pb = hex(b).match(/../g).map((x) => parseInt(x, 16));
  return '#' + pa.map((v, i) => Math.round(v + (pb[i] - v) * t).toString(16).padStart(2, '0')).join('');
}
const f = (n) => Math.round(n * 10) / 10;

let uid = 0;
const id = (p) => `${p}${++uid}`;

function linear(stops, x1 = 0, y1 = 0, x2 = 0, y2 = 1) {
  const gid = id('g');
  const s = stops.map(([o, c, op = 1]) => `<stop offset="${o}" stop-color="${c}" stop-opacity="${op}"/>`).join('');
  return { id: gid, def: `<linearGradient id="${gid}" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}">${s}</linearGradient>` };
}
function radial(stops, cx = 0.5, cy = 0.5, r = 0.5) {
  const gid = id('r');
  const s = stops.map(([o, c, op = 1]) => `<stop offset="${o}" stop-color="${c}" stop-opacity="${op}"/>`).join('');
  return { id: gid, def: `<radialGradient id="${gid}" cx="${cx}" cy="${cy}" r="${r}">${s}</radialGradient>` };
}

function wrap(W, H, defs, body, { grain = 0.09, vignette = 0.28 } = {}) {
  const v = radial([[0.55, '#000', 0], [1, '#000', vignette]], 0.5, 0.5, 0.75);
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">
<defs>${defs.join('')}${v.def}
<filter id="grain" x="0" y="0" width="100%" height="100%"><feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" stitchTiles="stitch"/><feColorMatrix type="saturate" values="0"/><feComponentTransfer><feFuncA type="table" tableValues="0 ${grain}"/></feComponentTransfer></filter>
<filter id="blur8"><feGaussianBlur stdDeviation="8"/></filter><filter id="blur20"><feGaussianBlur stdDeviation="20"/></filter><filter id="blur3"><feGaussianBlur stdDeviation="3"/></filter>
</defs>${body}<rect width="${W}" height="${H}" fill="url(#${v.id})"/><rect width="${W}" height="${H}" filter="url(#grain)" opacity="1"/></svg>`;
}

// Silueta de montaña: suma de senos con ruido, rellena hasta el borde inferior.
function ridge(W, H, base, amp, r, { freq = 1, sharp = 1, step = 8, bottom = H } = {}) {
  const ph = [r() * 10, r() * 10, r() * 10, r() * 10];
  let d = `M0 ${bottom} `;
  for (let x = 0; x <= W; x += step) {
    const t = x / W;
    let y = Math.sin(t * 3.1 * freq + ph[0]) * 0.5 + Math.sin(t * 7.3 * freq + ph[1]) * 0.25 + Math.sin(t * 17 * freq + ph[2]) * 0.12 * sharp + Math.sin(t * 41 * freq + ph[3]) * 0.05 * sharp;
    y += (r() - 0.5) * 0.04 * sharp;
    d += `L${f(x)} ${f(base - y * amp)} `;
  }
  return d + `L${W} ${bottom} Z`;
}

function sky(W, H, top, mid, bottom) {
  const g = linear([[0, top], [0.55, mid], [1, bottom]]);
  return { def: g.def, svg: `<rect width="${W}" height="${H}" fill="url(#${g.id})"/>` };
}

function sun(x, y, r, color, glow = '#fff3c4') {
  const g = radial([[0, glow, 0.9], [0.35, glow, 0.35], [1, glow, 0]]);
  return { def: g.def, svg: `<circle cx="${x}" cy="${y}" r="${r * 4.5}" fill="url(#${g.id})"/><circle cx="${x}" cy="${y}" r="${r}" fill="${color}"/>` };
}

function clouds(W, y, n, r, color = '#fff', op = 0.8) {
  let s = '';
  for (let i = 0; i < n; i++) {
    const cx = r() * W, cy = y + (r() - 0.5) * 120, w = 120 + r() * 220;
    s += `<g opacity="${f(op * (0.6 + r() * 0.4))}" fill="${color}"><ellipse cx="${f(cx)}" cy="${f(cy)}" rx="${f(w)}" ry="${f(w * 0.18)}"/><ellipse cx="${f(cx - w * 0.3)}" cy="${f(cy - w * 0.09)}" rx="${f(w * 0.45)}" ry="${f(w * 0.2)}"/><ellipse cx="${f(cx + w * 0.25)}" cy="${f(cy - w * 0.12)}" rx="${f(w * 0.38)}" ry="${f(w * 0.22)}"/></g>`;
  }
  return s;
}

function birds(x, y, n, r, color = '#1b2b2a') {
  let s = `<g fill="none" stroke="${color}" stroke-width="3" stroke-linecap="round">`;
  for (let i = 0; i < n; i++) {
    const bx = x + r() * 260, by = y + r() * 90, w = 10 + r() * 14;
    s += `<path d="M${f(bx - w)} ${f(by)} q${f(w / 2)} ${f(-w / 2)} ${f(w)} 0 q${f(w / 2)} ${f(-w / 2)} ${f(w)} 0"/>`;
  }
  return s + '</g>';
}

function palm(x, y, h, lean, color, r, scale = 1) {
  const tx = x + lean, ty = y - h;
  let s = `<path d="M${x} ${y} Q${f(x + lean * 0.2)} ${f(y - h * 0.5)} ${f(tx)} ${f(ty)}" stroke="${color}" stroke-width="${f(14 * scale)}" fill="none" stroke-linecap="round"/>`;
  for (let i = 0; i < 9; i++) {
    const a = (i / 9) * Math.PI * 2 + r() * 0.3;
    const len = (120 + r() * 60) * scale;
    const ex = tx + Math.cos(a) * len, ey = ty + Math.sin(a) * len * 0.55 + len * 0.25;
    const cx = tx + Math.cos(a) * len * 0.5, cy = ty + Math.sin(a) * len * 0.3 - 40 * scale;
    s += `<path d="M${f(tx)} ${f(ty)} Q${f(cx)} ${f(cy)} ${f(ex)} ${f(ey)} Q${f(cx + 10)} ${f(cy + 26 * scale)} ${f(tx)} ${f(ty)}" fill="${color}"/>`;
  }
  return s;
}

function waxPalm(x, y, h, color) {
  let s = `<rect x="${f(x - 4)}" y="${f(y - h)}" width="8" height="${f(h)}" fill="${color}" rx="4"/>`;
  for (let i = 0; i < 7; i++) {
    const a = -Math.PI / 2 + (i - 3) * 0.45;
    s += `<path d="M${x} ${f(y - h)} q${f(Math.cos(a) * 40)} ${f(Math.sin(a) * 30 - 10)} ${f(Math.cos(a) * 75)} ${f(Math.sin(a) * 40 + 35)}" stroke="${color}" stroke-width="6" fill="none" stroke-linecap="round"/>`;
  }
  return s;
}

function canopy(W, base, size, n, colors, r, rows = 3) {
  let s = '';
  for (let row = 0; row < rows; row++) {
    const c = colors[Math.min(row, colors.length - 1)];
    for (let i = 0; i < n; i++) {
      const x = (i / n) * W + (r() - 0.5) * 60;
      const y = base + row * size * 0.55 + (r() - 0.5) * 30;
      const rr = size * (0.6 + r() * 0.6);
      s += `<circle cx="${f(x)}" cy="${f(y)}" r="${f(rr)}" fill="${c}"/>`;
    }
  }
  return s;
}

// ---------- Paisajes ----------
export const scenes = {
  andes({ W, H, r, palette = {} }) {
    const p = { sky: ['#ffb38a', '#ffd9a8', '#fff1d6'], layers: ['#c9b6d9', '#9f8fc4', '#6f6aa8', '#3f5a8a', '#1f3d5c'], sun: '#fff6df', ...palette };
    const s = sky(W, H, p.sky[0], p.sky[1], p.sky[2]);
    const su = sun(W * 0.68, H * 0.36, H * 0.07, p.sun);
    let body = s.svg + su.svg + clouds(W, H * 0.22, 4, r, '#fff', 0.55);
    p.layers.forEach((c, i) => {
      const base = H * (0.42 + i * 0.11);
      body += `<path d="${ridge(W, H, base, H * (0.2 - i * 0.025), r, { freq: 1 + i * 0.4, sharp: 1.6 - i * 0.2 })}" fill="${c}"/>`;
      if (i === 0 && palette.snow) body += `<path d="${ridge(W, H, base, H * 0.2, rng(9), { freq: 1, sharp: 1.6 })}" fill="#fff" opacity="0.0"/>`;
      if (i < 3) body += `<rect y="${f(base)}" width="${W}" height="${f(H * 0.12)}" fill="#fff" opacity="0.12" filter="url(#blur20)"/>`;
    });
    body += birds(W * 0.2, H * 0.25, 5, r);
    return wrap(W, H, [s.def, su.def], body);
  },

  paramo({ W, H, r }) {
    const s = sky(W, H, '#8fc3d6', '#cfe6e4', '#eef3e6');
    let body = s.svg + clouds(W, H * 0.2, 5, r, '#fff', 0.7);
    body += `<path d="${ridge(W, H, H * 0.45, H * 0.16, r, { sharp: 1.4 })}" fill="#9db7b8"/>`;
    body += `<path d="${ridge(W, H, H * 0.52, H * 0.1, r)}" fill="#6f9a8c"/>`;
    body += `<ellipse cx="${W * 0.55}" cy="${H * 0.63}" rx="${W * 0.28}" ry="${H * 0.05}" fill="#3e7f95"/><ellipse cx="${W * 0.52}" cy="${H * 0.625}" rx="${W * 0.2}" ry="${H * 0.02}" fill="#8cc6d1" opacity="0.6"/>`;
    body += `<path d="${ridge(W, H, H * 0.7, H * 0.05, r, { sharp: 0.5 })}" fill="#7d8f4e"/>`;
    body += `<rect y="${H * 0.5}" width="${W}" height="${H * 0.12}" fill="#fff" opacity="0.3" filter="url(#blur20)"/>`;
    // Frailejones
    for (let i = 0; i < 16; i++) {
      const x = r() * W, y = H * (0.74 + r() * 0.24), h = 60 + (y / H) * 180 * (0.6 + r() * 0.6), sc = h / 200;
      body += `<rect x="${f(x - 9 * sc)}" y="${f(y - h)}" width="${f(18 * sc)}" height="${f(h)}" rx="${f(8 * sc)}" fill="#5b4a3a"/>`;
      for (let k = 0; k < 14; k++) {
        const a = (k / 14) * Math.PI * 2;
        body += `<ellipse cx="${f(x + Math.cos(a) * 28 * sc)}" cy="${f(y - h + Math.sin(a) * 12 * sc)}" rx="${f(34 * sc)}" ry="${f(9 * sc)}" transform="rotate(${f((a * 180) / Math.PI)} ${f(x + Math.cos(a) * 28 * sc)} ${f(y - h + Math.sin(a) * 12 * sc)})" fill="${k % 2 ? '#b9c49a' : '#d6dcb8'}"/>`;
      }
      body += `<circle cx="${f(x)}" cy="${f(y - h - 6 * sc)}" r="${f(9 * sc)}" fill="#f2c94c"/>`;
    }
    return wrap(W, H, [s.def], body);
  },

  caribe({ W, H, r, boats = true }) {
    const s = sky(W, H, '#3fb6e8', '#8fdcf2', '#e8f8fb');
    const sea = linear([[0, '#1fb7c8'], [0.4, '#14a3b8'], [1, '#0b6f9a']]);
    const sand = linear([[0, '#fbe6bf'], [1, '#f2cf93']]);
    const su = sun(W * 0.82, H * 0.18, H * 0.05, '#fffbe8');
    let body = s.svg + su.svg + clouds(W, H * 0.24, 5, r, '#fff', 0.85);
    body += `<rect y="${H * 0.45}" width="${W}" height="${H * 0.55}" fill="url(#${sea.id})"/>`;
    body += `<path d="M${W * 0.05} ${H * 0.452} q${W * 0.06} -${H * 0.035} ${W * 0.13} 0 Z" fill="#2f7d5b"/>`;
    for (let i = 0; i < 18; i++) {
      const y = H * (0.5 + r() * 0.3);
      body += `<path d="M${f(r() * W)} ${f(y)} h${f(40 + r() * 120)}" stroke="#fff" stroke-opacity="${f(0.25 + r() * 0.3)}" stroke-width="3" stroke-linecap="round"/>`;
    }
    body += `<path d="M0 ${H * 0.78} C ${W * 0.3} ${H * 0.7}, ${W * 0.6} ${H * 0.86}, ${W} ${H * 0.74} L${W} ${H} L0 ${H} Z" fill="#e8fbfb" opacity="0.7"/>`;
    body += `<path d="M0 ${H * 0.8} C ${W * 0.3} ${H * 0.73}, ${W * 0.6} ${H * 0.88}, ${W} ${H * 0.77} L${W} ${H} L0 ${H} Z" fill="url(#${sand.id})"/>`;
    if (boats) body += `<g transform="translate(${W * 0.4} ${H * 0.6})"><path d="M-70 0 h140 l-24 22 h-92 Z" fill="#ff5a5f"/><rect x="-40" y="-12" width="80" height="12" fill="#ffc531"/><path d="M0 -12 v-60" stroke="#40312a" stroke-width="4"/></g>`;
    body += palm(W * 0.12, H * 0.98, H * 0.45, W * 0.07, '#1f4f3a', r, 1.2) + palm(W * 0.9, H, H * 0.36, -W * 0.06, '#24573f', r, 1);
    return wrap(W, H, [s.def, sea.def, sand.def, su.def], body);
  },

  sanandres({ W, H, r }) {
    const bands = ['#0b4f8a', '#0d6fa8', '#119ac0', '#22c1c3', '#5fe0cf', '#a8f0dc', '#e9fbf2'];
    let body = `<rect width="${W}" height="${H}" fill="${bands[0]}"/>`;
    bands.forEach((c, i) => {
      body += `<path d="M0 ${f(H * (0.15 + i * 0.1))} C ${W * 0.3} ${f(H * (0.08 + i * 0.1))}, ${W * 0.7} ${f(H * (0.22 + i * 0.1))}, ${W} ${f(H * (0.12 + i * 0.1))} L${W} ${H} L0 ${H} Z" fill="${c}"/>`;
    });
    body += `<ellipse cx="${W * 0.62}" cy="${H * 0.88}" rx="${W * 0.32}" ry="${H * 0.14}" fill="#f6e2b5"/><ellipse cx="${W * 0.64}" cy="${H * 0.92}" rx="${W * 0.26}" ry="${H * 0.1}" fill="#3e8d55"/>`;
    for (let i = 0; i < 26; i++) body += `<circle cx="${f(W * 0.42 + r() * W * 0.44)}" cy="${f(H * 0.86 + r() * H * 0.12)}" r="${f(14 + r() * 18)}" fill="${r() > 0.5 ? '#2d7445' : '#4aa262'}"/>`;
    for (let i = 0; i < 10; i++) body += `<path d="M${f(r() * W)} ${f(H * (0.2 + r() * 0.5))} q20 -8 40 0" stroke="#fff" stroke-opacity="0.5" stroke-width="3" fill="none"/>`;
    body += `<g transform="translate(${W * 0.25} ${H * 0.45}) rotate(-12)"><path d="M-40 0 h80 l-12 14 h-56 Z" fill="#fff"/><path d="M0 0 v-56 l34 46 Z" fill="#ffc531"/></g>`;
    return wrap(W, H, [], body, { vignette: 0.2 });
  },

  cocora({ W, H, r }) {
    const s = sky(W, H, '#9fd3e6', '#d9efe9', '#f3f7ea');
    let body = s.svg + clouds(W, H * 0.18, 4, r, '#fff', 0.7);
    const greens = ['#9fc7a8', '#6fae84', '#3f8c63', '#2a6f4c', '#1c5238'];
    greens.forEach((c, i) => {
      const base = H * (0.38 + i * 0.12);
      body += `<path d="${ridge(W, H, base, H * (0.16 - i * 0.02), r, { freq: 0.8 + i * 0.3, sharp: 0.7 })}" fill="${c}"/>`;
      if (i < 2) body += `<rect y="${f(base)}" width="${W}" height="${f(H * 0.1)}" fill="#fff" opacity="0.35" filter="url(#blur20)"/>`;
      if (i >= 1 && i <= 3) {
        for (let k = 0; k < 6 + i * 2; k++) {
          const x = r() * W, y = base + H * 0.02 + r() * H * 0.05;
          body += waxPalm(x, y, H * (0.12 + i * 0.07) * (0.7 + r() * 0.5), mix(c, '#0f2f22', 0.55));
        }
      }
    });
    return wrap(W, H, [s.def], body);
  },

  coffee({ W, H, r }) {
    const s = sky(W, H, '#7cc6e0', '#cdeee6', '#fbf3df');
    const su = sun(W * 0.2, H * 0.22, H * 0.05, '#fff9e6');
    let body = s.svg + su.svg + clouds(W, H * 0.16, 3, r, '#fff', 0.8);
    body += `<path d="${ridge(W, H, H * 0.38, H * 0.12, r, { sharp: 0.6 })}" fill="#8ab89a"/>`;
    const hills = [['#5fa86a', 0.5], ['#3f8f51', 0.64], ['#2d7a42', 0.8]];
    hills.forEach(([c, y], i) => {
      const base = H * y;
      const d = ridge(W, H, base, H * 0.1, r, { freq: 0.6, sharp: 0.3 });
      body += `<path d="${d}" fill="${c}"/>`;
      // Surcos de café
      for (let row = 0; row < 7; row++) {
        let line = '';
        const yy = base + row * H * 0.028 + H * 0.02;
        for (let x = 0; x < W; x += 26 + i * 6) {
          const wob = Math.sin(x / (180 + row * 20) + row) * H * 0.03;
          line += `<circle cx="${f(x + (row % 2) * 12)}" cy="${f(yy + wob)}" r="${f(7 + i * 3)}"/>`;
        }
        body += `<g fill="${mix(c, '#0f3d22', 0.45)}">${line}</g>`;
      }
      if (i === 0) body += `<g transform="translate(${W * 0.66} ${base - H * 0.02})"><rect x="-70" y="-60" width="140" height="60" fill="#fffaf0"/><path d="M-84 -60 L0 -100 L84 -60 Z" fill="#c4452f"/><rect x="-62" y="-44" width="124" height="10" fill="#1d6fe0"/><rect x="-14" y="-30" width="28" height="30" fill="#7a3d1f"/><rect x="-54" y="-30" width="22" height="16" fill="#ff7a1a"/><rect x="32" y="-30" width="22" height="16" fill="#ff7a1a"/></g>`;
    });
    return wrap(W, H, [s.def, su.def], body);
  },

  amazon({ W, H, r }) {
    const s = sky(W, H, '#ff9a6a', '#ffcf8f', '#ffe9c2');
    const su = sun(W * 0.5, H * 0.32, H * 0.08, '#fff3d0');
    const river = linear([[0, '#ffd59a'], [1, '#c77b4a']]);
    let body = s.svg + su.svg + clouds(W, H * 0.2, 4, r, '#ffe1c7', 0.6);
    body += canopy(W, H * 0.42, 40, 40, ['#5c7d4f'], r, 1);
    body += `<path d="M${W * 0.42} ${H * 0.45} C ${W * 0.3} ${H * 0.6}, ${W * 0.7} ${H * 0.7}, ${W * 0.45} ${H} L${W * 0.75} ${H} C ${W * 0.95} ${H * 0.72}, ${W * 0.5} ${H * 0.6}, ${W * 0.58} ${H * 0.45} Z" fill="url(#${river.id})"/>`;
    body += canopy(W * 0.42, H * 0.55, 60, 12, ['#3e6b3a', '#2c5530', '#1e4226'], r, 3);
    body += `<g transform="translate(${W * 0.62} 0)">${canopy(W * 0.4, H * 0.58, 60, 12, ['#3e6b3a', '#2c5530', '#1e4226'], r, 3)}</g>`;
    body += `<path d="M${W * 0.5} ${H * 0.82} q30 -26 60 -6 q-26 2 -34 16 Z" fill="#ff8fb1"/>`;
    body += birds(W * 0.3, H * 0.2, 6, r, '#5b2e1f');
    return wrap(W, H, [s.def, su.def, river.def], body);
  },

  guajira({ W, H, r }) {
    const s = sky(W, H, '#ff8a5c', '#ffc07a', '#ffe7b8');
    const su = sun(W * 0.3, H * 0.4, H * 0.07, '#fff1cf');
    const sea = linear([[0, '#2a9fb5'], [1, '#13738f']]);
    let body = s.svg + su.svg;
    body += `<rect y="${H * 0.45}" width="${W}" height="${H * 0.2}" fill="url(#${sea.id})"/>`;
    const dunes = ['#f4b46a', '#eb9a52', '#e0843f', '#cf6c2e'];
    dunes.forEach((c, i) => {
      const base = H * (0.55 + i * 0.12);
      body += `<path d="M0 ${f(base)} C ${f(W * (0.2 + r() * 0.2))} ${f(base - H * 0.12)}, ${f(W * (0.5 + r() * 0.2))} ${f(base + H * 0.08)}, ${W} ${f(base - H * 0.05)} L${W} ${H} L0 ${H} Z" fill="${c}"/>`;
      body += `<path d="M0 ${f(base + 8)} C ${W * 0.3} ${f(base - H * 0.08)}, ${W * 0.6} ${f(base + H * 0.1)}, ${W} ${f(base)}" stroke="#ffd9a3" stroke-width="3" fill="none" opacity="0.6"/>`;
    });
    for (let i = 0; i < 5; i++) {
      const x = W * (0.55 + r() * 0.4), y = H * (0.72 + r() * 0.2), h = 50 + r() * 80;
      body += `<g fill="#3d6b3a"><rect x="${f(x - 7)}" y="${f(y - h)}" width="14" height="${f(h)}" rx="7"/><rect x="${f(x - 30)}" y="${f(y - h * 0.7)}" width="12" height="${f(h * 0.35)}" rx="6"/><rect x="${f(x - 30)}" y="${f(y - h * 0.4)}" width="26" height="10" rx="5"/></g>`;
    }
    return wrap(W, H, [s.def, su.def, sea.def], body);
  },

  canocristales({ W, H, r }) {
    const bank = linear([[0, '#4f8a3a'], [1, '#2f6b2a']]);
    let body = `<rect width="${W}" height="${H}" fill="url(#${bank.id})"/>`;
    body += `<path d="M${W * 0.35} 0 C ${W * 0.2} ${H * 0.3}, ${W * 0.65} ${H * 0.55}, ${W * 0.3} ${H} L${W * 0.78} ${H} C ${W * 0.98} ${H * 0.55}, ${W * 0.48} ${H * 0.3}, ${W * 0.68} 0 Z" fill="#c9b79a"/>`;
    const algae = ['#e5174f', '#ff3d6e', '#ff7a1a', '#ffc531', '#2bb673', '#7b3fe4', '#ff5aa0'];
    for (let i = 0; i < 90; i++) {
      const t = r();
      const cx = W * (0.35 + 0.33 * Math.sin(t * 3.2) * 0.3 + 0.17) + (r() - 0.5) * W * 0.28;
      const cy = t * H;
      body += `<ellipse cx="${f(cx)}" cy="${f(cy)}" rx="${f(20 + r() * 70)}" ry="${f(8 + r() * 26)}" fill="${algae[Math.floor(r() * algae.length)]}" opacity="${f(0.65 + r() * 0.35)}" transform="rotate(${f(r() * 60 - 30)} ${f(cx)} ${f(cy)})"/>`;
    }
    for (let i = 0; i < 16; i++) {
      body += `<path d="M${f(W * (0.3 + r() * 0.4))} ${f(r() * H)} q30 10 70 -4" stroke="#bff3ff" stroke-width="4" fill="none" opacity="0.7" stroke-linecap="round"/>`;
    }
    body += canopy(W * 0.3, H * 0.05, 50, 6, ['#2c5530', '#3e6b3a'], r, 2) + `<g transform="translate(${W * 0.78} ${H * 0.1})">${canopy(W * 0.3, 0, 50, 6, ['#2c5530', '#3e6b3a'], r, 2)}</g>`;
    return wrap(W, H, [bank.def], body, { vignette: 0.32 });
  },

  colonial({ W, H, r, palette }) {
    const p = palette || ['#ffc531', '#ff7a1a', '#1d9bf0', '#ff4fa3', '#2bb673', '#7b3fe4', '#ff5a5f'];
    const s = sky(W, H, '#2c8fe0', '#8fd0f2', '#e8f6fb');
    let body = s.svg + clouds(W, H * 0.15, 3, r, '#fff', 0.8);
    body += `<g transform="translate(${W * 0.72} ${H * 0.12})"><rect x="-40" y="0" width="80" height="${H * 0.4}" fill="#f6e7d0"/><path d="M-48 0 L0 -70 L48 0 Z" fill="#c4452f"/><rect x="-14" y="30" width="28" height="44" rx="14" fill="#5a3a2a"/><circle cx="0" cy="-20" r="6" fill="#ffc531"/></g>`;
    let x = -20;
    const ground = H * 0.82;
    while (x < W) {
      const w = 160 + r() * 140, h = H * (0.32 + r() * 0.16), c = p[Math.floor(r() * p.length)];
      body += `<rect x="${f(x)}" y="${f(ground - h)}" width="${f(w)}" height="${f(h)}" fill="${c}"/>`;
      body += `<rect x="${f(x)}" y="${f(ground - h - 14)}" width="${f(w)}" height="16" fill="#b5502f"/>`;
      body += `<rect x="${f(x)}" y="${f(ground - 40)}" width="${f(w)}" height="40" fill="${mix(c, '#000', 0.25)}"/>`;
      const door = w * 0.28;
      body += `<rect x="${f(x + w * 0.36)}" y="${f(ground - h * 0.42)}" width="${f(door)}" height="${f(h * 0.42)}" rx="${f(door / 2)}" fill="#4a2a1d"/>`;
      body += `<rect x="${f(x + w * 0.12)}" y="${f(ground - h * 0.85)}" width="${f(w * 0.24)}" height="${f(h * 0.25)}" fill="#fff" opacity="0.9"/><rect x="${f(x + w * 0.64)}" y="${f(ground - h * 0.85)}" width="${f(w * 0.24)}" height="${f(h * 0.25)}" fill="#fff" opacity="0.9"/>`;
      body += `<rect x="${f(x + w * 0.08)}" y="${f(ground - h * 0.6)}" width="${f(w * 0.84)}" height="8" fill="#5a3a2a"/>`;
      for (let k = 0; k < 9; k++) body += `<rect x="${f(x + w * 0.1 + k * w * 0.1)}" y="${f(ground - h * 0.6 - 30)}" width="4" height="30" fill="#5a3a2a"/>`;
      if (r() > 0.4) for (let k = 0; k < 18; k++) body += `<circle cx="${f(x + r() * w * 0.5)}" cy="${f(ground - h + r() * h * 0.35)}" r="${f(8 + r() * 10)}" fill="${r() > 0.3 ? '#ff2d87' : '#2f7d3a'}"/>`;
      x += w;
    }
    body += `<rect y="${ground}" width="${W}" height="${H - ground}" fill="#d9c3a3"/>`;
    for (let i = 0; i < 40; i++) body += `<rect x="${f(r() * W)}" y="${f(ground + r() * (H - ground))}" width="${f(30 + r() * 30)}" height="6" rx="3" fill="#bfa47f"/>`;
    return wrap(W, H, [s.def], body);
  },

  llanos({ W, H, r }) {
    const s = sky(W, H, '#3a2c6b', '#e5577a', '#ffb067');
    const su = sun(W * 0.5, H * 0.56, H * 0.12, '#ffdf8a', '#ffd27a');
    let body = s.svg + su.svg + clouds(W, H * 0.25, 4, r, '#ff9fb0', 0.4);
    body += `<rect y="${H * 0.6}" width="${W}" height="${H * 0.4}" fill="#4a2b3f"/>`;
    body += `<rect y="${H * 0.7}" width="${W}" height="${H * 0.08}" fill="#ffb067" opacity="0.5"/><rect x="${W * 0.38}" y="${H * 0.7}" width="${W * 0.24}" height="${H * 0.08}" fill="#ffdf8a" opacity="0.6"/>`;
    for (let i = 0; i < 9; i++) body += palm(r() * W, H * (0.62 + r() * 0.04), H * (0.12 + r() * 0.1), (r() - 0.5) * 30, '#2a1830', r, 0.55);
    body += `<path d="M0 ${H * 0.82} Q ${W * 0.5} ${H * 0.76} ${W} ${H * 0.84} L${W} ${H} L0 ${H} Z" fill="#2a1830"/>`;
    body += birds(W * 0.6, H * 0.3, 7, r, '#2a1830');
    return wrap(W, H, [s.def, su.def], body);
  },

  pacific({ W, H, r }) {
    const s = sky(W, H, '#7f9aa8', '#b7c9cc', '#dfe7e2');
    const sea = linear([[0, '#4f8199'], [1, '#1f4a63']]);
    let body = s.svg + `<rect y="${H * 0.2}" width="${W}" height="${H * 0.3}" fill="#fff" opacity="0.4" filter="url(#blur20)"/>`;
    body += `<path d="${ridge(W * 0.55, H, H * 0.42, H * 0.18, r, { sharp: 0.8 })}" fill="#2f5b45"/>`;
    body += canopy(W * 0.5, H * 0.45, 46, 14, ['#1f4a33', '#183d2a'], r, 2);
    body += `<rect y="${H * 0.52}" width="${W}" height="${H * 0.48}" fill="url(#${sea.id})"/>`;
    body += `<g transform="translate(${W * 0.68} ${H * 0.62})" fill="#1a2d38"><path d="M0 0 C -10 -40, -70 -90, -120 -80 C -80 -60, -40 -40, -10 10 Z"/><path d="M0 0 C 10 -40, 70 -90, 120 -80 C 80 -60, 40 -40, 10 10 Z"/><rect x="-14" y="0" width="28" height="60" rx="10"/></g>`;
    for (let i = 0; i < 20; i++) body += `<circle cx="${f(W * 0.68 + (r() - 0.5) * 220)}" cy="${f(H * 0.66 + r() * 20)}" r="${f(2 + r() * 6)}" fill="#fff" opacity="0.8"/>`;
    for (let i = 0; i < 16; i++) body += `<path d="M${f(r() * W)} ${f(H * (0.6 + r() * 0.35))} h${f(30 + r() * 80)}" stroke="#cfe6ee" stroke-opacity="0.4" stroke-width="3"/>`;
    return wrap(W, H, [s.def, sea.def], body);
  },

  tatacoa({ W, H, r, night = false }) {
    const s = night ? sky(W, H, '#0d1440', '#2b2d6e', '#7a4a7a') : sky(W, H, '#69b6e8', '#bfe3f2', '#f7e3c8');
    let body = s.svg;
    if (night) for (let i = 0; i < 220; i++) body += `<circle cx="${f(r() * W)}" cy="${f(r() * H * 0.6)}" r="${f(r() * 2.2)}" fill="#fff" opacity="${f(0.4 + r() * 0.6)}"/>`;
    const tones = night ? ['#4a2f4f', '#3a2440', '#2a1a30'] : ['#e7a06b', '#d17a4a', '#b45a34'];
    tones.forEach((c, i) => {
      const base = H * (0.5 + i * 0.15);
      body += `<path d="${ridge(W, H, base, H * 0.12, r, { freq: 2.4, sharp: 2.4, step: 6 })}" fill="${c}"/>`;
      for (let k = 0; k < 8; k++) body += `<path d="M${f(r() * W)} ${f(base + r() * H * 0.1)} v${f(30 + r() * 60)}" stroke="${mix(c, '#000', 0.2)}" stroke-width="4"/>`;
    });
    for (let i = 0; i < 6; i++) {
      const x = r() * W, y = H * (0.86 + r() * 0.1), h = 60 + r() * 90;
      body += `<g fill="${night ? '#1a1020' : '#4f7a3a'}"><rect x="${f(x - 9)}" y="${f(y - h)}" width="18" height="${f(h)}" rx="9"/><rect x="${f(x + 9)}" y="${f(y - h * 0.65)}" width="30" height="12" rx="6"/><rect x="${f(x + 27)}" y="${f(y - h * 0.9)}" width="12" height="${f(h * 0.3)}" rx="6"/></g>`;
    }
    return wrap(W, H, [s.def], body);
  },

  city({ W, H, r }) {
    const s = sky(W, H, '#25285e', '#7a4f9a', '#ffa07a');
    let body = s.svg;
    body += `<path d="${ridge(W, H, H * 0.45, H * 0.15, r, { sharp: 0.7 })}" fill="#3b3f78"/>`;
    body += `<path d="${ridge(W, H, H * 0.55, H * 0.1, r, { sharp: 0.5 })}" fill="#2e3166"/>`;
    for (let i = 0; i < 300; i++) body += `<circle cx="${f(r() * W)}" cy="${f(H * (0.48 + r() * 0.2))}" r="${f(1 + r() * 2)}" fill="#ffd27a" opacity="${f(0.5 + r() * 0.5)}"/>`;
    let x = 0;
    while (x < W) {
      const w = 40 + r() * 90, h = H * (0.08 + r() * 0.28);
      body += `<rect x="${f(x)}" y="${f(H * 0.92 - h)}" width="${f(w)}" height="${f(h + H * 0.08)}" fill="${mix('#1a1b44', '#2b2d6e', r())}"/>`;
      for (let wy = H * 0.92 - h + 10; wy < H * 0.9; wy += 18) for (let wx = x + 8; wx < x + w - 8; wx += 16) if (r() > 0.45) body += `<rect x="${f(wx)}" y="${f(wy)}" width="7" height="9" fill="#ffd27a" opacity="${f(0.6 + r() * 0.4)}"/>`;
      x += w + 4;
    }
    body += `<path d="M0 ${H * 0.32} L${W} ${H * 0.18}" stroke="#d9d9f0" stroke-width="3"/>`;
    for (let i = 1; i < 6; i++) { const cx = (W / 6) * i, cy = H * 0.32 - (H * 0.14 * cx) / W; body += `<g transform="translate(${f(cx)} ${f(cy)})"><path d="M0 0 v18" stroke="#d9d9f0" stroke-width="3"/><rect x="-18" y="18" width="36" height="28" rx="6" fill="${['#ffc531', '#ff5a5f', '#12b5b0'][i % 3]}"/><rect x="-12" y="24" width="24" height="10" fill="#1a1b44" opacity="0.6"/></g>`; }
    return wrap(W, H, [s.def], body);
  },

  waterfall({ W, H, r }) {
    const s = sky(W, H, '#bfe6f2', '#e6f5f0', '#f2f7ea');
    const water = linear([[0, '#e8fbff'], [1, '#9fdcef']], 0, 0, 1, 0);
    let body = s.svg;
    body += `<path d="M0 ${H * 0.15} L${W * 0.38} ${H * 0.22} L${W * 0.4} ${H * 0.75} L0 ${H * 0.8} Z" fill="#4a5f4a"/><path d="M${W} ${H * 0.12} L${W * 0.55} ${H * 0.22} L${W * 0.53} ${H * 0.75} L${W} ${H * 0.8} Z" fill="#3e5442"/>`;
    body += canopy(W * 0.4, H * 0.14, 50, 7, ['#2f6b3a', '#3e8a4a'], r, 2) + `<g transform="translate(${W * 0.55} 0)">${canopy(W * 0.45, H * 0.12, 50, 8, ['#2f6b3a', '#3e8a4a'], r, 2)}</g>`;
    body += `<rect x="${W * 0.39}" y="${H * 0.2}" width="${W * 0.15}" height="${H * 0.58}" fill="url(#${water.id})"/>`;
    for (let i = 0; i < 26; i++) body += `<path d="M${f(W * 0.395 + r() * W * 0.14)} ${f(H * 0.22)} v${f(H * (0.2 + r() * 0.36))}" stroke="#fff" stroke-width="${f(2 + r() * 4)}" opacity="0.7"/>`;
    body += `<ellipse cx="${W * 0.465}" cy="${H * 0.8}" rx="${W * 0.38}" ry="${H * 0.1}" fill="#2a8fa8"/><ellipse cx="${W * 0.465}" cy="${H * 0.77}" rx="${W * 0.14}" ry="${H * 0.04}" fill="#fff" opacity="0.7" filter="url(#blur8)"/>`;
    body += `<rect y="${H * 0.86}" width="${W}" height="${H * 0.14}" fill="#2f5b3a"/>` + canopy(W, H * 0.92, 60, 18, ['#24523a', '#1c4230'], r, 2);
    return wrap(W, H, [s.def, water.def], body);
  },

  // ---------- Gastronomía (vista cenital) ----------
  plate({ W, H, r, dish = 'bandeja', cloth = '#ff5a5f' }) {
    const cl = cloth;
    let body = `<rect width="${W}" height="${H}" fill="${cl}"/>`;
    const step = 80;
    for (let x = 0; x < W; x += step) body += `<rect x="${x}" y="0" width="${step / 2}" height="${H}" fill="#fff" opacity="0.12"/>`;
    for (let y = 0; y < H; y += step) body += `<rect x="0" y="${y}" width="${W}" height="${step / 2}" fill="#fff" opacity="0.12"/>`;
    const cx = W * 0.5, cy = H * 0.52, R = Math.min(W, H) * 0.36;
    body += `<ellipse cx="${cx + 18}" cy="${cy + 24}" rx="${R * 1.05}" ry="${R * 1.05}" fill="#000" opacity="0.18" filter="url(#blur20)"/>`;
    const plateColor = dish === 'ajiaco' || dish === 'sancocho' ? '#7a4a2a' : '#fdfaf4';
    body += `<circle cx="${cx}" cy="${cy}" r="${R}" fill="${plateColor}"/><circle cx="${cx}" cy="${cy}" r="${R * 0.82}" fill="${dish === 'ajiaco' || dish === 'sancocho' ? '#8f5a33' : '#f4efe4'}"/>`;
    const at = (a, d) => [cx + Math.cos(a) * R * d, cy + Math.sin(a) * R * d];
    const blob = (x, y, rx, ry, c, rot = 0) => `<ellipse cx="${f(x)}" cy="${f(y)}" rx="${f(rx)}" ry="${f(ry)}" fill="${c}" transform="rotate(${f(rot)} ${f(x)} ${f(y)})"/>`;
    if (dish === 'bandeja') {
      // Arroz
      let [x, y] = at(-2.4, 0.42);
      body += blob(x, y, R * 0.28, R * 0.24, '#fffdf6');
      for (let i = 0; i < 60; i++) body += blob(x + (r() - 0.5) * R * 0.45, y + (r() - 0.5) * R * 0.38, 5, 2.2, '#efe9da', r() * 180);
      // Fríjoles
      [x, y] = at(-0.8, 0.42);
      body += `<circle cx="${f(x)}" cy="${f(y)}" r="${f(R * 0.27)}" fill="#6b2a1f"/>`;
      for (let i = 0; i < 50; i++) body += blob(x + (r() - 0.5) * R * 0.42, y + (r() - 0.5) * R * 0.42, 9, 6, '#8a3a26', r() * 180);
      // Huevo
      [x, y] = at(0.6, 0.45);
      body += `<path d="M${f(x - R * 0.2)} ${f(y)} q${f(R * 0.05)} ${f(-R * 0.22)} ${f(R * 0.22)} ${f(-R * 0.16)} q${f(R * 0.2)} ${f(R * 0.05)} ${f(R * 0.14)} ${f(R * 0.22)} q${f(-R * 0.1)} ${f(R * 0.14)} ${f(-R * 0.3)} ${f(R * 0.06)} Z" fill="#fffef9"/><circle cx="${f(x)}" cy="${f(y)}" r="${f(R * 0.08)}" fill="#ffb21a"/>`;
      // Aguacate
      [x, y] = at(1.7, 0.45);
      body += blob(x, y, R * 0.2, R * 0.13, '#3f7a2a', 30) + blob(x, y, R * 0.16, R * 0.1, '#c9dc6a', 30) + `<circle cx="${f(x + 8)}" cy="${f(y + 4)}" r="${f(R * 0.05)}" fill="#7a4a2a"/>`;
      // Chicharrón
      [x, y] = at(2.6, 0.44);
      body += `<rect x="${f(x - R * 0.18)}" y="${f(y - R * 0.08)}" width="${f(R * 0.36)}" height="${f(R * 0.16)}" rx="12" fill="#c9762e" transform="rotate(-20 ${f(x)} ${f(y)})"/>`;
      for (let i = 0; i < 4; i++) body += `<rect x="${f(x - R * 0.15 + i * R * 0.08)}" y="${f(y - R * 0.07)}" width="6" height="${f(R * 0.14)}" fill="#8a4a1f" transform="rotate(-20 ${f(x)} ${f(y)})"/>`;
      // Arepa
      body += `<circle cx="${f(cx)}" cy="${f(cy)}" r="${f(R * 0.14)}" fill="#f6dc8a"/><circle cx="${f(cx - 10)}" cy="${f(cy - 6)}" r="5" fill="#d9a54a"/><circle cx="${f(cx + 14)}" cy="${f(cy + 8)}" r="4" fill="#d9a54a"/>`;
      // Tajada de plátano
      [x, y] = at(-1.6, 0.66);
      body += blob(x, y, R * 0.18, R * 0.06, '#e8a33a', 80);
    } else if (dish === 'ajiaco' || dish === 'sancocho') {
      body += `<circle cx="${cx}" cy="${cy}" r="${R * 0.72}" fill="${dish === 'ajiaco' ? '#f0c95a' : '#e3b768'}"/>`;
      for (let i = 0; i < 22; i++) body += `<circle cx="${f(cx + (r() - 0.5) * R * 1.1)}" cy="${f(cy + (r() - 0.5) * R * 1.1)}" r="${f(6 + r() * 12)}" fill="#fbe7a8" opacity="0.6"/>`;
      const [x, y] = at(-0.5, 0.25);
      body += `<g transform="rotate(-30 ${f(x)} ${f(y)})"><rect x="${f(x - R * 0.12)}" y="${f(y - R * 0.3)}" width="${f(R * 0.24)}" height="${f(R * 0.6)}" rx="${f(R * 0.12)}" fill="#ffd23a"/>`;
      for (let i = 0; i < 12; i++) body += `<circle cx="${f(x - R * 0.06 + (i % 3) * R * 0.06)}" cy="${f(y - R * 0.24 + Math.floor(i / 3) * R * 0.13)}" r="${f(R * 0.035)}" fill="#f1b51c"/>`;
      body += '</g>';
      if (dish === 'ajiaco') {
        body += blob(cx + R * 0.3, cy + R * 0.25, R * 0.16, R * 0.1, '#fffdf2') + blob(cx + R * 0.3, cy + R * 0.25, R * 0.12, R * 0.07, '#f4f0e0');
        for (let i = 0; i < 18; i++) body += `<circle cx="${f(cx + R * 0.25 + (r() - 0.5) * R * 0.2)}" cy="${f(cy - R * 0.3 + (r() - 0.5) * R * 0.2)}" r="5" fill="#4b6b2a"/>`;
        for (let i = 0; i < 10; i++) body += blob(cx + (r() - 0.5) * R, cy + (r() - 0.5) * R, 14, 5, '#3f8a2a', r() * 180);
      } else {
        for (let i = 0; i < 5; i++) body += blob(cx + (r() - 0.5) * R, cy + (r() - 0.5) * R, R * 0.12, R * 0.08, '#fff2c9', r() * 180);
        for (let i = 0; i < 4; i++) body += blob(cx + (r() - 0.5) * R, cy + (r() - 0.5) * R, R * 0.14, R * 0.1, '#c98a4a', r() * 180);
        for (let i = 0; i < 14; i++) body += blob(cx + (r() - 0.5) * R * 1.1, cy + (r() - 0.5) * R * 1.1, 10, 4, '#3f8a2a', r() * 180);
      }
    } else if (dish === 'arepas') {
      for (let i = 0; i < 5; i++) {
        const [x, y] = at((i / 5) * Math.PI * 2 + 0.3, 0.42);
        body += `<circle cx="${f(x)}" cy="${f(y)}" r="${f(R * 0.25)}" fill="#f4d47e"/><circle cx="${f(x)}" cy="${f(y)}" r="${f(R * 0.25)}" fill="none" stroke="#e2b55a" stroke-width="6"/>`;
        for (let k = 0; k < 6; k++) body += `<circle cx="${f(x + (r() - 0.5) * R * 0.3)}" cy="${f(y + (r() - 0.5) * R * 0.3)}" r="${f(4 + r() * 6)}" fill="#c98a3a" opacity="0.8"/>`;
      }
      body += `<circle cx="${cx}" cy="${cy}" r="${R * 0.16}" fill="#fff8e0"/><circle cx="${cx}" cy="${cy}" r="${R * 0.1}" fill="#ffe28a"/>`;
    } else if (dish === 'empanadas') {
      for (let i = 0; i < 6; i++) {
        const a = (i / 6) * Math.PI * 2;
        const [x, y] = at(a, 0.45);
        const deg = (a * 180) / Math.PI + 90;
        body += `<g transform="rotate(${f(deg)} ${f(x)} ${f(y)})"><path d="M${f(x - R * 0.24)} ${f(y)} A ${f(R * 0.24)} ${f(R * 0.24)} 0 0 1 ${f(x + R * 0.24)} ${f(y)} Z" fill="#e9a23b"/>`;
        for (let k = 0; k < 7; k++) body += `<circle cx="${f(x - R * 0.2 + k * R * 0.067)}" cy="${f(y - 4)}" r="5" fill="#c97a1f"/>`;
        body += '</g>';
      }
      body += `<circle cx="${cx}" cy="${cy}" r="${R * 0.15}" fill="#fff"/><circle cx="${cx}" cy="${cy}" r="${R * 0.12}" fill="#2f8a3a"/>`;
      for (let i = 0; i < 14; i++) body += `<circle cx="${f(cx + (r() - 0.5) * R * 0.2)}" cy="${f(cy + (r() - 0.5) * R * 0.2)}" r="4" fill="#ff3d2e"/>`;
    } else if (dish === 'fruits') {
      body = `<rect width="${W}" height="${H}" fill="${cl}"/>`;
      const fruits = [['#ffcf3a', '#fff6c2', 'maracuya'], ['#ff3f8e', '#fffaf4', 'pitahaya'], ['#f29b2a', '#d4e86a', 'lulo'], ['#3f8a2a', '#fffdf6', 'guanabana'], ['#ffd23a', '#fff4c9', 'uchuva'], ['#ff7a1a', '#ffd08a', 'mango']];
      for (let i = 0; i < 14; i++) {
        const [skin, flesh] = fruits[i % fruits.length];
        const x = W * (0.08 + r() * 0.84), y = H * (0.1 + r() * 0.8), rr = 70 + r() * 70;
        body += `<circle cx="${f(x + 10)}" cy="${f(y + 14)}" r="${f(rr)}" fill="#000" opacity="0.15" filter="url(#blur8)"/><circle cx="${f(x)}" cy="${f(y)}" r="${f(rr)}" fill="${skin}"/><circle cx="${f(x)}" cy="${f(y)}" r="${f(rr * 0.82)}" fill="${flesh}"/>`;
        for (let k = 0; k < 16; k++) body += `<circle cx="${f(x + (r() - 0.5) * rr)}" cy="${f(y + (r() - 0.5) * rr)}" r="${f(3 + r() * 4)}" fill="${i % 6 === 1 ? '#1a1a1a' : mix(skin, '#3a1a0a', 0.5)}"/>`;
      }
    } else if (dish === 'cazuela') {
      body += `<circle cx="${cx}" cy="${cy}" r="${R * 0.74}" fill="#e2602e"/>`;
      for (let i = 0; i < 10; i++) body += `<path d="M${f(cx + (r() - 0.5) * R)} ${f(cy + (r() - 0.5) * R)} q20 -30 40 0 q-20 10 -40 0" fill="#ff9a7a"/>`;
      for (let i = 0; i < 6; i++) { const x = cx + (r() - 0.5) * R, y = cy + (r() - 0.5) * R; body += blob(x, y, 24, 14, '#2a2a3a', r() * 180) + blob(x, y, 18, 9, '#ffb38a', r() * 180); }
      for (let i = 0; i < 20; i++) body += `<circle cx="${f(cx + (r() - 0.5) * R * 1.2)}" cy="${f(cy + (r() - 0.5) * R * 1.2)}" r="4" fill="#3f8a2a"/>`;
      body += blob(cx + R * 0.5, cy - R * 0.4, R * 0.12, R * 0.06, '#c9e86a', 40);
    } else if (dish === 'tamal') {
      body += `<path d="M${cx - R * 0.7} ${cy} L${cx} ${cy - R * 0.72} L${cx + R * 0.7} ${cy} L${cx} ${cy + R * 0.72} Z" fill="#2f7a3a"/><path d="M${cx - R * 0.5} ${cy} L${cx} ${cy - R * 0.5} L${cx + R * 0.5} ${cy} L${cx} ${cy + R * 0.5} Z" fill="#f2c25a"/>`;
      for (let i = 0; i < 6; i++) body += `<path d="M${f(cx - R * 0.65 + i * R * 0.22)} ${f(cy - R * 0.05)} l${f(R * 0.2)} ${f(-R * 0.2)}" stroke="#1f5a2a" stroke-width="4"/>`;
      body += blob(cx, cy, R * 0.12, R * 0.08, '#c95a2a') + blob(cx - R * 0.18, cy + R * 0.1, R * 0.07, R * 0.05, '#ff9a3a') + blob(cx + R * 0.15, cy - R * 0.12, R * 0.06, R * 0.05, '#fff3d0');
    }
    // Cubiertos
    body += `<rect x="${f(cx + R * 1.12)}" y="${f(cy - R * 0.7)}" width="16" height="${f(R * 1.4)}" rx="8" fill="#d9d4c8"/><rect x="${f(cx - R * 1.12 - 16)}" y="${f(cy - R * 0.7)}" width="16" height="${f(R * 1.4)}" rx="8" fill="#d9d4c8"/>`;
    return wrap(W, H, [], body, { vignette: 0.2 });
  },

  juice({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#12b5b0"/>`;
    for (let i = 0; i < 9; i++) body += `<circle cx="${f(r() * W)}" cy="${f(r() * H)}" r="${f(60 + r() * 120)}" fill="#fff" opacity="0.07"/>`;
    const glasses = [['#ffb21a', 0.3], ['#ff3f8e', 0.52], ['#7ac943', 0.74]];
    glasses.forEach(([c, x]) => {
      const gx = W * x, gy = H * 0.75, gw = W * 0.14, gh = H * 0.45;
      body += `<ellipse cx="${gx}" cy="${gy + 10}" rx="${gw * 0.6}" ry="16" fill="#000" opacity="0.2" filter="url(#blur8)"/>`;
      body += `<path d="M${gx - gw / 2} ${gy - gh} L${gx - gw * 0.4} ${gy} L${gx + gw * 0.4} ${gy} L${gx + gw / 2} ${gy - gh} Z" fill="#fff" opacity="0.5"/>`;
      body += `<path d="M${gx - gw * 0.46} ${gy - gh * 0.82} L${gx - gw * 0.4} ${gy - 6} L${gx + gw * 0.4} ${gy - 6} L${gx + gw * 0.46} ${gy - gh * 0.82} Z" fill="${c}"/>`;
      body += `<rect x="${gx + gw * 0.12}" y="${gy - gh * 1.25}" width="12" height="${gh * 0.7}" fill="#ff5a5f" transform="rotate(12 ${gx} ${gy - gh})"/>`;
      body += `<circle cx="${gx - gw * 0.4}" cy="${gy - gh}" r="${gw * 0.22}" fill="${mix(c, '#fff', 0.3)}"/><circle cx="${gx - gw * 0.4}" cy="${gy - gh}" r="${gw * 0.16}" fill="${mix(c, '#fff', 0.6)}"/>`;
    });
    return wrap(W, H, [], body, { vignette: 0.18 });
  },

  // ---------- Café ----------
  cup({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#6b3d22"/>`;
    for (let i = 0; i < 18; i++) body += `<rect x="0" y="${f((H / 18) * i)}" width="${W}" height="${f(H / 36)}" fill="#5a321b" opacity="0.6"/>`;
    for (let i = 0; i < 70; i++) {
      const x = r() * W, y = r() * H, a = r() * 180;
      body += `<g transform="rotate(${f(a)} ${f(x)} ${f(y)})"><ellipse cx="${f(x)}" cy="${f(y)}" rx="22" ry="15" fill="#3a1d0f"/><path d="M${f(x - 18)} ${f(y)} q18 6 36 0" stroke="#24110a" stroke-width="3" fill="none"/></g>`;
    }
    const cx = W * 0.5, cy = H * 0.5, R = Math.min(W, H) * 0.3;
    body += `<circle cx="${cx + 20}" cy="${cy + 26}" r="${R * 1.45}" fill="#000" opacity="0.3" filter="url(#blur20)"/><circle cx="${cx}" cy="${cy}" r="${R * 1.4}" fill="#f6f1e7"/><circle cx="${cx}" cy="${cy}" r="${R * 1.25}" fill="#e9e1d1"/>`;
    body += `<rect x="${cx + R * 0.9}" y="${cy - R * 0.18}" width="${R * 0.6}" height="${R * 0.36}" rx="${R * 0.18}" fill="#fffaf2"/>`;
    body += `<circle cx="${cx}" cy="${cy}" r="${R}" fill="#fffaf2"/><circle cx="${cx}" cy="${cy}" r="${R * 0.86}" fill="#c98a4a"/><circle cx="${cx}" cy="${cy}" r="${R * 0.8}" fill="#8a4a22"/>`;
    body += `<path d="M${cx} ${cy - R * 0.55} C ${cx + R * 0.5} ${cy - R * 0.5}, ${cx + R * 0.45} ${cy + R * 0.1}, ${cx} ${cy + R * 0.5} C ${cx - R * 0.45} ${cy + R * 0.1}, ${cx - R * 0.5} ${cy - R * 0.5}, ${cx} ${cy - R * 0.55} Z" fill="#f3dcb8"/><path d="M${cx} ${cy - R * 0.3} C ${cx + R * 0.25} ${cy - R * 0.25}, ${cx + R * 0.2} ${cy + R * 0.05}, ${cx} ${cy + R * 0.3} C ${cx - R * 0.2} ${cy + R * 0.05}, ${cx - R * 0.25} ${cy - R * 0.25}, ${cx} ${cy - R * 0.3} Z" fill="#a8622f"/>`;
    return wrap(W, H, [], body, { vignette: 0.35 });
  },

  cherries({ W, H, r }) {
    const bg = linear([[0, '#2f6b3a'], [1, '#1c4426']]);
    let body = `<rect width="${W}" height="${H}" fill="url(#${bg.id})"/>`;
    for (let i = 0; i < 26; i++) {
      const x = r() * W, y = r() * H, a = r() * 360, L = 140 + r() * 160;
      body += `<g transform="rotate(${f(a)} ${f(x)} ${f(y)})"><path d="M${f(x)} ${f(y)} q${f(L / 2)} ${f(-L * 0.35)} ${f(L)} 0 q${f(-L / 2)} ${f(L * 0.35)} ${f(-L)} 0" fill="${r() > 0.5 ? '#3f8a4a' : '#4fa05a'}"/><path d="M${f(x)} ${f(y)} h${f(L)}" stroke="#2a5f33" stroke-width="3"/></g>`;
    }
    body += `<path d="M${W * 0.05} ${H * 0.55} C ${W * 0.3} ${H * 0.45}, ${W * 0.6} ${H * 0.6}, ${W * 0.98} ${H * 0.45}" stroke="#6b4a2a" stroke-width="16" fill="none" stroke-linecap="round"/>`;
    for (let i = 0; i < 9; i++) {
      const t = 0.1 + i * 0.1, x = W * t, y = H * (0.5 + Math.sin(t * 6) * 0.04);
      for (let k = 0; k < 7; k++) {
        const cx = x + (r() - 0.5) * 80, cy = y + (r() - 0.5) * 60;
        const c = r() > 0.25 ? (r() > 0.4 ? '#d4202f' : '#a3142a') : (r() > 0.5 ? '#f2a51a' : '#8fbf3a');
        body += `<circle cx="${f(cx)}" cy="${f(cy)}" r="26" fill="${c}"/><circle cx="${f(cx - 8)}" cy="${f(cy - 9)}" r="7" fill="#fff" opacity="0.45"/>`;
      }
    }
    return wrap(W, H, [bg.def], body);
  },

  beans({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#e9dcc3"/>`;
    for (let y = 0; y < H; y += 60) body += `<rect x="0" y="${y}" width="${W}" height="2" fill="#cbb994"/>`;
    const tones = ['#c9a36a', '#b8864a', '#d8b98a', '#7a4a22', '#5a321b'];
    for (let i = 0; i < 900; i++) {
      const x = r() * W, y = r() * H, a = r() * 180;
      const c = x < W * 0.33 ? tones[Math.floor(r() * 3)] : x < W * 0.66 ? tones[1 + Math.floor(r() * 3)] : tones[3 + Math.floor(r() * 2)];
      body += `<g transform="rotate(${f(a)} ${f(x)} ${f(y)})"><ellipse cx="${f(x)}" cy="${f(y)}" rx="15" ry="10" fill="${c}"/><path d="M${f(x - 12)} ${f(y)} q12 4 24 0" stroke="${mix(c, '#000', 0.35)}" stroke-width="2" fill="none"/></g>`;
    }
    body += `<path d="M${W * 0.18} ${H} L${W * 0.3} ${H * 0.7} L${W * 0.34} ${H * 0.72} L${W * 0.24} ${H}" fill="#7a5a3a"/><path d="M${W * 0.25} ${H * 0.75} q60 -40 120 0 q-60 30 -120 0" fill="#9a7a5a"/>`;
    return wrap(W, H, [], body, { vignette: 0.25 });
  },

  pourover({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#f6ecd9"/><rect y="${H * 0.78}" width="${W}" height="${H * 0.22}" fill="#c9a77a"/>`;
    for (let i = 0; i < 8; i++) body += `<rect y="${f(H * 0.78 + i * 14)}" width="${W}" height="3" fill="#b38f62"/>`;
    const cx = W * 0.5;
    body += `<path d="M${cx - 150} ${H * 0.18} L${cx + 150} ${H * 0.18} L${cx + 30} ${H * 0.46} L${cx - 30} ${H * 0.46} Z" fill="#fff" opacity="0.85"/><path d="M${cx - 30} ${H * 0.46} L${cx + 30} ${H * 0.46} L${cx + 130} ${H * 0.76} L${cx - 130} ${H * 0.76} Z" fill="#e8f3f5" opacity="0.8"/>`;
    body += `<path d="M${cx - 112} ${H * 0.64} L${cx + 112} ${H * 0.64} L${cx + 130} ${H * 0.76} L${cx - 130} ${H * 0.76} Z" fill="#6b3a1a"/><rect x="${cx - 40}" y="${H * 0.42}" width="80" height="50" rx="10" fill="#c98a4a"/>`;
    body += `<path d="M${cx} ${H * 0.05} v${H * 0.13}" stroke="#8a5a2a" stroke-width="6" stroke-dasharray="10 8"/>`;
    body += `<g transform="translate(${W * 0.2} ${H * 0.66})"><rect x="-60" y="0" width="120" height="${H * 0.12}" rx="14" fill="#ff7a1a"/><rect x="54" y="20" width="36" height="40" rx="16" fill="none" stroke="#ff7a1a" stroke-width="12"/></g>`;
    for (let i = 0; i < 40; i++) body += `<ellipse cx="${f(W * 0.75 + (r() - 0.5) * 200)}" cy="${f(H * 0.75 + (r() - 0.5) * 30)}" rx="12" ry="8" fill="#5a321b" transform="rotate(${f(r() * 180)} ${f(W * 0.75)} ${f(H * 0.75)})"/>`;
    return wrap(W, H, [], body, { vignette: 0.2 });
  },

  // ---------- Cultura y artesanías ----------
  wayuu({ W, H, r }) {
    const cols = ['#ff3f8e', '#ffc531', '#12b5b0', '#7b3fe4', '#ff7a1a', '#1d6fe0', '#2bb673', '#e5174f'];
    let body = `<rect width="${W}" height="${H}" fill="#1a1530"/>`;
    const band = H / 9;
    for (let b = 0; b < 9; b++) {
      const c1 = cols[b % cols.length], c2 = cols[(b + 3) % cols.length];
      body += `<rect y="${f(b * band)}" width="${W}" height="${f(band)}" fill="${b % 2 ? '#fff6e6' : c1}"/>`;
      const s = band * 0.8;
      for (let x = 0; x < W + s; x += s) {
        const y = b * band + band / 2;
        body += `<path d="M${f(x)} ${f(y - s / 2)} L${f(x + s / 2)} ${f(y)} L${f(x)} ${f(y + s / 2)} L${f(x - s / 2)} ${f(y)} Z" fill="${b % 2 ? c2 : '#fff6e6'}"/><path d="M${f(x)} ${f(y - s / 4)} L${f(x + s / 4)} ${f(y)} L${f(x)} ${f(y + s / 4)} L${f(x - s / 4)} ${f(y)} Z" fill="${b % 2 ? c1 : c2}"/>`;
      }
      body += `<rect y="${f(b * band)}" width="${W}" height="6" fill="#1a1530" opacity="0.5"/>`;
    }
    return wrap(W, H, [], body, { vignette: 0.25, grain: 0.14 });
  },

  mola({ W, H, r }) {
    const layers = ['#e5174f', '#1a1530', '#ff7a1a', '#1a1530', '#ffc531', '#1a1530', '#12b5b0', '#1a1530', '#ff3f8e'];
    let body = '';
    layers.forEach((c, i) => {
      const inset = i * Math.min(W, H) * 0.045;
      body += `<rect x="${f(inset)}" y="${f(inset)}" width="${f(W - inset * 2)}" height="${f(H - inset * 2)}" rx="${f(40 + i * 10)}" fill="${c}"/>`;
    });
    const cx = W / 2, cy = H / 2;
    body += `<g transform="translate(${cx} ${cy})"><path d="M-160 0 C -120 -120, 120 -120, 160 0 C 120 120, -120 120, -160 0 Z" fill="#fff6e6"/><path d="M-110 0 C -80 -80, 80 -80, 110 0 C 80 80, -80 80, -110 0 Z" fill="#7b3fe4"/><circle r="40" fill="#ffc531"/><circle r="16" fill="#1a1530"/><path d="M160 0 l90 -60 v120 Z" fill="#fff6e6"/><path d="M180 0 l50 -30 v60 Z" fill="#e5174f"/></g>`;
    for (let i = 0; i < 40; i++) body += `<rect x="${f(r() * W)}" y="${f(r() * H)}" width="${f(10 + r() * 30)}" height="8" rx="4" fill="${['#ffc531', '#12b5b0', '#fff6e6'][i % 3]}" opacity="0.9"/>`;
    return wrap(W, H, [], body, { vignette: 0.15, grain: 0.14 });
  },

  vueltiao({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#f2a900"/>`;
    const cx = W / 2, cy = H / 2, R = Math.min(W, H) * 0.46;
    body += `<circle cx="${cx + 20}" cy="${cy + 26}" r="${R}" fill="#000" opacity="0.25" filter="url(#blur20)"/>`;
    for (let i = 0; i < 16; i++) {
      const rr = R * (1 - i / 16);
      body += `<circle cx="${cx}" cy="${cy}" r="${f(rr)}" fill="${i % 2 ? '#1a1410' : '#f6ecd0'}"/>`;
      if (i % 2 === 0) {
        for (let k = 0; k < 28; k++) {
          const a = (k / 28) * Math.PI * 2 + i;
          body += `<rect x="${f(cx + Math.cos(a) * rr * 0.97 - 6)}" y="${f(cy + Math.sin(a) * rr * 0.97 - 6)}" width="12" height="12" fill="#1a1410" transform="rotate(45 ${f(cx + Math.cos(a) * rr * 0.97)} ${f(cy + Math.sin(a) * rr * 0.97)})"/>`;
        }
      }
    }
    return wrap(W, H, [], body, { vignette: 0.2, grain: 0.14 });
  },

  flowers({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#fff3e0"/>`;
    const cols = ['#ff3f8e', '#ffc531', '#ff7a1a', '#7b3fe4', '#e5174f', '#ffffff', '#ff9ac8', '#f2e14b'];
    for (let i = 0; i < 46; i++) {
      const x = r() * W, y = r() * H, R = 50 + r() * 90, c = cols[Math.floor(r() * cols.length)], n = 6 + Math.floor(r() * 8);
      body += `<g transform="rotate(${f(r() * 360)} ${f(x)} ${f(y)})">`;
      for (let k = 0; k < n; k++) {
        const a = (k / n) * 360;
        body += `<ellipse cx="${f(x)}" cy="${f(y - R * 0.5)}" rx="${f(R * 0.22)}" ry="${f(R * 0.5)}" fill="${c}" transform="rotate(${f(a)} ${f(x)} ${f(y)})" opacity="0.95"/>`;
      }
      body += `<circle cx="${f(x)}" cy="${f(y)}" r="${f(R * 0.2)}" fill="${c === '#ffc531' ? '#7a3d1f' : '#ffc531'}"/></g>`;
      if (r() > 0.6) body += `<ellipse cx="${f(x + R)}" cy="${f(y + R * 0.4)}" rx="${f(R * 0.5)}" ry="${f(R * 0.2)}" fill="#2f8a3a" transform="rotate(${f(r() * 180)} ${f(x + R)} ${f(y + R * 0.4)})"/>`;
    }
    return wrap(W, H, [], body, { vignette: 0.2 });
  },

  carnival({ W, H, r }) {
    const bg = radial([[0, '#ffc531'], [0.6, '#ff3f8e'], [1, '#7b3fe4']], 0.5, 0.55, 0.8);
    let body = `<rect width="${W}" height="${H}" fill="url(#${bg.id})"/>`;
    const cx = W / 2, cy = H * 1.05;
    const cols = ['#e5174f', '#ffc531', '#12b5b0', '#1d6fe0', '#2bb673', '#ff7a1a', '#fff'];
    for (let i = 0; i < 26; i++) {
      const a = Math.PI + (i / 25) * Math.PI;
      const L = H * (0.75 + r() * 0.2);
      body += `<path d="M${cx} ${cy} L${f(cx + Math.cos(a - 0.06) * L)} ${f(cy + Math.sin(a - 0.06) * L)} Q${f(cx + Math.cos(a) * L * 1.08)} ${f(cy + Math.sin(a) * L * 1.08)} ${f(cx + Math.cos(a + 0.06) * L)} ${f(cy + Math.sin(a + 0.06) * L)} Z" fill="${cols[i % cols.length]}"/>`;
    }
    body += `<circle cx="${cx}" cy="${cy}" r="${H * 0.3}" fill="#1a1530"/><circle cx="${cx}" cy="${cy}" r="${H * 0.24}" fill="#ffc531"/>`;
    for (let i = 0; i < 160; i++) body += `<rect x="${f(r() * W)}" y="${f(r() * H)}" width="${f(8 + r() * 10)}" height="${f(4 + r() * 6)}" fill="${cols[i % cols.length]}" transform="rotate(${f(r() * 180)} ${f(r() * W)} ${f(r() * H)})"/>`;
    return wrap(W, H, [bg.def], body, { vignette: 0.25 });
  },

  music({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#1d6fe0"/>`;
    for (let i = 0; i < 12; i++) body += `<circle cx="${W / 2}" cy="${H / 2}" r="${f(80 + i * 70)}" fill="none" stroke="#fff" stroke-opacity="${f(0.12 - i * 0.008)}" stroke-width="18"/>`;
    // Tambor
    body += `<g transform="translate(${W * 0.32} ${H * 0.62})"><rect x="-130" y="-170" width="260" height="300" rx="40" fill="#c4452f"/><ellipse cx="0" cy="-170" rx="130" ry="36" fill="#f6ecd0"/>`;
    for (let k = 0; k < 7; k++) body += `<path d="M${-120 + k * 40} -150 l20 260" stroke="#ffc531" stroke-width="8"/>`;
    body += `</g>`;
    // Maracas
    body += `<g transform="translate(${W * 0.68} ${H * 0.42}) rotate(25)"><ellipse cx="0" cy="0" rx="80" ry="100" fill="#ffc531"/><rect x="-14" y="90" width="28" height="160" rx="12" fill="#7a3d1f"/><path d="M-70 -20 q70 -40 140 0" stroke="#e5174f" stroke-width="12" fill="none"/></g>`;
    body += `<g transform="translate(${W * 0.8} ${H * 0.62}) rotate(-20)"><ellipse cx="0" cy="0" rx="70" ry="90" fill="#12b5b0"/><rect x="-12" y="80" width="24" height="140" rx="12" fill="#7a3d1f"/><path d="M-60 10 q60 -40 120 0" stroke="#fff" stroke-width="10" fill="none"/></g>`;
    // Gaita
    body += `<g transform="translate(${W * 0.55} ${H * 0.2}) rotate(70)"><rect x="-14" y="-260" width="28" height="520" rx="12" fill="#e9c58a"/><circle cx="0" cy="-260" r="34" fill="#1a1410"/>${[0, 1, 2, 3, 4].map((k) => `<circle cx="0" cy="${40 + k * 40}" r="7" fill="#7a3d1f"/>`).join('')}</g>`;
    for (let i = 0; i < 14; i++) { const x = r() * W, y = r() * H; body += `<g fill="#fff" opacity="0.8"><circle cx="${f(x)}" cy="${f(y)}" r="14"/><rect x="${f(x + 10)}" y="${f(y - 60)}" width="6" height="60"/></g>`; }
    return wrap(W, H, [], body, { vignette: 0.25 });
  },

  pollera({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#fff6e6"/>`;
    const cols = ['#e5174f', '#ffc531', '#1d6fe0', '#ffffff', '#ff7a1a', '#2bb673'];
    const cx = W / 2, cy = H * 0.5;
    for (let ring = 10; ring > 0; ring--) {
      const R = ring * Math.max(W, H) * 0.06;
      let d = '';
      const n = 40;
      for (let k = 0; k <= n; k++) {
        const a = (k / n) * Math.PI * 2;
        const rr = R + Math.sin(a * 8 + ring) * R * 0.08;
        d += (k ? 'L' : 'M') + f(cx + Math.cos(a) * rr) + ' ' + f(cy + Math.sin(a) * rr * 0.8);
      }
      body += `<path d="${d}Z" fill="${cols[ring % cols.length]}"/><path d="${d}Z" fill="none" stroke="#1a1530" stroke-opacity="0.15" stroke-width="4"/>`;
    }
    body += `<circle cx="${cx}" cy="${cy}" r="${H * 0.06}" fill="#1a1530"/>`;
    return wrap(W, H, [], body, { vignette: 0.25 });
  },

  pottery({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#f6dcc0"/>`;
    for (let s = 0; s < 3; s++) {
      const y = H * (0.36 + s * 0.3);
      body += `<rect y="${f(y)}" width="${W}" height="22" fill="#8a5a3a"/><rect y="${f(y + 22)}" width="${W}" height="10" fill="#6b4226"/>`;
      let x = 30;
      while (x < W - 60) {
        const w = 90 + r() * 120, h = 110 + r() * 120, c = ['#c4452f', '#d9773a', '#a8502a', '#e8a35a', '#7b3fe4', '#12b5b0'][Math.floor(r() * 6)];
        const cxv = x + w / 2;
        body += `<path d="M${f(cxv - w * 0.22)} ${f(y - h)} h${f(w * 0.44)} q${f(-w * 0.04)} ${f(h * 0.15)} ${f(w * 0.26)} ${f(h * 0.5)} q${f(w * 0.1)} ${f(h * 0.4)} ${f(-w * 0.2)} ${f(h * 0.5)} h${f(-w * 0.6)} q${f(-w * 0.3)} ${f(-h * 0.1)} ${f(-w * 0.2)} ${f(-h * 0.5)} q${f(w * 0.3)} ${f(-h * 0.35)} ${f(w * 0.26)} ${f(-h * 0.5)} Z" fill="${c}"/>`;
        body += `<path d="M${f(cxv - w * 0.42)} ${f(y - h * 0.45)} h${f(w * 0.84)}" stroke="#fff6e6" stroke-width="6" opacity="0.8"/><path d="M${f(cxv - w * 0.38)} ${f(y - h * 0.32)} h${f(w * 0.76)}" stroke="#1a1410" stroke-width="4" stroke-dasharray="10 10" opacity="0.5"/>`;
        x += w + 20;
      }
    }
    return wrap(W, H, [], body, { vignette: 0.2 });
  },

  textiles({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#2a1f3a"/><rect y="${H * 0.08}" width="${W}" height="10" fill="#c9a77a"/>`;
    const cols = ['#e5174f', '#ffc531', '#12b5b0', '#7b3fe4', '#ff7a1a', '#2bb673', '#ff3f8e', '#1d6fe0'];
    let x = 20;
    while (x < W) {
      const w = 120 + r() * 120, h = H * (0.55 + r() * 0.35);
      const c1 = cols[Math.floor(r() * cols.length)], c2 = cols[Math.floor(r() * cols.length)];
      body += `<rect x="${f(x)}" y="${H * 0.09}" width="${f(w)}" height="${f(h)}" fill="${c1}"/>`;
      for (let yy = H * 0.12; yy < H * 0.09 + h - 20; yy += 34) {
        body += `<rect x="${f(x)}" y="${f(yy)}" width="${f(w)}" height="10" fill="${c2}"/>`;
        for (let xx = x + 10; xx < x + w - 10; xx += 28) body += `<path d="M${f(xx)} ${f(yy + 22)} l10 -8 l10 8 l-10 8 Z" fill="#fff6e6" opacity="0.85"/>`;
      }
      for (let k = 0; k < w / 10; k++) body += `<rect x="${f(x + k * 10)}" y="${f(H * 0.09 + h)}" width="4" height="${f(20 + r() * 20)}" fill="${c1}"/>`;
      x += w + 18;
    }
    return wrap(W, H, [], body, { vignette: 0.3, grain: 0.12 });
  },

  market({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#fff3d6"/>`;
    for (let i = 0; i < 12; i++) body += `<path d="M${f((W / 12) * i)} 0 h${f(W / 12)} v${H * 0.2} q${f(-W / 24)} 40 ${f(-W / 12)} 0 Z" fill="${i % 2 ? '#ff5a5f' : '#fff'}"/>`;
    const crates = ['#ffc531', '#e5174f', '#ff7a1a', '#7ac943', '#7b3fe4', '#ffcf3a', '#2bb673', '#ff3f8e'];
    for (let row = 0; row < 2; row++) {
      for (let i = 0; i < 4; i++) {
        const x = W * (0.04 + i * 0.24), y = H * (0.42 + row * 0.28), w = W * 0.22, h = H * 0.24;
        body += `<rect x="${f(x)}" y="${f(y)}" width="${f(w)}" height="${f(h)}" fill="#b8864a"/><rect x="${f(x)}" y="${f(y + h * 0.4)}" width="${f(w)}" height="8" fill="#8a5a3a"/>`;
        const c = crates[(row * 4 + i) % crates.length];
        for (let k = 0; k < 22; k++) body += `<circle cx="${f(x + 20 + r() * (w - 40))}" cy="${f(y + 10 + r() * h * 0.35)}" r="${f(18 + r() * 10)}" fill="${c}"/><circle cx="${f(x + 20 + r() * (w - 40))}" cy="${f(y + 10 + r() * h * 0.3)}" r="5" fill="#fff" opacity="0.4"/>`;
      }
    }
    return wrap(W, H, [], body, { vignette: 0.2 });
  },

  honey({ W, H, r }) {
    let body = `<rect width="${W}" height="${H}" fill="#ffb21a"/>`;
    const s = 60;
    for (let y = 0; y < H + s; y += s * 0.87) for (let x = 0; x < W + s; x += s * 1.5) {
      const ox = (Math.round(y / (s * 0.87)) % 2) * s * 0.75;
      const cx = x + ox, cy = y;
      let d = '';
      for (let k = 0; k < 6; k++) { const a = (k / 6) * Math.PI * 2; d += (k ? 'L' : 'M') + f(cx + Math.cos(a) * s * 0.48) + ' ' + f(cy + Math.sin(a) * s * 0.48); }
      body += `<path d="${d}Z" fill="${r() > 0.3 ? '#f29b0a' : '#ffd25a'}" stroke="#e08a00" stroke-width="4"/>`;
    }
    for (let i = 0; i < 3; i++) {
      const x = W * (0.25 + i * 0.25), y = H * 0.78;
      body += `<rect x="${x - 90}" y="${y - 260}" width="180" height="260" rx="30" fill="#ffcf5a" stroke="#fff" stroke-width="8" opacity="0.95"/><rect x="${x - 100}" y="${y - 290}" width="200" height="44" rx="12" fill="${['#e5174f', '#2bb673', '#7b3fe4'][i]}"/><rect x="${x - 60}" y="${y - 170}" width="120" height="70" rx="10" fill="#fff6e6"/>`;
    }
    return wrap(W, H, [], body, { vignette: 0.25 });
  },

  stones({ W, H, r }) {
    const s = sky(W, H, '#9fc7d9', '#dcefe9', '#eef5e6');
    let body = s.svg + `<path d="${ridge(W, H, H * 0.5, H * 0.12, r, { sharp: 0.6 })}" fill="#6fae84"/><rect y="${H * 0.68}" width="${W}" height="${H * 0.32}" fill="#4f8a5a"/>`;
    for (let i = 0; i < 3; i++) {
      const x = W * (0.25 + i * 0.25), y = H * 0.9, w = 150 + i * 20, h = 360 - i * 40;
      body += `<g><rect x="${x - w / 2}" y="${y - h}" width="${w}" height="${h}" rx="30" fill="#8f8a7a"/><rect x="${x - w / 2 + 14}" y="${y - h + 30}" width="${w - 28}" height="${h * 0.35}" rx="20" fill="#7a7566"/><circle cx="${x - w * 0.18}" cy="${y - h + 90}" r="16" fill="#4a4638"/><circle cx="${x + w * 0.18}" cy="${y - h + 90}" r="16" fill="#4a4638"/><rect x="${x - w * 0.28}" y="${y - h + 140}" width="${w * 0.56}" height="22" rx="8" fill="#4a4638"/>${[0, 1, 2, 3].map((k) => `<rect x="${x - w * 0.24 + k * w * 0.13}" y="${y - h + 142}" width="10" height="26" fill="#d6d0bd"/>`).join('')}</g>`;
    }
    body += canopy(W, H * 0.9, 50, 20, ['#3f7a4a', '#2f6b3a'], r, 2);
    return wrap(W, H, [s.def], body);
  },
};
