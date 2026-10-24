<?php use App\Core\Audit; $q = $_GET; ?>
<form class="filters" method="get">
  <input class="input" type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Buscar en el detalle" aria-label="Buscar">
  <select class="select" name="usuario" aria-label="Usuario"><option value="">Todos los usuarios</option><?php foreach ($users as $u): ?><option value="<?= (int) $u['id'] ?>" <?= (int) ($_GET['usuario'] ?? 0) === (int) $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option><?php endforeach; ?></select>
  <select class="select" name="accion" aria-label="Acción"><option value="">Todas las acciones</option><?php foreach (Audit::ACTIONS as $k => $l): ?><option value="<?= e($k) ?>" <?= ($_GET['accion'] ?? '') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
  <button class="btn" type="submit"><?= icon('filter') ?> Filtrar</button>
</form>
<div class="card"><p class="hint"><?= (int) $total ?> registros</p><div class="table-wrap"><table class="table">
  <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
  <tbody><?php foreach ($rows as $r): ?>
    <tr><td class="muted" style="white-space:nowrap"><?= e(fecha_hora($r['created_at'])) ?></td><td><?= e($r['user_name'] ?? 'Sistema') ?></td><td><span class="pill pill-archived"><?= e(Audit::ACTIONS[$r['action']] ?? $r['action']) ?></span></td><td><?= e($r['summary']) ?></td><td class="muted"><?= e($r['ip']) ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div></div>
<?php if ($pages > 1): ?><nav class="pagination"><?php for ($p = max(1, $page - 4); $p <= min($pages, $page + 4); $p++): $q['pagina'] = $p; ?><?= $p === $page ? '<span class="is-current">' . $p . '</span>' : '<a href="?' . e(http_build_query($q)) . '">' . $p . '</a>' ?><?php endfor; ?></nav><?php endif; ?>
