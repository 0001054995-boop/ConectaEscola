document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. LÓGICA DA TELA DE LOGIN
    // ==========================================

    const loginForm =
        document.querySelector('.login-form') ||
        document.getElementById('login-form');

    const loginError =
        document.querySelector('.form-message.error') ||
        document.getElementById('login-error');


    // Oculta a mensagem de erro ao carregar a página
    if (loginError) {
        loginError.style.display = 'none';
    }


    if (loginForm) {

        loginForm.addEventListener('submit', async (event) => {

            event.preventDefault();


            const unidadeInput =
                document.getElementById('unidade');

            const usuarioInput =
                document.getElementById('usuario');

            const senhaInput =
                document.getElementById('senha');


            // Verifica se os campos existem
            if (
                !unidadeInput ||
                !usuarioInput ||
                !senhaInput
            ) {
                console.error(
                    'Campos do formulário de login não encontrados.'
                );

                return;
            }


            const unidade =
                unidadeInput.value;

            const usuario =
                usuarioInput.value.trim();

            const senha =
                senhaInput.value.trim();


            // ==========================================
            // VALIDAÇÃO DOS CAMPOS
            // ==========================================

            if (
                !unidade ||
                !usuario ||
                !senha
            ) {

                if (loginError) {

                    loginError.style.display = 'block';

                    const errorText =
                        document.getElementById('error-text');

                    if (errorText) {
                        errorText.textContent =
                            'Preencha todos os campos.';
                    }
                }

                return;
            }


            // Esconde a mensagem de erro
            if (loginError) {
                loginError.style.display = 'none';
            }


            // ==========================================
            // ENVIO PARA O LOGINCONTROLLER.PHP
            // ==========================================

            const formData =
                new FormData(loginForm);


            try {

                const response = await fetch(
                    '../controllers/LoginController.php',
                    {
                        method: 'POST',
                        body: formData
                    }
                );


                // Verifica se o servidor respondeu corretamente
                if (!response.ok) {

                    throw new Error(
                        `Erro do servidor: ${response.status}`
                    );

                }


                const resultado =
                    await response.json();


                // ==========================================
                // LOGIN APROVADO
                // ==========================================

                if (resultado.success) {

                    if (loginError) {
                        loginError.style.display = 'none';
                    }


                    // Redireciona para a página
                    // indicada pelo LoginController.php
                    window.location.href =
                        resultado.redirect ||
                        'consulta.html';

                }


                // ==========================================
                // LOGIN RECUSADO
                // ==========================================

                else {

                    if (loginError) {

                        loginError.style.display =
                            'block';


                        const errorText =
                            document.getElementById(
                                'error-text'
                            );


                        if (errorText) {

                            errorText.textContent =
                                resultado.message ||
                                'Não foi possível realizar o login.';

                        }

                    }

                }


            } catch (error) {

                console.error(
                    'Erro no login:',
                    error
                );


                if (loginError) {

                    loginError.style.display =
                        'block';


                    const errorText =
                        document.getElementById(
                            'error-text'
                        );


                    if (errorText) {

                        errorText.textContent =
                            'Erro ao conectar com o servidor.';

                    }

                }

            }

        });

    }



    // ==========================================
    // 2. LÓGICA DA TELA DE CONSULTA DE AULAS
    // ==========================================

    const filterForm =
        document.getElementById('filter-form');

    const resultsBody =
        document.getElementById('results-body');

    const resultCount =
        document.getElementById('result-count');


    // Exibe orientação inicial
    exibirMensagemOrientacao();


    // ==========================================
    // ENVIO DOS FILTROS
    // ==========================================

    if (filterForm) {

        filterForm.addEventListener(
            'submit',
            (event) => {

                event.preventDefault();


                const formData =
                    new FormData(filterForm);


                // Verifica se pelo menos um filtro
                // foi preenchido
                let temFiltroPreenchido = false;


                for (
                    let [chave, valor]
                    of formData.entries()
                ) {

                    if (
                        typeof valor === 'string' &&
                        valor.trim() !== ''
                    ) {

                        temFiltroPreenchido = true;

                        break;

                    }

                }


                // Se nenhum filtro foi preenchido
                if (!temFiltroPreenchido) {

                    exibirMensagemOrientacao();

                    return;

                }


                const queryParams =
                    new URLSearchParams(
                        formData
                    ).toString();


                carregarAulas(queryParams);

            }
        );

    }



    // ==========================================
    // CARREGAR AULAS
    // ==========================================

    async function carregarAulas(
        params = ''
    ) {

        const url =
            `../controllers/ConsultaController.php?${params}`;


        exibirMensagemCarregando();


        try {

            const response =
                await fetch(url);


            if (!response.ok) {

                throw new Error(
                    `Erro ${response.status}: O arquivo ConsultaController.php não foi encontrado no caminho requisitado.`
                );

            }


            const resultado =
                await response.json();


            if (resultado.success) {

                renderizarTabela(
                    resultado.data
                );

            }

            else {

                exibirMensagemErro(
                    resultado.message ||
                    'Erro ao processar dados.'
                );

            }


        } catch (error) {

            console.error(
                'Erro de requisição:',
                error
            );


            exibirMensagemErro(
                error.message
            );

        }

    }



    // ==========================================
    // RENDERIZAR TABELA
    // ==========================================

    function renderizarTabela(aulas) {

        if (!resultsBody) {
            return;
        }


        resultsBody.innerHTML = '';


        // Atualiza quantidade de resultados
        if (resultCount) {

            const quantidade =
                aulas.length;


            resultCount.textContent =
                `${quantidade} ${
                    quantidade === 1
                        ? 'resultado'
                        : 'resultados'
                }`;

        }


        // Nenhum resultado
        if (aulas.length === 0) {

            resultsBody.innerHTML = `
                <tr>
                    <td
                        colspan="6"
                        style="text-align: center; padding: 20px;">

                        Nenhum horário encontrado
                        para os filtros selecionados.

                    </td>
                </tr>
            `;

            return;

        }


        // Cria as linhas da tabela
        aulas.forEach(aula => {

            const tr =
                document.createElement('tr');


            tr.innerHTML = `
                <td>${aula.data || '-'}</td>

                <td>${aula.horario || '-'}</td>

                <td>${aula.turma || '-'}</td>

                <td>${aula.instrutor || '-'}</td>

                <td>${aula.sala || '-'}</td>

                <td>
                    <span
                        class="badge ${getBadgeClass(
                            aula.situacao
                        )}">

                        ${aula.situacao || '-'}

                    </span>
                </td>
            `;


            resultsBody.appendChild(tr);

        });

    }



    // ==========================================
    // MENSAGEM INICIAL
    // ==========================================

    function exibirMensagemOrientacao() {

        if (!resultsBody) {
            return;
        }


        resultsBody.innerHTML = `
            <tr>

                <td
                    colspan="6"
                    style="
                        text-align: center;
                        padding: 20px;
                        color: #666;
                    ">

                    Preencha os campos acima e clique
                    em "Filtrar" para realizar a consulta.

                </td>

            </tr>
        `;


        if (resultCount) {

            resultCount.textContent =
                '0 resultados';

        }

    }



    // ==========================================
    // MENSAGEM DE CARREGAMENTO
    // ==========================================

    function exibirMensagemCarregando() {

        if (!resultsBody) {
            return;
        }


        resultsBody.innerHTML = `
            <tr>

                <td
                    colspan="6"
                    style="
                        text-align: center;
                        padding: 20px;
                    ">

                    Carregando dados...

                </td>

            </tr>
        `;

    }



    // ==========================================
    // MENSAGEM DE ERRO
    // ==========================================

    function exibirMensagemErro(mensagem) {

        if (!resultsBody) {
            return;
        }


        resultsBody.innerHTML = `
            <tr>

                <td
                    colspan="6"
                    style="
                        text-align: center;
                        color: #d9534f;
                        padding: 20px;
                    ">

                    ${mensagem}

                </td>

            </tr>
        `;


        if (resultCount) {

            resultCount.textContent =
                '0 resultados';

        }

    }



    // ==========================================
    // CLASSE DOS STATUS
    // ==========================================

    function getBadgeClass(situacao) {

        if (!situacao) {
            return '';
        }


        const status =
            situacao.toLowerCase();


        if (
            status.includes('confirm')
        ) {

            return 'badge-success';

        }


        if (
            status.includes('cancel')
        ) {

            return 'badge-danger';

        }


        if (
            status.includes('encerr')
        ) {

            return 'badge-secondary';

        }


        return '';

    }

});