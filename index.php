<?php
$activePage = 'home';
$prefix = '';
$pageTitle = 'JingleWorks | Custom Audio Production';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$latestNews = [];
$newsPreviewLimit = 90;
$successMessage = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

require __DIR__ . '/includes/db.php';

if ($dbError !== null || !$mysqli instanceof mysqli) {
    $errors[] = $dbError ?? 'Database connection failed.';
} else {
    $stmt = $mysqli->prepare('
        SELECT
            idNoticia,
            titulo,
            imagen,
            texto,
            fecha
        FROM noticias
        ORDER BY fecha DESC, idNoticia DESC
        LIMIT 3
    ');

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $latestNews[] = $row;
        }

        $stmt->close();
    } else {
        $errors[] = 'News query failed.';
    }

    $mysqli->close();
}

include __DIR__ . '/includes/header.php';
?>
<main>
      <?php if ($successMessage !== '') : ?>
        <div class="content">
          <div class="form-messages success">
            <ul>
              <li><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></li>
            </ul>
          </div>
        </div>
      <?php endif; ?>

      <section class="fullscreen-carousel">
        <div class="carousel-track">
          <div class="carousel-item">
            <img
              src="https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExc3hkZzIwbDliMXBldHg1bDV6aGJ1dHZhMzYxbHJsem4zaTY3Y2NyMyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/uxtiDOWclM0MBAJZs1/giphy.gif"
              alt="Ambient music"
              width="480"
              height="480"
            />
            <div class="caption">
              <h2>Ambient</h2>
              <p>Perfect for meditation, deep focus, and calm atmospheres.</p>
            </div>
          </div>
          <div class="carousel-item">
            <img
              src="https://media2.giphy.com/media/v1.Y2lkPTc5MGI3NjExdHhrM2dlemcwNXlldXdjeXBnYzhtdWdxamR3MTc5b2x0bzBrbmVzYiZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/XChQFtTCm8e8K9RJKS/giphy.gif"
              alt="Techno music"
              width="500"
              height="500"
            />
            <div class="caption">
              <h2>Techno</h2>
              <p>
                Ideal for clubs, parties, fast-paced content and motion
                graphics.
              </p>
            </div>
          </div>
          <div class="carousel-item">
            <img
              src="https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExcmswbGR3MTV0NDhyNDh2eGV3ZHk5eDJnZjltNDZvNXl4cnFuYzZlaSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/RiJRSLRPt0HfS2Ysny/giphy.gif"
              alt="Orchestral music"
              width="480"
              height="270"
            />
            <div class="caption">
              <h2>Orchestral</h2>
              <p>Great for cinematic intros, epic ads, and emotional scenes.</p>
            </div>
          </div>
          <div class="carousel-item">
            <img
              src="https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExMWc5ZnhjdjNlZXhlN2U2cDFtODQwbHBsaHRvMjlld21zeWs0YWExOCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/Lp0Ou5LRX3oGURgXxt/giphy.gif"
              alt="Lo-Fi music"
              width="384"
              height="480"
            />
            <div class="caption">
              <h2>Lo-Fi</h2>
              <p>
                Great for chill background beats, study sessions, and coffee
                shop vibes.
              </p>
            </div>
          </div>
          <div class="carousel-item">
            <img
              src="https://media2.giphy.com/media/v1.Y2lkPTc5MGI3NjExd2x1ODIxYWF5am1ocXZyNGhyZHJvNHRlMnhmbXBkYTRoZ244MGlpdCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/fxTZFbaSezLVbr3qLj/giphy.gif"
              alt="Jazz music"
              width="640"
              height="640"
            />
            <div class="caption">
              <h2>Jazz</h2>
              <p>
                Perfect for classy ads, lounge atmospheres, or soulful scenes.
              </p>
            </div>
          </div>
        </div>
        <div class="hero-overlay">
          <h1>Your Brand's Soundtrack Starts Here</h1>
          <p>
            We create custom-made jingles and music for ads, events, and
            experiences.
          </p>
          <a href="views/productos.php" class="cta-button">Hear Our Work</a>
        </div>
      </section>

      <!-- About Section -->
      <section class="about-section">
        <div class="content">
          <h2>Who We Are</h2>
          <p>
            JingleWorks is a creative audio agency that crafts powerful sonic
            identities for brands, events, and creators. From subtle ambiances
            to bold anthems, we shape the way your audience hears you.
          </p>
        </div>
      </section>

      <section class="news-section">
        <h2>Latest News</h2>
        <?php if (!empty($errors)) : ?>
          <p class="news-error"><?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif (empty($latestNews)) : ?>
          <p class="news-empty">No news available at the moment.</p>
        <?php else : ?>
          <div id="news-container" class="news-container">
            <?php foreach ($latestNews as $newsItem) : ?>
              <?php
              

              $fullText = trim((string) ($newsItem['texto'] ?? ''));
              if (function_exists('mb_strlen')) {
                  $isTruncated = mb_strlen($fullText) > $newsPreviewLimit;
                  $previewText = $isTruncated ? mb_substr($fullText, 0, $newsPreviewLimit) : $fullText;
              } else {
                  $isTruncated = strlen($fullText) > $newsPreviewLimit;
                  $previewText = $isTruncated ? substr($fullText, 0, $newsPreviewLimit) : $fullText;
              }
              ?>
              <article class="news-card">
                
                <div class="news-content">
                  <h3><?= htmlspecialchars($newsItem['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                  <p class="news-date"><?= htmlspecialchars(date('d/m/Y', strtotime($newsItem['fecha'])), ENT_QUOTES, 'UTF-8') ?></p>
                  <p>
                    <?= htmlspecialchars($previewText, ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($isTruncated) : ?>
                      ...
                      <a
                        class="news-read-more"
                        href="views/noticias.php#noticia-<?= htmlspecialchars((string) $newsItem['idNoticia'], ENT_QUOTES, 'UTF-8') ?>"
                      >
                        Read more
                      </a>
                    <?php endif; ?>
                  </p>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- Services Section -->
      <section class="services-section">
        <div class="content">
          <h2>Our Services</h2>
          <div class="services-grid">
            <div class="service-card">
              <h3>🎵 Advertising Jingles</h3>
              <p>
                Catchy, memorable music crafted to make your brand
                unforgettable.
              </p>
            </div>
            <div class="service-card">
              <h3>🔊 Sonic Logos</h3>
              <p>
                Short audio signatures that define your brand's identity
                instantly.
              </p>
            </div>
            <div class="service-card">
              <h3>🎤 Event Music</h3>
              <p>
                High-energy or ambient soundtracks for product launches and
                shows.
              </p>
            </div>
            <div class="service-card">
              <h3>🎧 Podcast Sound Design</h3>
              <p>
                Intros, outros, transitions, and background scoring for
                storytelling.
              </p>
            </div>
            <div class="service-card">
              <h3>🎼 Custom Compositions</h3>
              <p>
                Tailored music for apps, games, films, or anything creative.
              </p>
            </div>
            <div class="service-card">
              <h3>🎚️ Mixing & Mastering</h3>
              <p>
                Professional finishing touch to make your audio shine
                everywhere.
              </p>
            </div>
          </div>
        </div>
        <div class="services-cta">
          <a href="views/contacto.php" class="cta-button">Contact Us</a>
        </div>
      </section>

      <section class="usecase-section">
        <div class="content">
          <h2>Where Our Music Lives</h2>
          <div class="usecase-flex" id="usecaseFlex">
            <div class="usecase-box">🎬 Movie Intros</div>
            <div class="usecase-box">📺 TV Commercials</div>
            <div class="usecase-box">🎤 Live Events</div>
            <div class="usecase-box">🎧 Podcasts</div>
            <div class="usecase-box">📱 Apps & Games</div>
            <div class="usecase-box">🏢 Corporate Branding</div>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="cta-section">
        <div class="content">
          <h2>Ready to Sound Amazing?</h2>
          <p>
            Get a custom jingle or soundtrack that makes your brand
            unforgettable.
          </p>
          <a href="views/presupuesto.php" class="cta-button"
            >Request a Quote</a
          >
        </div>
      </section>
    </main>
<?php
$footerExtra = '';
include __DIR__ . '/includes/footer.php';
?>

