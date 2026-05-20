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
        $onlineNurses = $role === 'Administrador' ? $this->fetchOnlineNurses($pdo) : [];
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

    /**
     * Lista enfermeiros com sessão ativa (ficheiros de sessão recentes).
     * @return array<int, array{id:int, name:string}>
     */
    private function fetchOnlineNurses(PDO $pdo): array
    {
        $sessionPath = session_save_path();
        if ($sessionPath === '' || !is_dir($sessionPath)) {
            return [];
        }

        $lifetime = (int)ini_get('session.gc_maxlifetime');
        if ($lifetime <= 0) {
            $lifetime = 28800;
        }
        $threshold = time() - $lifetime;

        $files = glob($sessionPath . DIRECTORY_SEPARATOR . 'sess_*');
        if (!is_array($files) || $files === []) {
            return [];
        }

        $currentId = session_id();
        $savedSession = $_SESSION ?? [];
        $userIds = [];

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $mtime = @filemtime($file);
            if ($mtime === false || $mtime < $threshold) {
                continue;
            }
            // ignorar a sessão do próprio pedido (já desserializada)
            if ($currentId !== '' && substr(basename($file), 5) === $currentId) {
                if (($_SESSION['role'] ?? '') === 'Enfermeiro' && !empty($_SESSION['user_id'])) {
                    $userIds[(int)$_SESSION['user_id']] = true;
                }
                continue;
            }

            $raw = @file_get_contents($file);
            if ($raw === false || $raw === '') {
                continue;
            }

            $_SESSION = [];
            if (@session_decode($raw) === false) {
                continue;
            }

            if (($_SESSION['role'] ?? '') === 'Enfermeiro' && !empty($_SESSION['user_id'])) {
                $userIds[(int)$_SESSION['user_id']] = true;
            }
        }

        // restaurar a sessão original
        $_SESSION = $savedSession;

        if ($userIds === []) {
            return [];
        }

        $ids = array_keys($userIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id, full_name FROM users WHERE id IN ($placeholders) AND deleted_at IS NULL ORDER BY full_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'id' => (int)$row['id'],
                'name' => (string)$row['full_name'],
            ];
        }

        return $result;
    }

    private function fetchRecentIncidents(PDO $pdo, int $limit = 6): array
    {
        $sql = "
            SELECT
                i.id,
                (SELECT COUNT(*) FROM incidents i2 WHERE i2.id <= i.id) AS episode_number,
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
