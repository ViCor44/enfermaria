<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Incident;
use App\Models\Treatment;

class TreatmentController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function create(): void
    {
        Auth::requireRole(['Enfermeiro']);

        $incidentId = (int)($_GET['incident_id'] ?? 0);
        if ($incidentId <= 0) {
            $_SESSION['error'] = 'Acidente inválido.';
            header('Location: ' . $this->baseUrl . '?route=incidents_my');
            return;
        }

        $incident = Incident::findById($incidentId);
        if (!$incident) {
            $_SESSION['error'] = 'Acidente não encontrado.';
            header('Location: ' . $this->baseUrl . '?route=incidents_my');
            return;
        }

        $types = Treatment::getTypes();

        require __DIR__ . '/../Views/treatments/create.php';
    }

    public function store(): void
    {
        Auth::requireRole(['Enfermeiro']);

        $user = Auth::user();
        $userId = (int)$user['id'];

        $incidentId      = (int)($_POST['incident_id'] ?? 0);
        $treatmentTypeId = (int)($_POST['treatment_type_id'] ?? 0);
        $status          = $_POST['status'] ?? 'em_curso';
        $notes           = trim($_POST['notes'] ?? '');

        if ($incidentId <= 0 || $treatmentTypeId <= 0) {
            $_SESSION['error'] = 'Dados de tratamento incompletos.';
            header('Location: ' . $this->baseUrl . '?route=incidents_my');
            return;
        }

        if (!in_array($status, ['em_curso','concluido'], true)) {
            $status = 'em_curso';
        }

        Treatment::create([
            'incident_id'       => $incidentId,
            'user_id'           => $userId,
            'treatment_type_id' => $treatmentTypeId,
            'status'            => $status,
            'notes'             => $notes,
        ]);

        $_SESSION['success'] = 'Tratamento registado com sucesso.';
        header('Location: ' . $this->baseUrl . '?route=treatments_my');
    }

    public function changeStatus(): void
    {
        // Só enfermeiros podem alterar o estado dos tratamentos
        Auth::requireRole(['Enfermeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl . '?route=treatments_my');
            exit;
        }

        $treatmentId = isset($_POST['treatment_id']) ? (int)$_POST['treatment_id'] : 0;
        $newStatus   = $_POST['status'] ?? '';

        if ($treatmentId <= 0 || !in_array($newStatus, ['em_curso','concluido'], true)) {
            $_SESSION['error'] = 'Pedido inválido.';
            header('Location: ' . $this->baseUrl . '?route=treatments_my');
            exit;
        }

        $user = Auth::user();
        $userId = (int)$user['id'];

        // Verificar que o tratamento pertence ao enfermeiro logado
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("SELECT user_id FROM treatments WHERE id = ? LIMIT 1");
        $stmt->execute([$treatmentId]);
        $ownerId = (int)$stmt->fetchColumn();

        if ($ownerId !== $userId) {
            $_SESSION['error'] = 'Só o enfermeiro responsável pode alterar este tratamento.';
            header('Location: ' . $this->baseUrl . '?route=treatments_my');
            exit;
        }

        $ok = \App\Models\Treatment::setStatus($treatmentId, $newStatus);

        if ($ok) {
            $_SESSION['success'] = $newStatus === 'concluido' ? 'Tratamento marcado como concluído.' : 'Estado atualizado.';
        } else {
            $_SESSION['error'] = 'Erro ao atualizar estado.';
        }

        header('Location: ' . $this->baseUrl . '?route=treatments_my');
        exit;
    }

    public function conclude(): void
    {
        // verifica role (enfermeiros e admins podem concluir)
        Auth::requireRole(['Enfermeiro', 'Administrador']);

        $user = Auth::user();
        $userId = (int)$user['id'];

        // CSRF protection recomendada (ver se tens token)
        $treatmentId = isset($_POST['treatment_id']) ? (int)$_POST['treatment_id'] : 0;
        if ($treatmentId <= 0) {
            $_SESSION['error'] = 'Tratamento inválido.';
            header('Location: ' . $this->baseUrl . '?route=treatments_my');
            exit;
        }

        try {
            $ok = Treatment::conclude($treatmentId, $userId);
            if ($ok) {
                $_SESSION['success'] = 'Tratamento concluído com sucesso.';
            } else {
                $_SESSION['info'] = 'O tratamento já estava concluído.';
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao concluir tratamento.';
            // error_log($e->getMessage()); // opcional
        }

        // volta para a lista dos tratamentos (ou para a página que achas melhor)
        header('Location: ' . $this->baseUrl . '?route=treatments_my');
        exit;
    }

}
