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

    public static function search(array $opts = []): array
{
    $status     = $opts['status'] ?? null;
    $fromDate   = $opts['fromDate'] ?? null;
    $toDate     = $opts['toDate'] ?? null;
    $locationId = $opts['locationId'] ?? null;
    $userId     = array_key_exists('userId', $opts) ? $opts['userId'] : null; // nullable

    $pdo = \App\Core\Database::getConnection();

    $sql = "
        SELECT 
            tr.*,
            tt.name AS treatment_type_name,
            u.full_name AS nurse_name,
            u2.full_name AS concluded_by_name,

            -- dados retirados da tabela incidents (garantidos)
            i.occurred_at AS incident_occurred_at,
            it.name AS incident_type_name,
            l.name AS location_name,
            i.patient_age AS patient_age,
            i.patient_gender AS patient_gender

            -- se precisares de dados adicionais do paciente, estão disponíveis via LEFT JOIN p.*
        FROM treatments tr
        JOIN treatment_types tt ON tt.id = tr.treatment_type_id
        JOIN users u ON u.id = tr.user_id
        JOIN incidents i ON i.id = tr.incident_id
        LEFT JOIN incident_types it ON it.id = i.incident_type_id
        LEFT JOIN locations l ON l.id = i.location_id
        LEFT JOIN patients p ON p.incident_id = i.id
        LEFT JOIN users u2 ON u2.id = tr.concluded_by
        WHERE 1 = 1
    ";

    $params = [];

    if ($status) {
        $sql .= " AND tr.status = :status";
        $params[':status'] = $status;
    }

    if ($fromDate) {
        $sql .= " AND DATE(i.occurred_at) >= :fromDate";
        $params[':fromDate'] = $fromDate;
    }

    if ($toDate) {
        $sql .= " AND DATE(i.occurred_at) <= :toDate";
        $params[':toDate'] = $toDate;
    }

    if ($locationId) {
        $sql .= " AND i.location_id = :locationId";
        $params[':locationId'] = (int)$locationId;
    }

    if ($userId !== null) {
        $sql .= " AND tr.user_id = :userId";
        $params[':userId'] = (int)$userId;
    }

    $sql .= " ORDER BY tr.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}


    public static function createTypeIfNotExists(string $name): int
    {
        $name = trim($name);
        if ($name === '') return 0;

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT id FROM treatment_types WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $stmt->execute([$name]);
        $found = $stmt->fetchColumn();
        if ($found) return (int)$found;

        $ins = $pdo->prepare("INSERT INTO treatment_types (name) VALUES (?)");
        $ins->execute([$name]);
        return (int)$pdo->lastInsertId();
    }

    public static function conclude(int $treatmentId, int $concludedByUserId): bool
    {
        $pdo = Database::getConnection();

        // validar existência e estado atual
        $stmt = $pdo->prepare("SELECT status, user_id FROM treatments WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $treatmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException("Tratamento não encontrado.");
        }
        if ($row['status'] === 'concluido') {
            // já concluído
            return false;
        }

        // transacção para actualizar e auditar
        $pdo->beginTransaction();
        try {
            $upd = $pdo->prepare("
                UPDATE treatments
                SET status = 'concluido', concluded_by = :concluded_by, concluded_at = NOW()
                WHERE id = :id
            ");
            $upd->execute([
                ':concluded_by' => $concludedByUserId,
                ':id' => $treatmentId
            ]);

            // registar no audit_log
            $audit = $pdo->prepare("
                INSERT INTO audit_logs (user_id, entity_type, entity_id, action, meta)
                VALUES (:user_id, 'treatment', :entity_id, 'conclude', :meta)
            ");
            $meta = json_encode([
                'previous_status' => $row['status'],
                'original_owner_user_id' => (int)$row['user_id']
            ]);
            $audit->execute([
                ':user_id' => $concludedByUserId,
                ':entity_id' => $treatmentId,
                ':meta' => $meta
            ]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }


}
