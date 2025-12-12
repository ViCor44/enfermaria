<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT u.*, r.name AS role_name
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function updateLastLogin(int $userId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public static function getPendingApprovals(): array
    {
        $pdo = Database::getConnection();

        $sql = "
            SELECT u.id,
                u.email,
                u.full_name,
                u.role_id,
                r.name AS role_name,
                ua.created_at
            FROM users u
            JOIN roles r ON r.id = u.role_id
            JOIN user_approvals ua ON ua.user_id = u.id
            WHERE ua.status = 'pending'
            ORDER BY ua.created_at ASC
        ";

        $stmt = $pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function approveUser(int $userId, int $adminId): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        // marcar user como aprovado
        $u = $pdo->prepare("UPDATE users SET approved = 1 WHERE id = ?");
        $u->execute([$userId]);

        // atualizar registo de approval
        $a = $pdo->prepare("
            UPDATE user_approvals
            SET status = 'approved', admin_user_id = ?, decided_at = NOW()
            WHERE user_id = ? AND status = 'pending'
        ");
        $a->execute([$adminId, $userId]);

        $pdo->commit();
    }

    public static function rejectUser(int $userId, int $adminId, ?string $reason = null): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        // manter approved = 0 (ou garantir)
        $u = $pdo->prepare("UPDATE users SET approved = 0 WHERE id = ?");
        $u->execute([$userId]);

        $a = $pdo->prepare("
            UPDATE user_approvals
            SET status = 'rejected', admin_user_id = ?, decided_at = NOW(), reason = ?
            WHERE user_id = ? AND status = 'pending'
        ");
        $a->execute([$adminId, $reason, $userId]);

        $pdo->commit();
    }

        public static function createUser(string $email, string $password, string $fullName, string $roleName = 'Enfermeiro'): int
    {
        $pdo = Database::getConnection();

        // Verificar se email já existe
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new \RuntimeException('Email já registado.');
        }

        // Obter role_id
        $r = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
        $r->execute([$roleName]);
        $roleId = $r->fetchColumn();
        if (!$roleId) {
            throw new \RuntimeException('Perfil não encontrado na tabela roles.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $pdo->beginTransaction();

        // Criar utilizador com approved = 0
        $ins = $pdo->prepare('
            INSERT INTO users (email, password_hash, role_id, full_name, approved)
            VALUES (?,?,?,?,0)
        ');
        $ins->execute([$email, $hash, $roleId, $fullName]);
        $userId = (int)$pdo->lastInsertId();

        // Criar registo em user_approvals
        $a = $pdo->prepare('INSERT INTO user_approvals (user_id, status) VALUES (?, "pending")');
        $a->execute([$userId]);

        $pdo->commit();

        return $userId;
    }

    public static function setUserRole(int $userId, int $roleId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $stmt->execute([$roleId, $userId]);
    }

    // marcar deleted_at = NOW()
    public static function softDelete(int $userId): bool
    {
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id = :id");
        try {
            return $stmt->execute([':id' => $userId]);
        } catch (\PDOException $e) {
            error_log("softDelete error: " . $e->getMessage());
            return false;
        }
    }

    // restaurar (deleted_at = NULL)
    public static function restore(int $userId): bool
    {
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET deleted_at = NULL WHERE id = :id");
        try {
            return $stmt->execute([':id' => $userId]);
        } catch (\PDOException $e) {
            error_log("restore error: " . $e->getMessage());
            return false;
        }
    }


}
