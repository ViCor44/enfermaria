<?php
$baseUrl = '/enfermaria/public/index.php';
$nome    = $_SESSION['user_name'] ?? 'Administrador';
$role    = $_SESSION['role'] ?? '';
$currentUserId = $_SESSION['user_id'] ?? null;
$flashSuccess = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
$incidentView = $incident ?? [];

$hasHospitalTreatment = false;
$hasHospitalRefusal = !empty($incident['refused_hospital'])
    && (int)$incident['refused_hospital'] === 1;
$careTreatments = [];

foreach ($treatments as $t) {
    if (strcasecmp($t['treatment_type_name'], 'Enviado para hospital') === 0) {
        $hasHospitalTreatment = true;
        continue;
    }
    $careTreatments[] = $t;
}

$bodyMarkLabels = [
    'contusion' => 'Hematoma / Contusão',
    'wound' => 'Ferida',
    'burn' => 'Queimadura',
    'pain' => 'Dor',
    'insect_bite' => 'Picada de inseto',
    'epistaxis' => 'Epistaxis',
    'other' => 'Outra ocorrência',
];
$bodyMarks = json_decode((string)($incidentView['body_marks'] ?? '[]'), true);
$bodyMarks = is_array($bodyMarks) ? $bodyMarks : [];
$femaleBody = ($incidentView['patient_gender'] ?? '') === 'F';
$bodyFrontImage = $femaleBody ? 'body-front-female.svg' : 'body-front.svg';
$bodyBackImage = $femaleBody ? 'body-back-female.svg' : 'body-back.svg';

