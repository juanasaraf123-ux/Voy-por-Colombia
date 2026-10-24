// Renderiza las ilustraciones de demostración a PNG con Chromium (Playwright).
// Uso: node tools/demo-art/render.mjs <carpeta_salida_png>
// Después: php tools/demo-art/to-webp.php <carpeta_png> database/seeds/demo-media
import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { scenes, rng } from './scenes.mjs';

export const MANIFEST = [
  // nombre, escena, ancho, alto, semilla, parámetros
  ['andes-amanecer', 'andes', 1800, 1100, 11, {}],
  ['andes-violeta', 'andes', 1800, 1100, 23, { palette: { sky: ['#6a4c93', '#ff8fab', '#ffd6a5'], layers: ['#d8b4e2', '#b48ac9', '#8a63b3', '#5b3f8c', '#2e2357'] } }],
  ['nevado', 'andes', 1600, 1100, 5, { palette: { sky: ['#4aa3df', '#a9daf2', '#eef8fb'], layers: ['#f4f8fb', '#b9cfe0', '#7f9fbf', '#4d6f8f', '#27455f'] } }],
  ['paramo', 'paramo', 1600, 1100, 8, {}],
  ['caribe', 'caribe', 1800, 1100, 3, {}],
  ['caribe-2', 'caribe', 1400, 1400, 41, { boats: false }],
  ['san-andres', 'sanandres', 1600, 1100, 4, {}],
  ['cocora', 'cocora', 1800, 1100, 7, {}],
  ['cocora-vertical', 'cocora', 1100, 1500, 17, {}],
  ['paisaje-cafetero', 'coffee', 1800, 1100, 9, {}],
  ['paisaje-cafetero-2', 'coffee', 1400, 1400, 29, {}],
  ['amazonas', 'amazon', 1800, 1100, 12, {}],
  ['amazonas-vertical', 'amazon', 1100, 1500, 33, {}],
  ['guajira', 'guajira', 1800, 1100, 13, {}],
  ['cano-cristales', 'canocristales', 1600, 1100, 14, {}],
  ['cano-cristales-vertical', 'canocristales', 1100, 1500, 44, {}],
  ['cartagena', 'colonial', 1800, 1100, 15, {}],
  ['villa-de-leyva', 'colonial', 1600, 1100, 16, { palette: ['#fbf7ef', '#f6f0e2', '#fffaf2', '#f3ead8'] }],
  ['guatape', 'colonial', 1400, 1400, 26, { palette: ['#ffc531', '#12b5b0', '#ff3f8e', '#7b3fe4', '#2bb673', '#ff7a1a'] }],
  ['barichara', 'colonial', 1100, 1500, 36, { palette: ['#f7e7cf', '#f2dcc0', '#fff4e3'] }],
  ['llanos', 'llanos', 1800, 1100, 18, {}],
  ['pacifico', 'pacific', 1800, 1100, 19, {}],
  ['tatacoa', 'tatacoa', 1600, 1100, 20, {}],
  ['tatacoa-noche', 'tatacoa', 1600, 1100, 21, { night: true }],
  ['medellin', 'city', 1800, 1100, 22, {}],
  ['cascada', 'waterfall', 1100, 1500, 24, {}],
  ['san-agustin', 'stones', 1600, 1100, 25, {}],
  // Gastronomía
  ['bandeja-paisa', 'plate', 1400, 1400, 30, { dish: 'bandeja', cloth: '#ff5a5f' }],
  ['ajiaco', 'plate', 1400, 1400, 31, { dish: 'ajiaco', cloth: '#12b5b0' }],
  ['arepas', 'plate', 1400, 1100, 32, { dish: 'arepas', cloth: '#ffc531' }],
  ['sancocho', 'plate', 1400, 1400, 34, { dish: 'sancocho', cloth: '#7b3fe4' }],
  ['empanadas', 'plate', 1400, 1100, 35, { dish: 'empanadas', cloth: '#1d6fe0' }],
  ['frutas', 'plate', 1600, 1100, 37, { dish: 'fruits', cloth: '#fff3e0' }],
  ['cazuela', 'plate', 1400, 1400, 38, { dish: 'cazuela', cloth: '#ffc531' }],
  ['tamal', 'plate', 1400, 1100, 39, { dish: 'tamal', cloth: '#ff7a1a' }],
  ['jugos', 'juice', 1100, 1400, 40, {}],
  // Café
  ['taza-cafe', 'cup', 1400, 1400, 50, {}],
  ['cerezas-cafe', 'cherries', 1600, 1100, 51, {}],
  ['granos-cafe', 'beans', 1600, 1100, 52, {}],
  ['metodo-filtrado', 'pourover', 1100, 1400, 53, {}],
  // Cultura
  ['mochila-wayuu', 'wayuu', 1400, 1400, 60, {}],
  ['mola', 'mola', 1600, 1100, 61, {}],
  ['sombrero-vueltiao', 'vueltiao', 1400, 1400, 62, {}],
  ['silleta-flores', 'flowers', 1600, 1100, 63, {}],
  ['carnaval', 'carnival', 1600, 1100, 64, {}],
  ['musica', 'music', 1600, 1100, 65, {}],
  ['pollera', 'pollera', 1400, 1400, 66, {}],
  // Emprendimiento
  ['ceramica', 'pottery', 1600, 1100, 70, {}],
  ['tejidos', 'textiles', 1600, 1100, 71, {}],
  ['mercado', 'market', 1600, 1100, 72, {}],
  ['miel', 'honey', 1400, 1400, 73, {}],
];

const out = process.argv[2];
if (!out) {
  console.error('Uso: node render.mjs <carpeta_salida>');
  process.exit(1);
}
mkdirSync(out, { recursive: true });
const only = process.argv[3] ? process.argv[3].split(',') : null;
const browser = await chromium.launch();
const page = await browser.newPage();
for (const [name, scene, W, H, seed, params] of MANIFEST) {
  if (only && !only.includes(name)) continue;
  const svg = scenes[scene]({ W, H, r: rng(seed), ...params });
  writeFileSync(join(out, name + '.svg'), svg);
  await page.setViewportSize({ width: W, height: H });
  await page.setContent(`<html><body style="margin:0">${svg}</body></html>`);
  await page.screenshot({ path: join(out, name + '.png'), clip: { x: 0, y: 0, width: W, height: H } });
  console.log('✓', name);
}
await browser.close();
