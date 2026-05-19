<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Central request router.
 */
final class Router
{
    private array $routes = [
        'home' => ['PageController', 'home'],
        'contact' => ['PageController', 'contact'],
        'quote' => ['PageController', 'quote'],
        'login' => ['AuthController', 'showLogin'],
        'login-submit' => ['AuthController', 'login'],
        'register' => ['AuthController', 'showRegister'],
        'register-submit' => ['AuthController', 'register'],
        'logout' => ['AuthController', 'logout'],
        'gallery' => ['GalleryController', 'publicIndex'],
        'news' => ['NewsController', 'publicIndex'],
        'profile' => ['UserController', 'profile'],
        'profile-update' => ['UserController', 'updateProfile'],
        'profile-password' => ['UserController', 'changePassword'],
        'appointments' => ['AppointmentController', 'userIndex'],
        'appointment-create' => ['AppointmentController', 'create'],
        'appointment-update' => ['AppointmentController', 'update'],
        'appointment-delete' => ['AppointmentController', 'delete'],
        'admin' => ['AdminController', 'dashboard'],
        'admin-users' => ['AdminController', 'users'],
        'admin-user-create' => ['AdminController', 'createUser'],
        'admin-user-update' => ['AdminController', 'updateUser'],
        'admin-user-delete' => ['AdminController', 'deleteUser'],
        'admin-user-password' => ['AdminController', 'changePassword'],
        'admin-appointments' => ['AdminAppointmentController', 'index'],
        'admin-appointment-create' => ['AdminAppointmentController', 'create'],
        'admin-appointment-update' => ['AdminAppointmentController', 'update'],
        'admin-appointment-delete' => ['AdminAppointmentController', 'delete'],
        'admin-gallery' => ['GalleryController', 'adminIndex'],
        'gallery-create' => ['GalleryController', 'create'],
        'gallery-update' => ['GalleryController', 'update'],
        'gallery-delete' => ['GalleryController', 'delete'],
        'admin-news' => ['NewsController', 'adminIndex'],
        'news-create' => ['NewsController', 'create'],
        'news-update' => ['NewsController', 'update'],
        'news-delete' => ['NewsController', 'delete'],
    ];

    public function dispatch(): void
    {
        try {
            $page = (string) ($_GET['page'] ?? 'home');
            [$controller, $method] = $this->routes[$page] ?? ['PageController', 'notFound'];
            $this->call($controller, $method);
        } catch (Throwable $exception) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['flash']['error'] = $exception instanceof mysqli_sql_exception
                ? 'This section cannot be loaded because the database connection is unavailable.'
                : 'Something went wrong. Please try again.';
            $this->redirectHome();
        }
    }

    private function call(string $controller, string $method): void
    {
        require_once root_path('app/controllers/' . $controller . '.php');
        (new $controller())->$method();
    }

    private function redirectHome(): never
    {
        header('Location: index.php');
        exit;
    }
}
