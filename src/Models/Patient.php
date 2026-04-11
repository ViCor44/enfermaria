<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\Text;
use PDO;

class Patient
{
    private static function normalizePersonName(string $name): string
    {
        return Text::toPortugueseTitleCase($name);
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

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
        ?string $gender,
        int $isEmployee = 0
    ): int {

        $name = self::normalizePersonName($name);

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO patients
            (incident_id, full_name, age, gender, is_employee)
            VALUES
            (:incident_id, :name, :age, :gender, :is_employee)
        ");

        $stmt->execute([
            ':incident_id' => $incidentId,
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender,
            ':is_employee' => $isEmployee ? 1 : 0,
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

    public static function updateFromIncidentForm(int $patientId, array $data): void
    {
        $data['full_name'] = self::normalizePersonName((string)($data['full_name'] ?? ''));

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            UPDATE patients
            SET
                full_name = :full_name,
                age = :age,
                gender = :gender,
                is_employee = :is_employee,
                nationality = :nationality,
                address = :address,
                postal_code = :postal_code,
                city = :city,
                phone = :phone,
                dob = :dob,
                id_type = :id_type,
                id_number = :id_number,
                refused_hospital = :refused_hospital
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $patientId,
            ':full_name' => $data['full_name'],
            ':age' => $data['age'],
            ':gender' => $data['gender'],
            ':is_employee' => $data['is_employee'],
            ':nationality' => $data['nationality'],
            ':address' => $data['address'],
            ':postal_code' => $data['postal_code'],
            ':city' => $data['city'],
            ':phone' => $data['phone'],
            ':dob' => $data['dob'],
            ':id_type' => $data['id_type'],
            ':id_number' => $data['id_number'],
            ':refused_hospital' => $data['refused_hospital'],
        ]);
    }
}
