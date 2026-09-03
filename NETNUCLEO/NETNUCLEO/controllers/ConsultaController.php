<?php
declare(strict_types=1); require_once __DIR__ . '/../config/config.php'; require_once __DIR__ . '/../includes/auth.php'; header('Content-Type: application/json; charset=utf-8');
$papel=papelUsuario(); $data=trim((string)($_GET['dataAula']??'')); $periodo=trim((string)($_GET['periodo']??'')); $turma=trim((string)($_GET['turma']??'')); $instrutor=trim((string)($_GET['instrutor']??'')); $materia=trim((string)($_GET['materia']??'')); $situacao=trim((string)($_GET['situacao']??'')); $horario=trim((string)($_GET['horario']??''));
try{
 $sql="SELECT a.idAula,DATE_FORMAT(a.dataAula,'%d/%m/%Y') AS data,TIME_FORMAT(a.horarioinicioAula,'%H:%i') AS horarioInicio,TIME_FORMAT(a.horariofimAula,'%H:%i') AS horarioFim,CONCAT(TIME_FORMAT(a.horarioinicioAula,'%H:%i'),' - ',TIME_FORMAT(a.horariofimAula,'%H:%i')) AS horario,COALESCE(t.codigoTurma,'-') AS turma,COALESCE(i.nomeInstrutor,'-') AS instrutor,COALESCE(m.nomeMateria,'-') AS materia,COALESCE(s.nomeSala,'-') AS sala,CASE WHEN a.statusAula=1 THEN 'Realizada' ELSE 'Não realizada' END AS situacao,a.statusAula,a.turnoAula FROM aula a LEFT JOIN instrutor i ON i.idInstrutor=a.Instrutor_idInstrutor LEFT JOIN materia m ON m.idMateria=a.Materia_idMateria LEFT JOIN turma t ON t.idTurma=a.Turma_idTurma LEFT JOIN sala s ON s.idSala=a.Sala_idSala WHERE 1=1";
 $params=[];
 if($data!==''){$sql.=' AND a.dataAula=:data';$params[':data']=$data;}
 if($periodo!==''){$sql.=' AND a.turnoAula=:periodo';$params[':periodo']=$periodo;}
 if($turma!==''){$sql.=' AND t.idTurma=:turma';$params[':turma']=(int)$turma;}
 if($instrutor!==''){$sql.=' AND i.idInstrutor=:instrutor';$params[':instrutor']=(int)$instrutor;}
 if($materia!==''){$sql.=' AND m.idMateria=:materia';$params[':materia']=(int)$materia;}
 if($situacao==='realizada')$sql.=' AND a.statusAula=1'; elseif($situacao==='nao_realizada')$sql.=' AND a.statusAula=0';
 if($horario!==''){$sql.=' AND a.horarioinicioAula=:horario';$params[':horario']=$horario;}
 
 
 $sql.=' ORDER BY a.dataAula ASC,a.horarioinicioAula ASC,t.codigoTurma ASC'; $st=$pdo->prepare($sql);$st->execute($params);$dados=$st->fetchAll(); echo json_encode(['success'=>true,'data'=>$dados,'total'=>count($dados)],JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){http_response_code(500);echo json_encode(['success'=>false,'message'=>'Não foi possível consultar as aulas.'],JSON_UNESCAPED_UNICODE);}
