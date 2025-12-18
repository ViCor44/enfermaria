<?php
$baseUrl = '/enfermaria/public/index.php';
$nome    = $_SESSION['user_name'] ?? 'Administrador';
$role    = $_SESSION['role'] ?? '';
$currentUserId = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Acidente #<?= (int)$incident['id'] ?> · Detalhes</title>
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
            ← Voltar à lista de Acidentes
        </a>

        <span class="separator">|</span>

        <a class="back-link" href="<?= $baseUrl ?>?route=admin_incident_print&id=<?= (int)$incident['id'] ?>" target="_blank">
            Gerar PDF
        </a>
    </div>

    <h1>Episódio #<?= (int)$incident['id'] ?></h1>

    <hr class="separator-hr"> <!-- Adicionado para consistência com outras páginas -->

    <!-- Dados do Acidente -->
    <div class="card">
        <h2>Dados do Acidente</h2>
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
                <div class="label">Tipo de Acidente</div>
                <div class="value"><span class="badge"><?= htmlspecialchars($incident['incident_type_name']) ?></span></div>
            </div>
        </div>

        <div class="row" style="margin-top:1rem;">
            <div>
                <div class="label">Idade (utente)</div>
                <div class="value"><?= $incident['patient_age'] !== null ? (int)$incident['patient_age'] : '—' ?></div>
            </div>
            <div>
                <div class="label">Género (utente)</div>
                <div class="value"><?= $incident['patient_gender'] ?: '—' ?></div>
            </div>
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
        <h2>Dados do utente</h2>

        <?php if (empty($incident['patient_name'])): ?>
            <p class="subtitle">
                Não existe registo de envio para hospital / dados de utente associados a este Acidente.
            </p>

        <?php else: ?>
            <?php if (!empty($canSeePatient) && $canSeePatient === true): ?>
                <!-- Admin ou enfermeiro que tratou vêem os dados -->
                <div class="row" style="margin-top:1rem;">
                    <div>
                        <div class="label">Nome completo</div>
                        <div class="value"><?= htmlspecialchars($incident['patient_name']) ?></div>
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
                    Existem dados de utente associados a este Acidente, mas não tem permissão para os visualizar.
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Tratamentos associados -->
    <div class="card">
        <h2>Tratamentos associados</h2>
        <?php if (empty($treatments)): ?>
            <p class="subtitle">Não existem tratamentos registados para este Acidente.</p>
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
                    <tr>
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
    </div>

</main>
</body>
</html>