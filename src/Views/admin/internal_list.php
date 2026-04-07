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
        </div>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('recordModal');
    const closeBtn = document.getElementById('modalCloseBtn');
    const rows = document.querySelectorAll('tr.record-row');
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
