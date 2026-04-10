<style>
@page {
    margin: 2.5cm 2.2cm;
}

body {
    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
    font-size: 12px;
    line-height: 1.6;
}

.page {
    width: 100%;
}

h1 {
    text-align: center;
    font-size: 17px;
    font-weight: bold;
    margin-bottom: 35px;
}

h1 span {
    display: block;
    font-size: 16px;
    margin-top: 4px;
}

.text {
    margin-top: 25px;
    text-align: justify;
    word-wrap: break-word;
    font-size: 14px;
}

.bold {
    font-weight: bold;
}

.date {
    margin-top: 45px;
}

.signature {
    margin-top: 90px;
}

.signature-line {
    width: 320px;
    border-top: 1px solid #000;
    margin-top: 30px;
}

.small {
    font-size: 11px;
}

.footer {
    position: fixed;
    bottom: -1.8cm;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 9px;
    color: #444;
}

.footer-line {
    border-top: 0.5px solid #999;
    margin-bottom: 4px;
}
</style>


<div class="page">

    <h1>
        REFUSAL OF HOSPITAL TRANSFER<br>
        <span>DECLARATION</span>
    </h1>
    <br><br>

    <div class="text">
        I, <span class="bold"><?= htmlspecialchars($incident_data['patient_name']) ?></span>,
        of nationality <span class="bold"><?= htmlspecialchars($incident_data['patient_nationality'] ?? '') ?></span>,
        residing at
        <span class="bold"><?= htmlspecialchars($incident_data['patient_address']) ?></span>,
        as a guest of the Slide &amp; Splash Water Park, hereby declare that I was duly informed by the nurse
        <span class="bold"><?= htmlspecialchars($incident_data['nurse_name']) ?></span>,
        that I should be transported to the nearest hospital for medical examination and appropriate observation.
    </div>

    <div class="text bold">
        I have chosen not to undergo the procedure mentioned above.
    </div>

    <div class="text">
        I further declare that I was informed and warned of the risks to which I may be exposed by not undergoing the indicated procedure,
        assuming personally and individually all consequences and responsibility arising from my refusal.
    </div>

    <div class="date">
        Lagoa, <?= date('d/m/Y') ?>
    </div>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="small">(Printed name and signature)</div>
    </div>

    <div class="footer">
        <div class="footer-line"></div>
        Documento gerado automaticamente pelo Sistema SAE —
        Episódio nº <?= (int)($incident_data['episode_number'] ?? $incident_data['id']) ?> —
        <?= date('d/m/Y H:i') ?>
    </div>

</div>
