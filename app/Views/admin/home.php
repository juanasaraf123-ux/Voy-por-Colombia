<?php use App\Core\View; use App\Sections; ?>
<form method="post" action="<?= e(url('/admin/inicio')) ?>" data-unsaved>
  <?= csrf_field() ?>
  <div class="grid-form">
    <div>
      <section class="card">
        <h2><?= icon('star') ?> Portada cinematográfica</h2>
        <?php foreach (['hero_kicker', 'hero_title', 'hero_subtitle', 'hero_cta', 'hero_words'] as $k): [$max, $label] = $texts[$k]; ?>
          <div class="field"><label class="field-label" for="<?= $k ?>"><?= e($label) ?></label>
            <?php if ($max > 150): ?><textarea class="textarea" id="<?= $k ?>" name="<?= $k ?>" maxlength="<?= $max ?>" rows="2" data-counter="<?= $max ?>"><?= e(val($k, setting($k))) ?></textarea>
            <?php else: ?><input class="input<?= has_error($k) ? ' is-invalid' : '' ?>" id="<?= $k ?>" name="<?= $k ?>" maxlength="<?= $max ?>" value="<?= e(val($k, setting($k))) ?>"><?php endif; ?>
            <?= field_error($k) ?></div>
        <?php endforeach; ?>
        <p class="field-help">La última palabra del título se destaca en amarillo y cursiva.</p>
        <?= View::partial('admin-gallery', ['name' => 'hero_media_ids', 'label' => 'Imágenes de fondo (se alternan con efecto cinematográfico)', 'items' => $heroMedia, 'kinds' => 'image', 'help' => 'Usa entre 3 y 6 imágenes horizontales de alta calidad.']) ?>
        <?= View::partial('admin-cover', ['name' => 'hero_video_id', 'label' => 'Video de fondo (opcional, reemplaza las imágenes)', 'media' => $heroVideo, 'kind' => 'video', 'help' => 'Archivo MP4 o WebM corto (10–30 s), sin sonido y liviano (idealmente menos de 15 MB).']) ?>
      </section>
      <section class="card">
        <h2><?= icon('layers') ?> Bloques de categorías</h2>
        <p class="hint">Edita el texto, la imagen y el orden de los bloques que aparecen en la portada.</p>
        <?php foreach ($sections as $s): $label = $s['section_key'] === 'gallery' ? 'Galería' : Sections::ALL[$s['section_key']]['label']; ?>
          <fieldset style="border:1px solid var(--line);border-radius:14px;padding:14px;margin:0 0 12px">
            <legend class="field-label" style="padding:0 6px"><?= e($label) ?></legend>
            <div class="grid" style="grid-template-columns:120px 1fr;gap:14px;align-items:start">
              <div data-media-single data-kind="image">
                <input type="hidden" name="sections[<?= (int) $s['id'] ?>][cover_media_id]" value="<?= e($s['cover_media_id']) ?>" data-media-input>
                <div class="cover-box" data-media-preview style="aspect-ratio:1"><?php if ($s['path']): ?><img src="<?= e(media_url($s, 'thumb')) ?>" alt=""><?php else: ?><span class="empty-label"><?= icon('image') ?></span><?php endif; ?></div>
                <button class="btn btn-outline btn-sm mt" type="button" data-media-choose>Cambiar</button>
                <button class="btn btn-outline btn-sm" type="button" data-media-clear hidden>Quitar</button>
              </div>
              <div>
                <div class="row">
                  <div class="field"><label class="field-label">Título</label><input class="input" name="sections[<?= (int) $s['id'] ?>][title]" maxlength="80" value="<?= e($s['title']) ?>"></div>
                  <div class="field"><label class="field-label">Lema</label><input class="input" name="sections[<?= (int) $s['id'] ?>][tagline]" maxlength="160" value="<?= e($s['tagline']) ?>"></div>
                </div>
                <div class="field"><label class="field-label">Descripción</label><input class="input" name="sections[<?= (int) $s['id'] ?>][description]" maxlength="500" value="<?= e($s['description']) ?>"></div>
                <div class="flex"><label class="field-label">Orden <input class="input" style="width:80px;display:inline-block" type="number" min="0" max="99" name="sections[<?= (int) $s['id'] ?>][sort_order]" value="<?= (int) $s['sort_order'] ?>"></label>
                  <label class="check"><input type="checkbox" name="sections[<?= (int) $s['id'] ?>][show_on_home]" value="1" <?= $s['show_on_home'] ? 'checked' : '' ?>> Mostrar en la portada</label></div>
              </div>
            </div>
          </fieldset>
        <?php endforeach; ?>
      </section>
    </div>
    <aside>
      <section class="card">
        <h2><?= icon('check') ?> Guardar cambios</h2>
        <button class="btn btn-primary btn-block" type="submit"><?= icon('check') ?> Guardar portada</button>
        <a class="btn btn-outline btn-block mt" href="<?= e(url('/')) ?>" target="_blank"><?= icon('eye') ?> Ver la portada</a>
      </section>
      <section class="card">
        <h2><?= icon('sparkle') ?> Textos de secciones</h2>
        <?php foreach (['map_title', 'map_subtitle', 'home_featured_title', 'home_gems_title', 'closing_title', 'closing_text'] as $k): [$max, $label] = $texts[$k]; ?>
          <div class="field"><label class="field-label" for="<?= $k ?>"><?= e($label) ?></label><input class="input" id="<?= $k ?>" name="<?= $k ?>" maxlength="<?= $max ?>" value="<?= e(val($k, setting($k))) ?>"><?= field_error($k) ?></div>
        <?php endforeach; ?>
      </section>
      <section class="card">
        <h2><?= icon('star') ?> Contenido destacado</h2>
        <p class="hint">Los destinos de Turismo destacados aparecen en la portada (carrusel y postales); el resto de secciones en «Experiencias».</p>
        <div class="table-wrap"><table class="table">
          <thead><tr><th>Publicación</th><th>Orden</th><th>Quitar</th></tr></thead>
          <tbody>
            <?php foreach ($featured as $f): ?>
              <tr><td><a href="<?= e(url('/admin/publicaciones/' . $f['id'] . '/editar')) ?>"><?= e($f['title']) ?></a><br><small class="muted"><?= e(Sections::ALL[$f['section']]['label']) ?> · <?= e(status_label($f['effective_status'])) ?></small></td>
                <td><input class="input" style="width:70px" type="number" min="0" max="999" name="featured[<?= (int) $f['id'] ?>][order]" value="<?= (int) $f['featured_order'] ?>" aria-label="Orden"></td>
                <td><input type="checkbox" name="featured[<?= (int) $f['id'] ?>][remove]" value="1" aria-label="Quitar de destacados"></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table></div>
        <div class="field mt" data-entry-search data-pick-name="add_featured">
          <label class="field-label" for="feat-search">Agregar destacado</label>
          <input class="input" id="feat-search" type="search" placeholder="Busca una publicación…" data-entry-search-input autocomplete="off">
          <input type="hidden" name="add_featured" data-entry-search-value>
          <div class="search-results" data-entry-search-results hidden></div>
          <p class="hint" data-entry-search-picked></p>
        </div>
      </section>
    </aside>
  </div>
</form>
