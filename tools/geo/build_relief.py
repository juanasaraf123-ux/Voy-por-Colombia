#!/usr/bin/env python3
"""
Genera la textura de relieve ilustrada del mapa (public/assets/img/relief/colombia-relief.webp).

No es un modelo de elevación real: es una ilustración que aproxima las tres cordilleras,
la Sierra Nevada de Santa Marta y las serranías principales, y colorea las tierras bajas
según la región natural (selva, sabana, Caribe, desierto de La Guajira).
La imagen cubre los límites BOUNDS en proyección Web Mercator, los mismos que usa map.js.

Uso: python3 tools/geo/build_relief.py   (requiere numpy y Pillow)
"""
import json, math, os
import numpy as np
from PIL import Image, ImageDraw, ImageFilter

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
BOUNDS = {"west": -79.2, "east": -66.7, "south": -4.4, "north": 12.7}  # Debe coincidir con RELIEF_BOUNDS en map.js
WIDTH = 1500

def merc_y(lat):
    return math.log(math.tan(math.pi / 4 + math.radians(lat) / 2))

Y_N, Y_S = merc_y(BOUNDS["north"]), merc_y(BOUNDS["south"])
X_SPAN = math.radians(BOUNDS["east"] - BOUNDS["west"])
HEIGHT = int(round(WIDTH * (Y_N - Y_S) / X_SPAN))

def px(lon, lat):
    x = (math.radians(lon - BOUNDS["west"])) / X_SPAN * WIDTH
    y = (Y_N - merc_y(lat)) / (Y_N - Y_S) * HEIGHT
    return x, y

rng = np.random.default_rng(7)

def noise(octaves=6, base=6, persistence=0.55):
    """Ruido fractal suave (value noise) del tamaño de la imagen, entre 0 y 1."""
    total = np.zeros((HEIGHT, WIDTH), np.float32)
    amp, norm = 1.0, 0.0
    for o in range(octaves):
        cells = base * 2 ** o
        small = rng.random((max(2, int(cells * HEIGHT / WIDTH)), cells)).astype(np.float32)
        img = Image.fromarray((small * 255).astype(np.uint8)).resize((WIDTH, HEIGHT), Image.BICUBIC)
        total += np.asarray(img, np.float32) / 255 * amp
        norm += amp
        amp *= persistence
    return total / norm

# --- Cordilleras y serranías (lat, lon) con altura y ancho relativos ---
RIDGES = [
    # Cordillera Occidental
    ([(0.8, -77.9), (2.2, -77.0), (3.5, -76.6), (5.2, -76.1), (6.6, -76.1), (7.6, -76.2)], 0.75, 0.32),
    # Cordillera Central
    ([(0.9, -77.5), (2.0, -76.5), (3.4, -76.0), (4.6, -75.4), (5.8, -75.3), (7.0, -75.2), (8.2, -75.4)], 1.0, 0.36),
    # Cordillera Oriental
    ([(1.4, -76.5), (2.4, -75.8), (3.6, -74.9), (4.6, -74.0), (5.6, -73.3), (6.4, -72.6), (7.3, -72.5), (8.3, -72.9)], 0.9, 0.55),
    # Serranía del Perijá
    ([(8.3, -72.9), (9.4, -73.0), (10.6, -72.8)], 0.55, 0.22),
    # Serranía del Baudó
    ([(4.8, -77.3), (6.0, -77.4), (7.3, -77.6)], 0.25, 0.18),
    # Serranía de La Macarena
    ([(2.2, -73.9), (3.0, -73.8)], 0.35, 0.15),
    # Serranía de San Lucas
    ([(7.2, -74.3), (8.3, -74.2)], 0.35, 0.2),
]
PEAKS = [((10.85, -73.75), 1.25, 0.42), ((0.9, -77.7), 0.7, 0.35), ((6.45, -72.3), 0.6, 0.2), ((4.9, -75.35), 0.5, 0.18)]

yy, xx = np.mgrid[0:HEIGHT, 0:WIDTH].astype(np.float32)
elev = np.zeros((HEIGHT, WIDTH), np.float32)
deg_px = WIDTH / (BOUNDS["east"] - BOUNDS["west"])
warp = (noise(5, 6) - 0.5) * 0.9 * deg_px  # deforma las crestas para que no sean rectas

