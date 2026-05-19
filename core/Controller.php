<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

abstract class Controller
{
    /**
     * Shared wording for sections that cannot query the database.
     */
    protected function databaseUnavailableMessage(string $section): string
    {
        return "The {$section} section cannot be loaded because the database connection is unavailable.";
    }

    /**
     * Loads a PHP view and exposes controller data as local view variables.
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require root_path('app/views/' . $view . '.php');
    }

    /**
     * Sends a browser redirect, resolving relative app paths to the configured base URL.
     */
    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }
}
