<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Treatment;
use DateTimeImmutable;
use PDO;

class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();
        $pdo = Database::getConnection();

        $AcidentesHoje = \App\Models\Incident::countToday(null);
        $tratamentosEmCurso = \App\Models\Treatment::countInProgress(null);

        $incidentsYesterday = $this->countIncidentsForDate($pdo, new DateTimeImmutable('yesterday'));
        $incidentTrend = $this->buildTrend($AcidentesHoje, $incidentsYesterday);

        $completedTreatmentsToday = $this->countTreatmentsCompletedForDate($pdo, new DateTimeImmutable('today'));
        $role = (string)($_SESSION['role'] ?? '');
        $pendingApprovals = $role === 'Administrador' ? $this->countPendingApprovals($pdo) : 0;
        $recentIncidents = $this->fetchRecentIncidents($pdo, 6);

        $lastLoginRaw = $_SESSION['last_login'] ?? null;
        $lastLogin = $this->formatDateTime($lastLoginRaw);
        $currentDate = (new DateTimeImmutable('today'))->format('d/m/Y');

        // garantir que a view tem as variáveis
        require __DIR__ . '/../Views/dashboard/index.php';
    }

    private function countIncidentsForDate(PDO $pdo, DateTimeImmutable $date): int
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM incidents WHERE DATE(occurred_at) = :ref_date");
        $stmt->execute([':ref_date' => $date->format('Y-m-d')]);

        return (int)$stmt->fetchColumn();
    }

    private function countTreatmentsCompletedForDate(PDO $pdo, DateTimeImmutable $date): int
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM treatments WHERE status = 'concluido' AND DATE(concluded_at) = :ref_date");
        $stmt->execute([':ref_date' => $date->format('Y-m-d')]);

        return (int)$stmt->fetchColumn();
    }

    private function countPendingApprovals(PDO $pdo): int
    {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE approved = 0 AND deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }

    private function fetchRecentIncidents(PDO $pdo, int $limit = 6): array
    {
        $sql = "
            SELECT
                i.id,
                i.occurred_at,
                it.name AS incident_type_name,
                l.name AS location_name,
                u.full_name AS nurse_name
            FROM incidents i
            LEFT JOIN incident_types it ON it.id = i.incident_type_id
            LEFT JOIN locations l ON l.id = i.location_id
            LEFT JOIN users u ON u.id = i.user_id
            ORDER BY i.occurred_at DESC
            LIMIT :limit
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildTrend(int $current, int $previous): array
    {
        if ($previous === 0 && $current === 0) {
            return ['label' => 'Sem variação face a ontem', 'value' => '0%', 'direction' => 'neutral'];
        }

        if ($previous === 0) {
            return ['label' => 'Subida face a ontem', 'value' => '+100%', 'direction' => 'up'];
        }

        $delta = (($current - $previous) / $previous) * 100;
        if ($delta > 0) {
            return ['label' => 'Subida face a ontem', 'value' => '+' . (string)round($delta) . '%', 'direction' => 'up'];
        }

        if ($delta < 0) {
            return ['label' => 'Descida face a ontem', 'value' => (string)round($delta) . '%', 'direction' => 'down'];
        }

        return ['label' => 'Sem variação face a ontem', 'value' => '0%', 'direction' => 'neutral'];
    }

    private function formatDateTime(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return 'Sem registo';
        }

        $date = date_create($value);
        if ($date === false) {
            return $value;
        }

        return $date->format('d/m/Y H:i');
    }

}
