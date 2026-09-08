<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

exigirPapelJson(['admin']);
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sql = "
            SELECT
                a.idAdministrador,
                a.usuarioAdministrador,
                a.emailAdministrador,
                a.unidadeAdministrador,
                a.papelAdministrador,
                a.Instrutor_idInstrutor,
                a.Aluno_idAluno,
                COALESCE(i.nomeInstrutor, '') AS nomeInstrutor,
                COALESCE(i.cpfInstrutor, '') AS cpfInstrutor,
                COALESCE(i.emailInstrutor, '') AS emailInstrutor,
                COALESCE(i.telefoneInstrutor, '') AS telefoneInstrutor,
                COALESCE(i.areaInstrutor, '') AS areaInstrutor,
                COALESCE(al.nomeAluno, '') AS nomeAluno,
                COALESCE(al.cpfAluno, '') AS cpfAluno,
                COALESCE(al.emailAluno, '') AS emailAluno,
                COALESCE(al.telefoneAluno, '') AS telefoneAluno,
                al.Turma_idTurma,
                t.codigoTurma
            FROM administrador a
            LEFT JOIN instrutor i ON i.idInstrutor = a.Instrutor_idInstrutor
            LEFT JOIN aluno al ON al.idAluno = a.Aluno_idAluno
            LEFT JOIN turma t ON t.idTurma = al.Turma_idTurma
            ORDER BY a.usuarioAdministrador
        ";
        $st = $pdo->query($sql);
        echo json_encode(['success' => true, 'usuarios' => $st->fetchAll()], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $input = $_POST;
    $acao = (string)($input['acao'] ?? 'criar');
    $id = (int)($input['id'] ?? 0);

    if ($acao === 'excluir') {
        if ($id <= 0) throw new RuntimeException('Login não informado.');
        if ($id === (int)($_SESSION['administrador_id'] ?? 0)) {
            throw new RuntimeException('O administrador logado não pode excluir o próprio login.');
        }

        $st = $pdo->prepare('SELECT papelAdministrador, Instrutor_idInstrutor, Aluno_idAluno FROM administrador WHERE idAdministrador = :id');
        $st->execute([':id' => $id]);
        $conta = $st->fetch();
        if (!$conta) throw new RuntimeException('Login não encontrado.');

        $pdo->beginTransaction();
        $del = $pdo->prepare('DELETE FROM administrador WHERE idAdministrador = :id');
        $del->execute([':id' => $id]);

        // Remove também o cadastro vinculado quando ele não é usado por aulas nem por outro login.
        if ($conta['papelAdministrador'] === 'instrutor' && $conta['Instrutor_idInstrutor']) {
            $check = $pdo->prepare('SELECT COUNT(*) FROM administrador WHERE Instrutor_idInstrutor = :id');
            $check->execute([':id' => $conta['Instrutor_idInstrutor']]);
            $logins = (int)$check->fetchColumn();
            $check = $pdo->prepare('SELECT COUNT(*) FROM aula WHERE Instrutor_idInstrutor = :id');
            $check->execute([':id' => $conta['Instrutor_idInstrutor']]);
            $aulas = (int)$check->fetchColumn();
            if ($logins === 0 && $aulas === 0) {
                $delPessoa = $pdo->prepare('DELETE FROM instrutor WHERE idInstrutor = :id');
                $delPessoa->execute([':id' => $conta['Instrutor_idInstrutor']]);
            }
        }

        if ($conta['papelAdministrador'] === 'aluno' && $conta['Aluno_idAluno']) {
            $check = $pdo->prepare('SELECT COUNT(*) FROM administrador WHERE Aluno_idAluno = :id');
            $check->execute([':id' => $conta['Aluno_idAluno']]);
            $logins = (int)$check->fetchColumn();
            $check = $pdo->prepare('SELECT COUNT(*) FROM aula WHERE Aluno_idAluno = :id');
            $check->execute([':id' => $conta['Aluno_idAluno']]);
            $aulas = (int)$check->fetchColumn();
            if ($logins === 0 && $aulas === 0) {
                $delPessoa = $pdo->prepare('DELETE FROM aluno WHERE idAluno = :id');
                $delPessoa->execute([':id' => $conta['Aluno_idAluno']]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Login excluído com sucesso.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $usuario = trim((string)($input['usuario'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $unidade = trim((string)($input['unidade'] ?? 'Horto')) ?: 'Horto';
    $papel = (string)($input['papel'] ?? 'aluno');
    $senha = (string)($input['senha'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $usuario)) {
        throw new RuntimeException('Usuário inválido. Use de 3 a 50 caracteres.');
    }
    if (!in_array($papel, ['admin', 'instrutor', 'aluno'], true)) {
        throw new RuntimeException('Perfil inválido.');
    }

    $nome = trim((string)($input['nome'] ?? ''));
    $cpf = preg_replace('/\D+/', '', (string)($input['cpf'] ?? '')) ?: '';
    $telefone = preg_replace('/\D+/', '', (string)($input['telefone'] ?? '')) ?: '';
    $area = trim((string)($input['area'] ?? ''));
    $turmaId = (int)($input['turma_id'] ?? 0);

    if ($papel !== 'admin' && $nome === '') {
        throw new RuntimeException('Informe o nome do aluno ou instrutor.');
    }
    if ($papel === 'aluno' && $turmaId <= 0) {
        throw new RuntimeException('Selecione a turma do aluno.');
    }
    if ($acao === 'criar' && $senha === '') {
        throw new RuntimeException('Informe uma senha para o novo login.');
    }

    $pdo->beginTransaction();

    $instrutorId = null;
    $alunoId = null;

    if ($papel === 'instrutor') {
        if ($acao === 'editar') {
            $old = $pdo->prepare('SELECT Instrutor_idInstrutor FROM administrador WHERE idAdministrador = :id');
            $old->execute([':id' => $id]);
            $oldId = $old->fetchColumn();
            if ($oldId) {
                $instrutorId = (int)$oldId;
                $st = $pdo->prepare('UPDATE instrutor SET nomeInstrutor=:nome, cpfInstrutor=:cpf, emailInstrutor=:email, telefoneInstrutor=:telefone, areaInstrutor=:area WHERE idInstrutor=:id');
                $st->execute([':nome'=>$nome, ':cpf'=>$cpf ?: null, ':email'=>$email ?: null, ':telefone'=>$telefone ?: null, ':area'=>$area ?: null, ':id'=>$instrutorId]);
            } else {
                $st = $pdo->prepare('INSERT INTO instrutor (nomeInstrutor,cpfInstrutor,emailInstrutor,telefoneInstrutor,areaInstrutor,statusInstrutor) VALUES (:nome,:cpf,:email,:telefone,:area,1)');
                $st->execute([':nome'=>$nome, ':cpf'=>$cpf ?: null, ':email'=>$email ?: null, ':telefone'=>$telefone ?: null, ':area'=>$area ?: null]);
                $instrutorId = (int)$pdo->lastInsertId();
            }
        } else {
            $st = $pdo->prepare('INSERT INTO instrutor (nomeInstrutor,cpfInstrutor,emailInstrutor,telefoneInstrutor,areaInstrutor,statusInstrutor) VALUES (:nome,:cpf,:email,:telefone,:area,1)');
            $st->execute([':nome'=>$nome, ':cpf'=>$cpf ?: null, ':email'=>$email ?: null, ':telefone'=>$telefone ?: null, ':area'=>$area ?: null]);
            $instrutorId = (int)$pdo->lastInsertId();
        }
    }

    if ($papel === 'aluno') {
        if ($acao === 'editar') {
            $old = $pdo->prepare('SELECT Aluno_idAluno FROM administrador WHERE idAdministrador = :id');
            $old->execute([':id' => $id]);
            $oldId = $old->fetchColumn();
            if ($oldId) {
                $alunoId = (int)$oldId;
                $st = $pdo->prepare('UPDATE aluno SET nomeAluno=:nome, cpfAluno=:cpf, emailAluno=:email, telefoneAluno=:telefone, Turma_idTurma=:turma WHERE idAluno=:id');
                $st->execute([':nome'=>$nome, ':cpf'=>$cpf ?: null, ':email'=>$email ?: null, ':telefone'=>$telefone ?: null, ':turma'=>$turmaId, ':id'=>$alunoId]);
            } else {
                $st = $pdo->prepare('INSERT INTO aluno (nomeAluno,cpfAluno,emailAluno,telefoneAluno,Turma_idTurma) VALUES (:nome,:cpf,:email,:telefone,:turma)');
                $st->execute([':nome'=>$nome, ':cpf'=>$cpf ?: null, ':email'=>$email ?: null, ':telefone'=>$telefone ?: null, ':turma'=>$turmaId]);
                $alunoId = (int)$pdo->lastInsertId();
            }
        } else {
            $st = $pdo->prepare('INSERT INTO aluno (nomeAluno,cpfAluno,emailAluno,telefoneAluno,Turma_idTurma) VALUES (:nome,:cpf,:email,:telefone,:turma)');
            $st->execute([':nome'=>$nome, ':cpf'=>$cpf ?: null, ':email'=>$email ?: null, ':telefone'=>$telefone ?: null, ':turma'=>$turmaId]);
            $alunoId = (int)$pdo->lastInsertId();
        }
    }

    if ($acao === 'criar') {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $st = $pdo->prepare('INSERT INTO administrador (usuarioAdministrador,emailAdministrador,senhaAdministrador,unidadeAdministrador,papelAdministrador,Instrutor_idInstrutor,Aluno_idAluno) VALUES (:u,:e,:s,:un,:p,:i,:a)');
        $st->execute([':u'=>$usuario, ':e'=>$email ?: null, ':s'=>$hash, ':un'=>$unidade, ':p'=>$papel, ':i'=>$instrutorId, ':a'=>$alunoId]);
        $message = 'Login criado com sucesso.';
    } elseif ($acao === 'editar') {
        if ($id <= 0) throw new RuntimeException('Login não informado.');
        if ($senha !== '') {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $st = $pdo->prepare('UPDATE administrador SET usuarioAdministrador=:u,emailAdministrador=:e,senhaAdministrador=:s,unidadeAdministrador=:un,papelAdministrador=:p,Instrutor_idInstrutor=:i,Aluno_idAluno=:a WHERE idAdministrador=:id');
            $st->execute([':u'=>$usuario, ':e'=>$email ?: null, ':s'=>$hash, ':un'=>$unidade, ':p'=>$papel, ':i'=>$instrutorId, ':a'=>$alunoId, ':id'=>$id]);
        } else {
            $st = $pdo->prepare('UPDATE administrador SET usuarioAdministrador=:u,emailAdministrador=:e,unidadeAdministrador=:un,papelAdministrador=:p,Instrutor_idInstrutor=:i,Aluno_idAluno=:a WHERE idAdministrador=:id');
            $st->execute([':u'=>$usuario, ':e'=>$email ?: null, ':un'=>$unidade, ':p'=>$papel, ':i'=>$instrutorId, ':a'=>$alunoId, ':id'=>$id]);
        }
        $message = 'Informações e login alterados com sucesso.';
    } else {
        throw new RuntimeException('Ação inválida.');
    }

    $pdo->commit();
    echo json_encode(['success'=>true,'message'=>$message], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Não foi possível salvar. Verifique se o usuário ou CPF já existe e se os dados são válidos.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
