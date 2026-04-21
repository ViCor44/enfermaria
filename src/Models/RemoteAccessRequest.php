<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class RemoteAccessRequest
{
    public static function createPending(int $nurseUserId, string $nurseName, string $requestIp): string
    {
        $pdo = Database::getConnection();
        $requestCode = bin2hex(random_bytes(24));

        $stmt = $pdo->prepare(
            "INSERT INTO remote_access_requests
             (request_code, nurse_user_id, nurse_name, request_ip, status, created_at)
             VALUES (?, ?, ?, ?, 'pending', NOW())"
        );
        $stmt->execute([$requestCode, $nurseUserId, $nurseName, $requestIp]);

        return $requestCode;
    }

    public static function findByCode(string $requestCode): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT rar.*, u.full_name AS nurse_full_name
             FROM remote_access_requests rar
             JOIN users u ON u.id = rar.nurse_user_id
             WHERE rar.request_code = ?
             LIMIT 1"
        );
        $stmt->execute([$requestCode]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function expireOldPending(int $minutes = 20): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE remote_access_requests
             SET status = 'expired'
             WHERE status = 'pending'
               AND created_at < (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->execute([$minutes]);
    }

    public static function listPending(int $limit = 25): array
    {
        $pdo = Database::getConnection();
        $limit = max(1, min(100, $limit));

        $stmt = $pdo->query(
            "SELECT rar.id, rar.request_code, rar.nurse_name, rar.request_ip, rar.created_at,
                    u.id AS nurse_user_id, u.full_name AS nurse_full_name
             FROM remote_access_requests rar
             JOIN users u ON u.id = rar.nurse_user_id
             WHERE rar.status = 'pending'
             ORDER BY rar.created_at ASC
             LIMIT $limit"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function approve(int $requestId, int $adminId): ?array
    {
        $pdo = Database::getConnection();

        $select = $pdo->prepare(
            "SELECT * FROM remote_access_requests
             WHERE id = ? AND status = 'pending'
             LIMIT 1"
        );
        $select->execute([$requestId]);
        $row = $select->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $grantToken = bin2hex(random_bytes(32));

        $update = $pdo->prepare(
            "UPDATE remote_access_requests
             SET status = 'approved',
                 approved_by = ?,
                 approved_at = NOW(),
                 grant_token = ?,
                 grant_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
             WHERE id = ?"
        );
        $update->execute([$adminId, $grantToken, $requestId]);

        $row['grant_token'] = $grantToken;
        return $row;
    }

    public static function reject(int $requestId, int $adminId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE remote_access_requests
             SET status = 'rejected',
                 approved_by = ?,
                 approved_at = NOW()
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$adminId, $requestId]);

        return $stmt->rowCount() > 0;
    }

    public static function findApprovedByGrantToken(string $grantToken): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT rar.*, u.full_name AS nurse_full_name, u.role_id, u.approved, u.deleted_at, r.name AS role_name
             FROM remote_access_requests rar
             JOIN users u ON u.id = rar.nurse_user_id
             JOIN roles r ON r.id = u.role_id
             WHERE rar.grant_token = ?
               AND rar.status = 'approved'
               AND rar.grant_expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([$grantToken]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function markConsumed(int $requestId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE remote_access_requests
             SET status = 'consumed', consumed_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$requestId]);
    }
}
