<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;

class AdminUserController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function pending(): void
    {
        // Só Administrador
        Auth::requireRole(['Administrador']);

        $pendingUsers = User::getPendingApprovals();

        // buscar todos os perfis possíveis (Administrador, Manager, Enfermeiro, ...)
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->query("SELECT id, name FROM roles ORDER BY id");
        $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/admin/users_pending.php';
    }

    public function handleAction(): void
    {
        Auth::requireRole(['Administrador']);

        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $action = $_POST['action'] ?? '';
        $reason = $_POST['reason'] ?? null;
        $roleId = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;

        if ($userId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
            $_SESSION['error'] = 'Pedido inválido.';
            header('Location: ' . $this->baseUrl . '?route=admin_users');
            exit;
        }

        $admin = Auth::user();
        $adminId = (int)$admin['id'];

        if ($action === 'approve') {
            // se o admin escolheu um role válido, atualizamos
            if ($roleId > 0) {
                User::setUserRole($userId, $roleId);
            }

            User::approveUser($userId, $adminId);
            $_SESSION['success'] = 'Utilizador aprovado com sucesso. Perfil atualizado.';
        } else {
            User::rejectUser($userId, $adminId, $reason);
            $_SESSION['success'] = 'Utilizador rejeitado.';
        }

        header('Location: ' . $this->baseUrl . '?route=admin_users');
        exit;
    }

    public function listUsers(): void
    {
        Auth::requireRole(['Administrador']);
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->query("
            SELECT u.id, u.email, u.full_name, u.role_id, r.name AS role_name, u.approved
            FROM users u
            JOIN roles r ON r.id = u.role_id
            ORDER BY u.created_at DESC
        ");
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // obter lista de roles
        $r = $pdo->query("SELECT id, name FROM roles ORDER BY id");
        $roles = $r->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/admin/users_list.php';
    }

    public function changeRoleAction(): void
    {
        Auth::requireRole(['Administrador']);

        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $roleId = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;

        if ($userId <= 0 || $roleId <= 0) {
            $_SESSION['error'] = 'Parametros inválidos.';
            header('Location: /enfermaria/public/index.php?route=admin_users_list');
            exit;
        }

        \App\Models\User::setUserRole($userId, $roleId);
        $_SESSION['success'] = 'Perfil atualizado com sucesso.';
        header('Location: /enfermaria/public/index.php?route=admin_users_list');
        exit;
    }

}
