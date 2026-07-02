window.AtendeLabApi = (() => {
  const baseUrl = '/atendelab/public/';

  async function request(controller, action, { method = 'GET', query = {}, body = null } = {}) {
    const params = new URLSearchParams({ controller, action, ...query });
    const options = { method, credentials: 'same-origin' };

    if (method !== 'GET' && body !== null) {
      const form = body instanceof FormData ? body : objectToFormData(body);
      options.body = new URLSearchParams([...form.entries()]);
      options.headers = { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' };
    }

    const response = await fetch(`${baseUrl}?${params.toString()}`, options);
    const text = await response.text();
    let data;

    try {
      data = text ? JSON.parse(text) : {};
    } catch {
      throw new Error(text || 'Resposta inválida recebida do backend.');
    }

    if (!response.ok || data.erro) {
      throw new Error(data.erro || data.mensagem || `Erro HTTP ${response.status}`);
    }

    return data;
  }

  function objectToFormData(obj) {
    const form = new FormData();
    for (const [key, value] of Object.entries(obj)) {
      form.append(key, String(value ?? ''));
    }
    return form;
  }

  function toList(data) {
    if (Array.isArray(data)) return data;
    for (const key of ['dados', 'items', 'registros', 'pessoas', 'tipos', 'atendimentos', 'usuarios']) {
      if (Array.isArray(data?.[key])) return data[key];
    }
    return [];
  }

  function toObject(data) {
    if (!data || typeof data !== 'object') return {};
    for (const key of ['dados', 'item', 'registro', 'pessoa', 'tipo', 'atendimento', 'usuario']) {
      if (data[key] && typeof data[key] === 'object' && !Array.isArray(data[key])) {
        return data[key];
      }
    }
    return data;
  }

  function escape(value) {
    return String(value ?? '').replace(/[&<>'"]/g, char => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#039;',
      '"': '&quot;'
    })[char]);
  }

  function escapeAttr(value) {
    return escape(value).replace(/`/g, '&#096;');
  }

  function showAlert(id, message, type = 'success') {
    const element = document.getElementById(id);
    if (!element) return;
    element.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${escape(message)}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
  }

  return {
    get: (controller, action, query = {}) => request(controller, action, { query }),
    post: (controller, action, body = {}) => request(controller, action, { method: 'POST', body }),
    toList,
    toObject,
    escape,
    escapeAttr,
    showAlert
  };
})();
