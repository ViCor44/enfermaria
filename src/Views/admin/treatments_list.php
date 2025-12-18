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
    text-align: left; /* Alinha filtros à esquerda */
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
    font-weight: 500;
}
.badge-status-curso { 
    background: #fff7e6; 
    color: #b36b00; 
}
.badge-status-concluido { 
    background: #e6ffed; 
    color: #047857; 
}
.flash-success { 
    background: #e6ffed; 
    color: #047857; 
    padding: 0.7rem; 
    border-radius: 6px; 
    margin-bottom: 1rem; 
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
    <h1>Tratamentos</h1>

    <hr class="separator"> <!-- Adicionado para consistência -->

    <div class="filters">
        <form method="get" action="<?= $baseUrl ?>">
            <input type="hidden" name="route" value="admin_treatments">
            <div>
                <label>Estado</label>
                <select name="status">
                    <option value="">-- Todos --</option>
                    <option value="em_curso" <?= $statusFilter === 'em_curso' ? 'selected' : '' ?>>Em curso</option>
                    <option value="concluido" <?= $statusFilter === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                </select>
            </div>

            <div>
                <label>Data inicial</label>
                <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
            </div>

            <div>
                <label>Data final</label>
                <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
            </div>

            <div>
                <button type="submit">Filtrar</button>
                <a href="<?= $baseUrl ?>?route=admin_treatments" class="btn-reset">Limpar</a>
            </div>
        </form>
    </div>

    <?php if (empty($treatments)): ?>
        <p>Nenhum tratamento encontrado.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Data registo</th>
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
                        <td><?= htmlspecialchars($tr['created_at'] ?? $tr['created_at']) ?></td>
                        <td>
                            <a href="<?= $baseUrl ?>?route=admin_incident_detail&id=<?= (int)$tr['incident_id'] ?>">
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
</body>
</html>