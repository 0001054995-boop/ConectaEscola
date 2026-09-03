<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  'success' => true,
  'modoDemonstracao' => true,
  'usuario' => $_SESSION['usuario'] ?? null,
  'papel' => $_SESSION['papel'] ?? 'admin',
  'unidade' => $_SESSION['unidade'] ?? 'Horto'
], JSON_UNESCAPED_UNICODE);
