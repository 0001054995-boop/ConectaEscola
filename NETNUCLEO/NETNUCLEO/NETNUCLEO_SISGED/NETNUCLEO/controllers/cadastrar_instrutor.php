<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
exigirPapelPagina(['admin']);
$mensagem=''; $erro='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nome=trim((string)($_POST['nomeInstrutor']??'')); $cpf=preg_replace('/\D+/','',(string)($_POST['cpfInstrutor']??'')); $email=trim((string)($_POST['emailInstrutor']??'')); $telefone=preg_replace('/\D+/','',(string)($_POST['telefoneInstrutor']??'')); $area=trim((string)($_POST['areaInstrutor']??''));
    if($nome==='') $erro='O nome do instrutor é obrigatório.'; else {
        try { $stmt=$pdo->prepare('INSERT INTO instrutor (nomeInstrutor,cpfInstrutor,emailInstrutor,telefoneInstrutor,areaInstrutor,statusInstrutor) VALUES (:nome,:cpf,:email,:telefone,:area,1)'); $stmt->execute([':nome'=>$nome,':cpf'=>$cpf!==''?$cpf:null,':email'=>$email!==''?$email:null,':telefone'=>$telefone!==''?$telefone:null,':area'=>$area!==''?$area:null]); $mensagem='Instrutor cadastrado com sucesso. ID: '.$pdo->lastInsertId(); } catch(Throwable $e){$erro='Não foi possível cadastrar o instrutor.';}
    }
}
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Cadastrar Instrutor | NetNúcleo</title><link rel="stylesheet" href="../public/style.css"></head><body>
<header class="header"><div class="header-container"><a href="../view/index.html" class="logo-area"><img src="../public/img/logo.png" alt="SESI e SENAI" class="institution-logo"><h3 class="netindex">NETNÚCLEO - HORTO</h3></a><nav class="navigation"><a href="../view/index.html">Início</a><a href="cadastrar_aula.php">Cadastrar aula</a><a href="cadastrar_instrutor.php" class="active">Instrutor</a><a href="../view/consulta.html">Consultas</a><a href="LogoutController.php">Sair</a></nav></div></header>
<main class="page-main"><div class="container"><div class="page-title"><span class="section-label">CADASTRO</span><h1>Cadastrar instrutor</h1></div><?php if($mensagem): ?><div class="form-message success" style="display:block"><?=htmlspecialchars($mensagem)?></div><?php endif;?><?php if($erro): ?><div class="form-message error" style="display:block"><?=htmlspecialchars($erro)?></div><?php endif;?><section class="filter-card"><form method="post"><div class="form-group"><label>Nome</label><input type="text" name="nomeInstrutor" required></div><div class="form-group"><label>CPF</label><input type="text" name="cpfInstrutor" maxlength="11"></div><div class="form-group"><label>E-mail</label><input type="email" name="emailInstrutor"></div><div class="form-group"><label>Telefone</label><input type="text" name="telefoneInstrutor"></div><div class="form-group"><label>Área</label><input type="text" name="areaInstrutor"></div><div class="form-actions"><button class="button button-primary" type="submit">Salvar instrutor</button><a class="button button-secondary" href="cadastrar_aula.php">Cadastrar aula</a></div></form></section></div></main></body></html>
