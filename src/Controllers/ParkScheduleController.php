<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Models\ParkSchedule;
use App\Services\PdfScheduleImporter;
use DateTimeImmutable;
use RuntimeException;

class ParkScheduleController
{
    private string $baseUrl = '/enfermaria/public/index.php';

    public function index(): void
    {
        Auth::requireRole(['Administrador', 'Enfermeiro']);
        [$year, $month] = $this->requestedMonth();
        $_SESSION['park_schedule_csrf'] ??= bin2hex(random_bytes(32));
        $nurses = ParkSchedule::nurses();
        $assignmentsByDay = ParkSchedule::assignments($year, $month);
        $baseUrl = $this->baseUrl;
        require __DIR__ . '/../Views/schedules/calendar.php';
    }

    public function saveDay(): void
    {
        Auth::requireRole(['Administrador', 'Enfermeiro']);
        [$year, $month] = $this->requestedMonth();
        if (!hash_equals((string)($_SESSION['park_schedule_csrf'] ?? ''), (string)($_POST['csrf_token'] ?? ''))) {
            http_response_code(419); exit('Pedido expirado. Atualize a página e tente novamente.');
        }
        $date = (string)($_POST['work_date'] ?? '');
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date || (int)$parsed->format('Y') !== $year || (int)$parsed->format('n') !== $month) {
            $_SESSION['schedule_error'] = 'Data inválida para o mês selecionado.'; $this->redirect($year, $month);
        }
        $validNurses = array_map('intval', array_column(ParkSchedule::nurses(), 'id'));
        $validShifts = ['M', 'T', 'C', 'TE'];
        $posted = is_array($_POST['shifts'] ?? null) ? $_POST['shifts'] : [];
        $assignments = [];
        foreach ($posted as $nurseId => $shift) {
            $nurseId = (int)$nurseId;
            if (in_array($nurseId, $validNurses, true) && in_array($shift, $validShifts, true)) $assignments[] = ['nurse_id'=>$nurseId, 'shift'=>$shift];
        }
        ParkSchedule::replaceDay($year, $month, $date, (int)$_SESSION['user_id'], $assignments);
        $_SESSION['schedule_success'] = 'Escala de ' . $parsed->format('d/m/Y') . ' atualizada.';
        $this->redirect($year, $month);
    }

    public function upload(): void
    {
        Auth::requireRole(['Administrador', 'Enfermeiro']);
        $this->checkCsrf();
        $file = $_FILES['schedule_pdf'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
            $_SESSION['schedule_error'] = 'Selecione um ficheiro PDF válido.';
            $this->redirect((int)date('Y'), (int)date('n'));
        }
        if ((int)($file['size'] ?? 0) > 10 * 1024 * 1024) {
            $_SESSION['schedule_error'] = 'O PDF não pode exceder 10 MB.';
            $this->redirect((int)date('Y'), (int)date('n'));
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);
        if ($mime !== 'application/pdf') {
            $_SESSION['schedule_error'] = 'O ficheiro selecionado não é um PDF.';
            $this->redirect((int)date('Y'), (int)date('n'));
        }
        try {
            $_SESSION['park_schedule_import'] = (new PdfScheduleImporter())->parse((string)$file['tmp_name']);
            header('Location: '.$this->baseUrl.'?route=park_schedule_import_preview'); exit;
        } catch (RuntimeException $e) {
            $_SESSION['schedule_error'] = $e->getMessage();
            $this->redirect((int)date('Y'), (int)date('n'));
        }
    }

    public function preview(): void
    {
        Auth::requireRole(['Administrador', 'Enfermeiro']);
        $import = $_SESSION['park_schedule_import'] ?? null;
        if (!is_array($import)) $this->redirect((int)date('Y'), (int)date('n'));
        $nurses = ParkSchedule::nurses();
        $activePdfNames = array_values(array_unique(array_column($import['entries'], 'pdf_name')));
        $suggestions = $this->suggestions($activePdfNames, $nurses);
        $unmatchedCount = count(array_filter($activePdfNames, static fn(string $name): bool => empty($suggestions[$name])));
        $baseUrl = $this->baseUrl;
        require __DIR__ . '/../Views/schedules/import_preview.php';
    }

    public function confirmImport(): void
    {
        Auth::requireRole(['Administrador', 'Enfermeiro']);
        $this->checkCsrf();
        $import = $_SESSION['park_schedule_import'] ?? null;
        if (!is_array($import)) $this->redirect((int)date('Y'), (int)date('n'));
        $validNurses = array_map('intval', array_column(ParkSchedule::nurses(), 'id'));
        $posted = is_array($_POST['mapping'] ?? null) ? $_POST['mapping'] : [];
        $mapping = [];
        foreach ($posted as $encodedName => $staffChoice) {
            $name = base64_decode((string)$encodedName, true);
            if ($name === false) continue;
            if ($staffChoice === 'new') {
                $mapping[$name] = ParkSchedule::findOrCreateStaff(PdfScheduleImporter::canonicalName($name), $import['contacts'][$name] ?? null);
                continue;
            }
            $nurseId = (int)$staffChoice;
            if (in_array($nurseId, $validNurses, true)) $mapping[$name] = $nurseId;
        }
        $assignments = [];
        foreach ($import['entries'] as $entry) {
            if (!isset($mapping[$entry['pdf_name']])) {
                $_SESSION['schedule_error'] = 'Associe todos os enfermeiros antes de importar.';
                header('Location: '.$this->baseUrl.'?route=park_schedule_import_preview'); exit;
            }
            $assignments[] = ['date'=>$entry['date'], 'shift'=>$entry['shift'], 'nurse_id'=>$mapping[$entry['pdf_name']]];
        }
        ParkSchedule::replaceMonth((int)$import['year'], (int)$import['month'], (int)$_SESSION['user_id'], $assignments);
        unset($_SESSION['park_schedule_import']);
        $_SESSION['schedule_success'] = count($assignments).' turnos importados do PDF.';
        $this->redirect((int)$import['year'], (int)$import['month']);
    }

    private function suggestions(array $pdfNames, array $nurses): array
    {
        $aliases = ['beta'=>'elisabete simao', 'ana rita g'=>'ana rita gameiro', 'viktoriia m'=>'viktoriia manziuk'];
        $result = [];
        foreach ($pdfNames as $pdfName) {
            $needle = PdfScheduleImporter::normalize($pdfName);
            $needle = $aliases[$needle] ?? $needle;
            foreach ($nurses as $nurse) {
                $candidate = PdfScheduleImporter::normalize($nurse['full_name']);
                if ($candidate === $needle || str_starts_with($candidate, $needle.' ') || str_starts_with($needle, $candidate.' ')) {
                    $result[$pdfName] = (int)$nurse['id']; break;
                }
            }
        }
        return $result;
    }

    private function checkCsrf(): void
    {
        if (!hash_equals((string)($_SESSION['park_schedule_csrf'] ?? ''), (string)($_POST['csrf_token'] ?? ''))) {
            http_response_code(419); exit('Pedido expirado. Atualize a página e tente novamente.');
        }
    }

    private function requestedMonth(): array
    {
        $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT) ?: (int)date('Y');
        $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT) ?: (int)date('n');
        if ($year < 2020 || $year > 2100 || $month < 1 || $month > 12) return [(int)date('Y'), (int)date('n')];
        return [$year, $month];
    }

    private function redirect(int $year, int $month): void
    {
        header('Location: '.$this->baseUrl.'?route=park_schedule&year='.$year.'&month='.$month); exit;
    }
}
