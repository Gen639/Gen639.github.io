<?php
$activePage = 'contact';
$prefix = '../';
$pageTitle = 'Contact | JingleWorks';
$pageHeadExtras = <<<HTML
<link rel="stylesheet" href="../css/contacto.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
HTML;
include __DIR__ . '/../includes/header.php';
?>
    <main class="contact-page">
      <h1>Contact Us</h1>
      <p class="intro">
        We'd love to hear from you — whether you want to collaborate, request a
        custom jingle, or just say hello.
      </p>

      <div class="contact-details">
        <h2>Get in Touch</h2>
        <p>
          <strong>Address:</strong> Somewhere close calle, Some city name, Spain
        </p>
        <p><strong>Email:</strong> better send a pigeon</p>
        <p><strong>Phone:</strong> +XXXXXXXXXXXX</p>
      </div>
      <div id="map" class="contact-map"></div>

      <!-- Leaflet JS  -->
      <script>
        const map = L.map("map").setView([40.4168, -3.7038], 13); // Madrid example
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "© OpenStreetMap contributors",
        }).addTo(map);

        L.marker([40.4168, -3.7038])
          .addTo(map)
          .bindPopup("Our Office in Nowhere street")
          .openPopup();
      </script>
    </main>
    <!-- 🔚 Footer -->
<?php
$footerExtra = '<script src="../js/contacto.js"></script>';
include __DIR__ . '/../includes/footer.php';
?>
