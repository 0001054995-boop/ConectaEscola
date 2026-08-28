-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 21/08/2026 às 21:13
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_web`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas`
--

DROP TABLE IF EXISTS `aulas`;
CREATE TABLE IF NOT EXISTS `aulas` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `horario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `turma` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instrutor` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sala` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `situacao` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periodo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Manhã',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `aulas`
--

INSERT INTO `aulas` (`id`, `data`, `horario`, `turma`, `instrutor`, `sala`, `situacao`, `periodo`) VALUES
(1, '2026-08-21', '08:00 - 10:00', 'Turma 01', 'João da Silva', 'Lab 01', 'Confirmado', 'Manhã');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_email` (`email`),
  KEY `idx_usuarios_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `status`, `criado_em`, `atualizado_em`) VALUES
(1, 'João Silva', 'joao@email.com', '123456', 'ativo', '2026-08-21 19:21:41', '2026-08-21 19:21:41');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
