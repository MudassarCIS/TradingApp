-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 29, 2025 at 05:18 PM
-- Server version: 8.0.30
-- PHP Version: 8.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laravel_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive','paused') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `strategy` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trading_rules` json NOT NULL,
  `initial_balance` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `current_balance` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `total_profit` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `total_loss` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `win_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total_trades` int NOT NULL DEFAULT '0',
  `winning_trades` int NOT NULL DEFAULT '0',
  `losing_trades` int NOT NULL DEFAULT '0',
  `max_drawdown` decimal(5,2) NOT NULL DEFAULT '0.00',
  `risk_per_trade` decimal(5,2) NOT NULL DEFAULT '2.00',
  `auto_trading` tinyint(1) NOT NULL DEFAULT '1',
  `last_trade_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_accounts`
--

CREATE TABLE `api_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `exchange` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passphrase` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `permissions` json DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bonus_wallets`
--

CREATE TABLE `bonus_wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `deposit_id` bigint UNSIGNED DEFAULT NULL,
  `investment_amount` decimal(20,8) NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED NOT NULL,
  `parent_level` tinyint NOT NULL,
  `bonus_amount` decimal(20,8) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT',
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-admin@gmail.com|127.0.0.1', 'i:1;', 1761753307),
('laravel-cache-admin@gmail.com|127.0.0.1:timer', 'i:1761753307;', 1761753307),
('laravel-cache-customer@example.com|127.0.0.1', 'i:2;', 1761149367),
('laravel-cache-customer@example.com|127.0.0.1:timer', 'i:1761149367;', 1761149367);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_wallets`
--

CREATE TABLE `customers_wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT',
  `amount` decimal(20,8) NOT NULL,
  `payment_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_type` enum('debit','credit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_id` bigint UNSIGNED DEFAULT NULL COMMENT 'deposit id, bonus_wallet id, trade id etc',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `deposit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `network` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `proof_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `deposit_id`, `amount`, `currency`, `network`, `status`, `proof_image`, `notes`, `rejection_reason`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 32, 'TEST_1761149590_32', 1238.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-15 11:13:10', '2025-10-20 11:13:10', '2025-10-22 11:13:10'),
