<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/UserModel.php';

/**
 * Handles authenticated user profile display and self-service account edits.
 */
final class UserController extends Controller
{
    /**
     * Shows the current user's profile, with a graceful message if the DB is down.
     */
    public function profile(array $errors = []): void
    {
        Auth::requireLogin();

        try {
            $userData = (new UserModel())->findById((int) Auth::id());
        } catch (Throwable $exception) {
            $userData = null;
            $errors[] = $this->databaseUnavailableMessage('profile');
        }

        $this->view('user/profile', [
            'errors' => $errors,
            'success' => flash('success'),
            'userData' => $userData,
        ]);
    }

    /**
     * Updates personal profile data without touching password or role.
     */
    public function updateProfile(): void
    {
        Auth::requireLogin();

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'sexo' => trim($_POST['sexo'] ?? ''),
        ];
        $errors = [];

        foreach (['nombre', 'apellidos', 'email', 'telefono', 'fecha_nacimiento'] as $field) {
            if ($data[$field] === '') {
                $errors[] = 'Please fill in all required fields.';
                break;
            }
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($data['fecha_nacimiento'] !== '' && !is_valid_date($data['fecha_nacimiento'])) {
            $errors[] = 'Date of birth must be a valid date.';
        }

        $users = new UserModel();
        if ($data['email'] !== '' && $users->emailExists($data['email'], (int) Auth::id())) {
            $errors[] = 'That email address is already in use.';
        }

        if ($errors !== []) {
            $this->profile($errors);
            return;
        }

        $users->updateProfile((int) Auth::id(), $data);
        $_SESSION['user_email'] = $data['email'];
        $_SESSION['user_name'] = $data['nombre'] . ' ' . $data['apellidos'];
        flash('success', 'Profile updated successfully!');

        $this->redirect('index.php?page=profile');
    }

    /**
     * Changes the logged-in user's password after verifying the current password.
     */
    public function changePassword(): void
    {
        Auth::requireLogin();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $errors[] = 'Current password, new password, and confirmation are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        }

        $users = new UserModel();
        if ($errors === []) {
            $login = $users->findLoginById((int) Auth::id());
            if (!$login || !password_verify($currentPassword, (string) $login['password'])) {
                $errors[] = 'Current password is incorrect.';
            }
        }

        if ($errors !== []) {
            $this->profile($errors);
            return;
        }

        $users->updatePassword((int) Auth::id(), password_hash($newPassword, PASSWORD_DEFAULT));
        flash('success', 'Password changed successfully!');
        $this->redirect('index.php?page=profile');
    }
}
