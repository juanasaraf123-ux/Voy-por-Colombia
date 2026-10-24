<?php
use App\Core\Auth;
use App\Core\View;

$e = $entry;
$action = $e ? url('/admin/publicaciones/' . $e['id'] . '/editar') : url('/admin/contenido/' . $def['path'] . '/nuevo');
$status = val('status', $e['status'] ?? 'draft');
$selectedCats = array_map('intval', (array) val('categories', $selectedCategories));
$meta = $e['meta'] ?? [];
$metaVal = static function (array $f) use ($meta) {
    $saved = $meta[$f['key']] ?? '';
    return val('meta_' . $f['key'], is_array($saved) ? implode("\n", $saved) : $saved);
};
$cls = static fn (string $n) => has_error($n) ? ' is-invalid' : '';
?>
<form method="post" action="<?= e($action) ?>" class="grid-form" data-entry-form data-unsaved novalidate style="--c: <?= e($def['color']) ?>">
  <?= csrf_field() ?>
  <div>
    <?php if ($e): ?>
      <div class="flex" style="margin-bottom:14px">
        <span class="pill pill-<?= e($e['effective_status']) ?>"><?= e(status_label($e['effective_status'])) ?></span>
        <?php if ($e['is_demo']): ?><span class="pill pill-demo">Contenido de demostración</span><?php endif; ?>
        <span class="hint">Actualizado <?= e(fecha_hora($e['updated_at'])) ?></span>
        <a class="btn btn-outline btn-sm" style="margin-left:auto" href="<?= e(url('/admin/publicaciones/' . $e['id'] . '/vista-previa')) ?>" target="_blank"><?= icon('eye') ?> Vista previa</a>
        <?php if ($e['effective_status'] === 'published'): ?><a class="btn btn-outline btn-sm" href="<?= e($e['url']) ?>" target="_blank"><?= icon('globe') ?> Ver publicado</a><?php endif; ?>
      </div>
    <?php endif; ?>

    <section class="card">
      <div class="field">
        <label class="field-label" for="title">Título</label>
        <input class="input input-title<?= $cls('title') ?>" id="title" name="title" maxlength="190" required value="<?= e(val('title', $e['title'] ?? '')) ?>" data-slug-source>
        <?= field_error('title') ?>
      </div>
      <div class="field">
        <label class="field-label" for="slug">Dirección web</label>
        <div class="flex" style="flex-wrap:nowrap"><span class="hint" style="white-space:nowrap">/<?= e($def['path']) ?>/</span><input class="input<?= $cls('slug') ?>" id="slug" name="slug" value="<?= e(val('slug', $e['slug'] ?? '')) ?>" placeholder="se genera a partir del título" data-slug-target></div>
        <?= field_error('slug') ?>
      </div>
      <div class="field">
        <label class="field-label" for="summary">Resumen</label>
        <textarea class="textarea<?= $cls('summary') ?>" id="summary" name="summary" maxlength="400" rows="3" data-counter="400" placeholder="Una o dos frases que despierten curiosidad."><?= e(val('summary', $e['summary'] ?? '')) ?></textarea>
        <p class="field-help">Aparece en tarjetas, en el mapa y en buscadores. Obligatorio para publicar.</p>
        <?= field_error('summary') ?>
      </div>
      <div class="field" data-md-editor>
        <label class="field-label" for="body">Contenido</label>
        <div class="md-toolbar" role="toolbar" aria-label="Formato">
          <button type="button" data-md="h2" title="Subtítulo">Título</button>
          <button type="button" data-md="bold" title="Negrita"><strong>N</strong></button>
          <button type="button" data-md="italic" title="Cursiva"><em>C</em></button>
          <button type="button" data-md="list" title="Lista">• Lista</button>
          <button type="button" data-md="quote" title="Cita">❝ Cita</button>
          <button type="button" data-md="link" title="Enlace"><?= icon('link') ?></button>
          <button type="button" data-md-preview style="margin-left:auto" aria-pressed="false"><?= icon('eye') ?> Vista previa</button>
        </div>
        <textarea class="textarea tall" id="body" name="body" data-md-input><?= e(val('body', $e['body'] ?? '')) ?></textarea>
        <div class="md-preview" data-md-output hidden></div>
        <p class="field-help">Formato sencillo: <code>## Subtítulo</code>, <code>**negrita**</code>, <code>*cursiva*</code>, listas con <code>-</code>, citas con <code>&gt;</code> y enlaces <code>[texto](https://…)</code>. El HTML no se interpreta por seguridad.</p>
      </div>
    </section>

    <?php if ($fields): ?>
    <section class="card">
      <h2><?= icon($def['icon']) ?> Detalles de <?= e(mb_strtolower($def['label'])) ?></h2>
      <div class="row">
        <?php foreach ($fields as $f): $n = 'meta_' . $f['key']; $wide = in_array($f['type'], ['lines', 'textarea'], true); ?>
          <div class="field" <?= $wide ? 'style="grid-column:1/-1"' : '' ?>>
            <label class="field-label" for="<?= e($n) ?>"><?= e($f['label']) ?></label>
            <?php if ($f['type'] === 'select'): ?>
              <select class="select<?= $cls($n) ?>" id="<?= e($n) ?>" name="<?= e($n) ?>"><option value="">—</option><?php foreach ($f['options'] as $o): ?><option <?= $metaVal($f) === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?></select>
            <?php elseif ($wide): ?>
              <textarea class="textarea<?= $cls($n) ?>" id="<?= e($n) ?>" name="<?= e($n) ?>" rows="<?= $f['type'] === 'lines' ? 5 : 4 ?>"><?= e($metaVal($f)) ?></textarea>
            <?php else: ?>
              <input class="input<?= $cls($n) ?>" id="<?= e($n) ?>" name="<?= e($n) ?>" type="<?= e(in_array($f['type'], ['url', 'email', 'tel'], true) ? $f['type'] : 'text') ?>" value="<?= e($metaVal($f)) ?>">
            <?php endif; ?>
            <?php if (!empty($f['help'])): ?><p class="field-help"><?= e($f['help']) ?></p><?php endif; ?>
            <?= field_error($n) ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <section class="card">
      <h2><?= icon('camera') ?> Multimedia</h2>
      <?= View::partial('admin-gallery', ['name' => 'media_ids', 'label' => 'Galería, videos, audios y documentos', 'items' => $gallery, 'help' => 'Arrastra para ordenar. Las imágenes se muestran en mosaico; los videos y audios con su reproductor.']) ?>
    </section>

    <section class="card">
      <h2><?= icon('map') ?> Ubicación</h2>
      <div class="row">
        <div class="field">
          <label class="field-label" for="department_id">Departamento</label>
          <select class="select<?= $cls('department_id') ?>" id="department_id" name="department_id" data-dep-select data-target="#municipality_id">
            <option value="">Sin departamento</option>
            <?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>" data-slug="<?= e($d['slug']) ?>" <?= (int) val('department_id', $e['department_id'] ?? 0) === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?>
          </select><?= field_error('department_id') ?>
        </div>
        <div class="field">
          <label class="field-label" for="municipality_id">Municipio</label>
          <select class="select<?= $cls('municipality_id') ?>" id="municipality_id" name="municipality_id" data-mun-select data-selected="<?= (int) val('municipality_id', $e['municipality_id'] ?? 0) ?>">
            <option value="">Sin municipio</option>
            <?php foreach ($municipalities as $m): ?><option value="<?= (int) $m['id'] ?>" data-lat="<?= e($m['lat']) ?>" data-lng="<?= e($m['lng']) ?>" <?= (int) val('municipality_id', $e['municipality_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option><?php endforeach; ?>
          </select><?= field_error('municipality_id') ?>
        </div>
        <div class="field">
          <label class="field-label" for="lat">Latitud</label>
          <input class="input<?= $cls('lat') ?>" id="lat" name="lat" inputmode="decimal" value="<?= e(val('lat', $e['lat'] ?? '')) ?>" placeholder="Ej. 4.6386" data-lat>
        </div>
        <div class="field">
          <label class="field-label" for="lng">Longitud</label>
          <input class="input" id="lng" name="lng" inputmode="decimal" value="<?= e(val('lng', $e['lng'] ?? '')) ?>" placeholder="Ej. -75.4875" data-lng>
        </div>
      </div>
      <?= field_error('lat') ?>
      <div class="map-picker" data-map-picker aria-label="Haz clic en el mapa para ubicar el lugar"></div>
      <div class="flex mt">
        <button class="btn btn-outline btn-sm" type="button" data-use-municipality><?= icon('pin') ?> Usar el centro del municipio</button>
        <button class="btn btn-outline btn-sm" type="button" data-clear-coords><?= icon('close') ?> Quitar coordenadas</button>
        <span class="hint">Haz clic en el mapa o arrastra el marcador para ubicar el lugar.</span>
      </div>
      <label class="check"><input type="checkbox" name="show_on_map" value="1" <?= val('show_on_map', $e['show_on_map'] ?? 1) ? 'checked' : '' ?>> Mostrar en el mapa interactivo</label>
    </section>
  </div>

  <aside>
    <section class="card">
      <h2><?= icon('check') ?> Publicación</h2>
      <div class="field">
        <label class="field-label" for="status">Estado</label>
        <select class="select" id="status" name="status">
          <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Borrador (no visible)</option>
          <?php if (Auth::can('content.publish')): ?><option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publicado</option><?php endif; ?>
          <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archivado (no visible)</option>
        </select>
      </div>
      <div class="field">
        <label class="field-label" for="published_at">Fecha de publicación</label>
        <input class="input<?= $cls('published_at') ?>" type="datetime-local" id="published_at" name="published_at" value="<?= e(dt_local(val('published_at', $e['published_at'] ?? ''))) ?>">
        <p class="field-help">Si eliges una fecha futura y el estado es «Publicado», quedará <strong>programada</strong> y aparecerá automáticamente en esa fecha.</p>
        <?= field_error('published_at') ?>
      </div>
      <?php if ($e && $e['is_demo']): ?><label class="check"><input type="checkbox" name="keep_demo" value="1" checked> Mantener marcado como demostración</label><?php endif; ?>
      <div class="flex">
        <button class="btn btn-primary" type="submit" style="flex:1"><?= icon('check') ?> Guardar</button>
        <?php if ($e): ?><button class="btn btn-outline" type="submit" formaction="<?= e($action) ?>" formtarget="_self" name="_preview" value="1" data-save-preview title="Guardar y abrir vista previa"><?= icon('eye') ?></button><?php endif; ?>
      </div>
    </section>

    <section class="card">
      <?= View::partial('admin-cover', ['name' => 'cover_media_id', 'label' => 'Imagen principal', 'media' => $cover, 'help' => 'Se recomienda horizontal, mínimo 1600 px de ancho.']) ?>
    </section>

    <section class="card">
      <h2><?= icon('tag') ?> Categorías</h2>
      <div class="cat-picks">
        <?php foreach ($categories as $c): ?>
          <label class="cat-pick" style="--c: <?= e($c['color'] ?: $def['color']) ?>"><input type="checkbox" name="categories[]" value="<?= (int) $c['id'] ?>" <?= in_array((int) $c['id'], $selectedCats, true) ? 'checked' : '' ?>><span><?= icon($c['icon'] ?: 'tag') ?> <?= e($c['name']) ?></span></label>
        <?php endforeach; ?>
        <?php if (!$categories): ?><p class="hint">No hay categorías. <a href="<?= e(url('/admin/categorias')) ?>">Crear categorías</a></p><?php endif; ?>
      </div>
    </section>

    <section class="card">
      <h2><?= icon('star') ?> Destacar</h2>
      <label class="check"><input type="checkbox" name="is_featured" value="1" <?= val('is_featured', $e['is_featured'] ?? 0) ? 'checked' : '' ?>> Destacado en portada y sección</label>
      <div class="field"><label class="field-label" for="featured_order">Orden entre destacados</label><input class="input" type="number" min="0" max="999" id="featured_order" name="featured_order" value="<?= e(val('featured_order', $e['featured_order'] ?? 0)) ?>"></div>
      <label class="check"><input type="checkbox" name="is_hidden_gem" value="1" <?= val('is_hidden_gem', $e['is_hidden_gem'] ?? 0) ? 'checked' : '' ?>> Joya poco conocida</label>
    </section>

    <?php if (App\Sections::hasEvents($key)): ?>
    <section class="card">
      <h2><?= icon('calendar') ?> Fechas del evento</h2>
      <div class="field"><label class="field-label" for="event_start">Inicio</label><input class="input<?= $cls('event_start') ?>" type="datetime-local" id="event_start" name="event_start" value="<?= e(dt_local(val('event_start', $e['event_start'] ?? ''))) ?>"><?= field_error('event_start') ?></div>
      <div class="field"><label class="field-label" for="event_end">Fin</label><input class="input<?= $cls('event_end') ?>" type="datetime-local" id="event_end" name="event_end" value="<?= e(dt_local(val('event_end', $e['event_end'] ?? ''))) ?>"><?= field_error('event_end') ?></div>
      <p class="field-help">Si tiene fechas, aparece en el calendario de Cultura y en «Próximas festividades».</p>
    </section>
    <?php endif; ?>

    <?php if ($key === 'news'): ?>
    <section class="card">
      <div class="field"><label class="field-label" for="author_name">Autor</label><input class="input" id="author_name" name="author_name" maxlength="120" value="<?= e(val('author_name', $e['author_name'] ?? '')) ?>"></div>
    </section>
    <?php endif; ?>
  </aside>
</form>

<?php if ($e && Auth::can('content.delete')): ?>
<section class="card" style="margin-top:18px;border:1px solid #f3c6ca">
  <h2 style="color:#a31d2b"><?= icon('trash') ?> Eliminar publicación</h2>
  <p class="hint">Esta acción no se puede deshacer. Si solo quieres ocultarla, usa el estado «Archivado». Los archivos de la biblioteca no se eliminan.</p>
  <form method="post" action="<?= e(url('/admin/publicaciones/' . $e['id'] . '/eliminar')) ?>" class="flex" data-confirm="¿Eliminar definitivamente «<?= e($e['title']) ?>»?">
    <?= csrf_field() ?>
    <label class="sr-only" for="confirm-del">Escribe ELIMINAR</label>
    <input class="input" style="max-width:220px" id="confirm-del" name="confirm" placeholder="Escribe ELIMINAR" autocomplete="off">
    <button class="btn btn-danger" type="submit"><?= icon('trash') ?> Eliminar</button>
  </form>
</section>
<?php endif; ?>
