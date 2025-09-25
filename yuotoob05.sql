-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2025 at 10:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yuotoob05`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `cid` varchar(14) NOT NULL COMMENT 'comment id',
  `post_date` datetime NOT NULL DEFAULT current_timestamp(),
  `vidon` varchar(14) NOT NULL COMMENT 'Vid it was commented on',
  `vid` varchar(14) DEFAULT NULL COMMENT 'Vid attached to it',
  `body` varchar(100) NOT NULL COMMENT 'body of comment',
  `uid` varchar(20) NOT NULL COMMENT 'user who posted it',
  `is_reply` int(11) NOT NULL,
  `reply_to` varchar(14) NOT NULL,
  `master_comment` varchar(14) NOT NULL,
  `removed` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `fid` varchar(12) NOT NULL COMMENT 'favorite id',
  `added` timestamp NOT NULL DEFAULT current_timestamp(),
  `uid` varchar(12) NOT NULL,
  `vid` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='lol the whole table is ids';

-- --------------------------------------------------------

--
-- Table structure for table `invite_keys`
--

CREATE TABLE `invite_keys` (
  `id` int(11) NOT NULL,
  `invite_key` varchar(50) NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `sender` varchar(12) NOT NULL COMMENT 'uid of whom is sending the message',
  `receiver` varchar(12) NOT NULL COMMENT 'uid of the recipient',
  `subject` text NOT NULL COMMENT '(up to 75 characters) the title of the message, encrypted',
  `attached` varchar(15) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `body` text NOT NULL COMMENT '(up to 50,000 characters) the text of the message encrypted',
  `pmid` varchar(12) NOT NULL COMMENT 'id of the private message',
  `isRead` int(11) NOT NULL COMMENT 'If receiver saw it, mark 1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='private messages';

-- --------------------------------------------------------

--
-- Table structure for table `picks`
--

CREATE TABLE `picks` (
  `video` varchar(12) NOT NULL,
  `featured` datetime NOT NULL DEFAULT current_timestamp(),
  `special` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions_and_answers`
--

CREATE TABLE `questions_and_answers` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions_and_answers`
--

INSERT INTO `questions_and_answers` (`id`, `question`, `answer`) VALUES
(1, 'Can i promote my revival to YuoToob?', 'No, it\'s not allowed to promote your revival on the website just to make everyone migrate to your revival. Please make normal content. '),
(3, 'Don\'t post cringe content.', 'For the love of God, do not post cringy ass videos. I swear in the everliving fuck i\'ll delete them if i find any.');

-- --------------------------------------------------------

--
-- Table structure for table `rejections`
--

CREATE TABLE `rejections` (
  `uid` varchar(20) NOT NULL COMMENT 'id of video poster',
  `vid` varchar(20) NOT NULL COMMENT 'video id',
  `cdn` int(11) NOT NULL,
  `uploaded` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  `tags` varchar(900) NOT NULL,
  `ch1` int(11) NOT NULL DEFAULT 5,
  `ch2` int(11) DEFAULT NULL,
  `ch3` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `file` text NOT NULL,
  `time` int(11) NOT NULL,
  `converted` int(11) NOT NULL,
  `privacy` int(11) NOT NULL DEFAULT 1,
  `priva_group` int(11) DEFAULT NULL,
  `recorddate` datetime DEFAULT NULL,
  `address` text DEFAULT NULL,
  `addrcountry` text DEFAULT NULL,
  `comms_allow` int(11) NOT NULL DEFAULT 1,
  `allow_votes` int(11) NOT NULL DEFAULT 1,
  `views` int(11) NOT NULL,
  `comm_count` int(11) NOT NULL,
  `fav_count` int(11) NOT NULL,
  `ratings` int(11) NOT NULL,
  `age_restrict` int(11) NOT NULL,
  `reason` int(11) NOT NULL DEFAULT 1,
  `copyright_holder` varchar(65) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket` int(11) NOT NULL,
  `sender` text NOT NULL,
  `subject` int(11) NOT NULL,
  `message` text NOT NULL,
  `submitted` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` varchar(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` longtext NOT NULL,
  `old_pass` longtext DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `confirm_id` text NOT NULL,
  `confirm_expire` datetime DEFAULT NULL,
  `joined` datetime NOT NULL DEFAULT current_timestamp(),
  `em_confirmation` varchar(1000) NOT NULL DEFAULT 'false',
  `emailprefs_vdocomments` int(11) NOT NULL DEFAULT 1,
  `emailprefs_wklytape` int(11) NOT NULL,
  `emailprefs_privatem` int(11) NOT NULL DEFAULT 1,
  `lastlogin` datetime NOT NULL DEFAULT current_timestamp(),
  `last_act` datetime NOT NULL DEFAULT current_timestamp(),
  `failed_login` datetime DEFAULT NULL,
  `termination` int(11) NOT NULL COMMENT 'if equals 1 then theyre terminated',
  `birthday` date DEFAULT NULL,
  `name` varchar(500) NOT NULL,
  `relationship` int(11) NOT NULL,
  `gender` int(11) NOT NULL,
  `about` varchar(2500) NOT NULL,
  `website` varchar(255) NOT NULL,
  `hometown` varchar(500) NOT NULL,
  `city` varchar(500) NOT NULL,
  `country` varchar(500) NOT NULL,
  `occupations` varchar(500) NOT NULL,
  `companies` varchar(500) NOT NULL,
  `schools` varchar(500) NOT NULL,
  `hobbies` varchar(500) NOT NULL,
  `fav_media` varchar(500) NOT NULL,
  `playlists` int(11) NOT NULL,
  `subscriptions` int(11) NOT NULL,
  `subscribers` int(11) NOT NULL,
  `profile_views` int(11) NOT NULL,
  `fav_count` int(11) NOT NULL,
  `pub_vids` int(11) NOT NULL,
  `priv_vids` int(11) NOT NULL,
  `friends_count` int(11) NOT NULL,
  `vids_watched` int(11) NOT NULL,
  `music` varchar(500) NOT NULL,
  `books` varchar(500) NOT NULL,
  `staff` int(11) NOT NULL,
  `sysadmin` int(11) NOT NULL,
  `ip` varchar(500) DEFAULT NULL,
  `priv_id` varchar(35) DEFAULT NULL COMMENT 'non-public version of the user id used for things like email verif',
  `blazing` int(11) NOT NULL COMMENT '1 sets the cooldown limit to just 1',
  `retelimit` varchar(255) DEFAULT NULL,
  `profileColor` varchar(255) DEFAULT 'classic'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `uid` varchar(20) NOT NULL COMMENT 'id of video poster',
  `vid` varchar(20) NOT NULL COMMENT 'video id',
  `cdn` int(11) NOT NULL,
  `uploaded` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  `tags` varchar(900) NOT NULL,
  `ch1` int(11) NOT NULL DEFAULT 5,
  `ch2` int(11) DEFAULT NULL,
  `ch3` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `file` text NOT NULL,
  `time` int(11) NOT NULL DEFAULT 0,
  `converted` int(11) NOT NULL DEFAULT 0,
  `privacy` int(11) NOT NULL DEFAULT 1,
  `priva_group` int(11) DEFAULT NULL,
  `recorddate` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `addrcountry` text DEFAULT NULL,
  `comms_allow` int(11) NOT NULL DEFAULT 1,
  `allow_votes` int(11) NOT NULL DEFAULT 1,
  `age_restrict` int(11) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `comm_count` int(11) NOT NULL DEFAULT 0,
  `fav_count` int(11) NOT NULL DEFAULT 0,
  `ratings` int(11) NOT NULL DEFAULT 0,
  `rejected` int(11) NOT NULL DEFAULT 0,
  `reason` int(11) NOT NULL DEFAULT 0,
  `copyright_holder` varchar(65) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `views`
