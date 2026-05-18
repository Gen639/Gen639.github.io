<?php
declare(strict_types=1);
$footerExtra = $footerExtra ?? '';
?>
<footer class="footer">
      <div class="footer-content">
        <p>© 2025 JingleWorks. All rights reserved.</p>

        <div class="social-media-container">
          <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
            <img
              src="<?= $prefix ?>images/social/icons8-facebook-50.png"
              alt="facebook-icon"
              width="50"
              height="50"
            />
          </a>

          <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
            <img
              src="<?= $prefix ?>images/social/icons8-instagram-50.png"
              alt="instagram-icon"
              width="50"
              height="50"
            />
          </a>

          <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
            <img
              src="<?= $prefix ?>images/social/icons8-youtube-50.png"
              alt="youtube-icon"
              width="50"
              height="50"
            />
          </a>
          <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
            <img
              src="<?= $prefix ?>images/social/icons8-telegram-50.png"
              alt="telegram-icon"
              width="50"
              height="50"
            />
          </a>
          <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
            <img src="<?= $prefix ?>images/social/icons8-x-50.png" alt="x-icon" width="50" height="50" />
          </a>
          <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
            <img
              src="<?= $prefix ?>images/social/icons8-linkedin-50.png"
              alt="linkedin-icon"
              width="50"
              height="50"
            />
          </a>
        </div>

        <p>
          <a href="#">Legal Notice</a>
          |
          <a href="<?= $prefix ?>views/contacto.php">Contact</a>
          |
          <a href="<?= $prefix ?>views/presupuesto.php">Quote</a>
        </p>
      </div>
    </footer>
<script src="<?= $prefix ?>js/index.js"></script>
<?= $footerExtra ?>

  </body>
</html>
