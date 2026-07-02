<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Pessoas</h5>
                <h2 id="totalPessoas" class="text-primary">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Tipos de Atendimento</h5>
                <h2 id="totalTipos" class="text-success">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Atendimentos</h5>
                <h2 id="totalAtendimentos" class="text-warning">0</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h3>Acesso Rápido</h3>
        <div class="row">
            <div class="col-md-3 mb-2">
                <a href="?controller=frontend&action=pessoas" class="btn btn-outline-primary w-100">Pessoas</a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="?controller=frontend&action=tipos" class="btn btn-outline-success w-100">Tipos de Atendimento</a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="?controller=frontend&action=atendimentos" class="btn btn-outline-warning w-100">Atendimentos</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  try {
    const response = await AtendeLabApi.get('dashboard', 'resumo');
    document.getElementById('totalPessoas').textContent = response.indicadores?.total_pessoas || 0;
    document.getElementById('totalTipos').textContent = response.indicadores?.total_tipos || 0;
    document.getElementById('totalAtendimentos').textContent = response.indicadores?.total_atendimentos || 0;
  } catch (error) {
    document.querySelectorAll('[id^="total"]').forEach(el => el.textContent = '!');
    console.error(error);
  }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
