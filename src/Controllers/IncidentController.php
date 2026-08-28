<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Incident;
use App\Models\Location;
use App\Models\Treatment;
use App\Models\Patient;

class IncidentController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    private function redirectWithFormError(string $message): void
    {
        $_SESSION['error'] = $message;
        $_SESSION['old_incident_form'] = $_POST;
        header('Location: ' . $this->baseUrl . '?route=incidents_new');
        exit;
    }

    public function create(): void
    {
        Auth::requireRole(['Enfermeiro']);

        $types          = Incident::getTypes();
        $locations      = Location::allActive();
        $treatmentTypes = Treatment::getTypes();

        require __DIR__ . '/../Views/incidents/create.php';
    }

public function store(): void
{
    Auth::requireRole(['Enfermeiro']);

    $user   = Auth::user();
    $userId = (int)$user['id'];

    $incidentTypeId    = ($_POST['incident_type_id'] ?? '') !== '' ? (int)$_POST['incident_type_id'] : 0;
    $incidentTypeInput = trim($_POST['incident_type_input'] ?? '');

    $locationId    = ($_POST['location_id'] ?? '') !== '' ? (int)$_POST['location_id'] : 0;
    $locationInput = trim($_POST['location_input'] ?? '');

    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');

    $patientName   = trim($_POST['patient_name'] ?? '');
    $patientDob    = trim($_POST['patient_dob'] ?? '');
    $patientGender = trim($_POST['patient_gender'] ?? '') ?: null;
    $patientIsEmployee = isset($_POST['patient_is_employee']) ? 1 : 0;

    $description = trim($_POST['description'] ?? '') ?: null;

    $addTreatment = isset($_POST['add_treatment']);
    $rawTreatmentTypeIds = $_POST['treatment_type_id'] ?? [];

    if (!is_array($rawTreatmentTypeIds)) {
        $rawTreatmentTypeIds = [$rawTreatmentTypeIds];
    }

    $treatmentTypeIds = [];
    $treatmentStatus    = in_array($_POST['treatment_status'] ?? '', ['em_curso','concluido'], true)
        ? $_POST['treatment_status']
        : 'concluido';
    $treatmentNotes     = trim($_POST['treatment_notes'] ?? '') ?: null;

    $patientNationality = trim($_POST['patient_nationality'] ?? '') ?: null;
    $patientAddress     = trim($_POST['patient_address'] ?? '') ?: null;
    $patientPostalCode  = trim($_POST['patient_postal_code'] ?? '') ?: null;
    $patientCity        = trim($_POST['patient_city'] ?? '') ?: null;
    $patientPhone       = trim($_POST['patient_phone'] ?? '') ?: null;
    $patientIdType      = trim($_POST['patient_id_type'] ?? '') ?: null;
    $patientIdNumber    = trim($_POST['patient_id_number'] ?? '') ?: null;
    $patientRefusedHospital = isset($_POST['patient_refused_hospital']) ? 1 : 0;
    $isHospitalTransfer = $addTreatment && isset($_POST['hospital_transfer']);

    if ($incidentTypeId <= 0 && $incidentTypeInput === '') {
        $this->redirectWithFormError('Tipo de acidente obrigatório.');
    }

    if ($locationId <= 0 && $locationInput === '') {
        $this->redirectWithFormError('Local obrigatório.');
    }

    if ($date === '' || $time === '') {
        $this->redirectWithFormError('Data e hora obrigatórias.');
    }

    if ($patientName === '' || $patientDob === '') {
        $this->redirectWithFormError('Nome e data de nascimento do utente são obrigatórios.');
    }

    // Corrigir formato DD-MM-YYYY para YYYY-MM-DD se necessário
    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $patientDob)) {
        [$d, $m, $y] = explode('-', $patientDob);
        $patientDob = "$y-$m-$d";
    }
    $patientDobDate = \DateTimeImmutable::createFromFormat('Y-m-d', $patientDob);
    $dobIsValid = $patientDobDate instanceof \DateTimeImmutable
        && $patientDobDate->format('Y-m-d') === $patientDob;

    if (!$dobIsValid
        || $patientDobDate > new \DateTimeImmutable('today')
        || $patientDobDate < new \DateTimeImmutable('1920-01-01')
    ) {
        $this->redirectWithFormError('A data de nascimento é inválida.');
    }

    if ($incidentTypeId <= 0) {
        $incidentTypeId = Incident::createTypeIfNotExists($incidentTypeInput);
    }

    if ($locationId <= 0) {
        $locationId = Location::createIfNotExists($locationInput);
    }

    if ($addTreatment) {
        foreach ($rawTreatmentTypeIds as $rawId) {
            $treatmentTypeId = (int)$rawId;
            if ($treatmentTypeId > 0) {
                $treatmentTypeIds[] = $treatmentTypeId;
            }
        }

        $treatmentTypeIds = array_values(array_unique($treatmentTypeIds));

        if ($treatmentTypeIds !== []) {
            $validTypeIds = array_map(
                static fn (array $t): int => (int)$t['id'],
                Treatment::getTypes()
            );
            $treatmentTypeIds = array_values(array_intersect($treatmentTypeIds, $validTypeIds));
        }

        if ($isHospitalTransfer) {
            $hospitalTransferTypeId = Treatment::getHospitalTransferTypeId();
            if ($hospitalTransferTypeId === null) {
                $this->redirectWithFormError('O tratamento de envio para o hospital não está configurado.');
            }

            if (!in_array($hospitalTransferTypeId, $treatmentTypeIds, true)) {
                $treatmentTypeIds[] = $hospitalTransferTypeId;
            }
        }

        if ($treatmentTypeIds === []) {
            $this->redirectWithFormError('Selecione pelo menos um tratamento.');
        }
    }

    $occurredAt = $date . ' ' . $time . ':00';

    $pdo = Database::getConnection();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO incidents
            (user_id, incident_type_id, location_id, occurred_at, description)
            VALUES
            (:user_id, :type, :loc, :occurred, :descr)
        ");

        $stmt->execute([
            ':user_id'  => $userId,
            ':type'     => $incidentTypeId,
            ':loc'      => $locationId,
            ':occurred' => $occurredAt,
            ':descr'    => $description,
        ]);

        $incidentId = (int)$pdo->lastInsertId();

        $patientId = Patient::createBasic(
            $incidentId,
            $patientName,
            $patientDob,
            $patientGender,
            $patientIsEmployee
        );

        $updateIncident = $pdo->prepare("
            UPDATE incidents
            SET patient_id = :patient_id
            WHERE id = :incident_id
        ");

        $updateIncident->execute([
            ':patient_id'  => $patientId,
            ':incident_id' => $incidentId,
        ]);

        foreach ($treatmentTypeIds as $treatmentTypeId) {
            Treatment::create([
                'incident_id'       => $incidentId,
                'user_id'           => $userId,
                'treatment_type_id' => $treatmentTypeId,
                'status'            => $treatmentStatus,
                'notes'             => $treatmentNotes,
            ]);
        }

        if ($isHospitalTransfer) {
            Patient::updateHospitalData(
                $patientId,
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

        $pdo->commit();

        unset($_SESSION['old_incident_form']);

        $_SESSION['success'] = 'Acidente registado com sucesso.';
        header('Location: '.$this->baseUrl.'?route=admin_incident_detail&id='.$incidentId);
        exit;

    } catch (\Throwable $e) {
        $pdo->rollBack();
        $this->redirectWithFormError('Erro ao guardar ocorrência.');
    }
}
    public function insuranceTerm()
    {
        Auth::requireRole(['Administrador','Enfermeiro']);

        $id = (int)($_GET['id'] ?? 0);

        $incident = Incident::findWithDetailsForAdmin($id);
        $treatments = Incident::getTreatmentsForIncident($id);

        if (!$incident) {
            die('Ocorrência não encontrada');
        }

        $insuranceDescription = trim((string)($incident['description'] ?? ''));
        foreach ($treatments as $treatment) {
            if (
                isset($treatment['treatment_type_name'])
                && strcasecmp((string)$treatment['treatment_type_name'], 'Enviado para hospital') === 0
            ) {
                $hospitalNotes = trim((string)($treatment['notes'] ?? ''));
                if ($hospitalNotes !== '') {
                    $insuranceDescription = $hospitalNotes;
                }
            }
        }

        $incident['insurance_description'] = $insuranceDescription;

        require_once __DIR__.'/../../vendor/autoload.php';

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);

        ob_start();
        require __DIR__.'/../Views/incidents/insurance_term_pdf.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $dompdf->stream(
            "termo-seguro-{$incident['id']}.pdf",
            ["Attachment" => false] // false = abre no browser
        );

        exit;
    }

}
