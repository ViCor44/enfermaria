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
        DECLARACIÓN DE RECHAZO DE TRASLADO<br>
        <span>HOSPITALARIO</span>
    </h1>
    <br><br>

    <div class="text">
        Yo, <span class="bold"><?= htmlspecialchars($incident_data['patient_name']) ?></span>,
        de nacionalidad <span class="bold"><?= htmlspecialchars($incident_data['patient_nationality'] ?? '') ?></span>,
        con domicilio en
        <span class="bold"><?= htmlspecialchars($incident_data['patient_address']) ?></span>,
        como usuario del Parque Acuático Slide &amp; Splash, declaro que he sido debidamente informado por el enfermero
        <span class="bold"><?= htmlspecialchars($incident_data['nurse_name']) ?></span>,
        de que debía ser trasladado al hospital más cercano para la realización de exámenes y observación médica adecuada.
    </div>

    <div class="text bold">
        He decidido no realizar el procedimiento mencionado anteriormente.
    </div>

    <div class="text">
        Declaro asimismo que he sido informado de los riesgos a los que puedo estar expuesto al no realizar el procedimiento indicado,
        asumiendo de forma personal e individual todas las consecuencias y responsabilidades derivadas de mi negativa.
    </div>

    <div class="date">
        Lagoa, <?= date('d/m/Y') ?>
    </div>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="small">(Nombre legible y firma)</div>
    </div>

    <div class="footer">
        <div class="footer-line"></div>
        Documento gerado automaticamente pelo Sistema SAE —
        Episódio nº <?= (int)($incident_data['episode_number'] ?? $incident_data['id']) ?> —
        <?= date('d/m/Y H:i') ?>
    </div>

</div>
