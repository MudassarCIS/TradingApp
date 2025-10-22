-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2025 at 06:19 AM
-- Server version: 11.6.1-MariaDB-log
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `trade_ai_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','paused') NOT NULL DEFAULT 'active',
  `strategy` varchar(50) NOT NULL,
  `trading_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`trading_rules`)),
  `initial_balance` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `current_balance` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_profit` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_loss` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `win_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_trades` int(11) NOT NULL DEFAULT 0,
  `winning_trades` int(11) NOT NULL DEFAULT 0,
  `losing_trades` int(11) NOT NULL DEFAULT 0,
  `max_drawdown` decimal(5,2) NOT NULL DEFAULT 0.00,
  `risk_per_trade` decimal(5,2) NOT NULL DEFAULT 2.00,
  `auto_trading` tinyint(1) NOT NULL DEFAULT 1,
  `last_trade_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_accounts`
--

CREATE TABLE `api_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `exchange` varchar(20) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `secret_key` varchar(255) NOT NULL,
  `passphrase` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bonus_wallets`
--

CREATE TABLE `bonus_wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `deposit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `investment_amount` decimal(20,8) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL,
  `parent_level` tinyint(4) NOT NULL,
  `bonus_amount` decimal(20,8) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USDT',
  `package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-admin@admin.com|127.0.0.1', 'i:1;', 1759500271),
('laravel-cache-admin@admin.com|127.0.0.1:timer', 'i:1759500271;', 1759500271);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_wallets`
--

