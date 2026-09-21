<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Método não permitido.'], JSON_UNESCAPED_UNICODE); exit; }
$unidade=trim((string)($_POST['unidade']??'')); $usuario=trim((string)($_POST['usuario']??'')); $senha=(string)($_POST['senha']??'');
if ($unidade===''||$usuario===''||$senha==='') { echo json_encode(['success'=>false,'message'=>'Preencha todos os campos.'], JSON_UNESCAPED_UNICODE); exit; }
try {
 $stmt=$pdo->prepare('SELECT idAdministrador,usuarioAdministrador,emailAdministrador,senhaAdministrador,unidadeAdministrador,papelAdministrador,Instrutor_idInstrutor,Aluno_idAluno FROM administrador WHERE usuarioAdministrador=:usuario LIMIT 1');
 $stmt->execute([':usuario'=>$usuario]); $conta=$stmt->fetch();
 $senhaValida=$conta && password_verify($senha,(string)$conta['senhaAdministrador']);
 $unidadeValida=$conta && strcasecmp((string)$conta['unidadeAdministrador'],$unidade)===0;
 $sucesso=$senhaValida&&$unidadeValida;
 $log=$pdo->prepare('INSERT INTO login_log (Administrador_idAdministrador,usuarioInformado,unidadeInformada,dataLogin,horarioLogin,sucesso,ipLogin) VALUES (:id,:usuario,:unidade,CURDATE(),CURTIME(),:sucesso,:ip)');
 $log->execute([':id'=>$conta?(int)$conta['idAdministrador']:null,':usuario'=>$usuario,':unidade'=>$unidade,':sucesso'=>$sucesso?1:0,':ip'=>$_SERVER['REMOTE_ADDR']??null]);
 if(!$sucesso){echo json_encode(['success'=>false,'message'=>'Usuário, senha ou unidade inválidos.'],JSON_UNESCAPED_UNICODE);exit;}
 session_regenerate_id(true);
 $_SESSION['usuario_id']=(int)$conta['idAdministrador']; $_SESSION['administrador_id']=(int)$conta['idAdministrador']; $_SESSION['usuario']=(string)$conta['usuarioAdministrador']; $_SESSION['unidade']=(string)$conta['unidadeAdministrador']; $_SESSION['papel']=(string)$conta['papelAdministrador']; $_SESSION['instrutor_id']=$conta['Instrutor_idInstrutor']!==null?(int)$conta['Instrutor_idInstrutor']:null; $_SESSION['aluno_id']=$conta['Aluno_idAluno']!==null?(int)$conta['Aluno_idAluno']:null;
 if(password_needs_rehash((string)$conta['senhaAdministrador'],PASSWORD_DEFAULT)){ $novo=password_hash($senha,PASSWORD_DEFAULT); $u=$pdo->prepare('UPDATE administrador SET senhaAdministrador=:senha WHERE idAdministrador=:id');$u->execute([':senha'=>$novo,':id'=>(int)$conta['idAdministrador']]); }
 $redirect=['admin'=>'index.html','instrutor'=>'instrutor.html','aluno'=>'aluno.html'][$_SESSION['papel']]??'index.html';
 echo json_encode(['success'=>true,'message'=>'Login realizado com sucesso.','papel'=>$_SESSION['papel'],'redirect'=>$redirect],JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){http_response_code(500);echo json_encode(['success'=>false,'message'=>'Erro interno ao realizar o login.'],JSON_UNESCAPED_UNICODE);}
