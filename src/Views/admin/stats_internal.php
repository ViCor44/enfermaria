<?php
$baseUrl = '/enfermaria/public/index.php';

$listBase = $baseUrl . '?route=admin_internal_records'
    . ($filters['fromDate'] ? '&from=' . urlencode($filters['fromDate']) : '')
    . ($filters['toDate']   ? '&to='   . urlencode($filters['toDate'])   : '');

$chartCards = [
    [
        'id'          => 'chartLocation',
        'title'       => 'Registos por Local',
        'description' => 'Mostra onde se concentram mais registos internos.',
        'labels'      => array_column($locationStats, 'local'),
        'data'        => array_map('intval', array_column($locationStats, 'total')),
        'empty'       => 'Sem locais com registos no período selecionado.',
        'tone'        => 'gold',
        'clickUrl'    => $listBase,
        'locationIds' => array_column($locationStats, 'location_id'),
    ],
    [
        'id'          => 'chartGender',
        'title'       => 'Registos por Género',
        'description' => 'Distribuição por género dos utentes registados.',
        'labels'      => array_column($genderStats, 'genero'),
        'data'        => array_map('intval', array_column($genderStats, 'total')),
        'empty'       => 'Sem informação de género no período selecionado.',
        'tone'        => 'green',
        'clickUrl'    => $listBase,
    ],
    [
        'id'          => 'chartAge',
        'title'       => 'Registos por Faixa Etária',
        'description' => 'Ajuda a identificar padrões por grupo etário.',
        'labels'      => array_column($ageStats, 'faixa'),
        'data'        => array_map('intval', array_column($ageStats, 'total')),
        'empty'       => 'Sem idades suficientes para gerar distribuição.',
        'tone'        => 'blue',
        'clickUrl'    => $listBase,
    ],
    [
        'id'          => 'chartEmployee',
        'title'       => 'Colaborador vs Utente externo',
        'description' => 'Proporção entre colaboradores e utentes externos.',
        'labels'      => array_column($employeeStats, 'tipo'),
        'data'        => array_map('intval', array_column($employeeStats, 'total')),
        'empty'       => 'Sem registos no período selecionado.',
        'tone'        => 'rose',
        'clickUrl'    => $listBase,
    ],
    [
        'id'          => 'chartTreatment',
        'title'       => 'Tipo de Tratamento',
        'description' => 'Tratamentos mais aplicados nos registos internos.',
        'labels'      => array_column($treatmentStats, 'tipo'),
        'data'        => array_map('intval', array_column($treatmentStats, 'total')),
        'empty'       => 'Sem tratamentos associados ao período selecionado.',
        'tone'        => 'purple',
        'wide'        => true,
        'clickUrl'    => $listBase,
    ],
];

