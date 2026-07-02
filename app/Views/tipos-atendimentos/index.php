<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="h3">Tipos de Atendimento</h1>
        </div>
        <div class="col-auto">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTipo">
                + Novo Tipo
            </button>
        </div>
    </div>

    <div id="alertContainer"></div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tipo -->
<div class="modal fade" id="modalTipo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Novo Tipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTipo" onsubmit="salvarTipo(event)">
                <div class="modal-body">
                    <input type="hidden" id="tipoId" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="status">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modal;

function initModal() {
    modal = new bootstrap.Modal(document.getElementById('modalTipo'), {});
}

async function carregarTipos() {
    try {
        const data = await AtendeLabApi.get('tipos', 'listar');
        const tipos = AtendeLabApi.toList(data);
        const tbody = document.getElementById('tbody');
        tbody.innerHTML = '';

        tipos.forEach(tipo => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${AtendeLabApi.escape(tipo.nome)}</td>
                <td>${AtendeLabApi.escape(tipo.descricao || '')}</td>
                <td>
                    <span class="badge bg-${tipo.status === 'ativo' ? 'success' : 'danger'}">
                        ${AtendeLabApi.escape(tipo.status)}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-success" onclick="editarTipo(${tipo.id})">Editar</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="inativarTipo(${tipo.id})">Inativar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function editarTipo(id) {
    try {
        const data = await AtendeLabApi.get('tipos', 'buscar', { id });
        const tipo = AtendeLabApi.toObject(data);
        
        document.getElementById('tipoId').value = tipo.id;
        document.getElementById('nome').value = tipo.nome;
        document.getElementById('descricao').value = tipo.descricao || '';
        document.getElementById('status').value = tipo.status;
        document.getElementById('modalTitle').textContent = 'Editar Tipo';
        
        if (modal) modal.show();
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function salvarTipo(event) {
    event.preventDefault();
    
    const id = document.getElementById('tipoId').value;
    const dados = {
        nome: document.getElementById('nome').value,
        descricao: document.getElementById('descricao').value,
        status: document.getElementById('status').value
    };

    try {
        if (id) {
            dados.id = id;
            await AtendeLabApi.post('tipos', 'atualizar', dados);
            AtendeLabApi.showAlert('alertContainer', 'Tipo atualizado com sucesso!', 'success');
        } else {
            await AtendeLabApi.post('tipos', 'criar', dados);
            AtendeLabApi.showAlert('alertContainer', 'Tipo criado com sucesso!', 'success');
        }
        
        document.getElementById('formTipo').reset();
        document.getElementById('tipoId').value = '';
        document.getElementById('modalTitle').textContent = 'Novo Tipo';
        if (modal) modal.hide();
        carregarTipos();
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function inativarTipo(id) {
    if (!confirm('Tem certeza que deseja inativar este tipo?')) {
        return;
    }

    try {
        await AtendeLabApi.post('tipos', 'inativar', { id });
        AtendeLabApi.showAlert('alertContainer', 'Tipo inativado com sucesso!', 'success');
        carregarTipos();
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initModal();
    carregarTipos();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