(2, 33, 'TEST_1761149590_33', 819.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-12 11:13:10', '2025-10-12 11:13:10', '2025-10-22 11:13:10'),
(3, 34, 'TEST_1761149590_34', 1131.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-09-30 11:13:10', '2025-09-29 11:13:10', '2025-10-22 11:13:10'),
(4, 35, 'TEST_1761149590_35', 1379.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-18 11:13:10', '2025-09-30 11:13:10', '2025-10-22 11:13:10'),
(5, 36, 'TEST_1761149590_36', 1484.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-17 11:13:10', '2025-10-15 11:13:10', '2025-10-22 11:13:10'),
(6, 37, 'TEST_1761149590_37', 1128.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-21 11:13:10', '2025-10-07 11:13:10', '2025-10-22 11:13:10'),
(7, 38, 'TEST_1761149590_38', 1430.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-06 11:13:10', '2025-10-08 11:13:10', '2025-10-22 11:13:10'),
(8, 39, 'TEST_1761149590_39', 1129.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-09-24 11:13:10', '2025-10-01 11:13:10', '2025-10-22 11:13:10'),
(9, 40, 'TEST_1761149590_40', 1605.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-15 11:13:10', '2025-10-01 11:13:10', '2025-10-22 11:13:10'),
(10, 41, 'TEST_1761149590_41', 1735.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-11 11:13:10', '2025-10-19 11:13:10', '2025-10-22 11:13:10'),
(11, 42, 'TEST_1761149590_42', 1621.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-09-28 11:13:10', '2025-09-24 11:13:10', '2025-10-22 11:13:10'),
(12, 43, 'TEST_1761149590_43', 809.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-09-25 11:13:10', '2025-10-06 11:13:10', '2025-10-22 11:13:10'),
(13, 112, 'TEST_1761149590_112', 1315.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-11 11:13:10', '2025-09-26 11:13:10', '2025-10-22 11:13:10'),
(14, 44, 'TEST_1761149590_44', 1813.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-09-23 11:13:10', '2025-10-04 11:13:10', '2025-10-22 11:13:10'),
(15, 45, 'TEST_1761149590_45', 1669.00000000, 'USDT', 'TRC20', 'approved', NULL, 'Test deposit for referral system', NULL, NULL, '2025-10-02 11:13:10', '2025-10-18 11:13:10', '2025-10-22 11:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('support','general','technical','billing') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'support',
  `status` enum('open','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `is_read_by_user` tinyint(1) NOT NULL DEFAULT '0',
  `is_read_by_admin` tinyint(1) NOT NULL DEFAULT '0',
  `last_reply_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_11_070707_create_permission_tables', 1),
(5, '2025_09_22_131004_create_profiles_table', 1),
(6, '2025_09_22_131009_create_wallets_table', 1),
(7, '2025_09_22_131014_create_transactions_table', 1),
(8, '2025_09_22_131020_create_trades_table', 1),
(9, '2025_09_22_131026_create_agents_table', 1),
(10, '2025_09_22_131033_create_referrals_table', 1),
(11, '2025_09_22_131038_create_messages_table', 1),
(12, '2025_09_22_131043_create_notifications_table', 1),
(13, '2025_09_22_131048_create_packages_table', 1),
(14, '2025_09_22_131054_create_api_accounts_table', 1),
(15, '2025_09_22_131205_add_trading_fields_to_users_table', 1),
(16, '2025_09_23_073924_update_user_type_enum', 1),
(17, '2025_10_08_202305_create_plans_table', 1),
(18, '2025_10_08_210509_create_wallet_addresses_table', 1),
(19, '2025_10_13_184644_create_deposits_table', 1),
(20, '2025_10_18_000010_add_active_plan_to_users', 1),
(21, '2025_10_18_000011_create_bonus_wallets_table', 1),
(22, '2025_10_18_000012_create_customers_wallets_table', 1),
(23, '2025_10_29_000001_create_rent_bot_packages_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(1, 'App\\Models\\User', 3),
(2, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(2, 'App\\Models\\User', 10),
(2, 'App\\Models\\User', 11),
(2, 'App\\Models\\User', 12),
(2, 'App\\Models\\User', 13),
(2, 'App\\Models\\User', 14),
(2, 'App\\Models\\User', 15),
(2, 'App\\Models\\User', 16),
(2, 'App\\Models\\User', 17),
(2, 'App\\Models\\User', 18),
(2, 'App\\Models\\User', 19),
(2, 'App\\Models\\User', 20),
(2, 'App\\Models\\User', 21),
(2, 'App\\Models\\User', 22),
(2, 'App\\Models\\User', 23),
(2, 'App\\Models\\User', 24),
(2, 'App\\Models\\User', 25),
(2, 'App\\Models\\User', 26),
(2, 'App\\Models\\User', 27),
(2, 'App\\Models\\User', 28),
(2, 'App\\Models\\User', 29),
(2, 'App\\Models\\User', 30),
(2, 'App\\Models\\User', 31),
(2, 'App\\Models\\User', 32),
(2, 'App\\Models\\User', 33),
(2, 'App\\Models\\User', 34),
(2, 'App\\Models\\User', 35),
(2, 'App\\Models\\User', 36),
(2, 'App\\Models\\User', 37),
(2, 'App\\Models\\User', 38),
(2, 'App\\Models\\User', 39),
(2, 'App\\Models\\User', 40),
(2, 'App\\Models\\User', 41),
(2, 'App\\Models\\User', 42),
(2, 'App\\Models\\User', 43),
(2, 'App\\Models\\User', 44),
(2, 'App\\Models\\User', 45),
(2, 'App\\Models\\User', 46),
(2, 'App\\Models\\User', 47),
(2, 'App\\Models\\User', 48),
(2, 'App\\Models\\User', 49),
(2, 'App\\Models\\User', 50),
(2, 'App\\Models\\User', 51),
(2, 'App\\Models\\User', 52),
(2, 'App\\Models\\User', 53),
(2, 'App\\Models\\User', 54),
(2, 'App\\Models\\User', 55),
(2, 'App\\Models\\User', 56),
(2, 'App\\Models\\User', 57),
(2, 'App\\Models\\User', 58),
(2, 'App\\Models\\User', 59),
(2, 'App\\Models\\User', 60),
(2, 'App\\Models\\User', 61),
(2, 'App\\Models\\User', 62),
(2, 'App\\Models\\User', 63),
(2, 'App\\Models\\User', 64),
(2, 'App\\Models\\User', 65),
(2, 'App\\Models\\User', 66),
(2, 'App\\Models\\User', 67),
(2, 'App\\Models\\User', 68),
(2, 'App\\Models\\User', 69),
(2, 'App\\Models\\User', 70),
(2, 'App\\Models\\User', 71),
(2, 'App\\Models\\User', 72),
(2, 'App\\Models\\User', 73),
(2, 'App\\Models\\User', 74),
(2, 'App\\Models\\User', 75),
(2, 'App\\Models\\User', 76),
(2, 'App\\Models\\User', 77),
(2, 'App\\Models\\User', 78),
(2, 'App\\Models\\User', 79),
(2, 'App\\Models\\User', 80),
(2, 'App\\Models\\User', 81),
(2, 'App\\Models\\User', 82),
(2, 'App\\Models\\User', 83),
(2, 'App\\Models\\User', 84),
(2, 'App\\Models\\User', 85),
(2, 'App\\Models\\User', 86),
(2, 'App\\Models\\User', 87),
(2, 'App\\Models\\User', 88),
(2, 'App\\Models\\User', 89),
(2, 'App\\Models\\User', 90),
(2, 'App\\Models\\User', 91),
(2, 'App\\Models\\User', 92),
(2, 'App\\Models\\User', 93),
(2, 'App\\Models\\User', 94),
(2, 'App\\Models\\User', 95),
(2, 'App\\Models\\User', 96),
(2, 'App\\Models\\User', 97),
(2, 'App\\Models\\User', 98),
(2, 'App\\Models\\User', 99),
(2, 'App\\Models\\User', 100),
(2, 'App\\Models\\User', 101),
(2, 'App\\Models\\User', 102),
(2, 'App\\Models\\User', 103),
(2, 'App\\Models\\User', 104),
(2, 'App\\Models\\User', 105),
(2, 'App\\Models\\User', 106),
(2, 'App\\Models\\User', 107),
(2, 'App\\Models\\User', 108),
(2, 'App\\Models\\User', 109),
(2, 'App\\Models\\User', 110),
(2, 'App\\Models\\User', 111),
(2, 'App\\Models\\User', 112),
(2, 'App\\Models\\User', 113),
(2, 'App\\Models\\User', 114),
(2, 'App\\Models\\User', 115),
(2, 'App\\Models\\User', 116),
(2, 'App\\Models\\User', 117),
(2, 'App\\Models\\User', 118),
(2, 'App\\Models\\User', 119),
(2, 'App\\Models\\User', 120),
(2, 'App\\Models\\User', 121),
(2, 'App\\Models\\User', 122),
(2, 'App\\Models\\User', 123),
(2, 'App\\Models\\User', 124),
(2, 'App\\Models\\User', 125),
(2, 'App\\Models\\User', 126),
(2, 'App\\Models\\User', 127),
(2, 'App\\Models\\User', 128),
(2, 'App\\Models\\User', 129),
(2, 'App\\Models\\User', 130),
(2, 'App\\Models\\User', 131);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(20,8) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT',
  `min_investment` decimal(20,8) NOT NULL,
  `max_investment` decimal(20,8) DEFAULT NULL,
  `daily_return_rate` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `duration_days` int NOT NULL DEFAULT '30',
  `profit_share` decimal(5,2) NOT NULL DEFAULT '50.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `features` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `price`, `currency`, `min_investment`, `max_investment`, `daily_return_rate`, `duration_days`, `profit_share`, `is_active`, `features`, `created_at`, `updated_at`) VALUES
(1, 'Starter Package', 'Perfect for beginners', 100.00000000, 'USDT', 100.00000000, 500.00000000, 2.5000, 30, 50.00, 1, '[\"Basic AI trading\", \"Email support\", \"Mobile app access\"]', '2025-09-22 03:20:42', '2025-09-22 03:20:42'),
(2, 'Professional Package', 'For serious traders', 500.00000000, 'USDT', 500.00000000, 2000.00000000, 3.5000, 30, 50.00, 1, '[\"Advanced AI trading\", \"Priority support\", \"Custom strategies\", \"Real-time alerts\"]', '2025-09-22 03:20:42', '2025-09-22 03:20:42'),
(3, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\", \"24/7 support\", \"Custom strategies\", \"Real-time alerts\", \"Personal manager\"]', '2025-09-22 03:20:42', '2025-09-22 03:20:42'),
(4, 'Starter Package', 'Perfect for beginners', 100.00000000, 'USDT', 100.00000000, 500.00000000, 2.5000, 30, 50.00, 1, '[\"Basic AI trading\", \"Email support\", \"Mobile app access\"]', '2025-09-22 21:39:50', '2025-09-22 21:39:50'),
(5, 'Professional Package', 'For serious traders', 500.00000000, 'USDT', 500.00000000, 2000.00000000, 3.5000, 30, 50.00, 1, '[\"Advanced AI trading\", \"Priority support\", \"Custom strategies\", \"Real-time alerts\"]', '2025-09-22 21:39:50', '2025-09-22 21:39:50'),
(6, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\", \"24/7 support\", \"Custom strategies\", \"Real-time alerts\", \"Personal manager\"]', '2025-09-22 21:39:50', '2025-09-22 21:39:50'),
(7, 'Starter Package', 'Perfect for beginners', 100.00000000, 'USDT', 100.00000000, 500.00000000, 2.5000, 30, 50.00, 1, '[\"Basic AI trading\", \"Email support\", \"Mobile app access\"]', '2025-10-08 11:17:17', '2025-10-08 11:17:17'),
(8, 'Professional Package', 'For serious traders', 500.00000000, 'USDT', 500.00000000, 2000.00000000, 3.5000, 30, 50.00, 1, '[\"Advanced AI trading\", \"Priority support\", \"Custom strategies\", \"Real-time alerts\"]', '2025-10-08 11:17:17', '2025-10-08 11:17:17'),
(9, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\", \"24/7 support\", \"Custom strategies\", \"Real-time alerts\", \"Personal manager\"]', '2025-10-08 11:17:17', '2025-10-08 11:17:17'),
(10, 'Starter Package', 'Perfect for beginners', 100.00000000, 'USDT', 100.00000000, 500.00000000, 2.5000, 30, 50.00, 1, '[\"Basic AI trading\", \"Email support\", \"Mobile app access\"]', '2025-10-08 11:44:12', '2025-10-08 11:44:12'),
(11, 'Professional Package', 'For serious traders', 500.00000000, 'USDT', 500.00000000, 2000.00000000, 3.5000, 30, 50.00, 1, '[\"Advanced AI trading\", \"Priority support\", \"Custom strategies\", \"Real-time alerts\"]', '2025-10-08 11:44:12', '2025-10-08 11:44:12'),
(12, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\", \"24/7 support\", \"Custom strategies\", \"Real-time alerts\", \"Personal manager\"]', '2025-10-08 11:44:12', '2025-10-08 11:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage_users', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07'),
(2, 'manage_trades', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07'),
(3, 'manage_agents', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07'),
(4, 'manage_wallets', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07'),
(5, 'manage_transactions', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07'),
(6, 'manage_packages', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07'),
(7, 'view_reports', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07'),
(8, 'manage_support', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `investment_amount` decimal(10,2) NOT NULL,
  `joining_fee` decimal(10,2) NOT NULL,
  `bots_allowed` int NOT NULL,
  `trades_per_day` int NOT NULL,
  `direct_bonus` decimal(10,2) NOT NULL,
  `referral_level_1` decimal(5,2) NOT NULL,
  `referral_level_2` decimal(5,2) NOT NULL,
  `referral_level_3` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `investment_amount`, `joining_fee`, `bots_allowed`, `trades_per_day`, `direct_bonus`, `referral_level_1`, `referral_level_2`, `referral_level_3`, `is_active`, `sort_order`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 100.00, 10.00, 1, 5, 5.00, 5.00, 3.00, 2.00, 1, 1, 'Perfect for beginners', '2025-10-08 11:51:29', '2025-10-08 11:51:29'),
(2, 'Bronze', 500.00, 25.00, 2, 10, 25.00, 7.00, 5.00, 3.00, 1, 2, 'Great for regular investors', '2025-10-08 11:51:29', '2025-10-08 11:51:29'),
(3, 'Silver', 1000.00, 50.00, 3, 15, 50.00, 10.00, 7.00, 5.00, 1, 3, 'Ideal for serious investors', '2025-10-08 11:51:29', '2025-10-08 11:51:29'),
(4, 'Gold', 2500.00, 125.00, 5, 25, 125.00, 12.00, 10.00, 7.00, 1, 4, 'Premium investment package', '2025-10-08 11:51:29', '2025-10-08 11:51:29'),
(5, 'Diamond', 5000.00, 250.00, 8, 40, 250.00, 15.00, 12.00, 10.00, 1, 5, 'Elite investment opportunity', '2025-10-08 11:51:29', '2025-10-08 11:51:29'),
(6, 'Platinum', 10000.00, 500.00, 12, 60, 500.00, 18.00, 15.00, 12.00, 1, 6, 'VIP investment experience', '2025-10-08 11:51:29', '2025-10-08 11:51:29'),
(7, 'Elite', 25000.00, 1250.00, 20, 100, 1250.00, 20.00, 18.00, 15.00, 1, 7, 'Ultimate investment package', '2025-10-08 11:51:29', '2025-10-08 11:51:29'),
(8, 'Basic Plan', 1000.00, 0.00, 1, 10, 0.00, 10.00, 5.00, 2.00, 1, 1, 'Basic investment plan for testing', '2025-10-18 03:10:55', '2025-10-18 03:10:55');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_document_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_document_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_document_front` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_document_back` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kyc_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `kyc_notes` text COLLATE utf8mb4_unicode_ci,
  `transaction_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referred_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `date_of_birth`, `address`, `city`, `state`, `country`, `postal_code`, `id_document_type`, `id_document_number`, `id_document_front`, `id_document_back`, `kyc_status`, `kyc_notes`, `transaction_password`, `referral_code`, `referred_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Admin', 'User', '+1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'ADMIN001', NULL, '2025-10-22 10:37:08', '2025-10-22 10:37:08'),
(2, 2, 'John', 'Doe', '+1234567891', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '$2y$12$H8PNMTIJipeKzIx7oe4YSe1xeFasVoASMiIK/RF2VgnUnJQE.XnUa', 'JOHN001', NULL, '2025-10-22 10:37:09', '2025-10-22 10:37:09'),
(3, 4, 'Alice', 'Johnson', '+1234567765', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'IKP1DDPI', NULL, '2025-10-22 11:09:19', '2025-10-22 11:09:19'),
(4, 5, 'Bob', 'Smith', '+1234567261', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, '94XGYQHE', NULL, '2025-10-22 11:09:19', '2025-10-22 11:09:19'),
(5, 6, 'Carol', 'Davis', '+1234567822', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'RZXUL0AS', NULL, '2025-10-22 11:09:20', '2025-10-22 11:09:20'),
(6, 7, 'David', 'Wilson', '+1234567299', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'WFQFOTTM', NULL, '2025-10-22 11:09:20', '2025-10-22 11:09:20'),
(7, 8, 'Eva', 'Brown', '+1234567849', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'OF7IENU1', NULL, '2025-10-22 11:09:20', '2025-10-22 11:09:20'),
(8, 9, 'Frank', 'Miller', '+1234567936', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'XO9OHK09', NULL, '2025-10-22 11:09:21', '2025-10-22 11:09:21'),
(9, 10, 'Grace', 'Lee', '+1234567145', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rejected', NULL, NULL, 'WIFYFB2I', NULL, '2025-10-22 11:09:21', '2025-10-22 11:09:21'),
(10, 11, 'Henry', 'Taylor', '+1234567692', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'C7IIHWQZ', NULL, '2025-10-22 11:09:22', '2025-10-22 11:09:22'),
(11, 12, 'Customer_100_1', 'User', '+1234567597', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, '8T520Z5R', NULL, '2025-10-22 11:09:22', '2025-10-22 11:09:22'),
(12, 13, 'Customer_100_2', 'User', '+1234567912', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'ATSRCEXQ', NULL, '2025-10-22 11:09:22', '2025-10-22 11:09:22'),
(13, 14, 'Customer_100_3', 'User', '+1234567213', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'NPQGOMWU', NULL, '2025-10-22 11:09:23', '2025-10-22 11:09:23'),
(14, 15, 'Customer_100_4', 'User', '+1234567885', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'WAJWQ3PO', NULL, '2025-10-22 11:09:23', '2025-10-22 11:09:23'),
(15, 16, 'Customer_100_5', 'User', '+1234567907', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'OKRUISYJ', NULL, '2025-10-22 11:09:23', '2025-10-22 11:09:23'),
(16, 17, 'Customer_1000_1', 'User', '+1234567411', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'ZOITP9JP', NULL, '2025-10-22 11:09:24', '2025-10-22 11:09:24'),
(17, 18, 'Customer_1000_2', 'User', '+1234567118', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'RLLGVPE8', NULL, '2025-10-22 11:09:24', '2025-10-22 11:09:24'),
(18, 19, 'Customer_1000_3', 'User', '+1234567107', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'I1FTWJLD', NULL, '2025-10-22 11:09:24', '2025-10-22 11:09:24'),
(19, 20, 'Customer_3000_1', 'User', '+1234567459', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, '14CI0CO6', NULL, '2025-10-22 11:09:25', '2025-10-22 11:09:25'),
(20, 21, 'Customer_3000_2', 'User', '+1234567472', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'JECHCI3Z', NULL, '2025-10-22 11:09:25', '2025-10-22 11:09:25'),
(21, 22, 'Customer_6000_1', 'User', '+1234567364', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, '12ZI9A3B', NULL, '2025-10-22 11:09:25', '2025-10-22 11:09:25'),
(22, 23, 'Customer_6000_2', 'User', '+1234567686', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'S7TBIX9M', NULL, '2025-10-22 11:09:26', '2025-10-22 11:09:26'),
(23, 24, 'Main', 'Referrer', '+1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'SRP1CCQP', NULL, '2025-10-22 11:09:26', '2025-10-22 11:09:26'),
(24, 25, 'Level1', 'User1', '+12345678911', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, '2O9EQWGX', NULL, '2025-10-22 11:09:26', '2025-10-22 11:09:26'),
(25, 26, 'Level2', 'User1', '+12345678921', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'IXXXUNB4', NULL, '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(26, 27, 'Level3', 'User1', '+12345678931', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'LW70UVQV', NULL, '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(27, 28, 'Level2', 'User2', '+12345678922', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'BPSEF3TH', NULL, '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(28, 29, 'Level1', 'User2', '+12345678912', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, '9TNNNQMW', NULL, '2025-10-22 11:09:28', '2025-10-22 11:09:28'),
(29, 30, 'Level1', 'User3', '+12345678913', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'UO5VTJTB', NULL, '2025-10-22 11:09:30', '2025-10-22 11:09:30'),
(30, 31, 'VIP', 'Referrer', '+1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'ASTG9WAD', NULL, '2025-10-22 11:09:31', '2025-10-22 11:09:31'),
(31, 32, 'Level1', 'User1', '+1234567131', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'SLFYYCLP', NULL, '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(32, 33, 'Level1', 'User2', '+1234567362', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'PSPX53S7', NULL, '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(33, 34, 'Level1', 'User3', '+1234567554', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'A9BWILWY', NULL, '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(34, 35, 'Level1', 'User4', '+1234567463', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'MZVYFCUX', NULL, '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(35, 36, 'Level1', 'User5', '+1234567350', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'WGS6UZGH', NULL, '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(36, 37, 'Level2', 'User1', '+1234567768', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'JPQAXHIN', NULL, '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(37, 38, 'Level2', 'User2', '+1234567757', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'Y0WFIVLC', NULL, '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(38, 39, 'Level2', 'User3', '+1234567546', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'JJDKYBQW', NULL, '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(39, 40, 'Level2', 'User4', '+1234567678', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'DZTLYRPZ', NULL, '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(40, 41, 'Level2', 'User1', '+1234567894', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'H4NUWVSQ', NULL, '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(41, 42, 'Level2', 'User2', '+1234567608', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '9RQVQGEK', NULL, '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(42, 43, 'Level2', 'User3', '+1234567393', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'NOP89YES', NULL, '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(43, 44, 'Level2', 'User1', '+1234567610', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'ROX9CBS2', NULL, '2025-10-22 11:09:36', '2025-10-22 11:09:36'),
(44, 45, 'Level2', 'User2', '+1234567113', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'REDU95SD', NULL, '2025-10-22 11:09:36', '2025-10-22 11:09:36'),
(45, 46, 'Level2', 'User3', '+1234567271', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'YVHYEMNG', NULL, '2025-10-22 11:09:36', '2025-10-22 11:09:36'),
(46, 47, 'Level2', 'User1', '+1234567419', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'H3DH4FA1', NULL, '2025-10-22 11:09:37', '2025-10-22 11:09:37'),
(47, 48, 'Level2', 'User2', '+1234567810', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'A8RQ6QZU', NULL, '2025-10-22 11:09:37', '2025-10-22 11:09:37'),
(48, 49, 'Level2', 'User3', '+1234567556', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'HBPLUWD8', NULL, '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(49, 50, 'Level2', 'User4', '+1234567549', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'DXKOIPRQ', NULL, '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(50, 51, 'Level2', 'User1', '+1234567697', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'IENAS3F2', NULL, '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(51, 52, 'Level2', 'User2', '+1234567618', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '9HFJOKT2', NULL, '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(52, 53, 'Level2', 'User3', '+1234567971', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '1SFBWUNB', NULL, '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(53, 54, 'Level2', 'User4', '+1234567200', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '2XLPBUPE', NULL, '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(54, 55, 'Level3', 'User1', '+1234567232', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'YKR2PNUQ', NULL, '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(55, 56, 'Level3', 'User2', '+1234567236', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'STCGXVHH', NULL, '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(56, 57, 'Level3', 'User1', '+1234567847', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'BKDH9NOB', NULL, '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(57, 58, 'Level3', 'User2', '+1234567731', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'TXNNQ1SS', NULL, '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(58, 59, 'Level3', 'User3', '+1234567815', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'S135XCLE', NULL, '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(59, 60, 'Level3', 'User1', '+1234567224', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'OBASMTSK', NULL, '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(60, 61, 'Level3', 'User2', '+1234567401', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'DVVKSDWJ', NULL, '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(61, 62, 'Level3', 'User3', '+1234567919', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'ISGDXOCN', NULL, '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(62, 63, 'Level3', 'User1', '+1234567538', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '1D8I05PA', NULL, '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(63, 64, 'Level3', 'User2', '+1234567250', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'B1PTR6IF', NULL, '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(64, 65, 'Level3', 'User3', '+1234567580', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'XS0HQVIL', NULL, '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(65, 66, 'Level3', 'User1', '+1234567428', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'JKXODUKS', NULL, '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(66, 67, 'Level3', 'User2', '+1234567623', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '07MGAHHB', NULL, '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(67, 68, 'Level3', 'User1', '+1234567346', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'EILF56KW', NULL, '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(68, 69, 'Level3', 'User2', '+1234567260', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'SUY4VIBK', NULL, '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(69, 70, 'Level3', 'User3', '+1234567195', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'LISDYQS6', NULL, '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(70, 71, 'Level3', 'User1', '+1234567801', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'DC9ECSHQ', NULL, '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(71, 72, 'Level3', 'User2', '+1234567169', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'SHAHPNER', NULL, '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(72, 73, 'Level3', 'User1', '+1234567754', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'QBRHPJFJ', NULL, '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(73, 74, 'Level3', 'User2', '+1234567542', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'T8A6TNHZ', NULL, '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(74, 75, 'Level3', 'User1', '+1234567540', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'YQYDB6OR', NULL, '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(75, 76, 'Level3', 'User2', '+1234567717', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'XNGCUFAZ', NULL, '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(76, 77, 'Level3', 'User1', '+1234567149', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'QH0I6DNB', NULL, '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(77, 78, 'Level3', 'User2', '+1234567779', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'OSIWPZEB', NULL, '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(78, 79, 'Level3', 'User1', '+1234567159', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'RH2ECQUT', NULL, '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(79, 80, 'Level3', 'User2', '+1234567933', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'SLZ8KABU', NULL, '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(80, 81, 'Level3', 'User3', '+1234567667', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'FNOVPRXF', NULL, '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(81, 82, 'Level3', 'User1', '+1234567699', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'DIPCLUXQ', NULL, '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(82, 83, 'Level3', 'User2', '+1234567909', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'Q7R6RSNT', NULL, '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(83, 84, 'Level3', 'User1', '+1234567777', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'FDCITAVJ', NULL, '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(84, 85, 'Level3', 'User2', '+1234567992', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'IRQWU17S', NULL, '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(85, 86, 'Level3', 'User1', '+1234567699', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'SUOAWVT8', NULL, '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(86, 87, 'Level3', 'User2', '+1234567348', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'QEVLMOO0', NULL, '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(87, 88, 'Level3', 'User1', '+1234567278', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'CLE7NJWD', NULL, '2025-10-22 11:09:51', '2025-10-22 11:09:51'),
(88, 89, 'Level3', 'User2', '+1234567151', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'REJ95HKO', NULL, '2025-10-22 11:09:51', '2025-10-22 11:09:51'),
(89, 90, 'Level3', 'User3', '+1234567936', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'SMAWMZGV', NULL, '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(90, 91, 'Level3', 'User1', '+1234567355', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'UDCNOIN8', NULL, '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(91, 92, 'Level3', 'User2', '+1234567868', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'ZSQDNNLS', NULL, '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(92, 93, 'Level3', 'User1', '+1234567396', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '6PIWDZ86', NULL, '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(93, 94, 'Level3', 'User2', '+1234567341', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '9QHXIEWI', NULL, '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(94, 95, 'Level3', 'User1', '+1234567963', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '8ABM5NTS', NULL, '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(95, 96, 'Level3', 'User2', '+1234567591', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '4KNNFQHP', NULL, '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(96, 97, 'Level3', 'User3', '+1234567499', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '6HPU6ZKA', NULL, '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(97, 98, 'Level4', 'User1', '+1234567686', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'O1Q1AO9Z', NULL, '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(98, 99, 'Level4', 'User1', '+1234567723', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '7W71QCU6', NULL, '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(99, 100, 'Level4', 'User2', '+1234567676', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'HFKZAT0P', NULL, '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(100, 101, 'Level4', 'User1', '+1234567995', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'ZLZTEQGQ', NULL, '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(101, 102, 'Level4', 'User2', '+1234567834', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '1MPYQBNC', NULL, '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(102, 103, 'Level4', 'User1', '+1234567749', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'QK528VOM', NULL, '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(103, 104, 'Level4', 'User1', '+1234567305', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'Z8YLRY1E', NULL, '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(104, 105, 'Level4', 'User1', '+1234567166', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '4RPBIQRL', NULL, '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(105, 106, 'Level4', 'User2', '+1234567799', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'UIUXJWHB', NULL, '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(106, 107, 'Level4', 'User1', '+1234567275', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '9H1JPOPA', NULL, '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(107, 108, 'Level4', 'User1', '+1234567535', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '9QC00ZOE', NULL, '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(108, 109, 'Level4', 'User1', '+1234567529', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'K0YDCELQ', NULL, '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(109, 110, 'Level4', 'User2', '+1234567962', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'WHRTQBRH', NULL, '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(110, 111, 'Level4', 'User1', '+1234567910', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '2GF9ADCZ', NULL, '2025-10-22 11:09:59', '2025-10-22 11:09:59'),
(111, 112, 'Level2', 'User4', '+1234567661', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'SCLO08NT', NULL, '2025-10-22 11:12:46', '2025-10-22 11:12:46'),
(112, 113, 'Level2', 'User4', '+1234567670', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'JDTBTIGT', NULL, '2025-10-22 11:12:48', '2025-10-22 11:12:48'),
(113, 114, 'Level3', 'User3', '+1234567379', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'JBI2Z5SN', NULL, '2025-10-22 11:12:55', '2025-10-22 11:12:55'),
(114, 115, 'Level3', 'User1', '+1234567127', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'PGQJQ8GF', NULL, '2025-10-22 11:12:55', '2025-10-22 11:12:55'),
(115, 116, 'Level3', 'User2', '+1234567487', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'QQKVILAT', NULL, '2025-10-22 11:12:56', '2025-10-22 11:12:56'),
(116, 117, 'Level3', 'User3', '+1234567978', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'OEBGZW7U', NULL, '2025-10-22 11:12:56', '2025-10-22 11:12:56'),
(117, 118, 'Level3', 'User3', '+1234567769', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'UPLCALL5', NULL, '2025-10-22 11:12:57', '2025-10-22 11:12:57'),
(118, 119, 'Level3', 'User3', '+1234567118', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'YQOHEDBH', NULL, '2025-10-22 11:12:58', '2025-10-22 11:12:58'),
(119, 120, 'Level3', 'User3', '+1234567780', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'R5HQJMKG', NULL, '2025-10-22 11:12:59', '2025-10-22 11:12:59'),
(120, 121, 'Level3', 'User1', '+1234567422', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'UBRZGEPG', NULL, '2025-10-22 11:12:59', '2025-10-22 11:12:59'),
(121, 122, 'Level3', 'User2', '+1234567255', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'WYTKXROB', NULL, '2025-10-22 11:13:00', '2025-10-22 11:13:00'),
(122, 123, 'Level3', 'User3', '+1234567812', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'QN4GAMOV', NULL, '2025-10-22 11:13:01', '2025-10-22 11:13:01'),
(123, 124, 'Level3', 'User3', '+1234567738', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'OZ5P8AMB', NULL, '2025-10-22 11:13:02', '2025-10-22 11:13:02'),
(124, 125, 'Level3', 'User3', '+1234567984', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'M1ETN0LB', NULL, '2025-10-22 11:13:05', '2025-10-22 11:13:05'),
(125, 126, 'Level4', 'User2', '+1234567129', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, '1N7JDK2Z', NULL, '2025-10-22 11:13:07', '2025-10-22 11:13:07'),
(126, 127, 'Level4', 'User2', '+1234567826', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'ME7Y4R9L', NULL, '2025-10-22 11:13:07', '2025-10-22 11:13:07'),
(127, 128, 'Level4', 'User2', '+1234567865', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'SZDKQ4ZO', NULL, '2025-10-22 11:13:08', '2025-10-22 11:13:08'),
(128, 129, 'Level4', 'User2', '+1234567903', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'KK3HHD4J', NULL, '2025-10-22 11:13:09', '2025-10-22 11:13:09'),
(129, 130, 'Level4', 'User1', '+1234567245', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'TPO4HVL7', NULL, '2025-10-22 11:13:10', '2025-10-22 11:13:10'),
(130, 131, 'Level4', 'User2', '+1234567316', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'TJILNJNU', NULL, '2025-10-22 11:13:10', '2025-10-22 11:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint UNSIGNED NOT NULL,
  `referrer_id` bigint UNSIGNED NOT NULL,
  `referred_id` bigint UNSIGNED NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '10.00',
  `total_commission` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `pending_commission` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `joined_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `referrer_id`, `referred_id`, `commission_rate`, `total_commission`, `pending_commission`, `status`, `joined_at`, `created_at`, `updated_at`) VALUES
(1, 24, 25, 15.00, 0.00000000, 0.00000000, 'active', '2025-10-11 11:09:26', '2025-10-22 11:09:26', '2025-10-22 11:09:26'),
(2, 25, 26, 8.00, 0.00000000, 0.00000000, 'active', '2025-10-17 11:09:27', '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(3, 26, 27, 3.00, 0.00000000, 0.00000000, 'active', '2025-10-13 11:09:27', '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(4, 25, 28, 8.00, 0.00000000, 0.00000000, 'active', '2025-10-04 11:09:27', '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(5, 28, 27, 3.00, 0.00000000, 0.00000000, 'active', '2025-09-30 11:09:28', '2025-10-22 11:09:28', '2025-10-22 11:09:28'),
(6, 24, 29, 15.00, 0.00000000, 0.00000000, 'active', '2025-09-22 11:09:28', '2025-10-22 11:09:28', '2025-10-22 11:09:28'),
(7, 29, 26, 8.00, 0.00000000, 0.00000000, 'active', '2025-10-02 11:09:28', '2025-10-22 11:09:28', '2025-10-22 11:09:28'),
(8, 29, 28, 8.00, 0.00000000, 0.00000000, 'active', '2025-10-12 11:09:29', '2025-10-22 11:09:29', '2025-10-22 11:09:29'),
(9, 24, 30, 15.00, 0.00000000, 0.00000000, 'active', '2025-10-09 11:09:30', '2025-10-22 11:09:30', '2025-10-22 11:09:30'),
(10, 30, 26, 8.00, 0.00000000, 0.00000000, 'active', '2025-10-17 11:09:30', '2025-10-22 11:09:30', '2025-10-22 11:09:30'),
(11, 30, 28, 8.00, 0.00000000, 0.00000000, 'active', '2025-09-30 11:09:31', '2025-10-22 11:09:31', '2025-10-22 11:09:31'),
(12, 31, 32, 15.00, 0.00000000, 0.00000000, 'active', '2025-09-09 11:09:32', '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(13, 31, 33, 15.00, 0.00000000, 0.00000000, 'active', '2025-10-04 11:09:32', '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(14, 31, 34, 15.00, 0.00000000, 0.00000000, 'active', '2025-09-17 11:09:32', '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(15, 31, 35, 15.00, 0.00000000, 0.00000000, 'active', '2025-09-05 11:09:33', '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(16, 31, 36, 15.00, 0.00000000, 0.00000000, 'active', '2025-09-17 11:09:33', '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(17, 32, 37, 5.00, 0.00000000, 0.00000000, 'active', '2025-07-29 11:09:33', '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(18, 32, 38, 5.00, 0.00000000, 0.00000000, 'active', '2025-08-05 11:09:34', '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(19, 32, 39, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-20 11:09:34', '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(20, 32, 40, 5.00, 0.00000000, 0.00000000, 'active', '2025-09-26 11:09:34', '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(21, 33, 41, 5.00, 0.00000000, 0.00000000, 'active', '2025-09-19 11:09:35', '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(22, 33, 42, 5.00, 0.00000000, 0.00000000, 'active', '2025-07-25 11:09:35', '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(23, 33, 43, 5.00, 0.00000000, 0.00000000, 'active', '2025-07-25 11:09:35', '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(24, 34, 44, 5.00, 0.00000000, 0.00000000, 'active', '2025-08-16 11:09:36', '2025-10-22 11:09:36', '2025-10-22 11:09:36'),
(25, 34, 45, 5.00, 0.00000000, 0.00000000, 'active', '2025-08-30 11:09:36', '2025-10-22 11:09:36', '2025-10-22 11:09:36'),
(26, 34, 46, 5.00, 0.00000000, 0.00000000, 'active', '2025-08-02 11:09:37', '2025-10-22 11:09:37', '2025-10-22 11:09:37'),
(27, 35, 47, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-15 11:09:37', '2025-10-22 11:09:37', '2025-10-22 11:09:37'),
(28, 35, 48, 5.00, 0.00000000, 0.00000000, 'active', '2025-07-24 11:09:37', '2025-10-22 11:09:37', '2025-10-22 11:09:37'),
(29, 35, 49, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-21 11:09:38', '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(30, 35, 50, 5.00, 0.00000000, 0.00000000, 'active', '2025-08-30 11:09:38', '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(31, 36, 51, 5.00, 0.00000000, 0.00000000, 'active', '2025-09-06 11:09:38', '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(32, 36, 52, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-21 11:09:39', '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(33, 36, 53, 5.00, 0.00000000, 0.00000000, 'active', '2025-08-17 11:09:39', '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(34, 36, 54, 5.00, 0.00000000, 0.00000000, 'active', '2025-08-12 11:09:39', '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(35, 37, 55, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-30 11:09:40', '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(36, 37, 56, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-04 11:09:40', '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(37, 38, 57, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-17 11:09:40', '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(38, 38, 58, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-26 11:09:41', '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(39, 38, 59, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-23 11:09:41', '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(40, 39, 60, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-26 11:09:41', '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(41, 39, 61, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-15 11:09:42', '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(42, 39, 62, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-06 11:09:42', '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(43, 40, 63, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-12 11:09:42', '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(44, 40, 64, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-25 11:09:43', '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(45, 40, 65, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-25 11:09:43', '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(46, 41, 66, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-06 11:09:43', '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(47, 41, 67, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-29 11:09:44', '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(48, 42, 68, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-20 11:09:44', '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(49, 42, 69, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-13 11:09:44', '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(50, 42, 70, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-31 11:09:45', '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(51, 43, 71, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-14 11:09:45', '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(52, 43, 72, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-11 11:09:45', '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(53, 44, 73, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-09 11:09:46', '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(54, 44, 74, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-16 11:09:46', '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(55, 45, 75, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-21 11:09:46', '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(56, 45, 76, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-31 11:09:47', '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(57, 46, 77, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-08 11:09:47', '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(58, 46, 78, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-13 11:09:47', '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(59, 47, 79, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-26 11:09:48', '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(60, 47, 80, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-24 11:09:48', '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(61, 47, 81, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-14 11:09:48', '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(62, 48, 82, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-26 11:09:49', '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(63, 48, 83, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-15 11:09:49', '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(64, 49, 84, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-01 11:09:49', '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(65, 49, 85, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-07 11:09:50', '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(66, 50, 86, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-01 11:09:50', '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(67, 50, 87, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-23 11:09:50', '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(68, 51, 88, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-02 11:09:51', '2025-10-22 11:09:51', '2025-10-22 11:09:51'),
(69, 51, 89, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-26 11:09:51', '2025-10-22 11:09:51', '2025-10-22 11:09:51'),
(70, 51, 90, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-24 11:09:52', '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(71, 52, 91, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-27 11:09:52', '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(72, 52, 92, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-24 11:09:52', '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(73, 53, 93, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-05 11:09:53', '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(74, 53, 94, 2.00, 0.00000000, 0.00000000, 'active', '2025-07-28 11:09:53', '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(75, 54, 95, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-18 11:09:53', '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(76, 54, 96, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-09 11:09:54', '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(77, 54, 97, 2.00, 0.00000000, 0.00000000, 'active', '2025-07-24 11:09:54', '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(78, 55, 98, 0.00, 0.00000000, 0.00000000, 'active', '2025-08-27 11:09:54', '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(79, 56, 99, 0.00, 0.00000000, 0.00000000, 'active', '2025-08-15 11:09:55', '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(80, 56, 100, 0.00, 0.00000000, 0.00000000, 'active', '2025-10-11 11:09:55', '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(81, 57, 101, 0.00, 0.00000000, 0.00000000, 'active', '2025-09-07 11:09:55', '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(82, 57, 102, 0.00, 0.00000000, 0.00000000, 'active', '2025-07-29 11:09:56', '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(83, 58, 103, 0.00, 0.00000000, 0.00000000, 'active', '2025-10-16 11:09:56', '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(84, 59, 104, 0.00, 0.00000000, 0.00000000, 'active', '2025-09-28 11:09:56', '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(85, 60, 105, 0.00, 0.00000000, 0.00000000, 'active', '2025-10-09 11:09:57', '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(86, 60, 106, 0.00, 0.00000000, 0.00000000, 'active', '2025-08-26 11:09:57', '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(87, 61, 107, 0.00, 0.00000000, 0.00000000, 'active', '2025-10-05 11:09:57', '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(88, 62, 108, 0.00, 0.00000000, 0.00000000, 'active', '2025-08-19 11:09:58', '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(89, 63, 109, 0.00, 0.00000000, 0.00000000, 'active', '2025-08-10 11:09:58', '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(90, 63, 110, 0.00, 0.00000000, 0.00000000, 'active', '2025-09-12 11:09:58', '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(91, 64, 111, 0.00, 0.00000000, 0.00000000, 'active', '2025-10-21 11:09:59', '2025-10-22 11:09:59', '2025-10-22 11:09:59'),
(92, 33, 112, 5.00, 0.00000000, 0.00000000, 'active', '2025-08-16 11:12:46', '2025-10-22 11:12:46', '2025-10-22 11:12:46'),
(93, 34, 113, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-21 11:12:48', '2025-10-22 11:12:48', '2025-10-22 11:12:48'),
(94, 43, 114, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-17 11:12:55', '2025-10-22 11:12:55', '2025-10-22 11:12:55'),
(95, 112, 115, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-15 11:12:55', '2025-10-22 11:12:55', '2025-10-22 11:12:55'),
(96, 112, 116, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-16 11:12:56', '2025-10-22 11:12:56', '2025-10-22 11:12:56'),
(97, 112, 117, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-21 11:12:56', '2025-10-22 11:12:56', '2025-10-22 11:12:56'),
(98, 44, 118, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-28 11:12:57', '2025-10-22 11:12:57', '2025-10-22 11:12:57'),
(99, 45, 119, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-02 11:12:58', '2025-10-22 11:12:58', '2025-10-22 11:12:58'),
(100, 46, 120, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-05 11:12:59', '2025-10-22 11:12:59', '2025-10-22 11:12:59'),
(101, 113, 121, 2.00, 0.00000000, 0.00000000, 'active', '2025-10-11 11:12:59', '2025-10-22 11:12:59', '2025-10-22 11:12:59'),
(102, 113, 122, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-30 11:13:00', '2025-10-22 11:13:00', '2025-10-22 11:13:00'),
(103, 48, 123, 2.00, 0.00000000, 0.00000000, 'active', '2025-09-17 11:13:01', '2025-10-22 11:13:01', '2025-10-22 11:13:01'),
(104, 49, 124, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-28 11:13:02', '2025-10-22 11:13:02', '2025-10-22 11:13:02'),
(105, 53, 125, 2.00, 0.00000000, 0.00000000, 'active', '2025-08-20 11:13:05', '2025-10-22 11:13:05', '2025-10-22 11:13:05'),
(106, 58, 126, 0.00, 0.00000000, 0.00000000, 'active', '2025-08-22 11:13:07', '2025-10-22 11:13:07', '2025-10-22 11:13:07'),
(107, 59, 127, 0.00, 0.00000000, 0.00000000, 'active', '2025-09-01 11:13:07', '2025-10-22 11:13:07', '2025-10-22 11:13:07'),
(108, 61, 128, 0.00, 0.00000000, 0.00000000, 'active', '2025-09-11 11:13:08', '2025-10-22 11:13:08', '2025-10-22 11:13:08'),
(109, 64, 129, 0.00, 0.00000000, 0.00000000, 'active', '2025-08-01 11:13:09', '2025-10-22 11:13:09', '2025-10-22 11:13:09'),
(110, 65, 130, 0.00, 0.00000000, 0.00000000, 'active', '2025-10-05 11:13:10', '2025-10-22 11:13:10', '2025-10-22 11:13:10'),
(111, 65, 131, 0.00, 0.00000000, 0.00000000, 'active', '2025-10-10 11:13:10', '2025-10-22 11:13:10', '2025-10-22 11:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `rent_bot_packages`
--

CREATE TABLE `rent_bot_packages` (
  `id` bigint UNSIGNED NOT NULL,
  `allowed_bots` int UNSIGNED NOT NULL,
  `allowed_trades` int UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `validity` enum('month','year') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rent_bot_packages`
--

INSERT INTO `rent_bot_packages` (`id`, `allowed_bots`, `allowed_trades`, `amount`, `validity`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 50, 50.00, 'month', 1, '2025-10-29 12:10:54', '2025-10-29 12:10:54'),
(2, 2, 100, 100.00, 'month', 1, '2025-10-29 12:11:18', '2025-10-29 12:11:18'),
(3, 4, 200, 200.00, 'month', 1, '2025-10-29 12:11:47', '2025-10-29 12:11:47'),
(4, 5, 500, 300.00, 'month', 1, '2025-10-29 12:12:36', '2025-10-29 12:12:36');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07'),
(2, 'customer', 'web', '2025-10-22 10:37:07', '2025-10-22 10:37:07');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0fWOo7qNyYYQI2k6OavPYGdbwfKBr1Qej3ng7WFK', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRHIxc2l6ckN0d2x0cVphSTBLTnp4STJQV3FpaERlU3dwQnpDZjM5SCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fX0=', 1761325170),
('uSrakGUkH0LwfVF6eSWE1gm3xlAZfiQtyoYlrWGD', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoick83ZDBHakhldjdGWDduY0xtZEhFMFRodnhwZ3JxU3lMNTNwS0lLcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9yZW50LWJvdC1wYWNrYWdlcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1761757956);

-- --------------------------------------------------------

--
-- Table structure for table `trades`
--

CREATE TABLE `trades` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `agent_id` bigint UNSIGNED DEFAULT NULL,
  `trade_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `side` enum('buy','sell') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('market','limit','stop') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `price` decimal(20,8) DEFAULT NULL,
  `stop_price` decimal(20,8) DEFAULT NULL,
  `executed_quantity` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `average_price` decimal(20,8) DEFAULT NULL,
  `commission` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `status` enum('pending','partially_filled','filled','cancelled','rejected') COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_in_force` enum('GTC','IOC','FOK') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GTC',
  `profit_loss` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `profit_loss_percentage` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `exchange` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exchange_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('deposit','withdrawal','transfer','trade_profit','trade_loss','commission','refund') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `fee` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `net_amount` decimal(20,8) NOT NULL,
  `from_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmations` int NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_type` enum('customer','admin','manager','moderator') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `referral_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referred_by` bigint UNSIGNED DEFAULT NULL,
  `active_plan_id` bigint UNSIGNED DEFAULT NULL,
  `active_investment_amount` decimal(20,8) NOT NULL DEFAULT '0.00000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `user_type`, `is_active`, `last_login_at`, `referral_code`, `referred_by`, `active_plan_id`, `active_investment_amount`) VALUES
(1, 'Admin User', 'admin@admin.com', NULL, '$2y$12$0t8QTcYGXq12nL2bxo42v.38Zj4Cy9xDYw2J1j8PwGUcyNYTagk9G', NULL, '2025-10-22 10:37:08', '2025-10-29 11:33:40', 'admin', 1, '2025-10-29 11:33:40', 'ADMIN001', NULL, NULL, 0.00000000),
(2, 'John Doe', 'customer@example.com', NULL, '$2y$12$tDwDp68/6WShvNz1m/MD/ecIRw5ncH/1e0p3ujBfITcCgLPEIALHK', NULL, '2025-10-22 10:37:09', '2025-10-22 10:37:09', 'customer', 1, NULL, 'JOHN001', NULL, NULL, 0.00000000),
(3, 'Manager User', 'manager@aitradeapp.com', NULL, '$2y$12$J1D72DewzvZ8YBn39eCri.uXJm.KerYk9fxBlX1UuwynJMrPFjHZO', NULL, '2025-10-22 10:37:09', '2025-10-22 10:37:09', 'manager', 1, NULL, 'MGR001', NULL, NULL, 0.00000000),
(4, 'Alice Johnson', 'alice.johnson@example.com', NULL, '$2y$12$PiAe11DitOCyDgKQaIPnzuG4elpNz68HG8gCo0sLmXyc3L0wNiuU6', NULL, '2025-10-22 11:09:19', '2025-10-22 11:09:19', 'customer', 1, NULL, 'IKP1DDPI', NULL, 1, 2500.00000000),
(5, 'Bob Smith', 'bob.smith@example.com', NULL, '$2y$12$I3LrE1yxeyY92VZl8bJdIeA.9Q0AvLa9Ysjlnp4H5/mQ3.Ymi7dIC', NULL, '2025-10-22 11:09:19', '2025-10-22 11:09:19', 'customer', 1, NULL, '94XGYQHE', NULL, 2, 7500.00000000),
(6, 'Carol Davis', 'carol.davis@example.com', NULL, '$2y$12$tlOZ0Yp6wIOI7xCHCkEcOO1u.4sZMhBPXFZJQA3LNjeROSdBbd9li', NULL, '2025-10-22 11:09:20', '2025-10-22 11:09:20', 'customer', 1, NULL, 'RZXUL0AS', NULL, 1, 1200.00000000),
(7, 'David Wilson', 'david.wilson@example.com', NULL, '$2y$12$8IdaaDejQ/XMl27Urvf57OcgssaveQMVTnhP6iONQb47LAKhNKm.q', NULL, '2025-10-22 11:09:20', '2025-10-22 11:09:20', 'customer', 0, NULL, 'WFQFOTTM', NULL, 1, 500.00000000),
(8, 'Eva Brown', 'eva.brown@example.com', NULL, '$2y$12$V5TTMr56pItXYWjts93gmOra2lv8RLMvTFhvLgFpA4VSgY4KqQS7C', NULL, '2025-10-22 11:09:20', '2025-10-22 11:09:20', 'customer', 0, NULL, 'OF7IENU1', NULL, 1, 800.00000000),
(9, 'Frank Miller', 'frank.miller@example.com', NULL, '$2y$12$YQeYxoZ/yTBhB3BvsdyCf.ZLcTEQsE21615cIp8UmFqbXvErQMfEK', NULL, '2025-10-22 11:09:21', '2025-10-22 11:09:21', 'customer', 1, NULL, 'XO9OHK09', NULL, 1, 1000.00000000),
(10, 'Grace Lee', 'grace.lee@example.com', NULL, '$2y$12$.75kw8aSs1l.Co011k1tFOHFqWS8j8T2DLiUrO/da8AKQHpDq/suC', NULL, '2025-10-22 11:09:21', '2025-10-22 11:09:21', 'customer', 1, NULL, 'WIFYFB2I', NULL, 1, 500.00000000),
(11, 'Henry Taylor', 'henry.taylor@example.com', NULL, '$2y$12$J59gmCtoFcfTr5ydWBE23uaDYsSCHo9wud9PxRYCcXbUJdzurrYae', NULL, '2025-10-22 11:09:22', '2025-10-22 11:09:22', 'customer', 1, NULL, 'C7IIHWQZ', NULL, 2, 5000.00000000),
(12, 'Customer_100_1', 'customer_100_1@example.com', NULL, '$2y$12$iAv1wCMWVTGlyk3nezn2ZOArHLeY0g9tzLmZaVySxvgkAQf8X4mM6', NULL, '2025-10-22 11:09:22', '2025-10-22 11:09:22', 'customer', 1, NULL, '8T520Z5R', NULL, 1, 182.00000000),
(13, 'Customer_100_2', 'customer_100_2@example.com', NULL, '$2y$12$jKZhS0NzRYm/96QaVCMlWewf6i8hznbyJ1dIDZZx/p9YzNSRpqO1i', NULL, '2025-10-22 11:09:22', '2025-10-22 11:09:22', 'customer', 1, NULL, 'ATSRCEXQ', NULL, 1, 283.00000000),
(14, 'Customer_100_3', 'customer_100_3@example.com', NULL, '$2y$12$Vq96H3h/3ojblTGLRW3TSOkApoemu5K2YFOR2YcvlHtzawqo2MFme', NULL, '2025-10-22 11:09:23', '2025-10-22 11:09:23', 'customer', 1, NULL, 'NPQGOMWU', NULL, 1, 349.00000000),
(15, 'Customer_100_4', 'customer_100_4@example.com', NULL, '$2y$12$xEHS3ptTtxNutiEWGjhQ3.2QfpWDkUnZ0NLfr7rPPnjv6K9RzIveG', NULL, '2025-10-22 11:09:23', '2025-10-22 11:09:23', 'customer', 1, NULL, 'WAJWQ3PO', NULL, 1, 441.00000000),
(16, 'Customer_100_5', 'customer_100_5@example.com', NULL, '$2y$12$JpVRNvhrDurI7nYTK.qzn.dsYjORaPEh7tDSF7rZf7zreDzD2plzS', NULL, '2025-10-22 11:09:23', '2025-10-22 11:09:23', 'customer', 1, NULL, 'OKRUISYJ', NULL, 1, 457.00000000),
(17, 'Customer_1000_1', 'customer_1000_1@example.com', NULL, '$2y$12$QFe4a8xn/UlL5bg.bFFxjOykSWGcrNzfa4OIZAijXi/Ad145r2psG', NULL, '2025-10-22 11:09:24', '2025-10-22 11:09:24', 'customer', 1, NULL, 'ZOITP9JP', NULL, 1, 1003.00000000),
(18, 'Customer_1000_2', 'customer_1000_2@example.com', NULL, '$2y$12$n1h/JxPgEobg5IG4MBZvHOkI/NmT0km80Th9M/o/5BY1UDxqWowfu', NULL, '2025-10-22 11:09:24', '2025-10-22 11:09:24', 'customer', 1, NULL, 'RLLGVPE8', NULL, 1, 1588.00000000),
(19, 'Customer_1000_3', 'customer_1000_3@example.com', NULL, '$2y$12$OaKoTz9OvtE2Yy0ho2u2ceMyLAwMkdPUC0QQG2ZRhdEoe2jXl3qZS', NULL, '2025-10-22 11:09:24', '2025-10-22 11:09:24', 'customer', 1, NULL, 'I1FTWJLD', NULL, 1, 1644.00000000),
(20, 'Customer_3000_1', 'customer_3000_1@example.com', NULL, '$2y$12$CqaCgcEpCjDyPKduYu0eCuKAWwBPqId9eqzrKWystQQV./9rRB3FG', NULL, '2025-10-22 11:09:25', '2025-10-22 11:09:25', 'customer', 1, NULL, '14CI0CO6', NULL, 2, 4752.00000000),
(21, 'Customer_3000_2', 'customer_3000_2@example.com', NULL, '$2y$12$dIvVoy4LiYPMjr3G1M7biOWYLswO/dgNkCSsHFjhPvsTVLSutnUSa', NULL, '2025-10-22 11:09:25', '2025-10-22 11:09:25', 'customer', 1, NULL, 'JECHCI3Z', NULL, 2, 3913.00000000),
(22, 'Customer_6000_1', 'customer_6000_1@example.com', NULL, '$2y$12$iU2sbcMyIzL5MpIC0CljteiCWp34/fpCIj.lDpFCS1tM0CuNvpFbm', NULL, '2025-10-22 11:09:25', '2025-10-22 11:09:25', 'customer', 1, NULL, '12ZI9A3B', NULL, 2, 6644.00000000),
(23, 'Customer_6000_2', 'customer_6000_2@example.com', NULL, '$2y$12$2QUmxOjEU2p5txD5RA.La./4JKnpd2.EF8QRp6l8uDkIUcPafkS.O', NULL, '2025-10-22 11:09:26', '2025-10-22 11:09:26', 'customer', 1, NULL, 'S7TBIX9M', NULL, 2, 7783.00000000),
(24, 'Main Referrer', 'main@test.com', NULL, '$2y$12$0t8QTcYGXq12nL2bxo42v.38Zj4Cy9xDYw2J1j8PwGUcyNYTagk9G', '8fRGQD7Ss4RF9xHnGkro5DDAyrFWyTpkmhMGpBvZSgBVm3rmIkHxE2tUdhhe', '2025-10-22 11:09:26', '2025-10-29 10:57:25', 'customer', 1, '2025-10-29 10:57:25', 'SRP1CCQP', NULL, 2, 5000.00000000),
(25, 'Level 1 User 1', 'level1_1@test.com', NULL, '$2y$12$pRV6WQOt7RiguV0h9EvlYORxuzcqXE65.cATOshAgCmuq3w9RCub2', NULL, '2025-10-22 11:09:26', '2025-10-22 11:09:26', 'customer', 1, NULL, '2O9EQWGX', 24, 2, 5000.00000000),
(26, 'Level 2 User 1', 'level2_1@test.com', NULL, '$2y$12$2AparpnFIfEO/ngNLaomFuR2oKsrfsI50gHpEeo3hVDRqArx0zXxm', NULL, '2025-10-22 11:09:27', '2025-10-22 11:09:27', 'customer', 1, NULL, 'IXXXUNB4', 25, 2, 3000.00000000),
(27, 'Level 3 User 1', 'level3_1@test.com', NULL, '$2y$12$X0tlN9ZfMcDL.g37086JtuqxQo2gPgP0IPIYzzk2/1UavgwkBQVHm', NULL, '2025-10-22 11:09:27', '2025-10-22 11:09:27', 'customer', 1, NULL, 'LW70UVQV', 26, 1, 1500.00000000),
(28, 'Level 2 User 2', 'level2_2@test.com', NULL, '$2y$12$WvJ1wHGEDSt1ViSqOcQmCu3pAVhZjzAtVSCgVFvTJjxrYMcuV2eZO', NULL, '2025-10-22 11:09:27', '2025-10-22 11:09:27', 'customer', 1, NULL, 'BPSEF3TH', 25, 2, 3000.00000000),
(29, 'Level 1 User 2', 'level1_2@test.com', NULL, '$2y$12$h1Pqb5BaOJNXK5SXCbUud.uxtuXcGLep3Q4uyyW7TyuCfwc8WNc6u', NULL, '2025-10-22 11:09:28', '2025-10-22 11:09:28', 'customer', 1, NULL, '9TNNNQMW', 24, 2, 5000.00000000),
(30, 'Level 1 User 3', 'level1_3@test.com', NULL, '$2y$12$9Aqo4N91E1TNDieiA4HyduJhYTxQYdKnnciSmRyFxHF9PDwi76VZW', NULL, '2025-10-22 11:09:30', '2025-10-22 11:09:30', 'customer', 1, NULL, 'UO5VTJTB', 24, 2, 5000.00000000),
(31, 'VIP Referrer', 'vip.referrer@test.com', NULL, '$2y$12$i4OQIV1WOHRuQj2XewVY1.HOECLVYSjO6e1/QLoNxs0laA6L6dVtq', NULL, '2025-10-22 11:09:31', '2025-10-22 11:09:31', 'customer', 1, NULL, 'ASTG9WAD', NULL, 4, 10000.00000000),
(32, 'Level 1 User 1', 'level1_31_1@test.com', NULL, '$2y$12$GT1h8/RUxR230CfNeTMjkeLlRhap/5Ye1rzww4PnkQsLNmwBBZ0Yu', NULL, '2025-10-22 11:09:32', '2025-10-22 11:09:32', 'customer', 1, NULL, 'SLFYYCLP', 31, 4, 6642.00000000),
(33, 'Level 1 User 2', 'level1_31_2@test.com', NULL, '$2y$12$i/LY.3VcAXxPv/oiv8Wp2uBXegnKQvAH.UUI04IVPSP1nuMUG4ILa', NULL, '2025-10-22 11:09:32', '2025-10-22 11:09:32', 'customer', 1, NULL, 'PSPX53S7', 31, 4, 6881.00000000),
(34, 'Level 1 User 3', 'level1_31_3@test.com', NULL, '$2y$12$VdkWoD8ce/UduNQbcGsQGuWZzkqUI/D3GbUu4MAB9XtfkLIikrqZq', NULL, '2025-10-22 11:09:32', '2025-10-22 11:09:32', 'customer', 1, NULL, 'A9BWILWY', 31, 4, 6427.00000000),
(35, 'Level 1 User 4', 'level1_31_4@test.com', NULL, '$2y$12$OgzJcv92zYEQXJuRA3VPOu0KD7zPfergJ2zMNvKCR2JnoFvsP47hS', NULL, '2025-10-22 11:09:33', '2025-10-22 11:09:33', 'customer', 1, NULL, 'MZVYFCUX', 31, 4, 5520.00000000),
(36, 'Level 1 User 5', 'level1_31_5@test.com', NULL, '$2y$12$c8y55OOTfqZGO9TDUJyE5usLbvZeV3yjLpGiAe.PGSRA4wdqkt6Pe', NULL, '2025-10-22 11:09:33', '2025-10-22 11:09:33', 'customer', 1, NULL, 'WGS6UZGH', 31, 4, 5244.00000000),
(37, 'Level 2 User 1', 'level2_32_1@test.com', NULL, '$2y$12$bVqItP94hHdOZDLUrket.eusFmZvifF8GtBMsZYEeFXuj4YRMquLe', NULL, '2025-10-22 11:09:33', '2025-10-22 11:09:33', 'customer', 1, NULL, 'JPQAXHIN', 32, 3, 1742.00000000),
(38, 'Level 2 User 2', 'level2_32_2@test.com', NULL, '$2y$12$KXC7FhH5.JF2shqQ46QzAenl9TLRjsvzd/RPgRDV6zZ4ZLZWXffP2', NULL, '2025-10-22 11:09:34', '2025-10-22 11:09:34', 'customer', 1, NULL, 'Y0WFIVLC', 32, 3, 2943.00000000),
(39, 'Level 2 User 3', 'level2_32_3@test.com', NULL, '$2y$12$ldoNa/4Nflr958IlCG3dO.ZMBHXEIZg89q/GAEM8wU/m8qnyIB2Uu', NULL, '2025-10-22 11:09:34', '2025-10-22 11:09:34', 'customer', 1, NULL, 'JJDKYBQW', 32, 3, 2244.00000000),
(40, 'Level 2 User 4', 'level2_32_4@test.com', NULL, '$2y$12$.123wwcMl8OY2DcZQOj0yO46riFZJ45AJLKHYRjN0QYLKFdcsK4Ku', NULL, '2025-10-22 11:09:34', '2025-10-22 11:09:34', 'customer', 1, NULL, 'DZTLYRPZ', 32, 3, 1680.00000000),
(41, 'Level 2 User 1', 'level2_33_1@test.com', NULL, '$2y$12$13MNU3NKujGnQKyM5Lw.yutuqga8eAcEKKN6qqzc8yd6GI0Sd...a', NULL, '2025-10-22 11:09:35', '2025-10-22 11:09:35', 'customer', 1, NULL, 'H4NUWVSQ', 33, 3, 2942.00000000),
(42, 'Level 2 User 2', 'level2_33_2@test.com', NULL, '$2y$12$PvRaM/eLgFbskaoRpT3yAuWGNpjH3bTCfWJ/XAiHvZmwj33nkPpRK', NULL, '2025-10-22 11:09:35', '2025-10-22 11:09:35', 'customer', 1, NULL, '9RQVQGEK', 33, 3, 2948.00000000),
(43, 'Level 2 User 3', 'level2_33_3@test.com', NULL, '$2y$12$kopFOG/iGxFtOBtNwTpNIupvKRugL4o89Fwgbj3L18smmbrmePnqq', NULL, '2025-10-22 11:09:35', '2025-10-22 11:09:35', 'customer', 1, NULL, 'NOP89YES', 33, 3, 2143.00000000),
(44, 'Level 2 User 1', 'level2_34_1@test.com', NULL, '$2y$12$HY4xDqhfFDoclT6LeUyIOuyV3PfRpL7guQiae9EwxT2tcFTdUJNj6', NULL, '2025-10-22 11:09:36', '2025-10-22 11:09:36', 'customer', 1, NULL, 'ROX9CBS2', 34, 3, 1733.00000000),
(45, 'Level 2 User 2', 'level2_34_2@test.com', NULL, '$2y$12$RJGMgzSqlfpdSbQmGMPLnuW4ghvP2EkDnM8Cpdy4dYc2UepxlIpnG', NULL, '2025-10-22 11:09:36', '2025-10-22 11:09:36', 'customer', 1, NULL, 'REDU95SD', 34, 3, 1813.00000000),
(46, 'Level 2 User 3', 'level2_34_3@test.com', NULL, '$2y$12$6jSHAu7E5cx8KHcX1xTAUOAFZ3.8O91NSMBGFLg9EitvtCNs061X.', NULL, '2025-10-22 11:09:36', '2025-10-22 11:09:36', 'customer', 1, NULL, 'YVHYEMNG', 34, 3, 1814.00000000),
(47, 'Level 2 User 1', 'level2_35_1@test.com', NULL, '$2y$12$7lQ6y8hpx.jXfZSCTmivQOdlARuprndggYqWjvIVwngeSukjvexs2', NULL, '2025-10-22 11:09:37', '2025-10-22 11:09:37', 'customer', 1, NULL, 'H3DH4FA1', 35, 3, 1329.00000000),
(48, 'Level 2 User 2', 'level2_35_2@test.com', NULL, '$2y$12$5tqo5FS3NbDqJ9VV6WqZaOd2Kb2lEzrxlrA/g/GbsC.ifAAer.5i6', NULL, '2025-10-22 11:09:37', '2025-10-22 11:09:37', 'customer', 1, NULL, 'A8RQ6QZU', 35, 3, 2416.00000000),
(49, 'Level 2 User 3', 'level2_35_3@test.com', NULL, '$2y$12$1LqDaUu9UQ1MtQ8NlXiVYOy/VifWkwPaP8S/Y0k0jFpRgnfxC3zSG', NULL, '2025-10-22 11:09:37', '2025-10-22 11:09:37', 'customer', 1, NULL, 'HBPLUWD8', 35, 3, 1360.00000000),
(50, 'Level 2 User 4', 'level2_35_4@test.com', NULL, '$2y$12$E8lLnem2zFHPrgEU9UN57.GMFzHe6hf/H49u4Tl1Bdh5YcA6StJxq', NULL, '2025-10-22 11:09:38', '2025-10-22 11:09:38', 'customer', 1, NULL, 'DXKOIPRQ', 35, 3, 1109.00000000),
(51, 'Level 2 User 1', 'level2_36_1@test.com', NULL, '$2y$12$w0I7BGHRHdPg/i4xIj1kBuOlpg/RTDXviabYvHgC65nnnVX5aFlFO', NULL, '2025-10-22 11:09:38', '2025-10-22 11:09:38', 'customer', 1, NULL, 'IENAS3F2', 36, 3, 2578.00000000),
(52, 'Level 2 User 2', 'level2_36_2@test.com', NULL, '$2y$12$HHWlD3uCMmraongDKK8vUOt9OoeowFQugcnp0VWJJuHzSijtn2jjK', NULL, '2025-10-22 11:09:39', '2025-10-22 11:09:39', 'customer', 1, NULL, '9HFJOKT2', 36, 3, 2605.00000000),
(53, 'Level 2 User 3', 'level2_36_3@test.com', NULL, '$2y$12$e9R12asDGng2iNhTxOFnQetBRfdCeRCFR0toQRPH8HxrwZndqPmIm', NULL, '2025-10-22 11:09:39', '2025-10-22 11:09:39', 'customer', 1, NULL, '1SFBWUNB', 36, 3, 1480.00000000),
(54, 'Level 2 User 4', 'level2_36_4@test.com', NULL, '$2y$12$z.BQiR.0xPA/CPUQTDx6S.TU7mKtxzz8a7Xj2jxkrQbOejzExpaym', NULL, '2025-10-22 11:09:39', '2025-10-22 11:09:39', 'customer', 1, NULL, '2XLPBUPE', 36, 3, 2416.00000000),
(55, 'Level 3 User 1', 'level3_37_1@test.com', NULL, '$2y$12$D5.lsLyH8ZbXzPI.1arcEOyfeswRvErZi4R35Hp8BTN56es/yS4dy', NULL, '2025-10-22 11:09:40', '2025-10-22 11:09:40', 'customer', 1, NULL, 'YKR2PNUQ', 37, 3, 826.00000000),
(56, 'Level 3 User 2', 'level3_37_2@test.com', NULL, '$2y$12$ZkOY2t8vdSK6EwX8LSge1uPcMlPW2mOm./nXJvaydbYDAvkPH1ep6', NULL, '2025-10-22 11:09:40', '2025-10-22 11:09:40', 'customer', 1, NULL, 'STCGXVHH', 37, 3, 1254.00000000),
(57, 'Level 3 User 1', 'level3_38_1@test.com', NULL, '$2y$12$QDXl3gcvoPpOVndBUOovZ.yWyYdYIu1J2dgaFJv8jktP77I9E/bHS', NULL, '2025-10-22 11:09:40', '2025-10-22 11:09:40', 'customer', 1, NULL, 'BKDH9NOB', 38, 3, 510.00000000),
(58, 'Level 3 User 2', 'level3_38_2@test.com', NULL, '$2y$12$EP08sE2ssP9AoLGgkHYpHO7Yq21PnBVuD0u/wPABQy9kzG4.p2ox6', NULL, '2025-10-22 11:09:41', '2025-10-22 11:09:41', 'customer', 1, NULL, 'TXNNQ1SS', 38, 3, 992.00000000),
(59, 'Level 3 User 3', 'level3_38_3@test.com', NULL, '$2y$12$e20ERMGHshYqyZPiqJIFPuftbSvueJAj22fOhaQC2AkMAV.weZcK6', NULL, '2025-10-22 11:09:41', '2025-10-22 11:09:41', 'customer', 1, NULL, 'S135XCLE', 38, 3, 766.00000000),
(60, 'Level 3 User 1', 'level3_39_1@test.com', NULL, '$2y$12$R.2o.pikmHdsYRJ4Xopy0eSsMwFZnovogAw0Bx2JaugMNgTLT4dfG', NULL, '2025-10-22 11:09:41', '2025-10-22 11:09:41', 'customer', 1, NULL, 'OBASMTSK', 39, 3, 581.00000000),
(61, 'Level 3 User 2', 'level3_39_2@test.com', NULL, '$2y$12$6OXDX6Qb3RlCNh7/3RU6huc0Q00xgWA3kzW5M08brHl5Nsi2wOeDS', NULL, '2025-10-22 11:09:42', '2025-10-22 11:09:42', 'customer', 1, NULL, 'DVVKSDWJ', 39, 3, 1139.00000000),
(62, 'Level 3 User 3', 'level3_39_3@test.com', NULL, '$2y$12$Yytt7A6peHBEiPKPNYu6hOeyiCuJFFhoCDSLr55Urzb03RpVg4UBi', NULL, '2025-10-22 11:09:42', '2025-10-22 11:09:42', 'customer', 1, NULL, 'ISGDXOCN', 39, 3, 848.00000000),
(63, 'Level 3 User 1', 'level3_40_1@test.com', NULL, '$2y$12$UDPgb/3MeRDfbCsWeNUtVePLl1untxCdiQ/3dakrDUrM5OVXKMuJq', NULL, '2025-10-22 11:09:42', '2025-10-22 11:09:42', 'customer', 1, NULL, '1D8I05PA', 40, 3, 731.00000000),
(64, 'Level 3 User 2', 'level3_40_2@test.com', NULL, '$2y$12$Q23T8VO7H0F3Yj8B9KOC3O9Jksh0zLbjR68XGsde4C5IxN.m7hdtO', NULL, '2025-10-22 11:09:43', '2025-10-22 11:09:43', 'customer', 1, NULL, 'B1PTR6IF', 40, 3, 1194.00000000),
(65, 'Level 3 User 3', 'level3_40_3@test.com', NULL, '$2y$12$ljtlQtzy7EBSdumygtWEaunx.gv6Ahz94urqNb3nueqVh5KrsPXPy', NULL, '2025-10-22 11:09:43', '2025-10-22 11:09:43', 'customer', 1, NULL, 'XS0HQVIL', 40, 3, 924.00000000),
(66, 'Level 3 User 1', 'level3_41_1@test.com', NULL, '$2y$12$QLTtYYQz46DjTejeIhkovuYNvG1bdcv4vXMAJ3weKGuFr4ACUaCea', NULL, '2025-10-22 11:09:43', '2025-10-22 11:09:43', 'customer', 1, NULL, 'JKXODUKS', 41, 3, 991.00000000),
(67, 'Level 3 User 2', 'level3_41_2@test.com', NULL, '$2y$12$X5xnB0w8MQOzIFrAkNjNouuIMVpIkK29o5wTqRc5/8BI5K8MgBwhq', NULL, '2025-10-22 11:09:44', '2025-10-22 11:09:44', 'customer', 1, NULL, '07MGAHHB', 41, 3, 599.00000000),
(68, 'Level 3 User 1', 'level3_42_1@test.com', NULL, '$2y$12$NdUfXBXnvbrgKgBX1aigmO5q0K7C7gvXcSk.bCczLeGa5/EiqhEs.', NULL, '2025-10-22 11:09:44', '2025-10-22 11:09:44', 'customer', 1, NULL, 'EILF56KW', 42, 3, 1438.00000000),
(69, 'Level 3 User 2', 'level3_42_2@test.com', NULL, '$2y$12$NA3REa3rbf6eSVTQONMvuOWo54JTN5upcEoBGB02Ue2DdU2d3T4Vm', NULL, '2025-10-22 11:09:44', '2025-10-22 11:09:44', 'customer', 1, NULL, 'SUY4VIBK', 42, 3, 1429.00000000),
(70, 'Level 3 User 3', 'level3_42_3@test.com', NULL, '$2y$12$MVXZtDDx0kJJWAUU82CfN.1DVsDMKvsS6AQCzCFeFHWjrShiYne9C', NULL, '2025-10-22 11:09:45', '2025-10-22 11:09:45', 'customer', 1, NULL, 'LISDYQS6', 42, 3, 550.00000000),
(71, 'Level 3 User 1', 'level3_43_1@test.com', NULL, '$2y$12$CZTA1Q.YrC6kFftXaSRPeeAV8Ql.Tn94o9UU9V21uQr/b4AoQSFrq', NULL, '2025-10-22 11:09:45', '2025-10-22 11:09:45', 'customer', 1, NULL, 'DC9ECSHQ', 43, 3, 1297.00000000),
(72, 'Level 3 User 2', 'level3_43_2@test.com', NULL, '$2y$12$nSf.G4gVPclmFJh4HhRoH.QtThK8f.aJQJgWvBpp6kG0F8ftycPSy', NULL, '2025-10-22 11:09:45', '2025-10-22 11:09:45', 'customer', 1, NULL, 'SHAHPNER', 43, 3, 993.00000000),
(73, 'Level 3 User 1', 'level3_44_1@test.com', NULL, '$2y$12$fxtMUHIVk833gTwRKqTwQOcM5i9LvOLRoIiC.Z/MC710zrvG.XB.G', NULL, '2025-10-22 11:09:46', '2025-10-22 11:09:46', 'customer', 1, NULL, 'QBRHPJFJ', 44, 3, 1310.00000000),
(74, 'Level 3 User 2', 'level3_44_2@test.com', NULL, '$2y$12$rLCdxQUvl/K4jXp.ejJZsOx.AlYkx/Dyd6WURzWp3UpzcfhQWb5mO', NULL, '2025-10-22 11:09:46', '2025-10-22 11:09:46', 'customer', 1, NULL, 'T8A6TNHZ', 44, 3, 1415.00000000),
(75, 'Level 3 User 1', 'level3_45_1@test.com', NULL, '$2y$12$7qimcZMu5BmRLSzPB0L2Yey9FsDftcLy.GtjwOjXywxj0vEdmm7Ky', NULL, '2025-10-22 11:09:46', '2025-10-22 11:09:46', 'customer', 1, NULL, 'YQYDB6OR', 45, 3, 1307.00000000),
(76, 'Level 3 User 2', 'level3_45_2@test.com', NULL, '$2y$12$Xz.XOh7DDIbh5jZ5hkl8uezwtKHTuFFRz4O6hEuV5y8QJYv.LPBSq', NULL, '2025-10-22 11:09:47', '2025-10-22 11:09:47', 'customer', 1, NULL, 'XNGCUFAZ', 45, 3, 1388.00000000),
(77, 'Level 3 User 1', 'level3_46_1@test.com', NULL, '$2y$12$/m3w1m.RNOII3Slk8fQtpuE77RdrnSHtEWGEmWg4xwPt4d2DsyVhG', NULL, '2025-10-22 11:09:47', '2025-10-22 11:09:47', 'customer', 1, NULL, 'QH0I6DNB', 46, 3, 1445.00000000),
(78, 'Level 3 User 2', 'level3_46_2@test.com', NULL, '$2y$12$XzYU9UakSKZjc5uvHKceBOPvX9RQC4kezbJweTD.EhTFGpJznIDSi', NULL, '2025-10-22 11:09:47', '2025-10-22 11:09:47', 'customer', 1, NULL, 'OSIWPZEB', 46, 3, 557.00000000),
(79, 'Level 3 User 1', 'level3_47_1@test.com', NULL, '$2y$12$pfGDlGzsnzfceBBcrhCzYed6poIzPSOY48Uv0kmVqJM7xlCVezTq.', NULL, '2025-10-22 11:09:48', '2025-10-22 11:09:48', 'customer', 1, NULL, 'RH2ECQUT', 47, 3, 739.00000000),
(80, 'Level 3 User 2', 'level3_47_2@test.com', NULL, '$2y$12$S1wxpYDyYDxqmKRNAvmk3uknHMoSizM5xN8Xs0.s5SiKscsUUhLsq', NULL, '2025-10-22 11:09:48', '2025-10-22 11:09:48', 'customer', 1, NULL, 'SLZ8KABU', 47, 3, 988.00000000),
(81, 'Level 3 User 3', 'level3_47_3@test.com', NULL, '$2y$12$RtkSm4zdEIx9fkayap1l9e7jFHJVnoQhzuG0K7QgSpq8yjynH22Aq', NULL, '2025-10-22 11:09:48', '2025-10-22 11:09:48', 'customer', 1, NULL, 'FNOVPRXF', 47, 3, 1351.00000000),
(82, 'Level 3 User 1', 'level3_48_1@test.com', NULL, '$2y$12$u3.kDve7mzuoSEMeJ8id1uqd0S6NTQWp02r63fA0mJTBaFEKTZLSK', NULL, '2025-10-22 11:09:49', '2025-10-22 11:09:49', 'customer', 1, NULL, 'DIPCLUXQ', 48, 3, 642.00000000),
(83, 'Level 3 User 2', 'level3_48_2@test.com', NULL, '$2y$12$plDVHJbNdOnILtjejBU31.c3g8fmJMuC.gedjyF3SyXnBX9gSenwy', NULL, '2025-10-22 11:09:49', '2025-10-22 11:09:49', 'customer', 1, NULL, 'Q7R6RSNT', 48, 3, 1133.00000000),
(84, 'Level 3 User 1', 'level3_49_1@test.com', NULL, '$2y$12$oO0hpaxALz.2qqRNeavMKeoKoa8WC0vEsqBKJhlXzS1aDbgCGYhfK', NULL, '2025-10-22 11:09:49', '2025-10-22 11:09:49', 'customer', 1, NULL, 'FDCITAVJ', 49, 3, 988.00000000),
(85, 'Level 3 User 2', 'level3_49_2@test.com', NULL, '$2y$12$RbJxJynOCXjDs1IEZd.7Du2.fQf7cQftaauNq.AC/vjgtca3Gv2QG', NULL, '2025-10-22 11:09:50', '2025-10-22 11:09:50', 'customer', 1, NULL, 'IRQWU17S', 49, 3, 1249.00000000),
(86, 'Level 3 User 1', 'level3_50_1@test.com', NULL, '$2y$12$wK9GpsoR9QdYFUd583p0Juyy84ly977VeNua7.fibDvBdW5upKmQC', NULL, '2025-10-22 11:09:50', '2025-10-22 11:09:50', 'customer', 1, NULL, 'SUOAWVT8', 50, 3, 1251.00000000),
(87, 'Level 3 User 2', 'level3_50_2@test.com', NULL, '$2y$12$DggXYZPFbJ7yrktoIpUcze0wu3iOOM3n1Jzd2gHFHBGw5NrtHm.G.', NULL, '2025-10-22 11:09:50', '2025-10-22 11:09:50', 'customer', 1, NULL, 'QEVLMOO0', 50, 3, 829.00000000),
(88, 'Level 3 User 1', 'level3_51_1@test.com', NULL, '$2y$12$IcQ24ZFeb.IGdOtvjr2l8.M5yimk7EuRW8ipL5ewvGENTCzDqMgZS', NULL, '2025-10-22 11:09:51', '2025-10-22 11:09:51', 'customer', 1, NULL, 'CLE7NJWD', 51, 3, 1017.00000000),
(89, 'Level 3 User 2', 'level3_51_2@test.com', NULL, '$2y$12$LsJidH.OZNCupuhGUywACedIo0BO81Mrn42EpXhn4scFXfq.C6U8q', NULL, '2025-10-22 11:09:51', '2025-10-22 11:09:51', 'customer', 1, NULL, 'REJ95HKO', 51, 3, 692.00000000),
(90, 'Level 3 User 3', 'level3_51_3@test.com', NULL, '$2y$12$JH15JDU3TdxcFBbMToaeFePsCESEswkee2HwovosH0Tdnkk2gT0Re', NULL, '2025-10-22 11:09:52', '2025-10-22 11:09:52', 'customer', 1, NULL, 'SMAWMZGV', 51, 3, 1495.00000000),
(91, 'Level 3 User 1', 'level3_52_1@test.com', NULL, '$2y$12$V8.TnuJjHthe5W4FoMJGYeoH/62MmvzhE4eP9FoapS/grqzYJgBRC', NULL, '2025-10-22 11:09:52', '2025-10-22 11:09:52', 'customer', 1, NULL, 'UDCNOIN8', 52, 3, 943.00000000),
(92, 'Level 3 User 2', 'level3_52_2@test.com', NULL, '$2y$12$OiN8vHk7FEYHzFKjMQNPZe28o1urbaaBn.GuNlw6PIJ05PabA.ySK', NULL, '2025-10-22 11:09:52', '2025-10-22 11:09:52', 'customer', 1, NULL, 'ZSQDNNLS', 52, 3, 681.00000000),
(93, 'Level 3 User 1', 'level3_53_1@test.com', NULL, '$2y$12$5Dm07sFBZdEIg6ZaOK8.KuQi7g4mfBpjCoHcfjrMgXsaR1Cqf7pH6', NULL, '2025-10-22 11:09:53', '2025-10-22 11:09:53', 'customer', 1, NULL, '6PIWDZ86', 53, 3, 1268.00000000),
(94, 'Level 3 User 2', 'level3_53_2@test.com', NULL, '$2y$12$i/HMd5NuL/bf8rRp1p6qvOHEOex0cQyDYV3yQKhBsnh.aBpf9mrs.', NULL, '2025-10-22 11:09:53', '2025-10-22 11:09:53', 'customer', 1, NULL, '9QHXIEWI', 53, 3, 1439.00000000),
(95, 'Level 3 User 1', 'level3_54_1@test.com', NULL, '$2y$12$neWKwmHjPZRQ3ibef5fUcuXgHuMdYTWPw2dpTJtbV83Mmr/UsBBpC', NULL, '2025-10-22 11:09:53', '2025-10-22 11:09:53', 'customer', 1, NULL, '8ABM5NTS', 54, 3, 1019.00000000),
(96, 'Level 3 User 2', 'level3_54_2@test.com', NULL, '$2y$12$j0e8HghlWXklyu4ktCjK3OW.SYRfnFQsBAdy3NhQtCwHvbT6wZQB6', NULL, '2025-10-22 11:09:54', '2025-10-22 11:09:54', 'customer', 1, NULL, '4KNNFQHP', 54, 3, 1476.00000000),
(97, 'Level 3 User 3', 'level3_54_3@test.com', NULL, '$2y$12$f.0iveF/xT27hF2x9Q00WurIX1TeTo2CDzgB1i8/Eue.Ait5eoy.S', NULL, '2025-10-22 11:09:54', '2025-10-22 11:09:54', 'customer', 1, NULL, '6HPU6ZKA', 54, 3, 1460.00000000),
(98, 'Level 4 User 1', 'level4_55_1@test.com', NULL, '$2y$12$dieB4GpVG6nZXbMBIOlKkuGXFci8QeWil2Qw/HRS6tYm0GL.SvhLm', NULL, '2025-10-22 11:09:54', '2025-10-22 11:09:54', 'customer', 1, NULL, 'O1Q1AO9Z', 55, 3, 522.00000000),
(99, 'Level 4 User 1', 'level4_56_1@test.com', NULL, '$2y$12$tMS4FIdbyRcsYlt3sFZp3OySSlSlpsbm0zPkGNqJvihztgYuRRv32', NULL, '2025-10-22 11:09:55', '2025-10-22 11:09:55', 'customer', 1, NULL, '7W71QCU6', 56, 3, 342.00000000),
(100, 'Level 4 User 2', 'level4_56_2@test.com', NULL, '$2y$12$jXap1dVE5s8vMiZX06f32.k1C0A1JOEVppkigBy0OvqdpRz/XrrGO', NULL, '2025-10-22 11:09:55', '2025-10-22 11:09:55', 'customer', 1, NULL, 'HFKZAT0P', 56, 3, 191.00000000),
(101, 'Level 4 User 1', 'level4_57_1@test.com', NULL, '$2y$12$hazr7Jj5.K/6E.PORFuyoeOV3EusVtp5IriUppN4FmJ39aFVDYlqy', NULL, '2025-10-22 11:09:55', '2025-10-22 11:09:55', 'customer', 1, NULL, 'ZLZTEQGQ', 57, 3, 771.00000000),
(102, 'Level 4 User 2', 'level4_57_2@test.com', NULL, '$2y$12$XaxLoKsmMD1FNCWR/Ec.C.ev6SMu.atOrnPsAos.w/8z.KKu3Tkr2', NULL, '2025-10-22 11:09:56', '2025-10-22 11:09:56', 'customer', 1, NULL, '1MPYQBNC', 57, 3, 377.00000000),
(103, 'Level 4 User 1', 'level4_58_1@test.com', NULL, '$2y$12$21iXrncKS5ClBUdngnaVVe0B3sE1RACwBmXbClNMXbNNmM3PPy9VW', NULL, '2025-10-22 11:09:56', '2025-10-22 11:09:56', 'customer', 1, NULL, 'QK528VOM', 58, 3, 440.00000000),
(104, 'Level 4 User 1', 'level4_59_1@test.com', NULL, '$2y$12$ReIpfGLnb3FkoDZZA6R5c.Apl2N4AxzsshF5FWtlpukSQdopzHQkS', NULL, '2025-10-22 11:09:56', '2025-10-22 11:09:56', 'customer', 1, NULL, 'Z8YLRY1E', 59, 3, 447.00000000),
(105, 'Level 4 User 1', 'level4_60_1@test.com', NULL, '$2y$12$TcSl4a5S.IDO4Cnxcogn4.Y2OnOk8ej1jsOymnVHFL570wSQRGH.K', NULL, '2025-10-22 11:09:57', '2025-10-22 11:09:57', 'customer', 1, NULL, '4RPBIQRL', 60, 3, 655.00000000),
(106, 'Level 4 User 2', 'level4_60_2@test.com', NULL, '$2y$12$l2X6gPin2AnuWoS34DmVc.w/F85uEtpmvUNNJCXllAatXYR.bjcLm', NULL, '2025-10-22 11:09:57', '2025-10-22 11:09:57', 'customer', 1, NULL, 'UIUXJWHB', 60, 3, 445.00000000),
(107, 'Level 4 User 1', 'level4_61_1@test.com', NULL, '$2y$12$BKN3MqHhHmtEIvwRLtatvO6SMBOaLIOY0bSBx6Uk0AJvtVHu8.Xo.', NULL, '2025-10-22 11:09:57', '2025-10-22 11:09:57', 'customer', 1, NULL, '9H1JPOPA', 61, 3, 481.00000000),
(108, 'Level 4 User 1', 'level4_62_1@test.com', NULL, '$2y$12$bvRpCfKnprkdrvujHVMOLeDtR567I7eyn1hQsJud5eyNjOCtv1Vh6', NULL, '2025-10-22 11:09:58', '2025-10-22 11:09:58', 'customer', 1, NULL, '9QC00ZOE', 62, 3, 740.00000000),
(109, 'Level 4 User 1', 'level4_63_1@test.com', NULL, '$2y$12$63UIVw0EmVAjvaVLQWYFF.GpFIMzAM1clEvBCqqC6XLlqASRXmvAq', NULL, '2025-10-22 11:09:58', '2025-10-22 11:09:58', 'customer', 1, NULL, 'K0YDCELQ', 63, 3, 617.00000000),
(110, 'Level 4 User 2', 'level4_63_2@test.com', NULL, '$2y$12$3R.pTZxCHpn6Ib2j3trnyuNzCQ3aQ/ub2nNoqAPzZdOThU1.Ty6gm', NULL, '2025-10-22 11:09:58', '2025-10-22 11:09:58', 'customer', 1, NULL, 'WHRTQBRH', 63, 3, 201.00000000),
(111, 'Level 4 User 1', 'level4_64_1@test.com', NULL, '$2y$12$rIQd/ksMMCjPBJZDxtM5xu9yDeKbPVnTGYJ0wBgK2jWJ/D1bjvvui', NULL, '2025-10-22 11:09:59', '2025-10-22 11:09:59', 'customer', 1, NULL, '2GF9ADCZ', 64, 3, 357.00000000),
(112, 'Level 2 User 4', 'level2_33_4@test.com', NULL, '$2y$12$YnwtEny23eRu//1EQz/xB.R/D1ZXq/ZF5rl4IYXLQOs5l3UF/EHMW', NULL, '2025-10-22 11:12:46', '2025-10-22 11:12:46', 'customer', 1, NULL, 'SCLO08NT', 33, 3, 1694.00000000),
(113, 'Level 2 User 4', 'level2_34_4@test.com', NULL, '$2y$12$v9pd.aHdrchJ9nQ936RmN.qcDZwcZ7xQ1tbISZg/HLE6i0qdfPomC', NULL, '2025-10-22 11:12:48', '2025-10-22 11:12:48', 'customer', 1, NULL, 'JDTBTIGT', 34, 3, 2793.00000000),
(114, 'Level 3 User 3', 'level3_43_3@test.com', NULL, '$2y$12$.mjLSEfGWdECKfGf2ihqpeA37q6gEIMCb.6NqOIeI7ZCBg5x3Cisa', NULL, '2025-10-22 11:12:55', '2025-10-22 11:12:55', 'customer', 1, NULL, 'JBI2Z5SN', 43, 3, 1212.00000000),
(115, 'Level 3 User 1', 'level3_112_1@test.com', NULL, '$2y$12$q473nG00LhvSmGdujU2.Le5PKNIz9Apwz..VvqSrtaNViG8ODgta6', NULL, '2025-10-22 11:12:55', '2025-10-22 11:12:55', 'customer', 1, NULL, 'PGQJQ8GF', 112, 3, 1056.00000000),
(116, 'Level 3 User 2', 'level3_112_2@test.com', NULL, '$2y$12$GW3d4zthZd3fpYvh0/XeWe1pTsq5P/aIQqaVc.PfFk1crIKHZb8AO', NULL, '2025-10-22 11:12:56', '2025-10-22 11:12:56', 'customer', 1, NULL, 'QQKVILAT', 112, 3, 882.00000000),
(117, 'Level 3 User 3', 'level3_112_3@test.com', NULL, '$2y$12$YnXPF0Z72EklGJsecrBVT.oIgSy.xqqS1j778vfYqdUc7TrokN09q', NULL, '2025-10-22 11:12:56', '2025-10-22 11:12:56', 'customer', 1, NULL, 'OEBGZW7U', 112, 3, 1132.00000000),
(118, 'Level 3 User 3', 'level3_44_3@test.com', NULL, '$2y$12$TTfPvqbBP9aBIuBq0sIVquzY2FIRBPJ3TcvRV2sUQBQ42xtzVAHo.', NULL, '2025-10-22 11:12:57', '2025-10-22 11:12:57', 'customer', 1, NULL, 'UPLCALL5', 44, 3, 1338.00000000),
(119, 'Level 3 User 3', 'level3_45_3@test.com', NULL, '$2y$12$uzc6TSRknCF3UunxCeAco.u.EpBLxxziyTgOFI0JYNrmPe3tcKQo2', NULL, '2025-10-22 11:12:58', '2025-10-22 11:12:58', 'customer', 1, NULL, 'YQOHEDBH', 45, 3, 1366.00000000),
(120, 'Level 3 User 3', 'level3_46_3@test.com', NULL, '$2y$12$3bz0G9aTYHCZRiH9PdiGeuXX.2yuFusq/U5FOTh5jOiuaT6GncFGO', NULL, '2025-10-22 11:12:59', '2025-10-22 11:12:59', 'customer', 1, NULL, 'R5HQJMKG', 46, 3, 735.00000000),
(121, 'Level 3 User 1', 'level3_113_1@test.com', NULL, '$2y$12$m491ZSKE/eYTPDtuN9xDUOrBmbwYdY6vpllAuTMjkD96U4hIltZG6', NULL, '2025-10-22 11:12:59', '2025-10-22 11:12:59', 'customer', 1, NULL, 'UBRZGEPG', 113, 3, 704.00000000),
(122, 'Level 3 User 2', 'level3_113_2@test.com', NULL, '$2y$12$7loj0JRHrsc.9KZlihYpEuCiquVf.gaI4QtwBwdv3k/FOul2bRLsu', NULL, '2025-10-22 11:13:00', '2025-10-22 11:13:00', 'customer', 1, NULL, 'WYTKXROB', 113, 3, 1260.00000000),
(123, 'Level 3 User 3', 'level3_48_3@test.com', NULL, '$2y$12$F8Cszy32QIYak6S3uEKpROJUCmiOWFVPoJ1io5bJlrw97pZvIebBK', NULL, '2025-10-22 11:13:01', '2025-10-22 11:13:01', 'customer', 1, NULL, 'QN4GAMOV', 48, 3, 611.00000000),
(124, 'Level 3 User 3', 'level3_49_3@test.com', NULL, '$2y$12$Zqu3jgHs/H3D/58r7gbmQuthzNfSROciHJ8dg0nejaYEwgrHoS7iG', NULL, '2025-10-22 11:13:02', '2025-10-22 11:13:02', 'customer', 1, NULL, 'OZ5P8AMB', 49, 3, 1078.00000000),
(125, 'Level 3 User 3', 'level3_53_3@test.com', NULL, '$2y$12$D7003gIicz6ryjJosWc8cOkwQ3tLoiYpc.dKbnAC9NYWnKWIHo2gK', NULL, '2025-10-22 11:13:05', '2025-10-22 11:13:05', 'customer', 1, NULL, 'M1ETN0LB', 53, 3, 1490.00000000),
(126, 'Level 4 User 2', 'level4_58_2@test.com', NULL, '$2y$12$WqHxzdzOrH9qufxlGZblieWx5LTM5R/K9xzFDfNU491oj4PEY1EK2', NULL, '2025-10-22 11:13:07', '2025-10-22 11:13:07', 'customer', 1, NULL, '1N7JDK2Z', 58, 3, 708.00000000),
(127, 'Level 4 User 2', 'level4_59_2@test.com', NULL, '$2y$12$lVUaeinkXF8KaduyRfawI.6tDMlWgs79ukJKqJOsWBCktJ6bZUES.', NULL, '2025-10-22 11:13:07', '2025-10-22 11:13:07', 'customer', 1, NULL, 'ME7Y4R9L', 59, 3, 126.00000000),
(128, 'Level 4 User 2', 'level4_61_2@test.com', NULL, '$2y$12$O48fDASPw/6c1/sgPpLf6etsKPxZXwtlqj6t/RbyF4AvzrQBPHANW', NULL, '2025-10-22 11:13:08', '2025-10-22 11:13:08', 'customer', 1, NULL, 'SZDKQ4ZO', 61, 3, 181.00000000),
(129, 'Level 4 User 2', 'level4_64_2@test.com', NULL, '$2y$12$wSprTmZ4U0oHQKJ6xyRuJuv55nwhkAqTXp2A5dk7M4KyRdExQm92G', NULL, '2025-10-22 11:13:09', '2025-10-22 11:13:09', 'customer', 1, NULL, 'KK3HHD4J', 64, 3, 238.00000000),
(130, 'Level 4 User 1', 'level4_65_1@test.com', NULL, '$2y$12$hwh03r5wYooQVTuBp2E4p.EdMeo8O9fZa/HOhQ1DTcRri0PplIjOC', NULL, '2025-10-22 11:13:10', '2025-10-22 11:13:10', 'customer', 1, NULL, 'TPO4HVL7', 65, 3, 224.00000000),
(131, 'Level 4 User 2', 'level4_65_2@test.com', NULL, '$2y$12$TXbAxcEuWhh8BgjEtl/bDOkesvZ7wfLgDvtHdKZWxV.Qn3OIm6X56', NULL, '2025-10-22 11:13:10', '2025-10-22 11:13:10', 'customer', 1, NULL, 'TJILNJNU', 65, 3, 530.00000000);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USDT',
  `balance` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `locked_balance` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `total_deposited` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `total_withdrawn` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `total_profit` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `total_loss` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `currency`, `balance`, `locked_balance`, `total_deposited`, `total_withdrawn`, `total_profit`, `total_loss`, `created_at`, `updated_at`) VALUES
(1, 2, 'USDT', 1000.00000000, 0.00000000, 1000.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-22 10:37:09', '2025-10-22 10:37:09'),
(2, 4, 'USDT', 2500.00000000, 0.00000000, 2500.00000000, 0.00000000, 250.00000000, 0.00000000, '2025-10-22 11:09:19', '2025-10-22 11:09:19'),
(3, 5, 'USDT', 7500.00000000, 0.00000000, 7500.00000000, 0.00000000, 750.00000000, 0.00000000, '2025-10-22 11:09:19', '2025-10-22 11:09:19'),
(4, 6, 'USDT', 1200.00000000, 0.00000000, 1200.00000000, 0.00000000, 120.00000000, 0.00000000, '2025-10-22 11:09:20', '2025-10-22 11:09:20'),
(5, 7, 'USDT', 50.00000000, 0.00000000, 500.00000000, 450.00000000, 0.00000000, 0.00000000, '2025-10-22 11:09:20', '2025-10-22 11:09:20'),
(6, 8, 'USDT', 50.00000000, 0.00000000, 800.00000000, 750.00000000, 0.00000000, 0.00000000, '2025-10-22 11:09:21', '2025-10-22 11:09:21'),
(7, 9, 'USDT', 600.00000000, 0.00000000, 1000.00000000, 0.00000000, 100.00000000, 0.00000000, '2025-10-22 11:09:21', '2025-10-22 11:09:21'),
(8, 10, 'USDT', 300.00000000, 0.00000000, 500.00000000, 0.00000000, 50.00000000, 0.00000000, '2025-10-22 11:09:21', '2025-10-22 11:09:21'),
(9, 11, 'USDT', 3000.00000000, 0.00000000, 5000.00000000, 0.00000000, 500.00000000, 0.00000000, '2025-10-22 11:09:22', '2025-10-22 11:09:22'),
(10, 12, 'USDT', 127.40000000, 0.00000000, 182.00000000, 0.00000000, 27.30000000, 0.00000000, '2025-10-22 11:09:22', '2025-10-22 11:09:22'),
(11, 13, 'USDT', 198.10000000, 0.00000000, 283.00000000, 0.00000000, 42.45000000, 0.00000000, '2025-10-22 11:09:22', '2025-10-22 11:09:22'),
(12, 14, 'USDT', 244.30000000, 0.00000000, 349.00000000, 0.00000000, 52.35000000, 0.00000000, '2025-10-22 11:09:23', '2025-10-22 11:09:23'),
(13, 15, 'USDT', 308.70000000, 0.00000000, 441.00000000, 0.00000000, 66.15000000, 0.00000000, '2025-10-22 11:09:23', '2025-10-22 11:09:23'),
(14, 16, 'USDT', 319.90000000, 0.00000000, 457.00000000, 0.00000000, 68.55000000, 0.00000000, '2025-10-22 11:09:23', '2025-10-22 11:09:23'),
(15, 17, 'USDT', 702.10000000, 0.00000000, 1003.00000000, 0.00000000, 150.45000000, 0.00000000, '2025-10-22 11:09:24', '2025-10-22 11:09:24'),
(16, 18, 'USDT', 1111.60000000, 0.00000000, 1588.00000000, 0.00000000, 238.20000000, 0.00000000, '2025-10-22 11:09:24', '2025-10-22 11:09:24'),
(17, 19, 'USDT', 1150.80000000, 0.00000000, 1644.00000000, 0.00000000, 246.60000000, 0.00000000, '2025-10-22 11:09:24', '2025-10-22 11:09:24'),
(18, 20, 'USDT', 3326.40000000, 0.00000000, 4752.00000000, 0.00000000, 712.80000000, 0.00000000, '2025-10-22 11:09:25', '2025-10-22 11:09:25'),
(19, 21, 'USDT', 2739.10000000, 0.00000000, 3913.00000000, 0.00000000, 586.95000000, 0.00000000, '2025-10-22 11:09:25', '2025-10-22 11:09:25'),
(20, 22, 'USDT', 4650.80000000, 0.00000000, 6644.00000000, 0.00000000, 996.60000000, 0.00000000, '2025-10-22 11:09:25', '2025-10-22 11:09:25'),
(21, 23, 'USDT', 5448.10000000, 0.00000000, 7783.00000000, 0.00000000, 1167.45000000, 0.00000000, '2025-10-22 11:09:26', '2025-10-22 11:09:26'),
(22, 24, 'USDT', 10000.00000000, 0.00000000, 10000.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-22 11:09:26', '2025-10-22 11:09:26'),
(23, 25, 'USDT', 2500.00000000, 0.00000000, 5000.00000000, 0.00000000, 500.00000000, 0.00000000, '2025-10-22 11:09:26', '2025-10-22 11:09:26'),
(24, 26, 'USDT', 1500.00000000, 0.00000000, 3000.00000000, 0.00000000, 300.00000000, 0.00000000, '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(25, 27, 'USDT', 750.00000000, 0.00000000, 1500.00000000, 0.00000000, 150.00000000, 0.00000000, '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(26, 28, 'USDT', 1500.00000000, 0.00000000, 3000.00000000, 0.00000000, 300.00000000, 0.00000000, '2025-10-22 11:09:27', '2025-10-22 11:09:27'),
(27, 29, 'USDT', 2500.00000000, 0.00000000, 5000.00000000, 0.00000000, 500.00000000, 0.00000000, '2025-10-22 11:09:28', '2025-10-22 11:09:28'),
(28, 30, 'USDT', 2500.00000000, 0.00000000, 5000.00000000, 0.00000000, 500.00000000, 0.00000000, '2025-10-22 11:09:30', '2025-10-22 11:09:30'),
(29, 31, 'USDT', 15000.00000000, 0.00000000, 10000.00000000, 0.00000000, 5000.00000000, 0.00000000, '2025-10-22 11:09:31', '2025-10-22 11:09:31'),
(30, 32, 'USDT', 3985.20000000, 0.00000000, 6642.00000000, 0.00000000, 664.20000000, 0.00000000, '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(31, 33, 'USDT', 4128.60000000, 0.00000000, 6881.00000000, 0.00000000, 688.10000000, 0.00000000, '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(32, 34, 'USDT', 3856.20000000, 0.00000000, 6427.00000000, 0.00000000, 642.70000000, 0.00000000, '2025-10-22 11:09:32', '2025-10-22 11:09:32'),
(33, 35, 'USDT', 3312.00000000, 0.00000000, 5520.00000000, 0.00000000, 552.00000000, 0.00000000, '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(34, 36, 'USDT', 3146.40000000, 0.00000000, 5244.00000000, 0.00000000, 524.40000000, 0.00000000, '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(35, 37, 'USDT', 1045.20000000, 0.00000000, 1742.00000000, 0.00000000, 174.20000000, 0.00000000, '2025-10-22 11:09:33', '2025-10-22 11:09:33'),
(36, 38, 'USDT', 1765.80000000, 0.00000000, 2943.00000000, 0.00000000, 294.30000000, 0.00000000, '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(37, 39, 'USDT', 1346.40000000, 0.00000000, 2244.00000000, 0.00000000, 224.40000000, 0.00000000, '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(38, 40, 'USDT', 1008.00000000, 0.00000000, 1680.00000000, 0.00000000, 168.00000000, 0.00000000, '2025-10-22 11:09:34', '2025-10-22 11:09:34'),
(39, 41, 'USDT', 1765.20000000, 0.00000000, 2942.00000000, 0.00000000, 294.20000000, 0.00000000, '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(40, 42, 'USDT', 1768.80000000, 0.00000000, 2948.00000000, 0.00000000, 294.80000000, 0.00000000, '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(41, 43, 'USDT', 1285.80000000, 0.00000000, 2143.00000000, 0.00000000, 214.30000000, 0.00000000, '2025-10-22 11:09:35', '2025-10-22 11:09:35'),
(42, 44, 'USDT', 1039.80000000, 0.00000000, 1733.00000000, 0.00000000, 173.30000000, 0.00000000, '2025-10-22 11:09:36', '2025-10-22 11:09:36'),
(43, 45, 'USDT', 1087.80000000, 0.00000000, 1813.00000000, 0.00000000, 181.30000000, 0.00000000, '2025-10-22 11:09:36', '2025-10-22 11:09:36'),
(44, 46, 'USDT', 1088.40000000, 0.00000000, 1814.00000000, 0.00000000, 181.40000000, 0.00000000, '2025-10-22 11:09:37', '2025-10-22 11:09:37'),
(45, 47, 'USDT', 797.40000000, 0.00000000, 1329.00000000, 0.00000000, 132.90000000, 0.00000000, '2025-10-22 11:09:37', '2025-10-22 11:09:37'),
(46, 48, 'USDT', 1449.60000000, 0.00000000, 2416.00000000, 0.00000000, 241.60000000, 0.00000000, '2025-10-22 11:09:37', '2025-10-22 11:09:37'),
(47, 49, 'USDT', 816.00000000, 0.00000000, 1360.00000000, 0.00000000, 136.00000000, 0.00000000, '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(48, 50, 'USDT', 665.40000000, 0.00000000, 1109.00000000, 0.00000000, 110.90000000, 0.00000000, '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(49, 51, 'USDT', 1546.80000000, 0.00000000, 2578.00000000, 0.00000000, 257.80000000, 0.00000000, '2025-10-22 11:09:38', '2025-10-22 11:09:38'),
(50, 52, 'USDT', 1563.00000000, 0.00000000, 2605.00000000, 0.00000000, 260.50000000, 0.00000000, '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(51, 53, 'USDT', 888.00000000, 0.00000000, 1480.00000000, 0.00000000, 148.00000000, 0.00000000, '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(52, 54, 'USDT', 1449.60000000, 0.00000000, 2416.00000000, 0.00000000, 241.60000000, 0.00000000, '2025-10-22 11:09:39', '2025-10-22 11:09:39'),
(53, 55, 'USDT', 495.60000000, 0.00000000, 826.00000000, 0.00000000, 82.60000000, 0.00000000, '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(54, 56, 'USDT', 752.40000000, 0.00000000, 1254.00000000, 0.00000000, 125.40000000, 0.00000000, '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(55, 57, 'USDT', 306.00000000, 0.00000000, 510.00000000, 0.00000000, 51.00000000, 0.00000000, '2025-10-22 11:09:40', '2025-10-22 11:09:40'),
(56, 58, 'USDT', 595.20000000, 0.00000000, 992.00000000, 0.00000000, 99.20000000, 0.00000000, '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(57, 59, 'USDT', 459.60000000, 0.00000000, 766.00000000, 0.00000000, 76.60000000, 0.00000000, '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(58, 60, 'USDT', 348.60000000, 0.00000000, 581.00000000, 0.00000000, 58.10000000, 0.00000000, '2025-10-22 11:09:41', '2025-10-22 11:09:41'),
(59, 61, 'USDT', 683.40000000, 0.00000000, 1139.00000000, 0.00000000, 113.90000000, 0.00000000, '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(60, 62, 'USDT', 508.80000000, 0.00000000, 848.00000000, 0.00000000, 84.80000000, 0.00000000, '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(61, 63, 'USDT', 438.60000000, 0.00000000, 731.00000000, 0.00000000, 73.10000000, 0.00000000, '2025-10-22 11:09:42', '2025-10-22 11:09:42'),
(62, 64, 'USDT', 716.40000000, 0.00000000, 1194.00000000, 0.00000000, 119.40000000, 0.00000000, '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(63, 65, 'USDT', 554.40000000, 0.00000000, 924.00000000, 0.00000000, 92.40000000, 0.00000000, '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(64, 66, 'USDT', 594.60000000, 0.00000000, 991.00000000, 0.00000000, 99.10000000, 0.00000000, '2025-10-22 11:09:43', '2025-10-22 11:09:43'),
(65, 67, 'USDT', 359.40000000, 0.00000000, 599.00000000, 0.00000000, 59.90000000, 0.00000000, '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(66, 68, 'USDT', 862.80000000, 0.00000000, 1438.00000000, 0.00000000, 143.80000000, 0.00000000, '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(67, 69, 'USDT', 857.40000000, 0.00000000, 1429.00000000, 0.00000000, 142.90000000, 0.00000000, '2025-10-22 11:09:44', '2025-10-22 11:09:44'),
(68, 70, 'USDT', 330.00000000, 0.00000000, 550.00000000, 0.00000000, 55.00000000, 0.00000000, '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(69, 71, 'USDT', 778.20000000, 0.00000000, 1297.00000000, 0.00000000, 129.70000000, 0.00000000, '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(70, 72, 'USDT', 595.80000000, 0.00000000, 993.00000000, 0.00000000, 99.30000000, 0.00000000, '2025-10-22 11:09:45', '2025-10-22 11:09:45'),
(71, 73, 'USDT', 786.00000000, 0.00000000, 1310.00000000, 0.00000000, 131.00000000, 0.00000000, '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(72, 74, 'USDT', 849.00000000, 0.00000000, 1415.00000000, 0.00000000, 141.50000000, 0.00000000, '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(73, 75, 'USDT', 784.20000000, 0.00000000, 1307.00000000, 0.00000000, 130.70000000, 0.00000000, '2025-10-22 11:09:46', '2025-10-22 11:09:46'),
(74, 76, 'USDT', 832.80000000, 0.00000000, 1388.00000000, 0.00000000, 138.80000000, 0.00000000, '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(75, 77, 'USDT', 867.00000000, 0.00000000, 1445.00000000, 0.00000000, 144.50000000, 0.00000000, '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(76, 78, 'USDT', 334.20000000, 0.00000000, 557.00000000, 0.00000000, 55.70000000, 0.00000000, '2025-10-22 11:09:47', '2025-10-22 11:09:47'),
(77, 79, 'USDT', 443.40000000, 0.00000000, 739.00000000, 0.00000000, 73.90000000, 0.00000000, '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(78, 80, 'USDT', 592.80000000, 0.00000000, 988.00000000, 0.00000000, 98.80000000, 0.00000000, '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(79, 81, 'USDT', 810.60000000, 0.00000000, 1351.00000000, 0.00000000, 135.10000000, 0.00000000, '2025-10-22 11:09:48', '2025-10-22 11:09:48'),
(80, 82, 'USDT', 385.20000000, 0.00000000, 642.00000000, 0.00000000, 64.20000000, 0.00000000, '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(81, 83, 'USDT', 679.80000000, 0.00000000, 1133.00000000, 0.00000000, 113.30000000, 0.00000000, '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(82, 84, 'USDT', 592.80000000, 0.00000000, 988.00000000, 0.00000000, 98.80000000, 0.00000000, '2025-10-22 11:09:49', '2025-10-22 11:09:49'),
(83, 85, 'USDT', 749.40000000, 0.00000000, 1249.00000000, 0.00000000, 124.90000000, 0.00000000, '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(84, 86, 'USDT', 750.60000000, 0.00000000, 1251.00000000, 0.00000000, 125.10000000, 0.00000000, '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(85, 87, 'USDT', 497.40000000, 0.00000000, 829.00000000, 0.00000000, 82.90000000, 0.00000000, '2025-10-22 11:09:50', '2025-10-22 11:09:50'),
(86, 88, 'USDT', 610.20000000, 0.00000000, 1017.00000000, 0.00000000, 101.70000000, 0.00000000, '2025-10-22 11:09:51', '2025-10-22 11:09:51'),
(87, 89, 'USDT', 415.20000000, 0.00000000, 692.00000000, 0.00000000, 69.20000000, 0.00000000, '2025-10-22 11:09:51', '2025-10-22 11:09:51'),
(88, 90, 'USDT', 897.00000000, 0.00000000, 1495.00000000, 0.00000000, 149.50000000, 0.00000000, '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(89, 91, 'USDT', 565.80000000, 0.00000000, 943.00000000, 0.00000000, 94.30000000, 0.00000000, '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(90, 92, 'USDT', 408.60000000, 0.00000000, 681.00000000, 0.00000000, 68.10000000, 0.00000000, '2025-10-22 11:09:52', '2025-10-22 11:09:52'),
(91, 93, 'USDT', 760.80000000, 0.00000000, 1268.00000000, 0.00000000, 126.80000000, 0.00000000, '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(92, 94, 'USDT', 863.40000000, 0.00000000, 1439.00000000, 0.00000000, 143.90000000, 0.00000000, '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(93, 95, 'USDT', 611.40000000, 0.00000000, 1019.00000000, 0.00000000, 101.90000000, 0.00000000, '2025-10-22 11:09:53', '2025-10-22 11:09:53'),
(94, 96, 'USDT', 885.60000000, 0.00000000, 1476.00000000, 0.00000000, 147.60000000, 0.00000000, '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(95, 97, 'USDT', 876.00000000, 0.00000000, 1460.00000000, 0.00000000, 146.00000000, 0.00000000, '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(96, 98, 'USDT', 313.20000000, 0.00000000, 522.00000000, 0.00000000, 52.20000000, 0.00000000, '2025-10-22 11:09:54', '2025-10-22 11:09:54'),
(97, 99, 'USDT', 205.20000000, 0.00000000, 342.00000000, 0.00000000, 34.20000000, 0.00000000, '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(98, 100, 'USDT', 114.60000000, 0.00000000, 191.00000000, 0.00000000, 19.10000000, 0.00000000, '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(99, 101, 'USDT', 462.60000000, 0.00000000, 771.00000000, 0.00000000, 77.10000000, 0.00000000, '2025-10-22 11:09:55', '2025-10-22 11:09:55'),
(100, 102, 'USDT', 226.20000000, 0.00000000, 377.00000000, 0.00000000, 37.70000000, 0.00000000, '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(101, 103, 'USDT', 264.00000000, 0.00000000, 440.00000000, 0.00000000, 44.00000000, 0.00000000, '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(102, 104, 'USDT', 268.20000000, 0.00000000, 447.00000000, 0.00000000, 44.70000000, 0.00000000, '2025-10-22 11:09:56', '2025-10-22 11:09:56'),
(103, 105, 'USDT', 393.00000000, 0.00000000, 655.00000000, 0.00000000, 65.50000000, 0.00000000, '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(104, 106, 'USDT', 267.00000000, 0.00000000, 445.00000000, 0.00000000, 44.50000000, 0.00000000, '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(105, 107, 'USDT', 288.60000000, 0.00000000, 481.00000000, 0.00000000, 48.10000000, 0.00000000, '2025-10-22 11:09:57', '2025-10-22 11:09:57'),
(106, 108, 'USDT', 444.00000000, 0.00000000, 740.00000000, 0.00000000, 74.00000000, 0.00000000, '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(107, 109, 'USDT', 370.20000000, 0.00000000, 617.00000000, 0.00000000, 61.70000000, 0.00000000, '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(108, 110, 'USDT', 120.60000000, 0.00000000, 201.00000000, 0.00000000, 20.10000000, 0.00000000, '2025-10-22 11:09:58', '2025-10-22 11:09:58'),
(109, 111, 'USDT', 214.20000000, 0.00000000, 357.00000000, 0.00000000, 35.70000000, 0.00000000, '2025-10-22 11:09:59', '2025-10-22 11:09:59'),
(110, 112, 'USDT', 1016.40000000, 0.00000000, 1694.00000000, 0.00000000, 169.40000000, 0.00000000, '2025-10-22 11:12:46', '2025-10-22 11:12:46'),
(111, 113, 'USDT', 1675.80000000, 0.00000000, 2793.00000000, 0.00000000, 279.30000000, 0.00000000, '2025-10-22 11:12:48', '2025-10-22 11:12:48'),
(112, 114, 'USDT', 727.20000000, 0.00000000, 1212.00000000, 0.00000000, 121.20000000, 0.00000000, '2025-10-22 11:12:55', '2025-10-22 11:12:55'),
(113, 115, 'USDT', 633.60000000, 0.00000000, 1056.00000000, 0.00000000, 105.60000000, 0.00000000, '2025-10-22 11:12:55', '2025-10-22 11:12:55'),
(114, 116, 'USDT', 529.20000000, 0.00000000, 882.00000000, 0.00000000, 88.20000000, 0.00000000, '2025-10-22 11:12:56', '2025-10-22 11:12:56'),
(115, 117, 'USDT', 679.20000000, 0.00000000, 1132.00000000, 0.00000000, 113.20000000, 0.00000000, '2025-10-22 11:12:56', '2025-10-22 11:12:56'),
(116, 118, 'USDT', 802.80000000, 0.00000000, 1338.00000000, 0.00000000, 133.80000000, 0.00000000, '2025-10-22 11:12:57', '2025-10-22 11:12:57'),
(117, 119, 'USDT', 819.60000000, 0.00000000, 1366.00000000, 0.00000000, 136.60000000, 0.00000000, '2025-10-22 11:12:58', '2025-10-22 11:12:58'),
(118, 120, 'USDT', 441.00000000, 0.00000000, 735.00000000, 0.00000000, 73.50000000, 0.00000000, '2025-10-22 11:12:59', '2025-10-22 11:12:59'),
(119, 121, 'USDT', 422.40000000, 0.00000000, 704.00000000, 0.00000000, 70.40000000, 0.00000000, '2025-10-22 11:12:59', '2025-10-22 11:12:59'),
(120, 122, 'USDT', 756.00000000, 0.00000000, 1260.00000000, 0.00000000, 126.00000000, 0.00000000, '2025-10-22 11:13:00', '2025-10-22 11:13:00'),
(121, 123, 'USDT', 366.60000000, 0.00000000, 611.00000000, 0.00000000, 61.10000000, 0.00000000, '2025-10-22 11:13:01', '2025-10-22 11:13:01'),
(122, 124, 'USDT', 646.80000000, 0.00000000, 1078.00000000, 0.00000000, 107.80000000, 0.00000000, '2025-10-22 11:13:02', '2025-10-22 11:13:02'),
(123, 125, 'USDT', 894.00000000, 0.00000000, 1490.00000000, 0.00000000, 149.00000000, 0.00000000, '2025-10-22 11:13:05', '2025-10-22 11:13:05'),
(124, 126, 'USDT', 424.80000000, 0.00000000, 708.00000000, 0.00000000, 70.80000000, 0.00000000, '2025-10-22 11:13:07', '2025-10-22 11:13:07'),
(125, 127, 'USDT', 75.60000000, 0.00000000, 126.00000000, 0.00000000, 12.60000000, 0.00000000, '2025-10-22 11:13:07', '2025-10-22 11:13:07'),
(126, 128, 'USDT', 108.60000000, 0.00000000, 181.00000000, 0.00000000, 18.10000000, 0.00000000, '2025-10-22 11:13:08', '2025-10-22 11:13:08'),
(127, 129, 'USDT', 142.80000000, 0.00000000, 238.00000000, 0.00000000, 23.80000000, 0.00000000, '2025-10-22 11:13:09', '2025-10-22 11:13:09'),
(128, 130, 'USDT', 134.40000000, 0.00000000, 224.00000000, 0.00000000, 22.40000000, 0.00000000, '2025-10-22 11:13:10', '2025-10-22 11:13:10'),
(129, 131, 'USDT', 318.00000000, 0.00000000, 530.00000000, 0.00000000, 53.00000000, 0.00000000, '2025-10-22 11:13:10', '2025-10-22 11:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_addresses`
--

CREATE TABLE `wallet_addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wallet_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `network` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agents_user_id_status_index` (`user_id`,`status`);

--
-- Indexes for table `api_accounts`
--
ALTER TABLE `api_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_accounts_user_id_exchange_unique` (`user_id`,`exchange`),
  ADD KEY `api_accounts_exchange_is_active_index` (`exchange`,`is_active`);

--
-- Indexes for table `bonus_wallets`
--
ALTER TABLE `bonus_wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bonus_wallets_user_id_parent_id_index` (`user_id`,`parent_id`),
  ADD KEY `bonus_wallets_parent_id_foreign` (`parent_id`),
  ADD KEY `bonus_wallets_package_id_foreign` (`package_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `customers_wallets`
--
ALTER TABLE `customers_wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_wallets_user_id_index` (`user_id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deposits_deposit_id_unique` (`deposit_id`),
  ADD KEY `deposits_approved_by_foreign` (`approved_by`),
  ADD KEY `deposits_user_id_status_index` (`user_id`,`status`),
  ADD KEY `deposits_status_created_at_index` (`status`,`created_at`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_user_id_status_index` (`user_id`,`status`),
  ADD KEY `messages_admin_id_status_index` (`admin_id`,`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  ADD KEY `notifications_type_created_at_index` (`type`,`created_at`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `packages_is_active_price_index` (`is_active`,`price`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profiles_referral_code_unique` (`referral_code`),
  ADD KEY `profiles_user_id_foreign` (`user_id`),
  ADD KEY `profiles_referred_by_foreign` (`referred_by`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referrals_referrer_id_referred_id_unique` (`referrer_id`,`referred_id`),
  ADD KEY `referrals_referred_id_foreign` (`referred_id`),
  ADD KEY `referrals_referrer_id_status_index` (`referrer_id`,`status`);

--
-- Indexes for table `rent_bot_packages`
--
ALTER TABLE `rent_bot_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `trades`
--
ALTER TABLE `trades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trades_trade_id_unique` (`trade_id`),
  ADD KEY `trades_user_id_status_index` (`user_id`,`status`),
  ADD KEY `trades_symbol_created_at_index` (`symbol`,`created_at`),
  ADD KEY `trades_exchange_exchange_order_id_index` (`exchange`,`exchange_order_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_transaction_id_unique` (`transaction_id`),
  ADD KEY `transactions_user_id_type_index` (`user_id`,`type`),
  ADD KEY `transactions_status_created_at_index` (`status`,`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  ADD KEY `users_referred_by_foreign` (`referred_by`),
  ADD KEY `users_active_plan_id_foreign` (`active_plan_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallets_user_id_currency_unique` (`user_id`,`currency`);

--
-- Indexes for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_accounts`
--
ALTER TABLE `api_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bonus_wallets`
--
ALTER TABLE `bonus_wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers_wallets`
--
ALTER TABLE `customers_wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `rent_bot_packages`
--
ALTER TABLE `rent_bot_packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trades`
--
ALTER TABLE `trades`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `api_accounts`
--
ALTER TABLE `api_accounts`
  ADD CONSTRAINT `api_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bonus_wallets`
--
ALTER TABLE `bonus_wallets`
  ADD CONSTRAINT `bonus_wallets_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bonus_wallets_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bonus_wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers_wallets`
--
ALTER TABLE `customers_wallets`
  ADD CONSTRAINT `customers_wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deposits`
--
ALTER TABLE `deposits`
  ADD CONSTRAINT `deposits_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deposits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_referred_by_foreign` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_referred_id_foreign` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trades`
--
ALTER TABLE `trades`
  ADD CONSTRAINT `trades_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_active_plan_id_foreign` FOREIGN KEY (`active_plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_referred_by_foreign` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
