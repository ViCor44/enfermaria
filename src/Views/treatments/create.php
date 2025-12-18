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
    form {
        background: #fff; 
        padding: 2rem; /* Aumentado para mais espaço */
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        text-align: left; /* Alinha form à esquerda */
    }
    label { 
        display: block; 
        margin-top: 1rem; 
        font-weight: 600; 
        color: #555; 
    }
    label.required::after {
        content: " *";
        color: #e53e3e; /* Vermelho para required */
    }
    select, textarea {
        width: 100%; 
        padding: 0.7rem 0.9rem; /* Aumentado para inputs maiores */
        margin-top: 0.3rem; 
        border-radius: 8px; 
        border: 1px solid #ddd; 
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    select:focus, textarea:focus {
        border-color: #1f6feb;
        box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.1);
        outline: none;
    }
    textarea { 
        min-height: 120px; 
        resize: vertical; 
    }
    button {
        margin-top: 1.5rem; 
        padding: 0.7rem 1.5rem; /* Aumentado para botão maior */
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
    .flash-error { 
        background: #ffe0e0; 
        color: #900; 
        padding: 0.7rem; 
        border-radius: 6px; 
        margin-bottom: 1rem; 
    }
    .incident-box { 
        background: #f0f4ff; 
        padding: 1.5rem; 
        border-radius: 12px; 
        margin-bottom: 1.5rem; 
        font-size: .95rem; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        text-align: left;
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
        form {
            padding: 1.5rem;
        }
    }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Registar tratamento</h1>

    <hr class="separator"> <!-- Adicionado para consistência -->

    <div class="incident-box">
        <strong>Acidente:</strong>
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

        <label class="required">Tipo de tratamento</label>
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
        <div style="margin-right: 24px;">
            <label>Notas / Observações (opcional)</label>
            <textarea name="notes" placeholder="Descrição do tratamento realizado. Evite dados pessoais desnecessários."></textarea>
        </div>
        <button type="submit">Guardar tratamento</button>
    </form>
</main>
</body>
</html>