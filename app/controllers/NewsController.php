<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/NewsModel.php';

/**
 * Handles public news listing and admin news CRUD.
 */
final class NewsController extends Controller
{
    /**
     * Public news page with graceful DB fallback.
     */
    public function publicIndex(): void
    {
        $errors = [];
        try {
            $noticiasData = (new NewsModel())->allWithAuthors();
        } catch (Throwable $exception) {
            $noticiasData = [];
            $errors[] = $this->databaseUnavailableMessage('news');
        }

        $this->view('public/noticias', [
            'errors' => $errors,
            'noticiasData' => $noticiasData,
        ]);
    }

    /**
     * Admin listing and create/edit form data.
     */
    public function adminIndex(array $errors = []): void
    {
        Auth::requireAdmin();
        try {
            $noticiasData = (new NewsModel())->allWithAuthors();
        } catch (Throwable $exception) {
            $noticiasData = [];
            $errors[] = $this->databaseUnavailableMessage('news administration');
        }

        $this->view('admin/noticias', [
            'errors' => $errors,
            'success' => flash('success'),
            'noticiasData' => $noticiasData,
            'images' => $this->files('images/news'),
        ]);
    }

    /**
     * Creates a news article owned by the current admin user.
     */
    public function create(): void
    {
        Auth::requireAdmin();
        $data = $this->newsData();
        $errors = $this->validate($data);
        $model = new NewsModel();

        if ($data['titulo'] !== '' && $model->titleExists($data['titulo'])) {
            $errors[] = 'A news article with this title already exists.';
        }

        if ($errors !== []) {
            $this->adminIndex($errors);
            return;
        }

        $data['idUser'] = Auth::id();
        $model->create($data);
        flash('success', 'News article created successfully!');
        $this->redirect('index.php?page=admin-news');
    }

    /**
     * Updates article fields after title/date/image validation.
     */
    public function update(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['idNoticia'] ?? 0);
        $data = $this->newsData();
        $errors = $this->validate($data);
        $model = new NewsModel();

        if ($id <= 0) {
            $errors[] = 'News ID is missing.';
        } elseif ($data['titulo'] !== '' && $model->titleExists($data['titulo'], $id)) {
            $errors[] = 'Another news article with this title already exists.';
        }

        if ($errors !== []) {
            $this->adminIndex($errors);
            return;
        }

        $model->update($id, $data);
        flash('success', 'News article updated successfully!');
        $this->redirect('index.php?page=admin-news');
    }

    /**
     * Deletes a news article by id.
     */
    public function delete(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['idNoticia'] ?? 0);
        if ($id <= 0 || !(new NewsModel())->delete($id)) {
            $this->adminIndex(['News article not found.']);
            return;
        }
        flash('success', 'News article deleted successfully!');
        $this->redirect('index.php?page=admin-news');
    }

    /**
     * Normalizes POST payloads from the news forms.
     */
    private function newsData(): array
    {
        return [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'imagen' => $_POST['imagen'] ?? '',
            'texto' => trim($_POST['texto'] ?? ''),
            'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
        ];
    }

    /**
     * Validates required fields and selected image availability.
     */
    private function validate(array $data): array
    {
        $errors = [];
        if ($data['titulo'] === '' || $data['texto'] === '' || $data['imagen'] === '') {
            $errors[] = 'Title, content, and image are required.';
        }
        if (strlen($data['titulo']) > 255) {
            $errors[] = 'Title must be 255 characters or less.';
        }
        if (!is_valid_date($data['fecha'])) {
            $errors[] = 'Date must be valid.';
        }
        if ($data['imagen'] !== '' && !in_array($data['imagen'], $this->files('images/news'), true)) {
            $errors[] = 'Selected image is not valid.';
        }

        return $errors;
    }

    /**
     * Lists selectable media files from a project-relative directory.
     */
    private function files(string $relativeDir): array
    {
        $dir = root_path($relativeDir);
        return is_dir($dir) ? array_values(array_diff(scandir($dir), ['.', '..'])) : [];
    }
}
