<?php
$baseUrl = '/enfermaria/public/index.php';
require __DIR__ . '/../layouts/header.php';
?>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">
<style>
    body { margin:0; font-family:system-ui,sans-serif; background:#f3f6fb; }
    header {
        background:#1f6feb; color:#fff; padding:1rem 2rem;
        display:flex; justify-content:space-between; align-items:center;
    }
    main { max-width:1100px; margin:0 auto; padding:2rem; }
    h1 { margin-top:0; }

    .card {
        background:#fff; border-radius:12px; padding:1.5rem;
        box-shadow:0 8px 20px rgba(0,0,0,.06);
        margin-bottom:1.5rem;
    }
    .card h2 { margin-top:0; font-size:1.05rem; }

    .row { display:flex; flex-wrap:wrap; gap:1.5rem; }
    .row > div { flex:1; min-width:180px; }

    .label { font-size:.8rem; font-weight:600; color:#555; text-transform:uppercase; letter-spacing:.03em; }
    .value { margin-top:.2rem; font-size:.95rem; }

    .badge {
        display:inline-block; padding:.15rem .6rem; border-radius:999px;
        background:#e5f2ff; color:#1f6feb; font-size:.75rem;
    }

    .badge-status-curso { background:#fff7e6; color:#b36b00; }
    .badge-status-concluido { background:#e6ffed; color:#047857; }

    .table {
        width:100%; border-collapse:collapse; font-size:.9rem;
    }
    .table th, .table td {
        padding:.6rem .7rem; border-bottom:1px solid #eee; text-align:left;
    }
    .table th { background:#f0f4ff; }

    .subtitle { font-size:.9rem; color:#666; margin-bottom:.6rem; }

    .back-link {
        text-decoration:none; color:#1f6feb; font-size:.9rem;    
    }
    .separator {
        margin: 0 12px;
        color: #aaa;
        font-size: 0.9rem;
    }
</style>
<main style="max-width:1200px;margin:0 auto;padding:20px;">
    <h1>Estatísticas</h1>

    <div class="stats-grid">

        <!-- Faixa etária -->
        <div class="card">
            <h2>Acidentes por Faixa Etária</h2>
            <canvas id="chartAge"></canvas>
        </div>

        <!-- Género -->
        <div class="card">
            <h2>Acidentes por Género</h2>
            <canvas id="chartGender"></canvas>
        </div>

        <!-- Local -->
        <div class="card">
            <h2>Acidentes por Local</h2>
            <canvas id="chartLocation"></canvas>
        </div>

        <!-- Tipo de acidente -->
        <div class="card">
            <h2>Tipo de Acidente</h2>
            <canvas id="chartType"></canvas>
        </div>

        <!-- Tipo de tratamento -->
        <div class="card">
            <h2>Tipo de Tratamento</h2>
            <canvas id="chartTreatment"></canvas>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const buildChart = (id, labels, data) => {
    new Chart(document.getElementById(id), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: "Total",
                data: data,
                borderWidth: 1
            }]
        }
    });
};

// PHP → JS
buildChart("chartAge", <?= json_encode(array_column($ageStats, 'faixa')) ?>, <?= json_encode(array_column($ageStats, 'total')) ?>);
buildChart("chartGender", <?= json_encode(array_column($genderStats, 'genero')) ?>, <?= json_encode(array_column($genderStats, 'total')) ?>);
buildChart("chartLocation", <?= json_encode(array_column($locationStats, 'local')) ?>, <?= json_encode(array_column($locationStats, 'total')) ?>);
buildChart("chartType", <?= json_encode(array_column($typeStats, 'tipo')) ?>, <?= json_encode(array_column($typeStats, 'total')) ?>);
buildChart("chartTreatment", <?= json_encode(array_column($treatmentStats, 'tipo')) ?>, <?= json_encode(array_column($treatmentStats, 'total')) ?>);
</script>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(380px,1fr));
    gap: 20px;
}
.card {
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.07);
}
</style>
