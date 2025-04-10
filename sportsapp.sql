-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 05:14 AM
-- Server version: 9.2.0
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sportsapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `batting_scores`
--

CREATE TABLE `batting_scores` (
  `id` int NOT NULL,
  `match_id` int NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `runs_scored` int DEFAULT '0',
  `balls_faced` int DEFAULT '0',
  `fours` int DEFAULT '0',
  `sixes` int DEFAULT '0',
  `is_out` tinyint(1) DEFAULT '0',
  `dismissal_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `batting_scores`
--

INSERT INTO `batting_scores` (`id`, `match_id`, `team_name`, `player_name`, `runs_scored`, `balls_faced`, `fours`, `sixes`, `is_out`, `dismissal_type`) VALUES
(67, 7, 'A', 'Rohit Sharma', 23, 8, 0, 0, 0, NULL),
(68, 7, 'A', 'Virat Kohli', 0, 0, 0, 0, 0, NULL),
(69, 7, 'A', 'Jasprit Bumrah', 0, 0, 0, 0, 0, NULL),
(70, 7, 'A', 'Ravindra Jadeja', 0, 0, 0, 0, 0, NULL),
(71, 7, 'A', 'KL Rahul', 0, 0, 0, 0, 0, NULL),
(72, 7, 'A', 'Mohammed Shami', 0, 0, 0, 0, 0, NULL),
(73, 7, 'A', 'Hardik Pandya', 0, 0, 0, 0, 0, NULL),
(74, 7, 'A', 'Kuldeep Yadav', 0, 0, 0, 0, 0, NULL),
(75, 7, 'A', 'Shubman Gill', 0, 0, 0, 0, 0, NULL),
(76, 7, 'A', 'Suryakumar Yadav', 0, 0, 0, 0, 0, NULL),
(77, 7, 'A', 'Yuzvendra Chahal', 0, 0, 0, 0, 0, NULL),
(78, 7, 'B', 'Babar Azam', 0, 0, 0, 0, 0, NULL),
(79, 7, 'B', 'Shaheen Afridi', 0, 0, 0, 0, 0, NULL),
(80, 7, 'B', 'Shadab Khan', 0, 0, 0, 0, 0, NULL),
(81, 7, 'B', 'Mohammad Rizwan', 0, 0, 0, 0, 0, NULL),
(82, 7, 'B', 'Haris Rauf', 0, 0, 0, 0, 0, NULL),
(83, 7, 'B', 'Imam-ul-Haq', 0, 0, 0, 0, 0, NULL),
(84, 7, 'B', 'Mohammad Nawaz', 0, 0, 0, 0, 0, NULL),
(85, 7, 'B', 'Fakhar Zaman', 0, 0, 0, 0, 0, NULL),
(86, 7, 'B', 'Hasan Ali', 0, 0, 0, 0, 0, NULL),
(87, 7, 'B', 'Iftikhar Ahmed', 0, 0, 0, 0, 0, NULL),
(88, 7, 'B', 'Usama Mir', 0, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bowling_scores`
--

CREATE TABLE `bowling_scores` (
  `id` int NOT NULL,
  `match_id` int NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `overs_bowled` float DEFAULT '0',
  `runs_conceded` int DEFAULT '0',
  `wickets_taken` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bowling_scores`
--

INSERT INTO `bowling_scores` (`id`, `match_id`, `team_name`, `player_name`, `overs_bowled`, `runs_conceded`, `wickets_taken`) VALUES
(1, 7, 'A', 'Jasprit Bumrah', 0, 0, 0),
(2, 7, 'A', 'Mohammed Shami', 0, 0, 0),
(3, 7, 'A', 'Kuldeep Yadav', 0, 0, 0),
(4, 7, 'A', 'Yuzvendra Chahal', 0, 0, 0),
(5, 7, 'B', 'Shaheen Afridi', 0, 0, 0),
(6, 7, 'B', 'Haris Rauf', 0, 0, 0),
(7, 7, 'B', 'Hasan Ali', 0, 0, 0),
(8, 7, 'B', 'Usama Mir', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `current_players`
--

CREATE TABLE `current_players` (
  `match_id` int NOT NULL,
  `team_batting` char(1) DEFAULT NULL,
  `striker` varchar(255) DEFAULT NULL,
  `non_striker` varchar(255) DEFAULT NULL,
  `current_bowler` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int NOT NULL,
  `team_a` varchar(100) NOT NULL,
  `team_b` varchar(100) NOT NULL,
  `match_date` datetime NOT NULL,
  `location` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('upcoming','live','completed') NOT NULL DEFAULT 'upcoming',
  `playing11_status` enum('not_set','set') DEFAULT 'not_set'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `team_a`, `team_b`, `match_date`, `location`, `created_at`, `status`, `playing11_status`) VALUES
(1, 'India', 'Pakistan', '2025-04-15 18:30:00', 'Wankhede Stadium', '2025-04-09 19:18:53', 'live', 'set'),
(3, 'Australia', 'Sri Lanka', '2025-04-10 03:14:00', 'Brisbane', '2025-04-09 21:45:01', 'completed', 'not_set'),
(4, 'Royal Challengers Bangalore', 'Dehli Capitals', '2025-04-10 03:28:00', 'Bangalore', '2025-04-09 21:57:20', 'live', 'set'),
(5, 'Chennai Super Kings', 'Gujarat Titans', '2025-04-11 19:30:00', 'Chennai', '2025-04-09 22:33:44', 'upcoming', 'not_set'),
(7, 'India', 'Australia', '2025-04-11 05:11:00', 'India', '2025-04-09 23:41:34', 'live', 'set');

-- --------------------------------------------------------

--
-- Table structure for table `playing_eleven`
--

CREATE TABLE `playing_eleven` (
  `id` int NOT NULL,
  `match_id` int NOT NULL,
  `team` enum('A','B') NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `type` enum('bowler','batsman','allrounder') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `playing_eleven`
--

INSERT INTO `playing_eleven` (`id`, `match_id`, `team`, `player_name`, `type`) VALUES
(45, 1, 'A', 'Rohit Sharma', 'batsman'),
(46, 1, 'A', 'Virat Kohli', 'batsman'),
(47, 1, 'A', 'Jasprit Bumrah', 'bowler'),
(48, 1, 'A', 'Ravindra Jadeja', 'allrounder'),
(49, 1, 'A', 'KL Rahul', 'batsman'),
(50, 1, 'A', 'Mohammed Shami', 'bowler'),
(51, 1, 'A', 'Hardik Pandya', 'allrounder'),
(52, 1, 'A', 'Kuldeep Yadav', 'bowler'),
(53, 1, 'A', 'Shubman Gill', 'batsman'),
(54, 1, 'A', 'Suryakumar Yadav', 'batsman'),
(55, 1, 'A', 'Yuzvendra Chahal', 'bowler'),
(56, 1, 'B', 'Babar Azam', 'batsman'),
(57, 1, 'B', 'Shaheen Afridi', 'bowler'),
(58, 1, 'B', 'Shadab Khan', 'allrounder'),
(59, 1, 'B', 'Mohammad Rizwan', 'batsman'),
(60, 1, 'B', 'Haris Rauf', 'bowler'),
(61, 1, 'B', 'Imam-ul-Haq', 'batsman'),
(62, 1, 'B', 'Mohammad Nawaz', 'allrounder'),
(63, 1, 'B', 'Fakhar Zaman', 'batsman'),
(64, 1, 'B', 'Hasan Ali', 'bowler'),
(65, 1, 'B', 'Iftikhar Ahmed', 'allrounder'),
(66, 1, 'B', 'Usama Mir', 'bowler'),
(67, 4, 'A', 'Viral Kohli', 'batsman'),
(68, 4, 'A', 'Phil Salt', 'batsman'),
(69, 4, 'A', 'Devdutt Paddikal', 'batsman'),
(70, 4, 'A', 'Rajat Patidar', 'batsman'),
(71, 4, 'A', 'Liam Livingstone', 'allrounder'),
(72, 4, 'A', 'Jitesh Sharme', 'allrounder'),
(73, 4, 'A', 'Tim David', 'allrounder'),
(74, 4, 'A', 'Krunal Pandya', 'bowler'),
(75, 4, 'A', 'Bhuvneshwar Kumar', 'bowler'),
(76, 4, 'A', 'Josh Hazlewood', 'bowler'),
(77, 4, 'A', 'Yash Dayal', 'bowler'),
(78, 4, 'B', 'Jake Fraser', 'batsman'),
(79, 4, 'B', 'KL Rahul', 'batsman'),
(80, 4, 'B', 'Abhishek Porel', 'batsman'),
(81, 4, 'B', 'Axar Patel', 'allrounder'),
(82, 4, 'B', 'Sameer Rizvi', 'batsman'),
(83, 4, 'B', 'Tristan Stubbs', 'allrounder'),
(84, 4, 'B', 'Ashutosh Sharma', 'batsman'),
(85, 4, 'B', 'Vipraj Nigam', 'batsman'),
(86, 4, 'B', 'Mitchell Starc', 'bowler'),
(87, 4, 'B', 'Kuldeep Yadav', 'bowler'),
(88, 4, 'B', 'Mohit Sharma', 'bowler'),
(331, 7, 'A', 'Rohit Sharma', 'batsman'),
(332, 7, 'A', 'Virat Kohli', 'batsman'),
(333, 7, 'A', 'Jasprit Bumrah', 'bowler'),
(334, 7, 'A', 'Ravindra Jadeja', 'allrounder'),
(335, 7, 'A', 'KL Rahul', 'batsman'),
(336, 7, 'A', 'Mohammed Shami', 'bowler'),
(337, 7, 'A', 'Hardik Pandya', 'allrounder'),
(338, 7, 'A', 'Kuldeep Yadav', 'bowler'),
(339, 7, 'A', 'Shubman Gill', 'batsman'),
(340, 7, 'A', 'Suryakumar Yadav', 'batsman'),
(341, 7, 'A', 'Yuzvendra Chahal', 'bowler'),
(342, 7, 'B', 'Babar Azam', 'batsman'),
(343, 7, 'B', 'Shaheen Afridi', 'bowler'),
(344, 7, 'B', 'Shadab Khan', 'allrounder'),
(345, 7, 'B', 'Mohammad Rizwan', 'batsman'),
(346, 7, 'B', 'Haris Rauf', 'bowler'),
(347, 7, 'B', 'Imam-ul-Haq', 'batsman'),
(348, 7, 'B', 'Mohammad Nawaz', 'allrounder'),
(349, 7, 'B', 'Fakhar Zaman', 'batsman'),
(350, 7, 'B', 'Hasan Ali', 'bowler'),
(351, 7, 'B', 'Iftikhar Ahmed', 'allrounder'),
(352, 7, 'B', 'Usama Mir', 'bowler');

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` int NOT NULL,
  `match_id` int NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `score` int DEFAULT '0',
  `wickets` int DEFAULT NULL,
  `overs` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`id`, `match_id`, `team_name`, `score`, `wickets`, `overs`) VALUES
(3, 7, 'India', 0, 0, 0),
(4, 7, 'Australia', 23, 0, 0),
(5, 4, 'Royal Challengers Bangalore', 0, 0, 0),
(6, 4, 'Dehli Capitals', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `toss_info`
--

CREATE TABLE `toss_info` (
  `match_id` int NOT NULL,
  `toss_winner` enum('A','B') NOT NULL,
  `batting_first` enum('A','B') NOT NULL,
  `bowling_first` enum('A','B') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `toss_info`
--

INSERT INTO `toss_info` (`match_id`, `toss_winner`, `batting_first`, `bowling_first`) VALUES
(7, 'A', 'A', 'B');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Ayush shukla', 'ayushshukla8920@gmail.com', '$2y$10$Ouh.Ihvpnb2tLE.TeadYPeJ1rmxGo35/1Ci.SPdOK5indB.kRQPzm', 'admin', '2025-04-08 20:09:59'),
(2, 'Aditya', 'adityapratap232@lpu.in', '$2y$10$UOlsCbD0mRBaoIoddXW8g.dJSw9YTeEyEfl1lQdHy3C9mHReQYwv6', 'customer', '2025-04-08 20:30:51'),
(4, 'Ayush shukla', 'iamayushshukla8920@gmail.com', '$2y$10$cgztGjmZ./5AebknjEMAru/C0CO1.HUw6rVME9zX.H6nYdrvidy5q', 'customer', '2025-04-09 08:46:43'),
(5, 'Chandrashekhar', 'csdev@gmail.com', '$2y$10$ju.OMW0pUbKN7qQ.WusMDus1frrJkH7zx32tb8IiApOXXwMHbPGta', 'customer', '2025-04-09 19:03:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `batting_scores`
--
ALTER TABLE `batting_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`);

--
-- Indexes for table `bowling_scores`
--
ALTER TABLE `bowling_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`);

--
-- Indexes for table `current_players`
--
ALTER TABLE `current_players`
  ADD PRIMARY KEY (`match_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `playing_eleven`
--
ALTER TABLE `playing_eleven`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`);

--
-- Indexes for table `toss_info`
--
ALTER TABLE `toss_info`
  ADD PRIMARY KEY (`match_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `batting_scores`
--
ALTER TABLE `batting_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `bowling_scores`
--
ALTER TABLE `bowling_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `playing_eleven`
--
ALTER TABLE `playing_eleven`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=353;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `batting_scores`
--
ALTER TABLE `batting_scores`
  ADD CONSTRAINT `batting_scores_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bowling_scores`
--
ALTER TABLE `bowling_scores`
  ADD CONSTRAINT `bowling_scores_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `playing_eleven`
--
ALTER TABLE `playing_eleven`
  ADD CONSTRAINT `playing_eleven_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `toss_info`
--
ALTER TABLE `toss_info`
  ADD CONSTRAINT `toss_info_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
