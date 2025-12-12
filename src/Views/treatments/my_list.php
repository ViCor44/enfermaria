<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Meus tratamentos</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
body { margin:0; font-family:system-ui,sans-serif; background:#f3f6fb; }
header {
    background:#1f6feb; color:#fff; padding:1rem 2rem;
    display:flex; justify-content:space-between; align-items:center;
}
main { max-width:1000px; margin:0 auto; padding:2rem; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden;
        box-shadow:0 8px 20px rgba(0,0,0,.06); }
th,td { padding:.7rem; border-bottom:1px solid #eee; font-size:.9rem; text-align:left; }
th { background:#f0f4ff; }
tr:last-child td { border-bottom:none; }
.badge { padding:.2rem .5rem; border-radius:999px; font-size:.75rem; background:#e5f2ff; color:#1f6feb; }
.badge-status-curso { background:#fff7e6; color:#b36b00; }
.badge-status-concluido { background:#e6ffed; color:#047857; }
.flash-success { background:#e6ffed; color:#047857; padding:.7rem; border-radius:6px; margin-bottom:1rem; }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Meus tratamentos</h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash-success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($treatments)): ?>
        <p>Não existem tratamentos registados por si.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Data registo</th>
                    <th>Acidente</th>
                    <th>Local</th>
                    <th>Tipo de tratamento</th>
                    <th>Estado</th>
                    <th>Notas</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($treatments as $tr): ?>
                <!-- dentro do foreach ($treatments as $tr): -->
            <tr>
                <td><?= htmlspecialchars($tr['created_at']) ?></td>
                <td>
                    <?= htmlspecialchars($tr['incident_type_name']) ?><br>
                    <small><?= htmlspecialchars($tr['occurred_at']) ?></small>
                </td>
                <td><?= htmlspecialchars($tr['location_name']) ?></td>
                <td><span class="badge"><?= htmlspecialchars($tr['treatment_type_name']) ?></span></td>
                <td>
                    <?php if ($tr['status'] === 'em_curso'): ?>
                        <span class="badge badge-status-curso">Em curso</span>
                    <?php else: ?>
                        <span class="badge badge-status-concluido">Concluído</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(mb_strimwidth($tr['notes'] ?? '', 0, 80, '...')) ?></td>

                <!-- AÇÕES: botão Concluir (aparece só se 'em_curso' e se for o enfermeiro dono) -->
                <td>
                    <?php if ($tr['status'] === 'em_curso' && (int)($tr['user_id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0)): ?>
                        <form method="post" action="<?= $baseUrl ?>?route=treatments_change_status" style="display:inline;">
                            <input type="hidden" name="treatment_id" value="<?= (int)$tr['id'] ?>">
                            <input type="hidden" name="status" value="concluido">
                            <button type="submit" class="btn" style="background:#10b981;color:#fff;border-radius:8px;padding:.4rem .6rem;border:0;cursor:pointer;">
                                Marcar como concluído
                            </button>
                        </form>
                    <?php else: ?>
                        &nbsp;
                    <?php endif; ?>
                </td>
            </tr>

            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
