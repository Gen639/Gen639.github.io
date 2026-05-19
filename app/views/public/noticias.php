<?php
$activePage = 'noticias';
$pageTitle = 'News | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/noticias.css" />';
// Public news view. Data is provided by NewsController::publicIndex().
include root_path('includes/header.php');
?>
<main class="noticias-page">
  <h1>News</h1>
  <p class="intro">Stay updated with the latest news and announcements from JingleWorks.</p>
  <?php if (!empty($errors)) : ?><div class="form-messages error"><ul><?php foreach ($errors as $error) : ?><li><?= e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
  <?php if (empty($noticiasData)) : ?>
    <div class="no-news"><p>No news available at the moment.</p></div>
  <?php else : ?>
    <div class="noticias-container">
      <?php foreach ($noticiasData as $noticia) : ?>
        <article class="noticia-card" id="noticia-<?= e($noticia['idNoticia']) ?>">
          <?php if (!empty($noticia['imagen'])) : ?>
            <div class="noticia-image">
            <img src="<?= e('images/news/' . $noticia['imagen']) ?>" alt="<?= e($noticia['titulo']) ?>" />
            </div>
          <?php endif ?>
          <div class="noticia-content">
            <h2><?= e($noticia['titulo']) ?></h2>
            <div class="noticia-meta">
              <span class="noticia-fecha"><?= e(date('d/m/Y', strtotime($noticia['fecha']))) ?></span>
              <span class="noticia-autor">By <?= e($noticia['nombre']) ?></span>
            </div>
            <div class="noticia-content"><?= nl2br(e(trim($noticia['texto']))) ?></div>
          </div>
        </article>
      <?php endforeach ?>
    </div>
  <?php endif ?>
</main>
<?php
$footerExtra = '';
include root_path('includes/footer.php');
?>
