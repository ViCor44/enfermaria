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
        ?string $address,
        ?string $phone,
        ?string $dob,        // 'YYYY-MM-DD' ou null
        ?string $idType,     // 'CC' ou 'Passaporte'
        ?string $idNumber
    ): int {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO patients
            (incident_id, full_name, nationality, address, phone, dob, id_type, id_number)
            VALUES
            (:incident_id, :full_name, :nationality, :address, :phone, :dob, :id_type, :id_number)
        ");

        $stmt->execute([
            ':incident_id' => $incidentId,
            ':full_name'   => $fullName,
            ':nationality' => $nationality,
            ':address'     => $address,
            ':phone'       => $phone,
            ':dob'         => $dob ?: null,
            ':id_type'     => $idType ?: null,
            ':id_number'   => $idNumber ?: null,
        ]);

        return (int)$pdo->lastInsertId();
    }


    // Mais tarde podemos adicionar métodos para ler dados com controlo de acesso.
}
