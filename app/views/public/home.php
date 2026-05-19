<?php
$activePage = 'home';
$pageTitle = 'JingleWorks | Custom Audio Production';
include root_path('includes/header.php');
?>
<main>
  <?php if (($successMessage ?? '') !== '') : ?>
    <div class="content">
      <div class="form-messages success">
        <ul>
          <li><?= e($successMessage) ?></li>
        </ul>
      </div>
    </div>
  <?php endif; ?>
  <?php if (($errorMessage ?? '') !== '') : ?>
    <div class="content">
      <div class="form-messages error">
        <ul>
          <li><?= e($errorMessage) ?></li>
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
          <p>Ideal for clubs, parties, fast-paced content and motion graphics.</p>
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
          <p>Great for chill background beats, study sessions, and coffee shop vibes.</p>
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
          <p>Perfect for classy ads, lounge atmospheres, or soulful scenes.</p>
        </div>
      </div>
    </div>
    <div class="hero-overlay">
      <h1>Your Brand's Soundtrack Starts Here</h1>
      <p>We create custom-made jingles and music for ads, events, and experiences.</p>
      <a href="index.php?page=gallery" class="cta-button">Hear Our Work</a>
    </div>
  </section>

  <section class="about-section">
    <div class="content">
      <h2>Who We Are</h2>
      <p>
        JingleWorks is a creative audio agency that crafts powerful sonic
        identities for brands, events, and creators.
      </p>
    </div>
  </section>

  <section class="services-section">
    <div class="content">
      <h2>Our Services</h2>
      <div class="services-grid">
        <div class="service-card">
          <h3>Advertising Jingles</h3>
          <p>Catchy, memorable music crafted to make your brand unforgettable.</p>
        </div>
        <div class="service-card">
          <h3>Sonic Logos</h3>
          <p>Short audio signatures that define your brand's identity instantly.</p>
        </div>
        <div class="service-card">
          <h3>Event Music</h3>
          <p>High-energy or ambient soundtracks for product launches and shows.</p>
        </div>
        <div class="service-card">
          <h3>Podcast Sound Design</h3>
          <p>Intros, outros, transitions, and background scoring for storytelling.</p>
        </div>
        <div class="service-card">
          <h3>Custom Compositions</h3>
          <p>Tailored music for apps, games, films, or anything creative.</p>
        </div>
        <div class="service-card">
          <h3>Mixing & Mastering</h3>
          <p>Professional finishing touch to make your audio shine everywhere.</p>
        </div>
      </div>
    </div>
    <div class="services-cta">
      <a href="index.php?page=contact" class="cta-button">Contact Us</a>
    </div>
  </section>

  <section class="usecase-section">
    <div class="content">
      <h2>Where Our Music Lives</h2>
      <div class="usecase-flex" id="usecaseFlex">
        <div class="usecase-box">Movie Intros</div>
        <div class="usecase-box">TV Commercials</div>
        <div class="usecase-box">Live Events</div>
        <div class="usecase-box">Podcasts</div>
        <div class="usecase-box">Apps & Games</div>
        <div class="usecase-box">Corporate Branding</div>
      </div>
    </div>
  </section>

  <section class="cta-section">
    <div class="content">
      <h2>Ready to Sound Amazing?</h2>
      <p>Get a custom jingle or soundtrack that makes your brand unforgettable.</p>
      <a href="index.php?page=quote" class="cta-button">Request a Quote</a>
    </div>
  </section>
</main>
<?php
$footerExtra = '';
include root_path('includes/footer.php');
?>
