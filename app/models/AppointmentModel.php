<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class AppointmentModel
{
    private mysqli $mysqli;

    public function __construct(?mysqli $mysqli = null)
    {
        $this->mysqli = $mysqli ?? Database::connection();
    }

    public function create(int $idUser, string $fechaCita, string $motivoCita): bool
    {
        $stmt = $this->mysqli->prepare(
            'INSERT INTO citas (idUser, fecha_cita, motivo_cita)
             VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iss', $idUser, $fechaCita, $motivoCita);
        $success = $stmt->execute() && $stmt->affected_rows >= 0;
        $stmt->close();

        return $success;
    }

    public function findForUser(int $idCita, int $idUser): ?array
    {
        $stmt = $this->mysqli->prepare(
            'SELECT idCita, idUser, fecha_cita, motivo_cita
             FROM citas
             WHERE idCita = ? AND idUser = ?
             LIMIT 1'
        );
        $stmt->bind_param('ii', $idCita, $idUser);
        $stmt->execute();

        $appointment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $appointment ?: null;
    }

    public function updateForUser(int $idCita, int $idUser, string $fechaCita, string $motivoCita): bool
    {
        $stmt = $this->mysqli->prepare(
            'UPDATE citas
             SET fecha_cita = ?, motivo_cita = ?
             WHERE idCita = ? AND idUser = ?'
        );
        $stmt->bind_param('ssii', $fechaCita, $motivoCita, $idCita, $idUser);
        $success = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }

    public function deleteForUser(int $idCita, int $idUser): bool
    {
        $stmt = $this->mysqli->prepare('DELETE FROM citas WHERE idCita = ? AND idUser = ?');
        $stmt->bind_param('ii', $idCita, $idUser);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function allForUser(int $idUser): array
    {
        $stmt = $this->mysqli->prepare(
            'SELECT idCita, fecha_cita, motivo_cita
             FROM citas
             WHERE idUser = ?
             ORDER BY fecha_cita DESC, idCita DESC'
        );
        $stmt->bind_param('i', $idUser);
        $stmt->execute();

        $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $appointments;
    }

    public function allWithUsers(): array
    {
        $result = $this->mysqli->query(
            'SELECT c.idCita, c.fecha_cita, c.motivo_cita, c.idUser,
                    ud.nombre, ud.apellidos, ud.email, ul.usuario
             FROM citas c
             JOIN users_data ud ON c.idUser = ud.idUser
             JOIN users_login ul ON ud.idUser = ul.idUser
             ORDER BY c.fecha_cita DESC, c.idCita DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
