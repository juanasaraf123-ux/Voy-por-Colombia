<div class="flex between" style="margin-bottom:14px"><p class="muted" style="margin:0"><strong>Administrador:</strong> acceso total. <strong>Editor:</strong> gestiona contenido, multimedia, mapa, portada, rutas y categorías; no gestiona usuarios, auditoría ni configuración.</p>
  <a class="btn btn-primary" href="<?= e(url('/admin/usuarios/nuevo')) ?>"><?= icon('plus') ?> Nueva cuenta</a></div>
<div class="card"><div class="table-wrap"><table class="table">
  <thead><tr><th>Nombre</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Último ingreso</th><th></th></tr></thead>
  <tbody><?php foreach ($users as $u): ?>
    <tr><td><strong><?= e($u['name']) ?></strong><?= (int) $u['id'] === (int) $user['id'] ? ' <span class="pill pill-scheduled">Tú</span>' : '' ?></td><td><?= e($u['username']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role_name']) ?></td>
      <td><?= $u['is_active'] ? '<span class="pill pill-published">Activa</span>' : '<span class="pill pill-off">Desactivada</span>' ?></td>
      <td class="muted"><?= e($u['last_login_at'] ? fecha_hora($u['last_login_at']) : 'Nunca') ?></td>
      <td class="actions"><a class="btn btn-outline btn-sm" href="<?= e(url('/admin/usuarios/' . $u['id'])) ?>"><?= icon('edit') ?> Editar</a>
        <?php if ((int) $u['id'] !== (int) $user['id']): ?><form method="post" action="<?= e(url('/admin/usuarios/' . $u['id'] . '/estado')) ?>" data-confirm="<?= $u['is_active'] ? '¿Desactivar la cuenta de ' . e($u['name']) . '? No podrá ingresar.' : '¿Activar la cuenta?' ?>"><?= csrf_field() ?><button class="btn btn-outline btn-sm" type="submit"><?= $u['is_active'] ? 'Desactivar' : 'Activar' ?></button></form><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div></div>
