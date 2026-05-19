<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/UserModel.php';

/**
 * Handles admin dashboard and user-management actions.
 */
final class AdminController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireAdmin();
        $this->view('admin/control-panel');
    }

    /**
     * Lists users for administration, falling back to an empty list if DB loading fails.
     */
    public function users(array $errors = [], array $old = []): void
    {
        Auth::requireAdmin();
        try {
            $usersData = (new UserModel())->all();
        } catch (Throwable $exception) {
            $usersData = [];
            $errors[] = $this->databaseUnavailableMessage('users administration');
        }

        $this->view('admin/usuarios', [
            'errors' => $errors,
            'old' => $old,
            'success' => flash('success'),
            'usersData' => $usersData,
        ]);
    }

    /**
     * Creates user/profile/login records from the admin panel.
     */
    public function createUser(): void
    {
        Auth::requireAdmin();
        $data = $this->userData();
        $password = $_POST['password'] ?? '';
        $errors = $this->validateUserData($data);
        $errors = array_merge($errors, $this->validatePassword($password));

        $users = new UserModel();
        if ($data['email'] !== '' && $users->emailExists($data['email'])) {
            $errors[] = 'Email already exists.';
        }
        if ($data['usuario'] !== '' && $users->usernameExists($data['usuario'])) {
            $errors[] = 'Username already exists.';
        }

        if ($errors !== []) {
            $this->users($errors, $data);
            return;
        }

        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        $users->create($data);
        flash('success', 'User created successfully!');
        $this->redirect('index.php?page=admin-users');
    }

    /**
     * Updates profile fields and role for an existing user.
     */
    public function updateUser(): void
    {
        Auth::requireAdmin();
        $idUser = (int) ($_POST['idUser'] ?? 0);
        $data = $this->userData(false);
        $errors = $this->validateUserData($data, false);

        if ($idUser <= 0) {
            $errors[] = 'User ID is missing.';
        }

        $users = new UserModel();
        if ($data['email'] !== '' && $users->emailExists($data['email'], $idUser)) {
            $errors[] = 'Email is already used by another user.';
        }

        if ($errors !== []) {
            $this->users($errors);
            return;
        }

        $users->updateUser($idUser, $data);
        if ($idUser === Auth::id()) {
            $_SESSION['rol'] = $data['rol'];
        }
        flash('success', 'User updated successfully!');
        $this->redirect('index.php?page=admin-users');
    }

    /**
     * Lets an admin reset another user's password without exposing the old hash.
     */
    public function changePassword(): void
    {
        Auth::requireAdmin();
        $idUser = (int) ($_POST['idUser'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];

        if ($idUser <= 0) {
            $errors[] = 'Please enter a new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        } else {
            $errors = array_merge($errors, $this->validatePassword($newPassword, 'New password'));
        }

        if ($errors !== []) {
            $this->users($errors);
            return;
        }

        (new UserModel())->updatePassword($idUser, password_hash($newPassword, PASSWORD_DEFAULT));
        flash('success', 'Password changed successfully!');
        $this->redirect('index.php?page=admin-users');
    }

    /**
     * Deletes a user while preventing admins from deleting themselves.
     */
    public function deleteUser(): void
    {
        Auth::requireAdmin();
        $idUser = (int) ($_POST['idUser'] ?? 0);
        if ($idUser <= 0) {
            $this->users(['User ID is missing.']);
            return;
        }
        if ($idUser === Auth::id()) {
            $this->users(['You cannot delete your own account.']);
            return;
        }
        (new UserModel())->delete($idUser);
        flash('success', 'User deleted successfully!');
        $this->redirect('index.php?page=admin-users');
    }

    /**
     * Normalizes POST data from create and edit forms.
     */
    private function userData(bool $includeUsername = true): array
    {
        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'sexo' => trim($_POST['sexo'] ?? ''),
            'rol' => $_POST['rol'] ?? 'user',
        ];

        if ($includeUsername) {
            $data['usuario'] = trim($_POST['usuario'] ?? '');
        }

        return $data;
    }

    /**
     * Keeps admin user form validation in one place for create/update paths.
     */
    private function validateUserData(array $data, bool $includeUsername = true): array
    {
        $errors = [];
        foreach (['nombre', 'apellidos', 'email', 'telefono', 'fecha_nacimiento'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[] = 'Please fill in all required fields.';
                break;
            }
        }
        if ($includeUsername && ($data['usuario'] ?? '') === '') {
            $errors[] = 'Username is required.';
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }
        if ($data['fecha_nacimiento'] !== '' && !is_valid_date($data['fecha_nacimiento'])) {
            $errors[] = 'Birth date must be valid.';
        }
        if (!in_array($data['rol'], ['admin', 'user'], true)) {
            $errors[] = 'Role is invalid.';
        }

        return $errors;
    }

    /**
     * Applies the password rules shown in the user-management form.
     */
    private function validatePassword(string $password, string $label = 'Password'): array
    {
        $errors = [];
        if ($password === '') {
            $errors[] = $label . ' is required.';
            return $errors;
        }
        if (strlen($password) < 8) {
            $errors[] = $label . ' must be at least 8 characters long.';
        }
        if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)) {
            $errors[] = $label . ' must contain uppercase and lowercase letters.';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = $label . ' must contain at least one number.';
        }
        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = $label . ' must contain at least one special character (!@#$%^&*).';
        }

        return $errors;
    }
}
