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
        DÉCLARATION DE REFUS DE TRANSFERT<br>
        <span>HOSPITALIER</span>
    </h1>
    <br><br>

    <div class="text">
        Je soussigné(e), <span class="bold"><?= htmlspecialchars($incident_data['patient_name']) ?></span>,
        de nationalité <span class="bold"><?= htmlspecialchars($incident_data['patient_nationality'] ?? '') ?></span>,
        domicilié(e) à
        <span class="bold"><?= htmlspecialchars($incident_data['patient_address']) ?></span>,
        en tant qu’usager du parc aquatique Slide &amp; Splash, déclare avoir été dûment informé(e) par l’infirmier
        <span class="bold"><?= htmlspecialchars($incident_data['nurse_name']) ?></span>,
        de la nécessité d’être transporté(e) vers l’hôpital le plus proche afin de subir des examens médicaux et une observation appropriée.
    </div>

    <div class="text bold">
        J’ai décidé de ne pas effectuer la procédure mentionnée ci-dessus.
    </div>

    <div class="text">
        Je déclare également avoir été averti(e) des risques encourus en refusant la procédure indiquée,
        assumant personnellement et individuellement toutes les conséquences et responsabilités liées à mon refus.
    </div>

    <div class="date">
        Lagoa, <?= date('d/m/Y') ?>
    </div>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="small">(Nom lisible et signature)</div>
    </div>

    <div class="footer">
        <div class="footer-line"></div>
        Documento gerado automaticamente pelo Sistema SAE —
        Episódio nº <?= (int)$incident_data['id'] ?> —
        <?= date('d/m/Y H:i') ?>
    </div>

</div>
