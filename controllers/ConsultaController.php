<?php
// Desativa a exibição de erros HTML na resposta para não quebrar o JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    $host = '127.0.0.1';
    $port = '3306';
    $db   = 'sistema_web';
    $user = 'root';
    $pass = '';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 1. Recebe todos os campos enviados pelo formulário
    $dataInicial = $_GET['data-inicial'] ?? '';
    $dataFinal   = $_GET['data-final'] ?? '';
    $periodo     = $_GET['periodo'] ?? '';
    $instrutor   = $_GET['instrutor'] ?? '';
    $turma       = $_GET['turma'] ?? '';
    $encerradas  = $_GET['encerradas'] ?? '';

    // TRAVA: Se nenhum filtro foi preenchido, encerra sem consultar o banco
    if (empty($dataInicial) && empty($dataFinal) && empty($periodo) && empty($instrutor) && empty($turma) && empty($encerradas)) {
        echo json_encode([
            'success' => true,
            'data'    => []
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // 2. Monta a SQL dinamicamente
    $sql = "SELECT * FROM aulas WHERE 1=1";
    $params = [];

    if (!empty($dataInicial) && !empty($dataFinal)) {
        $sql .= " AND data BETWEEN :dataInicial AND :dataFinal";
        $params[':dataInicial'] = $dataInicial;
        $params[':dataFinal']   = $dataFinal;
    } elseif (!empty($dataInicial)) {
        $sql .= " AND data >= :dataInicial";
        $params[':dataInicial'] = $dataInicial;
    } elseif (!empty($dataFinal)) {
        $sql .= " AND data <= :dataFinal";
        $params[':dataFinal'] = $dataFinal;
    }

    if (!empty($periodo)) {
        $sql .= " AND periodo = :periodo";
        $params[':periodo'] = $periodo;
    }

    if (!empty($instrutor)) {
        $sql .= " AND instrutor = :instrutor";
        $params[':instrutor'] = $instrutor;
    }

    if (!empty($turma)) {
        $sql .= " AND turma = :turma";
        $params[':turma'] = $turma;
    }

    if (empty($encerradas)) {
        $sql .= " AND situacao != 'Encerrada'";
    }

    $sql .= " ORDER BY data DESC";

    // 3. Executa a busca
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $aulas = $stmt->fetchAll();

    // 4. Formata o retorno
    $resultados = [];
    foreach ($aulas as $aula) {
        $dataFormatada = !empty($aula['data']) ? date('d/m/Y', strtotime($aula['data'])) : '';
        
        $resultados[] = [
            'data'      => $dataFormatada,
            'horario'   => $aula['horario'] ?? '',
            'turma'     => $aula['turma'] ?? '',
            'instrutor' => $aula['instrutor'] ?? '',
            'sala'      => $aula['sala'] ?? '',
            'situacao'  => $aula['situacao'] ?? ''
        ];
    }

    echo json_encode([
        'success' => true,
        'data'    => $resultados
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no Banco de Dados: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no Servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
exit;