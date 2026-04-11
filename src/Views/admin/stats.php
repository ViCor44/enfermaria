<?php
$baseUrl = '/enfermaria/public/index.php';

$chartCards = [
    [
        'id' => 'chartType',
        'title' => 'Tipo de Ocorrência',
        'description' => 'Prioriza a leitura das causas mais frequentes no período.',
        'labels' => array_column($typeStats, 'tipo'),
        'data' => array_map('intval', array_column($typeStats, 'total')),
        'empty' => 'Sem ocorrências registadas para o período selecionado.',
        'tone' => 'rose',
    ],
    [
        'id' => 'chartLocation',
        'title' => 'Ocorrências por Local',
        'description' => 'Mostra onde se concentram mais episódios.',
        'labels' => array_column($locationStats, 'local'),
        'data' => array_map('intval', array_column($locationStats, 'total')),
        'empty' => 'Sem locais com ocorrências no período selecionado.',
        'tone' => 'gold',
    ],
    [
        'id' => 'chartAge',
        'title' => 'Ocorrências por Faixa Etária',
        'description' => 'Ajuda a identificar padrões por grupo etário.',
        'labels' => array_column($ageStats, 'faixa'),
        'data' => array_map('intval', array_column($ageStats, 'total')),
        'empty' => 'Sem idades suficientes para gerar distribuição.',
        'tone' => 'blue',
    ],
    [
        'id' => 'chartGender',
        'title' => 'Ocorrências por Género',
        'description' => 'Valores já apresentados com etiquetas legíveis.',
        'labels' => array_column($genderStats, 'genero'),
        'data' => array_map('intval', array_column($genderStats, 'total')),
        'empty' => 'Sem informação de género no período selecionado.',
        'tone' => 'green',
    ],
    [
        'id' => 'chartTreatment',
        'title' => 'Tipo de Tratamento',
        'description' => 'Destaca os tratamentos mais aplicados.',
        'labels' => array_column($treatmentStats, 'tipo'),
        'data' => array_map('intval', array_column($treatmentStats, 'total')),
        'empty' => 'Sem tratamentos associados ao período selecionado.',
        'tone' => 'purple',
    ],
];

