<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';

/**
 * Renders static public pages that do not need a dedicated domain controller.
 */
final class PageController extends Controller
{
    public function home(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $successMessage = flash('success') ?: ($_SESSION['flash_success'] ?? '');
        $errorMessage = flash('error');
        unset($_SESSION['flash_success']);

        $this->view('public/home', [
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
        ]);
    }

    public function contact(): void
    {
        $this->view('public/contacto');
    }

    public function quote(): void
    {
        $this->view('public/presupuesto');
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->view('public/not-found');
    }
}