--

CREATE TABLE `views` (
  `view_id` varchar(35) NOT NULL,
  `viewed` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'when it got viewed',
  `location` varchar(5) DEFAULT 'US',
  `ip` varchar(45) NOT NULL DEFAULT 'generic',
  `vid` varchar(12) NOT NULL COMMENT 'the video that was viewed',
  `uid` varchar(12) DEFAULT NULL COMMENT 'user who viewed the video',
  `referer` text NOT NULL COMMENT 'HTTP referer',
  `sid` text NOT NULL COMMENT 'session id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `yuotoob_web`
--

CREATE TABLE `yuotoob_web` (
  `version` varchar(59) NOT NULL,
  `logo_sm` varchar(100) NOT NULL,
  `logo` varchar(100) NOT NULL DEFAULT 'logo_sm.gif',
  `slogan` varchar(52) NOT NULL DEFAULT 'Upload, tag and share your videos worldwide!',
  `notice` varchar(99) NOT NULL,
  `maintenance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Website configuration';

--
-- Dumping data for table `yuotoob_web`
--

INSERT INTO `yuotoob_web` (`version`, `logo_sm`, `logo`, `slogan`, `notice`, `maintenance`) VALUES
('yuotoob', 'logo_sm.gif', 'logo.gif', 'Your Digital Video Repository', '', 0),
('yuotoob_april_fools', 'logo_flashing_sm.gif', 'logo_flashing.gif', 'ApRiL FoOlS!!!1', 'HaPpY ApRiL FoOlS To yUoToObIaNs!!!!!1', 0),
('yuotoob_christmas', 'logo_christ_sm.gif', 'logo_christ.gif', 'Merry Christmas!', 'Merry Christmas!!!', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`fid`);

--
-- Indexes for table `invite_keys`
--
ALTER TABLE `invite_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invite_key` (`invite_key`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`pmid`);

--
-- Indexes for table `picks`
--
ALTER TABLE `picks`
  ADD PRIMARY KEY (`video`);

--
-- Indexes for table `questions_and_answers`
--
ALTER TABLE `questions_and_answers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rejections`
--
ALTER TABLE `rejections`
  ADD PRIMARY KEY (`vid`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `uid` (`uid`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`vid`);

--
-- Indexes for table `views`
--
ALTER TABLE `views`
  ADD PRIMARY KEY (`view_id`);

--
-- Indexes for table `yuotoob_web`
--
ALTER TABLE `yuotoob_web`
  ADD PRIMARY KEY (`version`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `invite_keys`
--
ALTER TABLE `invite_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions_and_answers`
--
ALTER TABLE `questions_and_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
