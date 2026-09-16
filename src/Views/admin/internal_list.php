<?php
$baseUrl = '/enfermaria/public/index.php';

$fromDate   = $_GET['from'] ?? '';
$toDate     = $_GET['to'] ?? '';
$locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Enfermaria · Registos Internos</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
/* Copiado da listagem de ocorrências */

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

.subtitle { 
    font-size: 1rem; 
    color: #777; 
    margin-bottom: 2rem; 
}

.filters {
    background: #fff; 
    padding: 1.5rem; 
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    margin-bottom: 1.5rem;
    text-align: left;
}

.filters form { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 1rem; 
    align-items: flex-end; 
}

.filters label { 
    display: block; 
    font-size: .9rem; 
    font-weight: 600; 
    color: #555; 
    margin-bottom: 0.3rem;
}

.filters input, .filters select {
    padding: 0.6rem 0.8rem; 
    border-radius: 8px; 
    border: 1px solid #ddd; 
    min-width: 180px;
}

.filters button,
.filters a.btn-reset {
    padding: 0.6rem 1.2rem; 
    border-radius: 8px; 
    font-size: .95rem; 
    border: none;
    cursor: pointer;
}

.filters button {
    background: #1f6feb; 
    color: #fff; 
}

.filters a.btn-reset {
    border: 1px solid #ddd;
    color: #555;
    text-decoration: none;
    background: #f8f9fb;
}

table {
    width: 100%; 
    border-collapse: collapse; 
    background: #fff;
    border-radius: 12px; 
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

th, td { 
    padding: 0.8rem 1rem; 
    border-bottom: 1px solid #eee; 
    font-size: .95rem; 
    text-align: left; 
}

th { 
    background: #f0f4ff; 
    font-weight: 600;
    color: #555;
}

.separator {
    border: none;
    border-top: 1px solid #ddd;
    margin: 2rem 0;
}

tbody tr.record-row {
    cursor: pointer;
}

tbody tr.record-row:hover {
    background: #f8fbff;
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 9999;
}

.modal-backdrop.open {
    display: flex;
}

.modal-card {
    width: min(720px, 100%);
    max-height: 90vh;
    overflow: auto;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.25);
    text-align: left;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #edf0f5;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.1rem;
    color: #1f2937;
}

.modal-close {
    border: none;
    background: #eef2ff;
    color: #334155;
    border-radius: 8px;
    padding: 0.4rem 0.65rem;
    cursor: pointer;
    font-size: 1rem;
}

.modal-body {
    padding: 1rem 1.25rem 1.25rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.8rem 1rem;
    margin-bottom: 1rem;
}

.detail-item {
    background: #f8fafc;
    border: 1px solid #e6edf5;
    border-radius: 10px;
    padding: 0.65rem 0.75rem;
}

.detail-item small {
    display: block;
    color: #64748b;
    margin-bottom: 0.15rem;
}

.detail-item strong {
    color: #1e293b;
    font-weight: 600;
}

.detail-description {
    background: #fff;
    border: 1px solid #e6edf5;
    border-radius: 10px;
    padding: 0.85rem;
}

.detail-description small {
    display: block;
    color: #64748b;
    margin-bottom: 0.4rem;
}

#modal_description {
    margin: 0;
    color: #334155;
    white-space: pre-wrap;
}

.modal-body-location {
    display: none;
    margin-top: 1rem;
    padding: 0.85rem;
    border: 1px solid #e6edf5;
    border-radius: 10px;
}

.modal-body-location.visible { display: block; }

.modal-body-location > small {
    display: block;
    margin-bottom: 0.7rem;
    color: #64748b;
}

.modal-body-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem 1rem;
    margin-bottom: 1rem;
    padding: 0.7rem 0.8rem;
    border-radius: 8px;
    background: #f8fafc;
}

.modal-body-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: #475569;
    font-size: 0.78rem;
}

.modal-body-legend-swatch {
    width: 12px;
    height: 12px;
    border: 2px solid #fff;
    border-radius: 50%;
    background: var(--mark-color);
    box-shadow: 0 0 0 3px var(--mark-glow);
}

