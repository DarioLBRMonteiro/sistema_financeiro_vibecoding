CREATE DATABASE IF NOT EXISTS `gestao_financeira` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `gestao_financeira`;

-- --------------------------------------------------------
-- Tabela: usuarios
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `token_recuperacao` VARCHAR(64) DEFAULT NULL,
  `token_expiracao` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela: categorias
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED DEFAULT NULL, -- NULL para categorias padrão do sistema
  `nome` VARCHAR(50) NOT NULL,
  `tipo` ENUM('RECEITA', 'DESPESA') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_categorias_usuario` 
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela: lancamentos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lancamentos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED NOT NULL,
  `categoria_id` INT UNSIGNED NOT NULL,
  `descricao` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `data_movimentacao` DATE NOT NULL,
  `tipo` ENUM('RECEITA', 'DESPESA') NOT NULL,
  `status` ENUM('PAGO', 'PENDENTE') NOT NULL DEFAULT 'PAGO',
  `forma_pagamento` ENUM('DINHEIRO', 'PIX', 'DEBITO', 'CREDITO') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_lancamentos_usuario` 
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lancamentos_categoria` 
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Carga Inicial: Categorias Padrão (usuario_id = NULL)
-- --------------------------------------------------------
INSERT INTO `categorias` (`usuario_id`, `nome`, `tipo`) VALUES
(NULL, 'Salário', 'RECEITA'),
(NULL, 'Investimentos', 'RECEITA'),
(NULL, 'Outras Receitas', 'RECEITA'),
(NULL, 'Alimentação', 'DESPESA'),
(NULL, 'Moradia', 'DESPESA'),
(NULL, 'Transporte', 'DESPESA'),
(NULL, 'Saúde', 'DESPESA'),
(NULL, 'Lazer', 'DESPESA'),
(NULL, 'Outras Despesas', 'DESPESA')
ON DUPLICATE KEY UPDATE `nome` = `nome`;
