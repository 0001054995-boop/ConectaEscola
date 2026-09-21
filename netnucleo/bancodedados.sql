CREATE DATABASE IF NOT EXISTS sisged CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sisged;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS login_log;
DROP TABLE IF EXISTS aula;
DROP TABLE IF EXISTS sala;
DROP TABLE IF EXISTS materia;
DROP TABLE IF EXISTS instrutor;
DROP TABLE IF EXISTS aluno;
DROP TABLE IF EXISTS curso;
DROP TABLE IF EXISTS turma;
DROP TABLE IF EXISTS administrador;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE administrador (
  idAdministrador INT NOT NULL AUTO_INCREMENT,
  usuarioAdministrador VARCHAR(50) NOT NULL,
  emailAdministrador VARCHAR(100) NULL,
  senhaAdministrador VARCHAR(255) NOT NULL,
  unidadeAdministrador VARCHAR(50) NOT NULL DEFAULT 'Horto',
  papelAdministrador ENUM('admin','instrutor','aluno') NOT NULL DEFAULT 'admin',
  Instrutor_idInstrutor INT NULL,
  Aluno_idAluno INT NULL,
  PRIMARY KEY (idAdministrador),
  UNIQUE KEY uq_administrador_usuario (usuarioAdministrador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE turma (
  idTurma INT NOT NULL AUTO_INCREMENT,
  codigoTurma INT NOT NULL,
  turnoTurma VARCHAR(20) NOT NULL,
  datainicioTurma DATE NULL,
  datafimTurma DATE NULL,
  PRIMARY KEY (idTurma),
  UNIQUE KEY uq_turma_codigo (codigoTurma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE instrutor (
  idInstrutor INT NOT NULL AUTO_INCREMENT,
  nomeInstrutor VARCHAR(100) NOT NULL,
  cpfInstrutor VARCHAR(11) NULL,
  emailInstrutor VARCHAR(100) NULL,
  telefoneInstrutor VARCHAR(20) NULL,
  areaInstrutor VARCHAR(50) NULL,
  statusInstrutor TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (idInstrutor),
  UNIQUE KEY uq_instrutor_cpf (cpfInstrutor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE materia (
  idMateria INT NOT NULL AUTO_INCREMENT,
  siglaMateria VARCHAR(20) NOT NULL,
  nomeMateria VARCHAR(100) NOT NULL,
  cargahorariaMateria TIME NULL,
  ementaMateria VARCHAR(255) NULL,
  PRIMARY KEY (idMateria),
  UNIQUE KEY uq_materia_sigla (siglaMateria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sala (
  idSala INT NOT NULL AUTO_INCREMENT,
  nomeSala VARCHAR(50) NOT NULL,
  capacidadeSala INT NULL,
  tipoAula VARCHAR(50) NULL,
  blocoandarAula VARCHAR(50) NULL,
  PRIMARY KEY (idSala),
  UNIQUE KEY uq_sala_nome (nomeSala)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE aluno (
  idAluno INT NOT NULL AUTO_INCREMENT,
  nomeAluno VARCHAR(100) NULL,
  cpfAluno VARCHAR(11) NULL,
  emailAluno VARCHAR(100) NULL,
  telefoneAluno VARCHAR(20) NULL,
  Turma_idTurma INT NULL,
  PRIMARY KEY (idAluno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE curso (
  idCurso INT NOT NULL AUTO_INCREMENT,
  Turma_idTurma INT NULL,
  nomeCurso VARCHAR(100) NULL,
  modalidadeCurso VARCHAR(50) NULL,
  cargahorariaCurso INT NULL,
  nivelCurso INT NULL,
  PRIMARY KEY (idCurso),
  CONSTRAINT fk_curso_turma FOREIGN KEY (Turma_idTurma) REFERENCES turma(idTurma) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE aula (
  idAula INT NOT NULL AUTO_INCREMENT,
  Administrador_idAdministrador INT NULL,
  Aluno_idAluno INT NULL,
  Instrutor_idInstrutor INT NOT NULL,
  Materia_idMateria INT NOT NULL,
  Turma_idTurma INT NOT NULL,
  Sala_idSala INT NULL,
  dataAula DATE NOT NULL,
  turnoAula VARCHAR(20) NOT NULL,
  horarioinicioAula TIME NOT NULL,
  horariofimAula TIME NOT NULL,
  duracaoAula TIME NULL,
  tipoAula VARCHAR(50) NOT NULL DEFAULT 'Presencial',
  statusAula TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (idAula),
  KEY idx_aula_data (dataAula),
  KEY idx_aula_instrutor (Instrutor_idInstrutor),
  KEY idx_aula_materia (Materia_idMateria),
  KEY idx_aula_turma (Turma_idTurma),
  KEY idx_aula_status (statusAula),
  CONSTRAINT fk_aula_administrador FOREIGN KEY (Administrador_idAdministrador) REFERENCES administrador(idAdministrador) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_aula_aluno FOREIGN KEY (Aluno_idAluno) REFERENCES aluno(idAluno) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_aula_instrutor FOREIGN KEY (Instrutor_idInstrutor) REFERENCES instrutor(idInstrutor) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_aula_materia FOREIGN KEY (Materia_idMateria) REFERENCES materia(idMateria) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_aula_turma FOREIGN KEY (Turma_idTurma) REFERENCES turma(idTurma) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_aula_sala FOREIGN KEY (Sala_idSala) REFERENCES sala(idSala) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_log (
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
  KEY idx_login_usuario (usuarioInformado),
  CONSTRAINT fk_login_administrador FOREIGN KEY (Administrador_idAdministrador) REFERENCES administrador(idAdministrador) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuário inicial para testes: Sander / NossaCasinha / Horto.
-- A senha já está armazenada com password_hash().
INSERT INTO administrador (usuarioAdministrador, emailAdministrador, senhaAdministrador, unidadeAdministrador, papelAdministrador)
VALUES ('Sander', 'sander@netnucleo.local', '$2y$12$HZowrtjVWr4CKGJT2QBceuYY4IMBkowvUfgmwAYQf7dUCjSWiZLzm', 'Horto', 'admin');

INSERT INTO instrutor (nomeInstrutor, cpfInstrutor, emailInstrutor, telefoneInstrutor, areaInstrutor, statusInstrutor) VALUES
('Tubinho', '12345678901', 'joao.pereira@exemplo.com', '31999990001', 'Redes', 1),
('Guidu Ratu', '23456789012', 'maria.souza@exemplo.com', '31999990002', 'Programação', 1),
('Migles Belo', '34567890123', 'carlos.lima@exemplo.com', '31999990003', 'Banco de Dados', 1),
('Edson Paulo Nascimento', '45678901234', 'ana.oliveira@exemplo.com', '31999990004', 'Desenvolvimento Web', 1);

INSERT INTO materia (siglaMateria, nomeMateria, cargahorariaMateria, ementaMateria) VALUES
('RED', 'Redes de Computadores', '02:00:00', 'Fundamentos de redes e comunicação de dados.'),
('BDD', 'Banco de Dados', '02:00:00', 'Modelagem, SQL, relacionamentos e consultas.'),
('PROG', 'Programação', '02:00:00', 'Lógica, algoritmos e desenvolvimento de aplicações.'),
('WEB', 'Desenvolvimento Web', '02:00:00', 'HTML, CSS, JavaScript, PHP e aplicações web.'),
('SO', 'Sistemas Operacionais', '02:00:00', 'Conceitos de sistemas operacionais.'),
('LOG', 'Lógica de Programação', '02:00:00', 'Algoritmos, estruturas condicionais e repetição.');

INSERT INTO turma (codigoTurma, turnoTurma, datainicioTurma, datafimTurma) VALUES
(101, 'Manhã', '2026-02-01', '2026-12-15'),
(102, 'Tarde', '2026-02-01', '2026-12-15'),
(103, 'Noite', '2026-02-01', '2026-12-15');

INSERT INTO aluno (nomeAluno, cpfAluno, emailAluno, telefoneAluno, Turma_idTurma) VALUES ('Buzz', '56789012345', 'lucas.almeida@exemplo.com', '31999990005', 1), ('Beatriz Costa', '67890123456', 'beatriz.costa@exemplo.com', '31999990006', 2);

INSERT INTO sala (nomeSala, capacidadeSala, tipoAula, blocoandarAula) VALUES
('Laboratório 01', 30, 'Laboratório', 'Bloco A - 1º andar'),
('Laboratório 02', 30, 'Laboratório', 'Bloco A - 1º andar'),
('Sala 101', 35, 'Sala de aula', 'Bloco B - 1º andar');

-- Contas de teste: Tubinho / Jango e Buzz / Banana.
INSERT INTO administrador (usuarioAdministrador, emailAdministrador, senhaAdministrador, unidadeAdministrador, papelAdministrador, Instrutor_idInstrutor)
VALUES ('Tubinho', 'tubinho@netnucleo.local', '$2y$12$BOhrF9PG5ev/X5xt0dsyCulEKViXb33x7Qt69djdjSQUM0Fw.7DO2', 'Horto', 'instrutor', 1);
INSERT INTO administrador (usuarioAdministrador, emailAdministrador, senhaAdministrador, unidadeAdministrador, papelAdministrador, Aluno_idAluno)
VALUES ('Buzz', 'buzz@netnucleo.local', '$2y$12$sC6fsbZD6tHXPVdOlS/ndO6W21gZQimtJHuytECDc4OOw81EdRjYi', 'Horto', 'aluno', 1);

-- Registros de exemplo para a consulta funcionar imediatamente após importar o banco.
INSERT INTO aula (Administrador_idAdministrador, Instrutor_idInstrutor, Materia_idMateria, Turma_idTurma, Sala_idSala, dataAula, turnoAula, horarioinicioAula, horariofimAula, duracaoAula, tipoAula, statusAula) VALUES
(1, 1, 1, 1, 1, '2026-09-01', 'Manhã', '07:00:00', '08:40:00', '01:40:00', 'Presencial', 1),
(1, 3, 2, 2, 2, '2026-09-01', 'Tarde', '13:00:00', '14:40:00', '01:40:00', 'Presencial', 1),
(1, 4, 4, 3, 3, '2026-09-01', 'Noite', '18:30:00', '20:10:00', '01:40:00', 'Presencial', 0),
(1, 1, 1, 1, 1, '2026-09-02', 'Manhã', '07:00:00', '08:40:00', '01:40:00', 'Presencial', 1),
(1, 3, 2, 2, 2, '2026-09-02', 'Tarde', '13:00:00', '14:40:00', '01:40:00', 'Presencial', 1),
(1, 4, 4, 3, 3, '2026-09-02', 'Noite', '18:30:00', '20:10:00', '01:40:00', 'Presencial', 0),
(1, 2, 3, 1, 2, '2026-09-03', 'Manhã', '08:50:00', '10:30:00', '01:40:00', 'Presencial', 1),
(1, 1, 5, 2, 1, '2026-09-04', 'Tarde', '14:50:00', '16:30:00', '01:40:00', 'Presencial', 0),
(1, 1, 6, 3, 3, '2026-09-05', 'Noite', '20:20:00', '22:00:00', '01:40:00', 'Presencial', 0);

ALTER TABLE aluno ADD KEY idx_aluno_turma (Turma_idTurma);
ALTER TABLE aluno ADD CONSTRAINT fk_aluno_turma FOREIGN KEY (Turma_idTurma) REFERENCES turma(idTurma) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE administrador ADD KEY idx_admin_instrutor (Instrutor_idInstrutor), ADD KEY idx_admin_aluno (Aluno_idAluno);
ALTER TABLE administrador ADD CONSTRAINT fk_admin_instrutor FOREIGN KEY (Instrutor_idInstrutor) REFERENCES instrutor(idInstrutor) ON UPDATE CASCADE ON DELETE SET NULL, ADD CONSTRAINT fk_admin_aluno FOREIGN KEY (Aluno_idAluno) REFERENCES aluno(idAluno) ON UPDATE CASCADE ON DELETE SET NULL;