.modal-body-maps {
    display: grid;
    grid-template-columns: repeat(2, minmax(120px, 1fr));
    gap: 1rem;
    max-width: 390px;
    margin: 0 auto;
}

.modal-body-map { text-align: center; }

.modal-body-canvas {
    position: relative;
    aspect-ratio: 1 / 2;
    padding: 5px;
    border: 1px solid #dbe5ef;
    border-radius: 8px;
    background: #f8fbfe;
}

.modal-body-canvas img { width: 100%; height: 100%; object-fit: contain; }

.modal-body-marker {
    position: absolute;
    width: 12px;
    height: 12px;
    border: 2px solid #fff;
    border-radius: 50%;
    background: var(--mark-color);
    box-shadow: 0 0 0 5px var(--mark-glow);
    transform: translate(-50%, -50%);
}

.mark-contusion { --mark-color: #e5484d; --mark-glow: rgba(229, 72, 77, 0.17); }
.mark-wound { --mark-color: #d4145a; --mark-glow: rgba(212, 20, 90, 0.17); }
.mark-burn { --mark-color: #ff8a00; --mark-glow: rgba(255, 138, 0, 0.18); }
.mark-pain { --mark-color: #6c5ce7; --mark-glow: rgba(108, 92, 231, 0.17); }
.mark-insect_bite { --mark-color: #65a30d; --mark-glow: rgba(101, 163, 13, 0.18); }
.mark-epistaxis { --mark-color: #8b1e3f; --mark-glow: rgba(139, 30, 63, 0.18); }
.mark-other { --mark-color: #13b8a6; --mark-glow: rgba(19, 184, 166, 0.17); }

.modal-body-map span {
    display: block;
    margin-top: 0.35rem;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
}
</style>
</head>
<body>

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main>

<h1>Registos Internos</h1>
<p class="subtitle">Situações internas sem classificação como ocorrência.</p>

<hr class="separator">

<div class="filters">
    <form method="get" action="<?= $baseUrl ?>">
        <input type="hidden" name="route" value="admin_internal_records">

        <div>
            <label>Data inicial</label>
            <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>">
        </div>

        <div>
            <label>Data final</label>
            <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>">
        </div>

        <div>
            <label>Local</label>
            <select name="location_id">
                <option value="0">-- Todos --</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= (int)$loc['id'] ?>" <?= $locationId === (int)$loc['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit">Filtrar</button>
            <a href="<?= $baseUrl ?>?route=admin_internal_records" class="btn-reset">Limpar</a>
        </div>
    </form>
</div>

<?php if (empty($records)): ?>
    <p>Não foram encontrados Registos Internos com os critérios selecionados.</p>
<?php else: ?>

<table>
    <thead>
        <tr>
            <th>Episódio</th>
            <th>Primeiro Nome</th>
            <th>Último Nome</th>
            <th>Data / Hora</th>
            <th>Local</th>
            <th>Idade</th>
            <th>Género</th>
            <th>Colaborador</th>
            <th>Tratamento</th>
            <th>Enfermeiro</th>
            <th>Descrição</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($records as $r): ?>
        <tr
            class="record-row"
            role="button"
            tabindex="0"
            aria-label="Abrir detalhes do registo interno <?= (int)$r['id'] ?>"
            data-id="<?= (int)$r['id'] ?>"
            data-first-name="<?= htmlspecialchars((string)($r['first_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>"
            data-last-name="<?= htmlspecialchars((string)($r['last_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>"
            data-occurred-at="<?= htmlspecialchars((string)$r['occurred_at'], ENT_QUOTES, 'UTF-8') ?>"
            data-location="<?= htmlspecialchars((string)($r['location_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>"
            data-patient-age="<?= htmlspecialchars((string)($r['patient_age'] !== null ? (int)$r['patient_age'] : '—'), ENT_QUOTES, 'UTF-8') ?>"
            data-patient-gender="<?= htmlspecialchars((string)($r['patient_gender'] ?: '—'), ENT_QUOTES, 'UTF-8') ?>"
            data-is-employee="<?= !empty($r['is_employee']) ? 'Sim' : 'Não' ?>"
            data-treatment="<?= htmlspecialchars((string)($r['treatment'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>"
            data-nurse-name="<?= htmlspecialchars((string)$r['nurse_name'], ENT_QUOTES, 'UTF-8') ?>"
            data-description="<?= htmlspecialchars((string)($r['description'] ?: 'Sem descrição.'), ENT_QUOTES, 'UTF-8') ?>"
            data-body-marks="<?= htmlspecialchars((string)($r['body_marks'] ?? '[]'), ENT_QUOTES, 'UTF-8') ?>"
        >
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['first_name'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['last_name'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['occurred_at']) ?></td>
            <td><?= htmlspecialchars($r['location_name'] ?? '—') ?></td>
            <td><?= $r['patient_age'] !== null ? (int)$r['patient_age'] : '—' ?></td>
            <td><?= $r['patient_gender'] ?: '—' ?></td>
            <td><?= !empty($r['is_employee']) ? 'Sim' : 'Não' ?></td>
            <td><?= htmlspecialchars($r['treatment'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['nurse_name']) ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($r['description'], 0, 60, '…')) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

</main>

<div class="modal-backdrop" id="recordModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="recordModalTitle">
        <div class="modal-header">
            <h2 id="recordModalTitle">Detalhes do Registo Interno #<span id="modal_id"></span></h2>
            <button type="button" class="modal-close" id="modalCloseBtn" aria-label="Fechar">x</button>
        </div>
        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-item"><small>Primeiro Nome</small><strong id="modal_first_name"></strong></div>
                <div class="detail-item"><small>Último Nome</small><strong id="modal_last_name"></strong></div>
                <div class="detail-item"><small>Data / Hora</small><strong id="modal_occurred_at"></strong></div>
                <div class="detail-item"><small>Local</small><strong id="modal_location"></strong></div>
                <div class="detail-item"><small>Idade</small><strong id="modal_patient_age"></strong></div>
                <div class="detail-item"><small>Género</small><strong id="modal_patient_gender"></strong></div>
                <div class="detail-item"><small>Colaborador</small><strong id="modal_is_employee"></strong></div>
                <div class="detail-item"><small>Tratamento</small><strong id="modal_treatment"></strong></div>
                <div class="detail-item"><small>Enfermeiro</small><strong id="modal_nurse_name"></strong></div>
            </div>

            <div class="detail-description">
                <small>Descrição / Observações</small>
                <p id="modal_description"></p>
            </div>

            <div class="modal-body-location" id="modal_body_location">
                <small>Localização no corpo</small>
                <div class="modal-body-legend" aria-label="Legenda das marcações">
                    <span class="modal-body-legend-item mark-contusion"><i class="modal-body-legend-swatch" aria-hidden="true"></i>Hematoma / Contusão</span>
                    <span class="modal-body-legend-item mark-wound"><i class="modal-body-legend-swatch" aria-hidden="true"></i>Ferida</span>
                    <span class="modal-body-legend-item mark-burn"><i class="modal-body-legend-swatch" aria-hidden="true"></i>Queimadura</span>
                    <span class="modal-body-legend-item mark-pain"><i class="modal-body-legend-swatch" aria-hidden="true"></i>Dor</span>
                    <span class="modal-body-legend-item mark-insect_bite"><i class="modal-body-legend-swatch" aria-hidden="true"></i>Picada de inseto</span>
                    <span class="modal-body-legend-item mark-epistaxis"><i class="modal-body-legend-swatch" aria-hidden="true"></i>Epistaxis</span>
                    <span class="modal-body-legend-item mark-other"><i class="modal-body-legend-swatch" aria-hidden="true"></i>Outra ocorrência</span>
                </div>
                <div class="modal-body-maps">
                    <?php foreach (['front' => 'Frente', 'back' => 'Costas'] as $view => $label): ?>
                        <div class="modal-body-map">
                            <div class="modal-body-canvas" data-view="<?= $view ?>">
                                <img src="/enfermaria/public/assets/img/body-<?= $view ?>.svg" alt="Vista <?= strtolower($label) ?> do corpo humano" data-body-image="<?= $view ?>">
                            </div>
                            <span><?= $label ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('recordModal');
    const closeBtn = document.getElementById('modalCloseBtn');
    const rows = document.querySelectorAll('tr.record-row');
    const bodyLocation = document.getElementById('modal_body_location');
    const bodyImagePaths = {
        front: '/enfermaria/public/assets/img/body-front.svg',
        back: '/enfermaria/public/assets/img/body-back.svg',
        frontFemale: '/enfermaria/public/assets/img/body-front-female.svg',
        backFemale: '/enfermaria/public/assets/img/body-back-female.svg'
    };
    document.querySelectorAll('.modal-body-canvas [data-body-image]').forEach(image => {
        image.addEventListener('error', () => {
            image.src = bodyImagePaths[image.dataset.bodyImage];
        });
    });
    const markTypes = {
        contusion: 'Hematoma / Contusão',
        wound: 'Ferida',
        burn: 'Queimadura',
        pain: 'Dor',
        insect_bite: 'Picada de inseto',
        epistaxis: 'Epistaxis',
        other: 'Outra ocorrência'
    };
    const fields = {
        id: document.getElementById('modal_id'),
        first_name: document.getElementById('modal_first_name'),
        last_name: document.getElementById('modal_last_name'),
        occurred_at: document.getElementById('modal_occurred_at'),
        location: document.getElementById('modal_location'),
        patient_age: document.getElementById('modal_patient_age'),
        patient_gender: document.getElementById('modal_patient_gender'),
        is_employee: document.getElementById('modal_is_employee'),
        treatment: document.getElementById('modal_treatment'),
        nurse_name: document.getElementById('modal_nurse_name'),
        description: document.getElementById('modal_description')
    };

    function fillModalFromRow(row) {
        const d = row.dataset;
        fields.id.textContent = d.id || '—';
        fields.first_name.textContent = d.firstName || '—';
        fields.last_name.textContent = d.lastName || '—';
        fields.occurred_at.textContent = d.occurredAt || '—';
        fields.location.textContent = d.location || '—';
        fields.patient_age.textContent = d.patientAge || '—';
        fields.patient_gender.textContent = d.patientGender || '—';
        fields.is_employee.textContent = d.isEmployee || 'Não';
        fields.treatment.textContent = d.treatment || '—';
        fields.nurse_name.textContent = d.nurseName || '—';
        fields.description.textContent = d.description || 'Sem descrição.';

        const female = d.patientGender === 'F';
        document.querySelectorAll('.modal-body-canvas [data-body-image]').forEach(image => {
            const view = image.dataset.bodyImage;
            const pathKey = female ? `${view}Female` : view;
            image.src = bodyImagePaths[pathKey];
        });

        document.querySelectorAll('.modal-body-marker').forEach(marker => marker.remove());

        let marks = [];
        try {
            marks = JSON.parse(d.bodyMarks || '[]');
        } catch (error) {
            marks = [];
        }

        marks.forEach(mark => {
            const canvas = document.querySelector(`.modal-body-canvas[data-view="${mark.view}"]`);
            if (!canvas) return;

            const marker = document.createElement('i');
            const markType = markTypes[mark.type] ? mark.type : 'other';
            marker.className = `modal-body-marker mark-${markType}`;
            marker.style.left = `${mark.x}%`;
            marker.style.top = `${mark.y}%`;
            marker.title = markTypes[markType];
            canvas.appendChild(marker);
        });

        bodyLocation.classList.toggle('visible', marks.length > 0);
    }

    function openModal() {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    rows.forEach((row) => {
        row.addEventListener('click', () => {
            fillModalFromRow(row);
            openModal();
        });

        row.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                fillModalFromRow(row);
                openModal();
            }
        });
    });

    closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });
})();
</script>

</body>
</html>
