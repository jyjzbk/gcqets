-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-06-20 09:55:25
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `gcqets`
--

-- --------------------------------------------------------

--
-- 表的结构 `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `failed_jobs`
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
-- 表的结构 `jobs`
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
-- 表的结构 `job_batches`
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
-- 表的结构 `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_06_18_100001_create_organizations_table', 1),
(5, '2025_06_18_100002_create_roles_table', 1),
(6, '2025_06_18_100003_create_permissions_table', 1),
(7, '2025_06_18_100004_update_users_table', 1),
(8, '2025_06_18_100005_create_role_user_table', 1),
(9, '2025_06_18_100006_create_permission_role_table', 1),
(10, '2025_06_19_091006_create_personal_access_tokens_table', 1),
(11, '2025_06_19_130308_add_deleted_at_to_roles_and_permissions_tables', 1),
(12, '2025_06_20_100001_create_user_organizations_table', 2),
(13, '2025_06_20_100002_create_user_roles_table', 2),
(14, '2025_06_20_100003_create_user_permissions_table', 2);

-- --------------------------------------------------------

--
-- 表的结构 `organizations`
--

CREATE TABLE `organizations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '组织机构名称',
  `code` varchar(50) NOT NULL COMMENT '组织机构编码',
  `type` enum('province','city','district','education_zone','school') NOT NULL COMMENT '组织类型',
  `level` tinyint(4) NOT NULL COMMENT '组织层级 1-省 2-市 3-区县 4-学区 5-学校',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '父级组织ID',
  `full_path` varchar(500) DEFAULT NULL COMMENT '完整路径 如:1/2/3/4/5',
  `description` text DEFAULT NULL COMMENT '组织描述',
  `contact_person` varchar(50) DEFAULT NULL COMMENT '联系人',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `address` varchar(200) DEFAULT NULL COMMENT '地址',
  `longitude` decimal(10,7) DEFAULT NULL COMMENT '经度',
  `latitude` decimal(10,7) DEFAULT NULL COMMENT '纬度',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active' COMMENT '状态',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据JSON' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '权限名称',
  `display_name` varchar(150) NOT NULL COMMENT '权限显示名称',
  `module` varchar(50) NOT NULL COMMENT '所属模块',
  `action` varchar(50) NOT NULL COMMENT '操作动作',
  `description` text DEFAULT NULL COMMENT '权限描述',
  `min_level` tinyint(4) NOT NULL COMMENT '最小需要级别',
  `applicable_levels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '适用级别JSON数组' CHECK (json_valid(`applicable_levels`)),
  `scope_type` enum('self','direct_subordinates','all_subordinates','same_level','cross_level') NOT NULL COMMENT '权限范围类型',
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否系统权限',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `permission_role`
--

