<?php
$summaryRows = [
    ['label' => 'Ocorrências', 'value' => (int)($summary['incidents'] ?? 0)],
    ['label' => 'Tratamentos', 'value' => (int)($summary['treatments'] ?? 0)],
    ['label' => 'Local mais frequente', 'value' => $summary['topLocation']['local'] ?? 'Sem dados'],
    ['label' => 'Tipo principal', 'value' => $summary['topIncidentType']['tipo'] ?? 'Sem dados'],
    ['label' => 'Tratamento principal', 'value' => $summary['topTreatmentType']['tipo'] ?? 'Sem dados'],
];

$tables = [
    ['title' => 'Ocorrências por Faixa Etária', 'rows' => $ageStats, 'key' => 'faixa', 'label' => 'Faixa'],
    ['title' => 'Ocorrências por Género', 'rows' => $genderStats, 'key' => 'genero', 'label' => 'Género'],
    ['title' => 'Ocorrências por Local', 'rows' => $locationStats, 'key' => 'local', 'label' => 'Local'],
    ['title' => 'Tipo de Ocorrência', 'rows' => $typeStats, 'key' => 'tipo', 'label' => 'Tipo'],
    ['title' => 'Tipo de Tratamento', 'rows' => $treatmentStats, 'key' => 'tipo', 'label' => 'Tipo'],
];

