<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Registar Acidente</title>
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
    input, select, textarea {
        width:100%; padding:.6rem; margin-top:.3rem; border-radius:6px; border:1px solid #ccc;
    }
    textarea { min-height:90px; resize:vertical; }
    .row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.2rem;   /* <-- distância vertical entre as linhas */
    }

    .row > div {
        flex: 1;
    }

    button {
        margin-top:1.5rem; padding:.8rem 1.4rem; border:none; border-radius:8px;
        background:#1f6feb; color:#fff; font-size:1rem; cursor:pointer;
    }
    button:hover { background:#1557c0; }
    .flash-error { background:#ffe0e0; color:#900; padding:.7rem; border-radius:6px; margin-bottom:1rem; }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Registar novo Acidente</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash-error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $baseUrl ?>?route=incidents_store">
        <div class="row">
            <!-- TIPO DE Acidente (input com datalist) -->
            <div style="margin-right: 24px;">
                <label>Tipo de Acidente *</label>
                <input
                    list="incident-types-list"
                    name="incident_type_input"
                    id="incident_type_input"
                    placeholder="Escreva ou escolha..."
                    required
                    autocomplete="off"
                >
                <datalist id="incident-types-list">
                    <?php foreach ($types as $t): ?>
                        <option value="<?= htmlspecialchars($t['name']) ?>" data-id="<?= (int)$t['id'] ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <input type="hidden" name="incident_type_id" id="incident_type_id" value="">
                <div class="small">Pode escrever novo tipo de acidente ou escolher da lista — se não existir será criado.</div>

            </div>
            
            <div style="margin-right: 24px;">
                <label>Local / Atração *</label>

                <!-- input com datalist — o utilizador pode escrever ou escolher -->
                <input
                    list="locations-list"
                    name="location_input"
                    id="location_input"
                    placeholder="Escreva ou escolha..."
                    required
                    autocomplete="off"
                >

                <datalist id="locations-list">
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= htmlspecialchars($loc['name']) ?>" data-id="<?= (int)$loc['id'] ?>"></option>
                    <?php endforeach; ?>
                </datalist>

                <!-- hidden com o id (preenchido pelo JS se escolher uma sugestão) -->
                <input type="hidden" name="location_id" id="location_id" value="">

                <div class="small">Pode escrever o nome do local ou escolher da lista — se não existir será criado.</div>
            </div>

        </div>
        

        <!-- LINHA DATA / HORA -->
        <div class="row" style="margin-bottom: 24px;">
            <div style="margin-right: 24px;">
                <label>Data *</label>
                <input type="date" name="date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div style="margin-right: 24px;">
                <label>Hora *</label>
                <input type="time" name="time" required value="<?= date('H:i') ?>">
            </div>
        </div>

        <!-- LINHA IDADE / GÉNERO -->
        <div class="row">
            <div style="margin-right: 24px;">
                <label>Idade do utente (opcional)</label>
                <input type="number" name="patient_age" min="0" max="120">
            </div>
            <div style="margin-right: 24px;">
                <label>Género (opcional)</label>
                <select name="patient_gender">
                    <option value="">-- Não especificar --</option>
                    <option value="M">Masculino</option>
                    <option value="F">Feminino</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
        </div>

        <label>Descrição / Observações (opcional)</label>
        <textarea name="description" placeholder="Descrição sucinta do Acidente, sem dados de identificação desnecessários."></textarea>

        <div class="section-title">
            <div class="form-check" style="display:flex; align-items:center; gap:10px; margin: 10px 0 20px;">
                <input type="checkbox" id="toggle-treatment" name="add_treatment" style="width:18px; height:18px;">
                <label for="add_treatment" style="cursor:pointer; font-size:16px;">
                    Adicionar tratamento agora
                </label>
            </div>
        </div>

        <div id="treatment-block" style="display:none;">
            <div class="row">
                <!-- TIPO DE TRATAMENTO (no bloco de tratamento) -->
                <div style="margin-right: 24px;">
                    <label>Tipo de tratamento</label>
                    <input
                        list="treatment-types-list"
                        name="treatment_type_input"
                        id="treatment_type_input"
                        placeholder="Escreva ou escolha..."
                        autocomplete="off"
                    >
                    <datalist id="treatment-types-list">
                        <?php foreach ($treatmentTypes as $tt): ?>
                            <option value="<?= htmlspecialchars($tt['name']) ?>" data-id="<?= (int)$tt['id'] ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <input type="hidden" name="treatment_type_id" id="treatment_type_id" value="">
                </div>
                <div style="max-width:180px;">
                    <label>Estado</label>
                    <select name="treatment_status">
                        <option value="em_curso">Em curso</option>
                        <option value="concluido">Concluído</option>
                    </select>
                </div>
            </div>

            <label>Notas do tratamento (opcional)</label>
            <textarea name="treatment_notes" placeholder="Descreva o tratamento efetuado. Evite dados pessoais desnecessários."></textarea>

            <!-- Campos extra se for 'Enviado para hospital' -->
            <div id="patient-block" style="display:none; margin-top:1rem; padding:1rem; border-radius:8px; background:#fff7e6;">
                <strong>Dados do utente (para envio ao hospital)</strong>

                <div class="row">
                    <div style="margin-right: 24px;">
                        <label>Nome completo do utente *</label>
                        <input type="text" name="patient_name" id="patient_name">
                    </div>
                    <div style="margin-right: 24px;">
                        <label>Nacionalidade (opcional)</label>
                        <input type="text" name="patient_nationality">
                    </div>
                </div>

                <div class="row" style="margin-top:1rem;">
                    <div style="margin-right: 24px;">
                        <label>Morada *</label>
                        <input type="text" name="patient_address" id="patient_address">
                    </div>
                    <div style="margin-right: 24px;">
                        <label>Telefone *</label>
                        <input type="text" name="patient_phone" id="patient_phone" placeholder="+351 912 345 678">
                    </div>
                </div>

                <div class="row" style="margin-top:1rem;">
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

                <div class="row" style="margin-top:1rem;">
                    <div style="margin-right: 24px;">
                        <label>Número de Identificação</label>
                        <input type="text" name="patient_id_number" id="patient_id_number" placeholder="Número do CC ou do Passaporte">
                    </div>
                </div>

                <div class="small">
                    Estes dados só serão visíveis para o administrador e para o enfermeiro responsável.
                </div>
            </div>

        </div>

        <!-- --- Fim: bloco de Tratamento Aplicado --- -->

        <button type="submit" style="margin-top:1.5rem;">Guardar Acidente</button>
    </form>

    <script>
        const toggleTreatment = document.getElementById('toggle-treatment');
        const treatmentBlock  = document.getElementById('treatment-block');
        const treatmentType   = document.getElementById('treatment_type_id');
        const patientBlock    = document.getElementById('patient-block');

        function wireDatalist(inputId, datalistId, hiddenId) {
            const input = document.getElementById(inputId);
            const datalist = document.getElementById(datalistId);
            const hidden = document.getElementById(hiddenId);

            function buildMap() {
                const map = new Map();
                datalist.querySelectorAll('option').forEach(opt => {
                    const v = opt.value?.trim();
                    const id = opt.getAttribute('data-id');
                    if (v) map.set(v, id);
                });
                return map;
            }

            let map = buildMap();

            input.addEventListener('input', () => {
                const v = input.value.trim();
                if (map.has(v)) {
                    hidden.value = map.get(v);
                } else {
                    hidden.value = '';
                }
            });

            const obs = new MutationObserver(() => { map = buildMap(); });
            obs.observe(datalist, { childList: true, subtree: true });
        }
        wireDatalist('incident_type_input', 'incident-types-list', 'incident_type_id');
        wireDatalist('location_input', 'locations-list', 'location_id');
        wireDatalist('treatment_type_input', 'treatment-types-list', 'treatment_type_id');
    </script>
    <script>
// ID do tipo "Enviado para hospital" vindo do PHP
const hospitalTypeId = <?= isset($hospitalTreatmentTypeId) && $hospitalTreatmentTypeId ? (int)$hospitalTreatmentTypeId : 'null' ?>;
const hospitalTypeName = 'Enviado para hospital';

// estes campos podem existir em versões diferentes da view
const treatmentInput  = document.getElementById('treatment_type_input'); // input (datalist)
const treatmentHidden = document.getElementById('treatment_type_id');    // hidden id (preenchido pelo wireDatalist)
const treatmentSelect = document.getElementById('treatment_type_id_select'); // caso tenhas um select com outro id

// função para obter o id actual do tipo de tratamento (se houver)
function getSelectedTreatmentId() {
    // 1) se existir um hidden com valor -> usa-o
    if (treatmentHidden && treatmentHidden.value && treatmentHidden.value.trim() !== '') {
        return treatmentHidden.value.trim();
    }

    // 2) se existir um select (id distinto) e tem valor -> usa-o
    if (treatmentSelect && treatmentSelect.value) {
        return treatmentSelect.value;
    }

    // 3) se existir o input (datalist) tenta encontrar a option correspondente com data-id
    if (treatmentInput) {
        const val = treatmentInput.value.trim();
        if (val === '') return null;

        // procurar option no datalist com value igual e data-id
        const datalistId = treatmentInput.getAttribute('list');
        if (datalistId) {
            const dl = document.getElementById(datalistId);
            if (dl) {
                const opts = dl.querySelectorAll('option');
                for (const opt of opts) {
                    if (opt.value && opt.value.trim() === val) {
                        const id = opt.getAttribute('data-id');
                        if (id) return id;
                    }
                }
            }
        }

        // 4) fallback: se o texto coincidir com o nome conhecido do tipo hospital
        if (val.toLowerCase() === hospitalTypeName.toLowerCase()) {
            return 'name-match'; // especial — sinaliza match por nome
        }
    }

    return null;
}

// decide mostrar/esconder patientBlock
function updatePatientBlockVisibility() {
    // se o bloco de tratamento estiver escondido, esconder patientBlock
    if (!toggleTreatment || !toggleTreatment.checked) {
        patientBlock.style.display = 'none';
        return;
    }

    const selId = getSelectedTreatmentId();

    if (!selId) {
        patientBlock.style.display = 'none';
        return;
    }

    // se temos hospitalTypeId numérico compare, ou se foi name-match
    if ((hospitalTypeId !== null && hospitalTypeId !== 'null' && String(selId) === String(hospitalTypeId)) ||
        selId === 'name-match') {
        patientBlock.style.display = 'block';
    } else {
        patientBlock.style.display = 'none';
    }
}

// listeners
if (toggleTreatment) {
    toggleTreatment.addEventListener('change', () => {
        treatmentBlock.style.display = toggleTreatment.checked ? 'block' : 'none';
        updatePatientBlockVisibility();
    });
}

// se tens o input do datalist
if (treatmentInput) {
    treatmentInput.addEventListener('input', () => {
        // se wireDatalist preenche o hidden, o getSelectedTreatmentId já o detecta
        updatePatientBlockVisibility();
    });
}

// se tens o hidden (wireDatalist atualiza-o), observa mudanças no hidden
if (treatmentHidden) {
    const obs = new MutationObserver(() => updatePatientBlockVisibility());
    obs.observe(treatmentHidden, { attributes: true, attributeFilter: ['value'] });
    // também chama uma vez no load
}

// se tens um select (caso legazy), ouve change
if (treatmentSelect) {
    treatmentSelect.addEventListener('change', updatePatientBlockVisibility);
}

// chamar logo para reflectir estado inicial (útil quando a página é re-carregada com valores)
document.addEventListener('DOMContentLoaded', updatePatientBlockVisibility);
updatePatientBlockVisibility();
</script>
        
</main>
</body>
</html>
