-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 22 mai 2025 à 14:27
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `meetrun`
--

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250521140051', '2025-05-21 14:01:04', 3010);

-- --------------------------------------------------------

--
-- Structure de la table `event`
--

DROP TABLE IF EXISTS `event`;
CREATE TABLE IF NOT EXISTS `event` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organizer_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `date_event` datetime NOT NULL,
  `adress` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3BAE0AA7876C4DDA` (`organizer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `instant_message`
--

DROP TABLE IF EXISTS `instant_message`;
CREATE TABLE IF NOT EXISTS `instant_message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_message` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D047C08AF624B39D` (`sender_id`),
  KEY `IDX_D047C08ACD53EDB6` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `level_run`
--

DROP TABLE IF EXISTS `level_run`;
CREATE TABLE IF NOT EXISTS `level_run` (
  `id` int NOT NULL AUTO_INCREMENT,
  `level` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photo`
--

DROP TABLE IF EXISTS `photo`;
CREATE TABLE IF NOT EXISTS `photo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_14B7841871F7E88B` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
  `id` int NOT NULL AUTO_INCREMENT,
  `topic_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_message` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5A8A6C8D1F55203D` (`topic_id`),
  KEY `IDX_5A8A6C8DA76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `registration_event`
--

DROP TABLE IF EXISTS `registration_event`;
CREATE TABLE IF NOT EXISTS `registration_event` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `quantity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B404AA4F71F7E88B` (`event_id`),
  KEY `IDX_B404AA4FA76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `topic`
--

DROP TABLE IF EXISTS `topic`;
CREATE TABLE IF NOT EXISTS `topic` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` datetime NOT NULL,
  `is_closed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9D40DE1B12469DE2` (`category_id`),
  KEY `IDX_9D40DE1BA76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_register` datetime NOT NULL,
  `date_of_birth` datetime DEFAULT NULL,
  `is_banned` tinyint(1) NOT NULL,
  `picture_profil_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_user`
--

DROP TABLE IF EXISTS `user_user`;
CREATE TABLE IF NOT EXISTS `user_user` (
  `user_source` int NOT NULL,
  `user_target` int NOT NULL,
  PRIMARY KEY (`user_source`,`user_target`),
  KEY `IDX_F7129A803AD8644E` (`user_source`),
  KEY `IDX_F7129A80233D34C1` (`user_target`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `FK_3BAE0AA7876C4DDA` FOREIGN KEY (`organizer_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `instant_message`
--
ALTER TABLE `instant_message`
  ADD CONSTRAINT `FK_D047C08ACD53EDB6` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_D047C08AF624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `photo`
--
ALTER TABLE `photo`
  ADD CONSTRAINT `FK_14B7841871F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`);

--
-- Contraintes pour la table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `FK_5A8A6C8D1F55203D` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`id`),
  ADD CONSTRAINT `FK_5A8A6C8DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `registration_event`
--
ALTER TABLE `registration_event`
  ADD CONSTRAINT `FK_B404AA4F71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `FK_B404AA4FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `topic`
--
ALTER TABLE `topic`
  ADD CONSTRAINT `FK_9D40DE1B12469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  ADD CONSTRAINT `FK_9D40DE1BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `user_user`
--
ALTER TABLE `user_user`
  ADD CONSTRAINT `FK_F7129A80233D34C1` FOREIGN KEY (`user_target`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_F7129A803AD8644E` FOREIGN KEY (`user_source`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
