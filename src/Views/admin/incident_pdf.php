<?php
// src/Views/admin/incident_pdf.php
// Variáveis esperadas: $incident (array), $treatments (array), $canSeePatient (bool)
$printDate = (new DateTime())->format('Y-m-d H:i:s');
$episode = (int)($incident['id'] ?? 0);
?>
<!doctype html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Acidente #<?= (int)$incident['id'] ?> · Resumo</title>
<style>
/* --- Reset simples --- */
* { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; color:#222; margin:20px; background:#fff; }

/* Cabeçalho */
.header {
  margin-bottom:18px;
}
.title {
  font-size:26px; font-weight:700; margin:0 0 6px 0;
}
.subtitle { color:#666; font-size:12px; margin:0 0 12px 0; }

/* Card */
.card { background:#fff; border:1px solid #e8eef6; padding:14px; margin-bottom:14px; border-radius:6px; }
.card h2 { margin:0 0 10px 0; font-size:14px; color:#333; text-transform:uppercase; letter-spacing:0.04em; }

/* duas colunas (usamos float que o dompdf interpreta bem) */
.cols { clear:both; margin-top:6px; }
.col { float:left; width:48%; }
.col + .col { margin-left:4%; }

.row-item { margin-bottom:10px; }
.label { font-size:11px; font-weight:700; color:#666; text-transform:uppercase; }
.value { margin-top:4px; font-size:13px; color:#111; }

/* badge (simples) */
.badge { display:inline-block; padding:4px 8px; border-radius:999px; background:#eaf5ff; color:#0b6bf0; font-size:11px; }

/* tabela de tratamentos */
.table { width:100%; border-collapse: collapse; margin-top:8px; }
.table th, .table td { border:1px solid #e9eef5; padding:8px; font-size:12px; text-align:left; }
.table th { background:#f7fbff; font-weight:700; }

/* rodapé de impressão */
.footer { margin-top:18px; font-size:11px; color:#666; }

/* evitar floats não fechados */
.clearfix::after { content:""; display:table; clear:both; }
</style>
</head>
<body>

<div class="header">
  <div class="title">SAE - Sistema de Apoio à Enfermaria</div>  
  <div><h3>Relatório de Acidente</h3></div>
  <div class="subtitle">Episódio <?= (int)$incident['id'] ?></div>
</div>

<div class="card">
  <h2>Dados do Acidente</h2>
  <div class="cols clearfix">
    <div class="col">
      <div class="row-item"><div class="label">Data / Hora</div><div class="value"><?= htmlspecialchars($incident['occurred_at']) ?></div></div>
      <div class="row-item"><div class="label">Idade (utente)</div><div class="value"><?= $incident['patient_age'] !== null ? (int)$incident['patient_age'] : '—' ?></div></div>
      <div class="row-item"><div class="label">Género (utente)</div><div class="value"><?= $incident['patient_gender'] ?: '—' ?></div></div>
    </div>

    <div class="col">
      <div class="row-item"><div class="label">Local</div><div class="value"><?= htmlspecialchars($incident['location_name']) ?></div></div>
      <div class="row-item"><div class="label">Tipo de acidente</div><div class="value"><span class="badge"><?= htmlspecialchars($incident['incident_type_name']) ?></span></div></div>
      <div class="row-item"><div class="label">Enfermeiro responsável</div><div class="value"><?= htmlspecialchars($incident['nurse_name'] ?? '') ?></div></div>
    </div>
  </div>

  <?php if (!empty($incident['description'])): ?>
    <div style="margin-top:8px;">
      <div class="label">Descrição</div>
      <div class="value"><?= nl2br(htmlspecialchars($incident['description'])) ?></div>
    </div>
  <?php endif; ?>
</div>

<?php if (!empty($incident['patient_name'])): ?>
  <div class="card">
    <h2>Dados do utente</h2>
    <?php if ($canSeePatient): ?>
      <div class="cols clearfix">
        <div class="col">
          <div class="row-item"><div class="label">Nome completo</div><div class="value"><?= htmlspecialchars($incident['patient_name']) ?></div></div>
          <div class="row-item"><div class="label">Morada</div><div class="value"><?= htmlspecialchars($incident['patient_address'] ?? '—') ?></div></div>
          <div class="row-item"><div class="label">Data de nascimento</div><div class="value"><?= htmlspecialchars($incident['patient_dob'] ?? '—') ?></div></div>
        </div>
        <div class="col">
          <div class="row-item"><div class="label">Nacionalidade</div><div class="value"><?= htmlspecialchars($incident['patient_nationality'] ?? '—') ?></div></div>
          <div class="row-item"><div class="label">Telefone</div><div class="value"><?= htmlspecialchars($incident['patient_phone'] ?? '—') ?></div></div>
          <div class="row-item"><div class="label">Identificação</div><div class="value"><?=
            !empty($incident['patient_id_type']) ? htmlspecialchars($incident['patient_id_type']) . ' • ' . htmlspecialchars($incident['patient_id_number']) : '—'
          ?></div></div>
        </div>
      </div>
      <div class="footer">Estes dados são visíveis apenas à administração e ao enfermeiro responsável, por motivos de RGPD.</div>
    <?php else: ?>
      <div class="value">Existem dados de utente associados a este Acidente, mas não tem permissão para os visualizar.</div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="card">
  <h2>Tratamentos associados</h2>
  <?php if (empty($treatments)): ?>
    <div class="value">Não existem tratamentos registados para este Acidente. - Impressão: <?= htmlspecialchars($printDate) ?></div>
  <?php else: ?>
    <table class="table">
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
          <td><?= htmlspecialchars($tr['status']) ?></td>
          <td><?= htmlspecialchars($tr['nurse_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($tr['notes'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<div class="footer">
  Relatório gerado automaticamente pelo sistema de gestão de enfermaria. — Impressão: <?= htmlspecialchars($printDate) ?>
</div>
</body>
</html>