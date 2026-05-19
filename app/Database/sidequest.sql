-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2026 at 09:08 PM
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
-- Database: `sidequest`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('sidequest-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1777686664),
('sidequest-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1777686664;', 1777686664),
('sidequest-cache-b15df1c3b85ad4a0f08facff7664f55b', 'i:2;', 1777688164),
('sidequest-cache-b15df1c3b85ad4a0f08facff7664f55b:timer', 'i:1777688164;', 1777688164),
('sidequest-cache-b67db337f3c74e40672e750ad226ffd9', 'i:1;', 1777688128),
('sidequest-cache-b67db337f3c74e40672e750ad226ffd9:timer', 'i:1777688128;', 1777688128),
('sidequest-cache-recommendations:1', 'O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:15:\"App\\Models\\User\":35:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:17:{s:2:\"id\";i:2;s:8:\"username\";s:5:\"gapol\";s:10:\"first_name\";s:3:\"Gab\";s:9:\"last_name\";s:3:\"pol\";s:13:\"date_of_birth\";s:10:\"2001-12-21\";s:3:\"sex\";s:4:\"male\";s:5:\"email\";s:23:\"gab.pol@foundationu.com\";s:17:\"email_verified_at\";N;s:8:\"password\";s:60:\"$2y$12$z70TEYaFYVahqwaEeDTtK.Z37Nf1Pt.YvAhKiyGkpQXLyzvFj4B8y\";s:17:\"two_factor_secret\";N;s:25:\"two_factor_recovery_codes\";N;s:23:\"two_factor_confirmed_at\";N;s:20:\"profile_picture_path\";s:21:\"avatars/2/profile.jpg\";s:3:\"bio\";N;s:14:\"remember_token\";N;s:10:\"created_at\";s:19:\"2026-05-02 02:13:00\";s:10:\"updated_at\";s:19:\"2026-05-02 02:13:56\";}s:11:\"\0*\0original\";a:17:{s:2:\"id\";i:2;s:8:\"username\";s:5:\"gapol\";s:10:\"first_name\";s:3:\"Gab\";s:9:\"last_name\";s:3:\"pol\";s:13:\"date_of_birth\";s:10:\"2001-12-21\";s:3:\"sex\";s:4:\"male\";s:5:\"email\";s:23:\"gab.pol@foundationu.com\";s:17:\"email_verified_at\";N;s:8:\"password\";s:60:\"$2y$12$z70TEYaFYVahqwaEeDTtK.Z37Nf1Pt.YvAhKiyGkpQXLyzvFj4B8y\";s:17:\"two_factor_secret\";N;s:25:\"two_factor_recovery_codes\";N;s:23:\"two_factor_confirmed_at\";N;s:20:\"profile_picture_path\";s:21:\"avatars/2/profile.jpg\";s:3:\"bio\";N;s:14:\"remember_token\";N;s:10:\"created_at\";s:19:\"2026-05-02 02:13:00\";s:10:\"updated_at\";s:19:\"2026-05-02 02:13:56\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:17:\"email_verified_at\";s:8:\"datetime\";s:8:\"password\";s:6:\"hashed\";s:23:\"two_factor_confirmed_at\";s:8:\"datetime\";s:13:\"date_of_birth\";s:4:\"date\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:3:{i:0;s:4:\"name\";i:1;s:9:\"full_name\";i:2;s:19:\"profile_picture_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:4:{i:0;s:8:\"password\";i:1;s:17:\"two_factor_secret\";i:2;s:25:\"two_factor_recovery_codes\";i:3;s:14:\"remember_token\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:9:{i:0;s:5:\"email\";i:1;s:8:\"password\";i:2;s:8:\"username\";i:3;s:10:\"first_name\";i:4;s:9:\"last_name\";i:5;s:13:\"date_of_birth\";i:6;s:3:\"sex\";i:7;s:3:\"bio\";i:8;s:20:\"profile_picture_path\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:19:\"\0*\0authPasswordName\";s:8:\"password\";s:20:\"\0*\0rememberTokenName\";s:14:\"remember_token\";}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1777688372),
('sidequest-cache-recommendations:2', 'O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:15:\"App\\Models\\User\":35:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:17:{s:2:\"id\";i:1;s:8:\"username\";s:14:\"rafael_udtohan\";s:10:\"first_name\";s:6:\"rafael\";s:9:\"last_name\";s:7:\"udtohan\";s:13:\"date_of_birth\";s:10:\"2001-03-21\";s:3:\"sex\";s:4:\"male\";s:5:\"email\";s:30:\"rafael.udtohan@foundationu.com\";s:17:\"email_verified_at\";N;s:8:\"password\";s:60:\"$2y$12$k9EYMuxvE4OVOmp7wycVIu7nKMKVNm1ftjEUPy7Wv995tFi3uOOwy\";s:17:\"two_factor_secret\";N;s:25:\"two_factor_recovery_codes\";N;s:23:\"two_factor_confirmed_at\";N;s:20:\"profile_picture_path\";N;s:3:\"bio\";N;s:14:\"remember_token\";N;s:10:\"created_at\";s:19:\"2026-05-01 15:27:19\";s:10:\"updated_at\";s:19:\"2026-05-02 01:50:56\";}s:11:\"\0*\0original\";a:17:{s:2:\"id\";i:1;s:8:\"username\";s:14:\"rafael_udtohan\";s:10:\"first_name\";s:6:\"rafael\";s:9:\"last_name\";s:7:\"udtohan\";s:13:\"date_of_birth\";s:10:\"2001-03-21\";s:3:\"sex\";s:4:\"male\";s:5:\"email\";s:30:\"rafael.udtohan@foundationu.com\";s:17:\"email_verified_at\";N;s:8:\"password\";s:60:\"$2y$12$k9EYMuxvE4OVOmp7wycVIu7nKMKVNm1ftjEUPy7Wv995tFi3uOOwy\";s:17:\"two_factor_secret\";N;s:25:\"two_factor_recovery_codes\";N;s:23:\"two_factor_confirmed_at\";N;s:20:\"profile_picture_path\";N;s:3:\"bio\";N;s:14:\"remember_token\";N;s:10:\"created_at\";s:19:\"2026-05-01 15:27:19\";s:10:\"updated_at\";s:19:\"2026-05-02 01:50:56\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:17:\"email_verified_at\";s:8:\"datetime\";s:8:\"password\";s:6:\"hashed\";s:23:\"two_factor_confirmed_at\";s:8:\"datetime\";s:13:\"date_of_birth\";s:4:\"date\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:3:{i:0;s:4:\"name\";i:1;s:9:\"full_name\";i:2;s:19:\"profile_picture_url\";}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:4:{i:0;s:8:\"password\";i:1;s:17:\"two_factor_secret\";i:2;s:25:\"two_factor_recovery_codes\";i:3;s:14:\"remember_token\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:9:{i:0;s:5:\"email\";i:1;s:8:\"password\";i:2;s:8:\"username\";i:3;s:10:\"first_name\";i:4;s:9:\"last_name\";i:5;s:13:\"date_of_birth\";i:6;s:3:\"sex\";i:7;s:3:\"bio\";i:8;s:20:\"profile_picture_path\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:19:\"\0*\0authPasswordName\";s:8:\"password\";s:20:\"\0*\0rememberTokenName\";s:14:\"remember_token\";}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1777688283);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` varchar(1000) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comment_reactions`
--

CREATE TABLE `comment_reactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'like',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_reactions`
--

INSERT INTO `comment_reactions` (`id`, `comment_id`, `user_id`, `type`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'like', '2026-05-06 13:42:21', '2026-05-06 13:42:21');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `follower_id` bigint(20) UNSIGNED NOT NULL,
  `following_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `following_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-05-01 18:14:37', '2026-05-01 18:14:37'),
(2, 2, 1, '2026-05-01 18:16:12', '2026-05-01 18:16:12');

-- --------------------------------------------------------

--
-- Table structure for table `hidden_comments`
--

CREATE TABLE `hidden_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hidden_posts`
--

CREATE TABLE `hidden_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', '0001_01_01_000000', 'create_users_table', 'default', 'App', 0, 1),
(2, '0001_01_01_000001_create_cache_table', '0001_01_01_000001', 'create_cache_table', 'default', 'App', 0, 1),
(3, '0001_01_01_000002_create_jobs_table', '0001_01_01_000002', 'create_jobs_table', 'default', 'App', 0, 1),
(4, '2024_01_01_000001_create_posts_table', '2024_01_01_000001', 'create_posts_table', 'default', 'App', 0, 1),
(5, '2024_01_01_000002_create_follows_table', '2024_01_01_000002', 'create_follows_table', 'default', 'App', 0, 1),
(6, '2025_08_14_170933_add_two_factor_columns_to_users_table', '2025_08_14_170933', 'add_two_factor_columns_to_users_table', 'default', 'App', 0, 2),
(7, '2026_04_30_233917_add_profile_fields_to_users_table', '2026_04_30_233917', 'add_profile_fields_to_users_table', 'default', 'App', 0, 3),
(8, '2026_04_30_234115_create_posts_table', '2026_04_30_234115', 'create_posts_table', 'default', 'App', 0, 4),
(9, '2026_04_30_234144_create_follows_table', '2026_04_30_234144', 'create_follows_table', 'default', 'App', 0, 4),
(10, '2026_04_30_234128_create_comments_table', '2026_04_30_234128', 'create_comments_table', 'default', 'App', 0, 5),
(11, '2026_04_30_234136_create_reactions_table', '2026_04_30_234136', 'create_reactions_table', 'default', 'App', 0, 5),
(12, '2026_04_30_234149_create_notifications_table', '2026_04_30_234149', 'create_notifications_table', 'default', 'App', 0, 5),
(13, '2026-05-06-000001_AddPhotoPathToPosts', '2026-05-06-000001', 'App\\Database\\Migrations\\AddPhotoPathToPosts', 'default', 'App', 0, 6),
(14, '2026-05-06-000002_AddMediaTypeToPosts', '2026-05-06-000002', 'App\\Database\\Migrations\\AddMediaTypeToPosts', 'default', 'App', 0, 7),
(15, '2026-05-06-000003_AddCoverPhotoPathToUsers', '2026-05-06-000003', 'App\\Database\\Migrations\\AddCoverPhotoPathToUsers', 'default', 'App', 0, 8),
(16, '2026-05-06-000004_CreateSavedPostsTable', '2026-05-06-000004', 'App\\Database\\Migrations\\CreateSavedPostsTable', 'default', 'App', 0, 9),
(17, '2026-05-06-000005_CreateHiddenPostsTable', '2026-05-06-000005', 'App\\Database\\Migrations\\CreateHiddenPostsTable', 'default', 'App', 0, 9),
(18, '2026-05-06-000006_AddParentIdToComments', '2026-05-06-000006', 'App\\Database\\Migrations\\AddParentIdToComments', 'default', 'App', 0, 10),
(19, '2026-05-06-000007_CreateCommentReactionsTable', '2026-05-06-000007', 'App\\Database\\Migrations\\CreateCommentReactionsTable', 'default', 'App', 0, 10),
(20, '2026-05-06-000008_AddTypeToCommentReactions', '2026-05-06-000008', 'App\\Database\\Migrations\\AddTypeToCommentReactions', 'default', 'App', 0, 11),
(21, '2026-05-06-000009_CreateHiddenCommentsTable', '2026-05-06-000009', 'App\\Database\\Migrations\\CreateHiddenCommentsTable', 'default', 'App', 0, 12);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('5abc91d2-f8ae-45d3-b9be-f6d2488c6fcf', 'App\\Notifications\\NewFollowerNotification', 'App\\Models\\User', 2, '{\"follower_id\":1,\"follower_name\":\"rafael udtohan\",\"follower_username\":\"rafael_udtohan\",\"message\":\"rafael udtohan started following you.\"}', '2026-05-01 18:16:03', '2026-05-01 18:14:39', '2026-05-01 18:16:03'),
('61cfaeac-c09a-4608-8fa2-064e76b38a2d', 'App\\Notifications\\NewFollowerNotification', 'App\\Models\\User', 1, '{\"follower_id\":2,\"follower_name\":\"Gab pol\",\"follower_username\":\"gapol\",\"message\":\"Gab pol started following you.\"}', NULL, '2026-05-01 18:16:12', '2026-05-01 18:16:12');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `media_type` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 'nbhbvgucywupok[lmvpknojehfjhvbvhrbfiowpdkpo', '2026-05-01 07:28:31', '2026-05-01 07:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('like','love','haha','wow','sad','angry') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reactions`
--

INSERT INTO `reactions` (`id`, `post_id`, `user_id`, `type`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 'love', '2026-05-01 17:50:04', '2026-05-01 17:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `saved_posts`
--

CREATE TABLE `saved_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('DkphiaCvFKUYdX0c2Eul7CnSAagKWyjDh4RxpB6U', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ2VkFNWTZIODIydHB5S094YXBmYWpRYVlCR0xPVHg1aUYzQXpqOVBuIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1777688176);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `sex` enum('male','female','other') NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `profile_picture_path` varchar(255) DEFAULT NULL,
  `cover_photo_path` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `date_of_birth`, `sex`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `profile_picture_path`, `cover_photo_path`, `bio`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'rafael_udtohan', 'rafael', 'udtohan', '2001-03-21', 'male', 'rafael.udtohan@foundationu.com', NULL, '$2y$12$k9EYMuxvE4OVOmp7wycVIu7nKMKVNm1ftjEUPy7Wv995tFi3uOOwy', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-01 07:27:19', '2026-05-01 17:50:56'),
(2, 'gapol', 'Gab', 'pol', '2001-12-21', 'male', 'gab.pol@foundationu.com', NULL, '$2y$12$z70TEYaFYVahqwaEeDTtK.Z37Nf1Pt.YvAhKiyGkpQXLyzvFj4B8y', NULL, NULL, NULL, 'avatars/2/profile.jpg', NULL, NULL, NULL, '2026-05-01 18:13:00', '2026-05-01 18:13:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_user_id_foreign` (`user_id`),
  ADD KEY `comments_post_id_created_at_index` (`post_id`,`created_at`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `follows_follower_id_following_id_unique` (`follower_id`,`following_id`),
  ADD KEY `follows_following_id_foreign` (`following_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posts_user_id_foreign` (`user_id`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reactions_post_id_user_id_unique` (`post_id`,`user_id`),
  ADD KEY `reactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `comment_id_user_id` (`comment_id`,`user_id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hidden_comments`
--
ALTER TABLE `hidden_comments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_comment_id` (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indexes for table `hidden_posts`
--
ALTER TABLE `hidden_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_post_id` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `saved_posts`
--
ALTER TABLE `saved_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_post_id` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `hidden_comments`
--
ALTER TABLE `hidden_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hidden_posts`
--
ALTER TABLE `hidden_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_posts`
--
ALTER TABLE `saved_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_follower_id_foreign` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_following_id_foreign` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hidden_comments`
--
ALTER TABLE `hidden_comments`
  ADD CONSTRAINT `hidden_comments_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hidden_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hidden_posts`
--
ALTER TABLE `hidden_posts`
  ADD CONSTRAINT `hidden_posts_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `hidden_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `saved_posts`
--
ALTER TABLE `saved_posts`
  ADD CONSTRAINT `saved_posts_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `saved_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
