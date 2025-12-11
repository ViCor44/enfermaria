<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Patient
{
    public static function createForIncident(
        int $incidentId,
        string $fullName,
        ?string $nationality,
        ?string $hotel,
        ?string $roomNumber
    ): int {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO patients (incident_id, full_name, nationality, hotel, room_number)
            VALUES (:incident_id, :full_name, :nationality, :hotel, :room_number)
        ");

        $stmt->execute([
            ':incident_id' => $incidentId,
            ':full_name'   => $fullName,
            ':nationality' => $nationality,
            ':hotel'       => $hotel,
            ':room_number' => $roomNumber,
        ]);

        return (int)$pdo->lastInsertId();
    }

    // Mais tarde podemos adicionar métodos para ler dados com controlo de acesso.
}
