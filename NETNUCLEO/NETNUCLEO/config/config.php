<?php
declare(strict_types=1);

/**
 * Conexão central do SISGED/NetNúcleo.
 * Altere apenas estas variáveis se o seu MySQL tiver outro usuário/senha.
 */
$host = 'localhost';
$db = 'sisged';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('Erro de conexão com o banco de dados. Verifique o MySQL e o arquivo config/config.php.');
}
