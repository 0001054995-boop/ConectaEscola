<?php

namespace controll;

use PDO;
use PDOException;

class ConsultaController
{
    /**
     * Endpoint:
     * GET /consulta
     *
     * Recebe:
     * dataInicial
     * dataFinal
     * periodo
     * instrutor
     * turma
     * mostrarTurmasEncerradas
     *
     * Retorna os dados necessários para preencher a tabela.
     */
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // A consulta agora é feita por GET.
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                $this->response([
                    'success' => false,
                    'message' => 'A consulta deve ser enviada por GET.'
                ], 405);

                return;
            }

            $dados = $this->receberDados();

            $pdo = $this->conectarBanco();

            $sql = "
                SELECT
                    a.data AS data,
                    a.horario AS horario,
                    a.turma AS turma,
                    a.instrutor AS instrutor,
                    a.sala AS sala,
                    a.situacao AS situacao
                FROM aulas a
                WHERE 1 = 1
            ";

            $params = [];

            // Data inicial
            if ($dados['data-inicial'] !== '') {
                $sql .= " AND DATE(a.data) >= :dataInicial";
                $params[':dataInicial'] = $dados['data-inicial'];
            }

            // Data final
            if ($dados['data-final'] !== '') {
                $sql .= " AND DATE(a.data) <= :dataFinal";
                $params[':dataFinal'] = $dados['data-final'];
            }

            // Período
            if ($dados['periodo'] !== '') {
                $sql .= " AND a.periodo = :periodo";
                $params[':periodo'] = $dados['periodo'];
            }

            // Instrutor
            if ($dados['instrutor'] !== '') {
                $sql .= " AND a.instrutor = :instrutor";
                $params[':instrutor'] = $dados['instrutor'];
            }

            // Turma
            if ($dados['turma'] !== '') {
                $sql .= " AND a.turma = :turma";
                $params[':turma'] = $dados['turma'];
            }

            // Por padrão, turmas encerradas não aparecem.
            if (!$dados['encerradas']) {
                $sql .= " AND UPPER(a.situacao) <> 'ENCERRADA'";
            }

            $sql .= "
                ORDER BY
                    a.data ASC,
                    a.horario ASC,
                    a.turma ASC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->response([
                'success' => true,
                'data' => array_map(
                    [$this, 'formatarResultado'],
                    $resultados
                ),
                'total' => count($resultados)
            ]);

        } catch (PDOException $e) {

            $this->response([
                'success' => false,
                'message' => 'Não foi possível realizar a consulta no banco de dados.'
            ], 500);

        } catch (\Throwable $e) {

            $this->response([
                'success' => false,
                'message' => 'Erro ao processar a consulta.'
            ], 500);
        }
    }

    /**
     * Recebe os filtros enviados pelo JavaScript via GET.
     */
    private function receberDados(): array
    {
        /*
         * Aceita tanto:
         *
         * ?dataInicial=2026-08-01
         *
         * quanto:
         *
         * ?data-inicial=2026-08-01
         *
         * Isso deixa o Controller compatível com os dois formatos.
         */

        $dataInicial = $_GET['dataInicial']
            ?? $_GET['data-inicial']
            ?? '';

        $dataFinal = $_GET['dataFinal']
            ?? $_GET['data-final']
            ?? '';

        $periodo = $_GET['periodo']
            ?? '';

        $instrutor = $_GET['instrutor']
            ?? '';

        $turma = $_GET['turma']
            ?? '';

        $mostrarTurmasEncerradas = $_GET['mostrarTurmasEncerradas']
            ?? $_GET['encerradas']
            ?? false;

        return [
            'data-inicial' => $this->normalizarData($dataInicial),

            'data-final' => $this->normalizarData($dataFinal),

            'periodo' => trim((string) $periodo),

            'instrutor' => trim((string) $instrutor),

            'turma' => trim((string) $turma),

            'encerradas' => $this->booleano(
                $mostrarTurmasEncerradas
            )
        ];
    }

    /**
     * Normaliza a data para YYYY-MM-DD.
     *
     * Aceita:
     * YYYY-MM-DD
     * DD/MM/YYYY
     */
    private function normalizarData($data): string
    {
        $data = trim((string) $data);

        if ($data === '') {
            return '';
        }

        // YYYY-MM-DD
        $formato = \DateTime::createFromFormat(
            'Y-m-d',
            $data
        );

        if (
            $formato &&
            $formato->format('Y-m-d') === $data
        ) {
            return $data;
        }

        // DD/MM/YYYY
        $formato = \DateTime::createFromFormat(
            'd/m/Y',
            $data
        );

        if (
            $formato &&
            $formato->format('d/m/Y') === $data
        ) {
            return $formato->format('Y-m-d');
        }

        return '';
    }

    /**
     * Converte diferentes formatos de verdadeiro/falso
     * para boolean.
     */
    private function booleano($valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (int) $valor === 1;
        }

        return in_array(
            strtolower(trim((string) $valor)),
            [
                '1',
                'true',
                'sim',
                'yes',
                'on'
            ],
            true
        );
    }

    /**
     * Conexão com o banco de dados.
     */
    private function conectarBanco(): PDO
    {
        $config = require __DIR__ . '/../config.php';

        $host = $config['db_host']
            ?? getenv('DB_HOST')
            ?: '127.0.0.1';

        $port = $config['db_port']
            ?? getenv('DB_PORT')
            ?: '3306';

        $database = $config['db_name']
            ?? getenv('DB_NAME')
            ?: '';

        $username = $config['db_user']
            ?? getenv('DB_USER')
            ?: '';

        $password = $config['db_pass']
            ?? getenv('DB_PASS')
            ?: '';

        if ($database === '' || $username === '') {
            throw new PDOException(
                'Configuração do banco de dados não definida.'
            );
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $database
        );

        return new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    /**
     * Formata cada registro para exatamente
     * o formato esperado pela tabela.
     */
    private function formatarResultado(array $resultado): array
    {
        return [
            'data' => $resultado['data'] ?? '',
            'horario' => $resultado['horario'] ?? '',
            'turma' => $resultado['turma'] ?? '',
            'instrutor' => $resultado['instrutor'] ?? '',
            'sala' => $resultado['sala'] ?? '',
            'situacao' => $resultado['situacao'] ?? '',
        ];
    }

    /**
     * Envia a resposta JSON.
     */
    private function response(
        array $dados,
        int $status = 200
    ): void {
        http_response_code($status);

        echo json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );
    }
}