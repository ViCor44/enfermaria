<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Registar Incidente</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
    body { margin:0; font-family:system-ui,sans-serif; background:#f3f6fb; }
    header {
        background:#1f6feb; color:#fff; padding:1rem 2rem;
        display:flex; justify-content:space-between; align-items:center;
    }
    main { max-width:900px; margin:0 auto; padding:2rem; }
    h1 { margin-top:0; }
    form {
        background:#fff; padding:1.5rem; border-radius:12px;
        box-shadow:0 8px 20px rgba(0,0,0,.06);
    }
    label { display:block; margin-top:1rem; font-weight:600; color:#555; }
    input, select, textarea {
        width:100%; padding:.6rem; margin-top:.3rem; border-radius:6px; border:1px solid #ccc;
    }
    textarea { min-height:90px; resize:vertical; }
    .row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.2rem;   /* <-- distância vertical entre as linhas */
    }

    .row > div {
        flex: 1;
    }

    button {
        margin-top:1.5rem; padding:.8rem 1.4rem; border:none; border-radius:8px;
        background:#1f6feb; color:#fff; font-size:1rem; cursor:pointer;
    }
    button:hover { background:#1557c0; }
    .flash-error { background:#ffe0e0; color:#900; padding:.7rem; border-radius:6px; margin-bottom:1rem; }
</style>
</head>
<body>
<?php require __DIR__ . '/../layouts/header.php'; ?>
<main>
    <h1>Registar novo incidente</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash-error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $baseUrl ?>?route=incidents_store">
        <div class="row">
            <div>
                <label>Tipo de incidente *</label>
                <select name="incident_type_id" required>
                    <option value="">-- Selecionar --</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label>Local / Atração *</label>
                <select name="location_id">
                    <option value="">-- Selecionar da lista --</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= (int)$loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="font-size:.8rem;color:#666;">
                    Pode escolher um local existente ou indicar um novo campo ao lado.
                </small>
            </div>
            <div style="margin-right: 24px;">
                <label>Outro local (se não estiver na lista)</label>
                <input type="text" name="new_location" placeholder="Ex.: Zona de refeições nova">
            </div>
        </div>
        

        <!-- LINHA DATA / HORA -->
        <div class="row" style="margin-bottom: 24px;">
            <div style="margin-right: 24px;">
                <label>Data *</label>
                <input type="date" name="date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div style="margin-right: 24px;">
                <label>Hora *</label>
                <input type="time" name="time" required value="<?= date('H:i') ?>">
            </div>
        </div>

        <!-- LINHA IDADE / GÉNERO -->
        <div class="row">
            <div style="margin-right: 24px;">
                <label>Idade do utente (opcional)</label>
                <input type="number" name="patient_age" min="0" max="120">
            </div>
            <div style="margin-right: 24px;">
                <label>Género (opcional)</label>
                <select name="patient_gender">
                    <option value="">-- Não especificar --</option>
                    <option value="M">Masculino</option>
                    <option value="F">Feminino</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
        </div>

        <label>Descrição / Observações (opcional)</label>
        <textarea name="description" placeholder="Descrição sucinta do incidente, sem dados de identificação desnecessários."></textarea>

        <div class="section-title">
            Tratamento aplicado (opcional)
            <label style="font-weight:600; font-size:.9rem; margin-left:.5rem;">
                <input type="checkbox" id="toggle-treatment">
                Adicionar tratamento agora
            </label>
        </div>

        <div id="treatment-block" style="display:none;">
            <div class="row">
                <div>
                    <label>Tipo de tratamento</label>
                    <select name="treatment_type_id" id="treatment_type_id">
                        <option value="">-- Selecionar --</option>
                        <?php foreach ($treatmentTypes as $tt): ?>
                            <option value="<?= (int)$tt['id'] ?>"><?= htmlspecialchars($tt['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="max-width:180px;">
                    <label>Estado</label>
                    <select name="treatment_status">
                        <option value="em_curso">Em curso</option>
                        <option value="concluido">Concluído</option>
                    </select>
                </div>
            </div>

            <label>Notas do tratamento (opcional)</label>
            <textarea name="treatment_notes" placeholder="Descreva o tratamento efetuado. Evite dados pessoais desnecessários."></textarea>

            <!-- Campos extra se for 'Enviado para hospital' -->
            <div id="patient-block" style="display:none; margin-top:1rem; padding:1rem; border-radius:8px; background:#fff7e6;">
                <strong>Dados do utente (para envio ao hospital)</strong>

                <div class="row">
                    <div>
                        <label>Nome completo do utente *</label>
                        <input type="text" name="patient_name">
                    </div>
                    <div>
                        <label>Nacionalidade (opcional)</label>
                        <input type="text" name="patient_nationality">
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label>Hotel (opcional)</label>
                        <input type="text" name="patient_hotel">
                    </div>
                    <div>
                        <label>Nº de quarto (opcional)</label>
                        <input type="text" name="patient_room">
                    </div>
                </div>
                <div class="small">
                    Estes dados só serão visíveis para o administrador e para o enfermeiro responsável.
                </div>
            </div>
        </div>

        <!-- --- Fim: bloco de Tratamento Aplicado --- -->

        <button type="submit" style="margin-top:1.5rem;">Guardar incidente</button>
    </form>

    <script>
        const toggleTreatment = document.getElementById('toggle-treatment');
        const treatmentBlock  = document.getElementById('treatment-block');
        const treatmentType   = document.getElementById('treatment_type_id');
        const patientBlock    = document.getElementById('patient-block');

        // ID do tipo "Enviado para hospital" vindo do PHP
        const hospitalTypeId = <?= $hospitalTreatmentTypeId ? (int)$hospitalTreatmentTypeId : 'null' ?>;

        toggleTreatment.addEventListener('change', function () {
            treatmentBlock.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                patientBlock.style.display = 'none';
                if (treatmentType) treatmentType.value = '';
            }
        });

        if (treatmentType && hospitalTypeId) {
            treatmentType.addEventListener('change', function () {
                if (String(this.value) === String(hospitalTypeId)) {
                    patientBlock.style.display = 'block';
                } else {
                    patientBlock.style.display = 'none';
                }
            });
        }
    </script>
        
</main>
</body>
</html>
