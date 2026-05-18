<?php
$activePage = 'gallery';
$prefix = '../';
$pageTitle = 'Gallery | JingleWorks';
$pageHeadExtras = <<<HTML
<link href="https://cdn.jsdelivr.net/npm/lightbox2@2/dist/css/lightbox.min.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/productos.css" />
HTML;

$errors = [];
$posts = [];

require __DIR__ . '/../includes/db.php';

if ($dbError !== null) {
    $errors[] = $dbError;
} else {
    $stmt = $mysqli->prepare('
        SELECT gallery_item_id, name, summary, details, tags, image, audio_file, created_at
        FROM gallery_items
        ORDER BY created_at DESC
    ');

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        $stmt->close();
    } else {
        $errors[] = 'Database query failed.';
    }

    $mysqli->close();
}

include __DIR__ . '/../includes/header.php';
?>

<main class="gallery-page">
  <h1>Gallery</h1>

  <?php if (!empty($errors)) : ?>
    <div class="form-messages error">
      <ul>
        <?php foreach ($errors as $error) : ?>
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php elseif (empty($posts)) : ?>
    <div class="no-news">
      <p>No posts available at the moment.</p>
    </div>
  <?php else: ?>
    <div class="gallery-intro">
      <p>
        Browse a selection of our jingle styles, audio identities, and sound
        designs. Click any image to expand and preview a sample.
      </p>
    </div>

    <div class="project-gallery">
      <?php foreach ($posts as $post) : ?>
        <?php
        $tags = json_decode($post['tags'], true) ?? [];
        $imageFile = __DIR__ . '/../images/gallery/' . $post['image'];
        $imageSize = is_file($imageFile) ? getimagesize($imageFile) : null;
        ?>
        <div class="project-card" data-project>
          <div class="project-content">
            <h3><?= htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="short-desc">
              <?= htmlspecialchars($post['summary'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="genre-tags">
              <?php foreach ($tags as $tag) : ?>
                <?php $tagText = trim((string) $tag); ?>
                <?php if ($tagText === '') : ?>
                  <?php continue; ?>
                <?php endif; ?>
                <span><?= htmlspecialchars($tagText, ENT_QUOTES, 'UTF-8') ?></span>
              <?php endforeach; ?>
            </div>

            <button class="discover-btn" type="button">
              <span class="btn-show">Show more</span>
              <span class="btn-hide">Hide</span>
            </button>

            <div class="project-expanded-extra">
              <p>
                <?= nl2br(htmlspecialchars($post['details'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
              </p>
              <div
                class="audio-player"
                data-audio="../audio/<?= htmlspecialchars($post['audio_file'], ENT_QUOTES, 'UTF-8') ?>"
              >
                <div class="play-button">&#9654;</div>
                <div class="progress-bar">
                  <div class="progress-fill"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="project-image">
            <a
              href="../images/gallery/<?= htmlspecialchars($post['image'], ENT_QUOTES, 'UTF-8') ?>"
              data-lightbox="gallery"
              data-title="<?= htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8') ?>"
            >
              <img
                src="../images/gallery/<?= htmlspecialchars($post['image'], ENT_QUOTES, 'UTF-8') ?>"
                alt="<?= htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8') ?>"
                <?php if ($imageSize) : ?>
                width="<?= (int) $imageSize[0] ?>"
                height="<?= (int) $imageSize[1] ?>"
                <?php endif; ?>
              />
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php
$footerExtra = '<script src="../js/productos.js"></script><script src="https://cdn.jsdelivr.net/npm/lightbox2@2/dist/js/lightbox-plus-jquery.min.js"></script>';
include __DIR__ . '/../includes/footer.php';
?>

