<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$active = $active ?? ($activePage ?? '');
$pageTitle = $pageTitle ?? 'JingleWorks';
$pageHeadExtras = $pageHeadExtras ?? '';
$bodyClass = $bodyClass ?? '';

// Builds navigation items and marks the current section as active.
function navLink(string $href, string $text, string $key, string $active): string
{
    $class = $active === $key ? ' class="active"' : '';
    return '<li><a href="' . $href . '"' . $class . '>' . $text . '</a></li>';
}

$isLoggedIn = isset($_SESSION['idUser']);
$userRole = $_SESSION['rol'] ?? null;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="css/index.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
      rel="stylesheet"
    />
    <?= $pageHeadExtras ?>
  </head>
  <body<?php if ($bodyClass) : ?> class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>"<?php endif ?>>
    <nav class="navbar">
      <div class="logo">JingleWorks</div>
      <div class="hamburger" id="hamburger">&#9776;</div>
      <ul class="nav-links" id="navLinks">
        <?= navLink('index.php', 'Home', 'home', $active) ?>
        <?php if (!$isLoggedIn || $userRole !== 'admin'): ?>
          <?= navLink('index.php?page=gallery', 'Gallery', 'gallery', $active) ?>
        <?php endif; ?>
        <?= navLink('index.php?page=news', 'News', 'noticias', $active) ?>
        <?php if (!$isLoggedIn): ?>
          <?= navLink('index.php?page=login', 'Login', 'login', $active) ?>
          <?= navLink('index.php?page=register', 'Register', 'register', $active) ?>

        <?php else: ?>
          <?php if($userRole === "user"): ?>
            <?= navLink('index.php?page=appointments', 'Schedule Consultation', 'schedule Consultation', $active) ?>
          <?php else: ?>
            <?= navLink('index.php?page=admin-appointments', 'Manage Meetings', 'citas-administracion', $active) ?>
            <?= navLink('index.php?page=admin-gallery', 'Manage Gallery', 'galeria-administracion', $active) ?>
            <?= navLink('index.php?page=admin-users', 'Manage Users', 'usuarios-administracion', $active) ?>
            <?= navLink('index.php?page=admin-news', 'Manage News', 'noticias-administracion', $active) ?>

          <?php endif; ?>
          <?= navLink('index.php?page=profile', 'Profile', 'profile', $active) ?>
          <li><a href="index.php?page=logout">Logout</a></li>
        <?php endif; ?>
      </ul>
    </nav>
