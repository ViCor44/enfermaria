<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
$old = $_SESSION['old_incident_form'] ?? [];
unset($_SESSION['old_incident_form']);

$oldValue = static function (string $key, string $default = '') use ($old): string {
    $value = $old[$key] ?? $default;
    if (is_array($value)) {
        return $default;
    }

    return (string)$value;
};

$oldChecked = static function (string $key) use ($old): bool {
    return isset($old[$key]);
};

$oldTreatmentIds = [];
if (isset($old['treatment_type_id']) && is_array($old['treatment_type_id'])) {
    $oldTreatmentIds = $old['treatment_type_id'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Registar Ocorrência</title>
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
    .treatment-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
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
        grid-template-columns: 2.5fr 1fr 1.2fr; /* Nome, Data de nascimento, Género */
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
    <h1>Registar nova Ocorrência</h1>

    <hr class="separator">

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash-error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $baseUrl ?>?route=incidents_store" id="incidents-form">
        <div class="row">
            <!-- TIPO DE Ocorrência (input com datalist) -->
            <div style="margin-right: 24px;">
                <label class="required">Tipo de Ocorrência</label>
                <input
                    list="incident-types-list"
                    name="incident_type_input"
                    id="incident_type_input"
                    placeholder="Escreva ou escolha..."
                    required
                    autocomplete="off"
                    value="<?= htmlspecialchars($oldValue('incident_type_input')) ?>"
                >
                <datalist id="incident-types-list">
                    <?php if (isset($types) && is_iterable($types)): ?>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= htmlspecialchars($t['name']) ?>" data-id="<?= (int)$t['id'] ?>"></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
                <input type="hidden" name="incident_type_id" id="incident_type_id" value="<?= htmlspecialchars($oldValue('incident_type_id')) ?>">
                <div class="small">Pode escrever novo tipo de ocorrência ou escolher da lista — se não existir será criado.</div>
            </div>
            
            <div style="margin-right: 24px;">
                <label class="required">Local / Atração</label>
                <input
                    list="locations-list"
                    name="location_input"
                    id="location_input"
                    placeholder="Escreva ou escolha..."
                    required
                    autocomplete="off"
                    value="<?= htmlspecialchars($oldValue('location_input')) ?>"
                >
                <datalist id="locations-list">
                    <?php if (isset($locations) && is_iterable($locations)): ?>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= htmlspecialchars($loc['name']) ?>" data-id="<?= (int)$loc['id'] ?>"></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </datalist>
                <input type="hidden" name="location_id" id="location_id" value="<?= htmlspecialchars($oldValue('location_id')) ?>">
                <div class="small">Pode escrever o nome do local ou escolher da lista — se não existir será criado.</div>
            </div>

        </div>
        

        <!-- LINHA DATA / HORA -->
        <div class="row">
            <div style="margin-right: 24px;">
                <label class="required">Data</label>
                <input type="date" name="date" required value="<?= htmlspecialchars($oldValue('date', date('Y-m-d'))) ?>">
            </div>
            <div style="margin-right: 24px;">
                <label class="required">Hora</label>
                <input type="time" name="time" required value="<?= htmlspecialchars($oldValue('time', date('H:i'))) ?>">
            </div>
        </div>

        <!-- LINHA NOME / DATA DE NASCIMENTO / GÉNERO -->
        <div class="row patient-row">
            <div style="margin-right: 24px;">
                <label class="required">Nome do utente</label>
                <input type="text" name="patient_name" required autocomplete="name" value="<?= htmlspecialchars($oldValue('patient_name')) ?>">
            </div>

            <div style="margin-right: 24px;">
                <label class="required">Data de nascimento</label>
                <input type="date" id="patient_dob" name="patient_dob" required autocomplete="bday" min="1920-01-01" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($oldValue('patient_dob')) ?>">
            </div>

            <div style="margin-right: 24px;">
                <label>Género</label>
                <select name="patient_gender">
                    <option value="" <?= $oldValue('patient_gender') === '' ? 'selected' : '' ?>>-- Não especificar --</option>
                    <option value="M" <?= $oldValue('patient_gender') === 'M' ? 'selected' : '' ?>>Masculino</option>
                    <option value="F" <?= $oldValue('patient_gender') === 'F' ? 'selected' : '' ?>>Feminino</option>
                    <option value="Outro" <?= $oldValue('patient_gender') === 'Outro' ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>
        </div>

        <div class="form-check" style="margin: 0 24px 16px 0;">
            <input type="checkbox" id="patient_is_employee" name="patient_is_employee" value="1" <?= $oldChecked('patient_is_employee') ? 'checked' : '' ?>>
            <label for="patient_is_employee" style="cursor:pointer;">
                O utente é colaborador
            </label>
        </div>

        <div style="margin-right: 24px;">
        <label>Descrição / Observações</label>
        <textarea style="margin-right: 24px;" name="description" placeholder="Descrição sucinta da Ocorrência, sem dados de identificação desnecessários."><?= htmlspecialchars($oldValue('description')) ?></textarea>
        </div>

        <div class="section-title">
            <div class="form-check">
                <input type="checkbox" id="toggle-treatment" name="add_treatment" <?= $oldChecked('add_treatment') ? 'checked' : '' ?>>
                <label for="toggle-treatment" style="cursor:pointer;">
                    Adicionar tratamento agora
                </label>
            </div>
        </div>

        <div id="treatment-block" style="display:none;">
            <label class="required">Tipos de tratamento</label>
            <div class="treatment-list" id="treatment-list">
                <div class="treatment-entry" data-treatment-entry>
                    <div class="treatment-entry-field">
                        <label for="treatment_type_id_0">Tratamento 1</label>
                        <select
                            name="treatment_type_id[]"
                            id="treatment_type_id_0"
                            data-treatment-select
                        >
                            <option value="">-- Selecionar --</option>
                            <?php if (isset($treatmentTypes) && is_array($treatmentTypes)): ?>
                                <?php foreach ($treatmentTypes as $tt): ?>
                                    <option value="<?= (int)$tt['id'] ?>" <?= (string)($oldTreatmentIds[0] ?? '') === (string)$tt['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tt['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
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

            <div class="row">
                <div style="margin-right: 24px;">
                    <label>Estado</label>
                    <select name="treatment_status">
                        <option value="concluido" <?= $oldValue('treatment_status', 'concluido') === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                        <option value="em_curso" <?= $oldValue('treatment_status') === 'em_curso' ? 'selected' : '' ?>>Em curso</option>
                    </select>
                </div>
            </div>

            <label>Notas do tratamento (opcional)</label>
            <textarea name="treatment_notes" placeholder="Descreva o tratamento efetuado. Evite dados pessoais desnecessários. Se incluir 'Enviado para hospital', este texto poderá aparecer no termo de seguro."><?= htmlspecialchars($oldValue('treatment_notes')) ?></textarea>

            <div class="form-check" style="margin-top:1rem;">
                <input type="checkbox" id="hospital_transfer" name="hospital_transfer" value="1" <?= $oldChecked('hospital_transfer') ? 'checked' : '' ?>>
                <label for="hospital_transfer" style="cursor:pointer;">Enviado para o hospital</label>
            </div>
            <div class="small">Assinale para preencher os restantes dados necessários ao envio para o hospital.</div>

            <!-- Campos extra controlados pela checkbox de envio para o hospital -->
            <div id="patient-block" style="display:none;">
                <strong>Restantes dados do utente (para envio ao hospital)</strong>

                <div class="row">
                    <div style="margin-right: 24px; max-width: 400px;">
                        <label>Nacionalidade</label>
                        <input type="text" name="patient_nationality" value="<?= htmlspecialchars($oldValue('patient_nationality')) ?>">
                    </div>
                </div>

                <div class="row address-row">
                    <div style="margin-right: 24px;">
                        <label class="required">Morada</label>
                        <input type="text" name="patient_address" id="patient_address" value="<?= htmlspecialchars($oldValue('patient_address')) ?>">
                    </div>
             
                    <div style="margin-right: 24px;">
                        <label class="required">Código Postal</label>
                        <input type="text" name="patient_postal_code" id="patient_postal_code" value="<?= htmlspecialchars($oldValue('patient_postal_code')) ?>">
                    </div>
                    
                    <div style="margin-right: 24px;">
                        <label class="required">Cidade</label>
                        <input type="text" name="patient_city" id="patient_city" value="<?= htmlspecialchars($oldValue('patient_city')) ?>">
                    </div>

                    <div style="margin-right: 24px;">
                        <label class="required">Telefone</label>
                        <input type="text" name="patient_phone" id="patient_phone" placeholder="+351 912 345 678" value="<?= htmlspecialchars($oldValue('patient_phone')) ?>">
                    </div>
                </div>

                <div class="row">
                    <div style="margin-right: 24px;">
                        <label>Tipo de Identificação</label>
                        <select name="patient_id_type" id="patient_id_type">
                            <option value="" <?= $oldValue('patient_id_type') === '' ? 'selected' : '' ?>>-- Selecionar --</option>
                            <option value="CC" <?= $oldValue('patient_id_type') === 'CC' ? 'selected' : '' ?>>Cartão de Cidadão (CC)</option>
                            <option value="Passaporte" <?= $oldValue('patient_id_type') === 'Passaporte' ? 'selected' : '' ?>>Passaporte</option>
                        </select>
                    </div>
                </div>

                <div class="row small-field">
                    <div style="margin-right: 24px;">
                        <label>Número de Identificação</label>
                        <input type="text" name="patient_id_number" id="patient_id_number" placeholder="Número do CC ou do Passaporte" value="<?= htmlspecialchars($oldValue('patient_id_number')) ?>">
                    </div>
                </div>

                <div class="form-check" style="margin-top:1rem;">
                    <input
                        type="checkbox"
                        id="patient_refused_hospital"
                        name="patient_refused_hospital"
                        value="1"
                        <?= $oldChecked('patient_refused_hospital') ? 'checked' : '' ?>
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

        </div>

        <button type="submit">Guardar Ocorrência</button>
    </form>

    <script>
        const form = document.getElementById('incidents-form');
        const toggleTreatment = document.getElementById('toggle-treatment');
        const treatmentBlock  = document.getElementById('treatment-block');
        const treatmentList = document.getElementById('treatment-list');
        const addTreatmentButton = document.getElementById('add-treatment');
        const selectionError = document.getElementById('treatment-selection-error');
        const patientBlock = document.getElementById('patient-block');
        const hospitalTransfer = document.getElementById('hospital_transfer');
        const birthDateValidators = [];

        function wireDatalist(inputId, datalistId, hiddenId) {
            const input = document.getElementById(inputId);
            const datalist = document.getElementById(datalistId);
            const hidden = document.getElementById(hiddenId);

            function buildMap() {
                const map = new Map();
                datalist.querySelectorAll('option').forEach((option) => {
                    const value = option.value ? option.value.trim() : '';
                    const id = option.getAttribute('data-id');
                    if (value !== '' && id) {
                        map.set(value, id);
                    }
                });
                return map;
            }

            let map = buildMap();

            input.addEventListener('input', () => {
                const value = input.value.trim();
                hidden.value = map.has(value) ? map.get(value) : '';
            });

            const observer = new MutationObserver(() => {
                map = buildMap();
            });
            observer.observe(datalist, { childList: true, subtree: true });
        }

        wireDatalist('incident_type_input', 'incident-types-list', 'incident_type_id');
        wireDatalist('location_input', 'locations-list', 'location_id');

        function wireBirthDateField(inputId) {
            const input = document.getElementById(inputId);
            if (!input) {
                return null;
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

            return {
                input,
                validate,
                isWithinBounds,
            };
        }

        const patientDobValidator = wireBirthDateField('patient_dob');
        if (patientDobValidator) {
            birthDateValidators.push(patientDobValidator);
        }

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

        function hasSelectedTreatments() {
            return getTreatmentEntries().some((entry) => {
                const select = entry.querySelector('select[data-treatment-select]');
                return Number(select.value) > 0;
            });
        }

        function updateSelectionValidity() {
            const mustValidate = toggleTreatment.checked;
            const hasSelection = hasSelectedTreatments();
            const firstSelect = treatmentList.querySelector('select[data-treatment-select]');

            if (firstSelect) {
                firstSelect.setCustomValidity(!mustValidate || hasSelection ? '' : 'Selecione pelo menos um tratamento.');
            }

            selectionError.style.display = mustValidate && !hasSelection ? 'block' : 'none';
        }

        function togglePatientBlock() {
            patientBlock.style.display =
                toggleTreatment.checked && hospitalTransfer.checked ? 'block' : 'none';
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
                    <select name="treatment_type_id[]" data-treatment-select>
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

        toggleTreatment.addEventListener('change', () => {
            treatmentBlock.style.display = toggleTreatment.checked ? 'block' : 'none';
            updateSelectionValidity();
            togglePatientBlock();
        });

        hospitalTransfer.addEventListener('change', togglePatientBlock);

        addTreatmentButton.addEventListener('click', () => {
            createTreatmentEntry();
        });

        form.addEventListener('submit', (event) => {
            for (const validator of birthDateValidators) {
                validator.validate();

                if (validator.input.value !== '' && !validator.isWithinBounds(validator.input.value)) {
                    event.preventDefault();
                    validator.input.reportValidity();
                    validator.input.focus();
                    return;
                }
            }

            updateSelectionValidity();

            if (toggleTreatment.checked && !hasSelectedTreatments()) {
                event.preventDefault();
                const firstSelect = treatmentList.querySelector('select[data-treatment-select]');
                if (firstSelect) {
                    firstSelect.reportValidity();
                }
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            getTreatmentEntries().forEach((entry) => {
                wireTreatmentEntry(entry);
            });
            syncTreatmentLabels();
            refreshTreatmentChoices();
            treatmentBlock.style.display = toggleTreatment.checked ? 'block' : 'none';
            updateSelectionValidity();
            togglePatientBlock();
        });
    </script>
        
</main>
</body>
</html>
