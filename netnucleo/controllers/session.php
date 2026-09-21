<?php
declare(strict_types=1); require_once __DIR__ . '/../includes/auth.php'; header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>usuarioLogado(),'usuario'=>$_SESSION['usuario']??null,'papel'=>$_SESSION['papel']??null,'unidade'=>$_SESSION['unidade']??null],JSON_UNESCAPED_UNICODE);