CREATE TABLE `permission_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL COMMENT '权限ID',
  `role_id` bigint(20) UNSIGNED NOT NULL COMMENT '角色ID',
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '权限条件JSON' CHECK (json_valid(`conditions`)),
  `access_type` enum('allow','deny') NOT NULL DEFAULT 'allow' COMMENT '访问类型',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 4, 'auth-token', '67d14da8b25ea1216a6d47484a905674bf4bdd18f2d09eb6fa5a7448be57426e', '[\"*\"]', NULL, NULL, '2025-06-19 23:47:57', '2025-06-19 23:47:57'),
(2, 'App\\Models\\User', 4, 'auth-token', '23cc8ed6879da35ebbc9eb93cfddb8a5b75f961b40736793fb16424361f8994e', '[\"*\"]', NULL, NULL, '2025-06-19 23:50:40', '2025-06-19 23:50:40'),
(3, 'App\\Models\\User', 4, 'auth-token', 'ba9a87340c9012a8a2b2b3a840fc96f297723871deb84734a0303e45590e4af3', '[\"*\"]', '2025-06-19 23:53:23', NULL, '2025-06-19 23:52:55', '2025-06-19 23:53:23');

-- --------------------------------------------------------

--
-- 表的结构 `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `display_name` varchar(100) NOT NULL COMMENT '角色显示名称',
  `description` text DEFAULT NULL COMMENT '角色描述',
  `role_type` enum('system','custom') NOT NULL DEFAULT 'custom' COMMENT '角色类型',
  `level` tinyint(4) NOT NULL COMMENT '角色级别 1-省级 2-市级 3-区县级 4-学区级 5-学校级',
  `applicable_org_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '适用组织类型JSON数组' CHECK (json_valid(`applicable_org_types`)),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认角色',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `role_user`
--

CREATE TABLE `role_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
  `role_id` bigint(20) UNSIGNED NOT NULL COMMENT '角色ID',
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '授权组织范围ID',
  `scope_type` enum('current_org','direct_subordinates','all_subordinates') NOT NULL DEFAULT 'current_org' COMMENT '权限范围类型',
  `effective_date` date DEFAULT NULL COMMENT '生效日期',
  `expiry_date` date DEFAULT NULL COMMENT '失效日期',
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active' COMMENT '状态',
  `remarks` text DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '创建人ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `sessions`
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
-- 转存表中的数据 `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('cfV4uHCbPxJWuVuuzR6LiBukuWESHh2LLKtb2ZyS', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR2RnR3lHMXdvTWJsalFBYXlPZEhkR2ZpbXJWd2hxTGlENlFCN1BIOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1750342559),
('UwSirPNJ6VCKork8SKrtFRuNU4Dgb9kaJKdqSpBa', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUlZIY2U0N1poalFzMlNrampETExHQjVwbXJiWmZheTlhWnlSSU1kZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1750386203);

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `real_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `gender` enum('male','female','other') DEFAULT NULL COMMENT '性别',
  `birth_date` date DEFAULT NULL COMMENT '出生日期',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号码',
  `id_card` varchar(18) DEFAULT NULL COMMENT '身份证号',
  `address` text DEFAULT NULL COMMENT '住址',
  `department` varchar(100) DEFAULT NULL COMMENT '部门',
  `position` varchar(100) DEFAULT NULL COMMENT '职位',
  `title` varchar(100) DEFAULT NULL COMMENT '职称',
  `hire_date` date DEFAULT NULL COMMENT '入职日期',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '用户偏好设置' CHECK (json_valid(`preferences`)),
  `remarks` text DEFAULT NULL COMMENT '备注',
  `email` varchar(255) NOT NULL,
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '所属组织机构ID',
  `employee_id` varchar(50) DEFAULT NULL COMMENT '工号/学号',
  `user_type` enum('admin','teacher','student','supervisor') NOT NULL DEFAULT 'teacher' COMMENT '用户类型',
  `status` enum('active','inactive','locked','pending') NOT NULL DEFAULT 'pending' COMMENT '用户状态',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `username`, `real_name`, `gender`, `birth_date`, `phone`, `id_card`, `address`, `department`, `position`, `title`, `hire_date`, `last_login_at`, `last_login_ip`, `preferences`, `remarks`, `email`, `organization_id`, `employee_id`, `user_type`, `status`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Test User', NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'test@example.com', NULL, NULL, 'teacher', 'active', '2025-06-19 05:39:11', '$2y$12$GhQOp/GjS4u.JxNpiYyydulWPbhJiWH/RiVXIwTZsIdqt.BI3hudm', 'Mrb6fr42RW', '2025-06-19 05:39:12', '2025-06-19 05:39:12', NULL),
