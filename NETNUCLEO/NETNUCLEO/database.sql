CREATE DATABASE IF NOT EXISTS sisged CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sisged;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS login_log, aula, administrador, aluno, instrutor, materia, turma, sala;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE instrutor (
 idInstrutor INT AUTO_INCREMENT PRIMARY KEY,
 nomeInstrutor VARCHAR(120) NOT NULL,
 cpfInstrutor VARCHAR(20) NULL,
 emailInstrutor VARCHAR(150) NULL,
 telefoneInstrutor VARCHAR(30) NULL,
 areaInstrutor VARCHAR(120) NULL,
 statusInstrutor TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE aluno (
 idAluno INT AUTO_INCREMENT PRIMARY KEY,
 nomeAluno VARCHAR(120) NOT NULL,
 matriculaAluno VARCHAR(30) NULL,
 emailAluno VARCHAR(150) NULL
) ENGINE=InnoDB;

CREATE TABLE materia (
 idMateria INT AUTO_INCREMENT PRIMARY KEY,
 siglaMateria VARCHAR(30) NOT NULL,
 nomeMateria VARCHAR(120) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE turma (
 idTurma INT AUTO_INCREMENT PRIMARY KEY,
 codigoTurma VARCHAR(40) NOT NULL,
 turnoTurma VARCHAR(30) NOT NULL,
 datafimTurma DATE NULL
) ENGINE=InnoDB;

CREATE TABLE sala (
 idSala INT AUTO_INCREMENT PRIMARY KEY,
 nomeSala VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE administrador (
 idAdministrador INT AUTO_INCREMENT PRIMARY KEY,
 usuarioAdministrador VARCHAR(80) NOT NULL UNIQUE,
 emailAdministrador VARCHAR(150) NULL,
 senhaAdministrador VARCHAR(255) NOT NULL,
 unidadeAdministrador VARCHAR(120) NOT NULL DEFAULT 'Horto',
 papelAdministrador VARCHAR(30) NOT NULL DEFAULT 'admin',
 Instrutor_idInstrutor INT NULL,
 Aluno_idAluno INT NULL,
 FOREIGN KEY (Instrutor_idInstrutor) REFERENCES instrutor(idInstrutor) ON DELETE SET NULL,
 FOREIGN KEY (Aluno_idAluno) REFERENCES aluno(idAluno) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE aula (
 idAula INT AUTO_INCREMENT PRIMARY KEY,
 Administrador_idAdministrador INT NULL,
 Instrutor_idInstrutor INT NOT NULL,
 Materia_idMateria INT NOT NULL,
 Turma_idTurma INT NOT NULL,
 Sala_idSala INT NULL,
 dataAula DATE NOT NULL,
 turnoAula VARCHAR(30) NOT NULL,
 horarioinicioAula TIME NOT NULL,
 horariofimAula TIME NOT NULL,
 duracaoAula TIME NULL,
 tipoAula VARCHAR(30) NOT NULL DEFAULT 'Presencial',
 statusAula TINYINT(1) NOT NULL DEFAULT 1,
 FOREIGN KEY (Administrador_idAdministrador) REFERENCES administrador(idAdministrador) ON DELETE SET NULL,
 FOREIGN KEY (Instrutor_idInstrutor) REFERENCES instrutor(idInstrutor),
 FOREIGN KEY (Materia_idMateria) REFERENCES materia(idMateria),
 FOREIGN KEY (Turma_idTurma) REFERENCES turma(idTurma),
 FOREIGN KEY (Sala_idSala) REFERENCES sala(idSala) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE login_log (
 idLoginLog INT AUTO_INCREMENT PRIMARY KEY,
 Administrador_idAdministrador INT NULL,
 usuarioInformado VARCHAR(80) NULL,
 unidadeInformada VARCHAR(120) NULL,
 dataLogin DATE NOT NULL,
 horarioLogin TIME NOT NULL,
 sucesso TINYINT(1) NOT NULL DEFAULT 0,
 ipLogin VARCHAR(45) NULL,
 FOREIGN KEY (Administrador_idAdministrador) REFERENCES administrador(idAdministrador) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO instrutor (nomeInstrutor, cpfInstrutor, emailInstrutor, telefoneInstrutor, areaInstrutor) VALUES
('Ana Paula Mendes',NULL,'ana.mendes@exemplo.com',NULL,'Matemática'),
('Bruno Henrique Silva',NULL,'bruno.silva@exemplo.com',NULL,'Programação'),
('Carla Fernanda Souza',NULL,'carla.souza@exemplo.com',NULL,'Linguagens'),
('Diego Alves Rocha',NULL,'diego.rocha@exemplo.com',NULL,'Eletrônica');

INSERT INTO aluno (nomeAluno, matriculaAluno, emailAluno) VALUES
('Arthur César', 'AL001', 'arthur@exemplo.com'),
('Mariana Oliveira', 'AL002', 'mariana@exemplo.com');

INSERT INTO materia (siglaMateria, nomeMateria) VALUES
('MAT','Matemática'),('PROG','Programação Web'),('LP','Língua Portuguesa'),('ELE','Eletrônica Básica');

INSERT INTO turma (codigoTurma, turnoTurma, datafimTurma) VALUES
('1A-INFO','Manhã','2027-12-31'),('2A-INFO','Tarde','2027-12-31'),('3A-ADM','Noite','2027-12-31'),('1B-ELET','Manhã','2027-12-31');

INSERT INTO sala (nomeSala) VALUES ('Sala 01'),('Laboratório de Informática'),('Sala 03'),('Laboratório de Eletrônica');

-- Senha de demonstração: 123456
INSERT INTO administrador (usuarioAdministrador,emailAdministrador,senhaAdministrador,unidadeAdministrador,papelAdministrador,Instrutor_idInstrutor,Aluno_idAluno) VALUES
('admin','admin@exemplo.com','$2y$12$nJ6vHjDWz4qZFd.MKkJCMOSqGJqpklFRlQGT5v15GMRarQzKE7gSO','Horto','admin',NULL,NULL),
('instrutor','instrutor@exemplo.com','$2y$12$nJ6vHjDWz4qZFd.MKkJCMOSqGJqpklFRlQGT5v15GMRarQzKE7gSO','Horto','instrutor',2,NULL),
('aluno','aluno@exemplo.com','$2y$12$nJ6vHjDWz4qZFd.MKkJCMOSqGJqpklFRlQGT5v15GMRarQzKE7gSO','Horto','aluno',NULL,1);

-- Presets cobrindo diferentes combinações de filtros.
INSERT INTO aula (Administrador_idAdministrador,Instrutor_idInstrutor,Materia_idMateria,Turma_idTurma,Sala_idSala,dataAula,turnoAula,horarioinicioAula,horariofimAula,duracaoAula,tipoAula,statusAula) VALUES
(1,1,1,1,1,'2026-09-01','Manhã','08:00:00','09:00:00','01:00:00','Presencial',1),
(1,2,2,1,2,'2026-09-01','Manhã','09:10:00','10:40:00','01:30:00','Presencial',1),
(1,3,3,1,1,'2026-09-02','Manhã','08:00:00','09:30:00','01:30:00','Híbrida',1),
(1,2,2,2,2,'2026-09-02','Tarde','13:00:00','14:30:00','01:30:00','Online',1),
(1,1,1,2,3,'2026-09-03','Tarde','14:40:00','16:10:00','01:30:00','Presencial',0),
(1,4,4,4,4,'2026-09-03','Manhã','10:00:00','11:30:00','01:30:00','Presencial',1),
(1,3,3,3,3,'2026-09-04','Noite','19:00:00','20:30:00','01:30:00','Presencial',1),
(1,2,2,2,2,'2026-09-04','Tarde','16:20:00','17:20:00','01:00:00','Híbrida',1),
(1,1,1,1,1,'2026-09-08','Manhã','08:00:00','09:00:00','01:00:00','Presencial',1),
(1,4,4,4,4,'2026-09-09','Manhã','08:00:00','09:30:00','01:30:00','Online',0),
(1,2,2,1,2,'2026-09-10','Manhã','10:00:00','11:30:00','01:30:00','Presencial',1),
(1,3,3,3,3,'2026-09-10','Noite','19:00:00','20:00:00','01:00:00','Online',1);
