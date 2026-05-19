<?php
$activePage = 'schedule Consultation';
$pageTitle = 'Schedule Consultation | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/citaciones.css" />';
// User appointment view. Controller enforces ownership before updates/deletes.
include root_path('includes/header.php');
?>
<main class="citaciones-page">
  <div class="container">
    <h1>Schedule a Consultation</h1>

    <?php if (!empty($errors)) : ?>
      <div class="form-messages error"><ul><?php foreach ($errors as $error) : ?><li><?= e($error) ?></li><?php endforeach ?></ul></div>
    <?php endif ?>

    <?php if (!empty($success)) : ?>
      <div class="form-messages success"><ul><li><?= e($success) ?></li></ul></div>
    <?php endif ?>

    <section class="appointment-request">
      <h2>Request a New Appointment</h2>
      <form method="POST" action="index.php?page=appointment-create" class="appointment-form">
        <div class="form-group">
          <label for="fecha_cita">Appointment Date:</label>
          <input type="date" id="fecha_cita" name="fecha_cita" required min="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label for="motivo_cita">Reason for Appointment:</label>
          <textarea id="motivo_cita" name="motivo_cita" rows="4" placeholder="Describe what you need (e.g., jingle consultation, audio production, music advice...)" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Schedule Appointment</button>
      </form>
    </section>

    <section class="my-appointments">
      <h2>Your Scheduled Appointments</h2>
      <?php if (empty($citasData)) : ?>
        <p class="no-appointments">You have no scheduled appointments yet.</p>
      <?php else : ?>
        <div class="appointments-list">
          <?php foreach ($citasData as $cita) : ?>
            <?php
            $appointmentDate = new DateTime($cita['fecha_cita']);
            $today = new DateTime('today');
            // Dates before today are treated as completed and become read-only.
            $isFuture = $appointmentDate >= $today;
            $dateFormattedLong = $appointmentDate->format('F d, Y');
            ?>
            <div class="appointment-card <?= $isFuture ? 'future' : 'past' ?>">
              <div class="appointment-header">
                <h3><?= e($dateFormattedLong) ?></h3>
                <span class="appointment-status <?= $isFuture ? 'status-upcoming' : 'status-completed' ?>"><?= $isFuture ? 'Upcoming' : 'Completed' ?></span>
              </div>
              <div class="appointment-content">
                <p><strong>Reason:</strong></p>
                <p><?= e($cita['motivo_cita']) ?></p>
              </div>
              <?php if ($isFuture) : ?>
                <div class="appointment-actions">
                  <button class="btn btn-edit" onclick="openEditModal(<?= e(json_encode($cita)) ?>)">Edit</button>
                  <form method="POST" action="index.php?page=appointment-delete" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                    <input type="hidden" name="idCita" value="<?= e($cita['idCita']) ?>">
                    <button type="submit" class="btn btn-delete">Delete</button>
                  </form>
                </div>
              <?php else : ?>
                <div class="appointment-actions"><p class="info-text">This appointment has already been completed and cannot be modified.</p></div>
              <?php endif ?>
            </div>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </section>
  </div>
</main>

<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeEditModal()">&times;</span>
    <h2>Edit Appointment</h2>
    <form method="POST" action="index.php?page=appointment-update" class="appointment-form">
      <input type="hidden" id="editIdCita" name="idCita">
      <div class="form-group">
        <label for="editFechaCita">Appointment Date:</label>
        <input type="date" id="editFechaCita" name="fecha_cita" required min="<?= date('Y-m-d') ?>">
      </div>
      <div class="form-group">
        <label for="editMotivoCita">Reason for Appointment:</label>
        <textarea id="editMotivoCita" name="motivo_cita" rows="4" required></textarea>
      </div>
      <div class="modal-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
// Seeds the edit modal from the appointment card selected by the user.
function openEditModal(appointment) {
    document.getElementById('editIdCita').value = appointment.idCita;
    document.getElementById('editFechaCita').value = appointment.fecha_cita;
    document.getElementById('editMotivoCita').value = appointment.motivo_cita;
    document.getElementById('editModal').style.display = 'block';
}
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) modal.style.display = 'none';
}
</script>
<?php
$footerExtra = '';
include root_path('includes/footer.php');
?>
