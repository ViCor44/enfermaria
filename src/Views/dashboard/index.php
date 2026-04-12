<?php
$nome = $nome ?? ($_SESSION['user_name'] ?? 'Utilizador');
$role = $role ?? ($_SESSION['role'] ?? '');
$baseUrl = $baseUrl ?? '/enfermaria/public/index.php';
$today = date('Y-m-d');

$AcidentesHoje = $AcidentesHoje ?? 0;
$tratamentosEmCurso = $tratamentosEmCurso ?? 0;
$completedTreatmentsToday = $completedTreatmentsToday ?? 0;
$pendingApprovals = $pendingApprovals ?? 0;
$currentDate = $currentDate ?? date('d/m/Y');
$lastLogin = $lastLogin ?? 'Sem registo';

$incidentTrend = $incidentTrend ?? ['label' => 'Sem variação face a ontem', 'value' => '0%', 'direction' => 'neutral'];
$recentIncidents = $recentIncidents ?? [];
$isNurse = ($role === 'Enfermeiro');

$incidentTrendClass = 'trend-neutral';
if (($incidentTrend['direction'] ?? '') === 'up') {
    $incidentTrendClass = 'trend-up';
} elseif (($incidentTrend['direction'] ?? '') === 'down') {
    $incidentTrendClass = 'trend-down';
}

