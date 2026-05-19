<?php
$activePage = 'noticias-administracion';
$pageTitle = 'Manage News | JingleWorks';
$pageHeadExtras = '<link rel="stylesheet" href="css/noticias-administracion.css" />';
// Admin news-management view. Select options come from local images/news files.
include root_path('includes/header.php');
?>
<main class="noticias-administracion-page"><div class="container">
  <div class="page-header"><h1>Manage News</h1><a class="btn btn-secondary" href="index.php?page=news">View Public News</a></div>
  <?php if (!empty($errors)) : ?><div class="form-messages error"><ul><?php foreach ($errors as $error) : ?><li><?= e($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
  <?php if (!empty($success)) : ?><div class="form-messages success"><ul><li><?= e($success) ?></li></ul></div><?php endif ?>
  <section class="create-news">
    <h2>Create New News Article</h2>
    <form method="POST" action="index.php?page=news-create" class="news-form">
      <div class="form-group"><label for="titulo">Title: <span class="required">*</span></label><input type="text" id="titulo" name="titulo" required maxlength="255"></div>
      <div class="form-group"><label for="imagen">Picture: <span class="required">*</span></label><select id="imagen" name="imagen" required><option value="">-- Select Picture --</option><?php foreach ($images as $img) : ?><option value="<?= e($img) ?>"><?= e($img) ?></option><?php endforeach ?></select></div>
      <div class="form-group"><label for="fecha">Date: <span class="required">*</span></label><input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required></div>
      <div class="form-group"><label for="texto">Content: <span class="required">*</span></label><textarea id="texto" name="texto" rows="10" required></textarea></div>
      <button type="submit" class="btn btn-primary">Create News Article</button>
    </form>
  </section>
  <section class="news-list"><h2>News Articles List</h2>
    <?php if (empty($noticiasData)) : ?><p class="no-news">No news articles found.</p><?php else : ?>
      <div class="table-responsive"><table class="news-table"><thead><tr><th>Title</th><th>Date</th><th>Author</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($noticiasData as $noticia) : ?><tr>
        <td><?= e($noticia['titulo']) ?></td><td><?= e(date('d/m/Y', strtotime($noticia['fecha']))) ?></td><td><?= e($noticia['nombre']) ?></td>
        <td class="actions-cell">
          <button class="btn btn-edit" onclick="openEditModal(<?= e(json_encode($noticia)) ?>)">Edit</button>
          <form method="POST" action="index.php?page=news-delete" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this news article?');"><input type="hidden" name="idNoticia" value="<?= e($noticia['idNoticia']) ?>"><button type="submit" class="btn btn-delete">Delete</button></form>
        </td>
      </tr><?php endforeach ?>
      </tbody></table></div>
    <?php endif ?>
  </section>
</div></main>
<div id="editModal" class="modal"><div class="modal-content"><span class="close" onclick="closeEditModal()">&times;</span><h2>Edit News Article</h2>
  <form method="POST" action="index.php?page=news-update" class="news-form">
    <input type="hidden" id="editIdNoticia" name="idNoticia">
    <div class="form-group"><label for="editTitulo">Title: <span class="required">*</span></label><input type="text" id="editTitulo" name="titulo" required maxlength="255"></div>
    <div class="form-group"><label for="editPicture">Picture: <span class="required">*</span></label><select id="editPicture" name="imagen" required><option value="">-- Select Picture --</option><?php foreach ($images as $img) : ?><option value="<?= e($img) ?>"><?= e($img) ?></option><?php endforeach ?></select></div>
    <div class="form-group"><label for="editFecha">Date: <span class="required">*</span></label><input type="date" id="editFecha" name="fecha" required></div>
    <div class="form-group"><label for="editTexto">Content: <span class="required">*</span></label><textarea id="editTexto" name="texto" rows="10" required></textarea></div>
    <div class="modal-actions"><button type="submit" class="btn btn-primary">Save Changes</button><button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button></div>
  </form>
</div></div>
<script>
// Seeds the edit modal with the selected news article data.
function openEditModal(noticia){document.getElementById('editIdNoticia').value=noticia.idNoticia;document.getElementById('editTitulo').value=noticia.titulo;document.getElementById('editPicture').value=noticia.imagen;document.getElementById('editFecha').value=noticia.fecha;document.getElementById('editTexto').value=noticia.texto;document.getElementById('editModal').style.display='block';}
function closeEditModal(){document.getElementById('editModal').style.display='none';}
</script>
<?php $footerExtra = ''; include root_path('includes/footer.php'); ?>
