document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');

    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const usuario = document.getElementById('usuario')?.value.trim() || '';
            const senha = document.getElementById('senha')?.value || '';
            const unidade = document.getElementById('unidade')?.value.trim() || '';
            const errorText = document.getElementById('error-text');
            if (!usuario || !senha || !unidade) {
                mostrarErroLogin('Preencha todos os campos.');
                return;
            }
            if (loginError) loginError.style.display = 'none';
            try {
                const response = await fetch('../controllers/LoginController.php', { method: 'POST', body: new FormData(loginForm) });
                const resultado = await response.json();
                if (resultado.success) {
                    window.location.href = resultado.redirect || 'index.html';
                } else {
                    mostrarErroLogin(resultado.message || 'Login inválido.');
                }
            } catch (error) {
                console.error(error);
                mostrarErroLogin('Erro ao conectar com o servidor. Verifique se o Apache e o MySQL estão ativos.');
            }
            function mostrarErroLogin(mensagem) {
                if (loginError) loginError.style.display = 'block';
                if (errorText) errorText.textContent = mensagem;
            }
        });
    }

    // Nesta versão de demonstração, as três telas ficam acessíveis sem login.
    // As consultas continuam buscando os dados diretamente no banco MySQL.

    const filterForm = document.getElementById('filter-form');
    const resultsBody = document.getElementById('results-body');
    const resultCount = document.getElementById('result-count');

    if (filterForm) {
        carregarOpcoes();
        exibirMensagemCarregando();
        carregarAulas('');
        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const params = new URLSearchParams();
            const formData = new FormData(filterForm);
            for (const [chave, valor] of formData.entries()) {
                if (typeof valor === 'string' && valor.trim() !== '') params.set(chave, valor.trim());
            }
            carregarAulas(params.toString());
        });
        document.getElementById('limpar-filtros')?.addEventListener('click', () => {
            filterForm.reset();
            exibirMensagemOrientacao();
        });
    }

    async function carregarOpcoes() {
        try {
            const response = await fetch('../controllers/opcoes_consulta.php');
            const resultado = await response.json();
            if (!resultado.success) return;
            preencherSelect('turma', resultado.turmas, item => [`${item.id}`, `Turma ${item.codigo} - ${item.turno}`]);
            preencherSelect('instrutor', resultado.instrutores, item => [`${item.id}`, item.nome]);
            preencherSelect('materia', resultado.materias, item => [`${item.id}`, `${item.sigla} - ${item.nome}`]);
        } catch (error) {
            console.error('Erro ao carregar filtros:', error);
        }
    }

    function preencherSelect(id, itens, mapear) {
        const select = document.getElementById(id);
        if (!select) return;
        itens.forEach(item => {
            const [value, text] = mapear(item);
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            select.appendChild(option);
        });
    }

    async function carregarAulas(params) {
        exibirMensagemCarregando();
        try {
            const response = await fetch(`../controllers/ConsultaController.php?${params}`);
            const texto = await response.text();
            let resultado;
            try { resultado = JSON.parse(texto); } catch (e) { throw new Error('O servidor retornou um erro PHP em vez de JSON. Confira o Apache, PHP e o banco de dados.'); }
            if (!resultado.success) throw new Error(resultado.message || 'Erro ao consultar.');
            renderizarTabela(resultado.data || []);
        } catch (error) {
            console.error(error);
            exibirMensagemErro(error.message);
        }
    }

    function renderizarTabela(aulas) {
        if (!resultsBody) return;
        resultsBody.innerHTML = '';
        if (resultCount) resultCount.textContent = `${aulas.length} ${aulas.length === 1 ? 'resultado' : 'resultados'}`;
        if (!aulas.length) {
            resultsBody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;">Nenhum horário encontrado para os filtros selecionados.</td></tr>';
            return;
        }
        aulas.forEach(aula => {
            const tr = document.createElement('tr');
            [aula.data, aula.horario, aula.turma, aula.instrutor, aula.materia, aula.sala].forEach(valor => {
                const td = document.createElement('td');
                td.textContent = valor || '-';
                tr.appendChild(td);
            });
            const tdSituacao = document.createElement('td');
            const badge = document.createElement('span');
            badge.className = `badge ${aula.statusAula == 1 ? 'badge-success' : 'badge-danger'}`;
            badge.textContent = aula.situacao || '-';
            tdSituacao.appendChild(badge);
            tr.appendChild(tdSituacao);
            resultsBody.appendChild(tr);
        });
    }

    function exibirMensagemOrientacao() {
        if (resultsBody) resultsBody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:#666;">Preencha os filtros e clique em "Filtrar" para consultar o banco de dados.</td></tr>';
        if (resultCount) resultCount.textContent = '0 resultados';
    }
    function exibirMensagemCarregando() {
        if (resultsBody) resultsBody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;">Consultando banco de dados...</td></tr>';
    }
    function exibirMensagemErro(mensagem) {
        if (resultsBody) resultsBody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:20px;">${escapeHtml(mensagem)}</td></tr>`;
        if (resultCount) resultCount.textContent = 'Erro';
    }
    function escapeHtml(text) {
        const div = document.createElement('div'); div.textContent = text; return div.innerHTML;
    }
});
