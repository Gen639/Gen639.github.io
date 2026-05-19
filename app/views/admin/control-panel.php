<?php
$activePage = 'control-panel';
$pageTitle = 'Control Panel | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/usuarios-administracion.css" />';
// Simple admin landing page that links to each management section.
include root_path('includes/header.php');
?>
<main class="usuarios-administracion-page">
  <div class="container">
    <h1>Admin Control Panel</h1>
    <section class="users-list">
      <div class="table-responsive">
        <table class="users-table">
          <tbody>
            <tr><td><a href="index.php?page=admin-users">Manage Users</a></td></tr>
            <tr><td><a href="index.php?page=admin-appointments">Manage Appointments</a></td></tr>
            <tr><td><a href="index.php?page=admin-gallery">Manage Gallery</a></td></tr>
            <tr><td><a href="index.php?page=admin-news">Manage News</a></td></tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</main>
<?php
$footerExtra = '';
include root_path('includes/footer.php');
?>
