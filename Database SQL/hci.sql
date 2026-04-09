-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 09, 2026 at 09:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hci`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `created_at`) VALUES
(2, 'admin', '$2y$10$Rg6kz1qNVZRO5J/SQqiZCOU4q7UVvL/WLsisdSjWgCNNtwXbsRR/G', '2026-04-09 14:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `contraceptive_methods`
--

CREATE TABLE `contraceptive_methods` (
  `method_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('hormonal','barrier','long_term','natural','emergency') NOT NULL,
  `effectiveness` decimal(4,1) NOT NULL COMMENT 'Percentage e.g. 99.7',
  `delivery` enum('daily_pill','weekly_patch','monthly_injection','long_term','barrier','natural') NOT NULL,
  `is_hormone_free` tinyint(1) DEFAULT 0,
  `cost_level` enum('low','medium','high') NOT NULL,
  `suitable_smoker` tinyint(1) DEFAULT 1,
  `suitable_breastfeeding` tinyint(1) DEFAULT 1,
  `contraindications` text DEFAULT NULL COMMENT 'Comma-separated: hypertension,migraines,etc.',
  `description` text NOT NULL,
  `side_effects` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contraceptive_methods`
--

INSERT INTO `contraceptive_methods` (`method_id`, `name`, `category`, `effectiveness`, `delivery`, `is_hormone_free`, `cost_level`, `suitable_smoker`, `suitable_breastfeeding`, `contraindications`, `description`, `side_effects`, `created_at`) VALUES
(1, 'Combined Oral Contraceptive Pill', 'hormonal', 91.0, 'daily_pill', 0, 'low', 0, 0, 'hypertension,migraines,blood_clots,liver_disease', 'A daily pill containing estrogen and progestin that prevents ovulation. Must be taken at the same time each day for best effectiveness.', 'Nausea, breast tenderness, headaches, mood changes, decreased libido', '2026-03-27 06:51:24'),
(2, 'Progestin-Only Pill (Mini-pill)', 'hormonal', 91.0, 'daily_pill', 0, 'low', 1, 1, 'liver_disease', 'A daily pill containing only progestin. Safe for smokers and breastfeeding women. Must be taken at the same time every day.', 'Irregular bleeding, headaches, nausea, breast tenderness', '2026-03-27 06:51:24'),
(3, 'Hormonal IUD (Mirena)', 'long_term', 99.8, 'long_term', 0, 'high', 1, 1, 'liver_disease', 'A small T-shaped device inserted into the uterus that releases progestin. Provides 5–8 years of protection and is fully reversible.', 'Irregular spotting especially in first months, cramping after insertion, possible absence of periods', '2026-03-27 06:51:24'),
(4, 'Copper IUD (Non-hormonal)', 'long_term', 99.2, 'long_term', 1, 'high', 1, 1, '', 'A hormone-free T-shaped copper device inserted into the uterus. Provides up to 10 years of protection. Can also be used as emergency contraception.', 'Heavier periods, more cramping especially in the first few months', '2026-03-27 06:51:24'),
(5, 'Injectable Contraceptive (DMPA)', 'hormonal', 94.0, 'monthly_injection', 0, 'low', 1, 1, 'liver_disease,blood_clots', 'A progestin injection given every 3 months by a healthcare provider. No daily action needed.', 'Irregular bleeding, weight gain, delayed return to fertility after stopping', '2026-03-27 06:51:24'),
(6, 'Male Condom', 'barrier', 85.0, 'barrier', 1, 'low', 1, 1, '', 'A barrier method that physically prevents sperm from reaching the egg. Also protects against STIs. Widely available without prescription.', 'Possible latex allergy, reduced sensation', '2026-03-27 06:51:24'),
(7, 'Female Condom', 'barrier', 79.0, 'barrier', 1, 'low', 1, 1, '', 'A pouch inserted into the vagina before sex. Provides STI protection. Can be inserted ahead of time.', 'Can be noisy, requires practice to insert correctly', '2026-03-27 06:51:24'),
(8, 'Contraceptive Implant (Implanon)', 'long_term', 99.9, 'long_term', 0, 'high', 1, 1, 'liver_disease', 'A small rod inserted under the skin of the upper arm that releases progestin. Provides up to 3 years of protection and is fully reversible.', 'Irregular bleeding, headaches, acne, mood changes', '2026-03-27 06:51:24'),
(9, 'Fertility Awareness Method', 'natural', 76.0, 'natural', 1, 'low', 1, 1, '', 'Tracking menstrual cycles, basal body temperature, and cervical mucus to identify fertile days and avoid unprotected sex during that window.', 'No physical side effects, but requires consistent daily tracking and discipline', '2026-03-27 06:51:24'),
(10, 'Diaphragm with Spermicide', 'barrier', 88.0, 'barrier', 1, 'medium', 1, 1, '', 'A dome-shaped silicone cup inserted into the vagina to cover the cervix, used with spermicide. Must be inserted before sex and left in for 6 hours after.', 'Possible bladder infections, spermicide irritation, requires fitting by a doctor', '2026-03-27 06:51:24'),
(11, 'Emergency Contraceptive Pill (ECP)', 'emergency', 85.0, 'daily_pill', 0, 'medium', 1, 0, 'liver_disease', 'A high-dose hormonal pill taken within 72 hours after unprotected sex to prevent pregnancy. Not intended for regular use.', 'Nausea, vomiting, headache, irregular bleeding, breast tenderness', '2026-03-27 06:51:24'),
(12, 'Bilateral Tubal Ligation', 'long_term', 99.5, 'long_term', 1, 'high', 1, 1, '', 'A permanent surgical procedure that blocks or removes the fallopian tubes. Considered permanent — reversal is difficult and not always successful.', 'Surgical risks, small chance of ectopic pregnancy if method fails', '2026-03-27 06:51:24');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reply_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`post_id`, `user_id`, `title`, `content`, `created_at`, `updated_at`, `reply_count`) VALUES
(1, 2, 'May nakapag-try na ba ng Hormonal IUD? Kamusta ang experience?', 'First time ko magpa-IUD. Sabi nila maganda raw ito for long-term pero natatakot ako sa insertion pain. Mga ilang araw bago mawala ang cramps? May side effects ba kayong na-experience like weight gain or mood swings?', '2026-04-09 13:31:25', '2026-04-09 14:57:37', 1);

-- --------------------------------------------------------

--
-- Table structure for table `forum_replies`
--

CREATE TABLE `forum_replies` (
  `reply_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_replies`
--

INSERT INTO `forum_replies` (`reply_id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 2, 'aba malay ko sayo haha', '2026-04-09 14:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `questionnaire_responses`
--

