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
        TERMO DE RECUSA DE ENCAMINHAMENTO<br>
        <span>HOSPITALAR</span>
    </h1>
    <br><br>
    <div class="text">
        Eu, <span class="bold"><?= htmlspecialchars($incident_data['patient_name']) ?></span>,
        de nacionalidade <span class="bold"><?= htmlspecialchars($incident_data['patient_nationality'] ?? '') ?></span>,
        residente e domiciliado em
        <span class="bold"><?= htmlspecialchars($incident_data['patient_address']) ?></span>,
        na qualidade de utente do Parque Aquático Slide &amp; Splash, declaro que fui devidamente informado pelo enfermeiro
        <span class="bold"><?= htmlspecialchars($incident_data['nurse_name']) ?></span>,
        de que deveria ser transportado para o Hospital mais próximo para a realização de exames e observação médica adequada.
    </div>

    <div class="text bold">
        Optei por não realizar o procedimento acima mencionado.
    </div>

    <div class="text">
        Declaro ainda ter sido esclarecido e alertado sobre os riscos a que estarei sujeito pela não realização do procedimento indicado,
        assumindo pessoal e individualmente todas as consequências e responsabilidade da minha recusa.
    </div>

    <div class="date">
        Lagoa, <?= date('d/m/Y') ?>
    </div>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="small">(Nome legível e assinatura)</div>
    </div>

    <div class="footer">
        <div class="footer-line"></div>
        Documento gerado automaticamente pelo Sistema SAE —
        Episódio nº <?= (int)($incident_data['episode_number'] ?? $incident_data['id']) ?> —
        <?= date('d/m/Y H:i') ?>
    </div>

</div>
