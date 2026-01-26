<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class HospitalFollowup
{
    public static function create(array $data): void
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO hospital_followups
            (incident_id, went_to_hospital, visit_date, hospital_name, notes, document_path, created_by)
            VALUES
            (:incident_id, :went, :visit, :hospital, :notes, :doc, :by)
        ");

        $stmt->execute([
            ':incident_id' => $data['incident_id'],
            ':went'        => $data['went_to_hospital'],
            ':visit'       => $data['visit_date'],
            ':hospital'    => $data['hospital_name'],
            ':notes'       => $data['notes'],
            ':doc'         => $data['document_path'],
            ':by'          => $data['created_by'],
        ]);
    }

    public static function findByIncident(int $incidentId): array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT hf.*, u.full_name AS created_by_name
            FROM hospital_followups hf
            JOIN users u ON u.id = hf.created_by
            WHERE hf.incident_id = ?
            ORDER BY hf.created_at ASC
        ");

        $stmt->execute([$incidentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
