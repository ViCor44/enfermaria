<?php
namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'   => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['role'] ?? null,
        ];
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        $currentRole = $_SESSION['role'] ?? null;
        if (!in_array($currentRole, $roles, true)) {
            http_response_code(403);
            echo 'Acesso negado.';
            exit;
        }
    }
}
