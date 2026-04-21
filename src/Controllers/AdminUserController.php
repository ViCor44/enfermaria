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

    public function openNurseSession(): void
    {
        Auth::requireRole(['Administrador']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método não permitido';
            exit;
        }

        $targetUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $adminPassword = $_POST['admin_password'] ?? '';
        $adminId = (int)($_SESSION['user_id'] ?? 0);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if ($targetUserId <= 0 || $adminPassword === '') {
            $_SESSION['error'] = 'Indique o utilizador e a password do administrador.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        if ($targetUserId === $adminId) {
            $_SESSION['error'] = 'Não pode abrir uma sessão delegada para a sua própria conta.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        $admin = User::findByIdWithPassword($adminId);
        if (!$admin || ($admin['role_name'] ?? '') !== 'Administrador') {
            $_SESSION['error'] = 'Sessão inválida para delegação.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        if (!password_verify($adminPassword, $admin['password_hash'] ?? '')) {
            \App\Helpers\Logger::login("ADMIN OPEN SESSION FAIL (wrong admin password) | admin_id='{$adminId}' | target_user_id='{$targetUserId}' | ip='{$ip}'");
            $_SESSION['error'] = 'Password de administrador inválida.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        $target = User::findByIdWithPassword($targetUserId);
        if (!$target || !empty($target['deleted_at'])) {
            $_SESSION['error'] = 'Utilizador alvo não está disponível.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        if ((int)($target['approved'] ?? 0) !== 1) {
            $_SESSION['error'] = 'Só é possível abrir sessão de utilizadores aprovados.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        if (($target['role_name'] ?? '') !== 'Enfermeiro') {
            $_SESSION['error'] = 'Esta ação está disponível apenas para contas de enfermeiro.';
            header('Location: ' . $this->baseUrl . '?route=admin_users_list');
            exit;
        }

        $_SESSION['delegated_admin_context'] = [
            'user_id' => $admin['id'],
            'user_name' => $admin['full_name'],
            'role' => $admin['role_name'],
            'last_login' => $admin['last_login'] ?? null,
        ];

        session_regenerate_id(true);

        $_SESSION['last_login'] = $target['last_login'] ?? null;
        $_SESSION['user_id'] = $target['id'];
        $_SESSION['role'] = $target['role_name'];
        $_SESSION['user_name'] = $target['full_name'];
        $_SESSION['delegated_by_admin'] = true;
        $_SESSION['delegated_admin_name'] = $admin['full_name'];
        $_SESSION['delegated_admin_id'] = (int)$admin['id'];

        User::updateLastLogin((int)$target['id']);

        \App\Helpers\Logger::login("ADMIN OPEN SESSION SUCCESS | admin_id='{$adminId}' | nurse_id='{$target['id']}' | ip='{$ip}'");

        $_SESSION['success'] = 'Sessão do enfermeiro aberta com sucesso.';
        header('Location: ' . $this->baseUrl . '?route=dashboard');
        exit;
    }

    public function restoreAdminSession(): void
    {
        $ctx = $_SESSION['delegated_admin_context'] ?? null;
        if (!$ctx || empty($_SESSION['delegated_by_admin'])) {
            $_SESSION['error'] = 'Não existe sessão delegada ativa para restaurar.';
            header('Location: ' . $this->baseUrl . '?route=login');
            exit;
        }

        $delegatedUserId = (int)($_SESSION['user_id'] ?? 0);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$ctx['user_id'];
        $_SESSION['user_name'] = (string)$ctx['user_name'];
        $_SESSION['role'] = (string)$ctx['role'];
        $_SESSION['last_login'] = $ctx['last_login'] ?? null;

        unset($_SESSION['delegated_by_admin'], $_SESSION['delegated_admin_name'], $_SESSION['delegated_admin_id'], $_SESSION['delegated_admin_context']);

        \App\Helpers\Logger::login("ADMIN SESSION RESTORED | admin_id='{$_SESSION['user_id']}' | delegated_user_id='{$delegatedUserId}' | ip='{$ip}'");

        $_SESSION['success'] = 'Sessão de administrador restaurada.';
        header('Location: ' . $this->baseUrl . '?route=admin_users_list');
        exit;
    }

}
