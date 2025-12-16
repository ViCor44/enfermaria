

<style>
.about-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 2rem;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 22px rgba(0,0,0,0.08);
    font-family: system-ui, sans-serif;
}

.about-header {
    text-align: center;
    margin-bottom: 2rem;
}

.about-header img {
    width: 110px;
    margin-bottom: 1rem;
}

.about-header h1 {
    font-size: 2rem;
    margin: 0;
    color: #1f3c88;
}

.about-header p {
    font-size: 1.1rem;
    color: #555;
}

.section {
    margin-top: 2rem;
}

.section h2 {
    font-size: 1.4rem;
    color: #1f6feb;
    margin-bottom: .5rem;
    border-left: 5px solid #1f6feb;
    padding-left: .7rem;
}

.section p {
    font-size: 1rem;
    color: #444;
    line-height: 1.6;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-top: 1.2rem;
}

.value-card {
    background: #f3f6fb;
    padding: 1rem;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.value-card h3 {
    margin: 0;
    color: #1f6feb;
    font-size: 1.1rem;
}

.value-card p {
    color: #555;
    font-size: .95rem;
}

.footer-note {
    text-align: center;
    margin-top: 3rem;
    font-size: .9rem;
    color: #777;
}

.icon {
    width: 18px;
    vertical-align: middle;
    margin-right: 8px;
    opacity: .7;
}

.icon-sae {
    width: 18px;
    height: 18px;
    vertical-align: middle;
}


</style>

<div class="about-container">

    <div class="about-header">
        <img src="/enfermaria/public/assets/img/logo-sae.png" alt="SAE Logo">
        <h1>Sobre o SAE</h1>
        <p>Sistema de Apoio à Enfermaria do Parque Aquático</p>
    </div>

    <div class="section">
        <h2>O que é o SAE?</h2>
        <p>
            O SAE é uma plataforma moderna desenvolvida para apoiar o trabalho da enfermaria do Parque Aquático,
            garantindo uma gestão eficiente, segura e rápida de acidentes, tratamentos e registos clínicos internos.
            O sistema foi construído com foco na simplicidade de utilização, na segurança dos dados e no cumprimento das normas RGPD.
        </p>
    </div>

    <div class="section">
        <h2>Principais Funcionalidades</h2>
        <ul>
            <li><img class="icon-sae" src="/enfermaria/public/assets/icons/incident.svg" alt=""> Registo de acidentes em tempo real</li>
            <li><img class="icon-sae" src="/enfermaria/public/assets/icons/treatment.svg" alt=""> Gestão completa de tratamentos</li>
            <li><img class="icon-sae" src="/enfermaria/public/assets/icons/users.svg" alt=""> Perfis de utilizador</li>
            <li><img class="icon-sae" src="/enfermaria/public/assets/icons/integration.svg" alt=""> Integração de dados de utentes</li>
            <li><img class="icon-sae" src="/enfermaria/public/assets/icons/stats.svg" alt=""> Estatísticas automáticas</li>
            <li><img class="icon-sae" src="/enfermaria/public/assets/icons/lock.svg" alt=""> Segurança e RGPD</li>
        </ul>
    </div>

    <div class="section">
        <h2>Valores do Sistema</h2>
        <div class="values-grid">
            <div class="value-card">
                <h3>Segurança</h3>
                <p>Todos os dados clínicos são protegidos com rigor e armazenados de forma segura.</p>
            </div>
            <div class="value-card">
                <h3>Eficiência</h3>
                <p>Registos rápidos e automáticos para facilitar o trabalho diário da equipa de enfermagem.</p>
            </div>
            <div class="value-card">
                <h3>Organização</h3>
                <p>Informação centralizada, clara e acessível para melhor tomada de decisão.</p>
            </div>
            <div class="value-card">
                <h3>Modernização</h3>
                <p>Substituição definitiva de processos manuais por ferramentas inteligentes.</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Desenvolvimento</h2>
        <p>
            O SAE foi desenvolvido internamente para responder às necessidades reais da equipa de enfermaria,
            permitindo melhorias contínuas, expansão de funcionalidades e total adaptação operacional.
        </p>
    </div>

    <p class="footer-note">© <?= date('Y') ?> SAE — Sistema de Apoio à Enfermaria. Todos os direitos reservados.</p>

</div>
