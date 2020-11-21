-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2020 at 04:18 PM
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
  `index_post_content` bigint(20) UNSIGNED NOT NULL,
  `thread_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `post_content` text DEFAULT NULL,
  `post_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts_images`
--

CREATE TABLE `posts_images` (
  `thread_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `image_url` text NOT NULL,
  `image_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `threads_name`
--

CREATE TABLE `threads_name` (
  `thread_id` int(11) NOT NULL,
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
  ADD UNIQUE KEY `Unique_index_post` (`index_post_content`);
ALTER TABLE `posts_content` ADD FULLTEXT KEY `search_text` (`post_content`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `posts_content`
--
ALTER TABLE `posts_content`
  MODIFY `index_post_content` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
