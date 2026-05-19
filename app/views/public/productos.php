<?php
$activePage = 'gallery';
$pageTitle = 'Gallery | JingleWorks';
$pageHeadExtras = '<link href="https://cdn.jsdelivr.net/npm/lightbox2@2/dist/css/lightbox.min.css" rel="stylesheet" /><link rel="stylesheet" href="css/productos.css" />';
// Public gallery view. Data is provided by GalleryController::publicIndex().
include root_path('includes/header.php');
?>
<main class="gallery-page">
  <h1>Gallery</h1>
  <?php if (!empty($errors)) : ?><div class="form-messages error"><ul><?php foreach ($errors as $error) : ?><li><?= e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
  <?php if (empty($posts)) : ?>
    <div class="no-news"><p>No posts available at the moment.</p></div>
  <?php else : ?>
    <div class="gallery-intro"><p>Browse a selection of our jingle styles, audio identities, and sound designs.</p></div>
    <div class="project-gallery">
      <?php foreach ($posts as $post) : ?>
        <?php $tags = json_decode($post['tags'] ?? '[]', true) ?? []; ?>
        <div class="project-card" data-project>
          <div class="project-content">
            <h3><?= e($post['name']) ?></h3>
            <p class="short-desc"><?= e($post['summary'] ?? '') ?></p>
            <div class="genre-tags">
              <?php foreach ($tags as $tag) : ?>
                <?php if (trim((string) $tag) !== '') : ?><span><?= e($tag) ?></span><?php endif ?>
              <?php endforeach ?>
            </div>
            <button class="discover-btn" type="button"><span class="btn-show">Show more</span><span class="btn-hide">Hide</span></button>
            <div class="project-expanded-extra">
              <p><?= nl2br(e($post['details'] ?? '')) ?></p>
              <div class="audio-player" data-audio="<?= e('audio/' . $post['audio_file']) ?>">
                <div class="play-button">&#9654;</div><div class="progress-bar"><div class="progress-fill"></div></div>
              </div>
            </div>
          </div>
          <div class="project-image">
              <a href="<?= e('images/gallery/' . $post['image']) ?>" data-lightbox="gallery" data-title="<?= e($post['name']) ?>">
                <img src="<?= e('images/gallery/' . $post['image']) ?>" alt="<?= e($post['name']) ?>" />
            </a>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  <?php endif ?>
</main>
<?php
$footerExtra = '<script src="js/productos.js"></script><script src="https://cdn.jsdelivr.net/npm/lightbox2@2/dist/js/lightbox-plus-jquery.min.js"></script>';
include root_path('includes/footer.php');
?>
