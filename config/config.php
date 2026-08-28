<?php

return [
    'app_name' => 'Sistema PHP',
    'session_name' => 'sistema_php_session',
    'data_path' => __DIR__ . '/storage',

    // Banco de dados usado pelo ConsultaController.
    // Você pode preencher aqui ou usar as variáveis DB_HOST, DB_PORT,
    // DB_NAME, DB_USER e DB_PASS no ambiente do servidor.
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => getenv('DB_PORT') ?: '3306',
    'db_name' => getenv('DB_NAME') ?: '',
    'db_user' => getenv('DB_USER') ?: '',
    'db_pass' => getenv('DB_PASS') ?: '',
];