CREATE TABLE `customers_wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USDT',
  `amount` decimal(20,8) NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `transaction_type` enum('debit','credit') NOT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'deposit id, bonus_wallet id, trade id etc',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `deposit_id` varchar(255) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `network` varchar(20) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `proof_image` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `deposit_id`, `amount`, `currency`, `network`, `status`, `proof_image`, `notes`, `rejection_reason`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'DEPCWYGY7FE1760810580', 1000.00000000, 'USDT', 'BTC', 'pending', 'deposits/proofs/iLUvYBOI9KoAFAwIky4vQ2u66f3oTGEegg4vwKvJ.png', 'test docs', NULL, NULL, NULL, '2025-10-18 13:03:01', '2025-10-18 13:03:01');

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
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
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
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('support','general','technical','billing') NOT NULL DEFAULT 'support',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `is_read_by_user` tinyint(1) NOT NULL DEFAULT 0,
  `is_read_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `last_reply_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_11_070707_create_permission_tables', 1),
(8, '2025_09_22_131004_create_profiles_table', 2),
(9, '2025_09_22_131009_create_wallets_table', 2),
(10, '2025_09_22_131014_create_transactions_table', 2),
(11, '2025_09_22_131020_create_trades_table', 3),
(12, '2025_09_22_131026_create_agents_table', 3),
(13, '2025_09_22_131033_create_referrals_table', 3),
(14, '2025_09_22_131038_create_messages_table', 3),
(15, '2025_09_22_131043_create_notifications_table', 3),
(16, '2025_09_22_131048_create_packages_table', 3),
(17, '2025_09_22_131054_create_api_accounts_table', 3),
(18, '2025_09_22_131205_add_trading_fields_to_users_table', 3),
(19, '2025_09_23_073924_update_user_type_enum', 4),
(20, '2025_10_08_202305_create_plans_table', 5),
(21, '2025_10_08_210509_create_wallet_addresses_table', 6),
(22, '2025_10_13_184644_create_deposits_table', 7),
(23, '2025_10_18_000010_add_active_plan_to_users', 8),
(24, '2025_10_18_000011_create_bonus_wallets_table', 8),
(25, '2025_10_18_000012_create_customers_wallets_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(5, 'App\\Models\\User', 3),
(1, 'App\\Models\\User', 4),
(5, 'App\\Models\\User', 5),
(1, 'App\\Models\\User', 8),
(5, 'App\\Models\\User', 11),
(5, 'App\\Models\\User', 12),
(5, 'App\\Models\\User', 13),
(5, 'App\\Models\\User', 14),
(5, 'App\\Models\\User', 15),
(5, 'App\\Models\\User', 16),
(5, 'App\\Models\\User', 17),
(5, 'App\\Models\\User', 18),
(5, 'App\\Models\\User', 19),
(5, 'App\\Models\\User', 20),
(5, 'App\\Models\\User', 21);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(20,8) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USDT',
  `min_investment` decimal(20,8) NOT NULL,
  `max_investment` decimal(20,8) DEFAULT NULL,
  `daily_return_rate` decimal(5,4) NOT NULL DEFAULT 0.0000,
  `duration_days` int(11) NOT NULL DEFAULT 30,
  `profit_share` decimal(5,2) NOT NULL DEFAULT 50.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `price`, `currency`, `min_investment`, `max_investment`, `daily_return_rate`, `duration_days`, `profit_share`, `is_active`, `features`, `created_at`, `updated_at`) VALUES
(1, 'Starter Package', 'Perfect for beginners', 100.00000000, 'USDT', 100.00000000, 500.00000000, 2.5000, 30, 50.00, 1, '[\"Basic AI trading\",\"Email support\",\"Mobile app access\"]', '2025-09-22 08:20:42', '2025-09-22 08:20:42'),
(2, 'Professional Package', 'For serious traders', 500.00000000, 'USDT', 500.00000000, 2000.00000000, 3.5000, 30, 50.00, 1, '[\"Advanced AI trading\",\"Priority support\",\"Custom strategies\",\"Real-time alerts\"]', '2025-09-22 08:20:42', '2025-09-22 08:20:42'),
(3, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\",\"24\\/7 support\",\"Custom strategies\",\"Real-time alerts\",\"Personal manager\"]', '2025-09-22 08:20:42', '2025-09-22 08:20:42'),
(4, 'Starter Package', 'Perfect for beginners', 100.00000000, 'USDT', 100.00000000, 500.00000000, 2.5000, 30, 50.00, 1, '[\"Basic AI trading\",\"Email support\",\"Mobile app access\"]', '2025-09-23 02:39:50', '2025-09-23 02:39:50'),
(5, 'Professional Package', 'For serious traders', 500.00000000, 'USDT', 500.00000000, 2000.00000000, 3.5000, 30, 50.00, 1, '[\"Advanced AI trading\",\"Priority support\",\"Custom strategies\",\"Real-time alerts\"]', '2025-09-23 02:39:50', '2025-09-23 02:39:50'),
(6, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\",\"24\\/7 support\",\"Custom strategies\",\"Real-time alerts\",\"Personal manager\"]', '2025-09-23 02:39:50', '2025-09-23 02:39:50'),
(7, 'Starter Package', 'Perfect for beginners', 100.00000000, 'USDT', 100.00000000, 500.00000000, 2.5000, 30, 50.00, 1, '[\"Basic AI trading\",\"Email support\",\"Mobile app access\"]', '2025-10-08 16:17:17', '2025-10-08 16:17:17'),
(8, 'Professional Package', 'For serious traders', 500.00000000, 'USDT', 500.00000000, 2000.00000000, 3.5000, 30, 50.00, 1, '[\"Advanced AI trading\",\"Priority support\",\"Custom strategies\",\"Real-time alerts\"]', '2025-10-08 16:17:17', '2025-10-08 16:17:17'),
(9, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\",\"24\\/7 support\",\"Custom strategies\",\"Real-time alerts\",\"Personal manager\"]', '2025-10-08 16:17:17', '2025-10-08 16:17:17'),
(10, 'Starter Package', 'Perfect for beginners', 100.00000000, 'USDT', 100.00000000, 500.00000000, 2.5000, 30, 50.00, 1, '[\"Basic AI trading\",\"Email support\",\"Mobile app access\"]', '2025-10-08 16:44:12', '2025-10-08 16:44:12'),
(11, 'Professional Package', 'For serious traders', 500.00000000, 'USDT', 500.00000000, 2000.00000000, 3.5000, 30, 50.00, 1, '[\"Advanced AI trading\",\"Priority support\",\"Custom strategies\",\"Real-time alerts\"]', '2025-10-08 16:44:12', '2025-10-08 16:44:12'),
(12, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\",\"24\\/7 support\",\"Custom strategies\",\"Real-time alerts\",\"Personal manager\"]', '2025-10-08 16:44:12', '2025-10-08 16:44:12');

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
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'user-view', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03'),
(2, 'user-create', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03'),
(3, 'user-edit', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03'),
(4, 'user-delete', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03'),
(5, 'manage_users', 'web', '2025-09-22 08:20:41', '2025-09-22 08:20:41'),
(6, 'manage_trades', 'web', '2025-09-22 08:20:41', '2025-09-22 08:20:41'),
(7, 'manage_agents', 'web', '2025-09-22 08:20:41', '2025-09-22 08:20:41'),
(8, 'manage_wallets', 'web', '2025-09-22 08:20:41', '2025-09-22 08:20:41'),
(9, 'manage_transactions', 'web', '2025-09-22 08:20:41', '2025-09-22 08:20:41'),
(10, 'manage_packages', 'web', '2025-09-22 08:20:41', '2025-09-22 08:20:41'),
(11, 'view_reports', 'web', '2025-09-22 08:20:41', '2025-09-22 08:20:41'),
(12, 'manage_support', 'web', '2025-09-22 08:20:41', '2025-09-22 08:20:41');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `investment_amount` decimal(10,2) NOT NULL,
  `joining_fee` decimal(10,2) NOT NULL,
  `bots_allowed` int(11) NOT NULL,
  `trades_per_day` int(11) NOT NULL,
  `direct_bonus` decimal(10,2) NOT NULL,
  `referral_level_1` decimal(5,2) NOT NULL,
  `referral_level_2` decimal(5,2) NOT NULL,
  `referral_level_3` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `investment_amount`, `joining_fee`, `bots_allowed`, `trades_per_day`, `direct_bonus`, `referral_level_1`, `referral_level_2`, `referral_level_3`, `is_active`, `sort_order`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 100.00, 10.00, 1, 5, 5.00, 5.00, 3.00, 2.00, 1, 1, 'Perfect for beginners', '2025-10-08 16:51:29', '2025-10-08 16:51:29'),
(2, 'Bronze', 500.00, 25.00, 2, 10, 25.00, 7.00, 5.00, 3.00, 1, 2, 'Great for regular investors', '2025-10-08 16:51:29', '2025-10-08 16:51:29'),
(3, 'Silver', 1000.00, 50.00, 3, 15, 50.00, 10.00, 7.00, 5.00, 1, 3, 'Ideal for serious investors', '2025-10-08 16:51:29', '2025-10-08 16:51:29'),
(4, 'Gold', 2500.00, 125.00, 5, 25, 125.00, 12.00, 10.00, 7.00, 1, 4, 'Premium investment package', '2025-10-08 16:51:29', '2025-10-08 16:51:29'),
(5, 'Diamond', 5000.00, 250.00, 8, 40, 250.00, 15.00, 12.00, 10.00, 1, 5, 'Elite investment opportunity', '2025-10-08 16:51:29', '2025-10-08 16:51:29'),
(6, 'Platinum', 10000.00, 500.00, 12, 60, 500.00, 18.00, 15.00, 12.00, 1, 6, 'VIP investment experience', '2025-10-08 16:51:29', '2025-10-08 16:51:29'),
(7, 'Elite', 25000.00, 1250.00, 20, 100, 1250.00, 20.00, 18.00, 15.00, 1, 7, 'Ultimate investment package', '2025-10-08 16:51:29', '2025-10-08 16:51:29'),
(8, 'Basic Plan', 1000.00, 0.00, 1, 10, 0.00, 10.00, 5.00, 2.00, 1, 1, 'Basic investment plan for testing', '2025-10-18 08:10:55', '2025-10-18 08:10:55');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `id_document_type` varchar(255) DEFAULT NULL,
  `id_document_number` varchar(255) DEFAULT NULL,
  `id_document_front` varchar(255) DEFAULT NULL,
  `id_document_back` varchar(255) DEFAULT NULL,
  `kyc_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `kyc_notes` text DEFAULT NULL,
  `transaction_password` varchar(255) DEFAULT NULL,
  `referral_code` varchar(255) NOT NULL,
  `referred_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `date_of_birth`, `address`, `city`, `state`, `country`, `postal_code`, `id_document_type`, `id_document_number`, `id_document_front`, `id_document_back`, `kyc_status`, `kyc_notes`, `transaction_password`, `referral_code`, `referred_by`, `created_at`, `updated_at`) VALUES
(1, 4, 'Admin', 'User', '+1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'ADMIN001', NULL, '2025-09-22 08:20:42', '2025-09-22 08:20:42'),
(2, 5, 'John', 'Doe', '+1234567891', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '$2y$12$kM3p7RPtjIDTO9Qzxd7Dpuaeald1ATV..0QAVwjGuux2KAsSd0UYu', 'JOHN001', NULL, '2025-09-22 08:20:42', '2025-09-22 08:20:42'),
(4, 3, 'Test User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'TES9424', NULL, '2025-10-03 09:32:04', '2025-10-03 09:32:04'),
(5, 11, 'mudassar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'MUD7503', NULL, '2025-10-03 09:42:13', '2025-10-03 09:42:13');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NOT NULL,
  `referred_id` bigint(20) UNSIGNED NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 10.00,
  `total_commission` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `pending_commission` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `joined_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `referrer_id`, `referred_id`, `commission_rate`, `total_commission`, `pending_commission`, `status`, `joined_at`, `created_at`, `updated_at`) VALUES