def seg_dist(px0, py0, px1, py1):
    vx, vy = px1 - px0, py1 - py0
    t = np.clip(((xx - px0) * vx + (yy - py0) * vy) / (vx * vx + vy * vy + 1e-6), 0, 1)
    return np.hypot(xx - (px0 + t * vx), yy - (py0 + t * vy))

for line, height, width in RIDGES:
    pts = [px(lon, lat) for lat, lon in line]
    d = np.full((HEIGHT, WIDTH), 1e9, np.float32)
    for (x0, y0), (x1, y1) in zip(pts, pts[1:]):
        d = np.minimum(d, seg_dist(x0, y0, x1, y1))
    d = np.maximum(d + warp, 0)
    elev = np.maximum(elev, height * np.exp(-(d / (width * 1.5 * deg_px)) ** 2))
for (lat, lon), height, width in PEAKS:
    x0, y0 = px(lon, lat)
    d = np.maximum(np.hypot(xx - x0, yy - y0) + warp * 0.5, 0)
    elev = np.maximum(elev, height * np.exp(-(d / (width * 1.3 * deg_px)) ** 2))

n1 = noise(7, 5)
n2 = noise(6, 14, 0.6)
fine = noise(3, 90, 0.5)
ridged = 1 - np.abs(2 * noise(6, 9, 0.55) - 1)  # crestas fractales
mount = elev ** 1.15
elev = np.clip(mount * (0.3 + 0.8 * ridged ** 1.5) + 0.04 * n1 + 0.015 * fine, 0, 1.3)

# --- Regiones (para el color de las tierras bajas) ---
geo = json.load(open(os.path.join(ROOT, "public/data/geo/departamentos.geojson"), encoding="utf-8"))
REGION = {
    "amazonas": 1, "caqueta": 1, "guainia": 1, "guaviare": 1, "putumayo": 1, "vaupes": 1,
    "choco": 1, "narino": 1, "cauca": 1, "valle-del-cauca": 1,
    "arauca": 2, "casanare": 2, "meta": 2, "vichada": 2,
    "atlantico": 3, "bolivar": 3, "cesar": 3, "cordoba": 3, "magdalena": 3, "sucre": 3, "la-guajira": 3, "san-andres-y-providencia": 3,
}
mask = Image.new("L", (WIDTH, HEIGHT), 0)  # 0 andina, 1 selva, 2 sabana, 3 caribe
draw = ImageDraw.Draw(mask)
for f in geo["features"]:
    val = REGION.get(f["properties"]["slug"], 0) * 60 + 20
    g = f["geometry"]
    polys = g["coordinates"] if g["type"] == "MultiPolygon" else [g["coordinates"]]
    for poly in polys:
        draw.polygon([px(lon, lat) for lon, lat in poly[0]], fill=val)
mask = np.asarray(mask.filter(ImageFilter.GaussianBlur(28)), np.float32)

def weight(center):
    return np.clip(1 - np.abs(mask - center) / 60, 0, 1)

w_andina, w_selva, w_sabana, w_caribe = weight(20), weight(80), weight(140), weight(200)
total = w_andina + w_selva + w_sabana + w_caribe + 1e-6

LOW = {  # color de tierras bajas por región
    "andina": np.array([88, 128, 60]), "selva": np.array([30, 92, 46]),
    "sabana": np.array([132, 150, 70]), "caribe": np.array([110, 140, 62]),
}
low = (w_andina[..., None] * LOW["andina"] + w_selva[..., None] * LOW["selva"]
       + w_sabana[..., None] * LOW["sabana"] + w_caribe[..., None] * LOW["caribe"]) / total[..., None]

# Desierto de La Guajira (norte de la península)
gx, gy = px(-72.0, 11.9)
desert = np.clip(1 - np.hypot(xx - gx, (yy - gy) * 1.3) / (1.0 * deg_px), 0, 1) ** 1.2
low = low * (1 - desert[..., None]) + np.array([196, 160, 104]) * desert[..., None]

# Variación de vegetación en las tierras bajas
low = low * (0.8 + 0.22 * n1[..., None] + 0.28 * fine[..., None])

