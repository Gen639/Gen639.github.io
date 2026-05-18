<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$prefix = $prefix ?? '';
$active = $active ?? ($activePage ?? '');
$pageTitle = $pageTitle ?? 'JingleWorks';
$pageHeadExtras = $pageHeadExtras ?? '';
$bodyClass = $bodyClass ?? '';

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
    <link rel="stylesheet" href="<?= $prefix ?>css/index.css" />
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
        <?= navLink($prefix . 'index.php', 'Home', 'home', $active) ?>
        <?php if (!$isLoggedIn || $userRole !== 'admin'): ?>
          <?= navLink($prefix . 'views/productos.php', 'Gallery', 'gallery', $active) ?>
        <?php endif; ?>
        <?= navLink($prefix . 'views/noticias.php', 'News', 'noticias', $active) ?>
        <?php if (!$isLoggedIn): ?>
          <?= navLink($prefix . 'views/login.php', 'Login', 'login', $active) ?>
          <?= navLink($prefix . 'views/register.php', 'Register', 'register', $active) ?>

        <?php else: ?>
          <?php if($userRole === "user"): ?>
            <?= navLink($prefix . 'views/citaciones.php', 'Schedule Consultation', 'schedule Consultation', $active) ?>
          <?php else: ?>
            <?= navLink($prefix . 'views/citas-administracion.php', 'Manage Meetings', 'citas-administracion', $active) ?>
            <?= navLink($prefix . 'views/galeria-administracion.php', 'Manage Gallery', 'galeria-administracion', $active) ?>
            <?= navLink($prefix . 'views/usuarios-administracion.php', 'Manage Users', 'usuarios-administracion', $active) ?>
            <?= navLink($prefix . 'views/noticias-administracion.php', 'Manage News', 'noticias-administracion', $active) ?>

          <?php endif; ?>
          <?= navLink($prefix . 'views/profile.php', 'Profile', 'profile', $active) ?>
          <li><a href="<?= $prefix ?>views/logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </nav>
