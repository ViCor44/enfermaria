<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Incident
{
    public static function getTypes(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, name FROM incident_types ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO incidents
                (user_id, incident_type_id, location_id, occurred_at, patient_age, patient_gender, description)
            VALUES
                (:user_id, :incident_type_id, :location_id, :occurred_at, :patient_age, :patient_gender, :description)
        ");

        $stmt->execute([
            ':user_id'          => $data['user_id'],
            ':incident_type_id' => $data['incident_type_id'],
            ':location_id'      => $data['location_id'],
            ':occurred_at'      => $data['occurred_at'],
            ':patient_age'      => $data['patient_age'],
            ':patient_gender'   => $data['patient_gender'],
            ':description'      => $data['description'],
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function listByUser(int $userId): array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT i.*,
                t.name AS incident_type_name,
                l.name AS location_name
            FROM incidents i
            JOIN incident_types t ON t.id = i.incident_type_id
            JOIN locations l ON l.id = i.location_id
            WHERE i.user_id = ?
            ORDER BY i.occurred_at DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT i.*,
                t.name AS incident_type_name,
                l.name AS location_name
            FROM incidents i
            JOIN incident_types t ON t.id = i.incident_type_id
            JOIN locations l ON l.id = i.location_id
            WHERE i.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Pesquisar Acidentes com filtros reutilizáveis.
     * $fromDate / $toDate no formato 'YYYY-MM-DD' (ou null)
     * $locationId opcional
     * $userId opcional -> quando fornecido filtra só Acidentes desse user
     */
    public static function search(array $opts = []): array
    {
        $fromDate   = $opts['fromDate'] ?? null;
        $toDate     = $opts['toDate'] ?? null;
        $locationId = $opts['locationId'] ?? null;
        $userId     = isset($opts['userId']) ? (int)$opts['userId'] : null;
        $episode    = isset($opts['episode']) && $opts['episode'] !== '' ? (int)$opts['episode'] : null;

        $pdo = Database::getConnection();

        $sql = "
            SELECT i.*,
                it.name AS incident_type_name,
                l.name  AS location_name,
                u.full_name AS nurse_name
            FROM incidents i
            JOIN incident_types it ON it.id = i.incident_type_id
            JOIN locations l ON l.id = i.location_id
            JOIN users u ON u.id = i.user_id
            WHERE 1 = 1
        ";

        $params = [];

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
            $params[':locationId'] = $locationId;
        }

        if ($userId) {
            $sql .= " AND i.user_id = :userId";
            $params[':userId'] = $userId;
        }

        if ($episode !== null) {
            $sql .= " AND i.id = :episode";
            $params[':episode'] = $episode;
        }

        $sql .= " ORDER BY i.occurred_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function findWithDetailsForAdmin(int $id): ?array
    {
        $pdo = \App\Core\Database::getConnection();

        $sql = "
            SELECT
                i.*,
                it.name AS incident_type_name,
                l.name  AS location_name,
                u.full_name AS nurse_name,

                -- dados do paciente (LEFT JOIN porque pode não existir)
                p.full_name   AS patient_name,
                p.nationality AS patient_nationality,
                p.address     AS patient_address,
                p.phone       AS patient_phone,
                p.dob         AS patient_dob,
                p.id_type     AS patient_id_type,
                p.id_number   AS patient_id_number

            FROM incidents i
            LEFT JOIN incident_types it ON it.id = i.incident_type_id
            LEFT JOIN locations l ON l.id = i.location_id
            LEFT JOIN users u ON u.id = i.user_id
            LEFT JOIN patients p ON p.incident_id = i.id
            WHERE i.id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function getTreatmentsForIncident(int $incidentId): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT 
                tr.*,
                tt.name      AS treatment_type_name,
                u.full_name  AS nurse_name
            FROM treatments tr
            JOIN treatment_types tt ON tt.id = tr.treatment_type_id
            JOIN users u            ON u.id = tr.user_id
            WHERE tr.incident_id = :incident_id
            ORDER BY tr.created_at ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':incident_id' => $incidentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countToday(?int $userId = null): int
    {
        $pdo = \App\Core\Database::getConnection();
        $sql = "SELECT COUNT(*) FROM incidents WHERE DATE(occurred_at) = CURDATE()";
        $params = [];

        if ($userId !== null) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function countInProgress(?int $userId = null): int
    {
        $pdo = \App\Core\Database::getConnection();

        // assumimos que o campo status guarda 'em_curso' para tratamentos em curso
        $sql = "SELECT COUNT(*) FROM treatments WHERE status = 'em_curso'";
        $params = [];

        if ($userId !== null) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function createTypeIfNotExists(string $name): int
    {
        $name = trim($name);
        if ($name === '') return 0;

        $pdo = Database::getConnection();

        // Procurar (case-insensitive)
        $stmt = $pdo->prepare("SELECT id FROM incident_types WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $stmt->execute([$name]);
        $found = $stmt->fetchColumn();
        if ($found) {
            return (int)$found;
        }

        // Inserir novo tipo
        $ins = $pdo->prepare("INSERT INTO incident_types (name) VALUES (?)");
        $ins->execute([$name]);
        return (int)$pdo->lastInsertId();
    }

}
