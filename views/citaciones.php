<?php
session_start();

$activePage = 'schedule Consultation';
$prefix = '../';
$pageTitle = 'Schedule Consultation | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/citaciones.css" />';

// Verify user is logged in
if (!isset($_SESSION['idUser'])) {
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

        // Create new appointment
        if ($action === 'create') {
            $fecha_cita = $_POST['fecha_cita'] ?? '';
            $motivo_cita = $_POST['motivo_cita'] ?? '';

            // Validate inputs
            if (empty($fecha_cita)) {
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
                    // Insert appointment
                    $stmt = $mysqli->prepare('INSERT INTO citas (idUser, fecha_cita, motivo_cita) VALUES (?, ?, ?)');
                    if ($stmt) {
                        $stmt->bind_param('iss', $_SESSION['idUser'], $fecha_cita, $motivo_cita);
                        if ($stmt->execute()) {
                            $success[] = 'Appointment scheduled successfully!';
                        } else {
                            $errors[] = 'Error scheduling appointment. Please try again.';
                        }
                        $stmt->close();
                    } else {
                        $errors[] = 'Database error occurred.';
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
                // Verify ownership
                $stmt = $mysqli->prepare('SELECT idCita FROM citas WHERE idCita = ? AND idUser = ?');
                if ($stmt) {
                    $stmt->bind_param('ii', $idCita, $_SESSION['idUser']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        // Validate new date is not in the past
                        $appointmentDate = new DateTime($fecha_cita);
                        $today = new DateTime('today');

                        if ($appointmentDate < $today) {
                            $errors[] = 'The appointment date cannot be in the past.';
                        } else {
                            // Update appointment
                            $stmt2 = $mysqli->prepare('UPDATE citas SET fecha_cita = ?, motivo_cita = ? WHERE idCita = ? AND idUser = ?');
                            if ($stmt2) {
                                $stmt2->bind_param('ssii', $fecha_cita, $motivo_cita, $idCita, $_SESSION['idUser']);
                                if ($stmt2->execute()) {
                                    $success[] = 'Appointment updated successfully!';
                                } else {
                                    $errors[] = 'Error updating appointment. Please try again.';
                                }
                                $stmt2->close();
                            }
                        }
                    } else {
                        $errors[] = 'Appointment not found or you do not have permission to modify it.';
                    }
                    $stmt->close();
                }
            }
        }

        // Delete appointment
        elseif ($action === 'delete') {
            $idCita = $_POST['idCita'] ?? '';

            if (empty($idCita)) {
                $errors[] = 'Appointment ID is missing.';
            } else {
                // Verify ownership and check if date is in the future
                $stmt = $mysqli->prepare('SELECT fecha_cita FROM citas WHERE idCita = ? AND idUser = ?');
                if ($stmt) {
                    $stmt->bind_param('ii', $idCita, $_SESSION['idUser']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $cita = $result->fetch_assoc();
                        $appointmentDate = new DateTime($cita['fecha_cita']);
                        $today = new DateTime('today');

                        if ($appointmentDate < $today) {
                            $errors[] = 'Cannot delete appointments that have already been completed.';
                        } else {
                            // Delete appointment
                            $stmt2 = $mysqli->prepare('DELETE FROM citas WHERE idCita = ? AND idUser = ?');
                            if ($stmt2) {
                                $stmt2->bind_param('ii', $idCita, $_SESSION['idUser']);
                                if ($stmt2->execute()) {
                                    $success[] = 'Appointment deleted successfully!';
                                } else {
                                    $errors[] = 'Error deleting appointment. Please try again.';
                                }
                                $stmt2->close();
                            }
                        }
                    } else {
                        $errors[] = 'Appointment not found or you do not have permission to delete it.';
                    }
                    $stmt->close();
                }
            }
        }
    }

    // Fetch user's appointments (ordered by date)
    $citasData = [];
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
        $stmt->bind_param('i', $_SESSION['idUser']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $citasData[] = $row;
        }
        $stmt->close();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<main class="citaciones-page">
    <div class="container">
        <h1>Schedule a Consultation</h1>

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

        <!-- Request New Appointment Section -->
        <section class="appointment-request">
            <h2>Request a New Appointment</h2>
            <form method="POST" action="" class="appointment-form">
                <input type="hidden" name="action" value="create">

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
                        placeholder="Describe what you need (e.g., jingle consultation, audio_file production, music advice...)" 
                        required
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Schedule Appointment</button>
            </form>
        </section>

        <!-- Your Appointments Section -->
        <section class="my-appointments">
            <h2>Your Scheduled Appointments</h2>

            <?php if (empty($citasData)) : ?>
                <p class="no-appointments">You have no scheduled appointments yet.</p>
            <?php else : ?>
                <div class="appointments-list">
                    <?php foreach ($citasData as $cita) : 
                        $appointmentDate = new DateTime($cita['fecha_cita']);
                        $today = new DateTime('today');
                        $isFuture = $appointmentDate >= $today;
                        $dateFormatted = $appointmentDate->format('d/m/Y');
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

                            <?php if ($isFuture) : ?>
                                <div class="appointment-actions">
                                    <!-- Edit Button -->
                                    <button 
                                        class="btn btn-edit" 
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($cita), ENT_QUOTES, 'UTF-8') ?>)"
                                    >
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="idCita" value="<?= htmlspecialchars($cita['idCita'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn btn-delete">Delete</button>
                                    </form>
                                </div>
                            <?php else : ?>
                                <div class="appointment-actions">
                                    <p class="info-text">This appointment has already been completed and cannot be modified.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Appointment</h2>
        <form method="POST" action="" class="appointment-form">
            <input type="hidden" name="action" value="update">
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
function openEditModal(appointment) {
    document.getElementById('editIdCita').value = appointment.idCita;
    document.getElementById('editFechaCita').value = appointment.fecha_cita;
    document.getElementById('editMotivoCita').value = appointment.motivo_cita;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>

