-- Migración para agregar soporte OAuth a tabla users
-- Fecha: 2026-03-10
-- Descripción: Agrega campos oauth_provider, oauth_uid, oauth_token y hace password_hash nullable

USE `pt03_marcos_lopez`;

-- Modificar la tabla users para soportar OAuth
ALTER TABLE `users`
MODIFY COLUMN `password_hash` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `oauth_provider` VARCHAR(50) DEFAULT NULL AFTER `password_hash`,
ADD COLUMN `oauth_uid` VARCHAR(255) DEFAULT NULL AFTER `oauth_provider`,
ADD COLUMN `oauth_token` TEXT DEFAULT NULL AFTER `oauth_uid`,
ADD UNIQUE KEY `uq_oauth` (`oauth_provider`, `oauth_uid`);

-- Verificar cambios
SHOW COLUMNS FROM `users`;