(3, '测试用户', '测试用户', 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'test2@example.com', NULL, NULL, 'admin', 'active', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-06-19 13:51:38', '2025-06-19 13:51:38', NULL),
(4, 'admin', NULL, 'other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-19 23:52:55', '127.0.0.1', NULL, NULL, 'admin@example.com', NULL, NULL, 'teacher', 'active', NULL, '$2y$12$al/MChXb4D.vcAGfSmbhJu0/ydYsAClH5TmK1xDnvgqshRaMbcA0G', NULL, '2025-06-19 18:01:57', '2025-06-19 23:52:55', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `user_organizations`
--

CREATE TABLE `user_organizations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '组织机构ID',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否为主要组织',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
  `permission_id` bigint(20) UNSIGNED NOT NULL COMMENT '权限ID',
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '组织机构ID',
  `granted_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '授权人ID',
  `granted_at` timestamp NULL DEFAULT NULL COMMENT '授权时间',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
  `role_id` bigint(20) UNSIGNED NOT NULL COMMENT '角色ID',
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '组织机构ID',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `assigned_at` timestamp NULL DEFAULT NULL COMMENT '分配时间',
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '分配人ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- 表的索引 `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- 表的索引 `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- 表的索引 `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- 表的索引 `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organizations_code_unique` (`code`),
  ADD KEY `organizations_parent_id_level_index` (`parent_id`,`level`),
  ADD KEY `organizations_type_level_index` (`type`,`level`),
  ADD KEY `organizations_status_level_index` (`status`,`level`),
  ADD KEY `organizations_full_path_index` (`full_path`);

--
-- 表的索引 `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- 表的索引 `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD KEY `permissions_module_action_index` (`module`,`action`),
  ADD KEY `permissions_min_level_status_index` (`min_level`,`status`),
  ADD KEY `permissions_scope_type_index` (`scope_type`);

--
-- 表的索引 `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permission_role` (`permission_id`,`role_id`),
  ADD KEY `permission_role_permission_id_role_id_index` (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_access_type_index` (`role_id`,`access_type`);

--
-- 表的索引 `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- 表的索引 `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD KEY `roles_level_status_index` (`level`,`status`),
  ADD KEY `roles_role_type_index` (`role_type`);

--
-- 表的索引 `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role_org` (`user_id`,`role_id`,`organization_id`),
  ADD KEY `role_user_user_id_status_index` (`user_id`,`status`),
  ADD KEY `role_user_role_id_status_index` (`role_id`,`status`),
  ADD KEY `role_user_organization_id_scope_type_index` (`organization_id`,`scope_type`),
  ADD KEY `role_user_effective_date_expiry_date_index` (`effective_date`,`expiry_date`),
  ADD KEY `role_user_created_by_foreign` (`created_by`);

--
-- 表的索引 `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `unique_employee_org` (`employee_id`,`organization_id`),
  ADD KEY `users_organization_id_index` (`organization_id`),
  ADD KEY `users_employee_id_index` (`employee_id`),
  ADD KEY `users_user_type_status_index` (`user_type`,`status`),
  ADD KEY `users_phone_index` (`phone`);

--
-- 表的索引 `user_organizations`
--
ALTER TABLE `user_organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_organization` (`user_id`,`organization_id`),
  ADD KEY `user_organizations_organization_id_foreign` (`organization_id`),
  ADD KEY `user_organizations_user_id_organization_id_index` (`user_id`,`organization_id`);

--
-- 表的索引 `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission_org` (`user_id`,`permission_id`,`organization_id`),
  ADD KEY `user_permissions_permission_id_foreign` (`permission_id`),
  ADD KEY `user_permissions_granted_by_foreign` (`granted_by`),
  ADD KEY `user_permissions_user_id_permission_id_index` (`user_id`,`permission_id`),
  ADD KEY `user_permissions_organization_id_index` (`organization_id`);

--
-- 表的索引 `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role_org` (`user_id`,`role_id`,`organization_id`),
  ADD KEY `user_roles_role_id_foreign` (`role_id`),
  ADD KEY `user_roles_assigned_by_foreign` (`assigned_by`),
  ADD KEY `user_roles_user_id_role_id_index` (`user_id`,`role_id`),
  ADD KEY `user_roles_organization_id_index` (`organization_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用表AUTO_INCREMENT `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `user_organizations`
--
ALTER TABLE `user_organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `organizations`
--
ALTER TABLE `organizations`
  ADD CONSTRAINT `organizations_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- 限制表 `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- 限制表 `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `role_user_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL;

--
-- 限制表 `user_organizations`
--
ALTER TABLE `user_organizations`
  ADD CONSTRAINT `user_organizations_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_organizations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_permissions_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_roles_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
