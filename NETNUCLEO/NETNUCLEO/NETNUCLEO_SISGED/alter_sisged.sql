USE sisged;

-- Migração para a versão com três perfis.
ALTER TABLE administrador ADD COLUMN papelAdministrador ENUM('admin','instrutor','aluno') NOT NULL DEFAULT 'admin';
ALTER TABLE administrador ADD COLUMN Instrutor_idInstrutor INT NULL;
ALTER TABLE administrador ADD COLUMN Aluno_idAluno INT NULL;
ALTER TABLE aluno ADD COLUMN Turma_idTurma INT NULL;

CREATE TABLE IF NOT EXISTS login_log (
  idLogin INT NOT NULL AUTO_INCREMENT,
  Administrador_idAdministrador INT NULL,
  usuarioInformado VARCHAR(50) NOT NULL,
  unidadeInformada VARCHAR(50) NULL,
  dataLogin DATE NOT NULL,
  horarioLogin TIME NOT NULL,
  sucesso TINYINT(1) NOT NULL DEFAULT 0,
  ipLogin VARCHAR(45) NULL,
  PRIMARY KEY (idLogin),
  KEY idx_login_data (dataLogin),
  KEY idx_login_usuario (usuarioInformado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Depois de conferir os IDs existentes, podem ser criadas as FKs abaixo.
-- Elas ficam separadas para permitir adaptar uma base antiga sem apagar dados.
-- ALTER TABLE aluno ADD CONSTRAINT fk_aluno_turma FOREIGN KEY (Turma_idTurma) REFERENCES turma(idTurma) ON UPDATE CASCADE ON DELETE SET NULL;
-- ALTER TABLE administrador ADD CONSTRAINT fk_admin_instrutor FOREIGN KEY (Instrutor_idInstrutor) REFERENCES instrutor(idInstrutor) ON UPDATE CASCADE ON DELETE SET NULL;
-- ALTER TABLE administrador ADD CONSTRAINT fk_admin_aluno FOREIGN KEY (Aluno_idAluno) REFERENCES aluno(idAluno) ON UPDATE CASCADE ON DELETE SET NULL;

-- Atualiza os três logins de teste da versão atual.
-- Se a base antiga já possui estes usuários, os dados são atualizados sem criar duplicatas.
UPDATE administrador
SET usuarioAdministrador='Sander',
    emailAdministrador='sander@netnucleo.local',
    senhaAdministrador='$2y$12$HZowrtjVWr4CKGJT2QBceuYY4IMBkowvUfgmwAYQf7dUCjSWiZLzm',
    unidadeAdministrador='Horto',
    papelAdministrador='admin',
    Instrutor_idInstrutor=NULL,
    Aluno_idAluno=NULL
WHERE usuarioAdministrador IN ('admin','Sander')
LIMIT 1;

UPDATE administrador
SET usuarioAdministrador='Tubinho',
    emailAdministrador='tubinho@netnucleo.local',
    senhaAdministrador='$2y$12$BOhrF9PG5ev/X5xt0dsyCulEKViXb33x7Qt69djdjSQUM0Fw.7DO2',
    unidadeAdministrador='Horto',
    papelAdministrador='instrutor',
    Instrutor_idInstrutor=1,
    Aluno_idAluno=NULL
WHERE usuarioAdministrador IN ('instrutor','Tubinho')
LIMIT 1;

UPDATE administrador
SET usuarioAdministrador='Buzz',
    emailAdministrador='buzz@netnucleo.local',
    senhaAdministrador='$2y$12$sC6fsbZD6tHXPVdOlS/ndO6W21gZQimtJHuytECDc4OOw81EdRjYi',
    unidadeAdministrador='Horto',
    papelAdministrador='aluno',
    Instrutor_idInstrutor=NULL,
    Aluno_idAluno=1
WHERE usuarioAdministrador IN ('aluno','Buzz')
LIMIT 1;

UPDATE instrutor SET nomeInstrutor='Tubinho' WHERE idInstrutor=1;
UPDATE aluno SET nomeAluno='Buzz' WHERE idAluno=1;
