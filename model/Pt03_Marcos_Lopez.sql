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
  `profile_image` VARCHAR(255) DEFAULT 'userDefaultImg.jpg',
  `role` VARCHAR(20) DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuarios de prueba
INSERT INTO `users` (`username`, `email`, `password_hash`, `profile_image`, `role`) VALUES
('ash_ketchum', 'ash@pokemon.com', '$2y$10$rX5YqJ8vQp9k1LZt9MqDKehDq4N8WYzQv6C5L3D9xRnP2K8mJfH7G', 'userDefaultImg.jpg', 'user'),
('misty_water', 'misty@pokemon.com', '$2y$10$sA6ZrK9wRq0m2MAu0NrELfhEr5O9XZaRw7D6M4E0ySnQ3L9nKgI8H', 'userDefaultImg.jpg', 'user'),
('brock_stone', 'brock@pokemon.com', '$2y$10$tB7AsL0xSr1n3NBv1OsFMghFs6P0YAbSx8E7N5F1zToR4M0oLhJ9I', 'userDefaultImg.jpg', 'user'),
('gary_oak', 'gary@pokemon.com', '$2y$10$uC8BtM1ySs2o4OCw2PtGNhiGt7Q1ZBcTy9F8O6G2zUpS5N1pMiK0J', 'userDefaultImg.jpg', 'user');

-- Insertar publicaciones de prueba
INSERT INTO `pokemons` (`titulo`, `descripcion`, `user_id`) VALUES
('Pikachu', 'Mi primer Pokémon eléctrico, siempre a mi lado en cada aventura. ⚡', 1),
('Staryu', 'Pokémon acuático con forma de estrella, perfecto para batallas en el agua. 💧', 2),
('Onix', 'Pokémon de roca impresionante, mi compañero más fuerte y resistente. 🪨', 3),
('Eevee', 'Un Pokémon con múltiples evoluciones posibles, mi favorito absoluto. 🦊', 4);
