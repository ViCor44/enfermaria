<?php
$baseUrl = '/enfermaria/public/index.php';

$fromDate   = $_GET['from'] ?? '';
$toDate     = $_GET['to'] ?? '';
$locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Enfermaria · Registos Internos</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
/* Copiado da listagem de ocorrências */

body { 
    margin: 0; 
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; 
    background: #f5f7fb; 
    color: #333; 
}

main { 
    max-width: 1200px; 
    margin: 0 auto; 
    padding: 2rem; 
    text-align: center; 
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
    text-align: left;
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
}

.filters button,
.filters a.btn-reset {
    padding: 0.6rem 1.2rem; 
    border-radius: 8px; 
    font-size: .95rem; 
    border: none;
    cursor: pointer;
}

.filters button {
    background: #1f6feb; 
    color: #fff; 
}

.filters a.btn-reset {
    border: 1px solid #ddd;
    color: #555;
    text-decoration: none;
    background: #f8f9fb;
}

table {
    width: 100%; 
    border-collapse: collapse; 
    background: #fff;
    border-radius: 12px; 
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
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

.separator {
    border: none;
    border-top: 1px solid #ddd;
    margin: 2rem 0;
}
</style>
</head>
<body>

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main>

<h1>Registos Internos</h1>
<p class="subtitle">Situações internas sem classificação como ocorrência.</p>

<hr class="separator">

<div class="filters">
    <form method="get" action="<?= $baseUrl ?>">
        <input type="hidden" name="route" value="admin_internal_records">

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
            <a href="<?= $baseUrl ?>?route=admin_internal_records" class="btn-reset">Limpar</a>
        </div>
    </form>
</div>

<?php if (empty($records)): ?>
    <p>Não foram encontrados Registos Internos com os critérios selecionados.</p>
<?php else: ?>

<table>
    <thead>
        <tr>
            <th>Episódio</th>
            <th>Data / Hora</th>
            <th>Local</th>
            <th>Idade</th>
            <th>Género</th>
            <th>Tratamento</th>
            <th>Enfermeiro</th>
            <th>Descrição</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($records as $r): ?>
        <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['occurred_at']) ?></td>
            <td><?= htmlspecialchars($r['location_name'] ?? '—') ?></td>
            <td><?= $r['patient_age'] !== null ? (int)$r['patient_age'] : '—' ?></td>
            <td><?= $r['patient_gender'] ?: '—' ?></td>
            <td><?= htmlspecialchars($r['treatment'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['nurse_name']) ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($r['description'], 0, 60, '…')) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

</main>

</body>
</html>
