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

        $types     = Incident::getTypes();
        $locations = Location::allActive();
        $treatmentTypes = array_values(array_filter(
            Treatment::getTypes(),
            static fn (array $type): bool => strcasecmp((string)$type['name'], 'Enviado para hospital') !== 0
        ));

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
    $rawTreatmentTypeIds = $_POST['treatment_type_id'] ?? [];
    if (!is_array($rawTreatmentTypeIds)) {
        $rawTreatmentTypeIds = [$rawTreatmentTypeIds];
    }
    $rawTreatmentNotes = $_POST['treatment_notes'] ?? [];
    if (!is_array($rawTreatmentNotes)) {
        $rawTreatmentNotes = [$rawTreatmentNotes];
    }

    $patientNationality = trim($_POST['patient_nationality'] ?? '') ?: null;
    $patientAddress     = trim($_POST['patient_address'] ?? '') ?: null;
    $patientPostalCode  = trim($_POST['patient_postal_code'] ?? '') ?: null;
    $patientCity        = trim($_POST['patient_city'] ?? '') ?: null;
    $patientPhone       = trim($_POST['patient_phone'] ?? '') ?: null;
    $patientIdType      = trim($_POST['patient_id_type'] ?? '') ?: null;
    $patientIdNumber    = trim($_POST['patient_id_number'] ?? '') ?: null;
    $patientRefusedHospital = isset($_POST['patient_refused_hospital']) ? 1 : 0;
    $isHospitalTransfer = isset($_POST['hospital_transfer']);
    $bodyMarksInput = json_decode(trim((string)($_POST['body_marks'] ?? '[]')), true);

    if (!is_array($bodyMarksInput) || count($bodyMarksInput) > 20) {
        $this->redirectWithFormError('As marcações corporais são inválidas.');
    }

    $bodyMarks = [];
    $validBodyMarkTypes = ['contusion', 'wound', 'burn', 'pain', 'insect_bite', 'epistaxis', 'other'];
    foreach ($bodyMarksInput as $mark) {
        if (
            !is_array($mark)
            || !in_array($mark['view'] ?? null, ['front', 'back'], true)
            || !in_array($mark['type'] ?? null, $validBodyMarkTypes, true)
            || !is_numeric($mark['x'] ?? null)
            || !is_numeric($mark['y'] ?? null)
        ) {
            $this->redirectWithFormError('As marcações corporais são inválidas.');
        }

        $x = round((float)$mark['x'], 2);
        $y = round((float)$mark['y'], 2);
        if ($x < 0 || $x > 100 || $y < 0 || $y > 100) {
            $this->redirectWithFormError('As marcações corporais são inválidas.');
        }

        $bodyMarks[] = [
            'view' => $mark['view'],
            'type' => $mark['type'],
            'x' => $x,
            'y' => $y,
        ];
    }

    $bodyMarksJson = $bodyMarks === []
        ? null
        : json_encode($bodyMarks, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    $validTreatmentTypeIds = [];
    foreach (Treatment::getTypes() as $type) {
        if (strcasecmp((string)$type['name'], 'Enviado para hospital') !== 0) {
            $validTreatmentTypeIds[] = (int)$type['id'];
        }
    }

    $treatmentsToCreate = [];
    foreach ($rawTreatmentTypeIds as $index => $rawTreatmentTypeId) {
        $treatmentTypeId = (int)$rawTreatmentTypeId;
        if (
            $treatmentTypeId > 0
            && in_array($treatmentTypeId, $validTreatmentTypeIds, true)
            && !isset($treatmentsToCreate[$treatmentTypeId])
        ) {
            $notes = trim((string)($rawTreatmentNotes[$index] ?? ''));
            $treatmentsToCreate[$treatmentTypeId] = $notes !== '' ? $notes : null;
        }
    }

    if ($treatmentsToCreate === []) {
        $this->redirectWithFormError('Selecione pelo menos um tratamento prestado.');
    }

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

    if ($isHospitalTransfer && (
        $patientNationality === null
        || $patientAddress === null
        || $patientPostalCode === null
        || $patientCity === null
        || $patientPhone === null
    )) {
        $this->redirectWithFormError('Preencha a nacionalidade, morada, código postal, cidade e telefone para o envio ao hospital.');
    }

    if ($incidentTypeId <= 0) {
        $incidentTypeId = Incident::createTypeIfNotExists($incidentTypeInput);
    }

    if ($locationId <= 0) {
        $locationId = Location::createIfNotExists($locationInput);
    }

    $hospitalTransferTypeId = null;
    if ($isHospitalTransfer) {
        $hospitalTransferTypeId = Treatment::getHospitalTransferTypeId();
        if ($hospitalTransferTypeId === null) {
            $this->redirectWithFormError('O envio para o hospital não está configurado.');
        }
    }

    $occurredAt = $date . ' ' . $time . ':00';

    $pdo = Database::getConnection();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO incidents
            (user_id, incident_type_id, location_id, occurred_at, description, body_marks)
            VALUES
            (:user_id, :type, :loc, :occurred, :descr, :body_marks)
        ");

        $stmt->execute([
            ':user_id'  => $userId,
            ':type'     => $incidentTypeId,
            ':loc'      => $locationId,
            ':occurred' => $occurredAt,
            ':descr'    => $description,
            ':body_marks' => $bodyMarksJson,
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

        foreach ($treatmentsToCreate as $treatmentTypeId => $treatmentNotes) {
            Treatment::create([
                'incident_id'       => $incidentId,
                'user_id'           => $userId,
                'treatment_type_id' => $treatmentTypeId,
                'status'            => 'concluido',
                'notes'             => $treatmentNotes,
            ]);
        }

        if ($hospitalTransferTypeId !== null) {
            Treatment::create([
                'incident_id'       => $incidentId,
                'user_id'           => $userId,
                'treatment_type_id' => $hospitalTransferTypeId,
                'status'            => 'concluido',
                'notes'             => null,
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

        $_SESSION['success'] = 'Ocorrência registada com sucesso.';
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
