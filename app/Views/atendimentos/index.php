<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="h3">Atendimentos</h1>
        </div>
        <div class="col-auto">
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalAtendimento">
                + Novo Atendimento
            </button>
        </div>
    </div>

    <div id="alertContainer"></div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Pessoa</th>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Atendimento -->
<div class="modal fade" id="modalAtendimento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Atendimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAtendimento" onsubmit="salvarAtendimento(event)">
                <div class="modal-body">
                    <input type="hidden" id="atendimentoId" value="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pessoa *</label>
                            <select class="form-select" id="pessoa_id" required>
                                <option value="">Selecione uma pessoa</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Atendimento *</label>
                            <select class="form-select" id="tipo_atendimento_id" required>
                                <option value="">Selecione um tipo</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data *</label>
                            <input type="date" class="form-control" id="data_atendimento" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Horário *</label>
                            <input type="time" class="form-control" id="horario_atendimento" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição *</label>
                        <textarea class="form-control" id="descricao" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-warning">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Alterar Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formStatus" onsubmit="alterarStatus(event)">
                <div class="modal-body">
                    <input type="hidden" id="statusAtendimentoId" value="">

                    <div class="mb-3">
                        <label class="form-label">Novo Status *</label>
                        <select class="form-select" id="novoStatus" required>
                            <option value="">Selecione um status</option>
                            <option value="aberto">Aberto</option>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="concluido">Concluído</option>
                        </select>
                    </div>

                    <div class="mb-3" id="observacaoContainer" style="display: none;">
                        <label class="form-label">Observação (obrigatória ao concluir)</label>
                        <textarea class="form-control" id="observacao_final"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-warning">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalAtendimento;
let modalStatus;

function initModals() {
    modalAtendimento = new bootstrap.Modal(document.getElementById('modalAtendimento'), {});
    modalStatus = new bootstrap.Modal(document.getElementById('modalStatus'), {});
}

async function carregarCombos() {
    try {
        // Carregando pessoas
        const dataPessoas = await AtendeLabApi.get('pessoas', 'listar');
        const pessoas = AtendeLabApi.toList(dataPessoas).filter(p => p.status === 'ativo');
        
        const selectPessoa = document.getElementById('pessoa_id');
        selectPessoa.innerHTML = '<option value="">Selecione uma pessoa</option>';
        pessoas.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = AtendeLabApi.escape(p.nome);
            selectPessoa.appendChild(opt);
        });

        // Carregando tipos
        const dataTipos = await AtendeLabApi.get('tipos', 'listar');
        const tipos = AtendeLabApi.toList(dataTipos).filter(t => t.status === 'ativo');
        
        const selectTipo = document.getElementById('tipo_atendimento_id');
        selectTipo.innerHTML = '<option value="">Selecione um tipo</option>';
        tipos.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = AtendeLabApi.escape(t.nome);
            selectTipo.appendChild(opt);
        });
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function carregarAtendimentos() {
    try {
        const data = await AtendeLabApi.get('atendimentos', 'listar');
        const atendimentos = AtendeLabApi.toList(data);
        const tbody = document.getElementById('tbody');
        tbody.innerHTML = '';

        atendimentos.forEach(atendimento => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${AtendeLabApi.escape(atendimento.pessoa_nome || 'N/A')}</td>
                <td>${AtendeLabApi.escape(atendimento.tipo_nome || 'N/A')}</td>
                <td>${AtendeLabApi.escape(atendimento.data_atendimento || '')}</td>
                <td>${AtendeLabApi.escape(atendimento.horario_atendimento || '')}</td>
                <td>${AtendeLabApi.escape(atendimento.usuario_nome || 'N/A')}</td>
                <td>
                    <span class="badge bg-${
                        atendimento.status === 'concluido' ? 'success' : 
                        atendimento.status === 'em_andamento' ? 'info' : 'secondary'
                    }">
                        ${AtendeLabApi.escape(atendimento.status)}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-warning" onclick="editarStatus(${atendimento.id})">Status</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function salvarAtendimento(event) {
    event.preventDefault();
    
    const dados = {
        pessoa_id: document.getElementById('pessoa_id').value,
        tipo_atendimento_id: document.getElementById('tipo_atendimento_id').value,
        data_atendimento: document.getElementById('data_atendimento').value,
        horario_atendimento: document.getElementById('horario_atendimento').value,
        descricao: document.getElementById('descricao').value
    };

    if (!dados.pessoa_id || !dados.tipo_atendimento_id || !dados.data_atendimento || !dados.horario_atendimento || !dados.descricao) {
        AtendeLabApi.showAlert('alertContainer', 'Todos os campos obrigatórios devem ser preenchidos', 'danger');
        return;
    }

    try {
        await AtendeLabApi.post('atendimentos', 'criar', dados);
        AtendeLabApi.showAlert('alertContainer', 'Atendimento criado com sucesso!', 'success');
        
        document.getElementById('formAtendimento').reset();
        if (modalAtendimento) modalAtendimento.hide();
        carregarAtendimentos();
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function editarStatus(id) {
    document.getElementById('statusAtendimentoId').value = id;
    document.getElementById('novoStatus').value = '';
    document.getElementById('observacao_final').value = '';
    document.getElementById('observacaoContainer').style.display = 'none';
    if (modalStatus) modalStatus.show();
}

document.getElementById('novoStatus').addEventListener('change', function() {
    const observacaoContainer = document.getElementById('observacaoContainer');
    observacaoContainer.style.display = this.value === 'concluido' ? 'block' : 'none';
});

async function alterarStatus(event) {
    event.preventDefault();
    
    const novoStatus = document.getElementById('novoStatus').value;
    const observacao = document.getElementById('observacao_final').value;

    if (novoStatus === 'concluido' && !observacao.trim()) {
        AtendeLabApi.showAlert('alertContainer', 'Observação é obrigatória ao concluir', 'danger');
        return;
    }

    const dados = {
        id: document.getElementById('statusAtendimentoId').value,
        status: novoStatus
    };

    if (observacao) {
        dados.observacao_final = observacao;
    }

    try {
        await AtendeLabApi.post('atendimentos', 'alterarStatus', dados);
        AtendeLabApi.showAlert('alertContainer', 'Status atualizado com sucesso!', 'success');
        
        if (modalStatus) modalStatus.hide();
        carregarAtendimentos();
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initModals();
    carregarCombos();
    carregarAtendimentos();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
