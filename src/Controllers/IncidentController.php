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

        // ID do tipo "Enviado para hospital"
        $hospitalTreatmentTypeId = Treatment::getHospitalTransferTypeId();

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
    $patientHospitalDob = trim($_POST['patient_hospital_dob'] ?? '');
    if ($patientDob === '' && $patientHospitalDob !== '') {
        $patientDob = $patientHospitalDob;
    }
    $patientGender = trim($_POST['patient_gender'] ?? '') ?: null;
    $patientIsEmployee = isset($_POST['patient_is_employee']) ? 1 : 0;

    $description = trim($_POST['description'] ?? '') ?: null;

    $addTreatment = isset($_POST['add_treatment']);
    $rawTreatmentTypeIds = $_POST['treatment_type_id'] ?? [];
    $rawTreatmentTypeInputs = $_POST['treatment_type_input'] ?? [];

    if (!is_array($rawTreatmentTypeIds)) {
        $rawTreatmentTypeIds = [$rawTreatmentTypeIds];
    }

    if (!is_array($rawTreatmentTypeInputs)) {
        $rawTreatmentTypeInputs = [$rawTreatmentTypeInputs];
    }

    $treatmentTypeIds = [];
    $treatmentTypeInputs = [];
    $treatmentStatus    = in_array($_POST['treatment_status'] ?? '', ['em_curso','concluido'], true)
        ? $_POST['treatment_status']
        : 'em_curso';
    $treatmentNotes     = trim($_POST['treatment_notes'] ?? '') ?: null;

    $patientNationality = trim($_POST['patient_nationality'] ?? '') ?: null;
    $patientAddress     = trim($_POST['patient_address'] ?? '') ?: null;
    $patientPostalCode  = trim($_POST['patient_postal_code'] ?? '') ?: null;
    $patientCity        = trim($_POST['patient_city'] ?? '') ?: null;
    $patientPhone       = trim($_POST['patient_phone'] ?? '') ?: null;
    $patientIdType      = trim($_POST['patient_id_type'] ?? '') ?: null;
    $patientIdNumber    = trim($_POST['patient_id_number'] ?? '') ?: null;
    $patientRefusedHospital = isset($_POST['patient_refused_hospital']) ? 1 : 0;

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

    $patientDobDate = \DateTimeImmutable::createFromFormat('d/m/Y', $patientDob);
    $dobErrors = \DateTimeImmutable::getLastErrors();
    $dobIsValid = $patientDobDate instanceof \DateTimeImmutable
        && $dobErrors['warning_count'] === 0
        && $dobErrors['error_count'] === 0
        && $patientDobDate->format('d/m/Y') === $patientDob;

    if (!$dobIsValid || $patientDobDate > new \DateTimeImmutable('today') || $patientDobDate < new \DateTimeImmutable('1920-01-01')) {
        $this->redirectWithFormError('A data de nascimento é inválida.');
    }

    if ($incidentTypeId <= 0) {
        $incidentTypeId = Incident::createTypeIfNotExists($incidentTypeInput);
    }

    if ($locationId <= 0) {
        $locationId = Location::createIfNotExists($locationInput);
    }

    if ($addTreatment) {
        $maxTreatments = max(count($rawTreatmentTypeIds), count($rawTreatmentTypeInputs));

        for ($index = 0; $index < $maxTreatments; $index++) {
            $treatmentTypeId = (int)($rawTreatmentTypeIds[$index] ?? 0);
            $treatmentTypeInput = trim((string)($rawTreatmentTypeInputs[$index] ?? ''));

            if ($treatmentTypeInput !== '') {
                $treatmentTypeInputs[] = $treatmentTypeInput;
            }

            if ($treatmentTypeId <= 0 && $treatmentTypeInput !== '') {
                $treatmentTypeId = Treatment::createTypeIfNotExists($treatmentTypeInput);
            }

            if ($treatmentTypeId > 0) {
                $treatmentTypeIds[] = $treatmentTypeId;
            }
        }

        $treatmentTypeIds = array_values(array_unique($treatmentTypeIds));

        if ($treatmentTypeIds === []) {
            $this->redirectWithFormError('Selecione pelo menos um tratamento.');
        }
    }

    $occurredAt = $date . ' ' . $time . ':00';

    $hospitalTypeId = Treatment::getHospitalTransferTypeId();

    $hasHospitalByName = false;
    foreach ($treatmentTypeInputs as $inputName) {
        if (strcasecmp($inputName, 'Enviado para hospital') === 0) {
            $hasHospitalByName = true;
            break;
        }
    }

    $isHospitalTreatment =
        ($hospitalTypeId && in_array((int)$hospitalTypeId, $treatmentTypeIds, true)) ||
        $hasHospitalByName;

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

        if ($isHospitalTreatment) {
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
        header('Location: '.$this->baseUrl.'?route=admin_incidents');
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
