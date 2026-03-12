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
        string $name,
        ?int $age,
        ?string $gender
    ): int {

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO patients
            (full_name, age, gender)
            VALUES
            (:name, :age, :gender)
        ");

        $stmt->execute([
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender
        ]);

        return (int)$pdo->lastInsertId();
    }
}
