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
<title>Enfermaria · Incidentes (Admin)</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
body { margin:0; font-family:system-ui,sans-serif; background:#f3f6fb; }
header {
    background:#1f6feb; color:#fff; padding:1rem 2rem;
    display:flex; justify-content:space-between; align-items:center;
}
main { max-width:1200px; margin:0 auto; padding:2rem; }
h1 { margin-top:0; }

.filters {
    background:#fff; padding:1rem 1.5rem; border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
    margin-bottom:1.5rem;
}
.filters form { display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; }
.filters label { display:block; font-size:.85rem; font-weight:600; color:#555; }
.filters input, .filters select {
    padding:.4rem .5rem; border-radius:6px; border:1px solid #ccc; min-width:160px;
}
.filters button {
    padding:.5rem 1rem; border:none; border-radius:8px; cursor:pointer;
    background:#1f6feb; color:#fff; font-size:.9rem;
}
.filters a.btn-reset {
    padding:.5rem 1rem; border-radius:8px; font-size:.85rem; text-decoration:none;
    border:1px solid #ccc; color:#555; background:#f8f9fb;
}

table {
    width:100%; border-collapse:collapse; background:#fff;
    border-radius:12px; overflow:hidden;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
th,td { padding:.7rem .8rem; border-bottom:1px solid #eee; font-size:.9rem; text-align:left; }
th { background:#f0f4ff; }
tr:last-child td { border-bottom:none; }

.badge {
    display:inline-block; padding:.15rem .5rem; border-radius:999px;
    font-size:.75rem; background:#e5f2ff; color:#1f6feb;
}
.subtitle { font-size:.9rem; color:#666; margin-bottom:.5rem; }
</style>
</head>
<body>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Incidentes</h1>
    <p class="subtitle">Pesquisa por intervalo de datas e local (apenas visão de administração, com dados agregados).</p>

    <div class="filters">
        <form method="get" action="<?= $baseUrl ?>">
            <input type="hidden" name="route" value="admin_incidents">

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
        <p>Não foram encontrados incidentes com os critérios selecionados.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Data / Hora</th>
                    <th>Local</th>
                    <th>Tipo de incidente</th>
                    <th>Idade</th>
                    <th>Género</th>
                    <th>Enfermeiro</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($incidents as $i): ?>
                <tr>
                    <td>
                        <a href="<?= $baseUrl ?>?route=admin_incident_detail&id=<?= (int)$i['id'] ?>">
                            <?= htmlspecialchars($i['occurred_at']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($i['location_name']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($i['incident_type_name']) ?></span></td>
                    <td><?= $i['patient_age'] !== null ? (int)$i['patient_age'] : '—' ?></td>
                    <td><?= $i['patient_gender'] ?: '—' ?></td>
                    <td><?= htmlspecialchars($i['nurse_name'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

</body>
</html>
