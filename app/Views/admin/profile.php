<?php $cls = static fn ($n) => has_error($n) ? ' is-invalid' : ''; ?>
<form class="card" style="max-width:720px" method="post" action="<?= e(url('/admin/perfil')) ?>" novalidate>
  <?= csrf_field() ?>
  <p class="hint">Usuario: <strong><?= e($user['username']) ?></strong> · Rol: <strong><?= e($user['role_name']) ?></strong></p>
  <div class="row">
    <div class="field"><label class="field-label" for="name">Nombre completo</label><input class="input<?= $cls('name') ?>" id="name" name="name" value="<?= e(val('name', $user['name'])) ?>"><?= field_error('name') ?></div>
    <div class="field"><label class="field-label" for="email">Correo</label><input class="input<?= $cls('email') ?>" id="email" name="email" type="email" value="<?= e(val('email', $user['email'])) ?>"><?= field_error('email') ?></div>
  </div>
  <h2 class="mt"><?= icon('shield') ?> Cambiar contraseña</h2>
  <div class="row">
    <div class="field" style="grid-column:1/-1"><label class="field-label" for="current_password">Contraseña actual</label><input class="input<?= $cls('current_password') ?>" id="current_password" name="current_password" type="password" autocomplete="current-password"><?= field_error('current_password') ?></div>
    <div class="field"><label class="field-label" for="password">Nueva contraseña</label><input class="input<?= $cls('password') ?>" id="password" name="password" type="password" autocomplete="new-password"><?= field_error('password') ?></div>
    <div class="field"><label class="field-label" for="password_confirmation">Repite la nueva contraseña</label><input class="input<?= $cls('password_confirmation') ?>" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"><?= field_error('password_confirmation') ?></div>
  </div>
  <button class="btn btn-primary" type="submit"><?= icon('check') ?> Guardar perfil</button>
</form>
