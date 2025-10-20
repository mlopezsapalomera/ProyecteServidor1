DROP DATABASE IF EXISTS `pt02_marcos_lopez`;
CREATE DATABASE `pt02_marcos_lopez` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pt02_marcos_lopez`;

-- Tabla principal: pokemons
-- Campos: id (PK), titulo, descripcion
CREATE TABLE `pokemons` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`titulo` VARCHAR(255) NOT NULL,
	`descripcion` TEXT DEFAULT NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `idx_titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
