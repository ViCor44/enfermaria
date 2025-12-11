<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
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
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Meus incidentes</h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash-success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($incidents)): ?>
        <p>Não há incidentes registados por si.</p>
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
                    <td><?= htmlspecialchars($i['occurred_at']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($i['incident_type_name']) ?></span></td>
                    <td><?= htmlspecialchars($i['location_name']) ?></td>
                    <td><?= $i['patient_age'] !== null ? (int)$i['patient_age'] : '—' ?></td>
                    <td><?= $i['patient_gender'] ?: '—' ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($i['description'] ?? '', 0, 80, '...')) ?></td>
                    <td>
                        <a href="<?= $baseUrl ?>?route=treatments_new&incident_id=<?= (int)$i['id'] ?>">
                            Adicionar tratamento
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
