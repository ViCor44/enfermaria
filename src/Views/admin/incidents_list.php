<?php
$baseUrl = '/enfermaria/public/index.php';
$nome    = $_SESSION['user_name'] ?? 'Administrador';

$fromDate   = $_GET['from'] ?? '';
$toDate     = $_GET['to'] ?? '';
$locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Enfermaria · Acidentes (Admin)</title>
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
    text-align: center; /* Centraliza para consistência com login e dashboard */
}
h1 { 
    margin-top: 0; 
    font-size: 2rem;
    color: #1f6feb;
}
.subtitle { 
    font-size: 1rem; 
    color: #777; 
    margin-bottom: 2rem; 
}

.filters {
    background: #fff; 
    padding: 1.5rem; 
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    margin-bottom: 1.5rem;
    text-align: left; /* Alinha filtros à esquerda para melhor usabilidade */
}
.filters form { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 1rem; 
    align-items: flex-end; 
}
.filters label { 
    display: block; 
    font-size: .9rem; 
    font-weight: 600; 
    color: #555; 
    margin-bottom: 0.3rem;
}
.filters input, .filters select {
    padding: 0.6rem 0.8rem; 
    border-radius: 8px; 
    border: 1px solid #ddd; 
    min-width: 180px;
    background: #fff;
    transition: border-color 0.2s ease;
}
.filters input:focus, .filters select:focus {
    border-color: #1f6feb;
    outline: none;
}
.filters button {
    padding: 0.6rem 1.2rem; 
    border: none; 
    border-radius: 8px; 
    cursor: pointer;
    background: #1f6feb; 
    color: #fff; 
    font-size: .95rem;
    transition: background 0.2s ease;
}
.filters button:hover {
    background: #0f5bdb;
}
.filters a.btn-reset {
    padding: 0.6rem 1.2rem; 
    border-radius: 8px; 
    font-size: .95rem; 
    text-decoration: none;
    border: 1px solid #ddd; 
    color: #555; 
    background: #f8f9fb;
    transition: background 0.2s ease;
}
.filters a.btn-reset:hover {
    background: #e9ecef;
}

table {
    width: 100%; 
    border-collapse: collapse; 
    background: #fff;
    border-radius: 12px; 
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    margin: 0 auto;
}
th, td { 
    padding: 0.8rem 1rem; 
    border-bottom: 1px solid #eee; 
    font-size: .95rem; 
    text-align: left; 
}
th { 
    background: #f0f4ff; 
    font-weight: 600;
    color: #555;
}
tr:last-child td { 
    border-bottom: none; 
}
tr:hover {
    background: #f8faff;
}
a {
    color: #1f6feb;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}

.badge {
    display: inline-block; 
    padding: 0.3rem 0.7rem; 
    border-radius: 999px;
    font-size: .8rem; 
    background: #e5f2ff; 
    color: #1f6feb;
    font-weight: 500;
}

.separator {
    border: none;
    border-top: 1px solid #ddd;
    margin: 2rem 0;
}

/* Responsividade */
@media (max-width: 768px) {
    main {
        padding: 1rem;
    }
    .filters form {
        flex-direction: column;
        align-items: stretch;
    }
    .filters div {
        width: 100%;
    }
    table {
        font-size: 0.85rem;
    }
}
</style>
</head>
<body>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Acidentes</h1>
    <p class="subtitle">Pesquisa por intervalo de datas e local (apenas visão de administração, com dados agregados).</p>

    <hr class="separator"> <!-- Adicionado para consistência com login e dashboard -->

    <div class="filters">
        <form method="get" action="<?= $baseUrl ?>">
            <input type="hidden" name="route" value="admin_incidents">

            <div>
                <label>Episódio (ID)</label>
                <input type="text" name="episode" value="<?= htmlspecialchars($_GET['episode'] ?? '') ?>" placeholder="Pesquisar por episódio">
            </div>

            <div>
                <label>Data inicial</label>
                <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>">
            </div>

            <div>
                <label>Data final</label>
                <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>">
            </div>

            <div>
                <label>Local</label>
                <select name="location_id">
                    <option value="0">-- Todos --</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= (int)$loc['id'] ?>" <?= $locationId === (int)$loc['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit">Filtrar</button>
                <a href="<?= $baseUrl ?>?route=admin_incidents" class="btn-reset">Limpar</a>
            </div>
        </form>
    </div>

    <?php if (empty($incidents)): ?>
        <p>Não foram encontrados Acidentes com os critérios selecionados.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Episódio</th>
                    <th>Data / Hora</th>
                    <th>Local</th>
                    <th>Tipo de Acidente</th>
                    <th>Idade</th>
                    <th>Género</th>
                    <th>Enfermeiro</th>
                    <?php if ($role === 'Enfermeiro'): ?>
                    <th>Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($incidents as $i): ?>
                <tr>
                    <td><a href="<?= $baseUrl ?>?route=admin_incident_detail&id=<?= (int)$i['id'] ?>">
                            <?= (int)$i['id'] ?>
                        </a>
                    </td>
                    <td>
                        <a>
                            <?= htmlspecialchars($i['occurred_at']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($i['location_name']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($i['incident_type_name']) ?></span></td>
                    <td><?= $i['patient_age'] !== null ? (int)$i['patient_age'] : '—' ?></td>
                    <td><?= $i['patient_gender'] ?: '—' ?></td>
                    <td><?= htmlspecialchars($i['nurse_name'] ?? '') ?></td>
                    <?php if ($role === 'Enfermeiro'): ?>
                    <td><a href="<?= $baseUrl ?>?route=treatments_new&incident_id=<?= (int)$i['id'] ?>">Adicionar tratamento</a></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

</body>
</html>