foreach ($tables as &$table) {
    $totals = array_map(static fn(array $row): int => (int)($row['total'] ?? 0), $table['rows']);
    $table['max'] = $totals === [] ? 0 : max($totals);
}
unset($table);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Estatísticas</title>
    <style>
        @page {
            margin: 22mm 16mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #16324f;
            line-height: 1.45;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header {
            border-bottom: 3px solid #1f6feb;
            padding-bottom: 16px;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .brand-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-table td {
            vertical-align: top;
        }

        .brand-logo-cell {
            width: 92px;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
        }

        .brand-copy {
            text-align: right;
        }

        .brand-kicker {
            display: inline-block;
            margin-bottom: 6px;
            padding: 3px 8px;
            background: #eaf2ff;
            color: #1f6feb;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border-radius: 999px;
        }

        .title {
            font-size: 24px;
            color: #1f6feb;
            font-weight: 700;
        }

        .subtitle {
            margin-top: 6px;
            color: #5f7492;
        }

        .meta {
            margin-top: 12px;
            padding: 10px 12px;
            background: #f3f7fd;
            border: 1px solid #d6e4f5;
            border-radius: 10px;
            page-break-inside: avoid;
        }

        .meta-row {
            margin-top: 4px;
        }

        .section {
            margin-top: 16px;
        }

        .section-title {
            font-size: 15px;
            color: #183153;
            font-weight: 700;
            margin-bottom: 8px;
            page-break-after: avoid;
        }

        .section-compact,
        .section-compact .summary-grid,
        .section-compact .summary-table,
        .section-compact .insights,
        .section-compact .empty {
            page-break-inside: avoid;
        }

        .table-block {
            page-break-inside: auto;
        }

        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-left: -10px;
            margin-right: -10px;
        }

        .summary-grid td {
            width: 50%;
            background: #f8fbff;
            border: 1px solid #d9e4f2;
            border-radius: 12px;
            padding: 12px;
        }

        .summary-box-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #62748b;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .summary-box-value {
            margin-top: 8px;
            font-size: 22px;
            color: #183153;
            font-weight: 700;
        }

        .summary-box-meta {
            margin-top: 6px;
            color: #62748b;
            font-size: 10px;
        }

        .summary-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table {
            page-break-inside: avoid;
        }

        .summary-table td,
        .data-table th,
        .data-table td {
            border: 1px solid #d9e4f2;
            padding: 8px 10px;
            vertical-align: top;
        }

        .summary-table td:first-child {
            width: 40%;
            font-weight: 700;
            background: #f8fbff;
        }

        .data-table th {
            background: #eef4ff;
            text-align: left;
            font-weight: 700;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tfoot {
            display: table-row-group;
        }

        .data-table tr,
        .summary-table tr,
        .summary-grid tr {
            page-break-inside: avoid;
        }

        .bar-cell {
            width: 36%;
        }

        .bar-track {
            width: 100%;
            height: 10px;
            background: #edf3fb;
            border-radius: 999px;
            overflow: hidden;
        }

        .bar-fill {
            height: 10px;
            background: #1f6feb;
            border-radius: 999px;
        }

        .empty {
            padding: 10px 12px;
            border: 1px dashed #cddcf0;
            background: #fafcff;
            color: #62748b;
        }

        .insights {
            margin-top: 8px;
        }

        .insight-item {
            margin-top: 5px;
        }

        .footer {
            margin-top: 24px;
            color: #62748b;
            font-size: 10px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="brand-table">
            <tr>
                <td class="brand-logo-cell">
                    <?php if (!empty($logoDataUri)): ?>
                        <img class="brand-logo" src="<?= htmlspecialchars($logoDataUri) ?>" alt="SAE">
                    <?php endif; ?>
                </td>
                <td class="brand-copy">
                    <div class="brand-kicker">Relatório institucional</div>
                    <div class="title">Relatório de Estatísticas</div>
                    <p class="subtitle">Sistema de Apoio à Enfermaria</p>
                </td>
            </tr>
        </table>

        <div class="meta">
            <div class="meta-row"><strong>Período:</strong> <?= htmlspecialchars($filters['rangeLabel']) ?></div>
            <div class="meta-row"><strong>Filtro:</strong> <?= htmlspecialchars($filters['label']) ?></div>
            <div class="meta-row"><strong>Gerado em:</strong> <?= htmlspecialchars($generatedAt) ?></div>
        </div>
    </div>

    <div class="section section-compact">
        <div class="section-title">Resumo</div>
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-box-label">Ocorrências</div>
                    <div class="summary-box-value"><?= (int)($summary['incidents'] ?? 0) ?></div>
                    <div class="summary-box-meta">Variação: <?= htmlspecialchars($comparison['incidentsDelta']['value'] ?? '—') ?></div>
                </td>
                <td>
                    <div class="summary-box-label">Tratamentos</div>
                    <div class="summary-box-value"><?= (int)($summary['treatments'] ?? 0) ?></div>
                    <div class="summary-box-meta">Variação: <?= htmlspecialchars($comparison['treatmentsDelta']['value'] ?? '—') ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section section-compact">
        <div class="section-title">Resumo detalhado</div>
        <table class="summary-table">
            <?php foreach ($summaryRows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['label']) ?></td>
                    <td><?= htmlspecialchars((string)$row['value']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="section section-compact">
        <div class="section-title">Leitura rápida</div>
        <div class="insights">
            <div class="insight-item"><strong>Género predominante:</strong> <?= htmlspecialchars($insights['dominantGender'] ?? 'Sem dados suficientes') ?></div>
            <div class="insight-item"><strong>Tratamento dominante:</strong> <?= htmlspecialchars($insights['topTreatmentType'] ?? 'Sem dados') ?></div>
            <div class="insight-item"><strong>Base de comparação:</strong> <?= htmlspecialchars($insights['comparisonLabel'] ?? 'Sem comparação') ?></div>
            <div class="insight-item"><strong>Variação de ocorrências:</strong> <?= htmlspecialchars($comparison['incidentsDelta']['value'] ?? '—') ?></div>
            <div class="insight-item"><strong>Variação de tratamentos:</strong> <?= htmlspecialchars($comparison['treatmentsDelta']['value'] ?? '—') ?></div>
        </div>
    </div>

    <?php foreach ($tables as $table): ?>
        <div class="section table-section">
            <div class="section-title"><?= htmlspecialchars($table['title']) ?></div>

            <div class="table-block">
            <?php if ($table['rows'] === []): ?>
                <div class="empty">Sem dados para o período selecionado.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= htmlspecialchars($table['label']) ?></th>
                            <th class="bar-cell">Distribuição</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table['rows'] as $row): ?>
                            <?php
                            $total = (int)($row['total'] ?? 0);
                            $width = $table['max'] > 0 ? (int)round(($total / $table['max']) * 100) : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($row[$table['key']] ?? '')) ?></td>
                                <td class="bar-cell">
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: <?= $width ?>%;"></div>
                                    </div>
                                </td>
                                <td><?= $total ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="footer">Relatório gerado automaticamente pelo sistema.</div>
</body>
</html>