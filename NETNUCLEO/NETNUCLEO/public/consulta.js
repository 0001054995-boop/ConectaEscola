document.addEventListener('DOMContentLoaded', () => {
  const role = document.body.dataset.role || 'admin';
  const form = document.getElementById('filter-form');
  const body = document.getElementById('results-body');
  const count = document.getElementById('result-count');

  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }

  function mensagem(t) {
    const cols = role === 'admin' ? 7 : 6;
    body.innerHTML = `<tr><td colspan="${cols}" style="text-align:center;padding:20px;">${esc(t)}</td></tr>`;
  }

  async function opcoes() {
    try {
      const r = await fetch('../controllers/opcoes_consulta.php', {cache:'no-store'});
      const d = await r.json();
      if (!d.success) throw new Error(d.message || 'Não foi possível carregar as opções.');

      const turma = document.getElementById('turma');
      const instrutor = document.getElementById('instrutor');
      const materia = document.getElementById('materia');

      if (turma && d.turmas) d.turmas.forEach(x => turma.insertAdjacentHTML('beforeend', `<option value="${esc(x.id)}">Turma ${esc(x.codigo)} - ${esc(x.turno)}</option>`));
      if (instrutor && d.instrutores) d.instrutores.forEach(x => instrutor.insertAdjacentHTML('beforeend', `<option value="${esc(x.id)}">${esc(x.nome)}</option>`));
      if (materia && d.materias) d.materias.forEach(x => materia.insertAdjacentHTML('beforeend', `<option value="${esc(x.id)}">${esc(x.sigla)} - ${esc(x.nome)}</option>`));
    } catch (err) {
      console.error(err);
    }
  }

  async function carregar() {
    const cols = role === 'admin' ? 7 : 6;
    body.innerHTML = `<tr><td colspan="${cols}" style="text-align:center;padding:20px;">Consultando banco de dados...</td></tr>`;
    const q = new URLSearchParams(new FormData(form));
    for (const [k,v] of [...q.entries()]) if (!v) q.delete(k);

    try {
      const r = await fetch('../controllers/ConsultaController.php?' + q.toString(), {cache:'no-store'});
      const texto = await r.text();
      let d;
      try { d = JSON.parse(texto); }
      catch (e) { throw new Error('O servidor retornou um erro PHP em vez de JSON. Confira o Apache, PHP e o banco de dados.'); }
      if (!d.success) throw new Error(d.message || 'Não foi possível consultar as aulas.');

      body.innerHTML = '';
      count.textContent = `${d.data.length} ${d.data.length === 1 ? 'resultado' : 'resultados'}`;
      if (!d.data.length) { mensagem('Nenhuma aula encontrada para os filtros selecionados.'); return; }

      d.data.forEach(a => {
        const tr = document.createElement('tr');
        [a.data, a.horario, a.turma, a.instrutor, a.materia].forEach(v => {
          const td = document.createElement('td'); td.textContent = v || '-'; tr.appendChild(td);
        });
        if (role === 'admin') {
          const tdSala = document.createElement('td'); tdSala.textContent = a.sala || '-'; tr.appendChild(tdSala);
        }
        const td = document.createElement('td');
        const badge = document.createElement('span');
        badge.className = 'badge ' + (a.statusAula == 1 ? 'badge-success' : 'badge-danger');
        badge.textContent = a.situacao;
        td.appendChild(badge); tr.appendChild(td); body.appendChild(tr);
      });
    } catch (err) {
      mensagem(err.message || 'Erro ao consultar.');
    }
  }

  form.addEventListener('submit', e => { e.preventDefault(); carregar(); });
  document.getElementById('limpar-filtros').addEventListener('click', () => { form.reset(); carregar(); });
  opcoes().finally(carregar);
});
