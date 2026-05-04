-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 05, 2026 at 01:03 AM
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
  `created_at` datetime DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `how_used` text DEFAULT NULL,
  `best_for` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contraceptive_methods`
--

INSERT INTO `contraceptive_methods` (`method_id`, `name`, `category`, `effectiveness`, `delivery`, `is_hormone_free`, `cost_level`, `suitable_smoker`, `suitable_breastfeeding`, `contraindications`, `description`, `side_effects`, `created_at`, `image_path`, `how_used`, `best_for`) VALUES
(1, 'Combined Oral Contraceptive Pill', 'hormonal', 91.0, 'daily_pill', 0, 'low', 0, 0, 'hypertension,migraines,blood_clots,liver_disease', 'A daily pill containing estrogen and progestin that prevents ovulation. Must be taken at the same time each day for best effectiveness.', 'Nausea, breast tenderness, headaches, mood changes, decreased libido', '2026-04-20 07:54:19', 'method_69ee137dee9d45.91497925.png', 'Take one pill every day at the same time. Start on the first day of your period or as directed by your doctor. Pills come in 21-day or 28-day packs.', 'Women who want a reliable daily hormonal method and do not smoke, are not over 35, and have no history of blood clots or migraines.'),
(2, 'Progestin-Only Pill (Mini-pill)', 'hormonal', 91.0, 'daily_pill', 0, 'low', 1, 1, 'liver_disease', 'A daily pill containing only progestin. Safe for smokers and breastfeeding women. Must be taken at the same time every day.', 'Irregular bleeding, headaches, nausea, breast tenderness', '2026-04-20 07:54:19', 'method_69edf1908b4f21.42911926.png', 'Take one pill every day at the same time — no breaks. It must be taken within the same 3-hour window daily to remain effective.', 'Breastfeeding women, smokers, and those who cannot use estrogen-based contraceptives.'),
(3, 'Hormonal IUD (Mirena)', 'long_term', 99.8, 'long_term', 0, 'high', 1, 1, 'liver_disease', 'A small T-shaped device inserted into the uterus that releases progestin. Provides 5–8 years of protection and is fully reversible.', 'Irregular spotting especially in first months, cramping after insertion, possible absence of periods', '2026-04-20 07:54:19', 'method_69ef534c4d0294.45938735.png', 'Inserted by a trained healthcare provider into the uterus during a short in-clinic procedure. No action needed after insertion. Can be removed anytime.', 'Women who want 5–8 years of low-maintenance protection with lighter or no periods over time.'),
(4, 'Copper IUD (Non-hormonal)', 'long_term', 99.2, 'long_term', 1, 'high', 1, 1, '', 'A hormone-free T-shaped copper device inserted into the uterus. Provides up to 10 years of protection. Can also be used as emergency contraception.', 'Heavier periods, more cramping especially in the first few months', '2026-04-20 07:54:19', 'method_69ee1f4d251638.28750785.png', 'Inserted by a healthcare provider into the uterus. Works immediately upon insertion. Can also be inserted within 5 days of unprotected sex as emergency contraception. Lasts up to 10 years.', 'Women who want long-term hormone-free protection or need emergency contraception without hormones.'),
(5, 'Injectable Contraceptive (DMPA)', 'hormonal', 94.0, 'monthly_injection', 0, 'low', 1, 1, 'liver_disease,blood_clots', 'A progestin injection given every 3 months by a healthcare provider. No daily action needed.', 'Irregular bleeding, weight gain, delayed return to fertility after stopping', '2026-04-20 07:54:19', 'method_69edf0cc553b69.73734967.png', 'Given as an injection by a healthcare provider every 3 months (12 weeks). No daily action needed. Must return to clinic on schedule for the next dose.', 'Women who prefer not to take a daily pill and want a low-cost hormonal option with infrequent clinic visits.'),
(6, 'Condom', 'barrier', 85.0, 'barrier', 1, 'low', 1, 1, '', 'A barrier method that physically prevents sperm from reaching the egg. Also protects against STIs. Widely available without prescription.', 'Possible latex allergy, reduced sensation', '2026-04-20 07:54:19', 'method_69f91b60220451.91297825.png', 'Place over an erect penis before any sexual contact, leaving space at the tip. After sex, hold the base and remove carefully. Use a new condom each time. Female condoms are inserted inside the vagina before sex.', 'Anyone wanting protection against both pregnancy and STIs. Ideal as a backup method or for those without a regular partner.'),
(7, 'Contraceptive Implant (Implanon)', 'long_term', 99.9, 'long_term', 0, 'high', 1, 1, 'liver_disease', 'A small rod inserted under the skin of the upper arm that releases progestin. Provides up to 3 years of protection and is fully reversible.', 'Irregular bleeding, headaches, acne, mood changes', '2026-04-20 07:54:19', 'method_69f916dfc50a22.59144372.png', 'A small rod is inserted under the skin of the upper arm by a trained healthcare provider under local anesthesia. It works immediately if inserted within the first 5 days of your period. Lasts up to 3 years and can be removed anytime.', 'Women who want the most effective long-term reversible method with minimal effort after insertion.'),
(8, 'Fertility Awareness Method', 'natural', 76.0, 'natural', 1, 'low', 1, 1, '', 'Tracking menstrual cycles, basal body temperature, and cervical mucus to identify fertile days and avoid unprotected sex during that window.', 'No physical side effects, but requires consistent daily tracking and discipline', '2026-04-20 07:54:19', 'method_69f91664b0bce0.07540255.png', 'Track your menstrual cycle, basal body temperature each morning, and cervical mucus changes daily. Avoid unprotected sex during your identified fertile window (usually days 8–19 of the cycle). Apps or charts can help with tracking.', 'Women with regular cycles who are comfortable with daily tracking and prefer a completely natural, hormone-free approach.'),
(9, 'Diaphragm with Spermicide', 'barrier', 88.0, 'barrier', 1, 'medium', 1, 1, '', 'A dome-shaped silicone cup inserted into the vagina to cover the cervix, used with spermicide. Must be inserted before sex and left in for 6 hours after.', 'Possible bladder infections, spermicide irritation, requires fitting by a doctor', '2026-04-20 07:54:19', 'method_69edfecb6d2919.22545895.png', 'Apply spermicide inside the diaphragm and insert it into the vagina to cover the cervix up to 2 hours before sex. Leave it in for at least 6 hours after sex. Must be fitted by a doctor first.', 'Women who prefer a hormone-free barrier method and are comfortable with insertion. Requires planning ahead before sex.'),
(10, 'Emergency Contraceptive Pill (ECP)', 'emergency', 85.0, 'daily_pill', 0, 'medium', 1, 0, 'liver_disease', 'A high-dose hormonal pill taken within 72 hours after unprotected sex to prevent pregnancy. Not intended for regular use.', 'Nausea, vomiting, headache, irregular bleeding, breast tenderness', '2026-04-20 07:54:19', 'method_69f914bfd63710.42137550.png', 'Take one pill as soon as possible after unprotected sex, within 72 hours (3 days). A second dose may be needed 12 hours after the first depending on the brand. The sooner it is taken, the more effective it is.', 'Women who have had unprotected sex or contraceptive failure (e.g. broken condom) and need to prevent pregnancy urgently. Not for regular use.'),
(11, 'Bilateral Tubal Ligation', 'long_term', 99.5, 'long_term', 1, 'high', 1, 1, '', 'A permanent surgical procedure that blocks or removes the fallopian tubes. Considered permanent — reversal is difficult and not always successful.', 'Surgical risks, small chance of ectopic pregnancy if method fails', '2026-04-20 07:54:19', 'method_69f913971b5ff9.92511295.png', 'A one-time surgical procedure done under general or local anesthesia. The fallopian tubes are cut, tied, or blocked to permanently prevent eggs from reaching the uterus. Usually done as day surgery.', 'Women who are certain they do not want any more children and want a permanent, highly effective solution with no ongoing maintenance.');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reply_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`post_id`, `user_id`, `content`, `created_at`, `updated_at`, `reply_count`) VALUES
(1, 2, 'First time ko magpa-IUD. Sabi nila maganda raw ito for long-term pero natatakot ako sa insertion pain. Mga ilang araw bago mawala ang cramps? May side effects ba kayong na-experience like weight gain or mood swings?', '2026-04-09 13:31:25', '2026-04-10 07:36:31', 4);

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
(1, 1, 2, 'aba malay ko sayo haha', '2026-04-09 14:57:37'),
(3, 1, 2, 'Gets ko yung fear! Usually yung cramps nawawala within a few days. Sa side effects, depende talaga sa katawan—may iba nakaka-experience ng mood swings, pero hindi lahat. Maganda lang talaga siya for long-term.', '2026-04-10 06:40:39'),
(4, 1, 2, 'test', '2026-04-10 07:36:31');

-- --------------------------------------------------------

--
-- Table structure for table `forum_reply_ratings`
--

CREATE TABLE `forum_reply_ratings` (
  `reply_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_reply_ratings`
--

INSERT INTO `forum_reply_ratings` (`reply_id`, `user_id`, `rating`) VALUES
(1, 1, 4),
(1, 2, 2),
(1, 3, 2),
(4, 2, 3);

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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `age_range` varchar(20) DEFAULT NULL,
  `cycle_regularity` varchar(20) DEFAULT NULL,
  `relationship_status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questionnaire_responses`
--

INSERT INTO `questionnaire_responses` (`response_id`, `user_id`, `sexually_active`, `wants_children`, `children_when`, `health_conditions`, `is_smoker`, `is_breastfeeding`, `hormone_free_pref`, `delivery_pref`, `budget_pref`, `used_before`, `previous_method`, `submitted_at`, `updated_at`, `age_range`, `cycle_regularity`, `relationship_status`) VALUES
(2, 1, 'yes', 'yes', 'within_1yr', 'hypertension', 'no', 'no', 'somewhat', 'daily_pill', 'low', 'no', '', '2026-03-27 06:28:30', '2026-03-27 06:28:30', NULL, NULL, NULL),
(3, 1, 'no', 'yes', '1_to_3yrs', 'none', 'no', 'no', 'somewhat', 'barrier', 'low', 'no', '', '2026-03-27 06:33:04', '2026-03-27 06:33:04', NULL, NULL, NULL),
(4, 1, 'yes', 'yes', 'within_1yr', 'none', 'no', 'no', 'very_important', 'monthly_injection', 'low', 'no', '', '2026-03-27 06:38:25', '2026-03-27 06:38:25', NULL, NULL, NULL),
(5, 1, 'yes', 'no', 'not_applicable', 'none', 'no', 'no', 'not_important', 'long_term', 'high', 'no', '', '2026-03-27 06:49:56', '2026-03-27 06:49:56', NULL, NULL, NULL),
(6, 1, 'yes', 'yes', '1_to_3yrs', 'hypertension,migraines', 'no', 'no', 'very_important', 'long_term', 'low', 'no', '', '2026-03-27 07:29:53', '2026-03-27 07:29:53', NULL, NULL, NULL),
(7, 2, 'yes', 'no', 'not_applicable', 'diabetes', 'no', 'no', 'somewhat', 'daily_pill', 'low', 'no', '', '2026-04-09 12:38:21', '2026-04-09 12:38:21', NULL, NULL, NULL),
(8, 2, 'yes', 'yes', 'within_1yr', 'migraines', 'no', 'no', 'not_important', 'natural', 'low', 'no', '', '2026-04-10 10:28:58', '2026-04-10 10:28:58', NULL, NULL, NULL),
(9, 2, 'yes', 'yes', 'within_1yr', 'migraines', 'no', 'no', 'very_important', 'long_term', 'low', 'no', '', '2026-04-10 10:33:43', '2026-04-10 10:33:43', NULL, NULL, NULL),
(10, 2, 'yes', 'yes', '1_to_3yrs', 'depression', 'yes', 'no', 'somewhat', 'monthly_injection', 'low', 'no', '', '2026-04-26 17:54:59', '2026-04-26 17:54:59', 'under_18', 'regular', 'single'),
(11, 2, 'yes', 'no', 'not_applicable', 'hypertension', 'yes', 'yes', 'very_important', 'long_term', 'medium', 'no', '', '2026-04-26 20:58:59', '2026-04-26 20:58:59', '18_to_24', 'regular', 'committed'),
(12, 2, 'yes', 'no', 'not_applicable', 'liver_disease', 'no', 'no', 'not_important', 'monthly_injection', 'high', 'no', '', '2026-04-26 21:15:53', '2026-04-26 21:15:53', 'under_18', 'irregular', 'prefer_not_to_say'),
(13, 2, 'no', 'no', 'not_applicable', 'none', 'no', 'no', 'very_important', 'long_term', 'medium', 'no', '', '2026-04-28 06:50:31', '2026-04-28 06:50:31', 'under_18', 'regular', 'committed'),
(14, 2, 'prefer_not_to_say', 'unsure', 'not_applicable', 'none', 'no', 'no', 'somewhat', 'natural', 'medium', 'no', '', '2026-04-28 10:31:05', '2026-04-28 10:31:05', '18_to_24', 'regular', 'committed'),
(15, 2, 'yes', 'yes', '3yrs_plus', 'none', 'no', 'no', 'very_important', 'daily_pill', 'medium', 'yes', 'pills', '2026-04-28 10:54:58', '2026-04-28 10:54:58', '25_to_34', 'regular', 'committed'),
(16, 2, 'prefer_not_to_say', 'yes', '3yrs_plus', 'none', 'no', 'no', 'very_important', 'natural', 'medium', 'no', '', '2026-04-28 11:14:02', '2026-04-28 11:14:02', '18_to_24', 'regular', 'prefer_not_to_say'),
(17, 2, 'no', 'yes', '3yrs_plus', 'none', 'no', 'no', 'very_important', 'natural', 'medium', 'no', '', '2026-04-28 11:26:48', '2026-04-28 11:26:48', '18_to_24', 'regular', 'committed'),
(18, 2, 'yes', 'unsure', 'not_applicable', 'none', 'no', 'no', 'very_important', 'barrier', 'medium', 'yes', 'condoms', '2026-04-28 11:39:22', '2026-04-28 11:39:22', '18_to_24', 'regular', 'committed'),
(19, 2, 'yes', 'yes', '3yrs_plus', 'diabetes', 'no', 'no', 'very_important', 'natural', 'high', 'yes', 'condoms', '2026-04-28 13:31:07', '2026-04-28 13:31:07', '18_to_24', 'regular', 'committed'),
(20, 2, 'yes', 'yes', 'within_1yr', 'hypertension,blood_clots', 'no', 'no', 'very_important', 'natural', 'medium', 'no', '', '2026-04-28 14:12:16', '2026-04-28 14:12:16', '25_to_34', 'regular', 'committed');

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
(646, 2, 9, 6, 65, 1, '2026-04-26 17:41:02'),
(647, 2, 9, 4, 60, 2, '2026-04-26 17:41:02'),
(648, 2, 9, 8, 60, 3, '2026-04-26 17:41:02'),
(649, 2, 9, 11, 60, 4, '2026-04-26 17:41:02'),
(650, 2, 9, 9, 45, 5, '2026-04-26 17:41:02'),
(856, 2, 10, 5, 82, 1, '2026-04-26 20:57:38'),
(857, 2, 10, 2, 52, 2, '2026-04-26 20:57:38'),
(858, 2, 10, 6, 40, 3, '2026-04-26 20:57:38'),
(859, 2, 10, 8, 35, 4, '2026-04-26 20:57:38'),
(860, 2, 10, 3, 20, 5, '2026-04-26 20:57:38'),
(906, 2, 11, 4, 75, 1, '2026-04-26 21:11:54'),
(907, 2, 11, 11, 75, 2, '2026-04-26 21:11:54'),
(908, 2, 11, 3, 50, 3, '2026-04-26 21:11:54'),
(909, 2, 11, 7, 50, 4, '2026-04-26 21:11:54'),
(910, 2, 11, 9, 50, 5, '2026-04-26 21:11:54'),
(1241, 2, 12, 4, 40, 1, '2026-04-28 06:42:03'),
(1242, 2, 12, 11, 40, 2, '2026-04-28 06:42:03'),
(1243, 2, 12, 6, 20, 3, '2026-04-28 06:42:03'),
(1244, 2, 12, 9, 20, 4, '2026-04-28 06:42:03'),
(1245, 2, 12, 8, 15, 5, '2026-04-28 06:42:03'),
(1281, 2, 13, 4, 75, 1, '2026-04-28 10:21:39'),
(1282, 2, 13, 11, 75, 2, '2026-04-28 10:21:39'),
(1283, 2, 13, 3, 50, 3, '2026-04-28 10:21:39'),
(1284, 2, 13, 7, 50, 4, '2026-04-28 10:21:39'),
(1285, 2, 13, 9, 50, 5, '2026-04-28 10:21:39'),
(1291, 2, 14, 8, 67, 1, '2026-04-28 10:33:57'),
(1292, 2, 14, 9, 50, 2, '2026-04-28 10:33:57'),
(1293, 2, 14, 10, 45, 3, '2026-04-28 10:33:57'),
(1294, 2, 14, 1, 42, 4, '2026-04-28 10:33:57'),
(1295, 2, 14, 2, 42, 5, '2026-04-28 10:33:57'),
(1296, 2, 15, 10, 50, 1, '2026-04-28 10:55:02'),
(1297, 2, 15, 4, 45, 2, '2026-04-28 10:55:02'),
(1298, 2, 15, 9, 45, 3, '2026-04-28 10:55:02'),
(1299, 2, 15, 11, 45, 4, '2026-04-28 10:55:02'),
(1300, 2, 15, 1, 42, 5, '2026-04-28 10:55:02'),
(1301, 2, 16, 8, 67, 1, '2026-04-28 11:14:11'),
(1302, 2, 16, 9, 50, 2, '2026-04-28 11:14:11'),
(1303, 2, 16, 4, 45, 3, '2026-04-28 11:14:11'),
(1304, 2, 16, 11, 45, 4, '2026-04-28 11:14:11'),
(1305, 2, 16, 6, 42, 5, '2026-04-28 11:14:11'),
(1306, 2, 17, 8, 67, 1, '2026-04-28 11:27:00'),
(1307, 2, 17, 9, 50, 2, '2026-04-28 11:27:00'),
(1308, 2, 17, 4, 45, 3, '2026-04-28 11:27:00'),
(1309, 2, 17, 11, 45, 4, '2026-04-28 11:27:00'),
(1310, 2, 17, 6, 42, 5, '2026-04-28 11:27:00'),
(1326, 2, 18, 9, 85, 1, '2026-04-28 13:00:39'),
(1327, 2, 18, 6, 77, 2, '2026-04-28 13:00:39'),
(1328, 2, 18, 8, 47, 3, '2026-04-28 13:00:39'),
(1329, 2, 18, 4, 30, 4, '2026-04-28 13:00:39'),
(1330, 2, 18, 10, 30, 5, '2026-04-28 13:00:39'),
(1331, 2, 19, 8, 70, 1, '2026-04-28 13:31:13'),
(1332, 2, 19, 4, 65, 2, '2026-04-28 13:31:13'),
(1333, 2, 19, 11, 65, 3, '2026-04-28 13:31:13'),
(1334, 2, 19, 3, 40, 4, '2026-04-28 13:31:13'),
(1335, 2, 19, 6, 40, 5, '2026-04-28 13:31:13'),
(1406, 2, 20, 8, 82, 1, '2026-05-05 07:00:48'),
(1407, 2, 20, 9, 65, 2, '2026-05-05 07:00:48'),
(1408, 2, 20, 6, 57, 3, '2026-05-05 07:00:48'),
(1409, 2, 20, 4, 30, 4, '2026-05-05 07:00:48'),
(1410, 2, 20, 11, 30, 5, '2026-05-05 07:00:48');

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
(2, 'juan', '$2y$10$5QkCxcm6hFWDQPepQ2mVGuxgt9ncJS4MyXEv0Joq4jnrVhjpUO0Se', '2026-04-09 12:12:20', '2026-04-09 12:12:20'),
(3, 'hi', '$2y$10$p1FVMLMj5Cr2bN7f94hPfuVvzLXUz88xfHnW.5//2lE6m2UuL7ndu', '2026-04-10 05:32:35', '2026-04-10 05:32:35'),
(4, 'test', '$2y$10$gSG1gWEUYpb6jMyYv7k9Xe9MLKpvZsD0ytaq7cP12.DRlr.poS0ma', '2026-04-10 07:44:38', '2026-04-10 07:44:38'),
(5, 'kk', '$2y$10$YOq4laNHxKbzY.vc.II2WONUcETQR3ZlSB2vmXdDjfnPnf/vi8wTC', '2026-04-14 21:15:17', '2026-04-14 21:15:17');

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
-- Indexes for table `forum_reply_ratings`
--
ALTER TABLE `forum_reply_ratings`
  ADD PRIMARY KEY (`reply_id`,`user_id`);

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
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `questionnaire_responses`
--
ALTER TABLE `questionnaire_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1411;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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