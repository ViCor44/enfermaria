<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Treatment
{
    public static function getTypes(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, name FROM treatment_types ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO treatments
                (incident_id, user_id, treatment_type_id, status, notes)
            VALUES
                (:incident_id, :user_id, :treatment_type_id, :status, :notes)
        ");

        $stmt->execute([
            ':incident_id'       => $data['incident_id'],
            ':user_id'           => $data['user_id'],
            ':treatment_type_id' => $data['treatment_type_id'],
            ':status'            => $data['status'],
            ':notes'             => $data['notes'],
        ]);

        return (int)$pdo->lastInsertId();
    }


    public static function listByUser(int $userId): array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT tr.*,
                   tt.name AS treatment_type_name,
                   i.occurred_at,
                   it.name AS incident_type_name,
                   l.name AS location_name
            FROM treatments tr
            JOIN treatment_types tt ON tt.id = tr.treatment_type_id
            JOIN incidents i ON i.id = tr.incident_id
            JOIN incident_types it ON it.id = i.incident_type_id
            JOIN locations l ON l.id = i.location_id
            WHERE tr.user_id = ?
            ORDER BY tr.created_at DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countInProgress(?int $userId = null): int
    {
        $pdo = Database::getConnection();

        if ($userId) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM treatments
                WHERE status = 'em_curso' AND user_id = ?
            ");
            $stmt->execute([$userId]);
        } else {
            $stmt = $pdo->query("
                SELECT COUNT(*) FROM treatments
                WHERE status = 'em_curso'
            ");
        }

        return (int)$stmt->fetchColumn();
    }

    public static function getHospitalTransferTypeId(): ?int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM treatment_types WHERE name = ? LIMIT 1");
        $stmt->execute(['Enviado para hospital']);
        $id = $stmt->fetchColumn();

        return $id ? (int)$id : null;
    }

    public static function setStatus(int $treatmentId, string $status): bool
    {
        $allowed = ['em_curso','concluido'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE treatments SET status = ? WHERE id = ?");
        return (bool)$stmt->execute([$status, $treatmentId]);
    }


}
