<?php
$baseUrl = '/enfermaria/public/index.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Editar utente - Episódio #<?= (int)$incident['id'] ?></title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">
<style>
    body {
        margin: 0;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f5f7fb;
        color: #333;
    }
    main {
        max-width: 980px;
        margin: 0 auto;
        padding: 2rem;
    }
    .card {
        background: #fff;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 8px 20px rgba(0,0,0,.06);
    }
    h1 {
        margin: 0 0 1rem;
        color: #1f6feb;
    }
    .row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 1rem;
    }
    label {
        display: block;
        font-size: .85rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: .35rem;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    input, select {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: .55rem .7rem;
        font-size: .95rem;
        background: #fff;
    }
    .full {
        grid-column: 1 / -1;
    }
    .form-check {
        margin: .6rem 0 1rem;
    }
    .form-check input {
        width: auto;
        margin-right: .4rem;
    }
    .actions {
        margin-top: 1rem;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn {
        display: inline-block;
        border: none;
        border-radius: 8px;
        padding: .6rem 1rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-primary {
        background: #1f6feb;
        color: #fff;
    }
    .btn-secondary {
        background: #e5e7eb;
        color: #1f2937;
    }
    .subtitle {
        margin-top: 0;
        color: #6b7280;
        font-size: .95rem;
    }
    @media (max-width: 900px) {
        .row {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <div style="margin-bottom: 1rem;">
        <a href="<?= $baseUrl ?>?route=admin_incident_detail&id=<?= (int)$incident['id'] ?>">&larr; Voltar ao episódio</a>
    </div>

    <div class="card">
        <h1>Editar dados do utente</h1>
        <p class="subtitle">Episódio #<?= (int)$incident['id'] ?> - Atualize os dados que não estavam completos no registo inicial.</p>

        <form method="post" action="<?= $baseUrl ?>?route=incident_patient_update">
            <input type="hidden" name="incident_id" value="<?= (int)$incident['id'] ?>">
            <input type="hidden" name="patient_id" value="<?= (int)$patient['id'] ?>">

            <div class="row">
                <div class="full">
                    <label for="full_name">Nome do utente</label>
                    <input id="full_name" name="full_name" type="text" required value="<?= htmlspecialchars((string)($patient['full_name'] ?? '')) ?>">
                </div>

                <div>
                    <label for="age">Idade</label>
                    <input id="age" name="age" type="number" min="0" max="120" value="<?= $patient['age'] !== null ? (int)$patient['age'] : '' ?>">
                </div>

                <div>
                    <label for="gender">Genero</label>
                    <select id="gender" name="gender">
                        <option value="" <?= empty($patient['gender']) ? 'selected' : '' ?>>-- Nao especificar --</option>
                        <option value="M" <?= ($patient['gender'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($patient['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
                        <option value="Outro" <?= ($patient['gender'] ?? '') === 'Outro' ? 'selected' : '' ?>>Outro</option>
                    </select>
                </div>

                <div>
                    <label for="nationality">Nacionalidade</label>
                    <input id="nationality" name="nationality" type="text" value="<?= htmlspecialchars((string)($patient['nationality'] ?? '')) ?>">
                </div>
            </div>

            <div class="form-check">
                <input id="is_employee" type="checkbox" name="is_employee" value="1" <?= !empty($patient['is_employee']) ? 'checked' : '' ?>>
                <label for="is_employee" style="display:inline; text-transform:none; letter-spacing:0;">O utente e colaborador</label>
            </div>

            <div class="row">
                <div class="full">
                    <label for="address">Morada</label>
                    <input id="address" name="address" type="text" value="<?= htmlspecialchars((string)($patient['address'] ?? '')) ?>">
                </div>

                <div>
                    <label for="postal_code">Codigo Postal</label>
                    <input id="postal_code" name="postal_code" type="text" value="<?= htmlspecialchars((string)($patient['postal_code'] ?? '')) ?>">
                </div>

                <div>
                    <label for="city">Cidade</label>
                    <input id="city" name="city" type="text" value="<?= htmlspecialchars((string)($patient['city'] ?? '')) ?>">
                </div>

                <div>
                    <label for="phone">Telefone</label>
                    <input id="phone" name="phone" type="text" value="<?= htmlspecialchars((string)($patient['phone'] ?? '')) ?>">
                </div>

                <div>
                    <label for="dob">Data de Nascimento</label>
                    <input id="dob" name="dob" type="date" value="<?= htmlspecialchars((string)($patient['dob'] ?? '')) ?>">
                </div>

                <div>
                    <label for="id_type">Tipo de Identificacao</label>
                    <select id="id_type" name="id_type">
                        <option value="" <?= empty($patient['id_type']) ? 'selected' : '' ?>>-- Selecionar --</option>
                        <option value="CC" <?= ($patient['id_type'] ?? '') === 'CC' ? 'selected' : '' ?>>Cartao de Cidadao (CC)</option>
                        <option value="Passaporte" <?= ($patient['id_type'] ?? '') === 'Passaporte' ? 'selected' : '' ?>>Passaporte</option>
                    </select>
                </div>

                <div>
                    <label for="id_number">Numero de Identificacao</label>
                    <input id="id_number" name="id_number" type="text" value="<?= htmlspecialchars((string)($patient['id_number'] ?? '')) ?>">
                </div>
            </div>

            <div class="form-check">
                <input id="refused_hospital" type="checkbox" name="refused_hospital" value="1" <?= !empty($patient['refused_hospital']) ? 'checked' : '' ?>>
                <label for="refused_hospital" style="display:inline; text-transform:none; letter-spacing:0;">O utente recusou deslocacao ao hospital</label>
            </div>

            <div class="actions">
                <button class="btn btn-primary" type="submit">Guardar alteracoes</button>
                <a class="btn btn-secondary" href="<?= $baseUrl ?>?route=admin_incident_detail&id=<?= (int)$incident['id'] ?>">Cancelar</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
