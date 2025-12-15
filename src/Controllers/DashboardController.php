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
        $role = $_SESSION['role'] ?? '';
        $user = $_SESSION['user_id'] ?? null;
        
        // Admin / Manager / outros veem números globais
        $AcidentesHoje = \App\Models\Incident::countToday(null);
        $tratamentosEmCurso = \App\Models\Treatment::countInProgress(null);        

        $ultimoAcesso = $_SESSION['last_login'] ?? null; // ou busca no model/users

        // garantir que a view tem as variáveis
        require __DIR__ . '/../Views/dashboard/index.php';
    }

}
