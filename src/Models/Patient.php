<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Patient
{
    public static function createForIncident(
                
        ?string $nationality,
        ?string $address,
        ?string $postalCode,
        ?string $city,
        ?string $phone,
        ?string $dob,
        ?string $idType,
        ?string $idNumber,
        int $refusedHospital = 0
    ): int {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            UPDATE patients
            (               
                nationality,
                address,
                postal_code,
                city,
                phone,
                dob,
                id_type,
                id_number,
                refused_hospital
            )
            VALUES
            (
                :nationality,
                :address,
                :postal_code,
                :city,
                :phone,
                :dob,
                :id_type,
                :id_number,
                :refused_hospital
            )
        ");

        $stmt->execute([            
            ':nationality'      => $nationality,
            ':address'          => $address,
            ':postal_code'      => $postalCode ?: null,
            ':city'             => $city ?: null,
            ':phone'            => $phone,
            ':dob'              => $dob ?: null,
            ':id_type'          => $idType ?: null,
            ':id_number'        => $idNumber ?: null,
            ':refused_hospital' => $refusedHospital ? 1 : 0,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function createBasic(
        int $incidentId,
        string $name,
        ?int $age,
        ?string $gender
    ): int {

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO patients
            (incident_id, full_name, age, gender)
            VALUES
            (:incident_id, :name, :age, :gender)
        ");

        $stmt->execute([
            ':incident_id' => $incidentId,
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function updateHospitalData(
    int $patientId,
    ?string $nationality,
    ?string $address,
    ?string $postalCode,
    ?string $city,
    ?string $phone,
    ?string $dob,
    ?string $idType,
    ?string $idNumber,
    int $refusedHospital
): void {

    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("
        UPDATE patients
        SET
            nationality = :nationality,
            address = :address,
            postal_code = :postal_code,
            city = :city,
            phone = :phone,
            dob = :dob,
            id_type = :id_type,
            id_number = :id_number,
            refused_hospital = :refused
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $patientId,
        ':nationality' => $nationality,
        ':address' => $address,
        ':postal_code' => $postalCode,
        ':city' => $city,
        ':phone' => $phone,
        ':dob' => $dob,
        ':id_type' => $idType,
        ':id_number' => $idNumber,
        ':refused' => $refusedHospital
    ]);
}
}
