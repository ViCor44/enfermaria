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
            border-bottom: 2px solid #1f6feb;
            padding-bottom: 14px;
            margin-bottom: 18px;
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
        }

        .meta-row {
            margin-top: 4px;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            font-size: 15px;
            color: #183153;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .summary-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
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
        <div class="title">Relatório de Estatísticas</div>
        <p class="subtitle">Sistema de Apoio à Enfermaria</p>

        <div class="meta">
            <div class="meta-row"><strong>Período:</strong> <?= htmlspecialchars($filters['rangeLabel']) ?></div>
            <div class="meta-row"><strong>Filtro:</strong> <?= htmlspecialchars($filters['label']) ?></div>
            <div class="meta-row"><strong>Gerado em:</strong> <?= htmlspecialchars($generatedAt) ?></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Resumo</div>
        <table class="summary-table">
            <?php foreach ($summaryRows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['label']) ?></td>
                    <td><?= htmlspecialchars((string)$row['value']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="section">
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
        <div class="section">
            <div class="section-title"><?= htmlspecialchars($table['title']) ?></div>

            <?php if ($table['rows'] === []): ?>
                <div class="empty">Sem dados para o período selecionado.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= htmlspecialchars($table['label']) ?></th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table['rows'] as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($row[$table['key']] ?? '')) ?></td>
                                <td><?= (int)($row['total'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="footer">Relatório gerado automaticamente pelo sistema.</div>
</body>
</html>