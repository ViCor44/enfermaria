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
                ir.*,
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
}
