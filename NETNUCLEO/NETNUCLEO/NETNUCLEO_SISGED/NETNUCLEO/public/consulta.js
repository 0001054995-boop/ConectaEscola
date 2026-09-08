document.addEventListener('DOMContentLoaded', () => {
    const role = document.body.dataset.role || 'admin';
    const form = document.getElementById('filter-form');
    const body = document.getElementById('results-body');
    const count = document.getElementById('result-count');
    if (!form || !body) return;

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({
        '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'
    }[c]));

    function message(text) {
        body.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:20px;">${escapeHtml(text)}</td></tr>`;
        if (count) count.textContent = '0 resultados';
    }

    async function checkSession() {
        const r = await fetch('../controllers/session.php');
        const s = await r.json();
        if (!s.success) { location.href = 'login.html'; return false; }
        if (s.papel !== role) {
            location.href = s.papel === 'admin' ? 'index.html' : s.papel === 'instrutor' ? 'instrutor.html' : 'aluno.html';
            return false;
        }
        return true;
    }

    async function loadOptions() {
        const r = await fetch('../controllers/opcoes_consulta.php');
        const d = await r.json();
        if (!d.success) return;

        if (role === 'admin') {
            fill('turma', d.turmas, x => [`${x.id}`, `Turma ${x.codigo} - ${x.turno}`]);
            fill('instrutor', d.instrutores, x => [`${x.id}`, x.nome]);
            fill('materia', d.materias, x => [`${x.id}`, `${x.sigla} - ${x.nome}`]);
        }
        if (role === 'aluno') {
            fill('turma', d.turmas, x => [`${x.id}`, `Turma ${x.codigo} - ${x.turno}`]);
        }
    }

    function fill(id, items, mapper) {
        const select = document.getElementById(id);
        if (!select) return;
        items.forEach(item => {
            const [value, text] = mapper(item);
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            select.appendChild(option);
        });
    }

    async function search() {
        body.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;">Consultando banco de dados...</td></tr>';
        const q = new URLSearchParams(new FormData(form));
        [...q.entries()].forEach(([k,v]) => { if (!String(v).trim()) q.delete(k); });

        try {
            const r = await fetch('../controllers/ConsultaController.php?' + q.toString());
            const d = await r.json();
            if (r.status === 401) { location.href = 'login.html'; return; }
            if (!d.success) throw new Error(d.message || 'Erro ao consultar.');

            body.innerHTML = '';
            if (count) count.textContent = `${d.data.length} ${d.data.length === 1 ? 'resultado' : 'resultados'}`;
            if (!d.data.length) {
                body.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;">Nenhum horário encontrado para os filtros selecionados.</td></tr>';
                return;
            }

            d.data.forEach(aula => {
                const tr = document.createElement('tr');
                [aula.data, aula.horario, aula.turma, aula.instrutor, aula.materia, aula.sala].forEach(value => {
                    const td = document.createElement('td');
                    td.textContent = value || '-';
                    tr.appendChild(td);
                });
                const td = document.createElement('td');
                const badge = document.createElement('span');
                badge.className = 'badge ' + (Number(aula.statusAula) === 1 ? 'badge-success' : 'badge-danger');
                badge.textContent = aula.situacao || '-';
                td.appendChild(badge);
                tr.appendChild(td);
                body.appendChild(tr);
            });
        } catch (error) {
            message(error.message || 'Erro ao consultar.');
        }
    }

    document.getElementById('limpar-filtros')?.addEventListener('click', () => {
        form.reset();
        message('Preencha os filtros e clique em "Filtrar" para consultar.');
    });

    form.addEventListener('submit', event => {
        event.preventDefault();
        search();
    });

    (async () => {
        if (await checkSession()) {
            await loadOptions();
            message('Preencha os filtros e clique em "Filtrar" para consultar.');
        }
    })();
});
