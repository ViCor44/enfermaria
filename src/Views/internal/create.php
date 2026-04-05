<?php
$baseUrl = '/enfermaria/public/index.php';
$nome = $_SESSION['user_name'] ?? 'Enfermeiro';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Novo Registo Interno</title>
<link rel="stylesheet" href="/enfermaria/public/assets/css/layout.css">

<style>
/* === COPIADO do create de ocorrência === */

body { 
    margin: 0; 
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; 
    background: #f5f7fb; 
    color: #333; 
}

main { 
    max-width: 1200px; 
    margin: 0 auto; 
    padding: 2rem; 
    text-align: center;
}

h1 { 
    margin-top: 0; 
    font-size: 2rem;
    color: #1f6feb;
}

form {
    background: #fff; 
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    text-align: left;
}

label { 
    display: block; 
    margin-top: 1rem; 
    font-weight: 600; 
    color: #555; 
}

label.required::after {
    content: " *";
    color: #e53e3e;
}

input, select, textarea {
    width: 100%; 
    padding: 0.7rem 0.9rem;
    margin-top: 0.3rem; 
    border-radius: 8px; 
    border: 1px solid #ddd; 
}

textarea { min-height: 120px; }

.row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

button {
    margin-top: 1.5rem; 
    padding: 0.7rem 1.5rem;
    border-radius: 8px;
    background: #1f6feb; 
    color: #fff;
    border: none;
    cursor: pointer;
}

.flash-error { 
    background: #ffe0e0; 
    color: #900; 
    padding: 0.7rem; 
    border-radius: 6px; 
    margin-bottom: 1rem; 
}

.separator {
    border: none;
    border-top: 1px solid #ddd;
    margin: 2rem 0;
}
</style>
</head>

<body>

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main>

<h1>Novo Registo Interno</h1>

<hr class="separator">

<?php if (!empty($_SESSION['error'])): ?>
    <div class="flash-error">
        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= $baseUrl ?>?route=internal_store">

    <!-- DATA / HORA -->
    <div class="row">
        <div style="margin-right: 24px;">
            <label class="required">Data</label>
            <input type="date" name="date" required value="<?= date('Y-m-d') ?>">
        </div>

        <div style="margin-right: 24px;">
            <label class="required">Hora</label>
            <input type="time" name="time" required value="<?= date('H:i') ?>">
        </div>
    </div>

    <!-- LOCAL -->
    <div class="row">
        <div style="margin-right: 24px;">
            <label class="required">Local / Área</label>
            <input
                list="locations-list"
                name="location_input"
                id="location_input"
                placeholder="Escreva ou escolha..."
                autocomplete="off"
                required
            >
                <!-- PRIMEIRO E ÚLTIMO NOME -->
                <div class="row">
                    <div style="margin-right: 24px;">
                        <label class="required">Primeiro Nome</label>
                        <input type="text" name="first_name" required maxlength="100" placeholder="Primeiro nome">
                    </div>
                    <div style="margin-right: 24px;">
                        <label class="required">Último Nome</label>
                        <input type="text" name="last_name" required maxlength="100" placeholder="Último nome">
                    </div>
                </div>

            <datalist id="locations-list">
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= htmlspecialchars($loc['name']) ?>" data-id="<?= (int)$loc['id'] ?>"></option>
                <?php endforeach; ?>
            </datalist>

            <input type="hidden" name="location_id" id="location_id">
        </div>
    </div>

    <!-- TRATAMENTO -->
    <div class="row">
        <div style="margin-right: 24px;">
            <label class="required">Tratamento</label>
            <input
                list="treatment-types-list"
                name="treatment"
                id="treatment"
                placeholder="Escreva ou escolha..."
                autocomplete="off"
                required
            >

            <datalist id="treatment-types-list">
                <?php foreach ($treatmentTypes as $tt): ?>
                    <option value="<?= htmlspecialchars($tt['name']) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>
    </div>

    <!-- IDADE / GÉNERO -->
    <div class="row">
        <div style="margin-right: 24px;">
            <label>Idade</label>
            <input type="number" name="patient_age" min="0" max="120">
        </div>

        <div style="margin-right: 24px;">
            <label>Género</label>
            <select name="patient_gender">
                <option value="">-- Não especificar --</option>
                <option value="M">Masculino</option>
                <option value="F">Feminino</option>
                <option value="Outro">Outro</option>
            </select>
        </div>
    </div>

    <!-- DESCRIÇÃO -->
    <label>Descrição / Observações</label>
    <textarea
        name="description" style="margin-right: 24px;"
        placeholder="Descreva a situação interna. Não incluir dados pessoais."
        
    ></textarea>

    <button type="submit">Guardar Registo Interno</button>

</form>

<script>
    function wireDatalist(inputId, datalistId, hiddenId) {
        const input = document.getElementById(inputId);
        const datalist = document.getElementById(datalistId);
        const hidden = document.getElementById(hiddenId);

        function buildMap() {
            const map = new Map();
            datalist.querySelectorAll('option').forEach(opt => {
                const v = opt.value?.trim();
                const id = opt.getAttribute('data-id');
                if (v) map.set(v, id);
            });
            return map;
        }

        let map = buildMap();

        input.addEventListener('input', () => {
            const v = input.value.trim();
            if (map.has(v)) {
                hidden.value = map.get(v);
            } else {
                hidden.value = '';
            }
        });

        const obs = new MutationObserver(() => { map = buildMap(); });
        obs.observe(datalist, { childList: true, subtree: true });
    }
    wireDatalist('location_input', 'locations-list', 'location_id');
</script>

</main>

</body>
</html>
