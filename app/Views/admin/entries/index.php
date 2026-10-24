<?php
use App\Core\Auth;
$q = $_GET;
$tab = static function (?string $status) use ($q) { $q['estado'] = $status; unset($q['pagina']); return '?' . http_build_query(array_filter($q)); };
?>
<div class="flex between" style="margin-bottom:14px">
  <p class="muted" style="margin:0"><?= (int) $total ?> publicaciones en <?= e($def['label']) ?></p>
  <a class="btn btn-primary" href="<?= e(url('/admin/contenido/' . $def['path'] . '/nuevo')) ?>"><?= icon('plus') ?> Nuevo <?= e($def['singular']) ?></a>
</div>
<nav class="tabs" aria-label="Filtrar por estado">
  <a href="<?= e($tab(null)) ?>" aria-current="<?= !$filters['status'] ? 'true' : 'false' ?>">Todas (<?= array_sum($counts) ?>)</a>
  <a href="<?= e($tab('published')) ?>" aria-current="<?= $filters['status'] === 'published' ? 'true' : 'false' ?>">Publicadas (<?= $counts['published'] ?? 0 ?>)</a>
  <a href="<?= e($tab('scheduled')) ?>" aria-current="<?= $filters['status'] === 'scheduled' ? 'true' : 'false' ?>">Programadas</a>
  <a href="<?= e($tab('draft')) ?>" aria-current="<?= $filters['status'] === 'draft' ? 'true' : 'false' ?>">Borradores (<?= $counts['draft'] ?? 0 ?>)</a>
  <a href="<?= e($tab('archived')) ?>" aria-current="<?= $filters['status'] === 'archived' ? 'true' : 'false' ?>">Archivadas (<?= $counts['archived'] ?? 0 ?>)</a>
</nav>
<form class="filters" method="get">
  <?php if ($filters['status']): ?><input type="hidden" name="estado" value="<?= e($filters['status']) ?>"><?php endif; ?>
  <input class="input" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Buscar por título, lugar o texto…" aria-label="Buscar">
  <select class="select" name="categoria" aria-label="Categoría"><option value="">Todas las categorías</option><?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $filters['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <select class="select" name="departamento" aria-label="Departamento"><option value="">Todos los departamentos</option><?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (int) $filters['department_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select>
  <button class="btn" type="submit"><?= icon('filter') ?> Filtrar</button>
</form>
<div class="card">
  <?php if ($items): ?>
  <div class="table-wrap"><table class="table">
    <thead><tr><th></th><th>Título</th><th>Ubicación</th><th>Estado</th><th>Actualizado</th><th class="sr-only">Acciones</th></tr></thead>
    <tbody>
      <?php foreach ($items as $e): ?>
        <tr>
          <td><img class="t-thumb" src="<?= e(media_url($e['cover'], 'thumb')) ?>" alt=""></td>
          <td>
            <a class="t-title" href="<?= e(url('/admin/publicaciones/' . $e['id'] . '/editar')) ?>"><?= e($e['title']) ?></a>
            <div class="flex" style="margin-top:4px">
              <?php if ($e['is_featured']): ?><span class="pill pill-scheduled"><?= icon('star') ?> Destacado</span><?php endif; ?>
              <?php if ($e['is_hidden_gem']): ?><span class="pill" style="background:#efe5ff;color:#5b26b8">Joya</span><?php endif; ?>
              <?php if ($e['is_demo']): ?><span class="pill pill-demo">Demo</span><?php endif; ?>
              <?php if ($e['lat'] === null && $def['map']): ?><span class="pill pill-off">Sin coordenadas</span><?php endif; ?>
            </div>
          </td>
          <td class="muted"><?= e(trim(($e['municipality_name'] ?? '') . ', ' . ($e['department_name'] ?? ''), ', ')) ?: '—' ?></td>
          <td><span class="pill pill-<?= e($e['effective_status']) ?>"><?= e(status_label($e['effective_status'])) ?></span><?php if ($e['effective_status'] === 'scheduled'): ?><br><small class="muted"><?= e(fecha_hora($e['published_at'])) ?></small><?php endif; ?></td>
          <td class="muted"><?= e(fecha_hora($e['updated_at'])) ?></td>
          <td>
            <div class="actions">
              <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/publicaciones/' . $e['id'] . '/editar')) ?>"><?= icon('edit') ?> Editar</a>
              <a class="btn btn-outline btn-sm btn-icon" href="<?= e(url('/admin/publicaciones/' . $e['id'] . '/vista-previa')) ?>" target="_blank" title="Vista previa" aria-label="Vista previa"><?= icon('eye') ?></a>
              <?php if (Auth::can('content.publish')): ?>
                <form method="post" action="<?= e(url('/admin/publicaciones/' . $e['id'] . '/estado')) ?>"><?= csrf_field() ?>
                  <?php if ($e['status'] !== 'published'): ?><button class="btn btn-success btn-sm" name="status" value="published"><?= icon('check') ?> Publicar</button>
                  <?php else: ?><button class="btn btn-outline btn-sm" name="status" value="draft">Despublicar</button><?php endif; ?>
                  <?php if ($e['status'] !== 'archived'): ?><button class="btn btn-outline btn-sm" name="status" value="archived">Archivar</button><?php endif; ?>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?>
    <div class="empty-state"><?= icon($def['icon']) ?><p>No hay publicaciones con estos filtros.</p></div>
  <?php endif; ?>
</div>
<?php if ($pages > 1): ?>
  <nav class="pagination"><?php for ($p = 1; $p <= $pages; $p++): $q['pagina'] = $p; ?><?= $p === $page ? '<span class="is-current">' . $p . '</span>' : '<a href="?' . e(http_build_query($q)) . '">' . $p . '</a>' ?><?php endfor; ?></nav>
<?php endif; ?>
