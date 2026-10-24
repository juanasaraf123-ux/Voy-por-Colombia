<div class="flex between" style="margin-bottom:14px"><p class="muted" style="margin:0">Rutas sugeridas con varias paradas. Los visitantes también pueden armar la suya desde el mapa.</p>
  <a class="btn btn-primary" href="<?= e(url('/admin/rutas/nueva')) ?>"><?= icon('plus') ?> Nueva ruta</a></div>
<div class="card">
  <?php if ($routes): ?>
  <div class="table-wrap"><table class="table">
    <thead><tr><th></th><th>Ruta</th><th>Paradas</th><th>Estado</th><th></th></tr></thead>
    <tbody><?php foreach ($routes as $r): ?>
      <tr><td><img class="t-thumb" src="<?= e(media_url($r['cover'], 'thumb')) ?>" alt=""></td>
        <td><a class="t-title" href="<?= e(url('/admin/rutas/' . $r['id'])) ?>"><?= e($r['title']) ?></a> <?= $r['is_featured'] ? '<span class="pill pill-scheduled">Destacada</span>' : '' ?> <?= $r['is_demo'] ? '<span class="pill pill-demo">Demo</span>' : '' ?><br><small class="muted"><?= e($r['duration'] ?? '') ?></small></td>
        <td><?= (int) $r['stops_count'] ?></td>
        <td><span class="pill pill-<?= e($r['status']) ?>"><?= e(status_label($r['status'])) ?></span></td>
        <td class="actions"><a class="btn btn-outline btn-sm" href="<?= e(url('/admin/rutas/' . $r['id'])) ?>"><?= icon('edit') ?> Editar</a><?php if ($r['status'] === 'published'): ?><a class="btn btn-outline btn-sm btn-icon" href="<?= e($r['url']) ?>" target="_blank" aria-label="Ver"><?= icon('globe') ?></a><?php endif; ?></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
  <?php else: ?><div class="empty-state"><?= icon('route') ?><p>Aún no hay rutas.</p></div><?php endif; ?>
</div>
