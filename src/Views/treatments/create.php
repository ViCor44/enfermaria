<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
$hospitalTreatmentTypeId = (int)($hospitalTreatmentTypeId ?? 0);
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

    <form method="post" action="<?= $baseUrl ?>?route=treatments_store" id="treatments-form">
        <input type="hidden" name="incident_id" value="<?= (int)$incident['id'] ?>">

        <label class="required">Tipos de tratamento</label>
        <div class="treatment-list" id="treatment-list">
            <div class="treatment-entry" data-treatment-entry>
                <div class="treatment-entry-field">
                    <label for="treatment_type_input_0">Tratamento 1</label>
                    <input
                        list="treatment-types-list"
                        name="treatment_type_input[]"
                        id="treatment_type_input_0"
                        placeholder="Escreva ou escolha..."
                        autocomplete="off"
                    >
                    <input type="hidden" name="treatment_type_id[]" value="">
                </div>
                <button type="button" class="remove-treatment" data-remove-treatment style="display:none;">Remover</button>
            </div>
        </div>
        <datalist id="treatment-types-list">
            <?php foreach ($types as $t): ?>
                <option value="<?= htmlspecialchars($t['name']) ?>" data-id="<?= (int)$t['id'] ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <div class="small">Pode escolher um tratamento existente ou escrever um novo nome.</div>
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
            <textarea name="notes" placeholder="Descrição do tratamento realizado. Evite dados pessoais desnecessários."></textarea>
        </div>

            <!-- Campos extra se for 'Enviado para hospital' -->
            <div id="patient-block" style="display:none;">
                <strong>Restantes dados do utente (para envio ao hospital)</strong>

                <div class="row">
                    <div style="margin-right: 24px; max-width: 400px;">
                        <label>Nacionalidade</label>
                        <input type="text" name="patient_nationality">
                    </div>
                </div>

                <div class="row address-row">
                    <div style="margin-right: 24px;">
                        <label class="required">Morada</label>
                        <input type="text" name="patient_address" id="patient_address">
                    </div>
             
                    <div style="margin-right: 24px;">
                        <label class="required">Código Postal</label>
                        <input type="text" name="patient_postal_code" id="patient_postal_code">
                    </div>
                    
                    <div style="margin-right: 24px;">
                        <label class="required">Cidade</label>
                        <input type="text" name="patient_city" id="patient_city">
                    </div>

                    <div style="margin-right: 24px;">
                        <label class="required">Telefone</label>
                        <input type="text" name="patient_phone" id="patient_phone" placeholder="+351 912 345 678">
                    </div>
                </div>

                <div class="row">
                    <div style="margin-right: 24px;">
                        <label>Data de Nascimento</label>
                        <input type="date" name="patient_dob" id="patient_dob">
                    </div>
                    <div style="margin-right: 24px;">
                        <label>Tipo de Identificação</label>
                        <select name="patient_id_type" id="patient_id_type">
                            <option value="">-- Selecionar --</option>
                            <option value="CC">Cartão de Cidadão (CC)</option>
                            <option value="Passaporte">Passaporte</option>
                        </select>
                    </div>
                </div>

                <div class="row small-field">
                    <div style="margin-right: 24px;">
                        <label>Número de Identificação</label>
                        <input type="text" name="patient_id_number" id="patient_id_number" placeholder="Número do CC ou do Passaporte">
                    </div>
                </div>

                <div class="form-check" style="margin-top:1rem;">
                    <input
                        type="checkbox"
                        id="patient_refused_hospital"
                        name="patient_refused_hospital"
                        value="1"
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
    const treatmentDatalist = document.getElementById('treatment-types-list');
    const selectionError = document.getElementById('treatment-selection-error');
    const patientBlock = document.getElementById('patient-block');
    const hospitalTreatmentTypeId = <?= $hospitalTreatmentTypeId ?>;

    function buildTreatmentMap() {
        const map = new Map();

        treatmentDatalist.querySelectorAll('option').forEach((option) => {
            const value = option.value ? option.value.trim() : '';
            const id = option.getAttribute('data-id');

            if (value !== '' && id) {
                map.set(value, id);
            }
        });

        return map;
    }

    let treatmentMap = buildTreatmentMap();

    function getTreatmentEntries() {
        return Array.from(treatmentList.querySelectorAll('[data-treatment-entry]'));
    }

    function syncTreatmentLabels() {
        getTreatmentEntries().forEach((entry, index) => {
            const label = entry.querySelector('label');
            const input = entry.querySelector('input[name="treatment_type_input[]"]');
            const removeButton = entry.querySelector('[data-remove-treatment]');

            label.textContent = 'Tratamento ' + (index + 1);
            label.setAttribute('for', 'treatment_type_input_' + index);
            input.id = 'treatment_type_input_' + index;
            removeButton.style.display = index === 0 && getTreatmentEntries().length === 1 ? 'none' : 'inline-flex';
        });
    }

    function wireTreatmentEntry(entry) {
        const input = entry.querySelector('input[name="treatment_type_input[]"]');
        const hidden = entry.querySelector('input[name="treatment_type_id[]"]');
        const removeButton = entry.querySelector('[data-remove-treatment]');

        const syncHiddenValue = () => {
            const value = input.value.trim();
            hidden.value = treatmentMap.has(value) ? treatmentMap.get(value) : '';
            updateSelectionValidity();
            togglePatientBlock();
        };

        input.addEventListener('input', syncHiddenValue);
        input.addEventListener('change', syncHiddenValue);

        removeButton.addEventListener('click', () => {
            entry.remove();
            syncTreatmentLabels();
            updateSelectionValidity();
            togglePatientBlock();
        });
    }

    function createTreatmentEntry() {
        const entry = document.createElement('div');
        entry.className = 'treatment-entry';
        entry.setAttribute('data-treatment-entry', '');
        entry.innerHTML = `
            <div class="treatment-entry-field">
                <label>Tratamento</label>
                <input
                    list="treatment-types-list"
                    name="treatment_type_input[]"
                    placeholder="Escreva ou escolha..."
                    autocomplete="off"
                >
                <input type="hidden" name="treatment_type_id[]" value="">
            </div>
            <button type="button" class="remove-treatment" data-remove-treatment>Remover</button>
        `;

        wireTreatmentEntry(entry);
        treatmentList.appendChild(entry);
        syncTreatmentLabels();

        const input = entry.querySelector('input[name="treatment_type_input[]"]');
        input.focus();
    }

    function hasSelectedTreatments() {
        return getTreatmentEntries().some((entry) => {
            const input = entry.querySelector('input[name="treatment_type_input[]"]');
            return input.value.trim() !== '';
        });
    }

    function updateSelectionValidity() {
        const hasSelection = hasSelectedTreatments();

        const firstInput = treatmentList.querySelector('input[name="treatment_type_input[]"]');
        if (firstInput) {
            firstInput.setCustomValidity(hasSelection ? '' : 'Selecione pelo menos um tratamento.');
        }

        selectionError.style.display = hasSelection ? 'none' : 'block';
    }

    function togglePatientBlock() {
        const hasHospitalTransfer = getTreatmentEntries().some((entry) => {
            const input = entry.querySelector('input[name="treatment_type_input[]"]');
            const hidden = entry.querySelector('input[name="treatment_type_id[]"]');
            const typedValue = input.value.trim().toLowerCase();

            return Number(hidden.value) === hospitalTreatmentTypeId || typedValue === 'enviado para hospital';
        });

        if (hasHospitalTransfer) {
            patientBlock.style.display = 'block';
        } else {
            patientBlock.style.display = 'none';
        }
    }

    addTreatmentButton.addEventListener('click', () => {
        createTreatmentEntry();
    });

    form.addEventListener('submit', (event) => {
        updateSelectionValidity();

        if (!hasSelectedTreatments()) {
            event.preventDefault();
            const firstInput = treatmentList.querySelector('input[name="treatment_type_input[]"]');
            if (firstInput) {
                firstInput.reportValidity();
            }
        }
    });

    const datalistObserver = new MutationObserver(() => {
        treatmentMap = buildTreatmentMap();
    });
    datalistObserver.observe(treatmentDatalist, { childList: true, subtree: true });

    // garantir estado correto quando a página abre
    document.addEventListener('DOMContentLoaded', () => {
        getTreatmentEntries().forEach((entry) => {
            wireTreatmentEntry(entry);
        });
        syncTreatmentLabels();
        updateSelectionValidity();
        togglePatientBlock();
    });
</script>

</body>
</html>