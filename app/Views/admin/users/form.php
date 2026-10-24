<?php $action = $u ? url('/admin/usuarios/' . $u['id']) : url('/admin/usuarios/nuevo'); $cls = static fn ($n) => has_error($n) ? ' is-invalid' : ''; ?>
<p><a href="<?= e(url('/admin/usuarios')) ?>">← Volver a usuarios</a></p>
<form class="card" style="max-width:720px" method="post" action="<?= e($action) ?>" novalidate>
  <?= csrf_field() ?>
  <div class="row">
    <div class="field"><label class="field-label" for="name">Nombre completo</label><input class="input<?= $cls('name') ?>" id="name" name="name" maxlength="120" value="<?= e(val('name', $u['name'] ?? '')) ?>"><?= field_error('name') ?></div>
    <div class="field"><label class="field-label" for="email">Correo</label><input class="input<?= $cls('email') ?>" id="email" name="email" type="email" value="<?= e(val('email', $u['email'] ?? '')) ?>"><?= field_error('email') ?></div>
    <div class="field"><label class="field-label" for="username">Nombre de usuario</label><input class="input<?= $cls('username') ?>" id="username" name="username" maxlength="40" value="<?= e(val('username', $u['username'] ?? '')) ?>"><?= field_error('username') ?></div>
    <div class="field"><label class="field-label" for="role">Rol</label><select class="select<?= $cls('role') ?>" id="role" name="role"><?php foreach ($roles as $r): ?><option value="<?= e($r['slug']) ?>" <?= val('role', $u['role'] ?? 'editor') === $r['slug'] ? 'selected' : '' ?>><?= e($r['name']) ?> — <?= e($r['description']) ?></option><?php endforeach; ?></select><?= field_error('role') ?></div>
    <div class="field"><label class="field-label" for="password"><?= $u ? 'Nueva contraseña (opcional)' : 'Contraseña' ?></label><input class="input<?= $cls('password') ?>" id="password" name="password" type="password" autocomplete="new-password"><?= field_error('password') ?></div>
    <div class="field"><label class="field-label" for="password_confirmation">Repite la contraseña</label><input class="input<?= $cls('password_confirmation') ?>" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"><?= field_error('password_confirmation') ?></div>
  </div>
  <p class="field-help">Mínimo 10 caracteres combinando letras y números. Al cambiar la contraseña o el rol se cierran las sesiones abiertas de la cuenta.</p>
  <button class="btn btn-primary mt" type="submit"><?= icon('check') ?> Guardar</button>
</form>
