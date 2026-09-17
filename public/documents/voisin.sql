-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 17, 2026 at 03:53 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `voisin`
--

-- --------------------------------------------------------

--
-- Table structure for table `demande_amitie`
--

CREATE TABLE `demande_amitie` (
  `id` int NOT NULL,
  `date_demande` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `demandeur_id` int NOT NULL,
  `destinataire_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `demande_amitie`
--

INSERT INTO `demande_amitie` (`id`, `date_demande`, `status`, `demandeur_id`, `destinataire_id`) VALUES
(1, '2026-09-07 06:51:10', 'acceptee', 1, 2),
(2, '2026-09-07 07:18:07', 'acceptee', 3, 1),
(3, '2026-09-07 07:19:00', 'refusee', 1, 2),
(4, '2026-09-07 07:19:29', 'refusee', 1, 2),
(5, '2026-09-07 10:31:21', 'refusee', 3, 2),
(6, '2026-09-07 11:04:23', 'refusee', 4, 1),
(7, '2026-09-07 14:11:07', 'en_attente', 2, 4),
(8, '2026-09-11 15:15:45', 'en_attente', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260905101045', '2026-09-05 10:12:49', 238);

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publication`
--

CREATE TABLE `publication` (
  `id` int NOT NULL,
  `texte` longtext,
  `image` varchar(255) DEFAULT NULL,
  `visibilite` varchar(255) NOT NULL,
  `date_publication` datetime NOT NULL,
  `auteur_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `publication`
--

INSERT INTO `publication` (`id`, `texte`, `image`, `visibilite`, `date_publication`, `auteur_id`) VALUES
(1, 'jghjkhjhj', '6a9c1b46c373d.jpg', 'publique', '2026-09-05 13:38:14', 1),
(3, 'Vend vélo enfant, prix à débattre', '6a9fcab58360c.png', 'publique', '2026-09-07 06:00:43', 1),
(5, 'petite apéro entre nous', '6a9e77b352083.png', 'publique', '2026-09-07 08:37:07', 2),
(6, 'Invitation diner', '6a9e92dc844a0.png', 'amis', '2026-09-07 10:33:00', 3);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `pseudo` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `biographie` longtext,
  `date_inscription` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `pseudo`, `photo`, `biographie`, `date_inscription`) VALUES
(1, 'dom@gmail.com', '[]', '$2y$13$9GuYMEgUel9BLN6BFBkOGOMIM19tiZ6cO1Vcmspf4ayrYPdfEwmwu', 'Dom', '6a9c149037aed.png', 'Vive le soleil', '2026-09-05 13:09:35'),
(2, 'nath@gmail.com', '[]', '$2y$13$vsUc9G89nQ/xs6xbObH2fORKLaWdtwAzkj14lBYirJoaFEbrV4WJq', 'Nath', '6a9e866b19c02.png', 'La voisine qui rend service', '2026-09-07 06:29:40'),
(3, 'math@gmail.com', '[]', '$2y$13$qnLGw5lo6HgYGds3IOFitOizwV15Ap37cxcK6inAD.YEjCwgmoIoW', 'Math', '6a9e65219f544.png', NULL, '2026-09-07 07:17:52'),
(4, 'willou@gmail.com', '[]', '$2y$13$VB49z/smwcP2XXSZ2qyUcOKv8gcJr3qhCXHoJVsR/kRRzfGoqB8yW', 'Will', '6a9e9a0ea8890.png', 'Un ami qui vous veux du bien', '2026-09-07 11:03:42'),
(5, 'julie@gmail.com', '[]', '$2y$13$HAIfK6jONaVqbpOs/xO.suC.vzIpTIqXgOpMh1yGoIHzCvP8duYSC', 'Julie', '6a9fc2c42ced8.png', 'Ma passion c\'est le point de croix', '2026-09-08 08:09:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `demande_amitie`
--
ALTER TABLE `demande_amitie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CDBDB57395A6EE59` (`demandeur_id`),
  ADD KEY `IDX_CDBDB573A4F84F6E` (`destinataire_id`);

--
-- Indexes for table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Indexes for table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AF3C677960BB6FE6` (`auteur_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `demande_amitie`
--
ALTER TABLE `demande_amitie`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publication`
--
ALTER TABLE `publication`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `demande_amitie`
--
ALTER TABLE `demande_amitie`
  ADD CONSTRAINT `FK_CDBDB57395A6EE59` FOREIGN KEY (`demandeur_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_CDBDB573A4F84F6E` FOREIGN KEY (`destinataire_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `publication`
--
ALTER TABLE `publication`
  ADD CONSTRAINT `FK_AF3C677960BB6FE6` FOREIGN KEY (`auteur_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