CREATE TABLE `questionnaire_responses` (
  `response_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sexually_active` enum('yes','no','prefer_not_to_say') NOT NULL,
  `wants_children` enum('yes','no','unsure') NOT NULL,
  `children_when` enum('within_1yr','1_to_3yrs','3yrs_plus','not_applicable') NOT NULL DEFAULT 'not_applicable',
  `health_conditions` set('none','hypertension','migraines','diabetes','blood_clots','liver_disease','depression') NOT NULL DEFAULT 'none',
  `is_smoker` enum('yes','no') NOT NULL,
  `is_breastfeeding` enum('yes','no') NOT NULL,
  `hormone_free_pref` enum('very_important','somewhat','not_important') NOT NULL,
  `delivery_pref` enum('daily_pill','weekly_patch','monthly_injection','long_term','barrier','natural') NOT NULL,
  `budget_pref` enum('low','medium','high') NOT NULL,
  `used_before` enum('yes','no') NOT NULL,
  `previous_method` varchar(100) DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questionnaire_responses`
--

INSERT INTO `questionnaire_responses` (`response_id`, `user_id`, `sexually_active`, `wants_children`, `children_when`, `health_conditions`, `is_smoker`, `is_breastfeeding`, `hormone_free_pref`, `delivery_pref`, `budget_pref`, `used_before`, `previous_method`, `submitted_at`, `updated_at`) VALUES
(2, 1, 'yes', 'yes', 'within_1yr', 'hypertension', 'no', 'no', 'somewhat', 'daily_pill', 'low', 'no', '', '2026-03-27 06:28:30', '2026-03-27 06:28:30'),
(3, 1, 'no', 'yes', '1_to_3yrs', 'none', 'no', 'no', 'somewhat', 'barrier', 'low', 'no', '', '2026-03-27 06:33:04', '2026-03-27 06:33:04'),
(4, 1, 'yes', 'yes', 'within_1yr', 'none', 'no', 'no', 'very_important', 'monthly_injection', 'low', 'no', '', '2026-03-27 06:38:25', '2026-03-27 06:38:25'),
(5, 1, 'yes', 'no', 'not_applicable', 'none', 'no', 'no', 'not_important', 'long_term', 'high', 'no', '', '2026-03-27 06:49:56', '2026-03-27 06:49:56'),
(6, 1, 'yes', 'yes', '1_to_3yrs', 'hypertension,migraines', 'no', 'no', 'very_important', 'long_term', 'low', 'no', '', '2026-03-27 07:29:53', '2026-03-27 07:29:53'),
(7, 2, 'yes', 'no', 'not_applicable', 'diabetes', 'no', 'no', 'somewhat', 'daily_pill', 'low', 'no', '', '2026-04-09 12:38:21', '2026-04-09 12:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `response_id` int(11) NOT NULL,
  `method_id` int(11) NOT NULL,
  `score` int(11) NOT NULL COMMENT 'Match score out of 100',
  `rank` tinyint(4) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recommendations`
--

INSERT INTO `recommendations` (`recommendation_id`, `user_id`, `response_id`, `method_id`, `score`, `rank`, `created_at`) VALUES
(11, 1, 5, 3, 90, 1, '2026-03-27 07:27:49'),
(12, 1, 5, 8, 90, 2, '2026-03-27 07:27:49'),
(13, 1, 5, 4, 70, 3, '2026-03-27 07:27:49'),
(14, 1, 5, 12, 70, 4, '2026-03-27 07:27:49'),
(15, 1, 5, 1, 40, 5, '2026-03-27 07:27:49'),
(41, 1, 6, 4, 60, 1, '2026-04-09 11:16:11'),
(42, 1, 6, 12, 60, 2, '2026-04-09 11:16:11'),
(43, 1, 6, 6, 50, 3, '2026-04-09 11:16:11'),
(44, 1, 6, 7, 50, 4, '2026-04-09 11:16:11'),
(45, 1, 6, 9, 45, 5, '2026-04-09 11:16:11'),
(256, 2, 7, 1, 70, 1, '2026-04-09 15:11:20'),
(257, 2, 7, 2, 70, 2, '2026-04-09 15:11:20'),
(258, 2, 7, 11, 45, 3, '2026-04-09 15:11:20'),
(259, 2, 7, 5, 40, 4, '2026-04-09 15:11:20'),
(260, 2, 7, 6, 40, 5, '2026-04-09 15:11:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, 'garde', '$2y$10$BSlpgEccSsniI.9EUONY7ugQ6WwG2dpXuGvTrM5lRKr/TEyfdoCDC', '2026-03-27 06:26:22', '2026-03-27 06:26:22'),
(2, 'juan', '$2y$10$5QkCxcm6hFWDQPepQ2mVGuxgt9ncJS4MyXEv0Joq4jnrVhjpUO0Se', '2026-04-09 12:12:20', '2026-04-09 12:12:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `contraceptive_methods`
--
ALTER TABLE `contraceptive_methods`
  ADD PRIMARY KEY (`method_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `forum_replies`
--
ALTER TABLE `forum_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `post_id` (`post_id`,`created_at`);

--
-- Indexes for table `questionnaire_responses`
--
ALTER TABLE `questionnaire_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_response` (`response_id`),
  ADD KEY `idx_method` (`method_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contraceptive_methods`
--
ALTER TABLE `contraceptive_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `forum_replies`
--
ALTER TABLE `forum_replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `questionnaire_responses`
--
ALTER TABLE `questionnaire_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forum_replies`
--
ALTER TABLE `forum_replies`
  ADD CONSTRAINT `forum_replies_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `questionnaire_responses`
--
ALTER TABLE `questionnaire_responses`
  ADD CONSTRAINT `questionnaire_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recommendations_ibfk_2` FOREIGN KEY (`response_id`) REFERENCES `questionnaire_responses` (`response_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recommendations_ibfk_3` FOREIGN KEY (`method_id`) REFERENCES `contraceptive_methods` (`method_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;