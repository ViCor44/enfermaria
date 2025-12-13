<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Incident;
use App\Models\Location;
use Dompdf\Dompdf;
use Dompdf\Options;

class AdminIncidentController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function index(): void
    {
        Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

        $fromDate   = $_GET['from'] ?? '';
        $toDate     = $_GET['to'] ?? '';
        $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
        $episode    = isset($_GET['episode']) ? trim($_GET['episode']) : '';

        $locations  = \App\Models\Location::allActive();

        $incidents = \App\Models\Incident::search([
            'fromDate'   => $fromDate !== '' ? $fromDate : null,
            'toDate'     => $toDate !== '' ? $toDate : null,
            'locationId' => $locationId > 0 ? $locationId : null,
            'episode'    => $episode !== '' ? $episode : null,
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

        if (!$incident) {
            $_SESSION['error'] = 'Acidente não encontrado.';
            header('Location: ' . $this->baseUrl . '?route=admin_incidents');
            exit;
        }

        $treatments = Incident::getTreatmentsForIncident($id);
        $role          = $_SESSION['role'] ?? '';
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);

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

        require __DIR__ . '/../Views/admin/incidents_detail.php';
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
}
