DROP DATABASE IF EXISTS `pt03_marcos_lopez`;
CREATE DATABASE `pt03_marcos_lopez` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pt03_marcos_lopez`;

-- Tabla principal: pokemons
-- Campos: id (PK), titulo, descripcion
CREATE TABLE `pokemons` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`titulo` VARCHAR(255) NOT NULL,
	`descripcion` TEXT DEFAULT NULL,
	`user_id` INT UNSIGNED DEFAULT NULL,
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `idx_user_id` (`user_id`),
	INDEX `idx_titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de usuarios
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
