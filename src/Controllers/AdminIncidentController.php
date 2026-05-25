<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Incident;
use App\Models\Location;
use App\Models\Patient;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\HospitalFollowup;

class AdminIncidentController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    private function canCurrentUserSeePatient(array $incident): bool
    {
        $role = $_SESSION['role'] ?? '';
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);

        if ($role === 'Administrador') {
            return true;
        }

        if ($role === 'Enfermeiro') {
            $responsavelId = (int)($incident['user_id'] ?? 0);
            return $responsavelId === $currentUserId;
        }

        return false;
    }

    public function index(): void
    {
        Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

        $fromDate   = $_GET['from'] ?? '';
        $toDate     = $_GET['to'] ?? '';
        $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
        $episode    = isset($_GET['episode']) ? trim($_GET['episode']) : '';
        $typeId     = isset($_GET['type_id']) ? (int)$_GET['type_id'] : 0;

        $locations  = \App\Models\Location::allActive();

        $incidents = \App\Models\Incident::search([
            'fromDate'   => $fromDate !== '' ? $fromDate : null,
            'toDate'     => $toDate !== '' ? $toDate : null,
            'locationId' => $locationId > 0 ? $locationId : null,
            'episode'    => $episode !== '' ? $episode : null,
            'typeId'     => $typeId > 0 ? $typeId : null,
        ]);

        require __DIR__ . '/../Views/admin/incidents_list.php';
    }

    public function show(): void
    {
        Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'Acidente inválido.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        $incident = Incident::findWithDetailsForAdmin($id);

        $documents = Incident::getDocuments($id);

        if (!$incident) {
            $_SESSION['error'] = 'Acidente não encontrado.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        $treatments = Incident::getTreatmentsForIncident($id);
        $role = $_SESSION['role'] ?? '';
        $canSeePatient = false;
        $canGenerateHospitalDocs = in_array($role, ['Administrador', 'Enfermeiro'], true);

        $followups = HospitalFollowup::findByIncident($id);

        $canSeePatient = $this->canCurrentUserSeePatient($incident);

        require __DIR__ . '/../Views/admin/incidents_detail.php';
    }

    public function editPatient(): void
    {
        Auth::requireRole(['Administrador', 'Enfermeiro']);

        $incidentId = isset($_GET['incident_id']) ? (int)$_GET['incident_id'] : 0;
        if ($incidentId <= 0) {
            $_SESSION['error'] = 'Ocorrência inválida.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        $incident = Incident::findWithDetailsForAdmin($incidentId);
        if (!$incident) {
            $_SESSION['error'] = 'Ocorrência não encontrada.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        if (!$this->canCurrentUserSeePatient($incident)) {
            $_SESSION['error'] = 'Sem permissão para editar este utente.';
            header('Location: ' . $this->baseUrl . '?route=admin_incident_detail&id=' . $incidentId);
            exit;
        }

        $patientId = (int)($incident['patient_id'] ?? 0);
        if ($patientId <= 0) {
            $_SESSION['error'] = 'Não existe utente associado a esta ocorrência.';
            header('Location: ' . $this->baseUrl . '?route=admin_incident_detail&id=' . $incidentId);
            exit;
        }

        $patient = Patient::findById($patientId);
        if (!$patient) {
            $_SESSION['error'] = 'Utente não encontrado.';
            header('Location: ' . $this->baseUrl . '?route=admin_incident_detail&id=' . $incidentId);
            exit;
        }

        require __DIR__ . '/../Views/admin/patient_edit.php';
    }

    public function updatePatient(): void
    {
        Auth::requireRole(['Administrador', 'Enfermeiro']);

        $incidentId = isset($_POST['incident_id']) ? (int)$_POST['incident_id'] : 0;
        $patientId = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;

        if ($incidentId <= 0 || $patientId <= 0) {
            $_SESSION['error'] = 'Dados inválidos para atualização do utente.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        $incident = Incident::findWithDetailsForAdmin($incidentId);
        if (!$incident || (int)($incident['patient_id'] ?? 0) !== $patientId) {
            $_SESSION['error'] = 'Relação ocorrência/utente inválida.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        if (!$this->canCurrentUserSeePatient($incident)) {
            $_SESSION['error'] = 'Sem permissão para editar este utente.';
            header('Location: ' . $this->baseUrl . '?route=admin_incident_detail&id=' . $incidentId);
            exit;
        }

        $fullName = trim((string)($_POST['full_name'] ?? ''));
        if ($fullName === '') {
            $_SESSION['error'] = 'O nome do utente é obrigatório.';
            header('Location: ' . $this->baseUrl . '?route=incident_patient_edit&incident_id=' . $incidentId);
            exit;
        }

        $ageRaw = trim((string)($_POST['age'] ?? ''));
        $age = null;
        if ($ageRaw !== '') {
            $age = (int)$ageRaw;
            if ($age < 0 || $age > 120) {
                $_SESSION['error'] = 'A idade deve estar entre 0 e 120.';
                header('Location: ' . $this->baseUrl . '?route=incident_patient_edit&incident_id=' . $incidentId);
                exit;
            }
        }

        $gender = trim((string)($_POST['gender'] ?? ''));
        $allowedGenders = ['', 'M', 'F', 'Outro'];
        if (!in_array($gender, $allowedGenders, true)) {
            $gender = '';
        }

        $dob = trim((string)($_POST['dob'] ?? ''));
        if ($dob !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $_SESSION['error'] = 'Data de nascimento inválida.';
            header('Location: ' . $this->baseUrl . '?route=incident_patient_edit&incident_id=' . $incidentId);
            exit;
        }

        if ($dob !== '') {
            $dobDate = \DateTimeImmutable::createFromFormat('Y-m-d', $dob);
            $dobErrors = \DateTimeImmutable::getLastErrors();

            $dobIsValid = $dobDate instanceof \DateTimeImmutable
                && ($dobErrors['warning_count'] ?? 0) === 0
                && ($dobErrors['error_count'] ?? 0) === 0
                && $dobDate->format('Y-m-d') === $dob;

            if (!$dobIsValid || $dobDate > new \DateTimeImmutable('today') || $dobDate < new \DateTimeImmutable('1920-01-01')) {
                $_SESSION['error'] = 'Data de nascimento inválida.';
                header('Location: ' . $this->baseUrl . '?route=incident_patient_edit&incident_id=' . $incidentId);
                exit;
            }
        }

        try {
            Patient::updateFromIncidentForm($patientId, [
                'full_name' => $fullName,
                'age' => $age,
                'gender' => $gender !== '' ? $gender : null,
                'is_employee' => isset($_POST['is_employee']) ? 1 : 0,
                'nationality' => trim((string)($_POST['nationality'] ?? '')) ?: null,
                'address' => trim((string)($_POST['address'] ?? '')) ?: null,
                'postal_code' => trim((string)($_POST['postal_code'] ?? '')) ?: null,
                'city' => trim((string)($_POST['city'] ?? '')) ?: null,
                'phone' => trim((string)($_POST['phone'] ?? '')) ?: null,
                'dob' => $dob !== '' ? $dob : null,
                'id_type' => trim((string)($_POST['id_type'] ?? '')) ?: null,
                'id_number' => trim((string)($_POST['id_number'] ?? '')) ?: null,
                'refused_hospital' => isset($_POST['refused_hospital']) ? 1 : 0,
            ]);

            $_SESSION['success'] = 'Dados do utente atualizados com sucesso.';
            header('Location: ' . $this->baseUrl . '?route=admin_incident_detail&id=' . $incidentId);
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao atualizar dados do utente.';
            header('Location: ' . $this->baseUrl . '?route=incident_patient_edit&incident_id=' . $incidentId);
            exit;
        }
    }

    public function printPdf(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo 'Incidente inválido.';
            exit;
        }

        $incident = \App\Models\Incident::findWithDetailsForAdmin($id);
        if (!$incident) {
            http_response_code(404);
            echo 'Incidente não encontrado.';
            exit;
        }
        $treatments = \App\Models\Treatment::findByIncidentId($id);

        \App\Core\Auth::requireLogin();
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $role = $_SESSION['role'] ?? '';

        $canSeePatient = false;

        // 1. Administrador vê sempre
        if ($role === 'Administrador') {
            $canSeePatient = true;
        }
        // 2. Enfermeiro responsável pelo acidente
        elseif ($role === 'Enfermeiro') {

            // confirmar como está o nome do campo que guarda o ID do enfermeiro no acidente
            // assumo 'nurse_user_id' mas modifica se necessário
            $responsavelId = (int)($incident['user_id'] ?? 0);

            if ($responsavelId === $currentUserId) {
                $canSeePatient = true;
            }
        }

        // Carregar HTML da view
        $viewFile = __DIR__ . '/../Views/admin/incident_pdf.php';

        ob_start();
        $incident_data = $incident;
        $treatments_data = $treatments;
        $canSeePatient_flag = $canSeePatient;
        require $viewFile;
        $html = ob_get_clean();

        // Guardar o HTML para debug
        $debugFile = sys_get_temp_dir() . '/incident-' . $id . '.html';
        file_put_contents($debugFile, $html);

        // Instanciar dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        try {
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);

            $dompdf->setPaper('A4');
            $dompdf->render();

            // Limpar qualquer buffer de saída existente (importantíssimo)
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Gera conteúdo PDF em memória
            $pdfContent = $dompdf->output();

            // ===================================================
            // NOVO BLOCO — GRAVAR PDF NO SERVIDOR + ABRIR NA ABA
            // ===================================================

            $publicDir = realpath(__DIR__ . '/../../public');
            $pdfDir = $publicDir . '/pdfs/';

            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }

            $filename = 'acidente-' . $id . '.pdf';
            $filePath = $pdfDir . $filename;

            file_put_contents($filePath, $pdfContent);

            $pdfUrl = '/enfermaria/public/pdfs/' . $filename;

            header("Location: $pdfUrl");
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo "<h2>Erro ao gerar PDF</h2>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            exit;
        }

    }

