-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2026 at 11:34 AM
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
-- Database: `seeds_bazar`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Vegetables', 'vegetables', 0, 1, '2026-06-01 01:20:25', '2026-06-01 01:20:25'),
(2, 'Fruits', 'fruits', 1, 1, '2026-06-01 01:20:25', '2026-06-01 01:20:25'),
(3, 'Flowers', 'flowers', 2, 1, '2026-06-01 01:20:25', '2026-06-01 01:20:25'),
(4, 'Grains', 'grains', 3, 0, '2026-06-01 01:20:25', '2026-06-01 03:26:31'),
(5, 'Plants', 'plants', 4, 1, '2026-08-03 06:50:03', '2026-08-03 06:50:03');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `mobile` varchar(25) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `query` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `mobile`, `email`, `address`, `query`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(4, 'sadasdsd', '54654654654654', 'aaamin@yopmail.com', '200 Goold Street Racine, WI 53402', 'aaaaa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-02 04:47:29', '2026-06-02 04:47:29');

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
(4, '2026_06_01_000001_add_is_admin_to_users_table', 1),
(5, '2026_06_01_000002_create_categories_table', 1),
(6, '2026_06_01_000003_create_products_table', 1),
(7, '2026_06_01_000004_add_image_to_products_table', 2),
(8, '2026_06_01_000005_add_is_active_to_categories_table', 3),
(9, '2026_06_01_000006_create_settings_table', 4),
(10, '2026_06_02_074612_create_contact_messages_table', 5),
(11, '2026_06_02_075204_add_address_to_contact_messages_table', 6);

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
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `emoji` varchar(16) NOT NULL DEFAULT '?',
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `unit`, `emoji`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tomato Hybrid F1', 'High-yield hybrid tomato seeds, disease resistant.', 120.00, '50g pack', '🍅', 'products/l8InSQN9e5dFjxMSmomH7HBZ88qmyG2rzpk74CBx.png', 1, '2026-06-01 01:20:25', '2026-06-01 02:08:03'),
(2, 1, 'Green Chilli', 'Spicy green chilli variety, ideal for Indian climate.', 80.00, '25g pack', '🌶️', 'products/RwRA8FMFSHrlp1w3mPSnJSnfx2zat1YhgKNC1w4G.png', 1, '2026-06-01 01:20:25', '2026-06-01 01:59:42'),
(3, 1, 'Brinjal (Baingan)', 'Purple long brinjal seeds with excellent germination.', 95.00, '50g pack', '🍆', 'products/Dtq1dQU0uDuA8KUEgoVDExDf3CI2D5Tz23Ejv6rs.png', 1, '2026-06-01 01:20:25', '2026-06-01 02:10:08'),
(4, 1, 'Okra (Bhindi)', 'Tender okra variety, suitable for kharif season.', 75.00, '100g pack', '🥒', 'products/eyDKYYXs8omwtfLgrgh77gLWbFEPutY4IVAsOkNI.png', 1, '2026-06-01 01:20:25', '2026-06-01 02:04:54'),
(5, 1, 'Spinach (Palak)', 'Nutritious leafy green, quick growing.', 60.00, '100g pack', '🥬', 'products/RlFBprlVffvaCyKBB7QJUpRXbyEjoL5ohzwjsGL5.png', 1, '2026-06-01 01:20:25', '2026-06-01 02:06:18'),
(6, 2, 'Watermelon', 'Sweet red flesh watermelon, summer favourite.', 150.00, '50g pack', '🍉', 'products/9IP8SZeBrrNcR2wBQcHweaEPudfF9aosKmIi0Ncf.png', 1, '2026-06-01 01:20:25', '2026-06-01 02:14:39'),
(7, 2, 'Muskmelon', 'Aromatic muskmelon with high sugar content.', 130.00, '50g pack', '🍈', 'products/nAV19NORNidnzZqLytz4sPy0hBHiKuVXWgbMViAY.png', 1, '2026-06-01 01:20:25', '2026-06-01 02:13:00'),
(8, 2, 'Papaya', 'Dwarf papaya variety for home gardens.', 110.00, '25 seeds', '🥭', 'products/h7I0eLSkbHXymqA7nukqsSoWky4lw1kLxQwbsKgZ.jpg', 1, '2026-06-01 01:20:25', '2026-06-01 02:18:56'),
(9, 3, 'Marigold Orange', 'Bright orange marigold for borders & festivals.', 55.00, '10g pack', '🌼', 'products/UnVyUG6b3FRvXawZjO5g3racu1nYLeMXbj52qxID.jpg', 1, '2026-06-01 01:20:25', '2026-06-01 02:26:40'),
(10, 3, 'Sunflower Giant', 'Tall sunflower with large blooms.', 90.00, '50g pack', '🌻', 'products/ESIm4kDkWwsLgLdpDKxoUztRHtUyNj5JyGT4LW0p.jpg', 1, '2026-06-01 01:20:25', '2026-06-01 02:23:51'),
(11, 3, 'Zinnia', 'Zinnia Double Scarlet Mixed Color Flower Seeds produce vibrant, double-petaled blooms in a beautiful mix of colors, perfect for gardens, borders, and pots', 140.00, '20 seeds', '🌱', 'products/mdz7vU1Bs6E5eEwD0DnRBLHrEEmbsuXoeqq9RfD1.jpg', 1, '2026-06-01 01:20:25', '2026-06-01 03:21:38'),
(12, 4, 'Wheat HD-2967', 'High-yield wheat variety for rabi season.', 200.00, '1 kg pack', '🌾', NULL, 1, '2026-06-01 01:20:25', '2026-06-01 01:20:25'),
(13, 4, 'Paddy Basmati', 'Premium basmati paddy seeds.', 250.00, '1 kg pack', '🍚', NULL, 1, '2026-06-01 01:20:25', '2026-06-01 01:20:25'),
(14, 4, 'Mustard (Sarson)', 'Oil-rich mustard seeds for winter crop.', 85.00, '500g pack', '🌿', NULL, 1, '2026-06-01 01:20:25', '2026-06-01 01:20:25'),
(15, 5, 'Mango', 'Mango pants, designed for comfort and everyday elegance. Made from high-quality, breathable fabric, they offer a flattering fit with a modern silhouette. Perfect for work, casual outings, or special occasions, these versatile pants pair effortlessly with shirts, blouses, or T-shirts.', 100.00, '50g pack', '🌱', 'products/JZsMDLSKqNhsp5VfIipUnGdjj6TVq5sITY0PknQl.webp', 1, '2026-08-03 07:06:48', '2026-08-03 07:07:33'),
(16, 5, 'Banana', 'Enjoy the natural sweetness and rich nutrition of our fresh bananas. Carefully selected for quality and freshness, these bananas are soft, delicious, and perfect for snacking, smoothies, desserts, or breakfast. Packed with essential vitamins, minerals, and dietary fiber, they make a healthy choice for the whole family.', 25.00, '25g pack', '🌱', 'products/24nO8daArmX7mrrDG7E8dqSKXpiD6IdBx1KVmoBv.webp', 1, '2026-08-03 07:08:52', '2026-08-03 07:09:30'),
(18, 5, 'Lechi', 'Lechi (most commonly spelled lychee) is a sweet tropical fruit, an Italian noble family name, and a village in Iran. It features rough pink-red skin, white translucent flesh, and a large seed inside', 199.00, '20', '🌱', 'products/jpbrYmcxW2qZqmb58ArLrVyCqWG8PZRLsI5Jed30.webp', 1, '2026-08-03 07:14:33', '2026-08-03 07:14:33');

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
('2VSnqksaRg1pN1nBF1O5EMbd3FP9TRSmbggmQD0k', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWUN2R0dwd251eGVlUW5xd3NFaXhVb0diUmVnODdaTHlMSGVWcjh6diI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoxMDoic2hvcC5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1785817017),
('eCZgxcnhyEpjNyEWb9qUUnPM9yZXMxi6KBP9feqb', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRks1eWRMczBhczYxVXFIaEowRHJaYzF3MXA2Ull1akcxTVVxQko1ciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoxMDoic2hvcC5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1785910775),
('i006AGfagTiRjEzGwznazUO5ZD7ZFqKXIRcgaph2', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZno0cGRucTJlekZKT1pVUHRJTjV4WUVpVGJORmRGZzRWVThhN3lLcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoxMDoic2hvcC5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1785761079),
('qgb0AApSVbTZAls0bsk61egS63P9JkfcNrPguc1q', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid09TcHY2YTJVR0R4RFloMDhLckhzblhpd3Z0UEFJcnV3elZnWUhaVSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoxMDoic2hvcC5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1785816967);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
('site_logo', 'settings/p6HmpvpiUbCIwZj8chSO75TL8Uwb0zIjMGJtdMVY.png', '2026-06-01 23:49:02', '2026-07-29 00:53:13');

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
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_admin`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Seeds Admin', 'seeds@gmail.com', NULL, '$2y$12$lweQgFDyItdMskc8H4uiyOUHh.CBR/GRNp5YTfSgZS6O.jBPZ.jpm', 1, NULL, '2026-06-01 01:20:25', '2026-06-01 01:20:25');

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
