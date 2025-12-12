<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Administrador';
$statusFilter = $_GET['status'] ?? '';
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
.badge { padding:.2rem .5rem; border-radius:999px; font-size:.75rem; background:#e5f2ff; color:#1f6feb; }
.badge-status-curso { background:#fff7e6; color:#b36b00; }
.badge-status-concluido { background:#e6ffed; color:#047857; }
.flash-success { background:#e6ffed; color:#047857; padding:.7rem; border-radius:6px; margin-bottom:1rem; }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main style="max-width:1200px;margin:2rem auto;padding:0 1rem;">
    <h1>Tratamentos</h1>

    <div class="filters" style="margin:1rem 0;padding:1rem;background:#fff;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.04);">
        <form method="get" action="<?= $baseUrl ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="route" value="admin_treatments">
            <label>
                Estado
                <select name="status">
                    <option value="">-- Todos --</option>
                    <option value="em_curso" <?= $statusFilter === 'em_curso' ? 'selected' : '' ?>>Em curso</option>
                    <option value="concluido" <?= $statusFilter === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                </select>
            </label>

            <label>
                Data inicial
                <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
            </label>

            <label>
                Data final
                <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
            </label>

            <button type="submit">Filtrar</button>
        </form>
    </div>

    <?php if (empty($treatments)): ?>
        <p>Nenhum tratamento encontrado.</p>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,.06);">
            <thead style="background:#f0f4ff;">
                <tr>
                    <th style="padding:.8rem;">Data registo</th>
                    <th>Acidente</th>
                    <th>Local</th>
                    <th>Tipo</th>
                    <th>Enfermeiro</th>
                    <th>Estado</th>
                    <th>Notas</th>
                    <th>Observ.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($treatments as $tr): ?>
                    <tr>
                        <td style="padding:.8rem;"><?= htmlspecialchars($tr['created_at'] ?? $tr['created_at']) ?></td>
                        <td>
                            <?= htmlspecialchars($tr['incident_type_name']) ?><br>
                            <small><?= htmlspecialchars($tr['incident_occurred_at']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($tr['location_name']) ?></td>
                        <td><span style="display:inline-block;padding:.2rem .5rem;border-radius:999px;background:#e9f2ff;color:#1d4ed8;font-size:.8rem;">
                                <?= htmlspecialchars($tr['treatment_type_name']) ?>
                            </span></td>
                        <td><?= htmlspecialchars($tr['nurse_name']) ?></td>
                        <td>
                            <?php if ($tr['status'] === 'em_curso'): ?>
                                <span class="badge badge-status-curso">Em curso</span>
                            <?php else: ?>
                                <span class="badge badge-status-concluido">Concluído</span>
                            <?php endif; ?>                           
                        </td>
                        <td><?= htmlspecialchars(mb_strimwidth($tr['notes'] ?? '', 0, 120, '…')) ?></td>
                        <td>
                            <?php if ($role === 'Enfermeiro' && $tr['status'] === 'em_curso'): ?>
                                <form method="post" action="<?= $baseUrl ?>?route=treatment_conclude" style="display:inline;">
                                    <input type="hidden" name="treatment_id" value="<?= (int)$tr['id'] ?>">
                                    <!-- CSRF token se tiveres -->
                                    <button type="submit" class="btn btn-primary btn-sm"
                                        onclick="return confirm('Concluir este tratamento? Esta ação será registada.');">
                                        Concluir
                                    </button>
                                </form>
                            <?php else: ?>
                                <div>
                                    
                                    <?php if (!empty($tr['concluded_by_name'])): ?>
                                        <div style="font-size:12px;color:#6b7280;">
                                            Concluído por <?= htmlspecialchars($tr['concluded_by_name']) ?>
                                            <br>
                                            <small><?= htmlspecialchars($tr['concluded_at'] ?? '') ?></small>
                                        </div>
                                    <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
