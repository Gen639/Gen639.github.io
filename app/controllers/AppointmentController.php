<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/AppointmentModel.php';

/**
 * Handles appointment scheduling for logged-in users.
 */
final class AppointmentController extends Controller
{
    /**
     * Lists the current user's appointments with DB-outage fallback messaging.
     */
    public function userIndex(array $errors = []): void
    {
        Auth::requireLogin();
        try {
            $appointments = (new AppointmentModel())->allForUser((int) Auth::id());
        } catch (Throwable $exception) {
            $appointments = [];
            $errors[] = $this->databaseUnavailableMessage('appointments');
        }

        $this->view('user/citaciones', [
            'errors' => $errors,
            'success' => flash('success'),
            'citasData' => $appointments,
        ]);
    }

    /**
     * Creates a future appointment for the logged-in user.
     */
    public function create(): void
    {
        Auth::requireLogin();
        $fecha = $_POST['fecha_cita'] ?? '';
        $motivo = trim($_POST['motivo_cita'] ?? '');
        $errors = $this->validate($fecha, $motivo);

        if ($errors !== []) {
            $this->userIndex($errors);
            return;
        }

        (new AppointmentModel())->create((int) Auth::id(), $fecha, $motivo);
        flash('success', 'Appointment scheduled successfully!');
        $this->redirect('index.php?page=appointments');
    }

    /**
     * Updates only appointments owned by the logged-in user.
     */
    public function update(): void
    {
        Auth::requireLogin();
        $idCita = (int) ($_POST['idCita'] ?? 0);
        $fecha = $_POST['fecha_cita'] ?? '';
        $motivo = trim($_POST['motivo_cita'] ?? '');
        $model = new AppointmentModel();
        $errors = $this->validate($fecha, $motivo);

        if ($idCita <= 0 || !$model->findForUser($idCita, (int) Auth::id())) {
            $errors[] = 'Appointment not found or you do not have permission to modify it.';
        }

        if ($errors !== []) {
            $this->userIndex($errors);
            return;
        }

        $model->updateForUser($idCita, (int) Auth::id(), $fecha, $motivo);
        flash('success', 'Appointment updated successfully!');
        $this->redirect('index.php?page=appointments');
    }

    /**
     * Deletes a future appointment after ownership and date checks.
     */
    public function delete(): void
    {
        Auth::requireLogin();
        $idCita = (int) ($_POST['idCita'] ?? 0);
        $model = new AppointmentModel();
        $appointment = $idCita > 0 ? $model->findForUser($idCita, (int) Auth::id()) : null;

        if (!$appointment) {
            $this->userIndex(['Appointment not found or you do not have permission to delete it.']);
            return;
        }

        if (new DateTime($appointment['fecha_cita']) < new DateTime('today')) {
            $this->userIndex(['Cannot delete appointments that have already been completed.']);
            return;
        }

        $model->deleteForUser($idCita, (int) Auth::id());
        flash('success', 'Appointment deleted successfully!');
        $this->redirect('index.php?page=appointments');
    }

    /**
     * Validates the date and reason shared by create and update forms.
     */
    private function validate(string $fecha, string $motivo): array
    {
        $errors = [];
        if (!is_valid_date($fecha)) {
            $errors[] = 'Please select a valid date.';
        } elseif (new DateTime($fecha) < new DateTime('today')) {
            $errors[] = 'The appointment date cannot be in the past.';
        }
        if ($motivo === '') {
            $errors[] = 'Please enter the reason for the appointment.';
        }

        return $errors;
    }
}