$comparisonIncidents = $comparison['incidentsDelta']['value'] ?? '—';
$comparisonTreatments = $comparison['treatmentsDelta']['value'] ?? '—';
$comparisonIncidentsClass = $comparison['incidentsDelta']['direction'] ?? 'neutral';
$comparisonTreatmentsClass = $comparison['treatmentsDelta']['direction'] ?? 'neutral';
?>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">
<style>
    :root {
        --stats-bg: linear-gradient(180deg, #eef4ff 0%, #f8fbff 48%, #eef3f8 100%);
        --stats-surface: rgba(255, 255, 255, 0.92);
        --stats-border: rgba(32, 90, 170, 0.08);
        --stats-text: #183153;
        --stats-muted: #5f7492;
        --stats-accent: #1f6feb;
        --stats-accent-soft: rgba(31, 111, 235, 0.12);
        --stats-shadow: 0 18px 45px rgba(38, 70, 120, 0.12);
        --stats-radius: 22px;
    }

    body {
        margin: 0;
        background: var(--stats-bg);
        color: var(--stats-text);
    }

    .stats-page {
        max-width: 1280px;
        margin: 0 auto;
        padding: 32px 24px 48px;
    }

    .stats-hero {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .stats-hero-copy {
        max-width: 720px;
    }

    .stats-title {
        margin: 0;
        font-size: clamp(2rem, 3vw, 2.8rem);
        color: var(--stats-accent);
        letter-spacing: -0.03em;
    }

    .stats-subtitle {
        margin: 10px 0 0;
        font-size: 1rem;
        line-height: 1.6;
        color: var(--stats-muted);
    }

    .stats-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .stats-button,
    .stats-button:visited {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 18px;
        border-radius: 999px;
        border: 1px solid transparent;
        text-decoration: none;
        font-weight: 600;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .stats-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(31, 111, 235, 0.18);
    }

    .stats-button-primary {
        background: linear-gradient(135deg, #1f6feb, #4185f4);
        color: #fff;
    }

    .stats-button-secondary {
        background: rgba(255, 255, 255, 0.75);
        color: var(--stats-accent);
        border-color: rgba(31, 111, 235, 0.22);
        backdrop-filter: blur(10px);
    }

    .stats-filter-panel,
    .summary-card,
    .chart-card,
    .insights-card {
        background: var(--stats-surface);
        border: 1px solid var(--stats-border);
        border-radius: var(--stats-radius);
        box-shadow: var(--stats-shadow);
        backdrop-filter: blur(14px);
    }

    .stats-filter-panel {
        padding: 22px;
        margin-bottom: 22px;
    }

    .stats-filter-head {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-start;
        margin-bottom: 18px;
    }

    .stats-filter-head h2,
    .section-title {
        margin: 0;
        font-size: 1.15rem;
        color: var(--stats-text);
    }

    .stats-filter-head p,
    .section-copy {
        margin: 6px 0 0;
        color: var(--stats-muted);
        line-height: 1.5;
    }

    .stats-filter-form {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        align-items: end;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-field label {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--stats-text);
    }

    .filter-field select,
    .filter-field input {
        width: 100%;
        height: 46px;
        border-radius: 14px;
        border: 1px solid rgba(24, 49, 83, 0.14);
        background: rgba(255, 255, 255, 0.95);
        padding: 0 14px;
        color: var(--stats-text);
        font-size: 0.95rem;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .stats-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .summary-card {
        position: relative;
        overflow: hidden;
        padding: 22px;
    }

    .summary-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(31, 111, 235, 0.16), transparent 42%);
        pointer-events: none;
    }

    .summary-label {
        position: relative;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--stats-muted);
    }

    .summary-value {
        position: relative;
        margin-top: 12px;
        font-size: clamp(1.9rem, 2.4vw, 2.5rem);
        font-weight: 800;
        color: var(--stats-text);
        line-height: 1.05;
    }

    .summary-meta {
        position: relative;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-top: 14px;
        color: var(--stats-muted);
        font-size: 0.92rem;
    }

    .trend-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 66px;
        min-height: 32px;
        padding: 0 10px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.84rem;
    }

    .trend-pill.up {
        background: rgba(18, 160, 82, 0.12);
        color: #0c8c46;
    }

    .trend-pill.down {
        background: rgba(224, 72, 72, 0.12);
        color: #bf2f2f;
    }

    .trend-pill.neutral {
        background: rgba(24, 49, 83, 0.08);
        color: #53657e;
    }

    .insights-card {
        padding: 22px;
        margin-bottom: 22px;
    }

    .insights-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .insight-chip {
        border-radius: 18px;
        padding: 14px 16px;
        background: rgba(31, 111, 235, 0.06);
        border: 1px solid rgba(31, 111, 235, 0.08);
    }

    .insight-chip strong {
        display: block;
        margin-bottom: 6px;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--stats-muted);
    }

    .insight-chip span {
        color: var(--stats-text);
        font-weight: 700;
        line-height: 1.4;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-end;
        margin-bottom: 16px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .chart-card {
        padding: 22px;
    }

    .chart-card[data-tone="blue"] { border-top: 4px solid rgba(31, 111, 235, 0.55); }
    .chart-card[data-tone="green"] { border-top: 4px solid rgba(17, 136, 94, 0.55); }
    .chart-card[data-tone="gold"] { border-top: 4px solid rgba(212, 143, 22, 0.55); }
    .chart-card[data-tone="rose"] { border-top: 4px solid rgba(207, 87, 93, 0.55); }
    .chart-card[data-tone="purple"] { border-top: 4px solid rgba(110, 90, 197, 0.55); }

    .chart-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .chart-header h3 {
        margin: 0;
        font-size: 1.08rem;
        color: var(--stats-text);
    }

    .chart-header p {
        margin: 6px 0 0;
        color: var(--stats-muted);
        line-height: 1.5;
        font-size: 0.92rem;
    }

    .chart-total {
        min-width: 60px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(24, 49, 83, 0.06);
        color: var(--stats-text);
        text-align: center;
        font-weight: 800;
        font-size: 0.86rem;
    }

    .chart-shell {
        position: relative;
        height: 280px;
    }

    .empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 280px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(31, 111, 235, 0.06), rgba(31, 111, 235, 0.02));
        border: 1px dashed rgba(31, 111, 235, 0.18);
        padding: 24px;
        text-align: center;
        color: var(--stats-muted);
        line-height: 1.6;
    }

    .chart-card--wide {
        grid-column: 1 / -1;
    }

    @media (max-width: 1080px) {
        .stats-summary-grid,
        .insights-grid,
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .stats-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .stats-page {
            padding: 20px 14px 36px;
        }

        .stats-hero,
        .stats-filter-head,
        .section-header,
        .summary-meta,
        .chart-header {
            flex-direction: column;
            align-items: stretch;
        }

        .stats-actions,
        .filter-actions {
            justify-content: stretch;
        }

        .stats-button,
        .stats-button:visited {
            width: 100%;
        }

        .stats-summary-grid,
        .insights-grid,
        .stats-grid,
        .stats-filter-form {
            grid-template-columns: 1fr;
        }

        .chart-shell,
        .empty-state {
            min-height: 240px;
            height: 240px;
        }
    }