public function printRefusalPdf(): void
{
    Auth::requireRole(['Administrador', 'Enfermeiro']);

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo 'Incidente inválido.';
        exit;
    }

    $incident = \App\Models\Incident::findWithDetailsForAdmin($id);

    if (!$incident || empty($incident['refused_hospital'])) {
        http_response_code(404);
        echo 'Não existe recusa registada para este incidente.';
        exit;
    }

    /* =====================================================
    DETECTAR LÍNGUA PELO PAÍS / NACIONALIDADE
    ===================================================== */

    $nationality = strtolower(trim($incident['patient_nationality'] ?? ''));

    /* remover acentos para facilitar comparação */
    $nationality = iconv('UTF-8', 'ASCII//TRANSLIT', $nationality);

    $lang = 'en'; // DEFAULT inglês

    if (preg_match('/portugal|portuguese/', $nationality)) {

        $lang = 'pt';

    } elseif (preg_match('/brasil|brazil|brasileir/', $nationality)) {

        $lang = 'pt';   // Brasil usa português

    } elseif (preg_match('/spain|espan|spanish/', $nationality)) {

        $lang = 'es';

    } elseif (preg_match('/france|franc|french/', $nationality)) {

        $lang = 'fr';

    } elseif (preg_match('/germany|alem|deutsch/', $nationality)) {

        $lang = 'de';

    } elseif (preg_match('/england|british|uk|usa|american|english/', $nationality)) {

        $lang = 'en';

    }

    /* =====================================================
       ESCOLHER VIEW CORRETA
    ====================================================== */

    $viewFile = __DIR__ . '/../Views/pdfs/termo_recusa_' . $lang . '.php';

    // fallback para PT se não existir
    if (!file_exists($viewFile)) {
        $viewFile = __DIR__ . '/../Views/pdfs/termo_recusa_pt.php';
    }

    ob_start();
    $incident_data = $incident;
    require $viewFile;
    $html = ob_get_clean();

    /* =====================================================
       GERAR PDF
    ====================================================== */

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);

    try {

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $pdfContent = $dompdf->output();

        $publicDir = realpath(__DIR__ . '/../../public');
        $pdfDir = $publicDir . '/pdfs/';

        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }

        $filename = 'termo-recusa-' . $id . '-' . $lang . '.pdf';
        $filePath = $pdfDir . $filename;

        file_put_contents($filePath, $pdfContent);

        $pdfUrl = '/enfermaria/public/pdfs/' . $filename;

        header("Location: $pdfUrl");
        exit;

    } catch (\Exception $e) {

        http_response_code(500);
        echo "<h2>Erro ao gerar termo de recusa</h2>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        exit;
    }
}


}