?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Ocorrência #<?= (int)($incident['episode_number'] ?? $incident['id']) ?> · Detalhes</title>
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

    main.detail-page {
        width: min(1380px, calc(100% - 40px));
        max-width: none;
        padding: 28px 0 48px;
        text-align: left;
    }

    .detail-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 18px;
    }

    .detail-heading h1 {
        margin: 0;
        color: #10213f;
        font-size: clamp(1.7rem, 2.5vw, 2.25rem);
        letter-spacing: 0;
    }

    .detail-heading p { margin: 7px 0 0; color: #697890; font-size: .92rem; }

    .heading-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .heading-actions .back-link {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        padding: 0 14px;
        border: 1px solid #d5e0ed;
        border-radius: 7px;
        background: #fff;
        color: #294f7a;
        font-weight: 650;
    }
    .heading-actions .back-link:hover { background: #eef5fc; text-decoration: none; }

    .detail-grid {
        display: grid;
        grid-template-columns: minmax(330px, .85fr) minmax(520px, 1.45fr);
        gap: 14px;
        align-items: stretch;
        margin-bottom: 14px;
    }

    .detail-page .card {
        margin-bottom: 14px;
        padding: 20px;
        border: 1px solid #dce5f0;
        border-radius: 8px;
        box-shadow: 0 7px 22px rgba(26, 54, 93, .055);
    }

    .detail-grid .card { margin-bottom: 0; }
    .detail-page .card h2 { color: #10213f; font-size: 1rem; }

    .section-header { gap: 16px; }
    .detail-page .label { color: #697890; font-size: .7rem; font-weight: 750; letter-spacing: .07em; }
    .detail-page .value { color: #1f3653; font-size: .9rem; line-height: 1.45; }
    .detail-page .subtitle { color: #697890; font-size: .82rem; line-height: 1.5; }

    .patient-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px 24px;
        margin-top: 16px;
    }

    .patient-grid .wide { grid-column: span 2; }

    .body-card { display: grid; grid-template-columns: minmax(380px, 1fr) 210px; gap: 22px; }
    .body-card-header { grid-column: 1 / -1; display: flex; justify-content: space-between; gap: 16px; align-items: center; }
    .body-card-header h2 { margin: 0; }
    .body-card-header span { color: #697890; font-size: .8rem; }

    .body-maps { display: grid; grid-template-columns: repeat(2, minmax(150px, 1fr)); gap: 12px; max-width: 620px; margin: 0 auto; }
    .body-map { text-align: center; }
    .body-canvas { position: relative; width: min(100%, 190px); aspect-ratio: 1 / 2; margin: 0 auto; }
    .body-canvas img { width: 100%; height: 100%; object-fit: contain; }
    .body-map-label { display: block; margin-top: 7px; color: #405571; font-size: .8rem; font-weight: 700; }

    .body-marker {
        position: absolute;
        width: 18px;
        height: 18px;
        border: 3px solid #fff;
        border-radius: 50%;
        background: var(--mark-color);
        box-shadow: 0 0 0 7px var(--mark-glow), 0 2px 7px rgba(31,45,61,.25);
        transform: translate(-50%, -50%);
    }

    .mark-contusion { --mark-color:#e5484d; --mark-glow:rgba(229,72,77,.17); }
    .mark-wound { --mark-color:#d4145a; --mark-glow:rgba(212,20,90,.17); }
    .mark-burn { --mark-color:#ff8a00; --mark-glow:rgba(255,138,0,.18); }
    .mark-pain { --mark-color:#6c5ce7; --mark-glow:rgba(108,92,231,.17); }
    .mark-insect_bite { --mark-color:#65a30d; --mark-glow:rgba(101,163,13,.18); }
    .mark-epistaxis { --mark-color:#8b1e3f; --mark-glow:rgba(139,30,63,.18); }
    .mark-other { --mark-color:#13b8a6; --mark-glow:rgba(19,184,166,.17); }

    .body-legend { display: grid; align-content: start; gap: 5px; padding: 10px; border: 1px solid #dce5f0; border-radius: 7px; background: #f8fbff; }
    .body-legend-item { display: flex; align-items: center; gap: 10px; min-height: 34px; color: #405571; font-size: .77rem; }
    .legend-swatch { width: 13px; height: 13px; border: 3px solid #fff; border-radius: 50%; background: var(--mark-color); box-shadow: 0 0 0 4px var(--mark-glow); }

    .empty-body { grid-column: 1 / -1; padding: 24px; border: 1px dashed #cbd8e7; border-radius: 7px; background: #f8fbff; color: #697890; text-align: center; }

    .detail-page table { border: 1px solid #e0e8f2; border-radius: 7px; box-shadow: none; }
    .detail-page th { background: #eef4fb; color: #405571; font-size: .76rem; text-transform: uppercase; letter-spacing: .04em; }
    .detail-page td { color: #2d405d; font-size: .86rem; }

    /* Responsividade */
    @media (max-width: 1050px) {
        .detail-grid { grid-template-columns: 1fr; }
        .body-card { grid-template-columns: 1fr; }
        .body-card-header { grid-column: 1; }
        .body-legend { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 768px) {
        main.detail-page {
            width: min(100% - 24px, 1380px);
            padding: 1rem;
        }
        .detail-heading { flex-direction: column; }
        .patient-grid { grid-template-columns: 1fr 1fr; }
        .patient-grid .wide { grid-column: span 2; }
        .body-maps { grid-template-columns: repeat(2, minmax(120px, 1fr)); }
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
<?php if ($flashSuccess): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var message = <?= json_encode($flashSuccess, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Sucesso',
            text: message,
            icon: 'success',
            confirmButtonText: 'OK'
        });
    } else {
        window.alert(message);
    }
});
</script>
<?php endif; ?>
<main class="detail-page">
    <div class="detail-heading">
        <div>
            <h1>Episódio #<?= (int)($incident['episode_number'] ?? $incident['id']) ?></h1>
            <p>Detalhes clínicos, utente, tratamentos e encaminhamento da ocorrência.</p>
        </div>
        <div class="heading-actions">
            <a href="<?= $baseUrl ?>?route=admin_incidents" class="back-link">&larr; Voltar à lista</a>
            <a class="back-link" href="<?= $baseUrl ?>?route=admin_incident_print&id=<?= (int)$incident['id'] ?>" target="_blank">Gerar PDF</a>
        </div>
    </div>

    <div class="detail-grid">
    <div class="card occurrence-card">
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

    <div class="card patient-card">
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
                <div class="patient-grid">
                    <div class="wide">
                        <div class="label">Nome completo</div>
                        <div class="value"><?= htmlspecialchars($incident['patient_name']) ?></div>
                    </div>
                    <div>
                        <div class="label">Data de nascimento</div>
                        <div class="value"><?= !empty($incident['patient_dob']) ? htmlspecialchars($incident['patient_dob']) : '—' ?></div>
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
                    <div class="wide">
                        <div class="label">Morada</div>
                        <div class="value"><?= htmlspecialchars($incident['patient_address'] ?? '—') ?></div>
                    </div>
                    <div>
                        <div class="label">Telefone</div>
                        <div class="value"><?= htmlspecialchars($incident['patient_phone'] ?? '—') ?></div>
                    </div>
                    <div>
                        <div class="label">Código postal / Cidade</div>
                        <div class="value"><?= htmlspecialchars(trim((string)($incidentView['patient_postal_code'] ?? '') . ' ' . (string)($incidentView['patient_city'] ?? '')) ?: '—') ?></div>
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
    </div>

    <div class="card body-card">
        <div class="body-card-header">
            <h2>Localização no corpo</h2>
            <span><?= count($bodyMarks) ?> <?= count($bodyMarks) === 1 ? 'marcação' : 'marcações' ?></span>
        </div>
        <?php if ($bodyMarks === []): ?>
            <div class="empty-body">Não foram assinaladas zonas do corpo nesta ocorrência.</div>
        <?php else: ?>
            <div class="body-maps">
                <?php foreach (['front' => ['Frente', $bodyFrontImage], 'back' => ['Costas', $bodyBackImage]] as $view => [$viewLabel, $image]): ?>
                    <div class="body-map">
                        <div class="body-canvas">
                            <img src="/enfermaria/public/assets/img/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($viewLabel) ?> do corpo humano">
                            <?php foreach ($bodyMarks as $mark): ?>
                                <?php if (($mark['view'] ?? '') === $view && isset($mark['x'], $mark['y'])): ?>
                                    <?php $markType = isset($bodyMarkLabels[$mark['type'] ?? '']) ? $mark['type'] : 'other'; ?>
                                    <span class="body-marker mark-<?= htmlspecialchars($markType) ?>" style="left:<?= (float)$mark['x'] ?>%;top:<?= (float)$mark['y'] ?>%;" title="<?= htmlspecialchars($bodyMarkLabels[$markType]) ?>"></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <span class="body-map-label"><?= htmlspecialchars($viewLabel) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="body-legend">
                <?php foreach ($bodyMarkLabels as $type => $label): ?>
                    <div class="body-legend-item mark-<?= htmlspecialchars($type) ?>"><span class="legend-swatch"></span><?= htmlspecialchars($label) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tratamentos associados -->
    <div class="card">
        <div class="section-header">
            <h2>Tratamentos associados</h2>
            <?php if ($role === 'Enfermeiro'): ?>
                <a class="btn-primary"
                href="<?= $baseUrl ?>?route=treatments_new&incident_id=<?= (int)$incident['id'] ?>">
                    ➕ Adicionar tratamento
                </a>
            <?php endif; ?>
        </div>
    <?php if (empty($careTreatments)): ?>
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
                <?php foreach ($careTreatments as $tr): ?>
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
            return response.json().catch(function() {
                return {};
            }).then(function(payload) {
                if (!response.ok) {
                    var apiError = payload && payload.error ? String(payload.error) : 'http_error';
                    throw new Error(apiError + ' (HTTP ' + response.status + ')');
                }
                return payload;
            });
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

        <?php if ($hasHospitalRefusal): ?>
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
                    href="/enfermaria/public/index.php?route=admin_incident_print_refusal&id=<?= (int)($incidentView['id'] ?? 0) ?>">
                        📄 Gerar termo de recusa
                    </a>
                <?php endif; ?>
                    <a class="btn-primary"
                    href="<?= $baseUrl ?>?route=incident_hospital_followup&id=<?= (int)($incidentView['id'] ?? 0) ?>">
                        ➕ Registar ida posterior ao hospital
                    </a>

                </div>
        <?php elseif ($hasHospitalTreatment): ?>
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
                    href="<?= $baseUrl ?>?route=incident_insurance_term&id=<?= (int)($incidentView['id'] ?? 0) ?>">
                        📄 Gerar termo de seguro
                    </a>
                <?php endif; ?>
                    <a class="btn-primary"
                    href="<?= $baseUrl ?>?route=incident_hospital_followup&id=<?= (int)($incidentView['id'] ?? 0) ?>">
                        ➕ Registar ida posterior ao hospital
                    </a>

                </div>
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
