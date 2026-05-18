<?php
session_start();

$activePage = 'noticias-administracion';
$prefix = '../';
$pageTitle = 'Manage News | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/noticias-administracion.css" />';

// Verify admin is logged in
if (!isset($_SESSION['idUser']) || ($_SESSION['rol'] ?? null) !== 'admin') {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = [];
$images = array_diff(scandir('../images/news'), array('.', '..'));

// Database connection
require __DIR__ . '/../includes/db.php';

if ($dbError !== null) {
    $errors[] = $dbError;
} else {
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // Create new news
        if ($action === 'create') {
            $titulo = trim($_POST['titulo'] ?? '');
            $texto = trim($_POST['texto'] ?? '');
            $fecha = $_POST['fecha'] ?? date('Y-m-d');
            $imagen = $_POST['imagen'] ?? '';

            // Validate inputs
            if (empty($titulo) || empty($texto) || empty($imagen)) {
                $errors[] = 'Title, content, and image are required.';
            } elseif (strlen($titulo) > 255) {
                $errors[] = 'Title must be 255 characters or less.';
            } else {
                if (empty($errors)) {
                    // Check if title already exists
                    $stmt = $mysqli->prepare('SELECT idNoticia FROM noticias WHERE titulo = ?');
                    if ($stmt) {
                        $stmt->bind_param('s', $titulo);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $errors[] = 'A news article with this title already exists.';
                        } else {
                            // Insert news
                            $stmt2 = $mysqli->prepare('INSERT INTO noticias (titulo, imagen, texto, fecha, idUser) VALUES (?, ?, ?, ?, ?)');
                            if ($stmt2) {
                                $stmt2->bind_param('ssssi', $titulo, $imagen, $texto, $fecha, $_SESSION['idUser']);
                                if ($stmt2->execute()) {
                                    $success[] = 'News article created successfully!';
                                } else {
                                    $errors[] = 'Error creating news article. Please try again.';
                                }
                                $stmt2->close();
                            }
                        }
                        $stmt->close();
                    }
                }
            }
        }

        // Update news
        elseif ($action === 'update') {
            $idNoticia = $_POST['idNoticia'] ?? '';
            $titulo = trim($_POST['titulo'] ?? '');
            $texto = trim($_POST['texto'] ?? '');
            $fecha = $_POST['fecha'] ?? '';
            $imagen = $_POST['imagen'] ?? '';

            if (empty($idNoticia) || empty($titulo) || empty($texto) || empty($imagen)) {
                $errors[] = 'ID, title, content, and image are required.';
            } elseif (strlen($titulo) > 255) {
                $errors[] = 'Title must be 255 characters or less.';
            } else {
                if (empty($errors)) {
                    // Check if title is already used by another news article
                    $stmt = $mysqli->prepare('SELECT idNoticia FROM noticias WHERE titulo = ? AND idNoticia != ?');
                    if ($stmt) {
                        $stmt->bind_param('si', $titulo, $idNoticia);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $errors[] = 'Another news article with this title already exists.';
                        } else {
                            $stmt2 = $mysqli->prepare('UPDATE noticias SET titulo = ?, imagen = ?, texto = ?, fecha = ? WHERE idNoticia = ?');
                            if ($stmt2) {
                                $stmt2->bind_param('ssssi', $titulo, $imagen, $texto, $fecha, $idNoticia);
                                if ($stmt2->execute()) {
                                    $success[] = 'News article updated successfully!';
                                } else {
                                    $errors[] = 'Error updating news article. Please try again.';
                                }
                                $stmt2->close();
                            }
                        }
                        $stmt->close();
                    }
                }
            }
        }

        // Delete news
        elseif ($action === 'delete') {
            $idNoticia = $_POST['idNoticia'] ?? '';

            if (empty($idNoticia)) {
                $errors[] = 'News ID is missing.';
            } else {
                // Delete news
                $stmt2 = $mysqli->prepare('DELETE FROM noticias WHERE idNoticia = ?');
                if ($stmt2) {
                    $stmt2->bind_param('i', $idNoticia);
                    if ($stmt2->execute()) {
                        $success[] = 'News article deleted successfully!';
                    } else {
                        $errors[] = 'Error deleting news article. Please try again.';
                    }
                    $stmt2->close();
                }
            }
        }
    }

    // Fetch all news with author info
    $noticiasData = [];
    $stmt = $mysqli->prepare('
        SELECT
            n.idNoticia,
            n.titulo,
            n.imagen,
            n.texto,
            n.fecha,
            n.idUser,
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
    }
}

include __DIR__ . '/../includes/header.php';
?>

<main class="noticias-administracion-page">
    <div class="container">
        <div class="page-header">
            <h1>Manage News</h1>
            <a class="btn btn-secondary" href="noticias.php">View Public News</a>
        </div>

        <!-- Messages -->
        <?php if (!empty($errors)) : ?>
            <div class="form-messages error">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="form-messages success">
                <ul>
                    <?php foreach ($success as $msg) : ?>
                        <li><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Create New News Section -->
        <section class="create-news">
            <h2>Create New News Article</h2>
            <form method="POST" action="" class="news-form">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label for="titulo">Title: <span class="required">*</span></label>
                    <input type="text" id="titulo" name="titulo" required maxlength="255">
                </div>

                <div class="form-group">
                    <label for="imagen">Picture: <span class="required">*</span></label>
                    <select id="imagen" name="imagen" required>
                        <option value="">-- Select Picture --</option>
                        <?php foreach ($images as $img) : ?>
                            <option value="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha">Date: <span class="required">*</span></label>
                    <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="texto">Content: <span class="required">*</span></label>
                    <textarea id="texto" name="texto" rows="10" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Create News Article</button>
            </form>
        </section>

        <!-- News List Section -->
        <section class="news-list">
            <h2>News Articles List</h2>

            <?php if (empty($noticiasData)) : ?>
                <p class="no-news">No news articles found.</p>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="news-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Author</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($noticiasData as $noticia) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($noticia['fecha'])), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($noticia['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="actions-cell">
                                        <button 
                                            class="btn btn-edit" 
                                            onclick="openEditModal(<?= htmlspecialchars(json_encode($noticia), ENT_QUOTES, 'UTF-8') ?>)"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this news article? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="idNoticia" value="<?= htmlspecialchars($noticia['idNoticia'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- Edit News Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit News Article</h2>
        <form method="POST" action="" class="news-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="editIdNoticia" name="idNoticia">

            <div class="form-group">
                <label for="editTitulo">Title: <span class="required">*</span></label>
                <input type="text" id="editTitulo" name="titulo" required maxlength="255">
            </div>

            <div class="form-group">
                <label for="editPicture">Picture: <span class="required">*</span></label>
                <select id="editPicture" name="imagen" required>
                    <option value="">-- Select Picture --</option>
                    <?php foreach ($images as $img) : ?>
                        <option value="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="editFecha">Date: <span class="required">*</span></label>
                <input type="date" id="editFecha" name="fecha" required>
            </div>

            <div class="form-group">
                <label for="editTexto">Content: <span class="required">*</span></label>
                <textarea id="editTexto" name="texto" rows="10" required></textarea>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(noticia) {
    document.getElementById('editIdNoticia').value = noticia.idNoticia;
    document.getElementById('editTitulo').value = noticia.titulo;
    const imageName = (noticia.imagen || '').split('/').pop();
    document.getElementById('editPicture').value = imageName;
    document.getElementById('editFecha').value = noticia.fecha;
    document.getElementById('editTexto').value = noticia.texto;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    
    if (event.target === editModal) {
        editModal.style.display = 'none';
    }
}
</script>

<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>

