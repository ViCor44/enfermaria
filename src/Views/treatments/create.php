<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Novo Tratamento</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
body { margin:0; font-family:system-ui,sans-serif; background:#f3f6fb; }
header {
    background:#1f6feb; color:#fff; padding:1rem 2rem;
    display:flex; justify-content:space-between; align-items:center;
}
main { max-width:900px; margin:0 auto; padding:2rem; }
h1 { margin-top:0; }
form {
    background:#fff; padding:1.5rem; border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
label { display:block; margin-top:1rem; font-weight:600; color:#555; }
select, textarea {
    width:100%; padding:.6rem; margin-top:.3rem; border-radius:6px; border:1px solid #ccc;
}
textarea { min-height:90px; resize:vertical; }
button {
    margin-top:1.5rem; padding:.8rem 1.4rem; border:none; border-radius:8px;
    background:#1f6feb; color:#fff; font-size:1rem; cursor:pointer;
}
button:hover { background:#1557c0; }
.flash-error { background:#ffe0e0; color:#900; padding:.7rem; border-radius:6px; margin-bottom:1rem; }
.incident-box { background:#f0f4ff; padding:1rem; border-radius:10px; margin-bottom:1rem; font-size:.9rem; }
.badge { padding:.2rem .4rem; border-radius:999px; background:#e5f2ff; color:#1f6feb; font-size:.75rem; }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Registar tratamento</h1>

    <div class="incident-box">
        <strong>Incidente:</strong>
        <?= htmlspecialchars($incident['incident_type_name']) ?>
        em <span class="badge"><?= htmlspecialchars($incident['location_name']) ?></span><br>
        <strong>Data/hora:</strong> <?= htmlspecialchars($incident['occurred_at']) ?><br>
        <?php if ($incident['patient_age'] !== null): ?>
            <strong>Idade:</strong> <?= (int)$incident['patient_age'] ?> ·
        <?php endif; ?>
        <?php if (!empty($incident['patient_gender'])): ?>
            <strong>Género:</strong> <?= htmlspecialchars($incident['patient_gender']) ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash-error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $baseUrl ?>?route=treatments_store">
        <input type="hidden" name="incident_id" value="<?= (int)$incident['id'] ?>">

        <label>Tipo de tratamento *</label>
        <select name="treatment_type_id" required>
            <option value="">-- Selecionar --</option>
            <?php foreach ($types as $t): ?>
                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Estado</label>
        <select name="status">
            <option value="em_curso">Em curso</option>
            <option value="concluido">Concluído</option>
        </select>

        <label>Notas / Observações (opcional)</label>
        <textarea name="notes" placeholder="Descrição do tratamento realizado. Evite dados pessoais desnecessários."></textarea>

        <button type="submit">Guardar tratamento</button>
    </form>
</main>
</body>
</html>
