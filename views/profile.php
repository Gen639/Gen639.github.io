<?php
session_start();

$activePage = 'profile';
$prefix = '../';
$pageTitle = 'Profile | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/profile.css" />';

if (!isset($_SESSION['idUser'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = [];
$userData = [];

require __DIR__ . '/../includes/db.php';

if ($dbError !== null) {
    $errors[] = $dbError;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $sexo = trim($_POST['sexo'] ?? '');

        if ($nombre === '' || $apellidos === '' || $email === '' || $telefono === '' || $fecha_nacimiento === '' || $sexo === '') {
            $errors[] = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email direccion.';
        } else {
            $stmt = $mysqli->prepare('SELECT idUser FROM users_data WHERE email = ? AND idUser != ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('si', $email, $_SESSION['idUser']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $errors[] = 'That email direccion is already in use.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Database query failed.';
            }
        }

        if (empty($errors)) {
            $stmt = $mysqli->prepare('
                UPDATE users_data
                SET nombre = ?, apellidos = ?, email = ?, telefono = ?, fecha_nacimiento = ?, direccion = ?, sexo = ?
                WHERE idUser = ?
            ');
            if ($stmt) {
                $stmt->bind_param(
                    'sssssssi',
                    $nombre,
                    $apellidos,
                    $email,
                    $telefono,
                    $fecha_nacimiento,
                    $direccion,
                    $sexo,
                    $_SESSION['idUser']
                );

                if ($stmt->execute()) {
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_name'] = $nombre . ' ' . $apellidos;
                    $success[] = 'Profile updated successfully!';
                } else {
                    $errors[] = 'Could not update your profile. Please try again.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Database query failed.';
            }
        }
    }

    $stmt = $mysqli->prepare('
        SELECT
            ud.nombre,
            ud.apellidos,
            ud.email,
            ud.telefono,
            ud.fecha_nacimiento,
            ud.direccion,
            ud.sexo,
            ul.usuario,
            ul.rol
        FROM users_data ud
        JOIN users_login ul ON ud.idUser = ul.idUser
        WHERE ud.idUser = ? LIMIT 1
    ');
    if ($stmt) {
        $stmt->bind_param('i', $_SESSION['idUser']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
        } else {
            $errors[] = 'User data not found.';
        }
        $stmt->close();
    } else {
        $errors[] = 'Database query failed.';
    }

    $mysqli->close();
}

include __DIR__ . '/../includes/header.php';
?>
<main class="profile-page">
  <h1>Your Profile</h1>

  <?php if (!empty($errors)) : ?>
    <div class="form-messages error">
      <ul>
        <?php foreach ($errors as $error) : ?>
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (!empty($success)) : ?>
    <div class="form-messages success">
      <ul>
        <?php foreach ($success as $message) : ?>
          <li><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (!empty($userData)) : ?>
    <div class="profile-info">
      <div class="profile-info-header">
        <h2>Personal Information</h2>
       
      </div>
      <div class="info-grid">
        <div class="info-item">
          <strong>Name:</strong> <?= htmlspecialchars($userData['nombre'] . ' ' . $userData['apellidos'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="info-item">
          <strong>Username:</strong> <?= htmlspecialchars($userData['usuario'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="info-item">
          <strong>Email:</strong> <?= htmlspecialchars($userData['email'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="info-item">
          <strong>Phone:</strong> <?= htmlspecialchars($userData['telefono'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="info-item">
          <strong>Date of Birth:</strong> <?= htmlspecialchars($userData['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="info-item">
          <strong>Address:</strong> <?= htmlspecialchars($userData['direccion'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="info-item">
          <strong>Gender:</strong> <?= htmlspecialchars(ucfirst($userData['sexo']), ENT_QUOTES, 'UTF-8') ?>
        </div>
         <button type="button" class="btn btn-edit" onclick="openEditModal()">Edit</button>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php if (!empty($userData)) : ?>
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h2>Edit Profile</h2>
      <form method="POST" action="" class="profile-form">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-row">
          <div class="form-group">
            <label for="editNombre">First Name: <span class="required">*</span></label>
            <input type="text" id="editNombre" name="nombre" value="<?= htmlspecialchars($userData['nombre'], ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="form-group">
            <label for="editApellidos">Last Name: <span class="required">*</span></label>
            <input type="text" id="editApellidos" name="apellidos" value="<?= htmlspecialchars($userData['apellidos'], ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="editEmail">Email: <span class="required">*</span></label>
            <input type="email" id="editEmail" name="email" value="<?= htmlspecialchars($userData['email'], ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="form-group">
            <label for="editTelefono">Phone: <span class="required">*</span></label>
            <input type="text" id="editTelefono" name="telefono" value="<?= htmlspecialchars($userData['telefono'], ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="editFechaNacimiento">Date of Birth: <span class="required">*</span></label>
            <input type="date" id="editFechaNacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($userData['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') ?>" required>
          </div>
          <div class="form-group">
            <label for="editSexo">Gender: <span class="required">*</span></label>
            <select id="editSexo" name="sexo" required>
              <option value="">-- Select Gender --</option>
              <option value="female"<?= ($userData['sexo'] ?? '') === 'female' ? ' selected' : '' ?>>Female</option>
              <option value="male"<?= ($userData['sexo'] ?? '') === 'male' ? ' selected' : '' ?>>Male</option>
              <option value="other"<?= ($userData['sexo'] ?? '') === 'other' ? ' selected' : '' ?>>Other</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="editDireccion">Address:</label>
            <textarea id="editDireccion" name="direccion" rows="4"><?= htmlspecialchars($userData['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>

        <div class="modal-actions">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  function openEditModal() {
      document.getElementById('editModal').style.display = 'block';
  }

  function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
  }

  window.onclick = function(event) {
      const editModal = document.getElementById('editModal');
      if (event.target === editModal) {
          editModal.style.display = 'none';
      }
  }
  </script>
<?php endif; ?>
<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>
