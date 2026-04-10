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
        ERKLÄRUNG ZUR VERWEIGERUNG DES<br>
        <span>KRANKENHAUSTRANSPORTS</span>
    </h1>
    <br><br>

    <div class="text">
        Ich, <span class="bold"><?= htmlspecialchars($incident_data['patient_name']) ?></span>,
        Staatsangehörigkeit <span class="bold"><?= htmlspecialchars($incident_data['patient_nationality'] ?? '') ?></span>,
        wohnhaft in
        <span class="bold"><?= htmlspecialchars($incident_data['patient_address']) ?></span>,
        als Besucher des Wasserparks Slide &amp; Splash erkläre hiermit, dass ich vom Krankenpfleger
        <span class="bold"><?= htmlspecialchars($incident_data['nurse_name']) ?></span>
        ordnungsgemäß darüber informiert wurde, dass ich zur Durchführung medizinischer Untersuchungen und entsprechender Beobachtung
        in das nächstgelegene Krankenhaus gebracht werden sollte.
    </div>

    <div class="text bold">
        Ich habe mich entschieden, die oben genannte Maßnahme nicht durchführen zu lassen.
    </div>

    <div class="text">
        Weiter erkläre ich, dass ich über die Risiken aufgeklärt wurde, die mit der Nichtdurchführung der empfohlenen Maßnahme verbunden sind,
        und übernehme persönlich und individuell sämtliche Konsequenzen sowie die Verantwortung für meine Weigerung.
    </div>

    <div class="date">
        Lagoa, <?= date('d/m/Y') ?>
    </div>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="small">(Lesbarer Name und Unterschrift)</div>
    </div>

    <div class="footer">
        <div class="footer-line"></div>
        Documento gerado automaticamente pelo Sistema SAE —
        Episódio nº <?= (int)($incident_data['episode_number'] ?? $incident_data['id']) ?> —
        <?= date('d/m/Y H:i') ?>
    </div>

</div>
