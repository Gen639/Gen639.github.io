<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class NewsModel
{
    private mysqli $mysqli;

    public function __construct(?mysqli $mysqli = null)
    {
        $this->mysqli = $mysqli ?? Database::connection();
    }

    public function titleExists(string $titulo, ?int $exceptIdNoticia = null): bool
    {
        if ($exceptIdNoticia === null) {
            $stmt = $this->mysqli->prepare('SELECT idNoticia FROM noticias WHERE titulo = ? LIMIT 1');
            $stmt->bind_param('s', $titulo);
        } else {
            $stmt = $this->mysqli->prepare(
                'SELECT idNoticia
                 FROM noticias
                 WHERE titulo = ? AND idNoticia != ?
                 LIMIT 1'
            );
            $stmt->bind_param('si', $titulo, $exceptIdNoticia);
        }

        $stmt->execute();
        $exists = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $exists;
    }

    public function create(array $data): bool
    {
        $stmt = $this->mysqli->prepare(
            'INSERT INTO noticias (titulo, imagen, texto, fecha, idUser)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssssi',
            $data['titulo'],
            $data['imagen'],
            $data['texto'],
            $data['fecha'],
            $data['idUser']
        );
        $success = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }

    public function update(int $idNoticia, array $data): bool
    {
        $stmt = $this->mysqli->prepare(
            'UPDATE noticias
             SET titulo = ?, imagen = ?, texto = ?, fecha = ?
             WHERE idNoticia = ?'
        );
        $stmt->bind_param(
            'ssssi',
            $data['titulo'],
            $data['imagen'],
            $data['texto'],
            $data['fecha'],
            $idNoticia
        );
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function delete(int $idNoticia): bool
    {
        $stmt = $this->mysqli->prepare('DELETE FROM noticias WHERE idNoticia = ?');
        $stmt->bind_param('i', $idNoticia);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function latest(int $limit = 3): array
    {
        $stmt = $this->mysqli->prepare(
            'SELECT idNoticia, titulo, imagen, texto, fecha
             FROM noticias
             ORDER BY fecha DESC, idNoticia DESC
             LIMIT ?'
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();

        $news = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $news;
    }

    public function allWithAuthors(): array
    {
        $result = $this->mysqli->query(
            'SELECT n.idNoticia, n.titulo, n.imagen, n.texto, n.fecha, n.idUser, ud.nombre
             FROM noticias n
             JOIN users_data ud ON n.idUser = ud.idUser
             ORDER BY n.fecha DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
