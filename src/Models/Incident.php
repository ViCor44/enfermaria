<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\Text;
use PDO;

class Incident
{
    private static function columnExists(string $table, string $column): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SHOW COLUMNS FROM {$table} LIKE " . $pdo->quote($column));

        return $stmt !== false && $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    private static function buildDateFilterSql(array $filters, array &$params, string $alias = 'i'): string
    {
        $clauses = [];

        if (!empty($filters['fromDate'])) {
            $clauses[] = "DATE({$alias}.occurred_at) >= :stats_from_date";
            $params[':stats_from_date'] = $filters['fromDate'];
        }

        if (!empty($filters['toDate'])) {
            $clauses[] = "DATE({$alias}.occurred_at) <= :stats_to_date";
            $params[':stats_to_date'] = $filters['toDate'];
        }

        return $clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses);
    }

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
                (SELECT COUNT(*) FROM incidents i2 WHERE i2.id <= i.id) AS episode_number,
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
                (SELECT COUNT(*) FROM incidents i2 WHERE i2.id <= i.id) AS episode_number,
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
        $typeId     = isset($opts['typeId']) && (int)$opts['typeId'] > 0 ? (int)$opts['typeId'] : null;

        $pdo = Database::getConnection();

        $sql = "
            SELECT
                i.*,
                (SELECT COUNT(*) FROM incidents i2 WHERE i2.id <= i.id) AS episode_number,
                it.name AS incident_type_name,
                l.name  AS location_name,
                u.full_name AS nurse_name,

                p.full_name AS patient_name,
                p.dob AS patient_dob,
                p.gender AS patient_gender,
                p.is_employee AS patient_is_employee,
                p.refused_hospital,
                EXISTS (
                    SELECT 1
                    FROM treatments tr_hospital
                    JOIN treatment_types tt_hospital ON tt_hospital.id = tr_hospital.treatment_type_id
                    WHERE tr_hospital.incident_id = i.id
                      AND tt_hospital.name = 'Enviado para hospital'
                ) AS was_sent_to_hospital

            FROM incidents i
            JOIN incident_types it ON it.id = i.incident_type_id
            JOIN locations l ON l.id = i.location_id
            JOIN users u ON u.id = i.user_id
            LEFT JOIN patients p ON p.id = i.patient_id
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

        if ($typeId) {
            $sql .= " AND i.incident_type_id = :typeId";
            $params[':typeId'] = $typeId;
        }

        if ($userId) {
            $sql .= " AND i.user_id = :userId";
            $params[':userId'] = $userId;
        }

        if ($episode !== null && $episode > 0) {
            $sql .= " AND (i.id = :episodeId OR (SELECT COUNT(*) FROM incidents i2 WHERE i2.id <= i.id) = :episodeNumber)";
            $params[':episodeId'] = $episode;
            $params[':episodeNumber'] = $episode;
        }

        $sql .= " ORDER BY i.occurred_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithDetailsForAdmin(int $id): ?array
    {
        $pdo = \App\Core\Database::getConnection();

        $sql = "            
            SELECT
                i.*,
                (SELECT COUNT(*) FROM incidents i2 WHERE i2.id <= i.id) AS episode_number,
                it.name AS incident_type_name,
                l.name AS location_name,
                u.full_name AS nurse_name,

                p.full_name AS patient_name,
                p.dob AS patient_dob,
                p.gender AS patient_gender,
                p.is_employee AS patient_is_employee,
                p.nationality AS patient_nationality,
                p.address AS patient_address,
                p.postal_code AS patient_postal_code,
                p.city AS patient_city,
                p.phone AS patient_phone,
                p.dob AS patient_dob,
                p.id_type AS patient_id_type,
                p.id_number AS patient_id_number,
                p.refused_hospital

            FROM incidents i
            LEFT JOIN incident_types it ON it.id = i.incident_type_id
            LEFT JOIN locations l ON l.id = i.location_id
            LEFT JOIN users u ON u.id = i.user_id
            LEFT JOIN patients p ON p.id = i.patient_id
            WHERE i.id = :id
            LIMIT 1";

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
                u.full_name  AS nurse_name,
                ue.full_name AS notes_edited_by_name
            FROM treatments tr
            JOIN treatment_types tt ON tt.id = tr.treatment_type_id
            JOIN users u            ON u.id = tr.user_id
            LEFT JOIN users ue      ON ue.id = tr.notes_edited_by
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
        $name = Text::toPortugueseTitleCase($name);
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

    public static function statsByAge(array $filters = []): array
    {
        $db = Database::getConnection();
        $hasPatientAge = self::columnExists('patients', 'age');
        $ageExpression = $hasPatientAge
            ? 'COALESCE(p.age, i.patient_age)'
            : 'i.patient_age';
        $joinPatients = $hasPatientAge
            ? 'LEFT JOIN patients p ON p.id = i.patient_id'
            : '';
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params, 'i');

        $sql = "
            SELECT 
                CASE 
                    WHEN idade < 5 THEN '0-4'
                    WHEN idade BETWEEN 5 AND 12 THEN '5-12'
                    WHEN idade BETWEEN 13 AND 17 THEN '13-17'
                    WHEN idade BETWEEN 18 AND 30 THEN '18-30'
                    WHEN idade BETWEEN 31 AND 50 THEN '31-50'
                    ELSE '50+' 
                END AS faixa,
                COUNT(*) AS total
            FROM (
                SELECT {$ageExpression} AS idade
                FROM incidents i
                {$joinPatients}
                {$filterSql}
            ) dados
            WHERE idade IS NOT NULL
            GROUP BY faixa
            ORDER BY FIELD(faixa, '0-4', '5-12', '13-17', '18-30', '31-50', '50+');
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function statsByGender(array $filters = []): array
    {
        $db = Database::getConnection();
        $hasPatientGender = self::columnExists('patients', 'gender');
        $genderExpression = $hasPatientGender
            ? "COALESCE(NULLIF(TRIM(p.gender), ''), NULLIF(TRIM(i.patient_gender), ''))"
            : "NULLIF(TRIM(i.patient_gender), '')";
        $joinPatients = $hasPatientGender
            ? 'LEFT JOIN patients p ON p.id = i.patient_id'
            : '';
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params, 'i');

        $sql = "
            SELECT genero, COUNT(*) AS total
            FROM (
                SELECT {$genderExpression} AS genero
                FROM incidents i
                {$joinPatients}
                {$filterSql}
            ) dados
            GROUP BY genero
            ORDER BY FIELD(genero, 'M', 'F', 'Outro'), genero
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return array_values(array_filter(
            $stmt->fetchAll(\PDO::FETCH_ASSOC),
            static fn(array $row): bool => (int)($row['total'] ?? 0) > 0
        ));
    }

    public static function statsByLocation(array $filters = []): array
    {
        $db = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params, 'i');
        $sql = "SELECT l.id AS location_id, l.name AS local, COUNT(*) AS total
                FROM incidents i
                JOIN locations l ON i.location_id = l.id
                {$filterSql}
            GROUP BY l.id, l.name
            ORDER BY total DESC, l.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function statsByIncidentType(array $filters = []): array
    {
        $db = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params, 'i');
        $sql = "SELECT t.id AS type_id, t.name AS tipo, COUNT(*) AS total
                FROM incidents i
                JOIN incident_types t ON i.incident_type_id = t.id
                {$filterSql}
            GROUP BY t.id, t.name
            ORDER BY total DESC, t.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function countByFilters(array $filters = []): int
    {
        $db = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params, 'i');

        $stmt = $db->prepare("SELECT COUNT(*) FROM incidents i{$filterSql}");
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public static function topLocation(array $filters = []): ?array
    {
        $db = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params, 'i');

        $sql = "
            SELECT l.name AS local, COUNT(*) AS total
            FROM incidents i
            JOIN locations l ON i.location_id = l.id
            {$filterSql}
            GROUP BY l.name
            ORDER BY total DESC, l.name ASC
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function topIncidentType(array $filters = []): ?array
    {
        $db = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params, 'i');

        $sql = "
            SELECT t.name AS tipo, COUNT(*) AS total
            FROM incidents i
            JOIN incident_types t ON i.incident_type_id = t.id
            {$filterSql}
            GROUP BY t.name
            ORDER BY total DESC, t.name ASC
            LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM incidents WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function attachDocument(int $incidentId,string $type,string $file)
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO incident_documents (incident_id,type,file_path)
            VALUES (?,?,?)
        ");

        $stmt->execute([$incidentId,$type,$file]);
    }

    public static function getDocuments(int $incidentId)
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT * 
            FROM incident_documents
            WHERE incident_id = ?
        ");

        $stmt->execute([$incidentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
