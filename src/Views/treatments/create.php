<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
$hospitalTreatmentTypeId = (int)($hospitalTreatmentTypeId ?? 0);
$patientHospitalData = $patientHospitalData ?? [];
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
        padding: 2rem; /* Aumentado padding para mais espaço */
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
    input, select, textarea {
        width: 100%; 
        box-sizing: border-box;
        padding: 0.7rem 0.9rem; /* Aumentado padding para inputs maiores */
        margin-top: 0.3rem; 
        border-radius: 8px; 
        border: 1px solid #ddd; 
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    input:focus, select:focus, textarea:focus {
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
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Melhorado para grid, mais responsivo */
        gap: 1.5rem;
        margin-bottom: 1.5rem;
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
    .small {
        font-size: 0.85rem;
        color: #777;
        margin-top: 0.3rem;
    }
    .small.error {
        color: #b42318;
    }
    .section-title {
        margin-top: 2rem;
        font-weight: 600;
        color: #555;
        font-size: 1.1rem;
    }
    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 1rem 0;
    }
    .treatment-list {
        display: grid;
        gap: 0.85rem;
        margin-top: 0.5rem;
    }
    .treatment-entry {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.85rem;
        align-items: end;
    }
    .treatment-entry-field {
        padding: 0.9rem 1rem;
        border: 1px solid #d9e2f1;
        border-radius: 10px;
        background: #f8fbff;
    }
    .treatment-entry-field label {
        margin-top: 0;
    }
    .secondary-button {
        padding: 0.75rem 1rem;
        background: #e8f0fe;
        color: #174ea6;
    }
    .secondary-button:hover {
        background: #d7e6fd;
    }
    .remove-treatment {
        background: #fff1f2;
        color: #b42318;
        margin-top: 0;
    }
    .remove-treatment:hover {
        background: #ffe4e6;
    }
    .treatment-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }
    input[type="checkbox"] {
        accent-color: #1f6feb; /* Cor do checkbox */
        width: 18px;
        height: 18px;
    }
    #treatment-block {
        margin-top: 1rem;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #eee;
    }
    #patient-block {
        margin-top: 1.5rem;
        padding: 1.5rem;
        border-radius: 8px;
        background: #fff7e6;
        border: 1px solid #ffe4b5;
    }
    .separator {
        border: none;
        border-top: 1px solid #ddd;
        margin: 2rem 0;
    }

    .address-row {
        grid-template-columns: 2.3fr 1fr 1.2fr 1fr; /* Morada, Código Postal, Cidade, Telefone */
    }

    .patient-row {
        grid-template-columns: 2.5fr 1fr 1.2fr; /* Nome, Idade, Género */
    }

    .small-field {
        max-width: 420px;
    }
    .medium-field {
        max-width: 550px;
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
        <?php if (!empty($incident['patient_dob'])): ?>
            <strong>Data de nascimento:</strong> <?= htmlspecialchars($incident['patient_dob']) ?> ·
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

    <form method="post" action="<?= $baseUrl ?>?route=treatments_store" id="treatments-form">
        <input type="hidden" name="incident_id" value="<?= (int)$incident['id'] ?>">

        <label class="required">Tipos de tratamento</label>
        <div class="treatment-list" id="treatment-list">
            <div class="treatment-entry" data-treatment-entry>
                <div class="treatment-entry-field">
                    <label for="treatment_type_id_0">Tratamento 1</label>
                    <select
                        name="treatment_type_id[]"
                        id="treatment_type_id_0"
                        data-treatment-select
                        required
                    >
                        <option value="">-- Selecionar --</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" class="remove-treatment" data-remove-treatment style="display:none;">Remover</button>
            </div>
        </div>
        <div class="small">Escolha um tratamento existente.</div>
        <div class="small error" id="treatment-selection-error" style="display:none;">Selecione pelo menos um tratamento.</div>
        <div class="treatment-actions">
            <button type="button" class="secondary-button" id="add-treatment">Adicionar outro tratamento</button>
        </div>

        <label>Estado</label>
        <select name="status">            
            <option value="concluido">Concluído</option>
            <option value="em_curso">Em curso</option>
        </select>
        <div style="margin-right: 24px;">
            <label>Notas / Observações (opcional)</label>
            <textarea name="notes" placeholder="Descrição do tratamento realizado. Evite dados pessoais desnecessários. Se incluir 'Enviado para hospital', este texto poderá aparecer no termo de seguro."></textarea>
        </div>

            <!-- Campos extra se for 'Enviado para hospital' -->
            <div id="patient-block" style="display:none;">
                <strong>Restantes dados do utente (para envio ao hospital)</strong>

                <div class="row">
                    <div style="margin-right: 24px; max-width: 400px;">
                        <label>Nacionalidade</label>
                        <input type="text" name="patient_nationality" value="<?= htmlspecialchars((string)($patientHospitalData['nationality'] ?? '')) ?>">
                    </div>
                </div>

                <div class="row address-row">
                    <div style="margin-right: 24px;">
                        <label class="required">Morada</label>
                        <input type="text" name="patient_address" id="patient_address" value="<?= htmlspecialchars((string)($patientHospitalData['address'] ?? '')) ?>">
                    </div>
             
                    <div style="margin-right: 24px;">
                        <label class="required">Código Postal</label>
                        <input type="text" name="patient_postal_code" id="patient_postal_code" value="<?= htmlspecialchars((string)($patientHospitalData['postal_code'] ?? '')) ?>">
                    </div>
                    
                    <div style="margin-right: 24px;">
                        <label class="required">Cidade</label>
                        <input type="text" name="patient_city" id="patient_city" value="<?= htmlspecialchars((string)($patientHospitalData['city'] ?? '')) ?>">
                    </div>

                    <div style="margin-right: 24px;">
                        <label class="required">Telefone</label>
                        <input type="text" name="patient_phone" id="patient_phone" placeholder="+351 912 345 678" value="<?= htmlspecialchars((string)($patientHospitalData['phone'] ?? '')) ?>">
                    </div>
                </div>

                <div class="row">
                    <div style="margin-right: 24px;">
                        <label>Data de Nascimento</label>
                        <input type="date" name="patient_dob" id="patient_dob" min="1920-01-01" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars((string)($patientHospitalData['dob'] ?? '')) ?>">
                    </div>
                    <div style="margin-right: 24px;">
                        <label>Tipo de Identificação</label>
                        <select name="patient_id_type" id="patient_id_type">
                            <option value="">-- Selecionar --</option>
                            <option value="CC" <?= (($patientHospitalData['id_type'] ?? '') === 'CC') ? 'selected' : '' ?>>Cartão de Cidadão (CC)</option>
                            <option value="Passaporte" <?= (($patientHospitalData['id_type'] ?? '') === 'Passaporte') ? 'selected' : '' ?>>Passaporte</option>
                        </select>
                    </div>
                </div>

                <div class="row small-field">
                    <div style="margin-right: 24px;">
                        <label>Número de Identificação</label>
                        <input type="text" name="patient_id_number" id="patient_id_number" placeholder="Número do CC ou do Passaporte" value="<?= htmlspecialchars((string)($patientHospitalData['id_number'] ?? '')) ?>">
                    </div>
                </div>

                <div class="form-check" style="margin-top:1rem;">
                    <input
                        type="checkbox"
                        id="patient_refused_hospital"
                        name="patient_refused_hospital"
                        value="1"
                        <?= !empty($patientHospitalData['refused_hospital']) ? 'checked' : '' ?>
                    >
                    <label for="patient_refused_hospital" style="cursor:pointer;">
                        O utente recusou deslocação ao hospital
                    </label>
                </div>

                <div class="small" style="margin-top:.3rem;">
                    Assinalar apenas se o envio ao hospital foi recomendado
                    e recusado pelo próprio utente.
                </div>

                <div class="small">
                    Estes dados só serão visíveis para o administrador e para o enfermeiro responsável.
                </div>
            </div>

        <button type="submit">Guardar tratamento</button>
    </form>
</main>

<script>
    const form = document.getElementById('treatments-form');
    const treatmentList = document.getElementById('treatment-list');
    const addTreatmentButton = document.getElementById('add-treatment');
    const selectionError = document.getElementById('treatment-selection-error');
    const patientBlock = document.getElementById('patient-block');
    const hospitalTreatmentTypeId = <?= $hospitalTreatmentTypeId ?>;

    function wireBirthDateField(inputId) {
        const input = document.getElementById(inputId);
        if (!input) {
            return;
        }

        const minValue = input.getAttribute('min') || '';
        const maxValue = input.getAttribute('max') || '';
        const errorMessage = 'A data de nascimento deve estar entre 1920-01-01 e a data de hoje.';

        const isWithinBounds = (value) => {
            if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return false;
            }

            if (minValue !== '' && value < minValue) {
                return false;
            }

            if (maxValue !== '' && value > maxValue) {
                return false;
            }

            return true;
        };

        const validate = () => {
            if (input.value === '') {
                input.setCustomValidity('');
                return;
            }

            if (!isWithinBounds(input.value)) {
                input.setCustomValidity(errorMessage);
                return;
            }

            input.setCustomValidity('');
        };

        input.addEventListener('input', validate);
        input.addEventListener('change', validate);
        input.addEventListener('blur', () => {
            validate();

            if (input.value !== '' && !isWithinBounds(input.value)) {
                input.value = '';
                input.setCustomValidity(errorMessage);
                input.reportValidity();
            }
        });
    }

    wireBirthDateField('patient_dob');

    function getTreatmentEntries() {
        return Array.from(treatmentList.querySelectorAll('[data-treatment-entry]'));
    }

    function getSelectedTreatmentIds(exceptEntry = null) {
        const selectedIds = new Set();

        getTreatmentEntries().forEach((entry) => {
            if (entry === exceptEntry) {
                return;
            }

            const select = entry.querySelector('select[data-treatment-select]');
            const selectedId = Number(select.value);
            if (selectedId > 0) {
                selectedIds.add(selectedId);
            }
        });

        return selectedIds;
    }

    function refreshTreatmentChoices() {
        getTreatmentEntries().forEach((entry) => {
            const select = entry.querySelector('select[data-treatment-select]');
            const currentValue = select.value;
            const selectedElsewhere = getSelectedTreatmentIds(entry);

            Array.from(select.options).forEach((option) => {
                if (option.value === '') {
                    return;
                }
                const id = Number(option.value);
                option.disabled = selectedElsewhere.has(id) && option.value !== currentValue;
            });
        });
    }

    function syncTreatmentLabels() {
        getTreatmentEntries().forEach((entry, index) => {
            const label = entry.querySelector('label');
            const select = entry.querySelector('select[data-treatment-select]');
            const removeButton = entry.querySelector('[data-remove-treatment]');

            label.textContent = 'Tratamento ' + (index + 1);
            label.setAttribute('for', 'treatment_type_id_' + index);
            select.id = 'treatment_type_id_' + index;
            removeButton.style.display = index === 0 && getTreatmentEntries().length === 1 ? 'none' : 'inline-flex';
        });
    }

    function wireTreatmentEntry(entry) {
        const select = entry.querySelector('select[data-treatment-select]');
        const removeButton = entry.querySelector('[data-remove-treatment]');

        select.addEventListener('change', () => {
            refreshTreatmentChoices();
            updateSelectionValidity();
            togglePatientBlock();
        });

        removeButton.addEventListener('click', () => {
            entry.remove();
            syncTreatmentLabels();
            refreshTreatmentChoices();
            updateSelectionValidity();
            togglePatientBlock();
        });
    }

    function buildTreatmentOptionsHtml() {
        const source = treatmentList.querySelector('select[data-treatment-select]');
        if (!source) {
            return '';
        }
        return Array.from(source.options)
            .map((option) => {
                const value = option.value === '' ? '' : String(Number(option.value));
                const label = option.textContent;
                return '<option value="' + value.replace(/"/g, '&quot;') + '">' + label + '</option>';
            })
            .join('');
    }

    function createTreatmentEntry() {
        const entry = document.createElement('div');
        entry.className = 'treatment-entry';
        entry.setAttribute('data-treatment-entry', '');
        entry.innerHTML = `
            <div class="treatment-entry-field">
                <label>Tratamento</label>
                <select name="treatment_type_id[]" data-treatment-select required>
                    ${buildTreatmentOptionsHtml()}
                </select>
            </div>
            <button type="button" class="remove-treatment" data-remove-treatment>Remover</button>
        `;

        wireTreatmentEntry(entry);
        treatmentList.appendChild(entry);
        syncTreatmentLabels();
        refreshTreatmentChoices();

        const select = entry.querySelector('select[data-treatment-select]');
        select.focus();
    }

    function hasSelectedTreatments() {
        return getTreatmentEntries().some((entry) => {
            const select = entry.querySelector('select[data-treatment-select]');
            return Number(select.value) > 0;
        });
    }

    function updateSelectionValidity() {
        const hasSelection = hasSelectedTreatments();

        const firstSelect = treatmentList.querySelector('select[data-treatment-select]');
        if (firstSelect) {
            firstSelect.setCustomValidity(hasSelection ? '' : 'Selecione pelo menos um tratamento.');
        }

        selectionError.style.display = hasSelection ? 'none' : 'block';
    }

    function togglePatientBlock() {
        const hasHospitalTransfer = getTreatmentEntries().some((entry) => {
            const select = entry.querySelector('select[data-treatment-select]');
            return Number(select.value) === hospitalTreatmentTypeId;
        });

        patientBlock.style.display = hasHospitalTransfer ? 'block' : 'none';
    }

    addTreatmentButton.addEventListener('click', () => {
        createTreatmentEntry();
    });

    form.addEventListener('submit', (event) => {
        updateSelectionValidity();

        if (!hasSelectedTreatments()) {
            event.preventDefault();
            const firstSelect = treatmentList.querySelector('select[data-treatment-select]');
            if (firstSelect) {
                firstSelect.reportValidity();
            }
        }
    });

    // garantir estado correto quando a página abre
    document.addEventListener('DOMContentLoaded', () => {
        getTreatmentEntries().forEach((entry) => {
            wireTreatmentEntry(entry);
        });
        syncTreatmentLabels();
        refreshTreatmentChoices();
        updateSelectionValidity();
        togglePatientBlock();
    });
</script>

</body>
</html>