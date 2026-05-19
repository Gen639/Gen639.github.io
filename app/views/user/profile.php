<?php
$activePage = 'profile';
$pageTitle = 'Profile | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/profile.css" />';
// Authenticated profile view with separate modals for profile and password edits.
include root_path('includes/header.php');
?>
<main class="profile-page">
  <h1>Your Profile</h1>

  <?php if (!empty($errors)) : ?>
    <div class="form-messages error"><ul><?php foreach ($errors as $error) : ?><li><?= e($error) ?></li><?php endforeach ?></ul></div>
  <?php endif ?>

  <?php if (!empty($success)) : ?>
    <div class="form-messages success"><ul><li><?= e($success) ?></li></ul></div>
  <?php endif ?>

  <?php if (!empty($userData)) : ?>
    <div class="profile-info">
      <div class="profile-info-header"><h2>Personal Information</h2></div>
      <div class="info-grid">
        <div class="info-item"><strong>Name:</strong> <?= e($userData['nombre'] . ' ' . $userData['apellidos']) ?></div>
        <div class="info-item"><strong>Username:</strong> <?= e($userData['usuario']) ?></div>
        <div class="info-item"><strong>Email:</strong> <?= e($userData['email']) ?></div>
        <div class="info-item"><strong>Phone:</strong> <?= e($userData['telefono']) ?></div>
        <div class="info-item"><strong>Date of Birth:</strong> <?= e($userData['fecha_nacimiento']) ?></div>
        <div class="info-item"><strong>Address:</strong> <?= e(($userData['direccion'] ?? '') !== '' ? $userData['direccion'] : '-') ?></div>
        <div class="info-item"><strong>Gender:</strong> <?= e(($userData['sexo'] ?? '') !== '' ? ucfirst((string) $userData['sexo']) : '-') ?></div>
        <button type="button" class="btn btn-edit" onclick="openEditModal()">Edit</button>
        <button type="button" class="btn btn-password" onclick="openPasswordModal()">Change Password</button>
      </div>
    </div>
  <?php endif ?>
</main>

<?php if (!empty($userData)) : ?>
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h2>Edit Profile</h2>
      <form method="POST" action="index.php?page=profile-update" class="profile-form">
        <div class="form-row">
          <div class="form-group"><label for="editNombre">First Name: <span class="required">*</span></label><input type="text" id="editNombre" name="nombre" value="<?= e($userData['nombre']) ?>" required></div>
          <div class="form-group"><label for="editApellidos">Last Name: <span class="required">*</span></label><input type="text" id="editApellidos" name="apellidos" value="<?= e($userData['apellidos']) ?>" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label for="editEmail">Email: <span class="required">*</span></label><input type="email" id="editEmail" name="email" value="<?= e($userData['email']) ?>" required></div>
          <div class="form-group"><label for="editTelefono">Phone: <span class="required">*</span></label><input type="text" id="editTelefono" name="telefono" value="<?= e($userData['telefono']) ?>" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label for="editFechaNacimiento">Date of Birth: <span class="required">*</span></label><input type="date" id="editFechaNacimiento" name="fecha_nacimiento" value="<?= e($userData['fecha_nacimiento']) ?>" required></div>
          <div class="form-group">
            <label for="editSexo">Gender:</label>
            <select id="editSexo" name="sexo">
              <option value="">-- Select Gender --</option>
              <option value="female"<?= ($userData['sexo'] ?? '') === 'female' ? ' selected' : '' ?>>Female</option>
              <option value="male"<?= ($userData['sexo'] ?? '') === 'male' ? ' selected' : '' ?>>Male</option>
              <option value="other"<?= ($userData['sexo'] ?? '') === 'other' ? ' selected' : '' ?>>Other</option>
            </select>
          </div>
        </div>
        <div class="form-row"><div class="form-group"><label for="editDireccion">Address:</label><textarea id="editDireccion" name="direccion" rows="4"><?= e($userData['direccion'] ?? '') ?></textarea></div></div>
        <div class="modal-actions"><button type="submit" class="btn btn-primary">Save Changes</button><button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button></div>
      </form>
    </div>
  </div>
  <div id="passwordModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closePasswordModal()">&times;</span>
      <h2>Change Password</h2>
      <form method="POST" action="index.php?page=profile-password" class="profile-form">
        <div class="form-group"><label for="currentPassword">Current Password: <span class="required">*</span></label><input type="password" id="currentPassword" name="current_password" autocomplete="current-password" required></div>
        <div class="form-group"><label for="newPassword">New Password: <span class="required">*</span></label><input type="password" id="newPassword" name="new_password" autocomplete="new-password" required></div>
        <div class="form-group"><label for="confirmPassword">Confirm New Password: <span class="required">*</span></label><input type="password" id="confirmPassword" name="confirm_password" autocomplete="new-password" required></div>
        <div class="modal-actions"><button type="submit" class="btn btn-primary">Change Password</button><button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Cancel</button></div>
      </form>
    </div>
  </div>
  <script>
  // The modals are kept local to this view because no other page uses these exact fields.
  function openEditModal() { document.getElementById('editModal').style.display = 'block'; }
  function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
  function openPasswordModal() {
      document.getElementById('currentPassword').value = '';
      document.getElementById('newPassword').value = '';
      document.getElementById('confirmPassword').value = '';
      document.getElementById('passwordModal').style.display = 'block';
  }
  function closePasswordModal() { document.getElementById('passwordModal').style.display = 'none'; }
  window.onclick = function(event) {
      const editModal = document.getElementById('editModal');
      const passwordModal = document.getElementById('passwordModal');
      if (event.target === editModal) editModal.style.display = 'none';
      if (event.target === passwordModal) passwordModal.style.display = 'none';
  }
  </script>
<?php endif ?>
<?php
$footerExtra = '';
include root_path('includes/footer.php');
?>
