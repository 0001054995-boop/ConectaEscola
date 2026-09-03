<?php
declare(strict_types=1); require_once __DIR__.'/../config/config.php'; require_once __DIR__.'/../includes/auth.php'; exigirPapelJson(['admin']); header('Content-Type: application/json; charset=utf-8');
$method=$_SERVER['REQUEST_METHOD'];
try{
 if($method==='GET'){
  $st=$pdo->query("SELECT a.idAdministrador,a.usuarioAdministrador,a.emailAdministrador,a.unidadeAdministrador,a.papelAdministrador,a.Instrutor_idInstrutor,a.Aluno_idAluno,COALESCE(i.nomeInstrutor,'') AS nomeInstrutor,COALESCE(al.nomeAluno,'') AS nomeAluno FROM administrador a LEFT JOIN instrutor i ON i.idInstrutor=a.Instrutor_idInstrutor LEFT JOIN aluno al ON al.idAluno=a.Aluno_idAluno ORDER BY a.usuarioAdministrador");
  echo json_encode(['success'=>true,'usuarios'=>$st->fetchAll()],JSON_UNESCAPED_UNICODE); exit;
 }
 $input=$_POST;
 if($method!=='POST'){http_response_code(405);echo json_encode(['success'=>false,'message'=>'Método não permitido.'],JSON_UNESCAPED_UNICODE);exit;}
 $acao=$input['acao']??'criar'; $id=(int)($input['id']??0); $usuario=trim((string)($input['usuario']??''));$email=trim((string)($input['email']??''));$unidade=trim((string)($input['unidade']??'Horto'));$papel=(string)($input['papel']??'aluno');$senha=(string)($input['senha']??'');$instrutor=$input['instrutor_id']!==''&&isset($input['instrutor_id'])?(int)$input['instrutor_id']:null;$aluno=$input['aluno_id']!==''&&isset($input['aluno_id'])?(int)$input['aluno_id']:null;
 if(!preg_match('/^[a-zA-Z0-9._-]{3,50}$/',$usuario)) throw new RuntimeException('Usuário inválido. Use 3 a 50 caracteres, letras, números, ponto, hífen ou sublinhado.');
 if(!in_array($papel,['admin','instrutor','aluno'],true)) throw new RuntimeException('Perfil inválido.');
 if($papel==='instrutor' && !$instrutor) throw new RuntimeException('Selecione o instrutor vinculado.');
 if($papel==='aluno' && !$aluno) throw new RuntimeException('Selecione o aluno vinculado.');
 if($papel==='admin'){$instrutor=null;$aluno=null;}
 if($acao==='criar'){
  if($senha==='') throw new RuntimeException('Informe uma senha para o novo login.');
  $hash=password_hash($senha,PASSWORD_DEFAULT); $st=$pdo->prepare('INSERT INTO administrador(usuarioAdministrador,emailAdministrador,senhaAdministrador,unidadeAdministrador,papelAdministrador,Instrutor_idInstrutor,Aluno_idAluno) VALUES(:u,:e,:s,:un,:p,:i,:a)');
  $st->execute([':u'=>$usuario,':e'=>$email?:null,':s'=>$hash,':un'=>$unidade,':p'=>$papel,':i'=>$instrutor,':a'=>$aluno]); echo json_encode(['success'=>true,'message'=>'Login criado com sucesso.'],JSON_UNESCAPED_UNICODE); exit;
 }
 if($acao==='editar'){
  if($id<=0) throw new RuntimeException('Login não informado.');
  if($senha!==''){$hash=password_hash($senha,PASSWORD_DEFAULT);$st=$pdo->prepare('UPDATE administrador SET usuarioAdministrador=:u,emailAdministrador=:e,senhaAdministrador=:s,unidadeAdministrador=:un,papelAdministrador=:p,Instrutor_idInstrutor=:i,Aluno_idAluno=:a WHERE idAdministrador=:id');$st->execute([':u'=>$usuario,':e'=>$email?:null,':s'=>$hash,':un'=>$unidade,':p'=>$papel,':i'=>$instrutor,':a'=>$aluno,':id'=>$id]);}
  else {$st=$pdo->prepare('UPDATE administrador SET usuarioAdministrador=:u,emailAdministrador=:e,unidadeAdministrador=:un,papelAdministrador=:p,Instrutor_idInstrutor=:i,Aluno_idAluno=:a WHERE idAdministrador=:id');$st->execute([':u'=>$usuario,':e'=>$email?:null,':un'=>$unidade,':p'=>$papel,':i'=>$instrutor,':a'=>$aluno,':id'=>$id]);}
  echo json_encode(['success'=>true,'message'=>'Login alterado com sucesso.'],JSON_UNESCAPED_UNICODE); exit;
 }
 throw new RuntimeException('Ação inválida.');
}catch(PDOException $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>'Não foi possível salvar. Verifique se o usuário já existe e se os vínculos são válidos.'],JSON_UNESCAPED_UNICODE);}catch(Throwable $e){http_response_code(400);echo json_encode(['success'=>false,'message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
