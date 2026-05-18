<?php
session_start();

$activePage = 'noticias';
$prefix = '../';
$pageTitle = 'News | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/noticias.css" />';

$errors = [];
$noticiasData = [];

require __DIR__ . '/../includes/db.php';

if ($dbError !== null) {
    $errors[] = $dbError;
} else {
    $stmt = $mysqli->prepare('
        SELECT
            n.idNoticia,
            n.titulo,
            n.imagen,
            n.texto,
            n.fecha,
            ud.nombre
        FROM noticias n
        JOIN users_data ud ON n.idUser = ud.idUser
        ORDER BY n.fecha DESC
    ');
    
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $noticiasData[] = $row;
        }
        $stmt->close();
    } else {
        $errors[] = 'Database query failed.';
    }

    $mysqli->close();
}

include __DIR__ . '/../includes/header.php';
?>

<main class="noticias-page">
    <h1>News</h1>
    <p class="intro">Stay updated with the latest news and announcements from JingleWorks.</p>

    <?php if (!empty($errors)) : ?>
        <div class="form-messages error">
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php elseif (empty($noticiasData)) : ?>
        <div class="no-news">
            <p>No news available at the moment.</p>
        </div>
    <?php else: ?>
        <div class="noticias-container">
            <?php foreach ($noticiasData as $noticia) : ?>
                <article class="noticia-card" id="noticia-<?= htmlspecialchars((string) $noticia['idNoticia'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($noticia['imagen'])) : ?>
                        <?php
                        $imagePath = $noticia['imagen'];
                        $imageFile = null;
                        if (strpos($imagePath, '/') === false && strpos($imagePath, '\\') === false) {
                            $imageFile = __DIR__ . '/../images/news/' . $imagePath;
                            $imagePath = '../images/news/' . $imagePath;
                        } elseif (strpos($imagePath, 'images/news/') === 0) {
                            $imageFile = __DIR__ . '/../' . $imagePath;
                            $imagePath = '../' . $imagePath;
                        }
                        $imageSize = $imageFile && is_file($imageFile) ? getimagesize($imageFile) : null;
                        ?>
                        <div class="noticia-image">
                            <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" 
                                 alt="<?= htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                                 <?php if ($imageSize) : ?>
                                 width="<?= (int) $imageSize[0] ?>"
                                 height="<?= (int) $imageSize[1] ?>"
                                 <?php endif; ?> />
                        </div>
                    <?php endif; ?>
                    
                    <div class="noticia-content">
                        <h2><?= htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
                        
                        <div class="noticia-meta">
                            <span class="noticia-fecha">
                                <?= htmlspecialchars(date('d/m/Y', strtotime($noticia['fecha'])), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="noticia-autor">
                                By <?= htmlspecialchars($noticia['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        
                        <div class="noticia-content">
                            <?= nl2br(htmlspecialchars(trim($noticia['texto']), ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>

