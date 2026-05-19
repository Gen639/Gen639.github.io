<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class GalleryModel
{
    private mysqli $mysqli;

    public function __construct(?mysqli $mysqli = null)
    {
        $this->mysqli = $mysqli ?? Database::connection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->mysqli->prepare(
            'INSERT INTO gallery_items (name, summary, details, tags, image, audio_file)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $summary = $data['summary'] ?? null;
        $details = $data['details'] ?? null;
        $tags = $data['tags'] ?? null;
        $image = $data['image'] ?? null;
        $stmt->bind_param(
            'ssssss',
            $data['name'],
            $summary,
            $details,
            $tags,
            $image,
            $data['audio_file']
        );
        $success = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }

    public function update(int $galleryItemId, array $data): bool
    {
        $stmt = $this->mysqli->prepare(
            'UPDATE gallery_items
             SET name = ?,
                 summary = ?,
                 details = ?,
                 tags = ?,
                 image = ?,
                 audio_file = ?
             WHERE gallery_item_id = ?'
        );
        $summary = $data['summary'] ?? null;
        $details = $data['details'] ?? null;
        $tags = $data['tags'] ?? null;
        $image = $data['image'] ?? null;
        $stmt->bind_param(
            'ssssssi',
            $data['name'],
            $summary,
            $details,
            $tags,
            $image,
            $data['audio_file'],
            $galleryItemId
        );
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function delete(int $galleryItemId): bool
    {
        $stmt = $this->mysqli->prepare('DELETE FROM gallery_items WHERE gallery_item_id = ?');
        $stmt->bind_param('i', $galleryItemId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function all(): array
    {
        $result = $this->mysqli->query(
            'SELECT gallery_item_id, name, summary, details, tags, image, audio_file, created_at
             FROM gallery_items
             ORDER BY created_at DESC, gallery_item_id DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
