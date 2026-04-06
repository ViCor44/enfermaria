<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Incident;
use App\Models\Patient;
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
        $hospitalTreatmentTypeId = Treatment::getHospitalTransferTypeId();
        $patientHospitalData = null;

        if (!empty($incident['patient_id'])) {
            $patientHospitalData = Patient::findById((int)$incident['patient_id']);
        }

        require __DIR__ . '/../Views/treatments/create.php';
    }

public function store(): void
{
    Auth::requireRole(['Enfermeiro']);

    $user   = Auth::user();
    $userId = (int)$user['id'];

    $incidentId = (int)($_POST['incident_id'] ?? 0);
    $status     = $_POST['status'] ?? 'em_curso';
    $notes      = trim($_POST['notes'] ?? '') ?: null;

    $rawTreatmentTypeIds = $_POST['treatment_type_id'] ?? ($_POST['treatment_type_ids'] ?? []);
    $rawTreatmentTypeInputs = $_POST['treatment_type_input'] ?? [];

    if (!is_array($rawTreatmentTypeIds)) {
        $rawTreatmentTypeIds = [$rawTreatmentTypeIds];
    }

    if (!is_array($rawTreatmentTypeInputs)) {
        $rawTreatmentTypeInputs = [$rawTreatmentTypeInputs];
    }

    $treatmentTypeIds = [];
    $maxTreatments = max(count($rawTreatmentTypeIds), count($rawTreatmentTypeInputs));

    for ($index = 0; $index < $maxTreatments; $index++) {
        $treatmentTypeId = (int)($rawTreatmentTypeIds[$index] ?? 0);
        $treatmentTypeInput = trim((string)($rawTreatmentTypeInputs[$index] ?? ''));

        if ($treatmentTypeId <= 0 && $treatmentTypeInput !== '') {
            $treatmentTypeId = Treatment::createTypeIfNotExists($treatmentTypeInput);
        }

        if ($treatmentTypeId > 0) {
            $treatmentTypeIds[] = $treatmentTypeId;
        }
    }

    $treatmentTypeIds = array_values(array_unique($treatmentTypeIds));

    if ($incidentId <= 0 || $treatmentTypeIds === []) {
        $_SESSION['error'] = 'Dados inválidos.';
        header('Location: '.$this->baseUrl.'?route=admin_incidents');
        exit;
    }

    $pdo = Database::getConnection();

    try {

        $pdo->beginTransaction();

        /* -------------------- CRIAR TRATAMENTO -------------------- */

        foreach ($treatmentTypeIds as $treatmentTypeId) {
            Treatment::create([
                'incident_id'       => $incidentId,
                'user_id'           => $userId,
                'treatment_type_id' => $treatmentTypeId,
                'status'            => $status,
                'notes'             => $notes,
            ]);
        }

        /* -------------------- DETETAR SE É HOSPITAL -------------------- */

        $hospitalTypeId = Treatment::getHospitalTransferTypeId();

        $isHospitalTreatment =
            $hospitalTypeId && in_array((int)$hospitalTypeId, $treatmentTypeIds, true);

        /* -------------------- UPDATE PACIENTE -------------------- */

        if ($isHospitalTreatment) {

            $patientNationality = trim($_POST['patient_nationality'] ?? '') ?: null;
            $patientAddress     = trim($_POST['patient_address'] ?? '') ?: null;
            $patientPostalCode  = trim($_POST['patient_postal_code'] ?? '') ?: null;
            $patientCity        = trim($_POST['patient_city'] ?? '') ?: null;
            $patientPhone       = trim($_POST['patient_phone'] ?? '') ?: null;
            $patientDob         = trim($_POST['patient_dob'] ?? '') ?: null;
            $patientIdType      = trim($_POST['patient_id_type'] ?? '') ?: null;
            $patientIdNumber    = trim($_POST['patient_id_number'] ?? '') ?: null;

            $patientRefusedHospital = isset($_POST['patient_refused_hospital']) ? 1 : 0;

            /* obter paciente associado ao incidente */

            $incident = Incident::find($incidentId);

            if (!empty($incident['patient_id'])) {

                Patient::updateHospitalData(
                    (int)$incident['patient_id'],
                    $patientNationality,
                    $patientAddress,
                    $patientPostalCode,
                    $patientCity,
                    $patientPhone,
                    $patientDob,
                    $patientIdType,
                    $patientIdNumber,
                    $patientRefusedHospital
                );

            }

        }

        $pdo->commit();

        $_SESSION['success'] = count($treatmentTypeIds) === 1
            ? 'Tratamento registado com sucesso.'
            : 'Tratamentos registados com sucesso.';
        header('Location: '.$this->baseUrl.'?route=admin_incident_detail&id='.$incidentId);
        exit;

    } catch (\Throwable $e) {

        $pdo->rollBack();

        die($e->getMessage());
    }
}
    public function changeStatus(): void
    {
        // Só enfermeiros podem alterar o estado dos tratamentos
        Auth::requireRole(['Enfermeiro']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->baseUrl . '?route=admin_treatments');
            exit;
        }

        $treatmentId = isset($_POST['treatment_id']) ? (int)$_POST['treatment_id'] : 0;
        $newStatus   = $_POST['status'] ?? '';

        if ($treatmentId <= 0 || !in_array($newStatus, ['em_curso','concluido'], true)) {
            $_SESSION['error'] = 'Pedido inválido.';
            header('Location: ' . $this->baseUrl . '?route=admin_treatments');
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
            header('Location: ' . $this->baseUrl . '?route=admin_treatments');
            exit;
        }

        $ok = \App\Models\Treatment::setStatus($treatmentId, $newStatus);

        if ($ok) {
            $_SESSION['success'] = $newStatus === 'concluido' ? 'Tratamento marcado como concluído.' : 'Estado atualizado.';
        } else {
            $_SESSION['error'] = 'Erro ao atualizar estado.';
        }

        header('Location: ' . $this->baseUrl . '?route=admin_treatments');
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
        $conclusionNotes = trim((string)($_POST['conclusion_notes'] ?? ''));
        if ($conclusionNotes === '') {
            $conclusionNotes = null;
        }

        if ($treatmentId <= 0) {
            $_SESSION['error'] = 'Tratamento inválido.';
            header('Location: ' . $this->baseUrl . '?route=admin_treatments');
            exit;
        }

        try {
            $ok = Treatment::conclude($treatmentId, $userId, $conclusionNotes);
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
        header('Location: ' . $this->baseUrl . '?route=admin_treatments');
        exit;
    }

}
