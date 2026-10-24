<?php use App\Core\View;
$r = $route;
$action = $r ? url('/admin/rutas/' . $r['id']) : url('/admin/rutas/nueva');
$status = val('status', $r['status'] ?? 'draft');
?>
<p><a href="<?= e(url('/admin/rutas')) ?>">← Volver a rutas</a></p>
<form class="grid-form" method="post" action="<?= e($action) ?>" data-unsaved>
  <?= csrf_field() ?>
  <div>
    <section class="card">
      <div class="field"><label class="field-label" for="title">Nombre de la ruta</label><input class="input input-title<?= has_error('title') ? ' is-invalid' : '' ?>" id="title" name="title" maxlength="190" value="<?= e(val('title', $r['title'] ?? '')) ?>"><?= field_error('title') ?></div>
      <div class="field"><label class="field-label" for="summary">Resumen</label><textarea class="textarea" id="summary" name="summary" maxlength="400" rows="2" data-counter="400"><?= e(val('summary', $r['summary'] ?? '')) ?></textarea><?= field_error('summary') ?></div>
      <div class="field"><label class="field-label" for="body">Descripción y recomendaciones</label><textarea class="textarea" id="body" name="body" rows="6"><?= e(val('body', $r['body'] ?? '')) ?></textarea></div>
      <div class="field"><label class="field-label" for="duration">Duración sugerida</label><input class="input" id="duration" name="duration" maxlength="60" placeholder="Ej. 4 a 6 días" value="<?= e(val('duration', $r['duration'] ?? '')) ?>"></div>
    </section>
    <section class="card">
      <h2><?= icon('pin') ?> Paradas</h2>
      <div data-stops-list>
        <?php foreach ($stops as $i => $s): ?>
          <div class="stop-row" data-stop>
            <span class="n"><?= $i + 1 ?></span>
            <img src="<?= e(media_url($s['cover'], 'thumb')) ?>" alt="">
            <span><strong><?= e($s['title']) ?></strong><br><small class="muted"><?= e($s['department_name'] ?? '') ?></small></span>
            <input type="hidden" name="stops[]" value="<?= (int) $s['id'] ?>">
            <input class="input" name="stop_notes[]" maxlength="255" placeholder="Nota (opcional)" value="<?= e($s['stop_note'] ?? '') ?>">
            <span class="actions"><button class="btn btn-outline btn-sm btn-icon" type="button" data-stop-up aria-label="Subir">↑</button><button class="btn btn-outline btn-sm btn-icon" type="button" data-stop-down aria-label="Bajar">↓</button><button class="btn btn-outline btn-sm btn-icon" type="button" data-stop-remove aria-label="Quitar"><?= icon('close') ?></button></span>
          </div>
        <?php endforeach; ?>
      </div>
      <?= field_error('stops') ?>
      <div class="field mt" data-entry-search data-mode="stops">
        <label class="field-label" for="stop-search">Agregar parada</label>
        <input class="input" id="stop-search" type="search" placeholder="Busca un destino, finca, evento…" data-entry-search-input autocomplete="off">
        <div class="search-results" data-entry-search-results hidden></div>
      </div>
    </section>
  </div>
  <aside>
    <section class="card">
      <h2><?= icon('check') ?> Publicación</h2>
      <div class="field"><label class="field-label" for="status">Estado</label>
        <select class="select" id="status" name="status"><?php foreach (['draft' => 'Borrador', 'published' => 'Publicada', 'archived' => 'Archivada'] as $k => $l): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
      <label class="check"><input type="checkbox" name="is_featured" value="1" <?= val('is_featured', $r['is_featured'] ?? 0) ? 'checked' : '' ?>> Destacada en la portada</label>
      <?php if ($r && $r['is_demo']): ?><label class="check"><input type="checkbox" name="keep_demo" value="1" checked> Mantener como demostración</label><?php endif; ?>
      <button class="btn btn-primary btn-block" type="submit"><?= icon('check') ?> Guardar ruta</button>
    </section>
    <section class="card"><?= View::partial('admin-cover', ['name' => 'cover_media_id', 'label' => 'Imagen de la ruta', 'media' => $cover]) ?></section>
    <?php if ($r): ?>
    <section class="card">
      <form method="post" action="<?= e(url('/admin/rutas/' . $r['id'] . '/eliminar')) ?>" data-confirm="¿Eliminar la ruta «<?= e($r['title']) ?>»?"><?= csrf_field() ?><button class="btn btn-danger btn-block" type="submit"><?= icon('trash') ?> Eliminar ruta</button></form>
    </section>
    <?php endif; ?>
  </aside>
</form>
<template id="stop-template">
  <div class="stop-row" data-stop>
    <span class="n"></span><img src="" alt=""><span><strong></strong><br><small class="muted"></small></span>
    <input type="hidden" name="stops[]" value=""><input class="input" name="stop_notes[]" maxlength="255" placeholder="Nota (opcional)">
    <span class="actions"><button class="btn btn-outline btn-sm btn-icon" type="button" data-stop-up aria-label="Subir">↑</button><button class="btn btn-outline btn-sm btn-icon" type="button" data-stop-down aria-label="Bajar">↓</button><button class="btn btn-outline btn-sm btn-icon" type="button" data-stop-remove aria-label="Quitar"><?= icon('close') ?></button></span>
  </div>
</template>