</style>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main class="stats-page">
    <section class="stats-hero">
        <div class="stats-hero-copy">
            <h1 class="stats-title">Estatísticas</h1>
            <p class="stats-subtitle">
                Vista analítica das ocorrências e tratamentos com filtros temporais, comparação com o período anterior
                e leitura rápida dos indicadores mais relevantes.
            </p>
        </div>

        <div class="stats-actions">
            <a class="stats-button stats-button-secondary" href="<?= htmlspecialchars($exportUrl) ?>">Exportar CSV</a>
            <a class="stats-button stats-button-primary" href="<?= htmlspecialchars($baseUrl . '?route=admin_stats&period=30d') ?>">Ver últimos 30 dias</a>
        </div>
    </section>

    <section class="stats-filter-panel">
        <div class="stats-filter-head">
            <div>
                <h2>Filtros</h2>
                <p>Seleciona o intervalo temporal para atualizar todos os gráficos, os resumos e a exportação.</p>
            </div>
            <div class="chart-total"><?= htmlspecialchars($filters['rangeLabel']) ?></div>
        </div>

        <form class="stats-filter-form" method="get" action="<?= htmlspecialchars($baseUrl) ?>">
            <input type="hidden" name="route" value="admin_stats">

            <div class="filter-field">
                <label for="period">Período</label>
                <select id="period" name="period">
                    <option value="today" <?= $filters['period'] === 'today' ? 'selected' : '' ?>>Hoje</option>
                    <option value="7d" <?= $filters['period'] === '7d' ? 'selected' : '' ?>>Últimos 7 dias</option>
                    <option value="30d" <?= $filters['period'] === '30d' ? 'selected' : '' ?>>Últimos 30 dias</option>
                    <option value="month" <?= $filters['period'] === 'month' ? 'selected' : '' ?>>Mês atual</option>
                    <option value="year" <?= $filters['period'] === 'year' ? 'selected' : '' ?>>Ano atual</option>
                    <option value="all" <?= $filters['period'] === 'all' ? 'selected' : '' ?>>Todo o histórico</option>
                    <option value="custom" <?= $filters['period'] === 'custom' ? 'selected' : '' ?>>Personalizado</option>
                </select>
            </div>

            <div class="filter-field">
                <label for="from">Data inicial</label>
                <input id="from" type="date" name="from" value="<?= htmlspecialchars((string)($filters['fromDate'] ?? '')) ?>">
            </div>

            <div class="filter-field">
                <label for="to">Data final</label>
                <input id="to" type="date" name="to" value="<?= htmlspecialchars((string)($filters['toDate'] ?? '')) ?>">
            </div>

            <div class="filter-actions">
                <button class="stats-button stats-button-primary" type="submit">Aplicar filtros</button>
                <a class="stats-button stats-button-secondary" href="<?= htmlspecialchars($baseUrl . '?route=admin_stats&period=all') ?>">Limpar</a>
            </div>
        </form>
    </section>

    <section class="stats-summary-grid">
        <article class="summary-card">
            <div class="summary-label">Ocorrências</div>
            <div class="summary-value"><?= (int)$summary['incidents'] ?></div>
            <div class="summary-meta">
                <span><?= htmlspecialchars($comparison['previousLabel']) ?></span>
                <span class="trend-pill <?= htmlspecialchars($comparisonIncidentsClass) ?>"><?= htmlspecialchars($comparisonIncidents) ?></span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-label">Tratamentos</div>
            <div class="summary-value"><?= (int)$summary['treatments'] ?></div>
            <div class="summary-meta">
                <span>Comparação temporal</span>
                <span class="trend-pill <?= htmlspecialchars($comparisonTreatmentsClass) ?>"><?= htmlspecialchars($comparisonTreatments) ?></span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-label">Local mais frequente</div>
            <div class="summary-value"><?= htmlspecialchars($summary['topLocation']['local'] ?? 'Sem dados') ?></div>
            <div class="summary-meta">
                <span>Ocorrências concentradas</span>
                <span class="trend-pill neutral"><?= (int)($summary['topLocation']['total'] ?? 0) ?></span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-label">Tipo principal</div>
            <div class="summary-value"><?= htmlspecialchars($summary['topIncidentType']['tipo'] ?? 'Sem dados') ?></div>
            <div class="summary-meta">
                <span>Maior incidência no período</span>
                <span class="trend-pill neutral"><?= (int)($summary['topIncidentType']['total'] ?? 0) ?></span>
            </div>
        </article>
    </section>

    <section class="insights-card">
        <h2 class="section-title">Leitura rápida</h2>
        <p class="section-copy">Resumo executivo com os principais destaques do período filtrado.</p>

        <div class="insights-grid">
            <div class="insight-chip">
                <strong>Período analisado</strong>
                <span><?= htmlspecialchars($filters['rangeLabel']) ?></span>
            </div>
            <div class="insight-chip">
                <strong>Género predominante</strong>
                <span><?= htmlspecialchars($insights['dominantGender'] ?? 'Sem dados suficientes') ?></span>
            </div>
            <div class="insight-chip">
                <strong>Tratamento dominante</strong>
                <span><?= htmlspecialchars($insights['topTreatmentType']) ?></span>
            </div>
            <div class="insight-chip">
                <strong>Base de comparação</strong>
                <span><?= htmlspecialchars($insights['comparisonLabel']) ?></span>
            </div>
        </div>
    </section>

    <section>
        <div class="section-header">
            <div>
                <h2 class="section-title">Distribuição</h2>
                <p class="section-copy">Gráficos com eixos normalizados, valores por barra e estado vazio quando não existem dados.</p>
            </div>
        </div>

        <div class="stats-grid">
            <?php foreach ($chartCards as $index => $chart): ?>
                <?php $total = array_sum($chart['data']); ?>
                <article class="chart-card <?= $index === 4 ? 'chart-card--wide' : '' ?>" data-tone="<?= htmlspecialchars($chart['tone']) ?>">
                    <div class="chart-header">
                        <div>
                            <h3><?= htmlspecialchars($chart['title']) ?></h3>
                            <p><?= htmlspecialchars($chart['description']) ?></p>
                        </div>
                        <div class="chart-total"><?= (int)$total ?></div>
                    </div>

                    <?php if ($chart['data'] === []): ?>
                        <div class="empty-state"><?= htmlspecialchars($chart['empty']) ?></div>
                    <?php else: ?>
                        <div class="chart-shell">
                            <canvas id="<?= htmlspecialchars($chart['id']) ?>"></canvas>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const charts = <?= json_encode($chartCards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

const periodField = document.getElementById('period');
const fromField = document.getElementById('from');
const toField = document.getElementById('to');

const syncCustomFields = () => {
    const isCustom = periodField.value === 'custom';
    fromField.disabled = !isCustom;
    toField.disabled = !isCustom;
};

periodField.addEventListener('change', syncCustomFields);
syncCustomFields();

const paletteMap = {
    blue: { fill: 'rgba(31, 111, 235, 0.58)', stroke: 'rgba(31, 111, 235, 0.95)' },
    green: { fill: 'rgba(17, 136, 94, 0.58)', stroke: 'rgba(17, 136, 94, 0.95)' },
    gold: { fill: 'rgba(212, 143, 22, 0.58)', stroke: 'rgba(212, 143, 22, 0.95)' },
    rose: { fill: 'rgba(207, 87, 93, 0.58)', stroke: 'rgba(207, 87, 93, 0.95)' },
    purple: { fill: 'rgba(110, 90, 197, 0.58)', stroke: 'rgba(110, 90, 197, 0.95)' },
};

const valueLabelPlugin = {
    id: 'valueLabelPlugin',
    afterDatasetsDraw(chart) {
        const { ctx } = chart;
        const meta = chart.getDatasetMeta(0);
        ctx.save();
        ctx.fillStyle = '#35506f';
        ctx.font = '700 12px Segoe UI';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';

        meta.data.forEach((bar, index) => {
            const value = chart.data.datasets[0].data[index];
            ctx.fillText(String(value), bar.x, bar.y - 6);
        });

        ctx.restore();
    }
};

Chart.register(valueLabelPlugin);

const buildChart = (chart) => {
    const target = document.getElementById(chart.id);
    if (!target) {
        return;
    }

    const palette = paletteMap[chart.tone] || paletteMap.blue;

    new Chart(target, {
        type: 'bar',
        data: {
            labels: chart.labels,
            datasets: [{
                label: 'Total',
                data: chart.data,
                backgroundColor: palette.fill,
                borderColor: palette.stroke,
                borderRadius: 10,
                borderWidth: 1.5,
                maxBarThickness: 56,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 650,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(24, 49, 83, 0.94)',
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: (context) => `Total: ${context.parsed.y}`
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#5f7492',
                        maxRotation: 18,
                        minRotation: 0,
                        autoSkip: false,
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#5f7492'
                    },
                    grid: {
                        color: 'rgba(95, 116, 146, 0.14)'
                    }
                }
            }
        }
    });
};

charts.forEach((chart) => {
    if (Array.isArray(chart.data) && chart.data.length > 0) {
        buildChart(chart);
    }
});
</script>