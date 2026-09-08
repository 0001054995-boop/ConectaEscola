<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
exigirPapelPagina(['admin']);

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = trim((string)($_POST['dataAula'] ?? ''));
    $inicio = trim((string)($_POST['horarioinicioAula'] ?? ''));
    $fim = trim((string)($_POST['horariofimAula'] ?? ''));
    $turno = trim((string)($_POST['turnoAula'] ?? ''));
    $instrutor = (int)($_POST['idInstrutor'] ?? 0);
    $materia = (int)($_POST['idMateria'] ?? 0);
    $turma = (int)($_POST['idTurma'] ?? 0);
    $sala = (int)($_POST['idSala'] ?? 0);
    $tipo = trim((string)($_POST['tipoAula'] ?? 'Presencial'));
    $status = (int)($_POST['statusAula'] ?? 1);

    if ($data === '' || $inicio === '' || $fim === '' || $turno === '' || $instrutor <= 0 || $materia <= 0 || $turma <= 0) {
        $erro = 'Preencha data, horários, período, instrutor, matéria e turma.';
    } elseif (!in_array($status, [0, 1], true)) {
        $erro = 'Situação de aula inválida.';
    } elseif ($fim <= $inicio) {
        $erro = 'O horário final deve ser posterior ao horário inicial.';
    } else {
        try {
            $inicioObj = new DateTime($inicio);
            $fimObj = new DateTime($fim);
            $duracao = $fimObj->diff($inicioObj)->format('%H:%I:%S');

            $stmt = $pdo->prepare(
                'INSERT INTO aula
                (Administrador_idAdministrador, Instrutor_idInstrutor, Materia_idMateria, Turma_idTurma, Sala_idSala,
                 dataAula, turnoAula, horarioinicioAula, horariofimAula, duracaoAula, tipoAula, statusAula)
                 VALUES (:admin, :instrutor, :materia, :turma, :sala, :data, :turno, :inicio, :fim, :duracao, :tipo, :status)'
            );
            $stmt->execute([
                ':admin' => (int)$_SESSION['administrador_id'],
                ':instrutor' => $instrutor,
                ':materia' => $materia,
                ':turma' => $turma,
                ':sala' => $sala > 0 ? $sala : null,
                ':data' => $data,
                ':turno' => $turno,
                ':inicio' => $inicio,
                ':fim' => $fim,
                ':duracao' => $duracao,
                ':tipo' => $tipo !== '' ? $tipo : 'Presencial',
                ':status' => $status,
            ]);
            $mensagem = 'Aula registrada com sucesso. ID da aula: ' . $pdo->lastInsertId();
        } catch (Throwable $e) {
            $erro = 'Não foi possível registrar a aula. Verifique os dados selecionados.';
        }
    }
}

$instrutores = $pdo->query("SELECT idInstrutor, nomeInstrutor FROM instrutor WHERE statusInstrutor = 1 ORDER BY nomeInstrutor")->fetchAll();
$materias = $pdo->query("SELECT idMateria, siglaMateria, nomeMateria FROM materia ORDER BY nomeMateria")->fetchAll();
$turmas = $pdo->query("SELECT idTurma, codigoTurma, turnoTurma, datafimTurma FROM turma ORDER BY codigoTurma")->fetchAll();
$salas = $pdo->query("SELECT idSala, nomeSala FROM sala ORDER BY nomeSala")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Cadastrar Aula | NetNúcleo</title><link rel="stylesheet" href="../public/style.css"></head>
<body>
<header class="header"><div class="header-container"><a href="../view/index.html" class="logo-area"><img src="../public/img/logo.png" alt="SESI e SENAI" class="institution-logo"><h3 class="netindex">NETNÚCLEO - HORTO</h3></a><nav class="navigation"><a href="../view/index.html">Início</a><a href="cadastrar_aula.php" class="active">Cadastrar aula</a><a href="../view/consulta.html">Consultas</a><a href="LogoutController.php">Sair</a></nav></div></header>
<main class="page-main"><div class="container"><div class="page-title"><span class="section-label">CADASTRO</span><h1>Cadastrar aula</h1><p>Todos os valores selecionados serão gravados na tabela <strong>aula</strong>.</p></div>
<?php if ($mensagem): ?><div class="form-message success" style="display:block"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="form-message error" style="display:block"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<section class="filter-card"><form method="post">
<div class="form-group"><label for="dataAula">Data da aula</label><input type="date" id="dataAula" name="dataAula" required></div>
<div class="form-group"><label for="turnoAula">Período</label><select id="turnoAula" name="turnoAula" required><option value="">- Selecione -</option><option>Manhã</option><option>Tarde</option><option>Noite</option></select></div>
<div class="form-group"><label for="horarioinicioAula">Horário inicial</label><input type="time" id="horarioinicioAula" name="horarioinicioAula" required></div>
<div class="form-group"><label for="horariofimAula">Horário final</label><input type="time" id="horariofimAula" name="horariofimAula" required></div>
<div class="form-group"><label for="idInstrutor">Instrutor</label><select id="idInstrutor" name="idInstrutor" required><option value="">- Selecione -</option><?php foreach ($instrutores as $i): ?><option value="<?= (int)$i['idInstrutor'] ?>"><?= htmlspecialchars($i['nomeInstrutor']) ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label for="idMateria">Matéria</label><select id="idMateria" name="idMateria" required><option value="">- Selecione -</option><?php foreach ($materias as $m): ?><option value="<?= (int)$m['idMateria'] ?>"><?= htmlspecialchars($m['siglaMateria'] . ' - ' . $m['nomeMateria']) ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label for="idTurma">Turma</label><select id="idTurma" name="idTurma" required><option value="">- Selecione -</option><?php foreach ($turmas as $t): ?><option value="<?= (int)$t['idTurma'] ?>">Turma <?= htmlspecialchars((string)$t['codigoTurma']) ?> - <?= htmlspecialchars($t['turnoTurma']) ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label for="idSala">Sala</label><select id="idSala" name="idSala"><option value="">- Sem sala definida -</option><?php foreach ($salas as $s): ?><option value="<?= (int)$s['idSala'] ?>"><?= htmlspecialchars($s['nomeSala']) ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label for="tipoAula">Tipo de aula</label><select id="tipoAula" name="tipoAula"><option>Presencial</option><option>Online</option><option>Híbrida</option></select></div>
<div class="form-group"><label for="statusAula">Situação</label><select id="statusAula" name="statusAula"><option value="1">Realizada</option><option value="0">Não realizada</option></select></div>
<div class="form-actions"><button type="submit" class="button button-primary">Registrar aula</button><a href="../view/consulta.html" class="button button-secondary">Ir para consultas</a></div>
</form></section></div></main>
<script src="../public/script.js"></script></body></html>
