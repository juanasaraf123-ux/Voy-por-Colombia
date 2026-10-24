<?php use App\Sections; ?>
<div class="quick">
  <a class="btn btn-primary" href="<?= e(url('/admin/contenido/turismo/nuevo')) ?>"><?= icon('plus') ?> Nuevo destino</a>
  <a class="btn btn-outline" href="<?= e(url('/admin/contenido/noticias/nuevo')) ?>"><?= icon('news') ?> Nueva noticia</a>
  <a class="btn btn-outline" href="<?= e(url('/admin/multimedia')) ?>"><?= icon('upload') ?> Subir fotos o videos</a>
  <a class="btn btn-outline" href="<?= e(url('/admin/rutas/nueva')) ?>"><?= icon('route') ?> Nueva ruta</a>
  <a class="btn btn-outline" href="<?= e(url('/admin/inicio')) ?>"><?= icon('star') ?> Editar portada</a>
</div>
<div class="stats">
  <?php foreach ($stats as [$label, $n, $ic, $color, $href]): ?>
    <a class="stat" href="<?= e($href) ?>" style="--c: <?= e($color) ?>"><span class="ico"><?= icon($ic) ?></span><span><b><?= number_format((int) $n, 0, ',', '.') ?></b><small><?= e($label) ?></small></span></a>
  <?php endforeach; ?>
</div>
<?php if ($demo['entries'] || $demo['media']): ?>
  <div class="alert alert-info"><?= icon('info') ?><span>El sitio tiene <?= (int) $demo['entries'] ?> publicaciones y <?= (int) $demo['media'] ?> archivos de <strong>demostración</strong>. Cuando cargues contenido real puedes eliminarlos desde <?= App\Core\Auth::isAdmin() ? '<a href="' . e(url('/admin/configuracion')) . '">Configuración</a>' : 'Configuración (administradores)' ?>.</span></div>
<?php endif; ?>
<?php if ($noCoords): ?>
  <div class="alert alert-info"><?= icon('pin') ?><span><?= (int) $noCoords ?> destinos publicados no tienen coordenadas y no aparecen en el mapa. <a href="<?= e(url('/admin/mapa')) ?>">Revisar en Gestión del mapa</a>.</span></div>
<?php endif; ?>
<div class="grid grid-2">
  <section class="card">
    <div class="card-head"><h2><?= icon('layers') ?> Contenido por sección</h2><span class="hint"><?= (int) $published ?> publicadas · <?= (int) $drafts ?> borradores · <?= (int) $scheduled ?> programadas</span></div>
    <div class="section-bars">
      <?php $max = max(1, ...array_values(array_map(static fn ($s) => array_sum($s), $bySection ?: [[1]]))); ?>
      <?php foreach ($sections as $k => $s): $c = $bySection[$k] ?? []; $t = array_sum($c); ?>
        <a class="section-bar" href="<?= e(url('/admin/contenido/' . $s['path'])) ?>" style="text-decoration:none">
          <span><?= e($s['label']) ?></span>
          <span class="bar" title="Publicadas: <?= (int) ($c['published'] ?? 0) ?> · Borradores: <?= (int) ($c['draft'] ?? 0) ?> · Archivadas: <?= (int) ($c['archived'] ?? 0) ?>">
            <i style="width: <?= round(100 * ($c['published'] ?? 0) / $max) ?>%; background: <?= e($s['color']) ?>"></i>
            <i style="width: <?= round(100 * ($c['draft'] ?? 0) / $max) ?>%; background: #f2c94c"></i>
            <i style="width: <?= round(100 * ($c['archived'] ?? 0) / $max) ?>%; background: #c9c3b6"></i>
          </span>
          <span><?= $t ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="card">
    <div class="card-head"><h2><?= icon('clock') ?> Actividad reciente</h2><?php if (App\Core\Auth::isAdmin()): ?><a class="hint" href="<?= e(url('/admin/auditoria')) ?>">Ver auditoría</a><?php endif; ?></div>
    <ul class="activity">
      <?php foreach ($activity as $a): ?>
        <li><span class="a-ico"><?= icon(['create' => 'plus', 'update' => 'edit', 'delete' => 'trash', 'publish' => 'check', 'upload' => 'upload', 'login' => 'user', 'logout' => 'logout'][$a['action']] ?? 'info') ?></span>
          <span><strong><?= e($a['user_name'] ?? 'Sistema') ?></strong> · <?= e(App\Core\Audit::ACTIONS[$a['action']] ?? $a['action']) ?>: <?= e($a['summary']) ?><small><?= e(fecha_hora($a['created_at'])) ?></small></span></li>
      <?php endforeach; ?>
      <?php if (!$activity): ?><li class="muted">Sin actividad todavía.</li><?php endif; ?>
    </ul>
  </section>
</div>
<section class="card" style="margin-top:18px">
  <div class="card-head"><h2><?= icon('edit') ?> Editado recientemente</h2></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th></th><th>Título</th><th>Sección</th><th>Estado</th><th>Actualizado</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($recent as $e): ?>
        <tr>
          <td><img class="t-thumb" src="<?= e(media_url($e['cover'], 'thumb')) ?>" alt=""></td>
          <td><a class="t-title" href="<?= e(url('/admin/publicaciones/' . $e['id'] . '/editar')) ?>"><?= e($e['title']) ?></a></td>
          <td><?= e(Sections::ALL[$e['section']]['label']) ?></td>
          <td><span class="pill pill-<?= e($e['effective_status']) ?>"><?= e(status_label($e['effective_status'])) ?></span></td>
          <td class="muted"><?= e(fecha_hora($e['updated_at'])) ?></td>
          <td class="actions"><a class="btn btn-outline btn-sm" href="<?= e(url('/admin/publicaciones/' . $e['id'] . '/editar')) ?>"><?= icon('edit') ?> Editar</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
