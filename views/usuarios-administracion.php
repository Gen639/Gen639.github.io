<?php
session_start();

$activePage = 'usuarios-administracion';
$prefix = '../';
$pageTitle = 'Manage Users | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/usuarios-administracion.css" />';

// Verify admin is logged in
if (!isset($_SESSION['idUser']) || ($_SESSION['rol'] ?? null) !== 'admin') {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = [];

// Database connection
require __DIR__ . '/../includes/db.php';

if ($dbError !== null) {
    $errors[] = $dbError;
} else {
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // Create new user
        if ($action === 'create') {
            $nombre = $_POST['nombre'] ?? '';
            $apellidos = $_POST['apellidos'] ?? '';
            $email = $_POST['email'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
            $sexo = $_POST['sexo'] ?? '';
            $usuario = $_POST['usuario'] ?? '';
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? 'user';

            // Validate inputs
            if (empty($nombre) || empty($apellidos) || empty($email) || empty($telefono) || empty($fecha_nacimiento) || empty($usuario) || empty($password)) {
                $errors[] = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format.';
            } else {
                // Check if email or usuario already exists
                $stmt = $mysqli->prepare('SELECT idUser FROM users_login WHERE usuario = ?');
                if ($stmt) {
                    $stmt->bind_param('s', $usuario);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $errors[] = 'Username already exists.';
                    } else {
                        $stmt2 = $mysqli->prepare('SELECT idUser FROM users_data WHERE email = ?');
                        if ($stmt2) {
                            $stmt2->bind_param('s', $email);
                            $stmt2->execute();
                            $result2 = $stmt2->get_result();
                            
                            if ($result2->num_rows > 0) {
                                $errors[] = 'Email already exists.';
                            } else {
                                // Hash password
                                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                                // Insert user data
                                $stmt3 = $mysqli->prepare('INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, sexo) VALUES (?, ?, ?, ?, ?, ?)');
                                if ($stmt3) {
                                    $stmt3->bind_param('ssssss', $nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $sexo);
                                    if ($stmt3->execute()) {
                                        $newUserId = $stmt3->insert_id;
                                        
                                        // Insert login credentials
                                        $stmt4 = $mysqli->prepare('INSERT INTO users_login (idUser, usuario, password, rol) VALUES (?, ?, ?, ?)');
                                        if ($stmt4) {
                                            $stmt4->bind_param('isss', $newUserId, $usuario, $hashedPassword, $rol);
                                            if ($stmt4->execute()) {
                                                $success[] = 'User created successfully!';
                                            } else {
                                                $errors[] = 'Error creating login credentials.';
                                                // Delete the user data that was just created
                                                $cleanupStmt = $mysqli->prepare('DELETE FROM users_data WHERE idUser = ?');
                                                if ($cleanupStmt) {
                                                    $cleanupStmt->bind_param('i', $newUserId);
                                                    $cleanupStmt->execute();
                                                    $cleanupStmt->close();
                                                }
                                            }
                                            $stmt4->close();
                                        }
                                    } else {
                                        $errors[] = 'Error creating user. Please try again.';
                                    }
                                    $stmt3->close();
                                }
                            }
                            $stmt2->close();
                        }
                    }
                    $stmt->close();
                }
            }
        }

        // Update user
        elseif ($action === 'update') {
            $idUser = $_POST['idUser'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $apellidos = $_POST['apellidos'] ?? '';
            $email = $_POST['email'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
            $sexo = $_POST['sexo'] ?? '';
            $rol = $_POST['rol'] ?? 'user';

            if (empty($idUser) || empty($nombre) || empty($apellidos) || empty($email) || empty($telefono) || empty($fecha_nacimiento)) {
                $errors[] = 'Missing required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format.';
            } else {
                // Check if email is already used by another user
                $stmt = $mysqli->prepare('SELECT idUser FROM users_data WHERE email = ? AND idUser != ?');
                if ($stmt) {
                    $stmt->bind_param('si', $email, $idUser);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $errors[] = 'Email is already used by another user.';
                    } else {
                        // Update user data
                        $stmt2 = $mysqli->prepare('UPDATE users_data SET nombre = ?, apellidos = ?, email = ?, telefono = ?, fecha_nacimiento = ?, sexo = ? WHERE idUser = ?');
                        if ($stmt2) {
                            $stmt2->bind_param('ssssssi', $nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $sexo, $idUser);
                            if ($stmt2->execute()) {
                                // Update user rol
                                $stmt3 = $mysqli->prepare('UPDATE users_login SET rol = ? WHERE idUser = ?');
                                if ($stmt3) {
                                    $stmt3->bind_param('si', $rol, $idUser);
                                    if ($stmt3->execute()) {
                                        $success[] = 'User updated successfully!';
                                    } else {
                                        $errors[] = 'Error updating user rol.';
                                    }
                                    $stmt3->close();
                                }
                            } else {
                                $errors[] = 'Error updating user. Please try again.';
                            }
                            $stmt2->close();
                        }
                    }
                    $stmt->close();
                }
            }
        }

        // Delete user
        elseif ($action === 'delete') {
            $idUser = $_POST['idUser'] ?? '';

            if (empty($idUser)) {
                $errors[] = 'User ID is missing.';
            } elseif ($idUser == $_SESSION['idUser']) {
                $errors[] = 'You cannot delete your own account.';
            } else {
                // Delete user (cascade delete will handle login and appointments)
                $stmt = $mysqli->prepare('DELETE FROM users_data WHERE idUser = ?');
                if ($stmt) {
                    $stmt->bind_param('i', $idUser);
                    if ($stmt->execute()) {
                        $success[] = 'User deleted successfully!';
                    } else {
                        $errors[] = 'Error deleting user. Please try again.';
                    }
                    $stmt->close();
                }
            }
        }

        // Change password
        elseif ($action === 'change_password') {
            $idUser = $_POST['idUser'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($idUser) || empty($newPassword)) {
                $errors[] = 'Please enter a new password.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare('UPDATE users_login SET password = ? WHERE idUser = ?');
                if ($stmt) {
                    $stmt->bind_param('si', $hashedPassword, $idUser);
                    if ($stmt->execute()) {
                        $success[] = 'Password changed successfully!';
                    } else {
                        $errors[] = 'Error changing password. Please try again.';
                    }
                    $stmt->close();
                }
            }
        }
    }

    // Fetch all users with their login info
    $usersData = [];
    $stmt = $mysqli->prepare('
        SELECT
            ud.idUser,
            ud.nombre,
            ud.apellidos,
            ud.email,
            ud.telefono,
            ud.fecha_nacimiento,
            ud.sexo,
            ul.usuario,
            ul.rol
        FROM users_data ud
        JOIN users_login ul ON ud.idUser = ul.idUser
        ORDER BY ud.nombre ASC
    ');
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $usersData[] = $row;
        }
        $stmt->close();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<main class="usuarios-administracion-page">
    <div class="container">
        <h1>Manage Users</h1>

        <!-- Messages -->
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
                    <?php foreach ($success as $msg) : ?>
                        <li><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Create New User Section -->
        <section class="create-user">
            <h2>Create New User</h2>
            <form method="POST" action="" class="user-form">
                <input type="hidden" name="action" value="create">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">First Name: <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Last Name: <span class="required">*</span></label>
                        <input type="text" id="apellidos" name="apellidos" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email: <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Phone: <span class="required">*</span></label>
                        <input type="tel" id="telefono" name="telefono" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_nacimiento">Birth Date: <span class="required">*</span></label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                    <div class="form-group">
                        <label for="sexo">Gender:</label>
                        <select id="sexo" name="sexo">
                            <option value="">-- Select Gender --</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario">Username: <span class="required">*</span></label>
                        <input type="text" id="usuario" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password: <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="rol">Role: <span class="required">*</span></label>
                        <select id="rol" name="rol" required>
                            <option value="user">User</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Create User</button>
            </form>
        </section>

        <!-- Users List Section -->
        <section class="users-list">
            <h2>Users List</h2>

            <?php if (empty($usersData)) : ?>
                <p class="no-users">No users found.</p>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usersData as $user) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($user['usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($user['telefono'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="role-badge role-<?= htmlspecialchars($user['rol'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars(ucfirst($user['rol']), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <button 
                                            class="btn btn-edit" 
                                            onclick="openEditModal(<?= htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') ?>)"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            class="btn btn-password" 
                                            onclick="openPasswordModal(<?= $user['idUser'] ?>)"
                                        >
                                            Change Password
                                        </button>
                                        <form method="POST" action="" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="idUser" value="<?= htmlspecialchars($user['idUser'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-delete" <?= $user['idUser'] == $_SESSION['idUser'] ? 'disabled' : '' ?>>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit User</h2>
        <form method="POST" action="" class="user-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="editIdUser" name="idUser">

            <div class="form-row">
                <div class="form-group">
                    <label for="editNombre">First Name: <span class="required">*</span></label>
                    <input type="text" id="editNombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="editApellidos">Last Name: <span class="required">*</span></label>
                    <input type="text" id="editApellidos" name="apellidos" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editEmail">Email: <span class="required">*</span></label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editTelefono">Phone: <span class="required">*</span></label>
                    <input type="tel" id="editTelefono" name="telefono" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editFechaNacimiento">Birth Date:</label>
                    <input type="date" id="editFechaNacimiento" name="fecha_nacimiento" required>
                </div>
                <div class="form-group">
                    <label for="editSexo">Gender:</label>
                    <select id="editSexo" name="sexo">
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editRol">Role: <span class="required">*</span></label>
                    <select id="editRol" name="rol" required>
                        <option value="user">User</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closePasswordModal()">&times;</span>
        <h2>Change Password</h2>
        <form method="POST" action="" class="user-form">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" id="passwordUserId" name="idUser">

            <div class="form-group">
                <label for="newPassword">New Password: <span class="required">*</span></label>
                <input type="password" id="newPassword" name="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password: <span class="required">*</span></label>
                <input type="password" id="confirmPassword" name="confirm_password" required>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Change Password</button>
                <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('editIdUser').value = user.idUser;
    document.getElementById('editNombre').value = user.nombre;
    document.getElementById('editApellidos').value = user.apellidos;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editTelefono').value = user.telefono || '';
    document.getElementById('editFechaNacimiento').value = user.fecha_nacimiento || '';
    document.getElementById('editSexo').value = user.sexo || '';
    document.getElementById('editRol').value = user.rol;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openPasswordModal(userId) {
    document.getElementById('passwordUserId').value = userId;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('passwordModal').style.display = 'block';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    const passwordModal = document.getElementById('passwordModal');
    
    if (event.target === editModal) {
        editModal.style.display = 'none';
    }
    if (event.target === passwordModal) {
        passwordModal.style.display = 'none';
    }
}
</script>

<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>