# Rampa de altura: verde → oliva → ocre → marrón → páramo
def lerp(a, b, t):
    return a * (1 - t) + b * t

e = elev[..., None]
olive = np.array([86, 110, 54.0])
ochre = np.array([164, 122, 64.0])
brown = np.array([122, 82, 48.0])
paramo = np.array([190, 176, 146.0])
color = lerp(low, olive, np.clip(e / 0.3, 0, 1) ** 1.5)
color = np.where(e > 0.25, lerp(olive, ochre, np.clip((e - 0.25) / 0.3, 0, 1)), color)
color = np.where(e > 0.55, lerp(ochre, brown, np.clip((e - 0.55) / 0.3, 0, 1)), color)
color = np.where(e > 0.85, lerp(brown, paramo, np.clip((e - 0.85) / 0.35, 0, 1)), color)

# Sombreado (luz desde el noroeste)
gy_, gx_ = np.gradient(elev * 140.0)
shade = np.clip(1.0 + (-gx_ - gy_) / math.sqrt(2) * 1.6, 0.55, 1.35)
color = np.clip(color * shade[..., None], 0, 255)

# --- Estilo ilustrado: color propio por departamento (como un mapa pintado a mano) ---
DEPT_COLORS = {
    "la-guajira": (232, 160, 72), "magdalena": (214, 96, 62), "atlantico": (244, 190, 80), "cesar": (196, 132, 58),
    "bolivar": (236, 176, 70), "sucre": (164, 176, 74), "cordoba": (120, 160, 82), "norte-de-santander": (204, 84, 70),
    "santander": (228, 140, 60), "antioquia": (196, 78, 76), "choco": (58, 140, 118), "boyaca": (226, 168, 64),
    "cundinamarca": (182, 104, 70), "bogota-d-c": (222, 120, 84), "caldas": (114, 168, 140), "risaralda": (86, 150, 132),
    "quindio": (238, 182, 84), "tolima": (232, 128, 64), "huila": (214, 92, 70), "valle-del-cauca": (102, 168, 150),
    "cauca": (210, 140, 70), "narino": (186, 86, 82), "putumayo": (92, 150, 84), "caqueta": (70, 128, 74),
    "meta": (176, 178, 76), "casanare": (206, 186, 88), "arauca": (190, 170, 70), "vichada": (162, 176, 82),
    "guaviare": (96, 152, 92), "guainia": (78, 146, 120), "vaupes": (62, 132, 86), "amazonas": (52, 120, 78),
    "san-andres-y-providencia": (70, 180, 170),
}
tint = Image.new("RGB", (WIDTH, HEIGHT), (110, 150, 90))
tdraw = ImageDraw.Draw(tint)
for f in geo["features"]:
    g = f["geometry"]
    polys = g["coordinates"] if g["type"] == "MultiPolygon" else [g["coordinates"]]
    for poly in polys:
        tdraw.polygon([px(lon, lat) for lon, lat in poly[0]], fill=DEPT_COLORS.get(f["properties"]["slug"], (110, 150, 90)))
tint = np.asarray(tint.filter(ImageFilter.GaussianBlur(3)), np.float32)
luma = (color @ np.array([0.3, 0.59, 0.11]))[..., None] / 140.0
# Más color en las montañas, más verde natural en las selvas y llanuras bajas
mix = np.clip(0.42 + elev[..., None] * 0.45, 0.42, 0.85)
color = color * (1 - mix) + np.clip(tint * luma, 0, 255) * mix

# Líneas de contorno tipo grabado
contour = np.abs(((elev * 14.0) % 1.0) - 0.5) < 0.06
color = np.where((contour & (elev > 0.12))[..., None], color * 0.72, color)
# Trazos de vegetación en tierras bajas
hatch = (fine > 0.62) & (elev < 0.2)
color = np.where(hatch[..., None], color * 0.86, color)
color = np.clip(color * 1.06, 0, 255)

img = Image.fromarray(color.astype(np.uint8), "RGB").filter(ImageFilter.UnsharpMask(1.5, 40, 2))
out = os.path.join(ROOT, "public/assets/img/relief/colombia-relief.webp")
img.save(out, "WEBP", quality=78, method=6)
print(out, img.size, os.path.getsize(out) // 1024, "KB")
