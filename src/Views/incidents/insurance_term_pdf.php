<?php
// Views/admin/insurance_term_pdf.php

use Dompdf\Dompdf;
use Dompdf\Options;

$incident = $incident ?? [];

$today = date('d/m/Y');

// configurar dompdf
$options = new Options();
$options->set('defaultFont', 'Helvetica');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

ob_start();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">

<style>

body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 11px;
    margin: 30px;
}

h1 {
    font-size: 18px;
    text-align: center;
    margin-bottom: 2px;
}

h2 {
    font-size: 13px;
    text-align: center;
    margin-top: 0;
}

.section {
    border: 1px solid #000;
    padding: 8px;
    margin-bottom: 10px;
}

.section-title {
    font-weight: bold;
    text-transform: uppercase;
    font-size: 11px;
    margin-bottom: 4px;
}

.row {
    width: 100%;
    clear: both;
}

.col {
    float: left;
    border: 1px solid #000;
    padding: 4px;
    min-height: 28px;
    margin-right: 3px;
}

.col-25 { width: 23%; }
.col-33 { width: 32%; }
.col-40 { width: 40%; }
.col-50 { width: 48%; }
.col-75 { width: 73%; }
.col-100 { width: 98%; }

.label {
    font-size: 9px;
    text-transform: uppercase;
    color: #444;
}

.value {
    font-size: 11px;
    font-weight: bold;
}

.clearfix::after {
    content:"";
    display:block;
    clear:both;
}

.footer {
    margin-top: 20px;
    font-size: 11px;
}

.policy-row {
    border:2px solid #000;
    margin-bottom:18px;
    display: table;
    width:100%;
}

.policy-left,
.policy-middle,
.policy-right {
    display: table-cell;
    padding:8px;
    vertical-align: middle;
    border-right:2px solid #000;
}

.policy-right {
    border-right:none;
}

.small-label {
    font-size:9px;
    font-weight:700;
    text-transform: uppercase;
}

.policy-value {
    margin-top:6px;
    font-size:13px;
    font-weight:700;
}

.policy-number {
    margin-top:6px;
    font-size:15px;
    font-weight:700;
    letter-spacing:4px;
}

.series-box span {
    display:inline-block;
    border:1px solid #000;
    padding:4px 6px;
    font-weight:700;
    margin-right:3px;
}


</style>
</head>

<body>

<div style="text-align:center;margin-bottom:18px;">
    <img src="http://localhost/enfermaria/public/assets/img/logo-fidelidade.png"
         style="height:110px;margin-bottom:8px;">    
</div>

<!-- BLOCO MODALIDADE / APÓLICE -->
<div class="policy-row">

    <div class="policy-left">
        <div class="small-label">MODALIDADE / PRODUTO</div>
        <div class="policy-value">ACIDENTES PESSOAIS / GRUPO</div>
    </div>

    <div class="policy-middle">
        <div class="small-label">APÓLICE Nº</div>
        <div class="policy-number">
            AG23893918
        </div>
    </div>

    <div class="policy-right">
        <div class="small-label">SISTEMA DE INFORMAÇÃO</div>
        <div class="policy-number">
            100
        </div>
    </div>

</div>

<!-- TOMADOR -->
<div class="section">
    <div class="section-title">Tomador do Seguro</div>

    <div class="row clearfix">
        <div class="col col-50">
            <div class="label">Nome</div>
            <div class="value">Correia &amp; Santinha, Lda</div>
        </div>    
        <div class="col col-50">
            <div class="label">Morada</div>
            <div class="value">EN125 – Vale de Deus – Estômbar</div>
        </div>
    </div>
</div>

<!-- SINISTRADO -->
<div class="section">
    <div class="section-title">Pessoa Sinistrada</div>

    <div class="row clearfix">
        <div class="col col-75">
            <div class="label">Nome</div>
            <div class="value"><?= htmlspecialchars($incident['patient_name'] ?? '') ?></div>
        </div>
        <div class="col col-25">
            <div class="label">Telefone</div>
            <div class="value"><?= htmlspecialchars($incident['patient_phone'] ?? '') ?></div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col col-75">
            <div class="label">Morada</div>
            <div class="value"><?= htmlspecialchars($incident['patient_address'] ?? '') ?></div>
        </div>
        <div class="col col-25">
            <div class="label">Código Postal</div>
            <div class="value"><?= htmlspecialchars($incident['patient_postal_code'] ?? '') ?></div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col col-50">
            <div class="label">Localidade</div>
            <div class="value"><?= htmlspecialchars($incident['patient_city'] ?? '') ?></div>
        </div>
        <div class="col col-25">
            <div class="label">Data nascimento</div>
            <div class="value"><?= htmlspecialchars($incident['patient_dob'] ?? '') ?></div>
        </div>
        <div class="col col-25">
            <div class="label">Documento</div>
            <div class="value">
                <?= htmlspecialchars($incident['patient_id_type'] ?? '') ?>
                <?= htmlspecialchars($incident['patient_id_number'] ?? '') ?>
            </div>
        </div>
    </div>
</div>

<!-- OCORRÊNCIA -->
<div class="section">
    <div class="section-title">Identificação da Ocorrência</div>

    <div class="row clearfix">
        <div class="col col-25">
            <div class="label">Data</div>
            <div class="value"><?= date('d/m/Y', strtotime($incident['occurred_at'] ?? '')) ?></div>
        </div>
        <div class="col col-25">
            <div class="label">Hora</div>
            <div class="value"><?= date('H:i', strtotime($incident['occurred_at'] ?? '')) ?></div>
        </div>    
        <div class="col col-50">
            <div class="label">Local</div>
            <div class="value"><?= htmlspecialchars($incident['location_name'] ?? '') ?></div>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col col-100">
            <div class="label">Descrição</div>
            <div class="value"><?= nl2br(htmlspecialchars($incident['insurance_description'] ?? ($incident['description'] ?? ''))) ?></div>
        </div>
    </div>
</div>

<!-- TEXTO LEGAL -->
<div class="footer">
    <strong>Declaração:</strong><br>
    O(s) recibo(s), relatório(s) médico(s) e exames complementares deverão ser enviados para:<br><br>

    Correia &amp; Santinha, Lda<br>
    EN125 – Vale de Deus – Estômbar<br>
    Apartado 90<br>
    8401-901 Lagoa
</div>

</body>
</html>

<?php
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

// abre inline
$dompdf->stream(
    "termo_seguro_ocorrencia_{$incident['id']}.pdf",
    ["Attachment" => false]
);

exit;
