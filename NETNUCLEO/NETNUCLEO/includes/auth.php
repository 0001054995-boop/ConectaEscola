<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function modoDemonstracao(): bool
{
    return true;
}

function usuarioLogado(): bool
{
    return modoDemonstracao() || (isset($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] > 0);
}

function papelUsuario(): string
{
    return (string)($_SESSION['papel'] ?? 'admin');
}

function exigirLoginJson(): void
{
    if (!usuarioLogado()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'message'=>'Sessão expirada ou usuário não autenticado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function exigirPapelJson(array $papeis): void
{
    exigirLoginJson();
    if (!in_array(papelUsuario(), $papeis, true)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'message'=>'Você não possui permissão para esta ação.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function exigirLoginPagina(): void
{
    if (!usuarioLogado()) {
        header('Location: ../view/login.html');
        exit;
    }
}

function exigirPapelPagina(array $papeis): void
{
    exigirLoginPagina();
    if (!in_array(papelUsuario(), $papeis, true)) {
        $destino = papelUsuario() === 'instrutor' ? '../view/instrutor.html' : (papelUsuario() === 'aluno' ? '../view/aluno.html' : '../view/index.html');
        header('Location: ' . $destino);
        exit;
    }
}
