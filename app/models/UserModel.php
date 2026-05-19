<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class UserModel
{
    private mysqli $mysqli;

    public function __construct(?mysqli $mysqli = null)
    {
        $this->mysqli = $mysqli ?? Database::connection();
    }

    public function findLoginByEmail(string $email): ?array
    {
        $stmt = $this->mysqli->prepare(
            'SELECT ul.idLogin, ul.idUser, ul.usuario, ul.password, ul.rol, ud.nombre, ud.apellidos, ud.email
             FROM users_login ul
             JOIN users_data ud ON ul.idUser = ud.idUser
             WHERE ud.email = ?
             LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    public function findLoginById(int $idUser): ?array
    {
        $stmt = $this->mysqli->prepare(
            'SELECT ul.idLogin, ul.idUser, ul.usuario, ul.password, ul.rol, ud.nombre, ud.apellidos, ud.email
             FROM users_login ul
             JOIN users_data ud ON ul.idUser = ud.idUser
             WHERE ul.idUser = ?
             LIMIT 1'
        );
        $stmt->bind_param('i', $idUser);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    public function findById(int $idUser): ?array
    {
        $stmt = $this->mysqli->prepare(
            'SELECT ud.idUser, ud.nombre, ud.apellidos, ud.email, ud.telefono,
                    ud.fecha_nacimiento, ud.direccion, ud.sexo, ul.usuario, ul.rol
             FROM users_data ud
             JOIN users_login ul ON ud.idUser = ul.idUser
             WHERE ud.idUser = ?
             LIMIT 1'
        );
        $stmt->bind_param('i', $idUser);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    public function emailExists(string $email, ?int $exceptIdUser = null): bool
    {
        if ($exceptIdUser === null) {
            $stmt = $this->mysqli->prepare('SELECT idUser FROM users_data WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
        } else {
            $stmt = $this->mysqli->prepare(
                'SELECT idUser FROM users_data WHERE email = ? AND idUser != ? LIMIT 1'
            );
            $stmt->bind_param('si', $email, $exceptIdUser);
        }

        $stmt->execute();
        $exists = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $exists;
    }

    public function usernameExists(string $usuario): bool
    {
        $stmt = $this->mysqli->prepare('SELECT idLogin FROM users_login WHERE usuario = ? LIMIT 1');
        $stmt->bind_param('s', $usuario);
        $stmt->execute();

        $exists = (bool) $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $exists;
    }

    public function create(array $data): int
    {
        $this->mysqli->begin_transaction();

        try {
            $stmt = $this->mysqli->prepare(
                'INSERT INTO users_data
                    (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?)'
            );
            $direccion = ($data['direccion'] ?? '') !== '' ? $data['direccion'] : null;
            $sexo = ($data['sexo'] ?? '') !== '' ? $data['sexo'] : null;
            $stmt->bind_param(
                'sssssss',
                $data['nombre'],
                $data['apellidos'],
                $data['email'],
                $data['telefono'],
                $data['fecha_nacimiento'],
                $direccion,
                $sexo
            );
            $stmt->execute();
            $stmt->close();

            $idUser = $this->mysqli->insert_id;

            $stmt = $this->mysqli->prepare(
                'INSERT INTO users_login (usuario, password, idUser, rol)
                 VALUES (?, ?, ?, ?)'
            );
            $rol = $data['rol'] ?? 'user';
            $stmt->bind_param('ssis', $data['usuario'], $data['password'], $idUser, $rol);
            $stmt->execute();
            $stmt->close();

            $this->mysqli->commit();

            return $idUser;
        } catch (Throwable $exception) {
            $this->mysqli->rollback();
            throw $exception;
        }
    }

    public function updateProfile(int $idUser, array $data): bool
    {
        $stmt = $this->mysqli->prepare(
            'UPDATE users_data
             SET nombre = ?,
                 apellidos = ?,
                 email = ?,
                 telefono = ?,
                 fecha_nacimiento = ?,
                 direccion = ?,
                 sexo = ?
             WHERE idUser = ?'
        );
        $direccion = ($data['direccion'] ?? '') !== '' ? $data['direccion'] : null;
        $sexo = ($data['sexo'] ?? '') !== '' ? $data['sexo'] : null;
        $stmt->bind_param(
            'sssssssi',
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            $data['telefono'],
            $data['fecha_nacimiento'],
            $direccion,
            $sexo,
            $idUser
        );
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateRole(int $idUser, string $rol): bool
    {
        $stmt = $this->mysqli->prepare('UPDATE users_login SET rol = ? WHERE idUser = ?');
        $stmt->bind_param('si', $rol, $idUser);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updatePassword(int $idUser, string $passwordHash): bool
    {
        $stmt = $this->mysqli->prepare('UPDATE users_login SET password = ? WHERE idUser = ?');
        $stmt->bind_param('si', $passwordHash, $idUser);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function delete(int $idUser): bool
    {
        $stmt = $this->mysqli->prepare('DELETE FROM users_data WHERE idUser = ?');
        $stmt->bind_param('i', $idUser);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function all(): array
    {
        $result = $this->mysqli->query(
            'SELECT ud.idUser, ud.nombre, ud.apellidos, ud.email, ud.telefono,
                    ud.fecha_nacimiento, ud.direccion, ud.sexo, ul.usuario, ul.rol
             FROM users_data ud
             JOIN users_login ul ON ud.idUser = ul.idUser
             ORDER BY ud.idUser DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function normalUsers(): array
    {
        $result = $this->mysqli->query(
            'SELECT ud.idUser, ud.nombre, ud.apellidos, ud.email
             FROM users_data ud
             JOIN users_login ul ON ud.idUser = ul.idUser
             WHERE ul.rol = "user"
             ORDER BY ud.nombre ASC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateUser(int $idUser, array $data): bool
    {
        $this->mysqli->begin_transaction();

        try {
            $this->updateProfile($idUser, $data);
            $this->updateRole($idUser, $data['rol']);
            $this->mysqli->commit();

            return true;
        } catch (Throwable $exception) {
            $this->mysqli->rollback();
            throw $exception;
        }
    }
}
