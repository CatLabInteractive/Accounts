-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Gegenereerd op: 31 mei 2015 om 19:03
-- Serverversie: 5.6.24-0ubuntu2
-- PHP-versie: 5.6.4-4ubuntu6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `accounts`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `neuron_users`
--

CREATE TABLE IF NOT EXISTS `neuron_users` (
`u_id` int(11) NOT NULL,
  `u_email` varchar(255) DEFAULT NULL,
  `u_username` varchar(50) DEFAULT NULL,
  `u_password` varchar(255) DEFAULT NULL,
  `u_resetPassword` tinyint(1) NOT NULL DEFAULT '0',
  `u_emailVerified` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `neuron_users_deligated`
--

CREATE TABLE IF NOT EXISTS `neuron_users_deligated` (
`ud_id` int(11) NOT NULL,
  `u_id` int(11) DEFAULT NULL,
  `ud_type` varchar(20) NOT NULL,
  `ud_unique_id` varchar(255) NOT NULL,
  `ud_name` text,
  `ud_firstname` text,
  `ud_lastname` text,
  `ud_access_token` text,
  `ud_gender` enum('MALE','FEMALE') DEFAULT NULL,
  `ud_email` text,
  `ud_birthday` date DEFAULT NULL,
  `ud_locale` varchar(5) DEFAULT NULL,
  `ud_avatar` text,
  `ud_url` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `neuron_users_emails`
--

CREATE TABLE IF NOT EXISTS `neuron_users_emails` (
`ue_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `ue_email` varchar(255) NOT NULL,
  `ue_verified` tinyint(4) NOT NULL DEFAULT '0',
  `ue_token` varchar(32) NOT NULL,
  `ue_expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `set_time` char(10) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `neuron_users`
--
ALTER TABLE `neuron_users`
 ADD PRIMARY KEY (`u_id`), ADD UNIQUE KEY `u_email` (`u_email`), ADD KEY `u_username` (`u_username`);

--
-- Indexen voor tabel `neuron_users_deligated`
--
ALTER TABLE `neuron_users_deligated`
 ADD PRIMARY KEY (`ud_id`), ADD UNIQUE KEY `ud_type` (`ud_type`,`ud_unique_id`), ADD KEY `u_id` (`u_id`);

--
-- Indexen voor tabel `neuron_users_emails`
--
ALTER TABLE `neuron_users_emails`
 ADD PRIMARY KEY (`ue_id`), ADD KEY `u_id` (`u_id`);

--
-- Indexen voor tabel `sessions`
--
ALTER TABLE `sessions`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `neuron_users`
--
ALTER TABLE `neuron_users`
MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `neuron_users_deligated`
--
ALTER TABLE `neuron_users_deligated`
MODIFY `ud_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `neuron_users_emails`
--
ALTER TABLE `neuron_users_emails`
MODIFY `ue_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `neuron_users_deligated`
--
ALTER TABLE `neuron_users_deligated`
ADD CONSTRAINT `neuron_users_deligated_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `neuron_users` (`u_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;