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
            SELECT u.id, u.email, u.full_name, u.phone, u.role_id, r.name AS role_name, u.approved, u.created_at, u.deleted_at                      
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.deleted_at IS NULL
            ORDER BY u.full_name ASC
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
    public function deleteUser(): void
    {
        \App\Core\Auth::requireRole(['Administrador']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método não permitido';
            exit;
        }

        $userId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $action = $_POST['action'] ?? ''; // 'delete' ou 'restore'
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);

        if ($userId <= 0) {
            $_SESSION['error'] = 'Utilizador inválido.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        // Impedir apagar a si próprio
        if ($userId === $currentUserId && $action === 'delete') {
            $_SESSION['error'] = 'Não podes apagar a tua própria conta.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        if ($action === 'delete') {
            $ok = \App\Models\User::softDelete($userId);
            $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Utilizador removido (soft delete).' : 'Erro ao remover utilizador.';
        } elseif ($action === 'restore') {
            $ok = \App\Models\User::restore($userId);
            $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Utilizador restaurado.' : 'Erro ao restaurar utilizador.';
        } else {
            $_SESSION['error'] = 'Ação inválida.';
        }

        header('Location: ' . $this->baseUrl . '?route=admin_users_list');
        exit;
    }

}
