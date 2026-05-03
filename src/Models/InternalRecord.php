<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class InternalRecord
{
    public static function search(array $opts = []): array
    {
        $fromDate   = $opts['fromDate'] ?? null;
        $toDate     = $opts['toDate'] ?? null;
        $locationId = $opts['locationId'] ?? null;

        $pdo = Database::getConnection();

        $sql = "
            SELECT
                ir.*, ir.first_name, ir.last_name,
                l.name AS location_name,
                u.full_name AS nurse_name
            FROM internal_records ir
            LEFT JOIN locations l ON l.id = ir.location_id
            JOIN users u ON u.id = ir.user_id
            WHERE 1=1
        ";

        $params = [];

        if ($fromDate) {
            $sql .= " AND DATE(ir.occurred_at) >= :from";
            $params[':from'] = $fromDate;
        }

        if ($toDate) {
            $sql .= " AND DATE(ir.occurred_at) <= :to";
            $params[':to'] = $toDate;
        }

        if ($locationId) {
            $sql .= " AND ir.location_id = :loc";
            $params[':loc'] = $locationId;
        }

        $sql .= " ORDER BY ir.occurred_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Stats ───────────────────────────────────────────────────────────────

    private static function buildDateFilterSql(array $filters, array &$params, string $alias = 'ir'): string
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

    public static function countByFilters(array $filters = []): int
    {
        $pdo = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM internal_records ir{$filterSql}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function statsByLocation(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params);

        $sql = "SELECT l.name AS local, COUNT(*) AS total
                FROM internal_records ir
                JOIN locations l ON l.id = ir.location_id
                {$filterSql}
                GROUP BY l.name
                ORDER BY total DESC, l.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function topLocation(array $filters = []): ?array
    {
        $pdo = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params);

        $sql = "SELECT l.name AS local, COUNT(*) AS total
                FROM internal_records ir
                JOIN locations l ON l.id = ir.location_id
                {$filterSql}
                GROUP BY l.name
                ORDER BY total DESC, l.name ASC
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function statsByGender(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params);

        $sql = "SELECT NULLIF(TRIM(ir.patient_gender), '') AS genero, COUNT(*) AS total
                FROM internal_records ir
                {$filterSql}
                GROUP BY genero
                ORDER BY FIELD(genero, 'M', 'F', 'Outro'), genero";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return array_values(array_filter(
            $stmt->fetchAll(PDO::FETCH_ASSOC),
            static fn(array $row): bool => (int)($row['total'] ?? 0) > 0
        ));
    }

    public static function statsByAge(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params);

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
                SELECT ir.patient_age AS idade
                FROM internal_records ir
                {$filterSql}
            ) dados
            WHERE idade IS NOT NULL
            GROUP BY faixa
            ORDER BY FIELD(faixa, '0-4', '5-12', '13-17', '18-30', '31-50', '50+')
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function statsByEmployeeType(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params);

        $sql = "SELECT
                    CASE WHEN ir.is_employee = 1 THEN 'Colaborador' ELSE 'Utente externo' END AS tipo,
                    COUNT(*) AS total
                FROM internal_records ir
                {$filterSql}
                GROUP BY ir.is_employee
                ORDER BY total DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function statsByTreatment(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $params = [];
        $filterSql = self::buildDateFilterSql($filters, $params);

        $sql = "SELECT NULLIF(TRIM(ir.treatment), '') AS tipo, COUNT(*) AS total
                FROM internal_records ir
                {$filterSql}
                GROUP BY tipo
                ORDER BY total DESC, tipo ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return array_values(array_filter(
            $stmt->fetchAll(PDO::FETCH_ASSOC),
            static fn(array $row): bool => (int)($row['total'] ?? 0) > 0
        ));
    }
}
