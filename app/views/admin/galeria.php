<?php
$activePage = 'galeria-administracion';
$pageTitle = 'Manage Gallery | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/galeria-administracion.css" />';
// Admin gallery-management view. Posts reference existing image and audio files.
include root_path('includes/header.php');
?>
<main class="galeria-administracion-page"><div class="container">
  <div class="page-header"><h1>Manage Gallery</h1><a class="btn btn-secondary" href="index.php?page=gallery">View Public Gallery</a></div>
  <?php if (!empty($errors)) : ?><div class="form-messages error"><ul><?php foreach ($errors as $error) : ?><li><?= e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
  <?php if (!empty($success)) : ?><div class="form-messages success"><ul><li><?= e($success) ?></li></ul></div><?php endif ?>
  <section class="create-post"><h2>Create New Post in the Gallery</h2>
    <form method="POST" action="index.php?page=gallery-create" class="post-form">
      <div class="form-row"><div class="form-group"><label for="name">Name (Song): <span class="required">*</span></label><input type="text" id="name" name="name" required></div><div class="form-group"><label for="summary">Short Description:</label><input type="text" id="summary" name="summary" maxlength="255"></div></div>
      <div class="form-row"><div class="form-group"><label for="details">Long Description:</label><textarea id="details" name="details" rows="5"></textarea></div></div>
      <div class="form-row"><div class="form-group"><label for="tags">Tags (comma-separated):</label><input type="text" id="tags" name="tags"></div></div>
      <div class="form-row">
        <div class="form-group"><label for="image">Picture: <span class="required">*</span></label><select id="image" name="image" required><option value="">-- Select Picture --</option><?php foreach ($images as $img) : ?><option value="<?= e($img) ?>"><?= e($img) ?></option><?php endforeach ?></select></div>
        <div class="form-group"><label for="audio_file">Audio: <span class="required">*</span></label><select id="audio_file" name="audio_file" required><option value="">-- Select Audio --</option><?php foreach ($audio_files as $aud) : ?><option value="<?= e($aud) ?>"><?= e($aud) ?></option><?php endforeach ?></select></div>
      </div>
      <button type="submit" class="btn btn-primary">Create Post</button>
    </form>
  </section>
  <section class="posts-list"><h2>Gallery Posts List</h2>
    <?php if (empty($postData)) : ?><p class="no-posts">No posts found.</p><?php else : ?>
      <div class="table-responsive"><table class="posts-table"><thead><tr><th>Name</th><th>Short Description</th><th>Tags</th><th>Picture</th><th>Audio</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($postData as $post) : ?><tr>
        <td><?= e($post['name']) ?></td><td><?= e($post['summary'] ?? '') ?></td><td><?= e(implode(', ', json_decode($post['tags'] ?? '[]', true) ?? [])) ?></td><td><?= e($post['image']) ?></td><td><?= e($post['audio_file']) ?></td>
        <td class="actions-cell"><button class="btn btn-edit" onclick="openEditModal(<?= e(json_encode($post)) ?>)">Edit</button><form method="POST" action="index.php?page=gallery-delete" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this post?');"><input type="hidden" name="gallery_item_id" value="<?= e($post['gallery_item_id']) ?>"><button type="submit" class="btn btn-delete">Delete</button></form></td>
      </tr><?php endforeach ?>
      </tbody></table></div>
    <?php endif ?>
  </section>
</div></main>
<div id="editModal" class="modal"><div class="modal-content"><span class="close" onclick="closeEditModal()">&times;</span><h2>Edit Gallery Post</h2>
  <form method="POST" action="index.php?page=gallery-update" class="post-form">
    <input type="hidden" id="editIdGallery" name="gallery_item_id">
    <div class="form-row"><div class="form-group"><label for="editName">Name (Song): <span class="required">*</span></label><input type="text" id="editName" name="name" required></div><div class="form-group"><label for="editShortDescription">Short Description:</label><input type="text" id="editShortDescription" name="summary" maxlength="255"></div></div>
    <div class="form-row"><div class="form-group"><label for="editLongDescription">Long Description:</label><textarea id="editLongDescription" name="details" rows="5"></textarea></div></div>
    <div class="form-row"><div class="form-group"><label for="editTags">Tags (comma-separated):</label><input type="text" id="editTags" name="tags"></div></div>
    <div class="form-row"><div class="form-group"><label for="editPicture">Picture: <span class="required">*</span></label><select id="editPicture" name="image" required><?php foreach ($images as $img) : ?><option value="<?= e($img) ?>"><?= e($img) ?></option><?php endforeach ?></select></div><div class="form-group"><label for="editAudio">Audio: <span class="required">*</span></label><select id="editAudio" name="audio_file" required><?php foreach ($audio_files as $aud) : ?><option value="<?= e($aud) ?>"><?= e($aud) ?></option><?php endforeach ?></select></div></div>
    <div class="modal-actions"><button type="submit" class="btn btn-primary">Save Changes</button><button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button></div>
  </form>
</div></div>
<script>
// Converts stored JSON tags back into the comma-separated field used by the form.
function openEditModal(post){document.getElementById('editIdGallery').value=post.gallery_item_id;document.getElementById('editName').value=post.name;document.getElementById('editShortDescription').value=post.summary||'';document.getElementById('editLongDescription').value=post.details||'';document.getElementById('editTags').value=(JSON.parse(post.tags||'[]')).join(', ');document.getElementById('editPicture').value=post.image;document.getElementById('editAudio').value=post.audio_file;document.getElementById('editModal').style.display='block';}
function closeEditModal(){document.getElementById('editModal').style.display='none';}
</script>
<?php $footerExtra = ''; include root_path('includes/footer.php'); ?>
