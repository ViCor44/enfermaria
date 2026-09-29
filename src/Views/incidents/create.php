<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
$old = $_SESSION['old_incident_form'] ?? [];
unset($_SESSION['old_incident_form']);

$oldValue = static function (string $key, string $default = '') use ($old): string {
    $value = $old[$key] ?? $default;
    return is_array($value) ? $default : (string)$value;
};

$oldChecked = static fn (string $key): bool => isset($old[$key]);

$oldTreatmentIds = $old['treatment_type_id'] ?? [''];
if (!is_array($oldTreatmentIds)) {
    $oldTreatmentIds = [$oldTreatmentIds];
}
$oldTreatmentIds = $oldTreatmentIds === [] ? [''] : array_values($oldTreatmentIds);
$oldTreatmentNotes = $old['treatment_notes'] ?? [];
if (!is_array($oldTreatmentNotes)) {
    $oldTreatmentNotes = [$oldTreatmentNotes];
}
$oldTreatmentNotes = array_values($oldTreatmentNotes);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registar Ocorrência</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
    :root {
        --page-bg: #f4f7fb;
        --panel: #ffffff;
        --ink: #10213f;
        --muted: #697890;
        --line: #dce5f0;
        --blue: #2f7ee6;
        --blue-soft: #eef6ff;
        --green: #18885f;
        --green-dark: #116b4a;
        --danger: #c93838;
        --hospital: #b45309;
        --hospital-soft: #fff8eb;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        background: linear-gradient(135deg, rgba(47, 126, 230, 0.045), transparent 42%), var(--page-bg);
        color: var(--ink);
        font-family: "Segoe UI", Tahoma, sans-serif;
    }

    .incident-page {
        width: min(1480px, calc(100% - 40px));
        margin: 0 auto;
        padding: 30px 0 44px;
    }

    .page-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 20px;
    }

    .page-heading h1 {
        margin: 0;
        color: var(--ink);
        font-size: clamp(1.65rem, 2.4vw, 2.25rem);
        line-height: 1.12;
        letter-spacing: 0;
    }

    .page-heading p {
        margin: 7px 0 0;
        color: var(--muted);
        font-size: 0.95rem;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 16px;
        border: 1px solid var(--line);
        border-radius: 7px;
        background: #eaf0f7;
        color: #29415f;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .back-link:hover { background: #dfe8f3; }

    .flash-error {
        margin-bottom: 16px;
        padding: 12px 15px;
        border: 1px solid #f0b9b9;
        border-radius: 7px;
        background: #fff1f1;
        color: #8e2525;
    }

    .incident-form {
        display: grid;
        grid-template-columns: minmax(280px, .9fr) minmax(620px, 2.1fr);
        gap: 14px;
        align-items: start;
    }

    .form-column {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 14px;
    }

    .patient-panel { grid-area: patient; }
    .incident-panel { grid-area: incident; }
    .body-panel { grid-area: body; }
    .treatment-panel { grid-area: treatment; }
    .hospital-column {
        grid-area: hospital;
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 14px;
    }

    .form-panel {
        min-width: 0;
        padding: 20px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 7px 22px rgba(26, 54, 93, 0.055);
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 18px;
        color: var(--ink);
        font-size: 1rem;
        line-height: 1.25;
    }

    .panel-icon {
        display: inline-grid;
        flex: 0 0 25px;
        width: 25px;
        height: 25px;
        place-items: center;
        border-radius: 6px;
        background: var(--blue-soft);
        color: var(--blue);
        font-size: 0.78rem;
        font-weight: 800;
    }

    .field { margin-bottom: 16px; }
    .field:last-child { margin-bottom: 0; }

    .field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    label {
        display: block;
        margin-bottom: 6px;
        color: #2d405d;
        font-size: 0.84rem;
        font-weight: 650;
    }

    label.required::after { content: " *"; color: var(--danger); }

    input, select, textarea {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #cfdae8;
        border-radius: 7px;
        outline: 0;
        background: #fbfdff;
        color: #1f3653;
        font: inherit;
        font-size: 0.9rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }

    input:focus, select:focus, textarea:focus {
        border-color: var(--blue);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(47, 126, 230, 0.12);
    }

    textarea { min-height: 132px; resize: vertical; }

    .field-help, .privacy-note {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 0.76rem;
        line-height: 1.4;
    }

    .employee-toggle, .hospital-toggle, .refusal-toggle {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid var(--line);
        border-radius: 7px;
        background: #f7fafe;
    }

    .employee-toggle { margin-top: 4px; }

    .employee-toggle input, .hospital-toggle input, .refusal-toggle input {
        flex: 0 0 18px;
        width: 18px;
        min-height: 18px;
        height: 18px;
        margin: 1px 0 0;
        accent-color: var(--blue);
    }

    .employee-toggle label, .hospital-toggle label, .refusal-toggle label {
        margin: 0;
        cursor: pointer;
    }

    .hospital-panel {
        border-color: #efd6ad;
        background: var(--hospital-soft);
    }

    .hospital-panel .panel-icon {
        background: #ffedd5;
        color: var(--hospital);
    }

    .hospital-toggle {
        border-color: #e9c994;
        background: #fff;
    }

    .hospital-toggle input { accent-color: var(--hospital); }
    .hospital-toggle strong { display: block; color: #713f12; font-size: .9rem; }
    .hospital-toggle span { display: block; margin-top: 3px; color: #8a6740; font-size: .76rem; line-height: 1.35; }

    .hospital-details {
        display: none;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid #ead7b7;
    }

    .hospital-details.visible { display: block; }
    .refusal-toggle { margin-top: 4px; border-color: #ead7b7; background: #fffaf2; }

    @media (min-width: 1121px) {
        .incident-form.hospital-expanded .hospital-column {
            grid-column: 1 / -1;
        }

        .incident-form.hospital-expanded .hospital-panel {
            display: grid;
            grid-template-columns: minmax(280px, .9fr) minmax(620px, 2.1fr);
            grid-template-rows: auto 1fr;
            column-gap: 28px;
        }

        .incident-form.hospital-expanded .hospital-panel > .panel-title {
            grid-column: 1;
            grid-row: 1;
        }

        .incident-form.hospital-expanded .hospital-toggle {
            grid-column: 1;
            grid-row: 2;
            align-self: start;
        }

        .incident-form.hospital-expanded .hospital-details.visible {
            display: grid;
            grid-column: 2;
            grid-row: 1 / span 2;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 12px;
            margin-top: 0;
            padding: 0 0 0 28px;
            border-top: 0;
            border-left: 1px solid #ead7b7;
        }

        .incident-form.hospital-expanded .hospital-details > .field,
        .incident-form.hospital-expanded .hospital-details > .field-grid,
        .incident-form.hospital-expanded .hospital-details > .refusal-toggle {
            margin: 0;
        }

        .incident-form.hospital-expanded .hospital-details > .field:nth-child(2),
        .incident-form.hospital-expanded .hospital-details > .field-grid,
        .incident-form.hospital-expanded .hospital-details > .refusal-toggle {
            grid-column: 1 / -1;
        }
    }

    .treatment-list { display: grid; gap: 10px; }

    .treatment-entry {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 36px;
        gap: 8px;
        align-items: end;
    }

    .treatment-entry label { margin-bottom: 6px; }

    .treatment-fields { display: grid; gap: 10px; }
    .treatment-notes { min-height: 72px; resize: vertical; }

    .remove-treatment {
        display: grid;
        width: 36px;
        height: 42px;
        min-height: 42px;
        place-items: center;
        padding: 0;
        border: 1px solid #f0c6c6;
        border-radius: 7px;
        background: #fff4f4;
        color: #a52f2f;
        font-size: 1.15rem;
        cursor: pointer;
    }

    .remove-treatment:hover { background: #ffe8e8; }

    .add-treatment {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        margin-top: 12px;
        padding: 0 12px;
        border: 1px dashed #9bbce1;
        border-radius: 7px;
        background: #f4f9ff;
        color: #245b93;
        font: inherit;
        font-size: .84rem;
        font-weight: 700;
        cursor: pointer;
    }

    .add-treatment:hover { background: #eaf4ff; }
    .add-treatment:disabled { cursor: not-allowed; opacity: .55; }

    .body-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 8px;
    }

    .body-heading .panel-title { margin-bottom: 0; }

    .body-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mark-count { color: var(--muted); font-size: .8rem; white-space: nowrap; }

    .clear-marks {
        min-height: 34px;
        padding: 0 11px;
        border: 1px solid #cfdae8;
        border-radius: 6px;
        background: #fff;
        color: #405571;
        font: inherit;
        font-size: .8rem;
        font-weight: 650;
        cursor: pointer;
    }

    .clear-marks:hover { background: #f2f6fa; }
    .clear-marks:disabled { cursor: not-allowed; opacity: .5; }

    .body-instruction { margin: 0 0 14px 35px; color: var(--muted); font-size: .8rem; }

    .body-workspace {
        display: grid;
        grid-template-columns: minmax(300px, 1fr) 190px;
        gap: 18px;
        align-items: start;
    }

    .body-maps {
        display: grid;
        grid-template-columns: repeat(2, minmax(145px, 1fr));
        gap: 8px;
        max-width: 520px;
        margin: 0 auto;
    }

    .body-canvas {
        position: relative;
        display: block;
        width: min(100%, 175px);
        aspect-ratio: 1 / 2;
        height: auto;
        margin: 0 auto;
        padding: 0;
        overflow: hidden;
        border: 1px solid transparent;
        border-radius: 6px;
        background: transparent;
        cursor: crosshair;
    }

    .body-canvas:hover { border-color: #dce9f5; background: #f7fbff; }
    .body-canvas:focus-visible { outline: 3px solid rgba(47, 126, 230, .25); outline-offset: 2px; }
    .body-canvas img { display: block; width: 100%; height: 100%; object-fit: contain; pointer-events: none; }

    .body-marker {
        position: absolute;
        width: 18px;
        height: 18px;
        padding: 0;
        border: 3px solid #fff;
        border-radius: 50%;
        background: var(--mark-color);
        box-shadow: 0 0 0 7px var(--mark-glow), 0 2px 7px rgba(31, 45, 61, .25);
        transform: translate(-50%, -50%);
        cursor: pointer;
    }

    .mark-contusion { --mark-color: #e5484d; --mark-glow: rgba(229, 72, 77, .17); }
    .mark-wound { --mark-color: #d4145a; --mark-glow: rgba(212, 20, 90, .17); }
    .mark-burn { --mark-color: #ff8a00; --mark-glow: rgba(255, 138, 0, .18); }
    .mark-pain { --mark-color: #6c5ce7; --mark-glow: rgba(108, 92, 231, .17); }
    .mark-insect_bite { --mark-color: #65a30d; --mark-glow: rgba(101, 163, 13, .18); }
    .mark-epistaxis { --mark-color: #8b1e3f; --mark-glow: rgba(139, 30, 63, .18); }
    .mark-other { --mark-color: #13b8a6; --mark-glow: rgba(19, 184, 166, .17); }

    .body-map-label { display: block; margin-top: 8px; color: #2d405d; font-size: .84rem; font-weight: 700; text-align: center; }

    .mark-legend {
        display: grid;
        gap: 3px;
        padding: 10px;
        border: 1px solid var(--line);
        border-radius: 7px;
        background: #f8fbff;
    }

    .legend-item {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 10px;
        min-height: 36px;
        padding: 5px 7px;
        border: 1px solid transparent;
        border-radius: 6px;
        background: transparent;
        color: #39506d;
        font-family: inherit;
        font-size: .78rem;
        text-align: left;
        cursor: pointer;
    }

    .legend-item:hover { background: #f0f5fa; }
    .legend-item.active { border-color: #b9d5f4; background: #eef6ff; color: #174f8c; font-weight: 700; }
    .legend-swatch { width: 14px; height: 14px; border: 3px solid #fff; border-radius: 50%; background: var(--mark-color); box-shadow: 0 0 0 4px var(--mark-glow); }

    .privacy-note {
        padding: 14px 16px;
        border-left: 3px solid #e3a623;
        background: #fffaf0;
        color: #6d5726;
    }

    .submit-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 48px;
        padding: 0 18px;
        border: 0;
        border-radius: 7px;
        background: var(--green);
        color: #fff;
        font-size: 0.94rem;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(24, 136, 95, 0.16);
        transition: background 0.15s ease, transform 0.15s ease;
    }

    .submit-button:hover { background: var(--green-dark); transform: translateY(-1px); }

    @media (max-width: 1120px) {
        .incident-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-areas: "incident patient" "body body" "treatment hospital";
        }
        .form-column { display: contents; }
    }

    @media (max-width: 720px) {
        .incident-page { width: min(100% - 24px, 1480px); padding-top: 20px; }
        .page-heading { align-items: stretch; flex-direction: column; }
        .back-link { align-self: flex-start; }
        .incident-form { grid-template-columns: 1fr; }
        .field-grid { grid-template-columns: 1fr; }
        .form-panel { padding: 17px; }
        .incident-form { grid-template-areas: "incident" "patient" "body" "treatment" "hospital"; }
        .body-heading { align-items: stretch; flex-direction: column; }
        .body-actions { justify-content: space-between; }
        .body-instruction { margin-left: 0; }
        .body-workspace { grid-template-columns: 1fr; }
        .body-canvas { width: min(100%, 150px); }
    }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="incident-page">
    <div class="page-heading">
        <div>
            <h1>Nova ocorrência</h1>
            <p>Registe a ocorrência, identifique o utente e indique se foi encaminhado para o hospital.</p>
        </div>
        <a class="back-link" href="<?= htmlspecialchars($baseUrl) ?>?route=dashboard" aria-label="Voltar ao painel">
            <span aria-hidden="true">&larr;</span> Voltar ao painel
        </a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash-error" role="alert">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form class="incident-form" method="post" action="<?= htmlspecialchars($baseUrl) ?>?route=incidents_store" id="incidents-form">
        <section class="form-panel patient-panel" aria-labelledby="patient-title">
            <h2 class="panel-title" id="patient-title"><span class="panel-icon" aria-hidden="true">U</span>Dados do utente</h2>

            <div class="field">
                <label class="required" for="patient_name">Nome completo</label>
                <input type="text" id="patient_name" name="patient_name" required maxlength="200" autocomplete="name" placeholder="Nome completo do utente" value="<?= htmlspecialchars($oldValue('patient_name')) ?>">
            </div>

            <div class="field-grid">
                <div class="field">
                    <label class="required" for="patient_dob">Data de nascimento</label>
                    <input type="date" id="patient_dob" name="patient_dob" required autocomplete="bday" min="1920-01-01" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($oldValue('patient_dob')) ?>">
                </div>
                <div class="field">
                    <label for="patient_gender">Género</label>
                    <select id="patient_gender" name="patient_gender">
                        <option value="" <?= $oldValue('patient_gender') === '' ? 'selected' : '' ?>>Não especificar</option>
                        <option value="M" <?= $oldValue('patient_gender') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $oldValue('patient_gender') === 'F' ? 'selected' : '' ?>>Feminino</option>
                        <option value="Outro" <?= $oldValue('patient_gender') === 'Outro' ? 'selected' : '' ?>>Outro</option>
                    </select>
                </div>
            </div>

            <div class="employee-toggle">
                <input type="checkbox" id="patient_is_employee" name="patient_is_employee" value="1" <?= $oldChecked('patient_is_employee') ? 'checked' : '' ?>>
                <label for="patient_is_employee">O utente é colaborador</label>
            </div>
        </section>

        <section class="form-panel incident-panel" aria-labelledby="incident-title">
            <h2 class="panel-title" id="incident-title"><span class="panel-icon" aria-hidden="true">O</span>Dados da ocorrência</h2>

            <div class="field-grid">
                <div class="field">
                    <label class="required" for="date">Data</label>
                    <input type="date" id="date" name="date" required value="<?= htmlspecialchars($oldValue('date', date('Y-m-d'))) ?>">
                </div>
                <div class="field">
                    <label class="required" for="time">Hora</label>
                    <input type="time" id="time" name="time" required value="<?= htmlspecialchars($oldValue('time', date('H:i'))) ?>">
                </div>
            </div>

            <div class="field">
                <label class="required" for="incident_type_input">Tipo de ocorrência</label>
                <input list="incident-types-list" name="incident_type_input" id="incident_type_input" required autocomplete="off" placeholder="Escreva ou escolha um tipo" value="<?= htmlspecialchars($oldValue('incident_type_input')) ?>">
                <p class="field-help">Pode selecionar um tipo existente ou escrever um novo.</p>
                <datalist id="incident-types-list">
                    <?php if (isset($types) && is_iterable($types)): ?>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= htmlspecialchars($type['name']) ?>" data-id="<?= (int)$type['id'] ?>"></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
                <input type="hidden" name="incident_type_id" id="incident_type_id" value="<?= htmlspecialchars($oldValue('incident_type_id')) ?>">
            </div>

            <div class="field">
                <label class="required" for="location_input">Local / Atração</label>
                <input list="locations-list" name="location_input" id="location_input" required autocomplete="off" placeholder="Escreva ou escolha um local" value="<?= htmlspecialchars($oldValue('location_input')) ?>">
                <p class="field-help">Pode selecionar um local existente ou escrever um novo.</p>
                <datalist id="locations-list">
                    <?php if (isset($locations) && is_iterable($locations)): ?>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?= htmlspecialchars($location['name']) ?>" data-id="<?= (int)$location['id'] ?>"></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
                <input type="hidden" name="location_id" id="location_id" value="<?= htmlspecialchars($oldValue('location_id')) ?>">
            </div>

            <div class="field">
                <label for="description">Descrição / Observações</label>
                <textarea id="description" name="description" maxlength="1000" placeholder="Descreva a ocorrência de forma sucinta, sem dados pessoais desnecessários."><?= htmlspecialchars($oldValue('description')) ?></textarea>
            </div>
        </section>

        <section class="form-panel body-panel" aria-labelledby="body-title">
            <div class="body-heading">
                <h2 class="panel-title" id="body-title"><span class="panel-icon" aria-hidden="true">+</span>Localização no corpo</h2>
                <div class="body-actions">
                    <span class="mark-count" id="mark-count" aria-live="polite">0 marcações</span>
                    <button class="clear-marks" id="clear-marks" type="button" disabled>Limpar</button>
                </div>
            </div>
            <p class="body-instruction">Escolha o tipo e clique na zona afetada. Clique numa marcação para a remover.</p>
            <input type="hidden" id="body_marks" name="body_marks" value="<?= htmlspecialchars($oldValue('body_marks', '[]'), ENT_QUOTES, 'UTF-8') ?>">

            <div class="body-workspace">
                <div class="body-maps">
                    <div class="body-map">
                        <button class="body-canvas" type="button" data-view="front" aria-label="Marcar zona afetada na frente do corpo">
                            <img src="/enfermaria/public/assets/img/body-front.svg" alt="Vista frontal do corpo humano" data-body-image="front">
                        </button>
                        <span class="body-map-label">Frente</span>
                    </div>
                    <div class="body-map">
                        <button class="body-canvas" type="button" data-view="back" aria-label="Marcar zona afetada nas costas">
                            <img src="/enfermaria/public/assets/img/body-back.svg" alt="Vista posterior do corpo humano" data-body-image="back">
                        </button>
                        <span class="body-map-label">Costas</span>
                    </div>
                </div>

                <div class="mark-legend" aria-label="Tipo de marcação">
                    <button class="legend-item mark-contusion active" type="button" data-mark-type="contusion" aria-pressed="true"><span class="legend-swatch" aria-hidden="true"></span>Hematoma / Contusão</button>
                    <button class="legend-item mark-wound" type="button" data-mark-type="wound" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span>Ferida</button>
                    <button class="legend-item mark-burn" type="button" data-mark-type="burn" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span>Queimadura</button>
                    <button class="legend-item mark-pain" type="button" data-mark-type="pain" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span>Dor</button>
                    <button class="legend-item mark-insect_bite" type="button" data-mark-type="insect_bite" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span>Picada de inseto</button>
                    <button class="legend-item mark-epistaxis" type="button" data-mark-type="epistaxis" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span>Epistaxis</button>
                    <button class="legend-item mark-other" type="button" data-mark-type="other" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span>Outra ocorrência</button>
                </div>
            </div>
        </section>

        <section class="form-panel treatment-panel" aria-labelledby="treatment-title">
            <h2 class="panel-title" id="treatment-title"><span class="panel-icon" aria-hidden="true">T</span>Tratamentos prestados</h2>
            <div class="treatment-list" id="treatment-list">
                <?php foreach ($oldTreatmentIds as $index => $oldTreatmentId): ?>
                    <div class="treatment-entry" data-treatment-entry>
                        <div class="treatment-fields">
                            <div>
                            <label class="required" for="treatment_type_id_<?= (int)$index ?>">Tratamento <?= (int)$index + 1 ?></label>
                            <select name="treatment_type_id[]" id="treatment_type_id_<?= (int)$index ?>" data-treatment-select required>
                                <option value="">Selecionar tratamento</option>
                                <?php foreach ($treatmentTypes as $treatmentType): ?>
                                    <option value="<?= (int)$treatmentType['id'] ?>" <?= (string)$oldTreatmentId === (string)$treatmentType['id'] ? 'selected' : '' ?>><?= htmlspecialchars($treatmentType['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            <div>
                                <label for="treatment_notes_<?= (int)$index ?>">Notas do tratamento</label>
                                <textarea class="treatment-notes" name="treatment_notes[]" id="treatment_notes_<?= (int)$index ?>" data-treatment-notes maxlength="1000" placeholder="Descreva os cuidados prestados."><?= htmlspecialchars((string)($oldTreatmentNotes[$index] ?? '')) ?></textarea>
                            </div>
                        </div>
                        <button class="remove-treatment" type="button" data-remove-treatment title="Remover tratamento" aria-label="Remover tratamento">&times;</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="add-treatment" type="button" id="add-treatment"><span aria-hidden="true">+</span> Adicionar tratamento</button>
            <p class="field-help">Pode registar vários tratamentos na mesma ocorrência.</p>
        </section>

        <aside class="hospital-column" aria-label="Encaminhamento hospitalar e ações">
            <section class="form-panel hospital-panel" aria-labelledby="hospital-title">
                <h2 class="panel-title" id="hospital-title"><span class="panel-icon" aria-hidden="true">H</span>Encaminhamento hospitalar</h2>

                <div class="hospital-toggle">
                    <input type="checkbox" id="hospital_transfer" name="hospital_transfer" value="1" <?= $oldChecked('hospital_transfer') ? 'checked' : '' ?>>
                    <label for="hospital_transfer">
                        <strong>Enviar para o hospital</strong>
                        <span>Ative para completar os dados necessários ao encaminhamento.</span>
                    </label>
                </div>

                <div class="hospital-details" id="hospital-details">
                    <div class="field">
                        <label class="required" for="patient_nationality">Nacionalidade</label>
                        <input type="text" id="patient_nationality" name="patient_nationality" autocomplete="country-name" data-hospital-required value="<?= htmlspecialchars($oldValue('patient_nationality')) ?>">
                    </div>

                    <div class="field">
                        <label class="required" for="patient_address">Morada</label>
                        <input type="text" id="patient_address" name="patient_address" autocomplete="street-address" data-hospital-required value="<?= htmlspecialchars($oldValue('patient_address')) ?>">
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label class="required" for="patient_postal_code">Código postal</label>
                            <input type="text" id="patient_postal_code" name="patient_postal_code" autocomplete="postal-code" data-hospital-required value="<?= htmlspecialchars($oldValue('patient_postal_code')) ?>">
                        </div>
                        <div class="field">
                            <label class="required" for="patient_city">Cidade</label>
                            <input type="text" id="patient_city" name="patient_city" autocomplete="address-level2" data-hospital-required value="<?= htmlspecialchars($oldValue('patient_city')) ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label class="required" for="patient_phone">Telefone</label>
                        <input type="tel" id="patient_phone" name="patient_phone" autocomplete="tel" placeholder="+351 912 345 678" data-hospital-required value="<?= htmlspecialchars($oldValue('patient_phone')) ?>">
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label for="patient_id_type">Identificação</label>
                            <select id="patient_id_type" name="patient_id_type">
                                <option value="" <?= $oldValue('patient_id_type') === '' ? 'selected' : '' ?>>Não indicar</option>
                                <option value="CC" <?= $oldValue('patient_id_type') === 'CC' ? 'selected' : '' ?>>Cartão de Cidadão</option>
                                <option value="Passaporte" <?= $oldValue('patient_id_type') === 'Passaporte' ? 'selected' : '' ?>>Passaporte</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="patient_id_number">Número</label>
                            <input type="text" id="patient_id_number" name="patient_id_number" value="<?= htmlspecialchars($oldValue('patient_id_number')) ?>">
                        </div>
                    </div>

                    <div class="refusal-toggle">
                        <input type="checkbox" id="patient_refused_hospital" name="patient_refused_hospital" value="1" <?= $oldChecked('patient_refused_hospital') ? 'checked' : '' ?>>
                        <label for="patient_refused_hospital">O encaminhamento foi recomendado, mas o utente recusou.</label>
                    </div>
                </div>
            </section>

            <div class="privacy-note">Os dados adicionais só são recolhidos quando existe encaminhamento hospitalar e ficam limitados aos perfis autorizados.</div>
            <button class="submit-button" type="submit">Guardar ocorrência</button>
        </aside>
    </form>
</main>

<script>
    const form = document.getElementById('incidents-form');
    const leftColumn = document.createElement('div');
    const rightColumn = document.createElement('div');
    leftColumn.className = 'form-column form-column-left';
    rightColumn.className = 'form-column form-column-right';
    leftColumn.append(
        form.querySelector('.incident-panel'),
        form.querySelector('.treatment-panel'),
        form.querySelector('.hospital-column')
    );
    rightColumn.append(
        form.querySelector('.patient-panel'),
        form.querySelector('.body-panel')
    );
    form.append(leftColumn, rightColumn);

    const hospitalTransfer = document.getElementById('hospital_transfer');
    const hospitalDetails = document.getElementById('hospital-details');
    const hospitalRequiredFields = hospitalDetails.querySelectorAll('[data-hospital-required]');
    const patientDob = document.getElementById('patient_dob');
    const bodyMarksInput = document.getElementById('body_marks');
    const patientGender = document.getElementById('patient_gender');
    const markCount = document.getElementById('mark-count');
    const clearMarks = document.getElementById('clear-marks');
    const bodyCanvases = document.querySelectorAll('.body-canvas');
    const legendItems = document.querySelectorAll('.legend-item[data-mark-type]');
    const treatmentList = document.getElementById('treatment-list');
    const addTreatmentButton = document.getElementById('add-treatment');
    const markTypes = {
        contusion: 'Hematoma / Contusão',
        wound: 'Ferida',
        burn: 'Queimadura',
        pain: 'Dor',
        insect_bite: 'Picada de inseto',
        epistaxis: 'Epistaxis',
        other: 'Outra ocorrência'
    };
    let selectedMarkType = 'contusion';
    let bodyMarks = [];

    try {
        const savedMarks = JSON.parse(bodyMarksInput.value || '[]');
        if (Array.isArray(savedMarks)) bodyMarks = savedMarks.slice(0, 20);
    } catch (error) {
        bodyMarks = [];
    }

    const bodyImagePaths = {
        front: '/enfermaria/public/assets/img/body-front.svg',
        back: '/enfermaria/public/assets/img/body-back.svg',
        frontFemale: '/enfermaria/public/assets/img/body-front-female.svg',
        backFemale: '/enfermaria/public/assets/img/body-back-female.svg'
    };

    function wireDatalist(inputId, datalistId, hiddenId) {
        const input = document.getElementById(inputId);
        const datalist = document.getElementById(datalistId);
        const hidden = document.getElementById(hiddenId);
        const options = new Map();

        datalist.querySelectorAll('option').forEach(option => {
            if (option.value && option.dataset.id) options.set(option.value.trim(), option.dataset.id);
        });

        input.addEventListener('input', () => {
            hidden.value = options.get(input.value.trim()) || '';
        });
    }

    function syncHospitalDetails() {
        const visible = hospitalTransfer.checked;
        form.classList.toggle('hospital-expanded', visible);
        if (visible) {
            form.appendChild(document.querySelector('.hospital-column'));
        } else {
            leftColumn.appendChild(document.querySelector('.hospital-column'));
        }
        hospitalDetails.classList.toggle('visible', visible);
        hospitalDetails.setAttribute('aria-hidden', String(!visible));
        hospitalRequiredFields.forEach(field => { field.required = visible; });
    }

    function validateBirthDate() {
        const value = patientDob.value;
        const minimum = patientDob.min;
        const maximum = patientDob.max;
        const valid = value === '' || (value >= minimum && value <= maximum);
        patientDob.setCustomValidity(valid ? '' : 'A data de nascimento deve estar entre 1920 e a data de hoje.');
        return valid;
    }

    function updateBodyImages() {
        const female = patientGender.value === 'F';
        document.querySelectorAll('[data-body-image]').forEach(image => {
            const view = image.dataset.bodyImage;
            image.src = bodyImagePaths[female ? `${view}Female` : view];
        });
    }

    function renderBodyMarks() {
        document.querySelectorAll('.body-marker').forEach(marker => marker.remove());
        bodyMarks.forEach((mark, index) => {
            const canvas = document.querySelector(`.body-canvas[data-view="${mark.view}"]`);
            if (!canvas) return;
            const type = markTypes[mark.type] ? mark.type : 'other';
            const marker = document.createElement('span');
            marker.className = `body-marker mark-${type}`;
            marker.dataset.index = String(index);
            marker.style.left = `${mark.x}%`;
            marker.style.top = `${mark.y}%`;
            marker.title = `${markTypes[type]} — remover marcação`;
            canvas.appendChild(marker);
        });
        bodyMarksInput.value = JSON.stringify(bodyMarks);
        markCount.textContent = `${bodyMarks.length} ${bodyMarks.length === 1 ? 'marcação' : 'marcações'}`;
        clearMarks.disabled = bodyMarks.length === 0;
    }

    function getTreatmentEntries() {
        return Array.from(treatmentList.querySelectorAll('[data-treatment-entry]'));
    }

    function refreshTreatmentEntries() {
        const entries = getTreatmentEntries();
        entries.forEach((entry, index) => {
            const label = entry.querySelector('label');
            const select = entry.querySelector('[data-treatment-select]');
            const notes = entry.querySelector('[data-treatment-notes]');
            const notesLabel = notes.previousElementSibling;
            const removeButton = entry.querySelector('[data-remove-treatment]');
            const selectedElsewhere = new Set(
                entries
                    .filter(otherEntry => otherEntry !== entry)
                    .map(otherEntry => otherEntry.querySelector('[data-treatment-select]').value)
                    .filter(Boolean)
            );

            label.textContent = `Tratamento ${index + 1}`;
            label.htmlFor = `treatment_type_id_${index}`;
            select.id = `treatment_type_id_${index}`;
            notes.id = `treatment_notes_${index}`;
            notesLabel.htmlFor = notes.id;
            Array.from(select.options).forEach(option => {
                option.disabled = option.value !== '' && selectedElsewhere.has(option.value);
            });
            removeButton.style.visibility = entries.length === 1 ? 'hidden' : 'visible';
        });

        const availableCount = treatmentList.querySelector('[data-treatment-select]')?.options.length - 1 || 0;
        addTreatmentButton.disabled = entries.length >= availableCount;
    }

    function wireTreatmentEntry(entry) {
        entry.querySelector('[data-treatment-select]').addEventListener('change', refreshTreatmentEntries);
        entry.querySelector('[data-remove-treatment]').addEventListener('click', () => {
            if (getTreatmentEntries().length === 1) return;
            entry.remove();
            refreshTreatmentEntries();
        });
    }

    function addTreatmentEntry() {
        const sourceSelect = treatmentList.querySelector('[data-treatment-select]');
        const entry = document.createElement('div');
        entry.className = 'treatment-entry';
        entry.dataset.treatmentEntry = '';
        entry.innerHTML = `
            <div class="treatment-fields">
                <div>
                    <label class="required">Tratamento</label>
                    <select name="treatment_type_id[]" data-treatment-select required>${sourceSelect.innerHTML}</select>
                </div>
                <div>
                    <label>Notas do tratamento</label>
                    <textarea class="treatment-notes" name="treatment_notes[]" data-treatment-notes maxlength="1000" placeholder="Descreva os cuidados prestados."></textarea>
                </div>
            </div>
            <button class="remove-treatment" type="button" data-remove-treatment title="Remover tratamento" aria-label="Remover tratamento">&times;</button>
        `;
        treatmentList.appendChild(entry);
        wireTreatmentEntry(entry);
        refreshTreatmentEntries();
        entry.querySelector('[data-treatment-select]').focus();
    }

    wireDatalist('incident_type_input', 'incident-types-list', 'incident_type_id');
    wireDatalist('location_input', 'locations-list', 'location_id');
    hospitalTransfer.addEventListener('change', syncHospitalDetails);
    patientDob.addEventListener('input', validateBirthDate);
    patientDob.addEventListener('change', validateBirthDate);
    patientGender.addEventListener('change', updateBodyImages);
    document.querySelectorAll('[data-body-image]').forEach(image => {
        image.addEventListener('error', () => { image.src = bodyImagePaths[image.dataset.bodyImage]; });
    });
    bodyCanvases.forEach(canvas => {
        canvas.addEventListener('click', event => {
            const marker = event.target.closest('.body-marker');
            if (marker) {
                bodyMarks.splice(Number(marker.dataset.index), 1);
                renderBodyMarks();
                return;
            }
            if (bodyMarks.length >= 20) return;
            const rect = canvas.getBoundingClientRect();
            bodyMarks.push({
                view: canvas.dataset.view,
                type: selectedMarkType,
                x: Number((((event.clientX - rect.left) / rect.width) * 100).toFixed(2)),
                y: Number((((event.clientY - rect.top) / rect.height) * 100).toFixed(2))
            });
            renderBodyMarks();
        });
    });
    clearMarks.addEventListener('click', () => { bodyMarks = []; renderBodyMarks(); });
    getTreatmentEntries().forEach(wireTreatmentEntry);
    addTreatmentButton.addEventListener('click', addTreatmentEntry);
    legendItems.forEach(item => {
        item.addEventListener('click', () => {
            selectedMarkType = item.dataset.markType;
            legendItems.forEach(option => {
                const active = option === item;
                option.classList.toggle('active', active);
                option.setAttribute('aria-pressed', String(active));
            });
        });
    });
    form.addEventListener('submit', event => {
        if (!validateBirthDate()) {
            event.preventDefault();
            patientDob.reportValidity();
            patientDob.focus();
        }
    });
    syncHospitalDetails();
    updateBodyImages();
    renderBodyMarks();
    refreshTreatmentEntries();
</script>
</body>
</html>
