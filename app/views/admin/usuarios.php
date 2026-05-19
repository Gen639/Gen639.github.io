<?php
$activePage = 'usuarios-administracion';
$pageTitle = 'Manage Users | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/usuarios-administracion.css" />';
$old = array_merge([
    'nombre' => '',
    'apellidos' => '',
    'email' => '',
    'telefono' => '',
    'fecha_nacimiento' => '',
    'direccion' => '',
    'sexo' => '',
    'usuario' => '',
    'rol' => 'user',
], isset($old) && is_array($old) ? $old : []);
// Admin user-management view. Mutations are posted to named routes.
include root_path('includes/header.php');
?>
<main class="usuarios-administracion-page"><div class="container">
  <h1>Manage Users</h1>
  <?php if (!empty($errors)) : ?><div class="form-messages error"><ul><?php foreach ($errors as $error) : ?><li><?= e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
  <?php if (!empty($success)) : ?><div class="form-messages success"><ul><li><?= e($success) ?></li></ul></div><?php endif ?>
  <section class="create-user">
    <h2>Create New User</h2>
    <form method="POST" action="index.php?page=admin-user-create" class="user-form">
      <div class="form-row"><div class="form-group"><label for="nombre">First Name: <span class="required">*</span></label><input type="text" id="nombre" name="nombre" required value="<?= e($old['nombre']) ?>"></div><div class="form-group"><label for="apellidos">Last Name: <span class="required">*</span></label><input type="text" id="apellidos" name="apellidos" required value="<?= e($old['apellidos']) ?>"></div></div>
      <div class="form-row"><div class="form-group"><label for="email">Email: <span class="required">*</span></label><input type="email" id="email" name="email" required value="<?= e($old['email']) ?>"></div><div class="form-group"><label for="telefono">Phone: <span class="required">*</span></label><input type="tel" id="telefono" name="telefono" required value="<?= e($old['telefono']) ?>"></div></div>
      <div class="form-row"><div class="form-group"><label for="fecha_nacimiento">Birth Date: <span class="required">*</span></label><input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?= e($old['fecha_nacimiento']) ?>"></div><div class="form-group"><label for="sexo">Gender:</label><select id="sexo" name="sexo"><option value="">-- Select Gender --</option><option value="male"<?= $old['sexo'] === 'male' ? ' selected' : '' ?>>Male</option><option value="female"<?= $old['sexo'] === 'female' ? ' selected' : '' ?>>Female</option><option value="other"<?= $old['sexo'] === 'other' ? ' selected' : '' ?>>Other</option></select></div></div>
      <div class="form-row"><div class="form-group"><label for="direccion">Address:</label><textarea id="direccion" name="direccion" rows="3"><?= e($old['direccion']) ?></textarea></div></div>
      <div class="form-row"><div class="form-group"><label for="usuario">Username: <span class="required">*</span></label><input type="text" id="usuario" name="usuario" required value="<?= e($old['usuario']) ?>"></div><div class="form-group"><label for="password">Password: <span class="required">*</span></label><input type="password" id="password" name="password" required></div></div>
      <div class="password-requirements">
        <h4>Password Requirements:</h4>
        <ul>
          <li>At least 8 characters long</li>
          <li>Contains uppercase and lowercase letters</li>
          <li>Contains at least one number</li>
          <li>Contains at least one special character (!@#$%^&*)</li>
        </ul>
      </div>
      <div class="form-row"><div class="form-group"><label for="rol">Role: <span class="required">*</span></label><select id="rol" name="rol" required><option value="user"<?= $old['rol'] === 'user' ? ' selected' : '' ?>>User</option><option value="admin"<?= $old['rol'] === 'admin' ? ' selected' : '' ?>>Administrator</option></select></div></div>
      <button type="submit" class="btn btn-primary">Create User</button>
    </form>
  </section>
  <section class="users-list"><h2>Users List</h2>
    <?php if (empty($usersData)) : ?><p class="no-users">No users found.</p><?php else : ?>
      <div class="table-responsive"><table class="users-table"><thead><tr><th>Name</th><th>Email</th><th>Username</th><th>Phone</th><th>Role</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($usersData as $user) : ?><tr>
        <td><?= e($user['nombre'] . ' ' . $user['apellidos']) ?></td><td><?= e($user['email']) ?></td><td><?= e($user['usuario']) ?></td><td><?= e($user['telefono'] ?? '-') ?></td><td><span class="role-badge role-<?= e($user['rol']) ?>"><?= e(ucfirst($user['rol'])) ?></span></td>
        <td class="actions-cell"><button class="btn btn-edit" onclick="openEditModal(<?= e(json_encode($user)) ?>)">Edit</button><button class="btn btn-password" onclick="openPasswordModal(<?= e($user['idUser']) ?>)">Change Password</button><form method="POST" action="index.php?page=admin-user-delete" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');"><input type="hidden" name="idUser" value="<?= e($user['idUser']) ?>"><button type="submit" class="btn btn-delete" <?= (int) $user['idUser'] === (int) ($_SESSION['idUser'] ?? 0) ? 'disabled' : '' ?>>Delete</button></form></td>
      </tr><?php endforeach ?>
      </tbody></table></div>
    <?php endif ?>
  </section>
</div></main>
<div id="editModal" class="modal"><div class="modal-content"><span class="close" onclick="closeEditModal()">&times;</span><h2>Edit User</h2>
  <form method="POST" action="index.php?page=admin-user-update" class="user-form">
    <input type="hidden" id="editIdUser" name="idUser">
    <div class="form-row"><div class="form-group"><label for="editNombre">First Name: <span class="required">*</span></label><input type="text" id="editNombre" name="nombre" required></div><div class="form-group"><label for="editApellidos">Last Name: <span class="required">*</span></label><input type="text" id="editApellidos" name="apellidos" required></div></div>
    <div class="form-row"><div class="form-group"><label for="editEmail">Email: <span class="required">*</span></label><input type="email" id="editEmail" name="email" required></div><div class="form-group"><label for="editTelefono">Phone: <span class="required">*</span></label><input type="tel" id="editTelefono" name="telefono" required></div></div>
    <div class="form-row"><div class="form-group"><label for="editFechaNacimiento">Birth Date:</label><input type="date" id="editFechaNacimiento" name="fecha_nacimiento" required></div><div class="form-group"><label for="editSexo">Gender:</label><select id="editSexo" name="sexo"><option value="">-- Select Gender --</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div></div>
    <div class="form-row"><div class="form-group"><label for="editDireccion">Address:</label><textarea id="editDireccion" name="direccion" rows="3"></textarea></div></div>
    <div class="form-row"><div class="form-group"><label for="editRol">Role: <span class="required">*</span></label><select id="editRol" name="rol" required><option value="user">User</option><option value="admin">Administrator</option></select></div></div>
    <div class="modal-actions"><button type="submit" class="btn btn-primary">Save Changes</button><button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button></div>
  </form>
</div></div>
<div id="passwordModal" class="modal"><div class="modal-content"><span class="close" onclick="closePasswordModal()">&times;</span><h2>Change Password</h2>
  <form method="POST" action="index.php?page=admin-user-password" class="user-form"><input type="hidden" id="passwordUserId" name="idUser"><div class="form-group"><label for="newPassword">New Password: <span class="required">*</span></label><input type="password" id="newPassword" name="new_password" required></div><div class="form-group"><label for="confirmPassword">Confirm Password: <span class="required">*</span></label><input type="password" id="confirmPassword" name="confirm_password" required></div><div class="password-requirements"><h4>Password Requirements:</h4><ul><li>At least 8 characters long</li><li>Contains uppercase and lowercase letters</li><li>Contains at least one number</li><li>Contains at least one special character (!@#$%^&*)</li></ul></div><div class="modal-actions"><button type="submit" class="btn btn-primary">Change Password</button><button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Cancel</button></div></form>
</div></div>
<script>
// Modal helpers copy row data into form fields before submitting to AdminController.
function openEditModal(user){document.getElementById('editIdUser').value=user.idUser;document.getElementById('editNombre').value=user.nombre;document.getElementById('editApellidos').value=user.apellidos;document.getElementById('editEmail').value=user.email;document.getElementById('editTelefono').value=user.telefono||'';document.getElementById('editFechaNacimiento').value=user.fecha_nacimiento||'';document.getElementById('editSexo').value=(user.sexo||'').toLowerCase();document.getElementById('editDireccion').value=user.direccion||'';document.getElementById('editRol').value=user.rol;document.getElementById('editModal').style.display='block';}
function closeEditModal(){document.getElementById('editModal').style.display='none';}
function openPasswordModal(userId){document.getElementById('passwordUserId').value=userId;document.getElementById('newPassword').value='';document.getElementById('confirmPassword').value='';document.getElementById('passwordModal').style.display='block';}
function closePasswordModal(){document.getElementById('passwordModal').style.display='none';}
</script>
<?php $footerExtra = ''; include root_path('includes/footer.php'); ?>
