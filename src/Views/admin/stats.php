<?php
$baseUrl = '/enfermaria/public/index.php';

?>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">
<style>
    body { 
        margin: 0; 
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; 
        background: #f5f7fb; 
        color: #333; 
    }
    header {
        background: #1f6feb; 
        color: #fff; 
        padding: 1rem 2rem;
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .logo {
        font-weight: 700;
        letter-spacing: .03em;
        font-size: 1.2rem;
    }
    .user-info {
        font-size: .9rem;
        text-align: right;
    }
    .user-info a {
        color: #fff;
        text-decoration: underline;
        margin-left: .5rem;
    }
    main { 
        max-width: 1200px; 
        margin: 0 auto; 
        padding: 2rem; 
        text-align: center; /* Centraliza para consistência */
    }
    h1 { 
        margin-top: 0; 
        font-size: 2rem;
        color: #1f6feb;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    .card {
        background: #fff; 
        border-radius: 12px; 
        padding: 1.5rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }
    .card h2 { 
        margin-top: 0; 
        font-size: 1.2rem;
        color: #555;
        margin-bottom: 1rem;
    }
    .separator {
        border: none;
        border-top: 1px solid #ddd;
        margin: 2rem 0;
    }

    .btn {
        border: none;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        font-size: .9rem;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.1s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
    }
    .btn-outline { 
        background: #f0f4ff; 
        color: #1f6feb; 
        border: 1px solid #1f6feb;
    }
    .btn-outline:hover { 
        background: #e5f2ff; 
    }

    /* Responsividade */
    @media (max-width: 768px) {
        main {
            padding: 1rem;
        }
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .card {
            padding: 1rem;
        }
    }
</style>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Estatísticas</h1>

    <hr class="separator"> <!-- Adicionado para consistência com outras páginas -->

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