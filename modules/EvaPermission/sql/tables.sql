SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `scrapy` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `scrapy`;

DROP TABLE IF EXISTS `eva_permission_apikeys`;
CREATE TABLE IF NOT EXISTS `eva_permission_apikeys` (
  `userId` int(10) NOT NULL,
  `apikey` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `eva_permission_operations`;
CREATE TABLE IF NOT EXISTS `eva_permission_operations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `operationKey` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `resourceId` int(10) NOT NULL,
  `resourceKey` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_permission_resources`;
CREATE TABLE IF NOT EXISTS `eva_permission_resources` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `resourceKey` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `resourceGroup` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'app' COMMENT 'app | api | backend',
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resourceKey` (`resourceKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_permission_roles`;
CREATE TABLE IF NOT EXISTS `eva_permission_roles` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `roleKey` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roleKey` (`roleKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `eva_permission_roles_operations`;
CREATE TABLE IF NOT EXISTS `eva_permission_roles_operations` (
  `roleId` int(4) NOT NULL,
  `operationId` int(10) NOT NULL,
  PRIMARY KEY (`roleId`,`operationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `eva_permission_users_roles`;
CREATE TABLE IF NOT EXISTS `eva_permission_users_roles` (
  `userId` int(10) NOT NULL,
  `roleId` int(4) NOT NULL,
  PRIMARY KEY (`userId`,`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
