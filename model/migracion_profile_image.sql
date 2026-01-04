-- Script de migración para añadir columna profile_image a usuarios existentes
-- Ejecutar este script si ya tienes la base de datos creada

USE `pt03_marcos_lopez`;

-- Añadir columna profile_image si no existe
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `profile_image` VARCHAR(255) DEFAULT 'userDefaultImg.jpg' AFTER `password_hash`;
