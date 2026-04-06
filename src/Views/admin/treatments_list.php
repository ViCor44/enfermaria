<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Administrador';
$role = $_SESSION['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Meus tratamentos</title>
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
    margin-bottom: 2rem; 
}

.filters {
    background: #fff; 
    padding: 1.5rem; 
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    margin-bottom: 1.5rem;
    text-align: left; /* Alinha filtros à esquerda */
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
    background: #fff;
    transition: border-color 0.2s ease;
}
.filters input:focus, .filters select:focus {
    border-color: #1f6feb;
    outline: none;
}
.filters button {
    padding: 0.6rem 1.2rem; 
    border: none; 
    border-radius: 8px; 
    cursor: pointer;
    background: #1f6feb; 
    color: #fff; 
    font-size: .95rem;
    transition: background 0.2s ease;
}
.filters button:hover {
    background: #0f5bdb;
}
.filters a.btn-reset {
    padding: 0.6rem 1.2rem; 
    border-radius: 8px; 
    font-size: .95rem; 
    text-decoration: none;
    border: 1px solid #ddd; 
    color: #555; 
    background: #f8f9fb;
    transition: background 0.2s ease;
}
.filters a.btn-reset:hover {
    background: #e9ecef;
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
a {
    color: #1f6feb;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}

.badge { 
    display: inline-block; 
    padding: 0.3rem 0.7rem; 
    border-radius: 999px;
    font-size: .8rem; 
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
.flash-success { 
    background: #e6ffed; 
    color: #047857; 
    padding: 0.7rem; 
    border-radius: 6px; 
    margin-bottom: 1rem; 
}

.separator {
    border: none;
    border-top: 1px solid #ddd;
    margin: 2rem 0;
}

/* Responsividade */
@media (max-width: 768px) {
    main {
        padding: 1rem;
    }
    .filters form {
        flex-direction: column;
        align-items: stretch;
    }
    .filters div {
        width: 100%;
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
    <h1>Tratamentos</h1>

    <hr class="separator"> <!-- Adicionado para consistência -->

    <div class="filters">
        <form method="get" action="<?= $baseUrl ?>">
            <input type="hidden" name="route" value="admin_treatments">
            <div>
                <label>Estado</label>
                <select name="status">
                    <option value="">-- Todos --</option>
                    <option value="em_curso" <?= $statusFilter === 'em_curso' ? 'selected' : '' ?>>Em curso</option>
                    <option value="concluido" <?= $statusFilter === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                </select>
            </div>

            <div>
                <label>Data inicial</label>
                <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
            </div>

            <div>
                <label>Data final</label>
                <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
            </div>

            <div>
                <button type="submit">Filtrar</button>
                <a href="<?= $baseUrl ?>?route=admin_treatments" class="btn-reset">Limpar</a>
            </div>
        </form>
    </div>

    <?php if (empty($treatments)): ?>
        <p>Nenhum tratamento encontrado.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Data registo</th>
                    <th>Ocorrência</th>
                    <th>Local</th>
                    <th>Tipo</th>
                    <th>Enfermeiro</th>
                    <th>Estado</th>
                    <th>Notas</th>
                    <th>Observ.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($treatments as $tr): ?>
                    <tr class="treatment-row" style="cursor:pointer"
                        data-id="<?= (int)$tr['id'] ?>"
                        data-created_at="<?= htmlspecialchars($tr['created_at']) ?>"
                        data-incident_id="<?= (int)$tr['incident_id'] ?>"
                        data-incident_type="<?= htmlspecialchars($tr['incident_type_name'] ?? '') ?>"
                        data-location="<?= htmlspecialchars($tr['location_name'] ?? '') ?>"
                        data-type="<?= htmlspecialchars($tr['treatment_type_name'] ?? '') ?>"
                        data-status="<?= $tr['status'] === 'em_curso' ? 'Em curso' : 'Concluído' ?>"
                        data-nurse="<?= htmlspecialchars($tr['nurse_name'] ?? '') ?>"
                        data-notes="<?= htmlspecialchars($tr['notes'] ?? '') ?>"
                        data-editinfo="<?php if (!empty($tr['notes_edited_by_name'])): ?>Editado por <?= htmlspecialchars($tr['notes_edited_by_name']) ?><?php if (!empty($tr['notes_edited_at'])): ?> em <?= htmlspecialchars($tr['notes_edited_at']) ?><?php endif; ?><?php endif; ?>"
                    >
                        <td><?= htmlspecialchars($tr['created_at'] ?? $tr['created_at']) ?></td>
                        <td>
                            <a href="<?= $baseUrl ?>?route=admin_incident_detail&id=<?= (int)$tr['incident_id'] ?>">
                                <strong>#<?= (int)$tr['incident_id'] ?></strong> —
                                <?= htmlspecialchars($tr['incident_type_name']) ?>
                            </a>

                            <div style="font-size:12px;color:#6b7280;">
                                <?= htmlspecialchars($tr['incident_occurred_at']) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($tr['location_name']) ?></td>
                        <td><span style="display:inline-block;padding:.2rem .5rem;border-radius:999px;background:#e9f2ff;color:#1d4ed8;font-size:.8rem;">
                                <?= htmlspecialchars($tr['treatment_type_name']) ?>
                            </span></td>
                        <td><?= htmlspecialchars($tr['nurse_name']) ?></td>
                        <td>
                            <?php if ($tr['status'] === 'em_curso'): ?>
                                <span class="badge badge-status-curso">Em curso</span>
                            <?php else: ?>
                                <span class="badge badge-status-concluido">Concluído</span>
                            <?php endif; ?>                           
                        </td>
                        <td><?= htmlspecialchars(mb_strimwidth($tr['notes'] ?? '', 0, 120, '…')) ?></td>
                        <td>
                            <?php if ($role === 'Enfermeiro' && $tr['status'] === 'em_curso'): ?>
                                <form method="post" action="<?= $baseUrl ?>?route=treatment_conclude" style="display:inline;" class="js-conclude-form">
                                    <input type="hidden" name="treatment_id" value="<?= (int)$tr['id'] ?>">
                                    <input type="hidden" name="conclusion_notes" value="" class="js-conclusion-notes">
                                    <!-- CSRF token se tiveres -->
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Concluir
                                    </button>
                                </form>
                            <?php else: ?>
                                <div>
                                    
                                    <?php if (!empty($tr['concluded_by_name'])): ?>
                                        <div style="font-size:12px;color:#6b7280;">
                                            Concluído por <?= htmlspecialchars($tr['concluded_by_name']) ?>
                                            <br>
                                            <small><?= htmlspecialchars($tr['concluded_at'] ?? '') ?></small>
                                        </div>
                                    <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="treatment-modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);align-items:center;justify-content:center;">
            <div style="background:#fff;padding:2rem 2.5rem;border-radius:12px;max-width:520px;width:92vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
                <button id="close-modal" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#888;">&times;</button>
                <h3 style="margin-top:0;font-size:1.15rem;color:#1f6feb;">Detalhes do Tratamento</h3>
                <div style="margin-bottom:1rem;">
                    <strong>Data registo:</strong> <span id="modal-created-at"></span><br>
                    <strong>Ocorrência:</strong> <span id="modal-incident"></span><br>
                    <strong>Local:</strong> <span id="modal-location"></span><br>
                    <strong>Tipo de tratamento:</strong> <span id="modal-type"></span><br>
                    <strong>Estado:</strong> <span id="modal-status"></span><br>
                    <strong>Enfermeiro:</strong> <span id="modal-nurse"></span><br>
                </div>
                <div>
                    <strong>Notas:</strong>
                    <div id="modal-notes-view" style="margin-top:.5rem;white-space:pre-line;background:#f8f9fb;padding:.7rem 1rem;border-radius:8px;min-height:40px;"></div>
                    <textarea id="modal-notes-edit" style="display:none;width:100%;min-height:90px;margin-top:.5rem;padding:.7rem 1rem;border-radius:8px;border:1px solid #ddd;font-size:1rem;"></textarea>
                    <div id="modal-edit-info" style="font-size:.85rem;color:#888;margin-top:.5rem;"></div>
                    <div style="margin-top:1rem;display:flex;gap:8px;">
                        <button id="edit-notes-btn" style="background:#1f6feb;color:#fff;border:none;padding:.5rem 1.2rem;border-radius:8px;cursor:pointer;font-size:.95rem;">Editar notas</button>
                        <button id="save-notes-btn" style="display:none;background:#059669;color:#fff;border:none;padding:.5rem 1.2rem;border-radius:8px;cursor:pointer;font-size:.95rem;">Guardar</button>
                        <button id="cancel-notes-btn" style="display:none;background:#e5e7eb;color:#333;border:none;padding:.5rem 1.2rem;border-radius:8px;cursor:pointer;font-size:.95rem;">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('treatment-modal');
    var rows = document.querySelectorAll('.treatment-row');
    var closeBtn = document.getElementById('close-modal');
    var notesView = document.getElementById('modal-notes-view');
    var notesEdit = document.getElementById('modal-notes-edit');
    var editBtn = document.getElementById('edit-notes-btn');
    var saveBtn = document.getElementById('save-notes-btn');
    var cancelBtn = document.getElementById('cancel-notes-btn');
    var editInfo = document.getElementById('modal-edit-info');
    var currentTreatmentId = null;
    var currentNotes = '';

    function truncateNotes(text, maxLen) {
        return text.length > maxLen ? text.slice(0, maxLen - 1) + '…' : text;
    }

    function fillModalFromRow(row) {
        document.getElementById('modal-created-at').textContent = row.getAttribute('data-created_at') || '';
        document.getElementById('modal-incident').textContent = '#' + (row.getAttribute('data-incident_id') || '') + ' - ' + (row.getAttribute('data-incident_type') || '');
        document.getElementById('modal-location').textContent = row.getAttribute('data-location') || '';
        document.getElementById('modal-type').textContent = row.getAttribute('data-type') || '';
        document.getElementById('modal-status').textContent = row.getAttribute('data-status') || '';
        document.getElementById('modal-nurse').textContent = row.getAttribute('data-nurse') || '';

        currentTreatmentId = row.getAttribute('data-id');
        currentNotes = row.getAttribute('data-notes') || '';
        notesView.textContent = currentNotes;
        notesEdit.value = currentNotes;
        editInfo.textContent = row.getAttribute('data-editinfo') || '';

        notesView.style.display = '';
        notesEdit.style.display = 'none';
        editBtn.style.display = '';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
    }

    rows.forEach(function(row) {
        row.addEventListener('click', function(event) {
            if (event.target.closest('a, button, form, input, select, textarea')) {
                return;
            }
            fillModalFromRow(row);
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
        });
    });

    if (editBtn) {
        editBtn.addEventListener('click', function() {
            notesView.style.display = 'none';
            notesEdit.style.display = '';
            editBtn.style.display = 'none';
            saveBtn.style.display = '';
            cancelBtn.style.display = '';
            notesEdit.focus();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            notesEdit.value = currentNotes;
            notesView.style.display = '';
            notesEdit.style.display = 'none';
            editBtn.style.display = '';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        });
    }

    if (saveBtn) {
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
                    var notesCell = activeRow.children[6];
                    if (notesCell) {
                        notesCell.textContent = truncateNotes(data.notes, 120);
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
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    var concludeForms = document.querySelectorAll('.js-conclude-form');
    concludeForms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            var shouldConclude = confirm('Concluir este tratamento? Esta ação será registada.');
            if (!shouldConclude) {
                event.preventDefault();
                return;
            }

            var notesInput = form.querySelector('.js-conclusion-notes');
            if (notesInput) {
                notesInput.value = '';
            }

            var wantsConclusionNotes = confirm('Deseja adicionar notas de conclusão?');
            if (!wantsConclusionNotes) {
                return;
            }

            var noteText = prompt('Introduza as notas de conclusão:');
            if (noteText === null) {
                event.preventDefault();
                return;
            }

            var sanitized = noteText.trim();
            if (sanitized === '') {
                return;
            }

            if (notesInput) {
                notesInput.value = sanitized;
            }
        });
    });
});
</script>
</body>
</html>