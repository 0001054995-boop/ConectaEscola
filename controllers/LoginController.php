<?php

header('Content-Type: application/json; charset=utf-8');

$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';
$unidade = $_POST['unidade'] ?? '';

/*
 * Por enquanto não existe validação no banco de dados.
 * Qualquer usuário e senha preenchidos podem entrar.
 */

if (
    !empty(trim($usuario)) &&
    !empty(trim($senha)) &&
    !empty(trim($unidade))
) {

    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso!',
        'redirect' => 'consulta.html'
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Preencha todos os campos.'
    ]);

}

exit;