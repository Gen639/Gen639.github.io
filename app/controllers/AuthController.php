<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';

/**
 * Handles registration, login, remember-me email storage, and logout.
 */
final class AuthController extends Controller
{
    /**
     * Renders the login form, redirecting already authenticated users.
     */
    public function showLogin(array $errors = [], array $old = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['idUser'])) {
            if (($_SESSION['rol'] ?? '') === 'admin') {
                $this->redirect('index.php');
            }
            $this->redirect('index.php?page=profile');
        }

        $this->view('public/login', [
            'errors' => $errors,
            'old' => $old,
            'rememberedEmail' => $_COOKIE['remember_user'] ?? '',
            'successMessage' => flash('success'),
        ]);
    }

    /**
     * Validates credentials, seeds the session, and sends users to the home page.
     */
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $errors = [];

        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if ($password === '') {
            $errors[] = 'Password is required.';
        }

        if ($errors !== []) {
            $this->showLogin($errors, ['email' => $email]);
            return;
        }

        $user = (new UserModel())->findLoginByEmail($email);

        if (!$user || !password_verify($password, (string) $user['password'])) {
            $this->showLogin(['Invalid email or password.'], ['email' => $email]);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['idUser'] = (int) $user['idUser'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellidos'];
        $_SESSION['user_email'] = $user['email'];

        if ($remember) {
            setcookie('remember_user', $email, time() + (30 * 24 * 60 * 60), '/');
        }

        $this->view('public/login', [
            'errors' => [],
            'old' => [],
            'rememberedEmail' => '',
            'successMessage' => 'Login successful. Welcome back!',
            'redirectUrl' => 'index.php',
            'redirectDelay' => 2500,
        ]);
    }

    /**
     * Renders the registration form with safe defaults for sticky fields.
     */
    public function showRegister(array $errors = [], array $old = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['idUser'])) {
            if (($_SESSION['rol'] ?? '') === 'admin') {
                $this->redirect('index.php');
            }
            $this->redirect('index.php?page=profile');
        }

        $defaults = [
            'nombre' => '',
            'apellidos' => '',
            'telefono' => '',
            'fecha_nacimiento' => '',
            'direccion' => '',
            'sexo' => '',
            'email' => '',
            'usuario' => '',
        ];

        $this->view('public/register', [
            'errors' => $errors,
            'old' => array_merge($defaults, $old),
            'successMessage' => flash('success'),
        ]);
    }

    /**
     * Creates a normal user account after form and uniqueness validation.
     */
    public function register(): void
    {
        $old = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'sexo' => trim($_POST['sexo'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'usuario' => trim($_POST['usuario'] ?? ''),
        ];
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm-password'] ?? '';
        $errors = $this->validateUserData($old);

        if ($password === '' || $confirmPassword === '') {
            $errors[] = 'Password and confirm password are required.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        if (!isset($_POST['terms'])) {
            $errors[] = 'You must accept the Terms of Service and Privacy Policy.';
        }

        $users = new UserModel();
        if ($old['email'] !== '' && $users->emailExists($old['email'])) {
            $errors[] = 'The email address is already registered.';
        }
        if ($old['usuario'] !== '' && $users->usernameExists($old['usuario'])) {
            $errors[] = 'The username is already taken.';
        }

        if ($errors !== []) {
            $this->showRegister($errors, $old);
            return;
        }

        $old['password'] = password_hash($password, PASSWORD_DEFAULT);
        $old['rol'] = 'user';
        $users->create($old);
        flash('success', 'Registration successful! You can now log in.');

        $this->redirect('index.php?page=login');
    }

    /**
     * Clears the active session before returning to the home page.
     */
    public function logout(): never
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        $this->redirect('index.php');
    }

    /**
     * Shared registration validation for profile fields.
     */
    private function validateUserData(array $data): array
    {
        $errors = [];
        foreach (['nombre', 'apellidos', 'telefono', 'fecha_nacimiento', 'email', 'usuario'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }
        if (($data['email'] ?? '') !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (($data['fecha_nacimiento'] ?? '') !== '' && !is_valid_date($data['fecha_nacimiento'])) {
            $errors[] = 'Date of birth must be a valid date.';
        }

        return $errors;
    }
}