$comparisonRecords      = $comparison['recordsDelta']['value'] ?? '—';
$comparisonRecordsClass = $comparison['recordsDelta']['direction'] ?? 'neutral';
?>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --stats-bg: #f4f7fb;
        --stats-surface: #ffffff;
        --stats-surface-alt: #f8fbff;
        --stats-border: #d8e3f2;
        --stats-text: #183153;
        --stats-muted: #62748b;
        --stats-accent: #1f6feb;
        --stats-accent-soft: #e8f1ff;
        --stats-shadow: 0 10px 26px rgba(17, 37, 68, 0.08);
        --stats-radius: 18px;
    }

    body {
        margin: 0;
        background: var(--stats-bg);
        color: var(--stats-text);
        font-family: 'Manrope', 'Segoe UI', sans-serif;
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

    .stats-hero-copy { max-width: 720px; }

    .stats-title {
        margin: 0;
        font-size: clamp(2rem, 3vw, 2.6rem);
        color: var(--stats-accent);
        letter-spacing: -0.03em;
        font-weight: 800;
    }

    .stats-subtitle {
        margin: 10px 0 0;
        max-width: 62ch;
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
        background: #1f6feb;
        color: #fff;
    }

    .stats-button-secondary {
        background: #ffffff;
        color: var(--stats-accent);
        border-color: #cfe0fb;
    }

    /* Tab toggle */
    .stats-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 2px solid var(--stats-border);
        padding-bottom: 0;
    }

    .stats-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        border-radius: 10px 10px 0 0;
        border: 1px solid transparent;
        border-bottom: none;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--stats-muted);
        background: transparent;
        transition: background 0.18s, color 0.18s;
        position: relative;
        bottom: -2px;
    }

    .stats-tab:hover {
        background: var(--stats-accent-soft);
        color: var(--stats-accent);
    }

    .stats-tab.active {
        background: var(--stats-surface);
        color: var(--stats-accent);
        border-color: var(--stats-border);
        border-bottom-color: var(--stats-surface);
    }

    .stats-filter-panel,
    .summary-card,
    .chart-card,
    .insights-card {
        background: var(--stats-surface);
        border: 1px solid var(--stats-border);
        border-radius: var(--stats-radius);
        box-shadow: var(--stats-shadow);
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
        font-size: 1.12rem;
        font-weight: 800;
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
        border-radius: 12px;
        border: 1px solid rgba(24, 49, 83, 0.14);
        background: #ffffff;
        padding: 0 14px;
        color: var(--stats-text);
        font-size: 0.95rem;
        font-family: inherit;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .stats-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .comparison-banner {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 16px;
        align-items: stretch;
        margin-bottom: 16px;
        padding: 18px;
        background: var(--stats-surface-alt);
        border: 1px solid #dbe7f6;
        border-radius: 14px;
    }

    .comparison-banner strong {
        display: block;
        margin-bottom: 4px;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--stats-muted);
    }

    .comparison-banner span { color: var(--stats-text); font-weight: 700; }
    .comparison-note { color: var(--stats-muted); font-size: 0.9rem; line-height: 1.5; }

    .comparison-banner-intro {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .comparison-period-box {
        padding: 14px 16px;
        background: #ffffff;
        border: 1px solid #dbe7f6;
        border-radius: 12px;
    }

    .comparison-period-box.current { border-color: #c9dcfb; background: #f7fbff; }
    .comparison-period-box.base    { background: #fbfcfe; }

    .comparison-period-box .label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--stats-muted);
    }

    .comparison-period-box .value {
        color: var(--stats-text);
        font-weight: 800;
        line-height: 1.4;
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
        background: linear-gradient(180deg, rgba(232, 241, 255, 0.85), rgba(255, 255, 255, 0));
        pointer-events: none;
    }

    .summary-label {
        position: relative;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--stats-muted);
    }

    .summary-value {
        position: relative;
        margin-top: 12px;
        font-size: clamp(1.9rem, 2.4vw, 2.45rem);
        font-weight: 800;
        color: var(--stats-text);
        line-height: 1.05;
        letter-spacing: -0.03em;
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

    .trend-pill.up      { background: #e6f7ee; color: #0c8c46; }
    .trend-pill.down    { background: #fdecec; color: #bf2f2f; }
    .trend-pill.neutral { background: #eef3f9; color: #53657e; }

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

    .chart-card { padding: 22px; }

    .chart-card--wide { grid-column: 1 / -1; }

    .chart-card[data-tone="blue"]   { border-top: 4px solid #4b8ef7; }
    .chart-card[data-tone="green"]  { border-top: 4px solid #1f9a68; }
    .chart-card[data-tone="gold"]   { border-top: 4px solid #d79b21; }
    .chart-card[data-tone="rose"]   { border-top: 4px solid #d15f67; }
    .chart-card[data-tone="purple"] { border-top: 4px solid #7761d8; }

    .chart-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .chart-header h3 { margin: 0; font-size: 1.08rem; font-weight: 800; color: var(--stats-text); }
    .chart-header p  { margin: 6px 0 0; color: var(--stats-muted); line-height: 1.5; font-size: 0.92rem; }

    .chart-total {
        min-width: 60px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eff4fb;
        color: var(--stats-text);
        text-align: center;
        font-weight: 800;
        font-size: 0.86rem;
    }

    .chart-shell { position: relative; height: 280px; }

    .empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 280px;
        border-radius: 14px;
        background: #f8fbff;
        border: 1px dashed #cfe0fb;
        padding: 24px;
        text-align: center;
        color: var(--stats-muted);
        line-height: 1.6;
    }

    @media (max-width: 1080px) {
        .stats-summary-grid,
        .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .stats-filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 760px) {
        .stats-page { padding: 20px 14px 36px; }
        .stats-hero,
        .stats-filter-head,
        .comparison-banner,
        .section-header,
        .summary-meta,
        .chart-header { flex-direction: column; align-items: stretch; }
        .stats-actions,
        .filter-actions { justify-content: stretch; }
        .stats-button,
        .stats-button:visited { width: 100%; }
        .stats-summary-grid,
        .stats-grid,
        .comparison-banner,
        .stats-filter-form { grid-template-columns: 1fr; }
        .chart-shell,
        .empty-state { min-height: 240px; height: 240px; }
    }
</style>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main class="stats-page">
    <section class="stats-hero">
        <div class="stats-hero-copy">
            <h1 class="stats-title">Estatísticas</h1>
            <p class="stats-subtitle">
                Vista analítica dos registos internos com filtros temporais e comparação com o período anterior.
            </p>
        </div>

        <div class="stats-actions">
            <a class="stats-button stats-button-secondary" href="<?= htmlspecialchars($exportUrl) ?>">Exportar CSV</a>
            <a class="stats-button stats-button-primary" href="<?= htmlspecialchars($baseUrl . '?route=admin_stats_internal&period=30d') ?>">Ver últimos 30 dias</a>
        </div>
    </section>

    <!-- Tab toggle -->
    <nav class="stats-tabs" aria-label="Tipo de estatísticas">
        <a class="stats-tab" href="<?= htmlspecialchars($baseUrl . '?route=admin_stats&period=' . htmlspecialchars($filters['period'])) ?>">
            Ocorrências
        </a>
        <a class="stats-tab active" href="<?= htmlspecialchars($baseUrl . '?route=admin_stats_internal&period=' . htmlspecialchars($filters['period'])) ?>" aria-current="page">
            Registos Internos
        </a>
    </nav>

    <section class="stats-filter-panel">
        <div class="stats-filter-head">
            <div>
                <h2>Filtros</h2>
                <p>Seleciona o intervalo temporal para atualizar todos os gráficos e os resumos.</p>
            </div>
            <div class="chart-total"><?= htmlspecialchars($filters['rangeLabel']) ?></div>
        </div>

        <form class="stats-filter-form" method="get" action="<?= htmlspecialchars($baseUrl) ?>">
            <input type="hidden" name="route" value="admin_stats_internal">

            <div class="filter-field">
                <label for="period">Período</label>
                <select id="period" name="period">
                    <option value="today"  <?= $filters['period'] === 'today'  ? 'selected' : '' ?>>Hoje</option>
                    <option value="7d"     <?= $filters['period'] === '7d'     ? 'selected' : '' ?>>Últimos 7 dias</option>
                    <option value="30d"    <?= $filters['period'] === '30d'    ? 'selected' : '' ?>>Últimos 30 dias</option>
                    <option value="month"  <?= $filters['period'] === 'month'  ? 'selected' : '' ?>>Mês atual</option>
                    <option value="year"   <?= $filters['period'] === 'year'   ? 'selected' : '' ?>>Ano atual</option>
                    <option value="all"    <?= $filters['period'] === 'all'    ? 'selected' : '' ?>>Todo o histórico</option>
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
                <a class="stats-button stats-button-secondary" href="<?= htmlspecialchars($baseUrl . '?route=admin_stats_internal&period=all') ?>">Limpar</a>
            </div>
        </form>
    </section>

    <section class="comparison-banner">
        <div class="comparison-banner-intro">
            <strong>Base de comparação</strong>
            <span class="comparison-note">As variações são calculadas comparando o período analisado com a base apresentada ao lado.</span>
        </div>
        <div class="comparison-period-box current">
            <span class="label">Período analisado</span>
            <span class="value"><?= htmlspecialchars($filters['rangeLabel']) ?></span>
        </div>
        <div class="comparison-period-box base">
            <span class="label">Base de comparação</span>
            <span class="value"><?= htmlspecialchars($comparison['previousLabel']) ?></span>
        </div>
    </section>

    <section class="stats-summary-grid">
        <article class="summary-card">
            <div class="summary-label">Registos Internos</div>
            <div class="summary-value"><?= (int)$summary['records'] ?></div>
            <div class="summary-meta">
                <span>Variação vs base</span>
                <span class="trend-pill <?= htmlspecialchars($comparisonRecordsClass) ?>"><?= htmlspecialchars($comparisonRecords) ?></span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-label">Local mais frequente</div>
            <div class="summary-value"><?= htmlspecialchars($summary['topLocation']['local'] ?? 'Sem dados') ?></div>
            <div class="summary-meta">
                <span>Registos concentrados</span>
                <span class="trend-pill neutral"><?= (int)($summary['topLocation']['total'] ?? 0) ?></span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-label">Colaboradores vs Utentes ext.</div>
            <?php
                $empTotal = array_sum(array_column($employeeStats, 'total'));
                $empRow   = array_values(array_filter($employeeStats, fn($r) => $r['tipo'] === 'Colaborador'))[0] ?? null;
                $empPct   = $empTotal > 0 && $empRow ? (int)round(((int)$empRow['total'] / $empTotal) * 100) : 0;
            ?>
            <div class="summary-value"><?= $empPct ?>%</div>
            <div class="summary-meta">
                <span>Percentagem de colaboradores</span>
                <span class="trend-pill neutral"><?= (int)($empRow['total'] ?? 0) ?> col.</span>
            </div>
        </article>
    </section>

    <section>
        <div class="section-header">
            <div>
                <h2 class="section-title">Distribuição</h2>
                <p class="section-copy">Gráficos com os registos internos distribuídos por local, género, faixa etária e tipo de utente.</p>
            </div>
        </div>

        <div class="stats-grid">
            <?php foreach ($chartCards as $chart): ?>
                <?php $total = array_sum($chart['data']); ?>
                <article class="chart-card <?= !empty($chart['wide']) ? 'chart-card--wide' : '' ?>" data-tone="<?= htmlspecialchars($chart['tone']) ?>">
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
const fromField   = document.getElementById('from');
const toField     = document.getElementById('to');

const syncCustomFields = () => {
    const isCustom = periodField.value === 'custom';
    fromField.disabled = !isCustom;
    toField.disabled   = !isCustom;
};

periodField.addEventListener('change', syncCustomFields);
syncCustomFields();

const paletteMap = {
    blue:   { fill: 'rgba(31, 111, 235, 0.58)',  stroke: 'rgba(31, 111, 235, 0.95)'  },
    green:  { fill: 'rgba(17, 136, 94, 0.58)',   stroke: 'rgba(17, 136, 94, 0.95)'   },
    gold:   { fill: 'rgba(212, 143, 22, 0.58)',  stroke: 'rgba(212, 143, 22, 0.95)'  },
    rose:   { fill: 'rgba(207, 87, 93, 0.58)',   stroke: 'rgba(207, 87, 93, 0.95)'   },
    purple: { fill: 'rgba(110, 90, 197, 0.58)',  stroke: 'rgba(110, 90, 197, 0.95)'  },
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
    if (!target) return;

    const palette = paletteMap[chart.tone] || paletteMap.blue;
    const hasClick = !!chart.clickUrl;

    if (hasClick) target.style.cursor = 'pointer';

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
            animation: { duration: 650, easing: 'easeOutQuart' },
            onClick: hasClick ? (evt, elements) => {
                if (!elements.length) return;
                const idx = elements[0].index;
                let url = chart.clickUrl;
                if (chart.locationIds && chart.locationIds[idx]) {
                    url += '&location_id=' + encodeURIComponent(chart.locationIds[idx]);
                }
                window.location.href = url;
            } : undefined,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(24, 49, 83, 0.94)',
                    padding: 12,
                    displayColors: false,
                    callbacks: { label: (context) => `Total: ${context.parsed.y}` + (hasClick ? ' — clique para ver lista' : '') }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#5f7492', maxRotation: 18, minRotation: 0, autoSkip: false, font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: '#5f7492' },
                    grid: { color: 'rgba(95, 116, 146, 0.14)' }
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
