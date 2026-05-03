<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Incident;
use App\Models\Treatment;
use DateInterval;
use DateTimeImmutable;

class AdminStatsController
{
    public function index(): void
    {
        Auth::requireAdmin();

        $filters = $this->resolveFilters();

        [
            'ageStats' => $ageStats,
            'genderStats' => $genderStats,
            'locationStats' => $locationStats,
            'typeStats' => $typeStats,
            'treatmentStats' => $treatmentStats,
            'summary' => $summary,
            'comparison' => $comparison,
            'insights' => $insights,
        ] = $this->buildStatsPayload($filters);

        $exportUrl = '/enfermaria/public/index.php?' . http_build_query([
            'route' => 'admin_stats_export',
            'period' => $filters['period'],
            'from' => $filters['fromDate'],
            'to' => $filters['toDate'],
        ]);
        $exportPdfUrl = '/enfermaria/public/index.php?' . http_build_query([
            'route' => 'admin_stats_export_pdf',
            'period' => $filters['period'],
            'from' => $filters['fromDate'],
            'to' => $filters['toDate'],
        ]);

        require __DIR__ . '/../Views/admin/stats.php';
    }

    public function exportCsv(): void
    {
        Auth::requireAdmin();

        $filters = $this->resolveFilters();
        [
            'ageStats' => $ageStats,
            'genderStats' => $genderStats,
            'locationStats' => $locationStats,
            'typeStats' => $typeStats,
            'treatmentStats' => $treatmentStats,
            'summary' => $summaryData,
        ] = $this->buildStatsPayload($filters);

        $summary = [
            'Ocorrências' => $summaryData['incidents'],
            'Tratamentos' => $summaryData['treatments'],
            'Local mais frequente' => $this->exportTopValue($summaryData['topLocation'], 'local'),
            'Tipo de ocorrência principal' => $this->exportTopValue($summaryData['topIncidentType'], 'tipo'),
            'Tratamento principal' => $this->exportTopValue($summaryData['topTreatmentType'], 'tipo'),
        ];

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="estatisticas-' . date('Ymd-His') . '.csv"');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            exit;
        }

        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, ['Estatísticas', $filters['label']], ';');
        fputcsv($output, ['Período', $filters['rangeLabel']], ';');
        fputcsv($output, [], ';');

        fputcsv($output, ['Resumo', 'Valor'], ';');
        foreach ($summary as $label => $value) {
            fputcsv($output, [$label, $value], ';');
        }

        $this->writeCsvSection($output, 'Ocorrências por Faixa Etária', 'Faixa', $ageStats, 'faixa');
        $this->writeCsvSection($output, 'Ocorrências por Género', 'Género', $genderStats, 'genero');
        $this->writeCsvSection($output, 'Ocorrências por Local', 'Local', $locationStats, 'local');
        $this->writeCsvSection($output, 'Tipo de Ocorrência', 'Tipo', $typeStats, 'tipo');
        $this->writeCsvSection($output, 'Tipo de Tratamento', 'Tipo', $treatmentStats, 'tipo');

        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        Auth::requireAdmin();

        $filters = $this->resolveFilters();
        [
            'ageStats' => $ageStats,
            'genderStats' => $genderStats,
            'locationStats' => $locationStats,
            'typeStats' => $typeStats,
            'treatmentStats' => $treatmentStats,
            'summary' => $summary,
            'comparison' => $comparison,
            'insights' => $insights,
        ] = $this->buildStatsPayload($filters);

        $generatedAt = (new DateTimeImmutable('now'))->format('d/m/Y H:i');
        $logoDataUri = $this->buildPublicImageDataUri('assets/img/logo-sae.png');

        ob_start();
        require __DIR__ . '/../Views/admin/stats_pdf.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = 'estatisticas-' . date('Ymd-His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
        exit;
    }

    private function buildPublicImageDataUri(string $relativePath): ?string
    {
        $filePath = realpath(__DIR__ . '/../../public/' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        if ($filePath === false || !is_file($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $mime = mime_content_type($filePath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }

    private function buildStatsPayload(array $filters): array
    {
        $ageStats = Incident::statsByAge($filters);
        $genderStats = $this->formatGenderStats(Incident::statsByGender($filters));
        $locationStats = $this->formatCategoryStats(Incident::statsByLocation($filters), 'local', 'Local não definido');
        $typeStats = $this->formatCategoryStats(Incident::statsByIncidentType($filters), 'tipo', 'Tipo não definido');
        $treatmentStats = $this->formatCategoryStats(Treatment::statsByType($filters), 'tipo', 'Tratamento não definido');

        $summary = [
            'incidents' => Incident::countByFilters($filters),
            'treatments' => Treatment::countByFilters($filters),
            'topLocation' => Incident::topLocation($filters),
            'topIncidentType' => Incident::topIncidentType($filters),
            'topTreatmentType' => Treatment::topType($filters),
        ];

        $comparison = $this->buildComparison($filters, $summary);
        $insights = $this->buildInsights($summary, $genderStats, $comparison);

        return [
            'ageStats' => $ageStats,
            'genderStats' => $genderStats,
            'locationStats' => $locationStats,
            'typeStats' => $typeStats,
            'treatmentStats' => $treatmentStats,
            'summary' => $summary,
            'comparison' => $comparison,
            'insights' => $insights,
        ];
    }

    private function resolveFilters(): array
    {
        $allowedPeriods = ['all', 'today', '7d', '30d', 'month', 'year', 'custom'];
        $period = (string)($_GET['period'] ?? '30d');
        if (!in_array($period, $allowedPeriods, true)) {
            $period = '30d';
        }

        $today = new DateTimeImmutable('today');
        $fromDate = null;
        $toDate = null;
        $label = 'Últimos 30 dias';

        switch ($period) {
            case 'all':
                $label = 'Todo o histórico';
                break;
            case 'today':
                $fromDate = $today->format('Y-m-d');
                $toDate = $fromDate;
                $label = 'Hoje';
                break;
            case '7d':
                $fromDate = $today->sub(new DateInterval('P6D'))->format('Y-m-d');
                $toDate = $today->format('Y-m-d');
                $label = 'Últimos 7 dias';
                break;
            case '30d':
                $fromDate = $today->sub(new DateInterval('P29D'))->format('Y-m-d');
                $toDate = $today->format('Y-m-d');
                $label = 'Últimos 30 dias';
                break;
            case 'month':
                $fromDate = $today->modify('first day of this month')->format('Y-m-d');
                $toDate = $today->format('Y-m-d');
                $label = 'Mês atual';
                break;
            case 'year':
                $fromDate = $today->setDate((int)$today->format('Y'), 1, 1)->format('Y-m-d');
                $toDate = $today->format('Y-m-d');
                $label = 'Ano atual';
                break;
            case 'custom':
                $from = trim((string)($_GET['from'] ?? ''));
                $to = trim((string)($_GET['to'] ?? ''));
                if ($this->isValidDate($from) && $this->isValidDate($to) && $from <= $to) {
                    $fromDate = $from;
                    $toDate = $to;
                    $label = 'Intervalo personalizado';
                } else {
                    $period = '30d';
                    $fromDate = $today->sub(new DateInterval('P29D'))->format('Y-m-d');
                    $toDate = $today->format('Y-m-d');
                    $label = 'Últimos 30 dias';
                }
                break;
        }

        return [
            'period' => $period,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'label' => $label,
            'rangeLabel' => $this->formatRangeLabel($fromDate, $toDate, $label),
        ];
    }

    private function buildComparison(array $filters, array $summary): array
    {
        if (empty($filters['fromDate']) || empty($filters['toDate'])) {
            return [
                'available' => false,
                'incidentsDelta' => null,
                'treatmentsDelta' => null,
                'previousLabel' => 'Comparação indisponível para o período completo',
            ];
        }

        $from = new DateTimeImmutable($filters['fromDate']);
        $to = new DateTimeImmutable($filters['toDate']);
        $days = (int)$from->diff($to)->days + 1;
        $previousTo = $from->sub(new DateInterval('P1D'));
        $previousFrom = $previousTo->sub(new DateInterval('P' . max($days - 1, 0) . 'D'));

        $previousFilters = [
            'fromDate' => $previousFrom->format('Y-m-d'),
            'toDate' => $previousTo->format('Y-m-d'),
        ];

        $previousIncidents = Incident::countByFilters($previousFilters);
        $previousTreatments = Treatment::countByFilters($previousFilters);

        return [
            'available' => true,
            'previousLabel' => $this->formatRangeLabel($previousFilters['fromDate'], $previousFilters['toDate'], 'Período anterior'),
            'incidentsDelta' => $this->calculateDelta($summary['incidents'], $previousIncidents),
            'treatmentsDelta' => $this->calculateDelta($summary['treatments'], $previousTreatments),
        ];
    }

    private function buildInsights(array $summary, array $genderStats, array $comparison): array
    {
        $totalGender = array_sum(array_map(static fn(array $row): int => (int)$row['total'], $genderStats));
        $dominantGender = null;
        if ($genderStats !== [] && $totalGender > 0) {
            $first = $genderStats[0];
            $dominantGender = $first['genero'] . ' (' . (int)round(((int)$first['total'] / $totalGender) * 100) . '%)';
        }

        return [
            'dominantGender' => $dominantGender,
            'topLocation' => $this->exportTopValue($summary['topLocation'], 'local'),
            'topIncidentType' => $this->exportTopValue($summary['topIncidentType'], 'tipo'),
            'topTreatmentType' => $this->exportTopValue($summary['topTreatmentType'], 'tipo'),
            'comparisonLabel' => $comparison['previousLabel'],
        ];
    }

    private function formatGenderStats(array $rows): array
    {
        foreach ($rows as &$row) {
            $value = trim((string)($row['genero'] ?? ''));
            if ($value === 'M') {
                $row['genero'] = 'Masculino';
            } elseif ($value === 'F') {
                $row['genero'] = 'Feminino';
            } elseif ($value === 'Outro') {
                $row['genero'] = 'Outro';
            } else {
                $row['genero'] = 'Não especificado';
            }
        }

        return $rows;
    }

    private function formatCategoryStats(array $rows, string $key, string $fallback): array
    {
        foreach ($rows as &$row) {
            $value = trim((string)($row[$key] ?? ''));
            $row[$key] = $value !== '' ? $value : $fallback;
        }

        return $rows;
    }

    private function calculateDelta(int $current, int $previous): ?array
    {
        if ($previous === 0 && $current === 0) {
            return ['direction' => 'neutral', 'value' => '0%'];
        }

        if ($previous === 0) {
            return ['direction' => 'up', 'value' => '+100%'];
        }

        $delta = (($current - $previous) / $previous) * 100;
        if ($delta > 0) {
            return ['direction' => 'up', 'value' => '+' . round($delta) . '%'];
        }
        if ($delta < 0) {
            return ['direction' => 'down', 'value' => round($delta) . '%'];
        }

        return ['direction' => 'neutral', 'value' => '0%'];
    }

    private function isValidDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function formatRangeLabel(?string $fromDate, ?string $toDate, string $fallback): string
    {
        if ($fromDate === null || $toDate === null) {
            return $fallback;
        }

        $from = DateTimeImmutable::createFromFormat('Y-m-d', $fromDate);
        $to = DateTimeImmutable::createFromFormat('Y-m-d', $toDate);
        if ($from === false || $to === false) {
            return $fallback;
        }

        return $from->format('d/m/Y') . ' a ' . $to->format('d/m/Y');
    }

    private function exportTopValue(?array $row, string $key): string
    {
        if ($row === null) {
            return 'Sem dados';
        }

        $label = trim((string)($row[$key] ?? ''));
        $count = (int)($row['total'] ?? 0);
        return ($label !== '' ? $label : 'Sem dados') . ' (' . $count . ')';
    }

    private function writeCsvSection($output, string $title, string $labelHeader, array $rows, string $key): void
    {
        fputcsv($output, [], ';');
        fputcsv($output, [$title], ';');
        fputcsv($output, [$labelHeader, 'Total'], ';');

        foreach ($rows as $row) {
            fputcsv($output, [$row[$key] ?? '', $row['total'] ?? 0], ';');
        }
    }

    // ─── Registos Internos ───────────────────────────────────────────────────

    public function indexInternal(): void
    {
        Auth::requireAdmin();

        $filters = $this->resolveFilters();

        $locationStats    = $this->formatCategoryStats(\App\Models\InternalRecord::statsByLocation($filters), 'local', 'Local não definido');
        $genderStats      = $this->formatGenderStats(\App\Models\InternalRecord::statsByGender($filters));
        $ageStats         = \App\Models\InternalRecord::statsByAge($filters);
        $employeeStats    = \App\Models\InternalRecord::statsByEmployeeType($filters);
        $totalRecords     = \App\Models\InternalRecord::countByFilters($filters);
        $topLocation      = \App\Models\InternalRecord::topLocation($filters);

        $summary = [
            'records'     => $totalRecords,
            'topLocation' => $topLocation,
        ];

        $comparison = $this->buildInternalComparison($filters, $summary);

        $exportUrl = '/enfermaria/public/index.php?' . http_build_query([
            'route'  => 'admin_stats_internal_export',
            'period' => $filters['period'],
            'from'   => $filters['fromDate'],
            'to'     => $filters['toDate'],
        ]);

        require __DIR__ . '/../Views/admin/stats_internal.php';
    }

    public function exportCsvInternal(): void
    {
        Auth::requireAdmin();

        $filters = $this->resolveFilters();

        $locationStats = $this->formatCategoryStats(\App\Models\InternalRecord::statsByLocation($filters), 'local', 'Local não definido');
        $genderStats   = $this->formatGenderStats(\App\Models\InternalRecord::statsByGender($filters));
        $ageStats      = \App\Models\InternalRecord::statsByAge($filters);
        $employeeStats = \App\Models\InternalRecord::statsByEmployeeType($filters);
        $totalRecords  = \App\Models\InternalRecord::countByFilters($filters);
        $topLocation   = \App\Models\InternalRecord::topLocation($filters);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="registos-internos-' . date('Ymd-His') . '.csv"');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            exit;
        }

        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, ['Estatísticas — Registos Internos', $filters['label']], ';');
        fputcsv($output, ['Período', $filters['rangeLabel']], ';');
        fputcsv($output, [], ';');

        fputcsv($output, ['Resumo', 'Valor'], ';');
        fputcsv($output, ['Registos', $totalRecords], ';');
        fputcsv($output, ['Local mais frequente', $this->exportTopValue($topLocation, 'local')], ';');

        $this->writeCsvSection($output, 'Registos por Local',        'Local',             $locationStats, 'local');
        $this->writeCsvSection($output, 'Registos por Género',       'Género',            $genderStats,   'genero');
        $this->writeCsvSection($output, 'Registos por Faixa Etária', 'Faixa',             $ageStats,      'faixa');
        $this->writeCsvSection($output, 'Colaborador / Utente',      'Tipo de utente',    $employeeStats, 'tipo');

        fclose($output);
        exit;
    }

    private function buildInternalComparison(array $filters, array $summary): array
    {
        if (empty($filters['fromDate']) || empty($filters['toDate'])) {
            return [
                'available'       => false,
                'recordsDelta'    => null,
                'previousLabel'   => 'Comparação indisponível para o período completo',
            ];
        }

        $from = new DateTimeImmutable($filters['fromDate']);
        $to   = new DateTimeImmutable($filters['toDate']);
        $days = (int)$from->diff($to)->days + 1;
        $previousTo   = $from->sub(new DateInterval('P1D'));
        $previousFrom = $previousTo->sub(new DateInterval('P' . max($days - 1, 0) . 'D'));

        $previousFilters = [
            'fromDate' => $previousFrom->format('Y-m-d'),
            'toDate'   => $previousTo->format('Y-m-d'),
        ];

        $previousRecords = \App\Models\InternalRecord::countByFilters($previousFilters);

        return [
            'available'     => true,
            'previousLabel' => $this->formatRangeLabel($previousFilters['fromDate'], $previousFilters['toDate'], 'Período anterior'),
            'recordsDelta'  => $this->calculateDelta($summary['records'], $previousRecords),
        ];
    }
}
