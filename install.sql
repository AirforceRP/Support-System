-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 14, 2023 at 04:40 PM
-- Server version: 10.5.20-MariaDB-cll-lve
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `airforcerp_support`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('Member','Admin','Guest','Operator') NOT NULL DEFAULT 'Member',
  `secret` varchar(255) NOT NULL DEFAULT '',
  `last_seen` datetime NOT NULL,
  `status` enum('Occupied','Waiting','Idle','Away') NOT NULL DEFAULT 'Idle',
  `photo_url` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `registered` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `title`) VALUES
(1, 'General'),
(2, 'Technical'),
(3, 'Bug Report'),
(4, 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `account_sender_id` int(11) NOT NULL,
  `account_receiver_id` int(11) NOT NULL,
  `submit_date` datetime NOT NULL,
  `status` enum('Open','Archived') NOT NULL DEFAULT 'Open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `account_sender_id`, `account_receiver_id`, `submit_date`, `status`) VALUES

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `msg` text NOT NULL,
  `attachments` text NOT NULL,
  `submit_date` datetime NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `latest_message` datetime NOT NULL DEFAULT current_timestamp(),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--


--
-- Table structure for table `presets`
--

CREATE TABLE `presets` (
  `id` int(11) NOT NULL,
  `msg` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `presets`
--

INSERT INTO `presets` (`id`, `msg`) VALUES
(1, 'Thank you for choosing our website! If there is anything else you need, dont hesitate to contact us!'),
(2, 'Hi {name}, how may I help you today?');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `msg` text NOT NULL,
  `full_name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `ticket_status` enum('open','closed','resolved') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'low',
  `category_id` int(1) NOT NULL DEFAULT 1,
  `private` tinyint(1) NOT NULL DEFAULT 1,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `approved` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tickets`
-
-- --------------------------------------------------------

--
-- Table structure for table `tickets_comments`
--

CREATE TABLE `tickets_comments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `msg` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `account_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets_uploads`
--

CREATE TABLE `tickets_uploads` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `filepath` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `word_filters`
--

CREATE TABLE `word_filters` (
  `id` int(11) NOT NULL,
  `word` varchar(255) NOT NULL,
  `replacement` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `presets`
--
ALTER TABLE `presets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets_comments`
--
ALTER TABLE `tickets_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets_uploads`
--
ALTER TABLE `tickets_uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `word_filters`
--
ALTER TABLE `word_filters`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `presets`
--
ALTER TABLE `presets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tickets_comments`
--
ALTER TABLE `tickets_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets_uploads`
--
ALTER TABLE `tickets_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `word_filters`
--
ALTER TABLE `word_filters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
