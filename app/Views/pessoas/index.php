<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1 class="h3">Pessoas</h1>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPessoa">
                + Nova Pessoa
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
                        <th>Documento</th>
                        <th>Email</th>
                        <th>Curso</th>
                        <th>Período</th>
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

<!-- Modal Pessoa -->
<div class="modal fade" id="modalPessoa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nova Pessoa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPessoa" onsubmit="salvarPessoa(event)">
                <div class="modal-body">
                    <input type="hidden" id="pessoaId" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Documento *</label>
                        <input type="text" class="form-control" id="documento" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Curso</label>
                        <input type="text" class="form-control" id="curso">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Período</label>
                        <input type="text" class="form-control" id="periodo">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes"></textarea>
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
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modal;

function initModal() {
    modal = new bootstrap.Modal(document.getElementById('modalPessoa'), {});
}

async function carregarPessoas() {
    try {
        const data = await AtendeLabApi.get('pessoas', 'listar');
        const pessoas = AtendeLabApi.toList(data);
        const tbody = document.getElementById('tbody');
        tbody.innerHTML = '';

        pessoas.forEach(pessoa => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${AtendeLabApi.escape(pessoa.nome)}</td>
                <td>${AtendeLabApi.escape(pessoa.documento)}</td>
                <td>${AtendeLabApi.escape(pessoa.email)}</td>
                <td>${AtendeLabApi.escape(pessoa.curso || '')}</td>
                <td>${AtendeLabApi.escape(pessoa.periodo || '')}</td>
                <td>
                    <span class="badge bg-${pessoa.status === 'ativo' ? 'success' : 'danger'}">
                        ${AtendeLabApi.escape(pessoa.status)}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editarPessoa(${pessoa.id})">Editar</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="inativarPessoa(${pessoa.id})">Inativar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function editarPessoa(id) {
    try {
        const data = await AtendeLabApi.get('pessoas', 'buscar', { id });
        const pessoa = AtendeLabApi.toObject(data);
        
        document.getElementById('pessoaId').value = pessoa.id;
        document.getElementById('nome').value = pessoa.nome;
        document.getElementById('documento').value = pessoa.documento;
        document.getElementById('email').value = pessoa.email;
        document.getElementById('telefone').value = pessoa.telefone || '';
        document.getElementById('curso').value = pessoa.curso || '';
        document.getElementById('periodo').value = pessoa.periodo || '';
        document.getElementById('observacoes').value = pessoa.observacoes || '';
        document.getElementById('status').value = pessoa.status;
        document.getElementById('modalTitle').textContent = 'Editar Pessoa';
        
        if (modal) modal.show();
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function salvarPessoa(event) {
    event.preventDefault();
    
    const id = document.getElementById('pessoaId').value;
    const dados = {
        nome: document.getElementById('nome').value,
        documento: document.getElementById('documento').value,
        email: document.getElementById('email').value,
        telefone: document.getElementById('telefone').value,
        curso: document.getElementById('curso').value,
        periodo: document.getElementById('periodo').value,
        observacoes: document.getElementById('observacoes').value,
        status: document.getElementById('status').value
    };

    try {
        if (id) {
            dados.id = id;
            await AtendeLabApi.post('pessoas', 'atualizar', dados);
            AtendeLabApi.showAlert('alertContainer', 'Pessoa atualizada com sucesso!', 'success');
        } else {
            await AtendeLabApi.post('pessoas', 'criar', dados);
            AtendeLabApi.showAlert('alertContainer', 'Pessoa criada com sucesso!', 'success');
        }
        
        document.getElementById('formPessoa').reset();
        document.getElementById('pessoaId').value = '';
        document.getElementById('modalTitle').textContent = 'Nova Pessoa';
        if (modal) modal.hide();
        carregarPessoas();
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

async function inativarPessoa(id) {
    if (!confirm('Tem certeza que deseja inativar esta pessoa?')) {
        return;
    }

    try {
        await AtendeLabApi.post('pessoas', 'inativar', { id });
        AtendeLabApi.showAlert('alertContainer', 'Pessoa inativada com sucesso!', 'success');
        carregarPessoas();
    } catch (error) {
        AtendeLabApi.showAlert('alertContainer', error.message, 'danger');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initModal();
    carregarPessoas();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
