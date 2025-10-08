-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 03, 2025 at 02:43 PM
-- Server version: 12.0.2-MariaDB-log
-- PHP Version: 8.3.24

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
(19, '2025_09_23_073924_update_user_type_enum', 4);

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
(5, 'App\\Models\\User', 11);

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
(6, 'Premium Package', 'Maximum returns', 1000.00000000, 'USDT', 1000.00000000, 5000.00000000, 5.0000, 30, 50.00, 1, '[\"Premium AI trading\",\"24\\/7 support\",\"Custom strategies\",\"Real-time alerts\",\"Personal manager\"]', '2025-09-23 02:39:50', '2025-09-23 02:39:50');

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
('zuSKIxl7I00kayXtrmS6DalhVACjNOWW4FpADiga', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNVRNTTh5QjJLT25PeEF3UjB3eHRoUzV0U1dxdTdmSVV3Rlg0N09jZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9jdXN0b21lci90cmFkaW5nIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7fQ==', 1759502539);

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
  `referred_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `user_type`, `is_active`, `last_login_at`, `referral_code`, `referred_by`) VALUES
(1, 'Super Admin', 'admin@gmail.com', '2025-09-22 06:42:04', '$2y$12$R.wJJtqyCSj3OuOn2X/D.OicdMokoamVEv9NtYId.BGjaL.5m32/W', 'pGEaFr16ryfS9hvpP62XUnO9sEvRLulHK8Q4sptfyVmVSS3s5CUmmdF8CWFE', '2025-09-22 06:42:04', '2025-10-03 09:12:48', 'customer', 1, '2025-10-03 09:12:48', NULL, NULL),
(2, 'Manager User', 'test@manager.com', '2025-09-22 06:42:04', '$2y$12$aPEKy30sdI2Zs4JgeilCzu4zeNXnLUNam6q4j3tpA6/mQINGs.Ahu', 'P5mZaZ0t86', '2025-09-22 06:42:04', '2025-09-22 06:42:04', 'customer', 1, NULL, NULL, NULL),
(3, 'Test User', 'test@customer.com', '2025-09-22 06:42:05', '$2y$12$oR8LUqxdS67zTbs4ZPAEd.gyF0GBznnodWXgv53p7HzsNuP9kqom.', 'FJThu7O1DdrnDtyZLkwpmZPzofR6EPi4405PxitRHsRQ687X7sO3V1q7waPR', '2025-09-22 06:42:05', '2025-10-03 09:32:03', 'customer', 1, '2025-10-03 09:32:03', NULL, NULL),
(4, 'Admin User', 'admin@aitradeapp.com', NULL, '$2y$12$EIiTSi9h71pzA5V.kedu9./16Wvnky.emWVE2kjvP6hQ2IZdggxbm', NULL, '2025-09-22 08:20:41', '2025-09-22 08:20:41', 'admin', 1, NULL, 'ADMIN001', NULL),
(5, 'John Doe', 'customer@example.com', NULL, '$2y$12$Vxr8JRPq2IHGx/hBkEz2/OCDPcxXozUPamIcyvzETyZ/MwHOc3a.y', NULL, '2025-09-22 08:20:42', '2025-09-22 08:20:42', 'customer', 1, NULL, 'JOHN001', NULL),
(6, 'Test Customer', 'test@test.com', NULL, '$2y$12$QbcztFKvjuXG21d0iEiBVul8WvlXrczY97hedyeTr/w425OgPQgWi', NULL, '2025-09-22 09:08:18', '2025-09-22 09:08:18', 'customer', 1, NULL, NULL, NULL),
(8, 'Manager User', 'manager@aitradeapp.com', NULL, '$2y$12$9.0mDUbRG4saMcsQ7dlF3u5NbWOF5/zg7CfBM7ZUJkdKE8v2CbBxC', NULL, '2025-09-23 02:39:50', '2025-09-23 02:39:50', 'manager', 1, NULL, 'MGR001', NULL),
(9, 'Test Admin', 'admin@test.com', NULL, '$2y$12$pK5ROVlXM16PtxBbZ5l6U.GKA09NmLHpxGsaB7/0ELGPDGUgvxWpi', NULL, '2025-09-23 05:38:41', '2025-09-23 05:38:41', 'admin', 1, NULL, NULL, NULL),
(10, 'Test Customer', 'customer@test.com', NULL, '$2y$12$R.wJJtqyCSj3OuOn2X/D.OicdMokoamVEv9NtYId.BGjaL.5m32/W', 'W3sdBkj0fLa3g27RzGdewAvBMdiDCkJv8uwAdjZlsk8XD5eppmZ9iESp4ge5', '2025-09-23 05:38:41', '2025-10-03 09:28:43', 'customer', 1, '2025-10-03 09:28:43', NULL, NULL),
(11, 'mudassar', 'mudassar@test.com', NULL, '$2y$12$e922FTE4wN8WzvxCnPzYGu5uz33h/FDiYJJwfL4iP7.OoMt47qGOi', NULL, '2025-10-03 09:42:12', '2025-10-03 09:42:12', 'customer', 1, NULL, NULL, NULL);

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
(3, 11, 'USDT', 0.00000000, 0.00000000, 0.00000000, 0.00000000, 0.00000000, 0.00000000, '2025-10-03 09:42:13', '2025-10-03 09:42:13');

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
  ADD KEY `users_referred_by_foreign` (`referred_by`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallets_user_id_currency_unique` (`user_id`,`currency`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
