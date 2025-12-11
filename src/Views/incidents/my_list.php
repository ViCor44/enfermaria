<?php
    $baseUrl = '/enfermaria/public/index.php';
    $nome = $_SESSION['user_name'] ?? 'Enfermeiro';
    $fromDate = $_GET['from'] ?? '';
    $toDate = $_GET['to'] ?? '';
    $locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Meus Incidentes</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
body { margin:0; font-family:system-ui,sans-serif; background:#f3f6fb; }
header {
    background:#1f6feb; color:#fff; padding:1rem 2rem;
    display:flex; justify-content:space-between; align-items:center;
}
main { max-width:1000px; margin:0 auto; padding:2rem; }
h1 { margin-top:0; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden;
        box-shadow:0 8px 20px rgba(0,0,0,.06); }
th,td { padding:.7rem; border-bottom:1px solid #eee; font-size:.9rem; }
th { background:#f0f4ff; text-align:left; }
tr:last-child td { border-bottom:none; }
.badge { padding:.2rem .5rem; border-radius:999px; font-size:.75rem; background:#e5f2ff; color:#1f6feb; }
.flash-success { background:#e6ffed; color:#047857; padding:.7rem; border-radius:6px; margin-bottom:1rem; }
/* ---- Barra de filtros ---- */

.filter-bar {
    display: flex;
    align-items: flex-end;
    gap: 20px;
    margin: 20px 0 30px;
    background: #ffffff;
    padding: 18px 22px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.06);
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-group label {
    font-size: 14px;
    font-weight: 600;
    color: #444;
}

.filter-group input[type="date"],
.filter-group select {
    padding: 8px 12px;
    border: 1px solid #d0d6df;
    border-radius: 6px;
    font-size: 14px;
    background: #f9fafb;
    transition: 0.2s;
}

.filter-group input[type="date"]:focus,
.filter-group select:focus {
    outline: none;
    border-color: #4A6CF7;
    box-shadow: 0 0 0 2px rgba(74,108,247,0.2);
}

/* Botões */

.filter-buttons {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.btn-filter {
    background: #4A6CF7;
    color: #fff;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s;
}

.btn-filter:hover {
    background: #3b56c7;
}

.btn-clear {
    font-size: 13px;
    color: #444;
    text-decoration: underline;
    cursor: pointer;
}

.btn-clear:hover {
    color: #000;
}
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Meus incidentes</h1>

    <form method="get" action="<?= $baseUrl ?>" class="filter-bar">
        <input type="hidden" name="route" value="incidents_my">

        <div class="filter-group">
            <label>Data inicial</label>
            <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>">
        </div>

        <div class="filter-group">
            <label>Data final</label>
            <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>">
        </div>

        <div class="filter-group">
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
            <button type="submit" class="btn-filter">Filtrar</button>
            <a href="<?= $baseUrl ?>?route=incidents_my" class="btn-clear">Limpar</a>
        </div>
    </form>

    <?php if (empty($incidents)): ?>
        <p>Não foram encontrados incidentes.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Tipo</th>
                    <th>Local</th>
                    <th>Idade</th>
                    <th>Género</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidents as $i): ?>
                <tr>
                    <td><a href="<?= $baseUrl ?>?route=admin_incident_detail&id=<?= (int)$i['id'] ?>"><?= htmlspecialchars($i['occurred_at']) ?></a></td>
                    <td><span class="badge"><?= htmlspecialchars($i['incident_type_name']) ?></span></td>
                    <td><?= htmlspecialchars($i['location_name']) ?></td>
                    <td><?= $i['patient_age'] !== null ? (int)$i['patient_age'] : '—' ?></td>
                    <td><?= $i['patient_gender'] ?: '—' ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($i['description'] ?? '', 0, 80, '...')) ?></td>
                    <td><a href="<?= $baseUrl ?>?route=treatments_new&incident_id=<?= (int)$i['id'] ?>">Adicionar tratamento</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
