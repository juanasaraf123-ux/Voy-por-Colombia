<?php use App\Sections; ?>
<p class="hint">Las categorías alimentan los filtros del sitio y del mapa. Eliminar una categoría no elimina las publicaciones.</p>
<?php foreach (Sections::ALL as $key => $s): ?>
  <section class="card" id="sec-<?= e($key) ?>" style="--c: <?= e($s['color']) ?>">
    <h2><?= icon($s['icon']) ?> <?= e($s['label']) ?></h2>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Nombre</th><th>Ícono</th><th>Color</th><th>Orden</th><th>Uso</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($bySection[$key] ?? [] as $c): $fid = 'cat-' . $c['id']; ?>
          <tr>
            <td><input class="input" form="<?= $fid ?>" name="name" value="<?= e($c['name']) ?>" maxlength="80" aria-label="Nombre"></td>
            <td><select class="select" form="<?= $fid ?>" name="icon" aria-label="Ícono"><?php foreach ($icons as $ic): ?><option <?= $c['icon'] === $ic ? 'selected' : '' ?>><?= e($ic) ?></option><?php endforeach; ?></select></td>
            <td><input type="color" form="<?= $fid ?>" name="color" value="<?= e($c['color'] ?: '#0f9d77') ?>" aria-label="Color"></td>
            <td><input class="input" style="width:70px" type="number" form="<?= $fid ?>" name="sort_order" value="<?= (int) $c['sort_order'] ?>" min="0" max="99" aria-label="Orden"></td>
            <td class="muted"><?= (int) $c['uses'] ?></td>
            <td class="actions">
              <form id="<?= $fid ?>" method="post" action="<?= e(url('/admin/categorias/' . $c['id'])) ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit"><?= icon('check') ?> Guardar</button></form>
              <form method="post" action="<?= e(url('/admin/categorias/' . $c['id'] . '/eliminar')) ?>" data-confirm="¿Eliminar la categoría «<?= e($c['name']) ?>»? Se quitará de <?= (int) $c['uses'] ?> publicaciones."><?= csrf_field() ?><button class="btn btn-outline btn-sm btn-icon" type="submit" aria-label="Eliminar"><?= icon('trash') ?></button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td><input class="input" form="new-<?= e($key) ?>" name="name" maxlength="80" placeholder="Nueva categoría" aria-label="Nombre de la nueva categoría"></td>
          <td><select class="select" form="new-<?= e($key) ?>" name="icon" aria-label="Ícono"><?php foreach ($icons as $ic): ?><option><?= e($ic) ?></option><?php endforeach; ?></select></td>
          <td><input type="color" form="new-<?= e($key) ?>" name="color" value="<?= e($s['color']) ?>" aria-label="Color"></td>
          <td><input class="input" style="width:70px" type="number" form="new-<?= e($key) ?>" name="sort_order" value="20" aria-label="Orden"></td>
          <td></td>
          <td class="actions"><form id="new-<?= e($key) ?>" method="post" action="<?= e(url('/admin/categorias')) ?>"><?= csrf_field() ?><input type="hidden" name="section" value="<?= e($key) ?>"><button class="btn btn-sm" type="submit"><?= icon('plus') ?> Agregar</button></form></td>
        </tr>
      </tbody>
    </table></div>
  </section>
<?php endforeach; ?>
<?= field_error('name') ?>
