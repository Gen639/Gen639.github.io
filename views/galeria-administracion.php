<?php
session_start();

$activePage = 'galeria-administracion';
$prefix = '../';
$pageTitle = 'Manage Gallery | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="../css/galeria-administracion.css" />';

if (!isset($_SESSION['idUser']) || ($_SESSION['rol'] ?? null) !== 'admin') {
    header('Location: login.php');
    exit;
}
$errors = [];
$success = [];

require __DIR__ . '/../includes/db.php';

if ($dbError !== null) {
    $errors[] = $dbError;
} else {
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = $_POST['name'] ?? '';
        $shortDescription = $_POST['summary'] ?? '';
        $longDescription = $_POST['details'] ?? '';
        $tags_input = $_POST['tags'] ?? '';
        $image = $_POST['image'] ?? '';
        $audio_file = $_POST['audio_file'] ?? '';

        if (empty($name) || empty($image) || empty($audio_file)) {
            $errors[] = 'Name, image, and audio file are required.';
        } else {
            $tags = json_encode(array_map('trim', explode(',', $tags_input)));
            $stmt = $mysqli->prepare('INSERT INTO gallery_items (name, summary, details, tags, image, audio_file) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('ssssss', $name, $shortDescription, $longDescription, $tags, $image, $audio_file);
                if ($stmt->execute()) {
                    $success[] = 'Gallery post created successfully!';
                } else {
                    $errors[] = 'Error creating gallery_items post.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'update') {
        $gallery_item_id = $_POST['gallery_item_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $shortDescription = $_POST['summary'] ?? '';
        $longDescription = $_POST['details'] ?? '';
        $tags_input = $_POST['tags'] ?? '';
        $image = $_POST['image'] ?? '';
        $audio_file = $_POST['audio_file'] ?? '';

        if (empty($gallery_item_id) || empty($name) || empty($image) || empty($audio_file)) {
            $errors[] = 'ID, name, image, and audio file are required.';
        } else {
            $tags = json_encode(array_map('trim', explode(',', $tags_input)));
            $stmt = $mysqli->prepare('UPDATE gallery_items SET name = ?, summary = ?, details = ?, tags = ?, image = ?, audio_file = ? WHERE gallery_item_id = ?');
            if ($stmt) {
                $stmt->bind_param('ssssssi', $name, $shortDescription, $longDescription, $tags, $image, $audio_file, $gallery_item_id);
                if ($stmt->execute()) {
                    $success[] = 'Gallery post updated successfully!';
                } else {
                    $errors[] = 'Error updating gallery_items post.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $gallery_item_id = $_POST['gallery_item_id'] ?? '';
        if (empty($gallery_item_id)) {
            $errors[] = 'Gallery post ID is missing.';
        } else {
            $stmt = $mysqli->prepare('DELETE FROM gallery_items WHERE gallery_item_id = ?');
            if ($stmt) {
                $stmt->bind_param('i', $gallery_item_id);
                if ($stmt->execute()) {
                    $success[] = 'Gallery post deleted successfully!';
                } else {
                    $errors[] = 'Error deleting gallery_items post.';
                }
                $stmt->close();
            }
        }
    }
}

$postData =[];
$images = array_diff(scandir('../images/gallery'), array('.', '..'));
$audio_files = array_diff(scandir('../audio/'), array('.', '..'));
$stmt = $mysqli->prepare('
    SELECT gallery_item_id, name, summary, details, tags, image, audio_file, created_at
    FROM gallery_items
');
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $postData[] = $row;
    }
    $stmt->close();
}
}

include __DIR__ . '/../includes/header.php';
?>



<main class="galeria-administracion-page"> 
    
    <div class="container">
        <div class="page-header">
            <h1>Manage Gallery</h1>
            <a class="btn btn-secondary" href="productos.php">View Public Gallery</a>
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

 <!-- Create New POST Section -->
  <section class="create-post">
            <h2>Create New Post in the Gallery</h2>
            <form method="POST" action="" class="post-form">
                <input type="hidden" name="action" value="create">

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Name (Song): <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="summary">Short Description:</label>
                        <input type="text" id="summary" name="summary" maxlength="255" placeholder="Shown under the name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="details">Long Description:</label>
                        <textarea id="details" name="details" rows="5" placeholder="Shown after pressing Show more"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tags">Tags (comma-separated):</label>
                        <input type="text" id="tags" name="tags" placeholder="e.g. rock, pop, 2023">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="image">Picture: <span class="required">*</span></label>
                        <select id="image" name="image" required>
                            <option value="">-- Select Picture --</option>
                            <?php foreach ($images as $img) : ?>
                                <option value="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="audio_file">Audio: <span class="required">*</span></label>
                        <select id="audio_file" name="audio_file" required>
                            <option value="">-- Select Audio --</option>
                            <?php foreach ($audio_files as $aud) : ?>
                                <option value="<?= htmlspecialchars($aud, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($aud, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Create Post</button>
            </form>
        </section>

        <!-- Gallery Posts List Section -->
        <section class="posts-list">
            <h2>Gallery Posts List</h2>

            <?php if (empty($postData)) : ?>
                <p class="no-posts">No posts found.</p>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="posts-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Short Description</th>
                                <th>Long Description</th>
                                <th>Tags</th>
                                <th>Picture</th>
                                <th>Audio</th>
                                <th>Uploaded At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($postData as $post) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($post['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($post['summary'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($post['details'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php
                                        $tags = json_decode($post['tags'], true);
                                        echo htmlspecialchars(implode(', ', $tags ?? []), ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($post['image'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($post['audio_file'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="actions-cell">
                                        <button 
                                            class="btn btn-edit" 
                                            onclick="openEditModal(<?= htmlspecialchars(json_encode($post), ENT_QUOTES, 'UTF-8') ?>)"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="gallery_item_id" value="<?= htmlspecialchars($post['gallery_item_id'], ENT_QUOTES, 'UTF-8') ?>">
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

<!-- Edit Post Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Gallery Post</h2>
        <form method="POST" action="" class="post-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="editIdGallery" name="gallery_item_id">

            <div class="form-row">
                <div class="form-group">
                    <label for="editName">Name (Song): <span class="required">*</span></label>
                    <input type="text" id="editName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editShortDescription">Short Description:</label>
                    <input type="text" id="editShortDescription" name="summary" maxlength="255" placeholder="Shown under the name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editLongDescription">Long Description:</label>
                    <textarea id="editLongDescription" name="details" rows="5" placeholder="Shown after pressing Show more"></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editTags">Tags (comma-separated):</label>
                    <input type="text" id="editTags" name="tags" placeholder="e.g. rock, pop, 2023">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editPicture">Picture: <span class="required">*</span></label>
                    <select id="editPicture" name="image" required>
                        <option value="">-- Select Picture --</option>
                        <?php foreach ($images as $img) : ?>
                            <option value="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editAudio">Audio: <span class="required">*</span></label>
                    <select id="editAudio" name="audio_file" required>
                        <option value="">-- Select Audio --</option>
                        <?php foreach ($audio_files as $aud) : ?>
                            <option value="<?= htmlspecialchars($aud, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($aud, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(post) {
    document.getElementById('editIdGallery').value = post.gallery_item_id;
    document.getElementById('editName').value = post.name;
    document.getElementById('editShortDescription').value = post.summary || '';
    document.getElementById('editLongDescription').value = post.details || '';
    const tags = JSON.parse(post.tags || '[]');
    document.getElementById('editTags').value = tags.join(', ');
    document.getElementById('editPicture').value = post.image;
    document.getElementById('editAudio').value = post.audio_file;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php
$footerExtra = '';
include __DIR__ . '/../includes/footer.php';
?>

