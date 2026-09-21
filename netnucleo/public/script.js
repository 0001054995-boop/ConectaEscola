document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    const senha = document.getElementById('senha');
    const toggleSenha = document.getElementById('toggle-senha');

    if (toggleSenha && senha) {
        toggleSenha.addEventListener('click', () => {
            const mostrar = senha.type === 'password';
            senha.type = mostrar ? 'text' : 'password';
            toggleSenha.textContent = mostrar ? '🙈' : '👁';
            toggleSenha.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
            toggleSenha.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const usuario = document.getElementById('usuario')?.value.trim() || '';
            const senhaValor = document.getElementById('senha')?.value || '';
            const unidade = document.getElementById('unidade')?.value.trim() || '';
            const errorText = document.getElementById('error-text');

            if (!usuario || !senhaValor || !unidade) {
                mostrarErroLogin('Preencha todos os campos.');
                return;
            }

            if (loginError) loginError.style.display = 'none';

            try {
                const response = await fetch('../controllers/LoginController.php', {
                    method: 'POST',
                    body: new FormData(loginForm)
                });
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

    // Páginas estáticas também são protegidas por sessão.
    const role = document.body.dataset.role;
    if (role && !loginForm) {
        fetch('../controllers/session.php')
            .then(response => response.json())
            .then(sessao => {
                if (!sessao.success) {
                    window.location.href = 'login.html';
                    return;
                }
                if (sessao.papel !== role) {
                    window.location.href = sessao.papel === 'admin'
                        ? 'index.html'
                        : sessao.papel === 'instrutor'
                            ? 'instrutor.html'
                            : 'aluno.html';
                }
            })
            .catch(() => window.location.href = 'login.html');
    }
});
