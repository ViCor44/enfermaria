<?php
$baseUrl = '/enfermaria/public/index.php';
$nome    = $_SESSION['user_name'] ?? 'Administrador';
$role    = $_SESSION['role'] ?? '';
$currentUserId = $_SESSION['user_id'] ?? null;

$hasHospitalTreatment = false;

foreach ($treatments as $t) {
    if (strcasecmp($t['treatment_type_name'], 'Enviado para hospital') === 0) {
        $hasHospitalTreatment = true;
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Ocorrência #<?= (int)$incident['id'] ?> · Detalhes</title>
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
    .subtitle { 
        font-size: 1rem; 
        color: #777; 
        margin-bottom: 1rem; 
    }

    .card {
        background: #fff; 
        border-radius: 12px; 
        padding: 1.5rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        margin-bottom: 1.5rem;
        text-align: left; /* Alinha conteúdo das cards à esquerda para melhor leitura */
    }
    .card h2 { 
        margin-top: 0; 
        font-size: 1.2rem;
        color: #555;
    }

    .row { 
        display: flex; 
        flex-wrap: wrap; 
        gap: 1.5rem; 
    }
    .row > div { 
        flex: 1; 
        min-width: 200px; 
    }

    .label { 
        font-size: .85rem; 
        font-weight: 600; 
        color: #555; 
        text-transform: uppercase; 
        letter-spacing: .03em; 
    }
    .value { 
        margin-top: .3rem; 
        font-size: .95rem; 
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

    .badge-status-curso { 
        background: #fff7e6; 
        color: #b36b00; 
    }
    .badge-status-concluido { 
        background: #e6ffed; 
        color: #047857; 
    }

    table {
        width: 100%; 
        border-collapse: collapse; 
        background: #fff;
        border-radius: 12px; 
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        margin: 0 auto;
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
    tr:last-child td { 
        border-bottom: none; 
    }
    tr:hover {
        background: #f8faff;
    }

    .back-link {
        text-decoration: none; 
        color: #1f6feb; 
        font-size: .95rem;
        transition: text-decoration 0.2s ease;
    }
    .back-link:hover {
        text-decoration: underline;
    }
    .separator {
        margin: 0 0.5rem;
        color: #aaa;
        font-size: 0.95rem;
    }

    .separator-hr {
        border: none;
        border-top: 1px solid #ddd;
        margin: 1.5rem 0;
    }

    .followup-actions {
        margin-top: 12px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .followup-actions a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: .55rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: .9rem;
        text-decoration: none;
        transition: all .15s ease;
    }

    /* botão secundário */
    .followup-actions .btn-outline {
        border: 1px solid #f59e0b;
        color: #92400e;
        background: white;
    }

    .followup-actions .btn-outline:hover {
        background: #fff7ed;
    }

    /* botão principal */
    .followup-actions .btn-primary {
        background: #1f6feb;
        color: white;
        border: 1px solid transparent;
    }

    .followup-actions .btn-primary:hover {
        background: #0f5bdb;
        transform: translateY(-1px);
    }

    .section-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:12px;
    }

    .section-header h2{
        margin:0;
    }

    .btn-primary{
        background:#1f6feb;
        color:white;
        padding:.45rem .9rem;
        border-radius:8px;
        text-decoration:none;
        font-weight:600;
        font-size:.9rem;
    }

    .btn-primary:hover{
        background:#0f5bdb;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        main {
            padding: 1rem;
        }
        .row {
            flex-direction: column;
            gap: 1rem;
        }
        table {
            font-size: 0.85rem;
        }
    }
</style>
</head>
<body>

<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <div style="text-align: left; margin-bottom: 1rem;">
        <a href="<?= $baseUrl ?>?route=admin_incidents" class="back-link">
            ← Voltar à lista de Ocorrências
        </a>

        <span class="separator">|</span>

        <a class="back-link" href="<?= $baseUrl ?>?route=admin_incident_print&id=<?= (int)$incident['id'] ?>" target="_blank">
            Gerar PDF
        </a>
    </div>

    <h1>Episódio #<?= (int)$incident['id'] ?></h1>

    <hr class="separator-hr"> <!-- Adicionado para consistência com outras páginas -->

    <!-- Dados da Ocorrência -->
    <div class="card">
        <h2>Dados da Ocorrência</h2>
        <div class="row">
            <div>
                <div class="label">Data / Hora</div>
                <div class="value"><?= htmlspecialchars($incident['occurred_at']) ?></div>
            </div>
            <div>
                <div class="label">Local</div>
                <div class="value"><?= htmlspecialchars($incident['location_name']) ?></div>
            </div>
            <div>
                <div class="label">Tipo de Ocorrência</div>
                <div class="value"><span class="badge"><?= htmlspecialchars($incident['incident_type_name']) ?></span></div>
            </div>
        </div>

        <div class="row" style="margin-top:1rem;">            
            <div>
                <div class="label">Enfermeiro responsável</div>
                <div class="value"><?= htmlspecialchars($incident['nurse_name'] ?? '') ?></div>
            </div>
        </div>

        <?php if (!empty($incident['description'])): ?>
            <div style="margin-top:1rem;">
                <div class="label">Descrição</div>
                <div class="value"><?= nl2br(htmlspecialchars($incident['description'])) ?></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dados do paciente -->
    <div class="card">
        <div class="section-header">
            <h2>Dados do utente</h2>
            <?php if (!empty($canSeePatient) && $canSeePatient === true): ?>
                <a class="btn-primary" href="<?= $baseUrl ?>?route=incident_patient_edit&incident_id=<?= (int)$incident['id'] ?>">
                    Editar dados do utente
                </a>
            <?php endif; ?>
        </div>

            <?php if (!empty($canSeePatient) && $canSeePatient === true): ?>
                <!-- Admin ou enfermeiro que tratou vêem os dados -->
                <div class="row" style="margin-top:1rem;">
                    <div>
                        <div class="label">Nome completo</div>
                        <div class="value"><?= htmlspecialchars($incident['patient_name']) ?></div>
                    </div>
                    <div>
                        <div class="label">Idade</div>
                        <div class="value">
                            <?= $incident['patient_age'] !== null ? (int)$incident['patient_age'] . ' anos' : '—' ?>
                        </div>
                    </div>

                    <div>
                        <div class="label">Género</div>
                        <div class="value">
                            <?= !empty($incident['patient_gender']) ? htmlspecialchars($incident['patient_gender']) : '—' ?>
                        </div>
                    </div>
                    <div>
                        <div class="label">Colaborador</div>
                        <div class="value">
                            <?= !empty($incident['patient_is_employee']) ? 'Sim' : 'Não' ?>
                        </div>
                    </div>
                    <div>
                        <div class="label">Nacionalidade</div>
                        <div class="value">
                            <?= $incident['patient_nationality'] ? htmlspecialchars($incident['patient_nationality']) : '—' ?>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top:1rem;">
                    <div>
                        <div class="label">Morada</div>
                        <div class="value"><?= htmlspecialchars($incident['patient_address'] ?? '—') ?></div>
                    </div>
                    <div>
                        <div class="label">Telefone</div>
                        <div class="value"><?= htmlspecialchars($incident['patient_phone'] ?? '—') ?></div>
                    </div>
                </div>

                <div class="row" style="margin-top:1rem;">
                    <div>
                        <div class="label">Data de Nascimento</div>
                        <div class="value">
                            <?= !empty($incident['patient_dob']) ? htmlspecialchars($incident['patient_dob']) : '—' ?>
                        </div>
                    </div>
                    <div>
                        <div class="label">Identificação</div>
                        <div class="value">
                            <?= !empty($incident['patient_id_type']) ? htmlspecialchars($incident['patient_id_type']) . ' • ' . htmlspecialchars($incident['patient_id_number']) : '—' ?>
                        </div>
                    </div>

                    

                </div>
                <p class="subtitle" style="margin-top:1rem;">
                    Estes dados são visíveis apenas à administração e ao enfermeiro responsável, por motivos de RGPD.
                </p>

            <?php else: ?>
                <!-- Manager e outros enfermeiros apenas sabem que os dados existem -->
                <p class="subtitle">
                    Existem dados de utente associados a esta Ocorrência, mas não tem permissão para os visualizar.
                </p>
            <?php endif; ?>
    </div>

    <!-- Tratamentos associados -->
    <div class="card">
        <div class="section-header">
            <h2>Tratamentos associados</h2>

            <a class="btn-primary"
            href="<?= $baseUrl ?>?route=treatments_new&incident_id=<?= (int)$incident['id'] ?>">
                ➕ Adicionar tratamento
            </a>
        </div>
       <?php if (empty($treatments)): ?>
            <p class="subtitle">Não existem tratamentos registados para esta Ocorrência.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Data registo</th>
                        <th>Tipo de tratamento</th>
                        <th>Estado</th>
                        <th>Enfermeiro</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($treatments as $tr): ?>
                    <tr class="treatment-row" style="cursor:pointer"
                        data-id="<?= (int)$tr['id'] ?>"
                        data-created_at="<?= htmlspecialchars($tr['created_at']) ?>"
                        data-type="<?= htmlspecialchars($tr['treatment_type_name']) ?>"
                        data-status="<?= $tr['status'] === 'em_curso' ? 'Em curso' : 'Concluído' ?>"
                        data-nurse="<?= htmlspecialchars($tr['nurse_name'] ?? '') ?>"
                        data-notes="<?= htmlspecialchars($tr['notes'] ?? '') ?>"
                        data-editinfo="<?php if (!empty($tr['notes_edited_by_name'])): ?>Editado por <?= htmlspecialchars($tr['notes_edited_by_name']) ?><?php if (!empty($tr['notes_edited_at'])): ?> em <?= htmlspecialchars($tr['notes_edited_at']) ?><?php endif; ?><?php endif; ?>"
                    >
                        <td><?= htmlspecialchars($tr['created_at']) ?></td>
                        <td><?= htmlspecialchars($tr['treatment_type_name']) ?></td>
                        <td>
                            <?php if ($tr['status'] === 'em_curso'): ?>
                                <span class="badge badge-status-curso">Em curso</span>
                            <?php else: ?>
                                <span class="badge badge-status-concluido">Concluído</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($tr['nurse_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth($tr['notes'] ?? '', 0, 100, '…')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

<!-- Modal para detalhes do tratamento (sempre presente) -->
<div id="treatment-modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);align-items:center;justify-content:center;">
    <div style="background:#fff;padding:2rem 2.5rem;border-radius:12px;max-width:420px;width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
        <button id="close-modal" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#888;">&times;</button>
        <h3 style="margin-top:0;font-size:1.15rem;color:#1f6feb;">Detalhes do Tratamento</h3>
        <div style="margin-bottom:1rem;">
            <strong>Data registo:</strong> <span id="modal-created-at"></span><br>
            <strong>Tipo de tratamento:</strong> <span id="modal-type"></span><br>
            <strong>Estado:</strong> <span id="modal-status"></span><br>
            <strong>Enfermeiro:</strong> <span id="modal-nurse"></span><br>
        </div>
        <div>
            <strong>Notas:</strong>
            <div id="modal-notes-view" style="margin-top:.5rem;white-space:pre-line;background:#f8f9fb;padding:.7rem 1rem;border-radius:8px;min-height:40px;"></div>
            <textarea id="modal-notes-edit" style="display:none;width:100%;min-height:80px;margin-top:.5rem;padding:.7rem 1rem;border-radius:8px;border:1px solid #ddd;font-size:1rem;"></textarea>
            <div id="modal-edit-info" style="font-size:.85rem;color:#888;margin-top:.5rem;"></div>
            <div style="margin-top:1rem;display:flex;gap:8px;">
                <button id="edit-notes-btn" style="background:#1f6feb;color:#fff;border:none;padding:.5rem 1.2rem;border-radius:8px;cursor:pointer;font-size:.95rem;">Editar notas</button>
                <button id="save-notes-btn" style="display:none;background:#059669;color:#fff;border:none;padding:.5rem 1.2rem;border-radius:8px;cursor:pointer;font-size:.95rem;">Guardar</button>
                <button id="cancel-notes-btn" style="display:none;background:#e5e7eb;color:#333;border:none;padding:.5rem 1.2rem;border-radius:8px;cursor:pointer;font-size:.95rem;">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('treatment-modal');
    var closeBtn = document.getElementById('close-modal');
    var rows = document.querySelectorAll('.treatment-row');
    var notesView = document.getElementById('modal-notes-view');
    var notesEdit = document.getElementById('modal-notes-edit');
    var editBtn = document.getElementById('edit-notes-btn');
    var saveBtn = document.getElementById('save-notes-btn');
    var cancelBtn = document.getElementById('cancel-notes-btn');
    var editInfo = document.getElementById('modal-edit-info');
    var currentTreatmentId = null;
    var currentNotes = '';

    rows.forEach(function(row) {
        row.addEventListener('click', function() {
            document.getElementById('modal-created-at').textContent = row.getAttribute('data-created_at');
            document.getElementById('modal-type').textContent = row.getAttribute('data-type');
            document.getElementById('modal-status').textContent = row.getAttribute('data-status');
            document.getElementById('modal-nurse').textContent = row.getAttribute('data-nurse');
            notesView.textContent = row.getAttribute('data-notes');
            notesEdit.value = row.getAttribute('data-notes');
            editInfo.textContent = row.getAttribute('data-editinfo') || '';
            currentNotes = row.getAttribute('data-notes') || '';
            currentTreatmentId = row.getAttribute('data-id');

            notesView.style.display = '';
            notesEdit.style.display = 'none';
            editBtn.style.display = '';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';

            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
        });
    });

    editBtn.addEventListener('click', function() {
        notesView.style.display = 'none';
        notesEdit.style.display = '';
        editBtn.style.display = 'none';
        saveBtn.style.display = '';
        cancelBtn.style.display = '';
        notesEdit.focus();
    });

    cancelBtn.addEventListener('click', function() {
        notesEdit.value = currentNotes;
        notesView.style.display = '';
        notesEdit.style.display = 'none';
        editBtn.style.display = '';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
    });

    saveBtn.addEventListener('click', function() {
        if (!currentTreatmentId) {
            return;
        }

        saveBtn.disabled = true;
        fetch('<?= $baseUrl ?>?route=admin_treatment_update_notes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: 'treatment_id=' + encodeURIComponent(currentTreatmentId) + '&notes=' + encodeURIComponent(notesEdit.value)
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.error || 'erro_desconhecido');
            }

            var selector = '.treatment-row[data-id="' + String(currentTreatmentId).replace(/"/g, '\\"') + '"]';
            var activeRow = document.querySelector(selector);

            currentNotes = data.notes;
            notesView.textContent = data.notes;
            editInfo.textContent = data.editinfo || '';
            notesView.style.display = '';
            notesEdit.style.display = 'none';
            editBtn.style.display = '';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';

            if (activeRow) {
                activeRow.setAttribute('data-notes', data.notes);
                activeRow.setAttribute('data-editinfo', data.editinfo || '');
                var notesCell = activeRow.children[4];
                if (notesCell) {
                    notesCell.textContent = data.notes.length > 100 ? data.notes.slice(0, 99) + '…' : data.notes;
                }
            }
        })
        .catch(function(error) {
            alert('Erro ao guardar notas: ' + error.message);
        })
        .finally(function() {
            saveBtn.disabled = false;
        });
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    // Fechar modal ao clicar fora
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.style.display = 'none';

    });
});
</script>

        <?php if ($hasHospitalTreatment): ?>
            <?php if (!empty($incident['refused_hospital']) && (int)$incident['refused_hospital'] === 1): ?>
                <div style="
                    margin-top:1rem;
                    padding:.8rem 1rem;
                    background:#fff7e6;
                    border:1px solid #facc15;
                    border-radius:8px;
                    color:#92400e;
                    font-weight:600;
                ">
                    ⚠️ O utente recusou a deslocação ao hospital após avaliação.
                </div>
                
                <div class="followup-actions">
                <?php if (!empty($canGenerateHospitalDocs) && $canGenerateHospitalDocs === true): ?>
                    <a class="btn-outline"
                    target="_blank"
                    href="/enfermaria/public/index.php?route=admin_incident_print_refusal&id=<?= (int)$incident['id'] ?>">
                        📄 Gerar termo de recusa
                    </a>
                <?php endif; ?>
                    <a class="btn-primary"
                    href="<?= $baseUrl ?>?route=incident_hospital_followup&id=<?= (int)$incident['id'] ?>">
                        ➕ Registar ida posterior ao hospital
                    </a>

                </div>

            <?php else: ?>
                <div style="
                    margin-top:1rem;
                    padding:.8rem 1rem;
                    background:#e6ffed;
                    border:1px solid #86efac;
                    border-radius:8px;
                    color:#065f46;
                    font-weight:600;
                ">
                    🏥 Utente encaminhado para o hospital.
                </div>                
                <div class="followup-actions">
                <?php if (!empty($canGenerateHospitalDocs) && $canGenerateHospitalDocs === true): ?>
                    <a class="btn-outline"
                    target="_blank"
                    href="<?= $baseUrl ?>?route=incident_insurance_term&id=<?= (int)$incident['id'] ?>">
                        📄 Gerar termo de seguro
                    </a>
                <?php endif; ?>
                    <a class="btn-primary"
                    href="<?= $baseUrl ?>?route=incident_hospital_followup&id=<?= (int)$incident['id'] ?>">
                        ➕ Registar ida posterior ao hospital
                    </a>

                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
    <?php if (!empty($followups)): ?>
<div class="card">
    <h2>Seguimento hospitalar posterior</h2>

    <?php foreach ($followups as $f): ?>
        <div class="row-item">
            <div class="label">Data</div>
            <div class="value"><?= htmlspecialchars($f['visit_date']) ?></div>

            <div class="label">Hospital</div>
            <div class="value"><?= htmlspecialchars($f['hospital_name']) ?></div>

            <div class="label">Observações</div>
            <div class="value"><?= nl2br(htmlspecialchars($f['notes'])) ?></div>

            <?php if ($f['document_path']): ?>
                <div class="followup-actions">

                    <a class="btn-outline"
                    href="<?= htmlspecialchars($f['document_path']) ?>"
                    target="_blank">
                        📎 Ver comprovativo
                    </a>

                </div>

            <?php endif; ?>
        </div>

        <hr>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</main>
</body>
</html>