<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/GalleryModel.php';

/**
 * Handles public gallery listing and admin gallery CRUD.
 */
final class GalleryController extends Controller
{
    /**
     * Public gallery page with graceful DB fallback.
     */
    public function publicIndex(): void
    {
        $errors = [];
        try {
            $posts = (new GalleryModel())->all();
        } catch (Throwable $exception) {
            $posts = [];
            $errors[] = $this->databaseUnavailableMessage('gallery');
        }

        $this->view('public/productos', [
            'errors' => $errors,
            'posts' => $posts,
        ]);
    }

    /**
     * Admin gallery listing plus selectable local media files.
     */
    public function adminIndex(array $errors = []): void
    {
        Auth::requireAdmin();
        try {
            $postData = (new GalleryModel())->all();
        } catch (Throwable $exception) {
            $postData = [];
            $errors[] = $this->databaseUnavailableMessage('gallery administration');
        }

        $this->view('admin/galeria', [
            'errors' => $errors,
            'success' => flash('success'),
            'postData' => $postData,
            'images' => $this->files('images/gallery'),
            'audio_files' => $this->files('audio'),
        ]);
    }

    /**
     * Creates a gallery post that references existing image and audio files.
     */
    public function create(): void
    {
        Auth::requireAdmin();
        $data = $this->galleryData();
        $errors = $this->validate($data);
        if ($errors !== []) {
            $this->adminIndex($errors);
            return;
        }
        (new GalleryModel())->create($data);
        flash('success', 'Gallery post created successfully!');
        $this->redirect('index.php?page=admin-gallery');
    }

    /**
     * Updates one gallery post after validating local media choices.
     */
    public function update(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['gallery_item_id'] ?? 0);
        $data = $this->galleryData();
        $errors = $this->validate($data);
        if ($id <= 0) {
            $errors[] = 'Gallery post ID is missing.';
        }
        if ($errors !== []) {
            $this->adminIndex($errors);
            return;
        }
        (new GalleryModel())->update($id, $data);
        flash('success', 'Gallery post updated successfully!');
        $this->redirect('index.php?page=admin-gallery');
    }

    /**
     * Deletes one gallery post by id.
     */
    public function delete(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['gallery_item_id'] ?? 0);
        if ($id <= 0 || !(new GalleryModel())->delete($id)) {
            $this->adminIndex(['Gallery post not found.']);
            return;
        }
        flash('success', 'Gallery post deleted successfully!');
        $this->redirect('index.php?page=admin-gallery');
    }

    /**
     * Converts comma-separated tags into JSON for storage.
     */
    private function galleryData(): array
    {
        $tagsInput = trim($_POST['tags'] ?? '');
        return [
            'name' => trim($_POST['name'] ?? ''),
            'summary' => trim($_POST['summary'] ?? ''),
            'details' => trim($_POST['details'] ?? ''),
            'tags' => json_encode(array_values(array_filter(array_map('trim', explode(',', $tagsInput))))),
            'image' => $_POST['image'] ?? '',
            'audio_file' => $_POST['audio_file'] ?? '',
        ];
    }

    /**
     * Ensures required fields refer to files that exist in project media folders.
     */
    private function validate(array $data): array
    {
        $errors = [];
        if ($data['name'] === '' || $data['image'] === '' || $data['audio_file'] === '') {
            $errors[] = 'Name, image, and audio file are required.';
        }
        if ($data['image'] !== '' && !in_array($data['image'], $this->files('images/gallery'), true)) {
            $errors[] = 'Selected image is not valid.';
        }
        if ($data['audio_file'] !== '' && !in_array($data['audio_file'], $this->files('audio'), true)) {
            $errors[] = 'Selected audio file is not valid.';
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