(1, 12, 13, 10.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:10:56', '2025-10-18 08:10:56', '2025-10-18 08:10:56'),
(2, 12, 14, 10.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:10:57', '2025-10-18 08:10:57', '2025-10-18 08:10:57'),
(3, 12, 15, 10.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:10:57', '2025-10-18 08:10:57', '2025-10-18 08:10:57'),
(4, 13, 16, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:10:58', '2025-10-18 08:10:58', '2025-10-18 08:10:58'),
(5, 13, 17, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:10:58', '2025-10-18 08:10:58', '2025-10-18 08:10:58'),
(6, 14, 18, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:10:59', '2025-10-18 08:10:59', '2025-10-18 08:10:59'),
(7, 14, 19, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:10:59', '2025-10-18 08:10:59', '2025-10-18 08:10:59'),
(8, 15, 20, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:10:59', '2025-10-18 08:10:59', '2025-10-18 08:10:59'),
(9, 15, 21, 5.00, 0.00000000, 0.00000000, 'active', '2025-10-18 08:11:00', '2025-10-18 08:11:00', '2025-10-18 08:11:00');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03'),
(2, 'manager', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03'),
(3, 'staff', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03'),
(4, 'dataentry', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03'),
(5, 'customer', 'web', '2025-09-22 06:42:03', '2025-09-22 06:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1);

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
('4OLwH1i009VBKCzS7IZUNOA5zNiNn31jj5MVMMD3', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZ1IySnRnWFRWUEgzekhCbGJacEx5OHJjeHl0Yk0wbXNjMUNqYVYzaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9jdXN0b21lci93YWxsZXQvZGVwb3NpdCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==', 1760386993),
('jwwzysNxjPOSh7IPC7Et8fUsOlJSgBP3NjSorvTS', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT1lGT2Q2am5xdGJXNFBuM0dINzhYVFpxV0tEZGdWMTN2cDhmYWZwcSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyMToiaHR0cDovL2xvY2FsaG9zdDo4MDAwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1760780906),
('QA20cyts7GXxKEYy7FQbsRSr5IjbXUhbefA1YoFB', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZGNoclBZTjM0OHVZUUhDR0hpNmhEaWZlS0JHYVVzWmM1OWozVVZNUCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MDoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2N1c3RvbWVyL2Rhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1760810676),
('rBgR8w7tVkmiu6kLFWcoChKcdnVsHCLl76u3aX9I', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQlg0NmV5WWU1VVhlN01PeUxzdEVSaGcwUlg5ODFRMEZvVElqZXlwNSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9jdXN0b21lci9yZWZlcnJhbHMiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO30=', 1760792582);

-- --------------------------------------------------------

--
-- Table structure for table `trades`
--

CREATE TABLE `trades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `trade_id` varchar(255) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `side` enum('buy','sell') NOT NULL,
  `type` enum('market','limit','stop') NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `price` decimal(20,8) DEFAULT NULL,
  `stop_price` decimal(20,8) DEFAULT NULL,
  `executed_quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `average_price` decimal(20,8) DEFAULT NULL,
  `commission` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `status` enum('pending','partially_filled','filled','cancelled','rejected') NOT NULL,
  `time_in_force` enum('GTC','IOC','FOK') NOT NULL DEFAULT 'GTC',
  `profit_loss` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `profit_loss_percentage` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `exchange` varchar(20) NOT NULL,
  `exchange_order_id` varchar(255) DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `type` enum('deposit','withdrawal','transfer','trade_profit','trade_loss','commission','refund') NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `currency` varchar(10) NOT NULL,
  `amount` decimal(20,8) NOT NULL,
  `fee` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `net_amount` decimal(20,8) NOT NULL,
  `from_address` varchar(255) DEFAULT NULL,
  `to_address` varchar(255) DEFAULT NULL,
  `tx_hash` varchar(255) DEFAULT NULL,
  `confirmations` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_type` enum('customer','admin','manager','moderator') NOT NULL DEFAULT 'customer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `referral_code` varchar(255) DEFAULT NULL,
  `referred_by` bigint(20) UNSIGNED DEFAULT NULL,
  `active_plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `active_investment_amount` decimal(20,8) NOT NULL DEFAULT 0.00000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `user_type`, `is_active`, `last_login_at`, `referral_code`, `referred_by`, `active_plan_id`, `active_investment_amount`) VALUES
(1, 'Super Admin', 'admin@gmail.com', '2025-09-22 06:42:04', '$2y$12$R.wJJtqyCSj3OuOn2X/D.OicdMokoamVEv9NtYId.BGjaL.5m32/W', '2Tf12e0AwAaOxVylZ2tqGx08N2kYuAMyu3LdfnhZvW5VFwNqvIWWvaQBVUKQ', '2025-09-22 06:42:04', '2025-10-18 08:10:22', 'customer', 1, '2025-10-13 13:31:24', '4POO6CAY', NULL, NULL, 0.00000000),
(2, 'Manager User', 'test@manager.com', '2025-09-22 06:42:04', '$2y$12$aPEKy30sdI2Zs4JgeilCzu4zeNXnLUNam6q4j3tpA6/mQINGs.Ahu', 'P5mZaZ0t86', '2025-09-22 06:42:04', '2025-10-18 08:10:22', 'customer', 1, NULL, 'FVSKF0OQ', NULL, NULL, 0.00000000),
(3, 'Test User', 'test@customer.com', '2025-09-22 06:42:05', '$2y$12$oR8LUqxdS67zTbs4ZPAEd.gyF0GBznnodWXgv53p7HzsNuP9kqom.', '5re8K7YjjCllS7NGneKKiXSOF2tH9jTsuY5UVvzhjBcm6zKvc8sbBeSNoKgf', '2025-09-22 06:42:05', '2025-10-18 08:10:22', 'customer', 1, '2025-10-18 08:02:38', 'RX1YKZPS', NULL, NULL, 0.00000000),
(4, 'Admin User', 'admin@aitradeapp.com', NULL, '$2y$12$EIiTSi9h71pzA5V.kedu9./16Wvnky.emWVE2kjvP6hQ2IZdggxbm', NULL, '2025-09-22 08:20:41', '2025-09-22 08:20:41', 'admin', 1, NULL, 'ADMIN001', NULL, NULL, 0.00000000),
(5, 'John Doe', 'customer@example.com', NULL, '$2y$12$Vxr8JRPq2IHGx/hBkEz2/OCDPcxXozUPamIcyvzETyZ/MwHOc3a.y', NULL, '2025-09-22 08:20:42', '2025-09-22 08:20:42', 'customer', 1, NULL, 'JOHN001', NULL, NULL, 0.00000000),
(6, 'Test Customer', 'test@test.com', NULL, '$2y$12$QbcztFKvjuXG21d0iEiBVul8WvlXrczY97hedyeTr/w425OgPQgWi', NULL, '2025-09-22 09:08:18', '2025-10-18 08:10:22', 'customer', 1, NULL, 'KMUVSRRA', NULL, NULL, 0.00000000),
(8, 'Manager User', 'manager@aitradeapp.com', NULL, '$2y$12$9.0mDUbRG4saMcsQ7dlF3u5NbWOF5/zg7CfBM7ZUJkdKE8v2CbBxC', NULL, '2025-09-23 02:39:50', '2025-09-23 02:39:50', 'manager', 1, NULL, 'MGR001', NULL, NULL, 0.00000000),
(9, 'Test Admin', 'admin@test.com', NULL, '$2y$12$pK5ROVlXM16PtxBbZ5l6U.GKA09NmLHpxGsaB7/0ELGPDGUgvxWpi', NULL, '2025-09-23 05:38:41', '2025-10-18 08:10:22', 'admin', 1, NULL, 'XBP0ZNSM', NULL, NULL, 0.00000000),
(10, 'Test Customer', 'customer@test.com', NULL, '$2y$12$R.wJJtqyCSj3OuOn2X/D.OicdMokoamVEv9NtYId.BGjaL.5m32/W', 'W3sdBkj0fLa3g27RzGdewAvBMdiDCkJv8uwAdjZlsk8XD5eppmZ9iESp4ge5', '2025-09-23 05:38:41', '2025-10-18 08:10:22', 'customer', 1, '2025-10-03 09:28:43', 'OYZSMUJ0', NULL, NULL, 0.00000000),
(11, 'mudassar', 'mudassar@test.com', NULL, '$2y$12$e922FTE4wN8WzvxCnPzYGu5uz33h/FDiYJJwfL4iP7.OoMt47qGOi', NULL, '2025-10-03 09:42:12', '2025-10-18 08:10:22', 'customer', 1, NULL, 'KESQHEMS', NULL, NULL, 0.00000000),
(12, 'Main User', 'main@test.com', NULL, '$2y$12$veVmLuEf8RSCuUdiSB94nurREhr6fewrMRBBKsY8A2HBaOvX9mwh.', NULL, '2025-10-18 08:10:56', '2025-10-18 08:10:56', 'customer', 1, NULL, 'QH05ZVL9', NULL, NULL, 0.00000000),
(13, 'Level 1 User 1', 'level1_1@test.com', NULL, '$2y$12$DD6lLZaYCPnIKoSLxKiUYeFEw3GLiLJRAS0OkSMjykcOpJOER5k1u', NULL, '2025-10-18 08:10:56', '2025-10-18 08:10:56', 'customer', 1, NULL, 'KKINQHGR', 12, 8, 1000.00000000),
(14, 'Level 1 User 2', 'level1_2@test.com', NULL, '$2y$12$nUld18qlMZ6pr3bH5xfx7OCPZyLNcxQoLYV2Wpo6zk.MB1jsnIFC.', NULL, '2025-10-18 08:10:57', '2025-10-18 08:10:57', 'customer', 1, NULL, 'SOSYYLVS', 12, 8, 1000.00000000),
(15, 'Level 1 User 3', 'level1_3@test.com', NULL, '$2y$12$0TaXtopxKYmVVFcHIZBNoOGPjfGTuybQZNwrvlz1L3ijZBaMAC1wG', NULL, '2025-10-18 08:10:57', '2025-10-18 08:10:57', 'customer', 1, NULL, 'LYOAIOWR', 12, NULL, 0.00000000),
(16, 'Level 2 User 0-1', 'level2_0_1@test.com', NULL, '$2y$12$.oZtWr7YcHKWf1Han.B2cOCg.oosUUtV7Y3fkMrQgem2JEXXWVeEq', NULL, '2025-10-18 08:10:58', '2025-10-18 08:10:58', 'customer', 1, NULL, 'EJILJXKG', 13, NULL, 0.00000000),
(17, 'Level 2 User 0-2', 'level2_0_2@test.com', NULL, '$2y$12$tiiax4k61//m0JYd2.wEh.bjGQYe2gba5Whh3Pf6g/iNIHoMbaMdm', NULL, '2025-10-18 08:10:58', '2025-10-18 08:10:58', 'customer', 1, NULL, 'OYPX9GWA', 13, NULL, 0.00000000),
(18, 'Level 2 User 1-1', 'level2_1_1@test.com', NULL, '$2y$12$eVrPbCjh0bDtv1rOqePKGOrbQzz8CPAv0YgjX5Bit4i4Lwole4Y4C', NULL, '2025-10-18 08:10:59', '2025-10-18 08:10:59', 'customer', 1, NULL, 'UWZQZNIW', 14, NULL, 0.00000000),
(19, 'Level 2 User 1-2', 'level2_1_2@test.com', NULL, '$2y$12$DK9ziQxWCz6sgkbNTTLWH.dafE7yrxNqcCg9Uf7vNRsUSCJ5qDcA6', NULL, '2025-10-18 08:10:59', '2025-10-18 08:10:59', 'customer', 1, NULL, 'ABGPG2YL', 14, NULL, 0.00000000),
(20, 'Level 2 User 2-1', 'level2_2_1@test.com', NULL, '$2y$12$DvMJvPrpSTGVMWE84XJwC.uF6DjLHb4Gzs1kp3DgUOLx7lGYxxhoO', NULL, '2025-10-18 08:10:59', '2025-10-18 08:10:59', 'customer', 1, NULL, 'ISTERVTY', 15, NULL, 0.00000000),
(21, 'Level 2 User 2-2', 'level2_2_2@test.com', NULL, '$2y$12$VR5xhrm77V.sJHWMabC21eC5lqKH1/Nd4vpOUQCaztaBhGX8GGvya', NULL, '2025-10-18 08:11:00', '2025-10-18 08:11:00', 'customer', 1, NULL, 'MAP9EMM1', 15, NULL, 0.00000000);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USDT',
  `balance` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `locked_balance` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_deposited` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_withdrawn` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_profit` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `total_loss` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `currency`, `balance`, `locked_balance`, `total_deposited`, `total_withdrawn`, `total_profit`, `total_loss`, `created_at`, `updated_at`) VALUES
(1, 5, 'USDT', 1000.00000000, 0.00000000, 1000.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-09-22 08:20:42', '2025-09-22 08:20:42'),
(2, 3, 'USDT', 0.00000000, 0.00000000, 0.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-03 09:32:04', '2025-10-03 09:32:04'),
(3, 11, 'USDT', 0.00000000, 0.00000000, 0.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-03 09:42:13', '2025-10-03 09:42:13'),
(4, 13, 'USDT', 500.00000000, 0.00000000, 1000.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:10:56', '2025-10-18 08:10:56'),
(5, 14, 'USDT', 500.00000000, 0.00000000, 1000.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:10:57', '2025-10-18 08:10:57'),
(6, 15, 'USDT', 500.00000000, 0.00000000, 1000.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:10:57', '2025-10-18 08:10:57'),
(7, 16, 'USDT', 250.00000000, 0.00000000, 500.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:10:58', '2025-10-18 08:10:58'),
(8, 17, 'USDT', 250.00000000, 0.00000000, 500.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:10:58', '2025-10-18 08:10:58'),
(9, 18, 'USDT', 250.00000000, 0.00000000, 500.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:10:59', '2025-10-18 08:10:59'),
(10, 19, 'USDT', 250.00000000, 0.00000000, 500.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:10:59', '2025-10-18 08:10:59'),
(11, 20, 'USDT', 250.00000000, 0.00000000, 500.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:10:59', '2025-10-18 08:10:59'),
(12, 21, 'USDT', 250.00000000, 0.00000000, 500.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-18 08:11:00', '2025-10-18 08:11:00');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_addresses`
--

CREATE TABLE `wallet_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `symbol` varchar(255) NOT NULL,
  `wallet_address` varchar(255) NOT NULL,
  `network` varchar(255) DEFAULT NULL,
  `qr_code_image` varchar(255) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_addresses`
--

INSERT INTO `wallet_addresses` (`id`, `name`, `symbol`, `wallet_address`, `network`, `qr_code_image`, `instructions`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Bitcoin', 'BTC', '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 'BTC', 'qr-codes/1759959619_BTC.png', 'Only send Bitcoin (BTC) to this address. Other cryptocurrencies will be lost.', 1, 1, '2025-10-08 16:23:30', '2025-10-08 16:40:20'),
(2, 'Tether USD', 'USDT', 'TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE', 'TRC20', 'qr-codes/1759959620_USDT.png', 'Only send USDT (TRC20) to this address. Other cryptocurrencies will be lost.', 1, 2, '2025-10-08 16:23:30', '2025-10-08 16:40:20'),
(3, 'Ethereum', 'ETH', '0x742d35Cc6634C0532925a3b8D4C9db96C4b4d8b6', 'ERC20', 'qr-codes/1759959621_ETH.png', 'Only send Ethereum (ETH) to this address. Other cryptocurrencies will be lost.', 1, 3, '2025-10-08 16:23:30', '2025-10-08 16:40:21'),
(4, 'BTC', 'BTC', '2345234fdsfgsdt3fdgsdgdfdf', 'TRC20', 'qr-codes/wJurf4AZ1a4kx7xmpvKyzGSHr25KLwuNpinn84Cl.jpg', NULL, 1, 1, '2025-10-08 16:30:16', '2025-10-08 16:30:16'),
(5, 'BTC', 'BTC', 'erwtwe3245345345342523', 'TRC20', 'qr-codes/xd0mDLJeIpA8a1tbGuFf5KKLbDeQYu3wSILqYySC.jpg', 'test', 1, 1, '2025-10-08 16:30:56', '2025-10-08 16:30:56'),
(7, 'Tether USD', 'USDT', 'TXYZ1234567890abcdef', 'TRC20', NULL, NULL, 1, 1, '2025-10-13 14:17:29', '2025-10-13 14:17:29'),
(8, 'Bitcoin', 'BTC', '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 'BTC', NULL, NULL, 1, 2, '2025-10-13 14:17:35', '2025-10-13 14:17:35');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_accounts`
--
ALTER TABLE `api_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bonus_wallets`
--
ALTER TABLE `bonus_wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers_wallets`
--
ALTER TABLE `customers_wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `trades`
--
ALTER TABLE `trades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
