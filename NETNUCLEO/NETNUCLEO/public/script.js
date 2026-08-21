console.log("JavaScript do ConectaEscola carregado!");

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. LÓGICA DA TELA DE LOGIN
    // ==========================================

    const loginForm = document.querySelector('.login-form');
    const formMessage = document.querySelector('.form-message');

    if (formMessage && loginForm) {
        formMessage.style.display = 'none';
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const unidade = document.querySelector('#unidade')?.value.trim();
            const usuario = document.querySelector('#usuario')?.value.trim();
            const senha = document.querySelector('#senha')?.value.trim();

            if (!unidade || !usuario || !senha) {
                exibirMensagem(
                    formMessage,
                    'Por favor, preencha todos os campos do formulário.',
                    'erro'
                );
                return;
            }

            const formData = new FormData(loginForm);

            try {
                const response = await fetch('../controll/LoginController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    exibirMensagem(
                        formMessage,
                        result.message || 'Login realizado com sucesso! Redirecionando...',
                        'sucesso'
                    );

                    setTimeout(() => {
                        window.location.href = result.redirect || 'consulta.html';
                    }, 1500);

                } else {
                    exibirMensagem(
                        formMessage,
                        result.message || 'Usuário ou senha inválidos.',
                        'erro'
                    );
                }

            } catch (error) {
                console.error('Erro de conexão:', error);

                exibirMensagem(
                    formMessage,
                    'Erro de conexão com o servidor. Tente novamente.',
                    'erro'
                );
            }
        });
    }


    // ==========================================
    // 2. LÓGICA DA TELA DE CONSULTA
    // ==========================================

    const filterForm = document.querySelector('.filter-form');

    if (filterForm) {

        filterForm.addEventListener('submit', async (event) => {

            event.preventDefault();

            // ------------------------------------------
            // Captura dos elementos
            // ------------------------------------------

            const tabelaBody = document.querySelector('table tbody');
            const resultCount = document.querySelector('.result-count');

            const dataInicial = document.querySelector('#data-inicial');
            const dataFinal = document.querySelector('#data-final');
            const periodo = document.querySelector('#periodo');
            const instrutor = document.querySelector('#instrutor');
            const turma = document.querySelector('#turma');
            const encerradas = document.querySelector('#encerradas');


            // ==========================================
            // VALIDAÇÃO 1
            // Pelo menos um filtro precisa ser preenchido
            // ==========================================

            const temDataInicial = dataInicial && dataInicial.value;
            const temDataFinal = dataFinal && dataFinal.value;
            const temPeriodo = periodo && periodo.value;
            const temInstrutor = instrutor && instrutor.value;
            const temTurma = turma && turma.value;
            const mostrarEncerradas = encerradas && encerradas.checked;

            if (
                !temDataInicial &&
                !temDataFinal &&
                !temPeriodo &&
                !temInstrutor &&
                !temTurma &&
                !mostrarEncerradas
            ) {
                alert(
                    'Por favor, preencha ou selecione pelo menos um filtro para realizar a pesquisa.'
                );
                return;
            }


            // ==========================================
            // VALIDAÇÃO 2
            // Data inicial e data final devem ser usadas juntas
            // ==========================================

            if (temDataInicial && !temDataFinal) {
                alert(
                    'Informe a data final para completar o período da pesquisa.'
                );

                dataFinal.focus();
                return;
            }

            if (!temDataInicial && temDataFinal) {
                alert(
                    'Informe a data inicial para completar o período da pesquisa.'
                );

                dataInicial.focus();
                return;
            }


            // ==========================================
            // VALIDAÇÃO 3
            // Data inicial não pode ser maior que a data final
            // ==========================================

            if (temDataInicial && temDataFinal) {

                const inicio = new Date(dataInicial.value);
                const fim = new Date(dataFinal.value);

                if (inicio > fim) {

                    alert(
                        'A data inicial não pode ser maior que a data final.'
                    );

                    dataInicial.focus();
                    return;
                }
            }


            // ==========================================
            // VALIDAÇÃO 4
            // Verificar se os campos de seleção
            // possuem valores válidos
            // ==========================================

            if (
                periodo &&
                periodo.value &&
                !['Manhã', 'Tarde', 'Noite'].includes(periodo.value)
            ) {
                alert('Selecione um período válido.');
                periodo.focus();
                return;
            }


            // ==========================================
            // Todos os filtros passaram pelas validações
            // ==========================================

            console.log('Filtros validados com sucesso!');


            // ==========================================
            // Indicação visual de carregamento
            // ==========================================

            if (tabelaBody) {
                tabelaBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">
                            <em>Buscando resultados...</em>
                        </td>
                    </tr>
                `;
            }


            // ==========================================
            // Envio dos filtros para o PHP
            // ==========================================

            try {

                const searchParams = new URLSearchParams(
                    new FormData(filterForm)
                );

                const response = await fetch(
                    `../controll/ConsultaController.php?${searchParams.toString()}`
                );

                if (!response.ok) {
                    throw new Error(
                        `Erro HTTP: ${response.status}`
                    );
                }

                const resultado = await response.json();

                if (!resultado.success) {
                    throw new Error(
                        resultado.message || 'Erro ao realizar a consulta.'
                    );
                }

                renderizarTabela(
                    resultado.data,
                    tabelaBody,
                    resultCount
                );

            } catch (error) {

                console.error(
                    'Erro ao consultar dados:',
                    error
                );

                if (tabelaBody) {
                    tabelaBody.innerHTML = `
                        <tr>
                            <td colspan="6"
                                style="text-align: center; color: #721c24; padding: 15px;">
                                Erro ao conectar com o servidor para realizar a consulta.
                            </td>
                        </tr>
                    `;
                }

                if (resultCount) {
                    resultCount.textContent = 'Erro na consulta';
                }
            }
        });
    }
});


// ==========================================
// 3. RENDERIZAÇÃO DA TABELA
// ==========================================

function renderizarTabela(lista, tabelaBody, resultCount) {

    if (!tabelaBody) return;

    tabelaBody.innerHTML = '';

    if (!lista || lista.length === 0) {

        tabelaBody.innerHTML = `
            <tr>
                <td colspan="6"
                    style="text-align: center; color: #721c24; padding: 15px;">
                    Nenhum resultado encontrado.
                </td>
            </tr>
        `;

        if (resultCount) {
            resultCount.textContent = '0 resultados';
        }

        return;
    }


    lista.forEach(item => {

        const linha = document.createElement('tr');

        const badgeClass =
            item.situacao === 'Ativa'
                ? 'badge-success'
                : 'badge-danger';

        linha.innerHTML = `
            <td>${item.data}</td>
            <td>${item.horario}</td>
            <td>${item.turma}</td>
            <td>${item.instrutor}</td>
            <td>${item.sala}</td>
            <td>
                <span class="badge ${badgeClass}">
                    ${item.situacao}
                </span>
            </td>
        `;

        tabelaBody.appendChild(linha);
    });


    if (resultCount) {

        resultCount.textContent =
            `${lista.length} resultado${lista.length > 1 ? 's' : ''}`;
    }
}


// ==========================================
// 4. MENSAGENS DO LOGIN
// ==========================================

function exibirMensagem(elemento, texto, tipo) {

    if (!elemento) return;

    elemento.style.display = 'block';

    if (tipo === 'sucesso') {

        elemento.className = 'form-message success';
        elemento.style.backgroundColor = '#d4edda';
        elemento.style.color = '#155724';
        elemento.style.border = '1px solid #c3e6cb';

        elemento.innerHTML =
            `<strong>Sucesso:</strong> ${texto}`;

    } else {

        elemento.className = 'form-message error';
        elemento.style.backgroundColor = '#f8d7da';
        elemento.style.color = '#721c24';
        elemento.style.border = '1px solid #f5c6cb';

        elemento.innerHTML =
            `<strong>Erro:</strong> ${texto}`;
    }
}