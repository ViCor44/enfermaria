<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Novo Registo Interno</title>
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
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        background:
            linear-gradient(135deg, rgba(47, 126, 230, 0.045), transparent 42%),
            var(--page-bg);
        color: var(--ink);
        font-family: "Segoe UI", Tahoma, sans-serif;
    }

    .record-page {
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

    .record-form {
        display: grid;
        grid-template-columns: minmax(260px, 0.85fr) minmax(360px, 1.4fr) minmax(210px, 0.65fr);
        grid-template-areas:
            "patient body sidebar"
            "record body sidebar";
        gap: 14px;
        align-items: start;
    }

    .patient-panel { grid-area: patient; }
    .record-panel { grid-area: record; }
    .body-panel {
        grid-area: body;
        align-self: stretch;
    }
    .form-sidebar {
        grid-area: sidebar;
        align-self: stretch;
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

    label.required::after {
        content: " *";
        color: var(--danger);
    }

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

    textarea {
        min-height: 132px;
        resize: vertical;
    }

    .field-help, .character-count {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 0.76rem;
        line-height: 1.4;
    }

    .character-count { text-align: right; }

    .employee-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        margin: 4px 0 16px;
        padding: 10px 12px;
        border: 1px solid var(--line);
        border-radius: 7px;
        background: #f7fafe;
    }

    .employee-toggle input {
        width: 18px;
        min-height: 18px;
        height: 18px;
        margin: 0;
        accent-color: var(--blue);
    }

    .employee-toggle label {
        margin: 0;
        cursor: pointer;
    }

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

    .mark-count {
        color: var(--muted);
        font-size: 0.8rem;
        white-space: nowrap;
    }

    .clear-marks {
        min-height: 34px;
        padding: 0 11px;
        border: 1px solid #cfdae8;
        border-radius: 6px;
        background: #fff;
        color: #405571;
        font: inherit;
        font-size: 0.8rem;
        font-weight: 650;
        cursor: pointer;
    }

    .clear-marks:hover { background: #f2f6fa; }
    .clear-marks:disabled { cursor: not-allowed; opacity: 0.5; }

    .body-instruction {
        margin: 0 0 14px 35px;
        color: var(--muted);
        font-size: 0.8rem;
    }

    .body-maps {
        display: grid;
        grid-template-columns: repeat(2, minmax(145px, 1fr));
        gap: 8px;
        max-width: 620px;
        margin: 0 auto;
    }

    .body-map { min-width: 0; }

    .body-canvas {
        position: relative;
        display: block;
        width: min(100%, 200px);
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

    .body-canvas:hover { background: #f7fbff; border-color: #dce9f5; }
    .body-canvas:focus-visible {
        outline: 3px solid rgba(47, 126, 230, 0.25);
        outline-offset: 2px;
    }

    .body-canvas img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        pointer-events: none;
    }

    .body-marker {
        position: absolute;
        width: 18px;
        height: 18px;
        padding: 0;
        border: 3px solid #fff;
        border-radius: 50%;
        background: var(--mark-color);
        box-shadow: 0 0 0 7px var(--mark-glow), 0 2px 7px rgba(31, 45, 61, 0.25);
        transform: translate(-50%, -50%);
        cursor: pointer;
    }

    .mark-contusion { --mark-color: #e5484d; --mark-glow: rgba(229, 72, 77, 0.17); }
    .mark-wound { --mark-color: #d4145a; --mark-glow: rgba(212, 20, 90, 0.17); }
    .mark-burn { --mark-color: #ff8a00; --mark-glow: rgba(255, 138, 0, 0.18); }
    .mark-pain { --mark-color: #6c5ce7; --mark-glow: rgba(108, 92, 231, 0.17); }
    .mark-other { --mark-color: #13b8a6; --mark-glow: rgba(19, 184, 166, 0.17); }

    .body-map-label {
        display: block;
        margin-top: 8px;
        color: #2d405d;
        font-size: 0.84rem;
        font-weight: 700;
        text-align: center;
    }

    .legend-panel {
        padding: 18px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 7px 22px rgba(26, 54, 93, 0.045);
    }

    .legend-panel .panel-title { margin-bottom: 12px; }

    .legend-item {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 11px;
        min-height: 38px;
        padding: 5px 7px;
        border: 1px solid transparent;
        border-radius: 6px;
        background: transparent;
        color: #39506d;
        font-family: inherit;
        font-size: 0.84rem;
        text-align: left;
        cursor: pointer;
    }

    .legend-item:hover { background: #f5f8fc; }
    .legend-item.active {
        border-color: #b9d5f4;
        background: #eef6ff;
        color: #174f8c;
        font-weight: 700;
    }

    .legend-swatch {
        width: 16px;
        height: 16px;
        border: 3px solid #fff;
        border-radius: 50%;
        background: var(--mark-color);
        box-shadow: 0 0 0 5px var(--mark-glow);
    }

    .form-sidebar {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 14px;
    }

    .help-panel {
        padding: 18px;
        border: 1px solid #bcdcf8;
        border-radius: 8px;
        background: #edf7ff;
        color: #39546f;
    }

    .help-panel .panel-title {
        margin-bottom: 12px;
        color: #2166ad;
    }

    .help-panel ol {
        margin: 0;
        padding-left: 22px;
        font-size: 0.82rem;
        line-height: 1.65;
    }

    .privacy-note {
        padding: 14px 16px;
        border-left: 3px solid #e3a623;
        background: #fffaf0;
        color: #6d5726;
        font-size: 0.8rem;
        line-height: 1.45;
    }

    .submit-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 48px;
        margin-top: 0;
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

    .submit-button:hover {
        background: var(--green-dark);
        transform: translateY(-1px);
    }

    @media (max-width: 900px) {
        .record-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-areas:
                "patient record"
                "body body"
                "sidebar sidebar";
        }
        .submit-button { margin-top: 0; }
    }

    @media (max-width: 720px) {
        .record-page {
            width: min(100% - 24px, 1480px);
            padding-top: 20px;
        }
        .page-heading { align-items: stretch; flex-direction: column; }
        .back-link { align-self: flex-start; }
        .record-form {
            grid-template-columns: 1fr;
            grid-template-areas: "patient" "record" "body" "sidebar";
        }
        .field-grid { grid-template-columns: 1fr; }
        .form-panel { padding: 17px; }
        .body-heading { align-items: stretch; flex-direction: column; }
        .body-actions { justify-content: space-between; }
        .body-instruction { margin-left: 0; }
        .body-maps { grid-template-columns: repeat(2, minmax(130px, 1fr)); }
        .body-canvas { width: min(100%, 150px); }
    }
</style>
</head>

<body>

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="record-page">
    <div class="page-heading">
        <div>
            <h1>Registo interno</h1>
            <p>Registe uma situação menor e os cuidados prestados ao utente.</p>
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

    <form class="record-form" method="post" action="<?= htmlspecialchars($baseUrl) ?>?route=internal_store">
        <section class="form-panel patient-panel" aria-labelledby="patient-title">
            <h2 class="panel-title" id="patient-title"><span class="panel-icon" aria-hidden="true">U</span>Dados do utente</h2>

            <div class="field-grid">
                <div class="field">
                    <label class="required" for="first_name">Primeiro nome</label>
                    <input type="text" id="first_name" name="first_name" required maxlength="100" autocomplete="given-name" placeholder="Primeiro nome">
                </div>
                <div class="field">
                    <label class="required" for="last_name">Último nome</label>
                    <input type="text" id="last_name" name="last_name" required maxlength="100" autocomplete="family-name" placeholder="Último nome">
                </div>
            </div>

            <div class="field-grid">
                <div class="field">
                    <label for="patient_age">Idade</label>
                    <input type="number" id="patient_age" name="patient_age" min="0" max="120" inputmode="numeric" placeholder="Ex.: 34">
                </div>
                <div class="field">
                    <label for="patient_gender">Género</label>
                    <select id="patient_gender" name="patient_gender">
                        <option value="">Não especificar</option>
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
            </div>

            <div class="employee-toggle">
                <input type="checkbox" id="is_employee" name="is_employee" value="1">
                <label for="is_employee">O utente é colaborador</label>
            </div>
        </section>

        <section class="form-panel record-panel" aria-labelledby="record-title">
            <h2 class="panel-title" id="record-title"><span class="panel-icon" aria-hidden="true">R</span>Dados do registo</h2>

            <div class="field-grid">
                <div class="field">
                    <label class="required" for="date">Data</label>
                    <input type="date" id="date" name="date" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="field">
                    <label class="required" for="time">Hora</label>
                    <input type="time" id="time" name="time" required value="<?= date('H:i') ?>">
                </div>
            </div>

            <div class="field">
                <label class="required" for="location_input">Local / Área</label>
                <input
                    list="locations-list"
                    name="location_input"
                    id="location_input"
                    placeholder="Escreva ou escolha um local"
                    autocomplete="off"
                    required
                >
                <p class="field-help">Pode selecionar um local existente ou escrever um novo.</p>
            </div>

            <datalist id="locations-list">
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= htmlspecialchars($loc['name']) ?>" data-id="<?= (int)$loc['id'] ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <input type="hidden" name="location_id" id="location_id">

            <div class="field">
                <label class="required" for="treatment">Tratamento prestado</label>
                <select name="treatment" id="treatment" required>
                    <option value="">Selecionar tratamento</option>
                    <?php foreach ($treatmentTypes as $tt): ?>
                        <option value="<?= htmlspecialchars($tt['name']) ?>"><?= htmlspecialchars($tt['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label class="required" for="description">Descrição / Observações</label>
                <textarea
                    id="description"
                    name="description"
                    maxlength="500"
                    required
                    placeholder="Descreva a situação interna e os cuidados prestados."
                ></textarea>
                <p class="character-count"><span id="description-count">0</span>/500</p>
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
            <p class="body-instruction">Clique na zona afetada. Clique novamente numa marcação para a remover.</p>
            <input type="hidden" id="body_marks" name="body_marks" value="[]">

            <div class="body-maps">
                <div class="body-map">
                    <button class="body-canvas" type="button" data-view="front" aria-label="Marcar zona afetada na frente do corpo">
                        <img src="/enfermaria/public/assets/img/body-front.svg" alt="Vista frontal do corpo humano" data-body-image="front">
                    </button>
                    <span class="body-map-label">Frente</span>
                </div>

                <div class="body-map">
                    <button class="body-canvas" type="button" data-view="back" aria-label="Marcar zona afetada nas costas do corpo">
                        <img src="/enfermaria/public/assets/img/body-back.svg" alt="Vista posterior do corpo humano" data-body-image="back">
                    </button>
                    <span class="body-map-label">Costas</span>
                </div>
            </div>
        </section>

        <aside class="form-sidebar" aria-label="Ajuda e ações">
            <div class="legend-panel">
                <h2 class="panel-title"><span class="panel-icon" aria-hidden="true">i</span>Legenda</h2>
                <button class="legend-item mark-contusion active" type="button" data-mark-type="contusion" aria-pressed="true"><span class="legend-swatch" aria-hidden="true"></span><span>Hematoma / Contusão</span></button>
                <button class="legend-item mark-wound" type="button" data-mark-type="wound" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span><span>Ferida</span></button>
                <button class="legend-item mark-burn" type="button" data-mark-type="burn" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span><span>Queimadura</span></button>
                <button class="legend-item mark-pain" type="button" data-mark-type="pain" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span><span>Dor</span></button>
                <button class="legend-item mark-other" type="button" data-mark-type="other" aria-pressed="false"><span class="legend-swatch" aria-hidden="true"></span><span>Outra ocorrência</span></button>
            </div>

            <div class="help-panel">
                <h2 class="panel-title"><span class="panel-icon" aria-hidden="true">i</span>Como utilizar</h2>
                <ol>
                    <li>Identifique o utente.</li>
                    <li>Indique a data, hora e local.</li>
                    <li>Escolha o tipo de ocorrência na legenda.</li>
                    <li>Marque no corpo a zona afetada.</li>
                    <li>Selecione o tratamento prestado.</li>
                    <li>Descreva a situação de forma sucinta.</li>
                    <li>Guarde o registo interno.</li>
                </ol>
            </div>

            <div class="privacy-note">
                Evite incluir dados pessoais desnecessários na descrição.
            </div>

            <button class="submit-button" type="submit">Guardar registo interno</button>
        </aside>
    </form>

<script>
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
    wireDatalist('location_input', 'locations-list', 'location_id');

    const description = document.getElementById('description');
    const descriptionCount = document.getElementById('description-count');
    description.addEventListener('input', () => {
        descriptionCount.textContent = String(description.value.length);
    });

    const bodyMarksInput = document.getElementById('body_marks');
    const patientGender = document.getElementById('patient_gender');
    const markCount = document.getElementById('mark-count');
    const clearMarks = document.getElementById('clear-marks');
    const bodyCanvases = document.querySelectorAll('.body-canvas');
    const legendItems = document.querySelectorAll('.legend-item[data-mark-type]');
    const markTypes = {
        contusion: 'Hematoma / Contusão',
        wound: 'Ferida',
        burn: 'Queimadura',
        pain: 'Dor',
        other: 'Outra ocorrência'
    };
    let selectedMarkType = 'contusion';
    let bodyMarks = [];

    const bodyImagePaths = {
        front: '/enfermaria/public/assets/img/body-front.svg',
        back: '/enfermaria/public/assets/img/body-back.svg',
        frontFemale: '/enfermaria/public/assets/img/body-front-female.svg',
        backFemale: '/enfermaria/public/assets/img/body-back-female.svg'
    };

    function updateBodyImages() {
        const female = patientGender.value === 'F';
        document.querySelectorAll('[data-body-image]').forEach(image => {
            const view = image.dataset.bodyImage;
            const pathKey = female ? `${view}Female` : view;
            image.src = bodyImagePaths[pathKey];
        });
    }

    document.querySelectorAll('[data-body-image]').forEach(image => {
        image.addEventListener('error', () => {
            image.src = bodyImagePaths[image.dataset.bodyImage];
        });
    });
    patientGender.addEventListener('change', updateBodyImages);
    updateBodyImages();

    function renderBodyMarks() {
        document.querySelectorAll('.body-marker').forEach(marker => marker.remove());

        bodyMarks.forEach((mark, index) => {
            const canvas = document.querySelector(`.body-canvas[data-view="${mark.view}"]`);
            if (!canvas) return;

            const marker = document.createElement('span');
            const markType = markTypes[mark.type] ? mark.type : 'other';
            marker.className = `body-marker mark-${markType}`;
            marker.dataset.index = String(index);
            marker.style.left = `${mark.x}%`;
            marker.style.top = `${mark.y}%`;
            marker.title = `${markTypes[markType]} — remover marcação`;
            marker.setAttribute('aria-label', `${markTypes[markType]} — remover marcação`);
            canvas.appendChild(marker);
        });

        bodyMarksInput.value = JSON.stringify(bodyMarks);
        markCount.textContent = `${bodyMarks.length} ${bodyMarks.length === 1 ? 'marcação' : 'marcações'}`;
        clearMarks.disabled = bodyMarks.length === 0;
    }

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

    clearMarks.addEventListener('click', () => {
        bodyMarks = [];
        renderBodyMarks();
    });

    legendItems.forEach(item => {
        item.addEventListener('click', () => {
            selectedMarkType = item.dataset.markType;
            legendItems.forEach(option => {
                const isActive = option === item;
                option.classList.toggle('active', isActive);
                option.setAttribute('aria-pressed', String(isActive));
            });
        });
    });
</script>

</main>

</body>
</html>
