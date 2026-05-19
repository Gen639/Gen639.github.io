<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/UserModel.php';

/**
 * Handles appointment administration for selected normal users.
 */
final class AdminAppointmentController extends Controller
{
    /**
     * Renders user selection, selected user details, and that user's appointments.
     */
    public function index(array $errors = []): void
    {
        Auth::requireAdmin();
        $selectedIdUser = (int) ($_GET['idUser'] ?? $_POST['idUser'] ?? 0);
        try {
            $users = new UserModel();
            $usersData = $users->normalUsers();
            $selectedUserDetails = $selectedIdUser > 0 ? $users->findById($selectedIdUser) : null;
            $citasData = $selectedIdUser > 0 ? (new AppointmentModel())->allForUser($selectedIdUser) : [];
        } catch (Throwable $exception) {
            $usersData = [];
            $selectedUserDetails = null;
            $citasData = [];
            $errors[] = $this->databaseUnavailableMessage('appointments administration');
        }

        $this->view('admin/citas', [
            'errors' => $errors,
            'success' => flash('success'),
            'selectedIdUser' => $selectedIdUser,
            'usersData' => $usersData,
            'selectedUserDetails' => $selectedUserDetails,
            'citasData' => $citasData,
        ]);
    }

    /**
     * Creates an appointment for a selected user.
     */
    public function create(): void
    {
        Auth::requireAdmin();
        $idUser = (int) ($_POST['idUser'] ?? 0);
        $fecha = $_POST['fecha_cita'] ?? '';
        $motivo = trim($_POST['motivo_cita'] ?? '');
        $errors = $this->validate($idUser, $fecha, $motivo);

        if ($errors !== []) {
            $_GET['idUser'] = (string) $idUser;
            $this->index($errors);
            return;
        }

        (new AppointmentModel())->create($idUser, $fecha, $motivo);
        flash('success', 'Appointment created successfully!');
        $this->redirect('index.php?page=admin-appointments&idUser=' . $idUser);
    }

    /**
     * Updates an appointment for the selected user.
     */
    public function update(): void
    {
        Auth::requireAdmin();
        $idUser = (int) ($_POST['idUser'] ?? 0);
        $idCita = (int) ($_POST['idCita'] ?? 0);
        $fecha = $_POST['fecha_cita'] ?? '';
        $motivo = trim($_POST['motivo_cita'] ?? '');
        $errors = $this->validate($idUser, $fecha, $motivo);
        $model = new AppointmentModel();

        if ($idCita <= 0 || !$model->findForUser($idCita, $idUser)) {
            $errors[] = 'Appointment not found.';
        }

        if ($errors !== []) {
            $_GET['idUser'] = (string) $idUser;
            $this->index($errors);
            return;
        }

        $model->updateForUser($idCita, $idUser, $fecha, $motivo);
        flash('success', 'Appointment updated successfully!');
        $this->redirect('index.php?page=admin-appointments&idUser=' . $idUser);
    }

    /**
     * Deletes an appointment for the selected user.
     */
    public function delete(): void
    {
        Auth::requireAdmin();
        $idUser = (int) ($_POST['idUser'] ?? 0);
        $idCita = (int) ($_POST['idCita'] ?? 0);
        if ($idUser <= 0 || $idCita <= 0 || !(new AppointmentModel())->deleteForUser($idCita, $idUser)) {
            $_GET['idUser'] = (string) $idUser;
            $this->index(['Appointment not found.']);
            return;
        }
        flash('success', 'Appointment deleted successfully!');
        $this->redirect('index.php?page=admin-appointments&idUser=' . $idUser);
    }

    /**
     * Shared admin appointment validation for create and update actions.
     */
    private function validate(int $idUser, string $fecha, string $motivo): array
    {
        $errors = [];
        if ($idUser <= 0) {
            $errors[] = 'Please select a user.';
        }
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
