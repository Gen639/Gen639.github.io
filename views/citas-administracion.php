<?php
session_start();

$activePage = 'citas-administracion';
$prefix = '../';
$pageTitle = 'Manage Appointments | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/citas-administracion.css" />';

// Verify admin is logged in
if (!isset($_SESSION['idUser']) || ($_SESSION['rol'] ?? null) !== 'admin') {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = [];
$selectedIdUser = null;
$usersData = [];
$citasData = [];

// Database connection
require __DIR__ . '/../includes/db.php';

if ($dbError !== null) {
    $errors[] = $dbError;
} else {
    // Get selected user from GET or POST
    $selectedIdUser = $_GET['idUser'] ?? $_POST['idUser'] ?? null;

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $selectedIdUser = $_POST['idUser'] ?? null;

        // Create new appointment
        if ($action === 'create') {
            $fecha_cita = $_POST['fecha_cita'] ?? '';
            $motivo_cita = $_POST['motivo_cita'] ?? '';

            // Validate inputs
            if (empty($selectedIdUser)) {
                $errors[] = 'Please select a user.';
            } elseif (empty($fecha_cita)) {
                $errors[] = 'Please select a date.';
            } elseif (empty($motivo_cita)) {
                $errors[] = 'Please enter the reason for the appointment.';
            } else {
                // Validate date is not in the past
                $appointmentDate = new DateTime($fecha_cita);
                $today = new DateTime('today');

                if ($appointmentDate < $today) {
                    $errors[] = 'The appointment date cannot be in the past.';
                } else {
                    // Verify user exists
                    $stmt = $mysqli->prepare('SELECT idUser FROM users_data WHERE idUser = ?');
                    if ($stmt) {
                        $stmt->bind_param('i', $selectedIdUser);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            // Insert appointment
                            $stmt2 = $mysqli->prepare('INSERT INTO citas (idUser, fecha_cita, motivo_cita) VALUES (?, ?, ?)');
                            if ($stmt2) {
                                $stmt2->bind_param('iss', $selectedIdUser, $fecha_cita, $motivo_cita);
                                if ($stmt2->execute()) {
                                    $success[] = 'Appointment created successfully!';
                                } else {
                                    $errors[] = 'Error creating appointment. Please try again.';
                                }
                                $stmt2->close();
                            }
                        } else {
                            $errors[] = 'Selected user does not exist.';
                        }
                        $stmt->close();
                    }
                }
            }
        }

        // Update appointment
        elseif ($action === 'update') {
            $idCita = $_POST['idCita'] ?? '';
            $fecha_cita = $_POST['fecha_cita'] ?? '';
            $motivo_cita = $_POST['motivo_cita'] ?? '';

            if (empty($idCita) || empty($fecha_cita) || empty($motivo_cita)) {
                $errors[] = 'Missing required fields.';
            } else {
                // Validate new date is not in the past
                $appointmentDate = new DateTime($fecha_cita);
                $today = new DateTime('today');

                if ($appointmentDate < $today) {
                    $errors[] = 'The appointment date cannot be in the past.';
                } else {
                    // Update appointment
                    $stmt = $mysqli->prepare('UPDATE citas SET fecha_cita = ?, motivo_cita = ? WHERE idCita = ? AND idUser = ?');
                    if ($stmt) {
                        $stmt->bind_param('ssii', $fecha_cita, $motivo_cita, $idCita, $selectedIdUser);
                        if ($stmt->execute()) {
                            $success[] = 'Appointment updated successfully!';
                        } else {
                            $errors[] = 'Error updating appointment. Please try again.';
                        }
                        $stmt->close();
                    }
                }
            }
        }

        // Delete appointment
        elseif ($action === 'delete') {
            $idCita = $_POST['idCita'] ?? '';

            if (empty($idCita)) {
                $errors[] = 'Appointment ID is missing.';
            } else {
                // Delete appointment
                $stmt = $mysqli->prepare('DELETE FROM citas WHERE idCita = ? AND idUser = ?');
                if ($stmt) {
                    $stmt->bind_param('ii', $idCita, $selectedIdUser);
                    if ($stmt->execute()) {
                        $success[] = 'Appointment deleted successfully!';
                    } else {
                        $errors[] = 'Error deleting appointment. Please try again.';
                    }
                    $stmt->close();
                }
            }
        }
    }

    // Fetch all users (excluding admin accounts)
    $stmt = $mysqli->prepare('
        SELECT
            ud.idUser,
            ud.nombre,
            ud.apellidos,
            ud.email
        FROM users_data ud
        JOIN users_login ul ON ud.idUser = ul.idUser
        WHERE ul.rol = "user"
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

    // Fetch appointments for selected user (if any)
    if (!empty($selectedIdUser)) {
        $stmt = $mysqli->prepare('
            SELECT
                idCita,
                fecha_cita,
                motivo_cita
            FROM citas
            WHERE idUser = ?
            ORDER BY fecha_cita DESC
        ');
        if ($stmt) {
            $stmt->bind_param('i', $selectedIdUser);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $citasData[] = $row;
            }
            $stmt->close();
        }

        // Fetch selected user details
        $selectedUserDetails = null;
        $stmt = $mysqli->prepare('
            SELECT
                nombre,
                apellidos,
                email,
                telefono
            FROM users_data
            WHERE idUser = ?
        ');
        if ($stmt) {
            $stmt->bind_param('i', $selectedIdUser);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $selectedUserDetails = $result->fetch_assoc();
            }
            $stmt->close();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<main class="citas-administracion-page">
    <div class="container">
        <h1>Manage User Appointments</h1>

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

        <!-- User Selection Section -->
        <section class="user-selection">
            <h2>Select a User</h2>
            <form method="GET" action="" class="user-select-form">
                <div class="form-group">
                    <label for="idUser">User:</label>
                    <select id="idUser" name="idUser" required onchange="this.form.submit()">
                        <option value="">-- Choose a User --</option>
                        <?php foreach ($usersData as $user) : ?>
                            <option 
                                value="<?= htmlspecialchars($user['idUser'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= $selectedIdUser == $user['idUser'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($user['nombre'] . ' ' . $user['apellidos'] . ' (' . $user['email'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </section>

        <?php if (!empty($selectedIdUser) && $selectedUserDetails) : ?>
            <!-- User Details Section -->
            <section class="user-details">
                <h2>User Information</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <strong>Name:</strong> <?= htmlspecialchars($selectedUserDetails['nombre'] . ' ' . $selectedUserDetails['apellidos'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="detail-item">
                        <strong>Email:</strong> <?= htmlspecialchars($selectedUserDetails['email'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="detail-item">
                        <strong>Phone:</strong> <?= htmlspecialchars($selectedUserDetails['telefono'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </section>

            <!-- Create Appointment Section -->
            <section class="create-appointment">
                <h2>Create New Appointment</h2>
                <form method="POST" action="" class="appointment-form">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="idUser" value="<?= htmlspecialchars($selectedIdUser, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group">
                        <label for="fecha_cita">Appointment Date:</label>
                        <input 
                            type="date" 
                            id="fecha_cita" 
                            name="fecha_cita" 
                            required
                            min="<?= date('Y-m-d') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="motivo_cita">Reason for Appointment:</label>
                        <textarea 
                            id="motivo_cita" 
                            name="motivo_cita" 
                            rows="4" 
                            placeholder="Describe the appointment reason..." 
                            required
                        ></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Appointment</button>
                </form>
            </section>

            <!-- User's Appointments Section -->
            <section class="user-appointments">
                <h2>Appointments for This User</h2>

                <?php if (empty($citasData)) : ?>
                    <p class="no-appointments">This user has no scheduled appointments yet.</p>
                <?php else : ?>
                    <div class="appointments-list">
                        <?php foreach ($citasData as $cita) : 
                            $appointmentDate = new DateTime($cita['fecha_cita']);
                            $today = new DateTime('today');
                            $isFuture = $appointmentDate >= $today;
                            $dateFormattedLong = $appointmentDate->format('F d, Y');
                        ?>
                            <div class="appointment-card <?= $isFuture ? 'future' : 'past' ?>">
                                <div class="appointment-header">
                                    <h3><?= htmlspecialchars($dateFormattedLong, ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="appointment-status <?= $isFuture ? 'status-upcoming' : 'status-completed' ?>">
                                        <?= $isFuture ? 'Upcoming' : 'Completed' ?>
                                    </span>
                                </div>

                                <div class="appointment-content">
                                    <p><strong>Reason:</strong></p>
                                    <p><?= htmlspecialchars($cita['motivo_cita'], ENT_QUOTES, 'UTF-8') ?></p>
                                </div>

                                <div class="appointment-actions">
                                    <!-- Edit Button -->
                                    <button
                                        type="button"
                                        class="btn btn-edit js-open-edit-modal"
                                        data-id-cita="<?= htmlspecialchars((string) $cita['idCita'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-fecha-cita="<?= htmlspecialchars($cita['fecha_cita'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-motivo-cita="<?= htmlspecialchars($cita['motivo_cita'], ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="idUser" value="<?= htmlspecialchars($selectedIdUser, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="idCita" value="<?= htmlspecialchars($cita['idCita'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-delete">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
</main>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Appointment</h2>
        <form method="POST" action="" class="appointment-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="idUser" value="<?= htmlspecialchars($selectedIdUser, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="editIdCita" name="idCita">

            <div class="form-group">
                <label for="editFechaCita">Appointment Date:</label>
                <input 
                    type="date" 
                    id="editFechaCita" 
                    name="fecha_cita" 
                    required
                    min="<?= date('Y-m-d') ?>"
                >
            </div>

            <div class="form-group">
                <label for="editMotivoCita">Reason for Appointment:</label>
                <textarea 
                    id="editMotivoCita" 
                    name="motivo_cita" 
                    rows="4" 
                    required
                ></textarea>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const editModal = document.getElementById('editModal');
const editIdCita = document.getElementById('editIdCita');
const editFechaCita = document.getElementById('editFechaCita');
const editMotivoCita = document.getElementById('editMotivoCita');
const editButtons = document.querySelectorAll('.js-open-edit-modal');

function openEditModal(button) {
    editIdCita.value = button.dataset.idCita ?? '';
    editFechaCita.value = button.dataset.fechaCita ?? '';
    editMotivoCita.value = button.dataset.motivoCita ?? '';
    editModal.classList.add('is-open');
    document.body.classList.add('modal-open');
}

function closeEditModal() {
    editModal.classList.remove('is-open');
    document.body.classList.remove('modal-open');
}

editButtons.forEach((button) => {
    button.addEventListener('click', () => openEditModal(button));
});

window.addEventListener('click', (event) => {
    if (event.target === editModal) {
        closeEditModal();
    }
});
</script>

<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>

