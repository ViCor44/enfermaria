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
        ?string $dob,
        ?string $idType,
        ?string $idNumber,
        int $refusedHospital = 0
    ): int {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO patients
            (
                incident_id,
                full_name,
                nationality,
                address,
                phone,
                dob,
                id_type,
                id_number,
                refused_hospital
            )
            VALUES
            (
                :incident_id,
                :full_name,
                :nationality,
                :address,
                :phone,
                :dob,
                :id_type,
                :id_number,
                :refused_hospital
            )
        ");

        $stmt->execute([
            ':incident_id'      => $incidentId,
            ':full_name'        => $fullName,
            ':nationality'      => $nationality,
            ':address'          => $address,
            ':phone'            => $phone,
            ':dob'              => $dob ?: null,
            ':id_type'          => $idType ?: null,
            ':id_number'        => $idNumber ?: null,
            ':refused_hospital' => $refusedHospital ? 1 : 0,
        ]);

        return (int) $pdo->lastInsertId();
    }
}
