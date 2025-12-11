<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Treatment;
use PDO;

class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();

        $user = Auth::user();
        $userId = (int)$user['id'];
        $nome   = $user['name'] ?? 'Utilizador';
        $role   = $user['role'] ?? '';

        $pdo = Database::getConnection();

        // Incidentes de hoje
        if ($role === 'Enfermeiro') {
            // enfermeiro vê só os incidentes registados por si
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM incidents
                WHERE user_id = ?
                  AND DATE(occurred_at) = CURDATE()
            ");
            $stmt->execute([$userId]);
        } else {
            // Admin / Manager vêem o total global de hoje
            $stmt = $pdo->query("
                SELECT COUNT(*)
                FROM incidents
                WHERE DATE(occurred_at) = CURDATE()
            ");
        }
        $incidentsToday = (int)$stmt->fetchColumn();

        // Tratamentos em curso – por agora ainda não temos tabela de tratamentos
        if ($role === 'Enfermeiro') {
            $treatmentsInProgress = \App\Models\Treatment::countInProgress($userId);
        } else {
            $treatmentsInProgress = \App\Models\Treatment::countInProgress(null); // total global
        }

        // Último acesso anterior ao login atual
        $lastLogin = $_SESSION['last_login'] ?? null;

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
