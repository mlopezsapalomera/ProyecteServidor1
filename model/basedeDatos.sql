-- Script de creación de la base de datos y tabla inicial para el proyecto
-- Uso: importar este .sql en tu servidor MySQL/MariaDB (por ejemplo con phpMyAdmin o mysql CLI)

-- Cambia el nombre de la base de datos si lo deseas
DROP DATABASE IF EXISTS `proyecte_servidor1`;
CREATE DATABASE `proyecte_servidor1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `proyecte_servidor1`;

-- Tabla principal: pokemons
-- Campos: id (PK), titulo, descripcion
CREATE TABLE `pokemons` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`titulo` VARCHAR(255) NOT NULL,
	`descripcion` TEXT DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Fin del script
