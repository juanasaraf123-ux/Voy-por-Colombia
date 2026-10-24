#!/usr/bin/env python3
"""
Genera los archivos geográficos de la plataforma a partir de geoBoundaries (gbOpen, COL).

Fuente: https://www.geoboundaries.org  (datos de OpenStreetMap, licencia ODbL 1.0)
Pasos previos:
  1. Descargar ADM1 y ADM2 simplificados de geoBoundaries:
     https://media.githubusercontent.com/media/wmgeolab/geoBoundaries/main/releaseData/gbOpen/COL/ADM1/geoBoundaries-COL-ADM1_simplified.geojson
     (y lo mismo con ADM2).
  2. Unir cada municipio con su departamento y simplificar con mapshaper (npm i mapshaper):
     mapshaper -i adm2.geojson -join adm1.geojson point-method fields=shapeName,shapeISO prefix=d_ \
       -each 'cx=this.innerX, cy=this.innerY' -o adm2_joined.geojson
     mapshaper -i adm1.geojson -simplify 6% weighted keep-shapes -filter-fields shapeName,shapeISO -o precision=0.0001 dep_s.geojson
     mapshaper -i adm2_joined.geojson -simplify 12% weighted keep-shapes -filter-fields shapeName,d_shapeName,cx,cy -o precision=0.0001 mun_s.geojson
Uso:
  python3 build_geo.py dep_s.geojson mun_s.geojson <raiz_del_proyecto>
"""
import csv, json, os, re, sys, unicodedata

NAME_FIX = {
    "Bogota Capital District": "Bogotá D.C.",
    "Bogotá, D.c.": "Bogotá D.C.",
    "Archipiélago de San Andrés, Providencia y Santa Catalina": "San Andrés y Providencia",
    "Distrito Especial, Industrial Y Portuario De Barr*": "Barranquilla",
    "Cartagena De Indias": "Cartagena de Indias",
    "Monguì": "Monguí",
    "Zipaquira": "Zipaquirá",
    "Calarca": "Calarcá",
    "Libano": "Líbano",
    "Suaréz": "Suárez",
    "Falan": "Falán",
    "Inza": "Inzá",
    "Bahía Solano (Mutis)": "Bahía Solano",
    "Providencia (Santa Isabel)": "Providencia",
    "Tuchin": "Tuchín",
}
LOWER = {"De", "Del", "La", "Las", "Los", "Y", "El"}

def tidy(name):
    """Corrige mayúsculas de conectores ("Villa De Leyva" -> "Villa de Leyva")."""
    words = name.split(" ")
    return " ".join(w.lower() if i > 0 and w in LOWER else w for i, w in enumerate(words))

def slugify(s):
    s = unicodedata.normalize("NFKD", s).encode("ascii", "ignore").decode()
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")

def main(dep_path, mun_path, root):
    out_geo = os.path.join(root, "public", "data", "geo")
    os.makedirs(os.path.join(out_geo, "municipios"), exist_ok=True)

    deps = json.load(open(dep_path, encoding="utf-8"))
    for f in deps["features"]:
        raw = f["properties"]["shapeName"]
        name = NAME_FIX.get(raw, raw)
        f["properties"] = {"name": name, "slug": slugify(name), "iso": f["properties"].get("shapeISO", "")}
    deps.pop("crs", None)
    json.dump(deps, open(os.path.join(out_geo, "departamentos.geojson"), "w", encoding="utf-8"),
              ensure_ascii=False, separators=(",", ":"))

    muns = json.load(open(mun_path, encoding="utf-8"))
    by_dep, rows = {}, []
    for f in muns["features"]:
        p = f["properties"]
        dep_name = NAME_FIX.get(p["d_shapeName"], p["d_shapeName"])
        dslug = slugify(dep_name)
        name = NAME_FIX.get(p["shapeName"]) or tidy(p["shapeName"])
        mslug = slugify(name)
        f["properties"] = {"name": name, "slug": mslug, "dep": dslug}
        by_dep.setdefault(dslug, []).append(f)
        rows.append([dslug, name, mslug, round(p["cy"], 5), round(p["cx"], 5)])

    for dslug, feats in by_dep.items():
        json.dump({"type": "FeatureCollection", "features": feats},
                  open(os.path.join(out_geo, "municipios", dslug + ".geojson"), "w", encoding="utf-8"),
                  ensure_ascii=False, separators=(",", ":"))

    rows.sort()
    with open(os.path.join(root, "database", "data", "municipios.csv"), "w", newline="", encoding="utf-8") as fh:
        w = csv.writer(fh)
        w.writerow(["departamento_slug", "nombre", "slug", "lat", "lng"])
        w.writerows(rows)
    print(len(deps["features"]), "departamentos;", len(rows), "municipios")

if __name__ == "__main__":
    main(*sys.argv[1:4])
