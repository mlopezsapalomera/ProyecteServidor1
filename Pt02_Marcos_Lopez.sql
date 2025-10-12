-- Esquema para la BDD del proyecto CRUD articulos
-- Autor: Marcos López
-- Fecha: 2025-10-12
-- Descripción: Base de datos para sistema CRUD con PDO y Prepared Statements

-- Eliminar la base de datos si existe (para empezar limpio)
DROP DATABASE IF EXISTS `pt02_marcos_lopez`;

-- Crear la base de datos con charset UTF-8
CREATE DATABASE `pt02_marcos_lopez` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos creada
USE `pt02_marcos_lopez`;

-- Crear tabla de artículos/pokemons
CREATE TABLE `pokemons` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(255) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_titulo` (`titulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

