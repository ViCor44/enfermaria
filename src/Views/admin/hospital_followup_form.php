<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Seguimento Hospitalar</title>

<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
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

form {
    background: #fff; 
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    text-align: left;
}

label { 
    display: block; 
    margin-top: 1rem; 
    font-weight: 600; 
    color: #555; 
}

label.required::after {
    content: " *";
    color: #e53e3e;
}

input,
textarea {
    width: 100%; 
    padding: 0.7rem 0.9rem;
    margin-top: 0.3rem; 
    border-radius: 8px; 
    border: 1px solid #ddd; 
    background: #fff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

input:focus,
textarea:focus {
    border-color: #1f6feb;
    box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.1);
    outline: none;
}

textarea { 
    min-height: 120px; 
    resize: vertical; 
}

.row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

button {
    margin-top: 1.5rem; 
    padding: 0.7rem 1.5rem;
    border: none; 
    border-radius: 8px;
    background: #1f6feb; 
    color: #fff; 
    font-size: 1rem; 
    cursor: pointer;
    transition: background 0.2s ease, transform 0.1s ease;
}

button:hover { 
    background: #0f5bdb; 
    transform: translateY(-2px);
}

.separator {
    border: none;
    border-top: 1px solid #ddd;
    margin: 2rem 0;
}

.previous {
    margin-bottom:1.5rem;
    background:#f8fafc;
    padding:1.2rem;
    border-radius:10px;
    border:1px solid #e5e7eb;
}

.previous ul {
    margin:.5rem 0 0 1.2rem;
}

.previous li {
    font-size:.9rem;
}

@media (max-width: 768px) {
    main {
        padding: 1rem;
    }
    form {
        padding: 1.5rem;
    }
}
</style>
</head>

<body>

<?php require __DIR__.'/../layouts/header.php'; ?>

<main>

<h1>Seguimento hospitalar</h1>

<hr class="separator">

<form method="post"
      action="<?= $baseUrl ?>?route=incident_hospital_followup_store"
      enctype="multipart/form-data">

<div class="row">
    <div style="margin-right: 24px;">
        <strong>Ocorrência #<?= (int)$incident['id'] ?></strong>
    </div>
</div>

<?php if (!empty($followups)): ?>
<div class="previous">
    <strong>Registos anteriores:</strong>
    <ul>
        <?php foreach ($followups as $f): ?>
            <li>
                <?= htmlspecialchars($f['visit_date']) ?> —
                <?= htmlspecialchars($f['hospital_name']) ?>
                (<?= htmlspecialchars($f['created_by_name']) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<input type="hidden" name="incident_id" value="<?= (int)$incident['id'] ?>">

<div class="row">
    <div style="margin-right: 24px;">
        <label class="required">Data da ida ao hospital</label>
        <input type="date" name="visit_date" required>
    </div>

    <div style="margin-right: 24px;">
        <label>Hospital</label>
        <input type="text" name="hospital_name">
    </div>
</div>
<div style="margin-right: 24px;">
<label>Observações</label>
<textarea name="notes"></textarea>
</div>
<div style="margin-right: 24px;">
<label>Comprovativo (PDF / imagem)</label>
<input type="file" name="document" accept=".pdf,image/*">
</div>
<button type="submit">Guardar seguimento</button>

</form>

</main>

</body>
</html>
