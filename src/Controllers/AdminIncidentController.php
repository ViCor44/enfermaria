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

        $canSeePatient = false;

        if ($role === 'Administrador') {
            // Admin vê sempre
            $canSeePatient = true;

        } elseif ($role === 'Enfermeiro') {
            // Enfermeiro só vê se tiver tratado este Acidente
            foreach ($treatments as $tr) {
                if ((int)$tr['user_id'] === $currentUserId) {
                    $canSeePatient = true;
                    break;
                }
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

        if ($role === 'Administrador') {
            // Admin vê sempre
            $canSeePatient = true;

        } elseif ($role === 'Enfermeiro') {
            // Enfermeiro só vê se tiver tratado este Acidente
            foreach ($treatments as $tr) {
                if ((int)$tr['user_id'] === $currentUserId) {
                    $canSeePatient = true;
                    break;
                }
            }
        }

        $treatments = \App\Models\Treatment::findByIncidentId($id);

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

    // Guarda para debug / confirmação
    $outPath = sys_get_temp_dir() . '/acidente-' . $id . '.pdf';
    file_put_contents($outPath, $pdfContent);

    // Verifica o prefixo "%PDF-"
    $starts = substr($pdfContent, 0, 5);
    if ($starts !== '%PDF-') {
        // grava ficheiro de debug com o conteúdo textual para inspecionar
        file_put_contents(sys_get_temp_dir() . "/acidente-{$id}-bad.txt", substr($pdfContent, 0, 500));
        throw new \Exception("O PDF gerado não começa com %PDF- (prefixo='$starts'). Verifique $outPath e arquivo de debug.");
    }

    // Enviar ficheiro para o browser com headers limpos
    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($outPath));
    header('Content-Disposition: attachment; filename="acidente-' . $id . '.pdf"');
    readfile($outPath);
    exit;

} catch (\Exception $e) {
    http_response_code(500);
    echo "<h2>Erro ao gerar PDF</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Ficheiro de debug: " . htmlspecialchars($outPath) . "</p>";
    // grava stack trace
    file_put_contents(sys_get_temp_dir() . '/incident-' . $id . '-error.txt', $e->getMessage() . "\n\n" . $e->getTraceAsString());
    exit;
}

    }
    // src/Controllers/AdminIncidentController.php
    public function print(): void
    {
        // check permissions (só admins/managers? ajusta conforme regras)
        \App\Core\Auth::requireRole(['Administrador', 'Manager', 'Enfermeiro']);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        // carregar modelo de incidente e tratamentos (usa os teus métodos existentes)
        $incident = \App\Models\Incident::findWithDetailsForAdmin($id);
        if (!$incident) {
            http_response_code(404);
            echo 'Acidente não encontrado';
            return;
        }

        // buscar tratamentos (cria Treatment::findByIncidentId se não existir)
        $treatments = \App\Models\Treatment::findByIncidentId($id);

        // decidir se o utilizador actual pode ver dados do paciente
        $currentUserId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? '';
        $canSeePatient = false;

        if ($role === 'Administrador') {
            // Admin vê sempre
            $canSeePatient = true;

        } elseif ($role === 'Enfermeiro') {
            // Enfermeiro só vê se tiver tratado este Acidente
            foreach ($treatments as $tr) {
                if ((int)$tr['user_id'] === $currentUserId) {
                    $canSeePatient = true;
                    break;
                }
            }
        }

        // renderizar o template de impressão para uma string (output buffering)
        $viewFile = __DIR__ . '/../Views/admin/incident_print.php';
        ob_start();
        require $viewFile; // este ficheiro deve usar $incident, $treatments, $canSeePatient
        $html = ob_get_clean();

        // gerar PDF com Dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true); // se usas imagens ou css externos
        $options->set('defaultFont', 'Arial'); // garantir Arial
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        // stream (forçar download) ou inline exibir no browser
        $filename = 'acidente-' . $id . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]); // false = abre inline
    }


}
