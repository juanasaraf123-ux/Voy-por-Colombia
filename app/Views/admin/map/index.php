<?php use App\Sections; ?>
<div class="grid-form">
  <section class="card">
    <div class="card-head"><h2><?= icon('map') ?> Marcadores en el mapa</h2><span class="hint">Arrastra un marcador para corregir su ubicación. Haz clic para editar la publicación.</span></div>
    <div class="map-admin" data-admin-map data-markers="<?= e(json_encode($markers, JSON_UNESCAPED_UNICODE)) ?>" data-update-url="<?= e(url('/admin/mapa/publicaciones/')) ?>"></div>
    <p class="hint mt"><span class="pin" style="display:inline-block;vertical-align:middle;width:14px;height:14px"></span> Publicado · <span class="pin draft" style="display:inline-block;vertical-align:middle;width:14px;height:14px"></span> Borrador · <span class="pin archived" style="display:inline-block;vertical-align:middle;width:14px;height:14px"></span> Archivado</p>
  </section>
  <aside>
    <section class="card">
      <h2><?= icon('alert') ?> Sin coordenadas (<?= count($missing) ?>)</h2>
      <?php if ($missing): ?>
        <ul style="margin:0;padding-left:18px;max-height:300px;overflow:auto"><?php foreach ($missing as $m): ?><li><a href="<?= e(url('/admin/publicaciones/' . $m['id'] . '/editar')) ?>"><?= e($m['title']) ?></a> <small class="muted">· <?= e(Sections::ALL[$m['section']]['label']) ?></small></li><?php endforeach; ?></ul>
      <?php else: ?><p class="hint">Todas las publicaciones con ubicación tienen coordenadas.</p><?php endif; ?>
      <p class="hint">Las noticias no se muestran en el mapa.</p>
    </section>
  </aside>
</div>
<section class="card" style="margin-top:18px">
  <div class="card-head"><h2><?= icon('layers') ?> Departamentos</h2><span class="hint">Los límites provienen de geoBoundaries (OpenStreetMap). Aquí editas la región, capital, descripción e imagen de cada uno.</span></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Departamento</th><th>Región</th><th>Capital</th><th>Publicaciones</th><th>Imagen</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($departments as $d): ?>
        <tr><td><strong><?= e($d['name']) ?></strong></td><td><span class="pill" style="background:<?= e($d['region_color'] ?? '#eee') ?>;color:#fff"><?= e($d['region_name'] ?? '—') ?></span></td><td><?= e($d['capital'] ?? '—') ?></td><td><?= (int) ($counts[$d['id']] ?? 0) ?></td><td><?= $d['cover_media_id'] ? icon('check') : '<span class="muted">—</span>' ?></td>
          <td class="actions"><a class="btn btn-outline btn-sm" href="<?= e(url('/admin/mapa/departamentos/' . $d['id'])) ?>"><?= icon('edit') ?> Editar</a></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
