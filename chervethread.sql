-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2020 at 01:11 AM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chervethread`
--

-- --------------------------------------------------------

--
-- Table structure for table `dictonary`
--

CREATE TABLE `dictonary` (
  `word` varchar(255) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts_content`
--

CREATE TABLE `posts_content` (
  `index_post` mediumint(8) UNSIGNED NOT NULL,
  `post_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts_data`
--

CREATE TABLE `posts_data` (
  `index_post` mediumint(8) UNSIGNED NOT NULL,
  `post_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts_ids`
--

CREATE TABLE `posts_ids` (
  `index_post` mediumint(8) UNSIGNED NOT NULL,
  `thread_id` mediumint(8) UNSIGNED DEFAULT NULL,
  `post_id` mediumint(8) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts_images`
--

CREATE TABLE `posts_images` (
  `index_post` mediumint(8) UNSIGNED NOT NULL,
  `image_url` text NOT NULL,
  `image_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `threads_name`
--

CREATE TABLE `threads_name` (
  `thread_id` mediumint(8) UNSIGNED NOT NULL,
  `thread_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dictonary`
--
ALTER TABLE `dictonary`
  ADD PRIMARY KEY (`word`),
  ADD UNIQUE KEY `word` (`word`);

--
-- Indexes for table `posts_content`
--
ALTER TABLE `posts_content`
  ADD PRIMARY KEY (`index_post`);

--
-- Indexes for table `posts_data`
--
ALTER TABLE `posts_data`
  ADD PRIMARY KEY (`index_post`);

--
-- Indexes for table `posts_ids`
--
ALTER TABLE `posts_ids`
  ADD PRIMARY KEY (`index_post`);

--
-- Indexes for table `threads_name`
--
ALTER TABLE `threads_name`
  ADD PRIMARY KEY (`thread_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