$todayIncidentsHref = $baseUrl . '?route=admin_incidents&from=' . $today . '&to=' . $today;
$inProgressTreatmentsHref = $baseUrl . '?route=admin_treatments&status=em_curso';
$newIncidentHref = $baseUrl . '?route=incidents_new';
$newInternalHref = $baseUrl . '?route=internal_new';
$newTreatmentHref = $baseUrl . '?route=treatments_new';
$statsHref = $baseUrl . '?route=admin_stats';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Enfermaria | Dashboard</title>
    <link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --dash-bg: #f3f7fc;
            --dash-surface: #ffffff;
            --dash-surface-soft: #f8fbff;
            --dash-text: #16324f;
            --dash-muted: #62748b;
            --dash-accent: #1f6feb;
            --dash-border: #d9e4f2;
            --dash-shadow: 0 14px 32px rgba(16, 38, 70, 0.08);
            --dash-radius: 16px;
        }

        body {
            margin: 0;
            font-family: 'Manrope', 'Segoe UI', sans-serif;
            background: var(--dash-bg);
            color: var(--dash-text);
        }

        .dashboard-page {
            max-width: 1240px;
            margin: 0 auto;
            padding: 30px 22px 44px;
        }

        .hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .hero h1 {
            margin: 0;
            color: var(--dash-accent);
            font-size: clamp(2rem, 3vw, 2.5rem);
            letter-spacing: -0.03em;
        }

        .hero p {
            margin: 10px 0 0;
            color: var(--dash-muted);
            line-height: 1.55;
            max-width: 68ch;
        }

        .status-pill {
            background: #e9f2ff;
            border: 1px solid #cfe0fb;
            color: #1f6feb;
            border-radius: 999px;
            padding: 9px 14px;
            font-weight: 700;
            font-size: 0.86rem;
            white-space: nowrap;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .quick-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            border-radius: 999px;
            border: 1px solid transparent;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }

        .quick-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(31, 111, 235, 0.16);
        }

        .quick-btn-primary {
            background: var(--dash-accent);
            color: #ffffff;
        }

        .quick-btn-soft {
            background: #ffffff;
            border-color: #cfe0fb;
            color: #245baf;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr;
            gap: 14px;
            margin-bottom: 18px;
        }

        .metric-card {
            background: var(--dash-surface);
            border: 1px solid var(--dash-border);
            border-radius: var(--dash-radius);
            box-shadow: var(--dash-shadow);
            padding: 18px;
        }

        .metric-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            margin-bottom: 8px;
        }

        .metric-label {
            margin: 0;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--dash-muted);
            font-weight: 800;
        }

        .metric-value {
            margin: 0;
            font-size: clamp(2rem, 2.8vw, 2.8rem);
            line-height: 1;
            font-weight: 800;
            color: #163a68;
            letter-spacing: -0.03em;
        }

        .metric-sub {
            margin-top: 10px;
            color: var(--dash-muted);
            font-size: 0.92rem;
            line-height: 1.45;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
        }

        .trend-chip {
            min-width: 66px;
            text-align: center;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .trend-up {
            background: #e7f8ee;
            color: #0d8e49;
        }

        .trend-down {
            background: #fdeced;
            color: #c4363c;
        }

        .trend-neutral {
            background: #edf2f8;
            color: #5f7390;
        }

        .metric-highlight {
            border-color: #cfe0fb;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
        }

        .secondary-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 14px;
        }

        .panel {
            background: var(--dash-surface);
            border: 1px solid var(--dash-border);
            border-radius: var(--dash-radius);
            box-shadow: var(--dash-shadow);
            padding: 18px;
        }

        .panel h2 {
            margin: 0;
            font-size: 1.1rem;
            color: #1b3f6f;
        }

        .panel-copy {
            margin: 8px 0 14px;
            color: var(--dash-muted);
            font-size: 0.94rem;
        }

        .activity-list {
            display: grid;
            gap: 10px;
        }

        .activity-item {
            border: 1px solid #dfe8f5;
            border-radius: 12px;
            background: var(--dash-surface-soft);
            padding: 12px 13px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: center;
        }

        .activity-main {
            color: #1d3f6d;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .activity-meta {
            margin-top: 4px;
            color: var(--dash-muted);
            font-size: 0.84rem;
        }

        .activity-time {
            color: #4f6786;
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .empty-state {
            border: 1px dashed #cfdced;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            color: #61758f;
            background: #fbfdff;
        }

        .meta-list {
            display: grid;
            gap: 10px;
            margin-top: 4px;
        }

        .meta-row {
            border: 1px solid #e0e9f7;
            border-radius: 12px;
            padding: 11px 12px;
            background: #f8fbff;
        }

        .meta-row strong {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #5d7491;
            margin-bottom: 4px;
        }

        .meta-row span {
            color: #1c3f6d;
            font-weight: 700;
        }

        @media (max-width: 980px) {
            .metrics-grid,
            .secondary-grid,
            .quick-actions {
                grid-template-columns: 1fr;
            }

            .hero {
                flex-direction: column;
                align-items: stretch;
            }

            .status-pill {
                width: fit-content;
            }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="dashboard-page">
    <section class="hero">
        <div>
            <h1>Painel de Operações</h1>
            <p>Bem-vindo, <?= htmlspecialchars($nome) ?>. Aqui tens uma leitura rápida do estado da enfermaria, com foco no que exige ação imediata.</p>
        </div>
        <div class="status-pill">Atualizado em <?= htmlspecialchars($currentDate) ?></div>
    </section>

    <section class="quick-actions">
        <?php if ($isNurse): ?>
            <a class="quick-btn quick-btn-primary" href="<?= htmlspecialchars($newIncidentHref) ?>">Nova ocorrência</a>
            <a class="quick-btn quick-btn-soft" href="<?= htmlspecialchars($newInternalHref) ?>">Novo registo interno</a>
            <a class="quick-btn quick-btn-soft" href="<?= htmlspecialchars($newTreatmentHref) ?>">Novo tratamento</a>
        <?php else: ?>
            <a class="quick-btn quick-btn-primary" href="<?= htmlspecialchars($todayIncidentsHref) ?>">Ver ocorrências de hoje</a>
            <a class="quick-btn quick-btn-soft" href="<?= htmlspecialchars($inProgressTreatmentsHref) ?>">Ver tratamentos em curso</a>
            <a class="quick-btn quick-btn-soft" href="<?= htmlspecialchars($statsHref) ?>">Abrir estatísticas</a>
        <?php endif; ?>
    </section>

    <section class="metrics-grid">
        <a class="metric-card metric-highlight" href="<?= htmlspecialchars($todayIncidentsHref) ?>">
            <div class="metric-head">
                <p class="metric-label">Ocorrências hoje</p>
                <span class="trend-chip <?= htmlspecialchars($incidentTrendClass) ?>"><?= htmlspecialchars($incidentTrend['value'] ?? '0%') ?></span>
            </div>
            <p class="metric-value"><?= (int)$AcidentesHoje ?></p>
            <div class="metric-sub">
                <span><?= htmlspecialchars($incidentTrend['label'] ?? 'Sem variação') ?></span>
                <span>Ver detalhe</span>
            </div>
        </a>

        <a class="metric-card" href="<?= htmlspecialchars($inProgressTreatmentsHref) ?>">
            <div class="metric-head">
                <p class="metric-label">Tratamentos em curso</p>
            </div>
            <p class="metric-value"><?= (int)$tratamentosEmCurso ?></p>
            <div class="metric-sub">
                <span>Concluídos hoje: <?= (int)$completedTreatmentsToday ?></span>
                <span>Ver lista</span>
            </div>
        </a>

        <div class="metric-card">
            <div class="metric-head">
                <p class="metric-label">Último acesso</p>
            </div>
            <p class="metric-value" style="font-size: 1.65rem;"><?= htmlspecialchars($lastLogin) ?></p>
            <div class="metric-sub">
                <span>Pendências administrativas</span>
                <span class="trend-chip trend-neutral"><?= (int)$pendingApprovals ?></span>
            </div>
        </div>
    </section>

    <section class="secondary-grid">
        <div class="panel">
            <h2>Atividade recente</h2>
            <p class="panel-copy">Últimas ocorrências registadas para apoio rápido à supervisão.</p>

            <?php if ($recentIncidents === []): ?>
                <div class="empty-state">Sem ocorrências recentes para apresentar.</div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($recentIncidents as $incident): ?>
                        <div class="activity-item">
                            <div>
                                <div class="activity-main">#<?= (int)$incident['id'] ?> · <?= htmlspecialchars($incident['incident_type_name'] ?? 'Tipo não definido') ?></div>
                                <div class="activity-meta">
                                    <?= htmlspecialchars($incident['location_name'] ?? 'Local não definido') ?> · <?= htmlspecialchars($incident['nurse_name'] ?? 'Sem enfermeiro') ?>
                                </div>
                            </div>
                            <div class="activity-time"><?= htmlspecialchars(date('d/m H:i', strtotime((string)$incident['occurred_at']))) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="panel">
            <h2>Estado rápido</h2>
            <p class="panel-copy">Resumo para orientação operacional durante o turno.</p>

            <div class="meta-list">
                <div class="meta-row">
                    <strong>Perfil atual</strong>
                    <span><?= htmlspecialchars($role !== '' ? $role : 'Sem perfil') ?></span>
                </div>
                <div class="meta-row">
                    <strong>Dia em análise</strong>
                    <span><?= htmlspecialchars($currentDate) ?></span>
                </div>
                <div class="meta-row">
                    <strong>Tratamentos concluídos hoje</strong>
                    <span><?= (int)$completedTreatmentsToday ?></span>
                </div>
                <div class="meta-row">
                    <strong>Aprovações pendentes</strong>
                    <span><?= (int)$pendingApprovals ?></span>
                </div>
            </div>
        </aside>
    </section>
</main>
</body>
</html>
