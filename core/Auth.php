<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Small session-based authentication helper used by controllers and views.
 */
final class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['idUser']);
    }

    public static function id(): ?int
    {
        self::start();
        return isset($_SESSION['idUser']) ? (int) $_SESSION['idUser'] : null;
    }

    public static function role(): ?string
    {
        self::start();
        return $_SESSION['rol'] ?? null;
    }

    /**
     * Guards pages that require any authenticated user.
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    /**
     * Guards administrative pages and actions.
     */
    public static function requireAdmin(): void
    {
        if (!self::check() || self::role() !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }
    }
}
