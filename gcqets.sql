-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-07-02 05:55:24
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
-- 表的结构 `district_assignment_history`
--

CREATE TABLE `district_assignment_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL COMMENT '学校ID',
  `old_district_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '原学区ID',
  `new_district_id` bigint(20) UNSIGNED NOT NULL COMMENT '新学区ID',
  `assignment_type` enum('auto','manual','import') NOT NULL COMMENT '划分类型',
  `reason` text DEFAULT NULL COMMENT '调整原因',
  `assignment_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '划分数据(距离、规模等)' CHECK (json_valid(`assignment_data`)),
  `operated_by` bigint(20) UNSIGNED NOT NULL COMMENT '操作人',
  `effective_date` timestamp NULL DEFAULT NULL COMMENT '生效日期',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `district_boundaries`
--

CREATE TABLE `district_boundaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `education_district_id` bigint(20) UNSIGNED NOT NULL COMMENT '学区组织ID',
  `name` varchar(100) NOT NULL COMMENT '边界名称',
  `boundary_points` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '边界坐标点集合' CHECK (json_valid(`boundary_points`)),
  `center_latitude` decimal(10,8) DEFAULT NULL COMMENT '中心纬度',
  `center_longitude` decimal(11,8) DEFAULT NULL COMMENT '中心经度',
  `area_size` decimal(10,2) DEFAULT NULL COMMENT '覆盖面积(平方公里)',
  `school_count` int(11) NOT NULL DEFAULT 0 COMMENT '包含学校数量',
  `total_students` int(11) NOT NULL DEFAULT 0 COMMENT '总学生数',
  `description` text DEFAULT NULL COMMENT '描述',
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '创建者',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新者',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `district_boundaries`
--

INSERT INTO `district_boundaries` (`id`, `education_district_id`, `name`, `boundary_points`, `center_latitude`, `center_longitude`, `area_size`, `school_count`, `total_students`, `description`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 4, '廉州学区边界', '\"[{\\\"lat\\\":38.045,\\\"lng\\\":114.84},{\\\"lat\\\":38.045,\\\"lng\\\":114.855},{\\\"lat\\\":38.03,\\\"lng\\\":114.855},{\\\"lat\\\":38.03,\\\"lng\\\":114.84},{\\\"lat\\\":38.045,\\\"lng\\\":114.84}]\"', 38.04000000, 114.84500000, 7.39, 3, 2170, '廉州学区管辖范围', 'active', 1, NULL, '2025-06-21 06:34:10', '2025-06-23 06:23:14');

-- --------------------------------------------------------

--
-- 表的结构 `education_zones`
--

CREATE TABLE `education_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '学区名称',
  `code` varchar(255) NOT NULL COMMENT '学区编码',
  `district_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属区县ID',
  `description` text DEFAULT NULL COMMENT '学区描述',
  `boundary_points` varchar(255) DEFAULT NULL COMMENT '边界点坐标集合',
  `center_longitude` decimal(10,7) DEFAULT NULL COMMENT '中心点经度',
  `center_latitude` decimal(10,7) DEFAULT NULL COMMENT '中心点纬度',
  `area` decimal(10,2) DEFAULT NULL COMMENT '面积(平方公里)',
  `school_count` int(11) NOT NULL DEFAULT 0 COMMENT '学校数量',
  `student_capacity` int(11) NOT NULL DEFAULT 0 COMMENT '学生容量',
  `current_students` int(11) NOT NULL DEFAULT 0 COMMENT '当前学生数',
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '学区管理员ID',
  `manager_name` varchar(255) DEFAULT NULL COMMENT '学区管理员姓名',
  `manager_phone` varchar(255) DEFAULT NULL COMMENT '学区管理员电话',
  `status` enum('active','inactive','planning') NOT NULL DEFAULT 'active' COMMENT '状态',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
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
-- 表的结构 `import_logs`
--

CREATE TABLE `import_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '导入用户ID',
  `file_name` varchar(255) NOT NULL COMMENT '导入文件名',
  `file_path` varchar(255) NOT NULL COMMENT '导入文件路径',
  `import_type` varchar(255) NOT NULL COMMENT '导入类型',
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending' COMMENT '导入状态',
  `total_rows` int(11) NOT NULL DEFAULT 0 COMMENT '总行数',
  `processed_rows` int(11) NOT NULL DEFAULT 0 COMMENT '已处理行数',
  `success_rows` int(11) NOT NULL DEFAULT 0 COMMENT '成功行数',
  `failed_rows` int(11) NOT NULL DEFAULT 0 COMMENT '失败行数',
  `error_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '错误详情' CHECK (json_valid(`error_details`)),
  `import_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '导入选项' CHECK (json_valid(`import_options`)),
  `started_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(12, '2025_06_20_100001_create_user_organizations_table', 1),
(13, '2025_06_20_100003_create_user_permissions_table', 1),
(14, '2025_06_20_100004_add_parent_id_to_permissions_table', 1),
(15, '2025_06_20_100005_add_username_to_users_table', 1),
(16, '2025_06_21_070941_add_expires_at_to_user_permissions_table', 2),
(18, '2025_06_21_110539_create_organization_import_logs_table', 3),
(19, '2025_06_21_135355_create_school_locations_table', 4),
(20, '2025_06_21_135525_create_district_boundaries_table', 4),
(22, '2025_06_21_135654_create_district_boundaries_table', 5),
(24, '2025_06_21_135913_create_district_assignment_history_table', 6),
(25, '2025_06_21_160001_create_permission_inheritance_table', 7),
(26, '2025_06_21_160002_create_permission_audit_logs_table', 7),
(27, '2025_06_21_160003_create_permission_templates_table', 7),
(28, '2025_06_21_160004_create_permission_conflicts_table', 7),
(29, '2025_06_25_071329_add_school_fields_to_organizations_table', 8),
(30, '2025_06_25_071350_create_import_logs_table', 8),
(31, '2025_06_25_071407_create_permission_templates_table', 9),
(32, '2025_06_25_071427_create_template_permissions_table', 9),
(33, '2025_06_25_071446_create_education_zones_table', 9),
(34, '2025_06_25_071500_create_zone_schools_table', 9),
(35, '2025_06_25_080001_create_permission_inheritance_table', 10),
(36, '2025_06_25_080002_create_permission_audit_logs_table', 10),
(37, '2025_06_25_080003_create_permission_conflicts_table', 10),
(38, '2025_06_25_112210_create_school_import_logs_table', 11),
(39, '2025_06_27_143637_add_primary_organization_id_to_users_table', 12),
(40, '2025_06_28_020000_add_source_to_user_permissions_table', 13);

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
  `deleted_at` timestamp NULL DEFAULT NULL,
  `student_count` int(11) DEFAULT NULL COMMENT '学生数量',
  `campus_area` decimal(10,2) DEFAULT NULL COMMENT '校园面积(平方米)',
  `school_code` varchar(50) DEFAULT NULL COMMENT '学校代码',
  `principal_name` varchar(50) DEFAULT NULL COMMENT '校长姓名',
  `principal_phone` varchar(20) DEFAULT NULL COMMENT '校长联系电话',
  `principal_email` varchar(100) DEFAULT NULL COMMENT '校长邮箱',
  `founded_year` year(4) DEFAULT NULL COMMENT '建校年份',
  `school_type` enum('public','private','other') DEFAULT NULL COMMENT '学校类型',
  `education_level` enum('primary','middle','high','vocational','comprehensive') DEFAULT NULL COMMENT '教育阶段'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `code`, `type`, `level`, `parent_id`, `full_path`, `description`, `contact_person`, `contact_phone`, `address`, `longitude`, `latitude`, `status`, `sort_order`, `extra_data`, `created_at`, `updated_at`, `deleted_at`, `student_count`, `campus_area`, `school_code`, `principal_name`, `principal_phone`, `principal_email`, `founded_year`, `school_type`, `education_level`) VALUES
(1, '河北省', 'HB001', 'province', 1, NULL, '1', '河北省教育厅', '张省长', '0311-12345678', '河北省石家庄市长安区', NULL, NULL, 'active', 1, NULL, '2025-06-20 01:16:54', '2025-06-20 01:16:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '石家庄市', 'SJZ001', 'city', 2, 1, '1/2', '石家庄市教育局', '李市长', '0311-87654321', '河北省石家庄市长安区中山路', NULL, NULL, 'active', 1, NULL, '2025-06-20 01:16:54', '2025-06-20 01:16:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, '藁城区', 'GC001', 'district', 3, 2, '1/2/3', '藁城区教育局', '王区长', '0311-88123456', '河北省石家庄市藁城区府前街', NULL, NULL, 'active', 1, NULL, '2025-06-20 01:16:54', '2025-06-20 01:16:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '廉州学区', 'LZ001', 'education_zone', 4, 3, '1/2/3/4', '廉州学区管理委员会', '赵主任', '0311-88234567', '河北省石家庄市藁城区廉州镇', NULL, NULL, 'active', 1, NULL, '2025-06-20 01:16:54', '2025-06-20 01:16:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, '东城小学', 'DC001', 'school', 5, 4, '1/2/3/4/5', '藁城区廉州学区东城小学', '刘校长', '0311-88345678', '河北省石家庄市藁城区廉州镇东城村', NULL, NULL, 'active', 1, NULL, '2025-06-20 01:16:55', '2025-06-20 01:16:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, '西城小学', 'XC001', 'school', 5, 4, '1/2/3/4/6', '藁城区廉州学区西城小学', '陈校长', '0311-88345679', '河北省石家庄市藁城区廉州镇西城村', NULL, NULL, 'active', 2, NULL, '2025-06-20 01:16:55', '2025-06-20 01:16:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, '南城小学', 'NC001', 'school', 5, 4, '1/2/3/4/7', '藁城区廉州学区南城小学', '孙校长', '0311-88345680', '河北省石家庄市藁城区廉州镇南城村', NULL, NULL, 'active', 3, NULL, '2025-06-20 01:16:55', '2025-06-26 16:38:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, '测试小学A', 'TEST001', 'province', 5, 4, NULL, '测试导入的学校A', '张校长', '0311-88111111', '测试地址A', NULL, NULL, 'active', 0, NULL, '2025-06-21 05:24:52', '2025-06-21 05:24:52', '2025-06-21 05:24:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, '测试小学B', 'TEST002', 'province', 5, 4, NULL, '测试导入的学校B', '李校长', '0311-88222222', '测试地址B', NULL, NULL, 'active', 0, NULL, '2025-06-21 05:24:52', '2025-06-21 05:24:52', '2025-06-21 05:24:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, '兴安学区', 'XA001', 'province', 4, 3, '1/2/3/10', '藁城区兴安镇', '11', '14129112344', '藁城区兴安镇', NULL, NULL, 'active', 1, NULL, '2025-06-26 06:25:33', '2025-06-27 04:29:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '兴安小学11', 'XX001', 'province', 5, 10, '10/11', '藁城区兴安镇', '22', '1234567', '藁城区兴安镇', NULL, NULL, 'active', 1, NULL, '2025-06-26 06:29:03', '2025-06-26 18:28:24', '2025-06-26 18:28:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, '兴安小学', 'XX002', 'school', 5, 10, '1/2/3/10/12', '藁城区兴安镇', '李明', '1234567', '藁城区兴安镇', NULL, NULL, 'active', 7, NULL, '2025-06-26 18:30:19', '2025-06-27 05:07:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, '大同小学', 'DT001', 'school', 5, 16, '1/2/3/16/13', '地', '帮', '1234566', '藁城区寺', NULL, NULL, 'active', 10, NULL, '2025-06-27 00:58:34', '2025-06-27 05:07:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, '兴安镇中', 'XA100', 'school', 5, 10, '1/2/3/10/14', 'aa', 'aa', NULL, 'aa', NULL, NULL, 'active', 11, NULL, '2025-06-27 01:01:49', '2025-06-27 05:07:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, '张村小学', 'ZC001', 'school', 5, 10, '1/2/3/10/15', '11', 'zz', '1234565', '11', NULL, NULL, 'active', 11, NULL, '2025-06-27 01:48:58', '2025-06-27 05:07:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '岗上学区', 'GS001', 'province', 4, 3, '1/2/3/16', 'qwe', '李明', '88934522', '藁城区', NULL, NULL, 'active', 14, NULL, '2025-06-27 03:53:00', '2025-06-27 04:29:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '邢台市', 'XT001', 'province', 2, 1, '1/17', '11', '李市长', '99876102', '11', NULL, NULL, 'active', 6, NULL, '2025-06-27 04:01:31', '2025-06-27 04:01:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, '无极县', 'WJ001', 'province', 3, 2, '1/2/18', '从', '从', NULL, '从', NULL, NULL, 'active', 0, NULL, '2025-06-27 04:02:48', '2025-06-27 04:29:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, '角中小学', 'JZ001', 'school', 5, 10, '10/3/2/1/19', 'jjjzzz', 'jjj', NULL, 'jjjzzz', NULL, NULL, 'active', 0, NULL, '2025-06-27 05:11:45', '2025-06-27 05:36:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, '大同镇中', 'DT002', 'school', 5, 16, '16/3/2/1/20', 'DDD', 'DD', '1232344', 'S在', NULL, NULL, 'active', 0, NULL, '2025-06-27 05:38:10', '2025-06-27 05:49:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `organization_import_logs`
--

CREATE TABLE `organization_import_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL COMMENT '导入文件名',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '导入用户ID',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '父级组织ID',
  `total_rows` int(11) NOT NULL DEFAULT 0 COMMENT '总行数',
  `success_count` int(11) NOT NULL DEFAULT 0 COMMENT '成功数量',
  `failed_count` int(11) NOT NULL DEFAULT 0 COMMENT '失败数量',
  `errors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '错误信息' CHECK (json_valid(`errors`)),
  `warnings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '警告信息' CHECK (json_valid(`warnings`)),
  `status` enum('processing','completed','failed') NOT NULL DEFAULT 'processing' COMMENT '状态',
  `remarks` text DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `organization_import_logs`
--

INSERT INTO `organization_import_logs` (`id`, `filename`, `user_id`, `parent_id`, `total_rows`, `success_count`, `failed_count`, `errors`, `warnings`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'test_organizations.csv', 1, 4, 2, 2, 0, '[]', '[]', 'completed', NULL, '2025-06-21 05:24:51', '2025-06-21 05:24:52');

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
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '父级权限ID',
  `path` varchar(255) DEFAULT NULL COMMENT '权限路径',
  `name` varchar(100) NOT NULL COMMENT '权限名称',
  `display_name` varchar(150) NOT NULL COMMENT '权限显示名称',
  `module` varchar(50) NOT NULL COMMENT '所属模块',
  `group` varchar(255) DEFAULT NULL COMMENT '权限分组',
  `action` varchar(50) NOT NULL COMMENT '操作动作',
  `resource` varchar(255) DEFAULT NULL COMMENT '资源',
  `method` varchar(255) DEFAULT NULL COMMENT 'HTTP方法',
  `route` varchar(255) DEFAULT NULL COMMENT '路由名称',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `description` text DEFAULT NULL COMMENT '权限描述',
  `min_level` tinyint(4) NOT NULL COMMENT '最小需要级别',
  `applicable_levels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '适用级别JSON数组' CHECK (json_valid(`applicable_levels`)),
  `scope_type` enum('self','direct_subordinates','all_subordinates','same_level','cross_level') NOT NULL COMMENT '权限范围类型',
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否系统权限',
  `is_menu` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否为菜单',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `permissions`
--

INSERT INTO `permissions` (`id`, `parent_id`, `path`, `name`, `display_name`, `module`, `group`, `action`, `resource`, `method`, `route`, `icon`, `description`, `min_level`, `applicable_levels`, `scope_type`, `is_system`, `is_menu`, `status`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, NULL, 'system.manage', '系统管理', 'system', NULL, 'manage', NULL, NULL, NULL, NULL, '系统管理权限', 1, '[1,2,3,4,5]', 'all_subordinates', 1, 0, 'active', 1, '2025-06-20 01:16:55', '2025-06-20 01:16:55', NULL),
(2, NULL, NULL, 'organization.view', '查看组织机构', 'organization', NULL, 'view', NULL, NULL, NULL, NULL, '查看组织机构信息', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 2, '2025-06-20 01:16:55', '2025-06-20 01:16:55', NULL),
(3, NULL, NULL, 'organization.create', '创建组织机构', 'organization', NULL, 'create', NULL, NULL, NULL, NULL, '创建下级组织机构', 1, '[1,2,3,4]', 'direct_subordinates', 0, 0, 'active', 3, '2025-06-20 01:16:55', '2025-06-22 02:20:33', NULL),
(4, NULL, NULL, 'organization.edit', '编辑组织机构', 'organization', NULL, 'edit', NULL, NULL, NULL, NULL, '编辑组织机构信息', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 4, '2025-06-20 01:16:55', '2025-06-20 01:16:55', NULL),
(5, NULL, NULL, 'user.view', '查看用户', 'user', NULL, 'view', NULL, NULL, NULL, NULL, '查看用户信息', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 5, '2025-06-20 01:16:55', '2025-06-20 01:16:55', NULL),
(6, NULL, NULL, 'user.create', '创建用户', 'user', NULL, 'create', NULL, NULL, NULL, NULL, '创建新用户', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 6, '2025-06-20 01:16:55', '2025-06-22 02:20:34', NULL),
(7, NULL, NULL, 'user.edit', '编辑用户', 'user', NULL, 'edit', NULL, NULL, NULL, NULL, '编辑用户信息', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 7, '2025-06-20 01:16:55', '2025-06-20 01:16:55', NULL),
(8, NULL, NULL, 'role.view', '查看角色', 'role', NULL, 'view', NULL, NULL, NULL, NULL, '查看角色信息', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 8, '2025-06-20 01:16:55', '2025-06-20 01:16:55', NULL),
(9, NULL, NULL, 'role.assign', '分配角色', 'role', NULL, 'assign', NULL, NULL, NULL, NULL, '为用户分配角色', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 11, '2025-06-20 01:16:56', '2025-06-21 03:09:35', NULL),
(10, NULL, NULL, 'experiment.view', '查看实验', 'experiment', NULL, 'view', NULL, NULL, NULL, NULL, '查看实验信息', 5, '[5]', 'self', 0, 0, 'active', 10, '2025-06-20 01:16:56', '2025-06-20 01:16:56', NULL),
(11, NULL, NULL, 'experiment.manage', '管理实验', 'experiment', NULL, 'manage', NULL, NULL, NULL, NULL, '管理实验教学', 5, '[5]', 'self', 0, 0, 'active', 11, '2025-06-20 01:16:56', '2025-06-20 01:16:56', NULL),
(12, NULL, NULL, 'experiment.report', '实验报告', 'experiment', NULL, 'report', NULL, NULL, NULL, NULL, '查看和管理实验报告', 4, '[4,5]', 'all_subordinates', 0, 0, 'active', 12, '2025-06-20 01:16:56', '2025-06-20 01:16:56', NULL),
(13, NULL, NULL, 'organization.update', '更新组织机构', 'organization', NULL, 'update', NULL, NULL, NULL, NULL, '更新组织机构信息', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 5, '2025-06-20 02:37:46', '2025-06-22 02:20:34', NULL),
(14, NULL, NULL, 'organization.delete', '删除组织机构', 'organization', NULL, 'delete', NULL, NULL, NULL, NULL, '删除组织机构', 1, '[1,2,3,4]', 'direct_subordinates', 0, 0, 'active', 6, '2025-06-20 02:37:46', '2025-06-22 02:20:34', NULL),
(15, NULL, NULL, 'organization.index', '组织机构列表', 'organization', NULL, 'index', NULL, NULL, NULL, NULL, '查看组织机构列表', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 7, '2025-06-20 02:37:46', '2025-06-22 02:20:34', NULL),
(16, NULL, NULL, 'user.update', '更新用户', 'user', NULL, 'update', NULL, NULL, NULL, NULL, '更新用户信息', 1, '[1,2,3,4,5]', 'all_subordinates', 0, 0, 'active', 8, '2025-06-20 03:45:28', '2025-06-22 02:20:34', NULL),
(17, NULL, NULL, 'user.delete', '删除用户', 'user', NULL, 'delete', NULL, NULL, NULL, NULL, '删除用户', 1, '[1,2,3,4]', 'direct_subordinates', 0, 0, 'active', 9, '2025-06-20 03:45:28', '2025-06-22 02:20:34', NULL),
(18, NULL, NULL, 'user.assign-role', '分配用户角色', 'user', NULL, 'assign-role', NULL, NULL, NULL, NULL, '为用户分配角色', 1, '[1,2,3,4]', 'all_subordinates', 0, 0, 'active', 10, '2025-06-20 03:45:28', '2025-06-21 03:09:35', NULL),
(19, NULL, NULL, 'role.create', '创建角色', 'role', NULL, 'create', NULL, NULL, NULL, NULL, '创建新角色', 1, '[1,2,3]', 'direct_subordinates', 0, 0, 'active', 12, '2025-06-20 03:45:28', '2025-06-22 02:20:34', NULL),
(20, NULL, NULL, 'role.update', '更新角色', 'role', NULL, 'update', NULL, NULL, NULL, NULL, '更新角色信息', 1, '[1,2,3]', 'all_subordinates', 0, 0, 'active', 13, '2025-06-20 03:45:28', '2025-06-21 03:09:35', NULL),
(21, NULL, NULL, 'role.delete', '删除角色', 'role', NULL, 'delete', NULL, NULL, NULL, NULL, '删除角色', 1, '[1,2,3]', 'direct_subordinates', 0, 0, 'active', 14, '2025-06-20 03:45:28', '2025-06-21 03:09:35', NULL),
(22, NULL, NULL, 'role.assign-permissions', '分配角色权限', 'role', NULL, 'assign-permissions', NULL, NULL, NULL, NULL, '为角色分配权限', 1, '[1,2,3]', 'all_subordinates', 0, 0, 'active', 15, '2025-06-20 03:45:28', '2025-06-21 03:09:35', NULL),
(23, NULL, NULL, 'organization.import', '批量导入组织', 'organization', NULL, 'import', NULL, NULL, NULL, NULL, NULL, 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 5, '2025-06-21 05:04:18', '2025-06-21 05:04:18', NULL),
(24, NULL, NULL, 'user.index', '查看用户列表', 'user', NULL, 'index', NULL, NULL, NULL, NULL, NULL, 1, '[1,2,3,4,5]', 'all_subordinates', 1, 0, 'active', 10, '2025-06-21 05:04:18', '2025-06-21 05:04:18', NULL),
(25, NULL, NULL, 'role.index', '查看角色列表', 'role', NULL, 'index', NULL, NULL, NULL, NULL, NULL, 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 20, '2025-06-21 05:04:18', '2025-06-21 05:04:18', NULL),
(26, NULL, NULL, 'district.view', '查看学区划分', 'district', NULL, 'view', NULL, NULL, NULL, NULL, NULL, 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 30, '2025-06-21 06:31:44', '2025-06-21 06:31:44', NULL),
(27, NULL, NULL, 'district.manage', '管理学区划分', 'district', NULL, 'manage', NULL, NULL, NULL, NULL, NULL, 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 31, '2025-06-21 06:31:44', '2025-06-21 06:31:44', NULL),
(28, NULL, NULL, 'permission.view', '查看权限设置', 'permission', NULL, 'view', NULL, NULL, NULL, NULL, '查看权限设置界面', 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 20, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(29, NULL, NULL, 'permission.visualization', '权限可视化', 'permission', NULL, 'visualization', NULL, NULL, NULL, NULL, '查看权限继承关系和可视化界面', 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 21, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(30, NULL, NULL, 'permission.matrix', '权限矩阵管理', 'permission', NULL, 'matrix', NULL, NULL, NULL, NULL, '管理权限矩阵和批量权限操作', 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 22, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(31, NULL, NULL, 'permission.audit', '权限审计', 'permission', NULL, 'audit', NULL, NULL, NULL, NULL, '查看权限审计日志和历史记录', 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 23, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(32, NULL, NULL, 'permission.template.view', '查看权限模板', 'permission', NULL, 'template.view', NULL, NULL, NULL, NULL, '查看权限模板列表', 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 24, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(33, NULL, NULL, 'permission.template.create', '创建权限模板', 'permission', NULL, 'template.create', NULL, NULL, NULL, NULL, '创建新的权限模板', 1, '[1,2,3]', 'all_subordinates', 1, 0, 'active', 25, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(34, NULL, NULL, 'permission.template.update', '编辑权限模板', 'permission', NULL, 'template.update', NULL, NULL, NULL, NULL, '编辑权限模板', 1, '[1,2,3]', 'all_subordinates', 1, 0, 'active', 26, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(35, NULL, NULL, 'permission.template.delete', '删除权限模板', 'permission', NULL, 'template.delete', NULL, NULL, NULL, NULL, '删除权限模板', 1, '[1,2,3]', 'all_subordinates', 1, 0, 'active', 27, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(36, NULL, NULL, 'permission.template.apply', '应用权限模板', 'permission', NULL, 'template.apply', NULL, NULL, NULL, NULL, '将权限模板应用到角色或用户', 1, '[1,2,3,4]', 'all_subordinates', 1, 0, 'active', 28, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(37, NULL, NULL, 'permission.conflict.resolve', '解决权限冲突', 'permission', NULL, 'conflict.resolve', NULL, NULL, NULL, NULL, '解决和处理权限冲突', 1, '[1,2,3]', 'all_subordinates', 1, 0, 'active', 29, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(38, NULL, NULL, 'permission.audit.export', '导出审计日志', 'permission', NULL, 'audit.export', NULL, NULL, NULL, NULL, '导出权限审计日志', 1, '[1,2,3]', 'all_subordinates', 1, 0, 'active', 30, '2025-06-22 02:20:34', '2025-06-22 02:20:34', NULL),
(39, NULL, NULL, 'school.import', '学校信息导入', 'organization', 'school', 'import', 'school', NULL, NULL, NULL, '批量导入学校信息', 1, '[1,2,3,4]', 'all_subordinates', 0, 0, 'active', 10, '2025-06-25 03:40:20', '2025-06-25 03:40:20', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `permission_audit_logs`
--

CREATE TABLE `permission_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '操作用户ID',
  `target_user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '目标用户ID',
  `role_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '角色ID',
  `permission_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '权限ID',
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '组织机构ID',
  `action` enum('grant','revoke','modify','inherit','override') NOT NULL COMMENT '操作类型',
  `target_type` enum('user','role','organization') NOT NULL COMMENT '目标类型',
  `target_name` varchar(255) DEFAULT NULL COMMENT '目标名称',
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '变更前的值' CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '变更后的值' CHECK (json_valid(`new_values`)),
  `reason` text DEFAULT NULL COMMENT '变更原因',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(255) DEFAULT NULL COMMENT '用户代理',
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'success' COMMENT '操作状态',
  `error_message` text DEFAULT NULL COMMENT '错误信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `permission_audit_logs`
--

INSERT INTO `permission_audit_logs` (`id`, `user_id`, `target_user_id`, `role_id`, `permission_id`, `organization_id`, `action`, `target_type`, `target_name`, `old_values`, `new_values`, `reason`, `ip_address`, `user_agent`, `status`, `error_message`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 1, 14, 2, 'grant', 'role', '系统管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '212.242.76.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-05 20:16:03', '2025-06-22 02:53:03'),
(2, 1, NULL, 12, 20, 3, 'revoke', 'role', '部门管理员', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '223.170.124.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-24 12:00:03', '2025-06-22 02:53:03'),
(3, 1, 1, NULL, 4, 2, 'modify', 'user', '系统管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '218.207.47.83', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-01 09:46:03', '2025-06-22 02:53:03'),
(4, 2, NULL, NULL, 20, 7, 'revoke', 'organization', '南城小学', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '220.214.21.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 08:27:03', '2025-06-22 02:53:03'),
(5, 3, NULL, 7, 24, 5, 'revoke', 'role', '副校长', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '197.207.211.61', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-10 05:10:03', '2025-06-22 02:53:03'),
(6, 2, NULL, NULL, 1, 5, 'modify', 'organization', '东城小学', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '198.255.174.159', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-29 21:13:03', '2025-06-22 02:53:03'),
(7, 3, 3, NULL, 36, 3, 'inherit', 'user', '刘校长', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '211.175.203.80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-19 15:11:03', '2025-06-22 02:53:03'),
(8, 1, NULL, NULL, 4, 7, 'inherit', 'organization', '南城小学', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '215.242.136.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-28 06:50:03', '2025-06-22 02:53:03'),
(9, 1, 1, NULL, 7, 2, 'override', 'user', '系统管理员', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '216.201.8.148', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-14 09:54:03', '2025-06-22 02:53:03'),
(10, 2, 2, NULL, 27, 2, 'revoke', 'user', '赵学区主任', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '211.180.21.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 05:29:04', '2025-06-22 02:53:04'),
(11, 2, NULL, NULL, 5, 5, 'revoke', 'organization', '东城小学', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '220.191.214.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-03 20:50:04', '2025-06-22 02:53:04'),
(12, 1, NULL, NULL, 6, 2, 'revoke', 'organization', '石家庄市', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '210.211.29.175', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 12:48:04', '2025-06-22 02:53:04'),
(13, 3, NULL, NULL, 7, 4, 'revoke', 'organization', '廉州学区', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '201.212.180.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-21 07:16:04', '2025-06-22 02:53:04'),
(14, 3, NULL, NULL, 14, 2, 'override', 'organization', '石家庄市', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '222.191.240.109', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-06 05:13:04', '2025-06-22 02:53:04'),
(15, 2, NULL, 5, 29, 7, 'grant', 'role', '学区管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '196.184.222.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-20 05:21:04', '2025-06-22 02:53:04'),
(16, 1, NULL, NULL, 5, 7, 'inherit', 'organization', '南城小学', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '211.198.153.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-07 21:38:04', '2025-06-22 02:53:04'),
(17, 1, NULL, 9, 26, 7, 'inherit', 'role', '教师', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '206.195.176.80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-16 02:02:04', '2025-06-22 02:53:04'),
(18, 2, 2, NULL, 11, 2, 'modify', 'user', '赵学区主任', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '207.190.226.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-04 20:16:04', '2025-06-22 02:53:04'),
(19, 3, NULL, NULL, 11, 4, 'grant', 'organization', '廉州学区', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '223.196.73.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-21 21:18:04', '2025-06-22 02:53:04'),
(20, 3, 3, NULL, 2, 5, 'inherit', 'user', '刘校长', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '207.227.189.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-31 13:37:04', '2025-06-22 02:53:04'),
(21, 2, NULL, 6, 24, 4, 'revoke', 'role', '校长', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '199.179.196.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-05 17:04:04', '2025-06-22 02:53:04'),
(22, 1, NULL, NULL, 8, 6, 'grant', 'organization', '西城小学', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '200.244.42.117', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-02 04:53:04', '2025-06-22 02:53:04'),
(23, 2, NULL, NULL, 18, 4, 'inherit', 'organization', '廉州学区', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '223.182.129.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-11 02:56:04', '2025-06-22 02:53:04'),
(24, 2, NULL, 1, 29, 3, 'inherit', 'role', '系统管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '222.197.2.72', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-27 23:18:04', '2025-06-22 02:53:04'),
(25, 1, NULL, 4, 7, 2, 'revoke', 'role', '区县级管理员', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '223.249.44.129', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-29 10:45:04', '2025-06-22 02:53:04'),
(26, 2, NULL, 3, 13, 7, 'grant', 'role', '市级管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '203.238.29.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-29 10:34:04', '2025-06-22 02:53:04'),
(27, 2, NULL, NULL, 7, 3, 'inherit', 'organization', '藁城区', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '219.244.165.172', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-28 05:05:04', '2025-06-22 02:53:04'),
(28, 3, 3, NULL, 19, 7, 'revoke', 'user', '刘校长', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '203.246.229.243', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-22 11:27:04', '2025-06-22 02:53:04'),
(29, 2, NULL, 5, 9, 3, 'revoke', 'role', '学区管理员', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '199.239.120.49', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-05 17:43:04', '2025-06-22 02:53:04'),
(30, 2, 2, NULL, 22, 2, 'revoke', 'user', '赵学区主任', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '199.177.154.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-03 23:29:04', '2025-06-22 02:53:04'),
(31, 2, NULL, NULL, 19, 6, 'grant', 'organization', '西城小学', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '198.248.174.186', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-19 21:34:04', '2025-06-22 02:53:04'),
(32, 1, NULL, 3, 21, 6, 'inherit', 'role', '市级管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '201.169.55.246', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-23 04:20:04', '2025-06-22 02:53:04'),
(33, 3, NULL, 13, 35, 5, 'override', 'role', '普通用户', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '205.177.30.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-28 06:21:04', '2025-06-22 02:53:04'),
(34, 3, NULL, 10, 3, 4, 'inherit', 'role', '实验管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '195.253.173.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-13 17:02:04', '2025-06-22 02:53:04'),
(35, 1, NULL, 5, 26, 7, 'grant', 'role', '学区管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '197.247.24.117', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-04 03:26:04', '2025-06-22 02:53:04'),
(36, 1, 1, NULL, 1, 7, 'revoke', 'user', '系统管理员', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '202.234.60.30', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-18 01:28:04', '2025-06-22 02:53:04'),
(37, 3, NULL, NULL, 34, 6, 'revoke', 'organization', '西城小学', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '207.197.154.8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-03 04:50:04', '2025-06-22 02:53:04'),
(38, 2, NULL, 11, 8, 1, 'override', 'role', '组织管理员', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '194.189.227.163', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-27 16:42:04', '2025-06-22 02:53:04'),
(39, 2, NULL, NULL, 33, 6, 'revoke', 'organization', '西城小学', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '199.253.183.75', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-08 10:15:04', '2025-06-22 02:53:04'),
(40, 3, NULL, 4, 2, 5, 'modify', 'role', '区县级管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '204.171.24.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-07 01:23:04', '2025-06-22 02:53:04'),
(41, 3, NULL, 1, 18, 7, 'modify', 'role', '系统管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '208.223.96.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-07 21:46:04', '2025-06-22 02:53:04'),
(42, 3, NULL, 8, 13, 2, 'grant', 'role', '教务主任', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '196.243.233.54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-09 04:56:04', '2025-06-22 02:53:04'),
(43, 3, NULL, 1, 17, 7, 'revoke', 'role', '系统管理员', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '200.174.33.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-12 03:35:05', '2025-06-22 02:53:05'),
(44, 2, NULL, 11, 3, 3, 'override', 'role', '组织管理员', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '209.239.54.207', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-11 04:48:05', '2025-06-22 02:53:05'),
(45, 3, NULL, 12, 5, 4, 'modify', 'role', '部门管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '215.194.148.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-11 13:21:05', '2025-06-22 02:53:05'),
(46, 1, NULL, 6, 26, 1, 'revoke', 'role', '校长', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '198.234.237.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-29 15:49:05', '2025-06-22 02:53:05'),
(47, 3, 3, NULL, 5, 5, 'override', 'user', '刘校长', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '221.231.247.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-14 06:54:05', '2025-06-22 02:53:05'),
(48, 3, 3, NULL, 8, 1, 'revoke', 'user', '刘校长', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '195.239.62.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-01 00:27:05', '2025-06-22 02:53:05'),
(49, 1, 1, NULL, 21, 3, 'override', 'user', '系统管理员', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '201.206.230.16', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-05 15:55:05', '2025-06-22 02:53:05'),
(50, 3, 3, NULL, 5, 5, 'modify', 'user', '刘校长', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '193.254.153.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-20 06:48:05', '2025-06-22 02:53:05'),
(51, 1, NULL, NULL, 8, 3, 'modify', 'organization', '藁城区', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '203.175.171.153', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-23 04:42:05', '2025-06-22 02:55:05'),
(52, 1, NULL, NULL, 25, 6, 'grant', 'organization', '西城小学', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '217.198.146.178', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-12 16:52:05', '2025-06-22 02:55:05'),
(53, 1, NULL, 8, 19, 3, 'modify', 'role', '教务主任', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '218.201.231.221', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-17 01:30:06', '2025-06-22 02:55:06'),
(54, 3, NULL, 2, 3, 7, 'grant', 'role', '省级管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '214.236.182.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-18 16:31:06', '2025-06-22 02:55:06'),
(55, 2, NULL, 1, 15, 4, 'modify', 'role', '系统管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '204.190.93.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-15 03:00:06', '2025-06-22 02:55:06'),
(56, 2, 2, NULL, 28, 4, 'revoke', 'user', '赵学区主任', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '210.200.15.22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-15 02:05:06', '2025-06-22 02:55:06'),
(57, 2, NULL, NULL, 24, 2, 'inherit', 'organization', '石家庄市', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '202.190.31.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-30 11:44:06', '2025-06-22 02:55:06'),
(58, 2, NULL, 13, 21, 5, 'modify', 'role', '普通用户', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '197.251.26.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-27 12:00:06', '2025-06-22 02:55:06'),
(59, 3, NULL, 7, 16, 1, 'modify', 'role', '副校长', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '201.254.244.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-08 14:33:06', '2025-06-22 02:55:06'),
(60, 3, 3, NULL, 22, 6, 'modify', 'user', '刘校长', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '211.217.195.31', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 05:09:06', '2025-06-22 02:55:06'),
(61, 2, 2, NULL, 20, 5, 'grant', 'user', '赵学区主任', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '195.183.170.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-13 00:03:06', '2025-06-22 02:55:06'),
(62, 2, 2, NULL, 36, 5, 'revoke', 'user', '赵学区主任', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '200.206.17.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 12:32:06', '2025-06-22 02:55:06'),
(63, 2, NULL, 13, 2, 4, 'inherit', 'role', '普通用户', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '200.174.99.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-11 03:07:06', '2025-06-22 02:55:06'),
(64, 3, 3, NULL, 17, 5, 'override', 'user', '刘校长', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '195.249.140.243', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-04 10:35:06', '2025-06-22 02:55:06'),
(65, 1, NULL, NULL, 38, 7, 'inherit', 'organization', '南城小学', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '203.249.209.244', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-27 14:52:06', '2025-06-22 02:55:06'),
(66, 3, 3, NULL, 16, 4, 'override', 'user', '刘校长', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '195.184.64.99', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-23 16:01:06', '2025-06-22 02:55:06'),
(67, 2, NULL, 1, 17, 4, 'inherit', 'role', '系统管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '201.230.94.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-30 01:03:06', '2025-06-22 02:55:06'),
(68, 1, NULL, 11, 2, 1, 'grant', 'role', '组织管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '202.172.41.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-01 14:55:06', '2025-06-22 02:55:06'),
(69, 2, 2, NULL, 38, 3, 'grant', 'user', '赵学区主任', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '222.222.49.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-27 00:47:06', '2025-06-22 02:55:06'),
(70, 2, 2, NULL, 5, 4, 'revoke', 'user', '赵学区主任', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '194.211.192.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-05 22:16:06', '2025-06-22 02:55:06'),
(71, 1, NULL, 4, 27, 5, 'revoke', 'role', '区县级管理员', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '211.172.150.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-09 09:02:06', '2025-06-22 02:55:06'),
(72, 1, 1, NULL, 33, 2, 'modify', 'user', '系统管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '210.199.216.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-30 05:08:06', '2025-06-22 02:55:06'),
(73, 3, NULL, 4, 24, 7, 'grant', 'role', '区县级管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '223.211.65.55', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-29 10:46:06', '2025-06-22 02:55:06'),
(74, 3, 3, NULL, 34, 2, 'modify', 'user', '刘校长', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '197.218.5.61', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-26 00:10:06', '2025-06-22 02:55:06'),
(75, 1, 1, NULL, 22, 7, 'inherit', 'user', '系统管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '212.188.219.214', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-31 01:34:06', '2025-06-22 02:55:06'),
(76, 2, 2, NULL, 30, 4, 'override', 'user', '赵学区主任', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '220.245.54.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-29 12:36:06', '2025-06-22 02:55:06'),
(77, 2, NULL, NULL, 11, 4, 'grant', 'organization', '廉州学区', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '199.197.237.218', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-13 14:57:06', '2025-06-22 02:55:06'),
(78, 2, 2, NULL, 35, 1, 'modify', 'user', '赵学区主任', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '209.203.190.29', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-02 00:35:06', '2025-06-22 02:55:06'),
(79, 1, NULL, NULL, 12, 4, 'inherit', 'organization', '廉州学区', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '204.200.195.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-02 19:32:06', '2025-06-22 02:55:06'),
(80, 3, 3, NULL, 22, 1, 'modify', 'user', '刘校长', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '208.243.48.86', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-30 10:56:06', '2025-06-22 02:55:06'),
(81, 2, NULL, 12, 29, 5, 'modify', 'role', '部门管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '219.209.142.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-22 23:27:06', '2025-06-22 02:55:06'),
(82, 2, NULL, 1, 31, 6, 'modify', 'role', '系统管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '204.217.151.199', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-23 22:29:06', '2025-06-22 02:55:06'),
(83, 3, NULL, 4, 30, 1, 'inherit', 'role', '区县级管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '200.193.172.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-07 01:28:06', '2025-06-22 02:55:06'),
(84, 3, NULL, NULL, 10, 7, 'modify', 'organization', '南城小学', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '199.255.119.212', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-23 16:29:06', '2025-06-22 02:55:06'),
(85, 2, NULL, 7, 11, 5, 'revoke', 'role', '副校长', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '203.210.200.232', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-10 09:53:07', '2025-06-22 02:55:07'),
(86, 1, NULL, NULL, 32, 1, 'revoke', 'organization', '河北省', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '193.171.87.62', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-10 04:53:07', '2025-06-22 02:55:07'),
(87, 1, 1, NULL, 19, 3, 'grant', 'user', '系统管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '195.253.142.40', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-03 10:22:07', '2025-06-22 02:55:07'),
(88, 2, NULL, NULL, 29, 1, 'revoke', 'organization', '河北省', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '201.199.8.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-31 15:53:07', '2025-06-22 02:55:07'),
(89, 2, NULL, NULL, 23, 1, 'inherit', 'organization', '河北省', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '221.254.88.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-29 17:52:07', '2025-06-22 02:55:07'),
(90, 2, NULL, 8, 16, 4, 'revoke', 'role', '教务主任', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '198.229.29.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 12:15:07', '2025-06-22 02:55:07'),
(91, 3, NULL, NULL, 33, 1, 'inherit', 'organization', '河北省', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '214.226.92.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-26 03:15:07', '2025-06-22 02:55:07'),
(92, 2, NULL, NULL, 32, 1, 'revoke', 'organization', '河北省', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '212.209.51.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-17 21:54:07', '2025-06-22 02:55:07'),
(93, 2, NULL, 12, 27, 6, 'grant', 'role', '部门管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '195.251.125.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-21 21:11:07', '2025-06-22 02:55:07'),
(94, 1, NULL, 9, 20, 3, 'override', 'role', '教师', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '205.207.195.40', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-01 06:37:07', '2025-06-22 02:55:07'),
(95, 3, NULL, 7, 37, 2, 'grant', 'role', '副校长', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '223.210.36.22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-23 19:09:07', '2025-06-22 02:55:07'),
(96, 2, NULL, NULL, 31, 1, 'modify', 'organization', '河北省', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '199.214.8.157', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-01 19:02:07', '2025-06-22 02:55:07'),
(97, 1, NULL, NULL, 15, 3, 'override', 'organization', '藁城区', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '197.188.38.68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-12 20:17:07', '2025-06-22 02:55:07'),
(98, 1, NULL, 12, 25, 5, 'grant', 'role', '部门管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '210.195.207.80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-15 11:55:07', '2025-06-22 02:55:07'),
(99, 2, NULL, 4, 12, 3, 'revoke', 'role', '区县级管理员', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '223.214.184.17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-23 17:57:07', '2025-06-22 02:55:07'),
(100, 1, 1, NULL, 35, 5, 'modify', 'user', '系统管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '213.224.210.27', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-27 05:59:07', '2025-06-22 02:55:07'),
(101, 2, NULL, 9, 11, 7, 'revoke', 'role', '教师', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '200.220.76.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-17 19:43:08', '2025-06-22 02:58:08'),
(102, 3, NULL, NULL, 6, 1, 'inherit', 'organization', '河北省', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '198.242.204.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-02 01:07:08', '2025-06-22 02:58:08'),
(103, 3, 3, NULL, 12, 1, 'inherit', 'user', '刘校长', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '207.216.88.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-29 15:02:08', '2025-06-22 02:58:08'),
(104, 3, NULL, 3, 33, 4, 'modify', 'role', '市级管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '221.252.74.37', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-27 14:03:08', '2025-06-22 02:58:08'),
(105, 1, NULL, 5, 29, 6, 'grant', 'role', '学区管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '217.245.10.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-11 03:29:08', '2025-06-22 02:58:08'),
(106, 3, NULL, 2, 8, 5, 'revoke', 'role', '省级管理员', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '220.176.86.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-24 07:50:08', '2025-06-22 02:58:08'),
(107, 1, NULL, NULL, 13, 1, 'inherit', 'organization', '河北省', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '197.175.213.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 21:05:08', '2025-06-22 02:58:08'),
(108, 1, 1, NULL, 4, 1, 'override', 'user', '系统管理员', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '203.234.20.253', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-22 08:36:08', '2025-06-22 02:58:08'),
(109, 2, NULL, NULL, 8, 2, 'modify', 'organization', '石家庄市', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '197.179.189.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-06 12:41:09', '2025-06-22 02:58:09'),
(110, 2, NULL, NULL, 38, 6, 'modify', 'organization', '西城小学', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '213.175.229.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-10 17:47:09', '2025-06-22 02:58:09'),
(111, 1, NULL, NULL, 19, 3, 'grant', 'organization', '藁城区', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '198.198.78.244', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-05 10:06:09', '2025-06-22 02:58:09'),
(112, 1, 1, NULL, 22, 4, 'inherit', 'user', '系统管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '208.175.215.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-30 12:53:09', '2025-06-22 02:58:09'),
(113, 1, NULL, 1, 28, 2, 'override', 'role', '系统管理员', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '214.228.233.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-16 16:48:09', '2025-06-22 02:58:09'),
(114, 3, NULL, 11, 12, 7, 'grant', 'role', '组织管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '218.180.178.22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-21 04:47:09', '2025-06-22 02:58:09'),
(115, 3, NULL, 8, 26, 5, 'grant', 'role', '教务主任', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '209.231.235.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-14 17:08:09', '2025-06-22 02:58:09'),
(116, 2, NULL, NULL, 37, 6, 'override', 'organization', '西城小学', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '196.210.133.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-28 02:55:09', '2025-06-22 02:58:09'),
(117, 2, 2, NULL, 26, 3, 'grant', 'user', '赵学区主任', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '195.192.81.67', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-15 05:47:09', '2025-06-22 02:58:09'),
(118, 2, NULL, 12, 8, 3, 'inherit', 'role', '部门管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '192.234.162.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-19 10:43:09', '2025-06-22 02:58:09'),
(119, 3, 3, NULL, 19, 7, 'modify', 'user', '刘校长', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '200.243.198.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-28 02:58:09', '2025-06-22 02:58:09'),
(120, 3, 3, NULL, 16, 3, 'revoke', 'user', '刘校长', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '216.189.95.46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-22 01:42:09', '2025-06-22 02:58:09'),
(121, 1, 1, NULL, 14, 5, 'grant', 'user', '系统管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '208.237.139.244', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-06 18:05:09', '2025-06-22 02:58:09'),
(122, 3, NULL, NULL, 10, 6, 'inherit', 'organization', '西城小学', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '223.188.43.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-31 04:14:09', '2025-06-22 02:58:09'),
(123, 2, 2, NULL, 35, 2, 'revoke', 'user', '赵学区主任', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '201.168.153.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-01 19:23:09', '2025-06-22 02:58:09'),
(124, 3, 3, NULL, 16, 4, 'modify', 'user', '刘校长', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '197.221.14.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-20 23:27:09', '2025-06-22 02:58:09'),
(125, 3, NULL, 7, 18, 6, 'inherit', 'role', '副校长', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '221.232.76.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-26 19:35:09', '2025-06-22 02:58:09'),
(126, 3, NULL, NULL, 10, 2, 'inherit', 'organization', '石家庄市', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '219.175.144.63', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-20 18:37:09', '2025-06-22 02:58:09'),
(127, 3, NULL, NULL, 18, 2, 'revoke', 'organization', '石家庄市', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '215.171.24.115', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-08 18:35:09', '2025-06-22 02:58:09'),
(128, 2, NULL, 2, 12, 4, 'inherit', 'role', '省级管理员', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '218.188.222.204', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-01 03:23:09', '2025-06-22 02:58:09'),
(129, 2, NULL, NULL, 6, 4, 'override', 'organization', '廉州学区', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '200.223.250.157', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 16:11:09', '2025-06-22 02:58:09'),
(130, 1, NULL, NULL, 34, 3, 'revoke', 'organization', '藁城区', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '205.171.119.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-15 23:49:09', '2025-06-22 02:58:09'),
(131, 3, 3, NULL, 38, 7, 'inherit', 'user', '刘校长', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '222.177.140.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-28 07:36:09', '2025-06-22 02:58:09'),
(132, 1, NULL, NULL, 16, 7, 'inherit', 'organization', '南城小学', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '201.234.123.136', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-30 17:20:09', '2025-06-22 02:58:09'),
(133, 2, NULL, 11, 27, 3, 'grant', 'role', '组织管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '208.211.116.203', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-18 04:19:09', '2025-06-22 02:58:09'),
(134, 1, 1, NULL, 38, 2, 'modify', 'user', '系统管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '216.169.130.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-06 10:49:09', '2025-06-22 02:58:09'),
(135, 3, NULL, 13, 24, 4, 'inherit', 'role', '普通用户', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '202.184.230.132', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-15 04:32:09', '2025-06-22 02:58:09'),
(136, 1, NULL, NULL, 1, 1, 'override', 'organization', '河北省', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '210.172.224.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-03 16:48:09', '2025-06-22 02:58:09'),
(137, 1, 1, NULL, 7, 1, 'override', 'user', '系统管理员', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '218.186.89.60', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-04 23:36:09', '2025-06-22 02:58:09'),
(138, 2, NULL, 7, 21, 1, 'grant', 'role', '副校长', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '222.174.41.194', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-06 17:38:09', '2025-06-22 02:58:09'),
(139, 1, NULL, NULL, 26, 4, 'revoke', 'organization', '廉州学区', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '218.213.248.53', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-13 14:18:09', '2025-06-22 02:58:09'),
(140, 3, NULL, NULL, 34, 3, 'grant', 'organization', '藁城区', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '201.235.246.117', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-20 01:48:09', '2025-06-22 02:58:09'),
(141, 2, NULL, 7, 12, 4, 'grant', 'role', '副校长', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '223.249.242.70', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-12 00:05:09', '2025-06-22 02:58:09'),
(142, 1, NULL, 12, 17, 3, 'modify', 'role', '部门管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '193.254.33.96', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-25 09:24:09', '2025-06-22 02:58:09'),
(143, 2, NULL, 4, 14, 4, 'modify', 'role', '区县级管理员', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '213.204.35.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-16 21:56:09', '2025-06-22 02:58:09'),
(144, 1, NULL, 9, 24, 7, 'revoke', 'role', '教师', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '217.183.143.17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-31 11:05:10', '2025-06-22 02:58:10'),
(145, 2, NULL, 5, 14, 5, 'grant', 'role', '学区管理员', NULL, '{\"status\":\"granted\"}', '用户职责变更，需要新增权限', '219.223.221.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-09 19:26:10', '2025-06-22 02:58:10'),
(146, 1, NULL, 9, 9, 4, 'revoke', 'role', '教师', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '193.169.20.244', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-09 09:33:10', '2025-06-22 02:58:10'),
(147, 3, NULL, NULL, 37, 5, 'revoke', 'organization', '东城小学', NULL, '{\"status\":\"revoked\"}', '用户离职，撤销相关权限', '210.232.37.222', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-05-27 17:54:10', '2025-06-22 02:58:10'),
(148, 3, NULL, NULL, 11, 1, 'modify', 'organization', '河北省', '{\"status\":\"inactive\",\"permissions\":[\"user.view\",\"organization.view\"]}', '{\"status\":\"active\",\"permissions\":[\"user.view\",\"user.create\",\"organization.view\"]}', '权限调整，优化权限配置', '222.234.104.195', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-06 10:31:10', '2025-06-22 02:58:10'),
(149, 3, NULL, NULL, 10, 2, 'override', 'organization', '石家庄市', NULL, '{\"overridden\":true}', '特殊需求，手动覆盖权限', '217.190.34.70', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-04 00:26:10', '2025-06-22 02:58:10'),
(150, 1, NULL, NULL, 13, 3, 'inherit', 'organization', '藁城区', NULL, '{\"source\":\"parent_organization\"}', '组织架构调整，权限自动继承', '206.244.42.151', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', NULL, '2025-06-13 01:07:10', '2025-06-22 02:58:10');

-- --------------------------------------------------------

--
-- 表的结构 `permission_conflicts`
--

CREATE TABLE `permission_conflicts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '用户ID',
  `role_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '角色ID',
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '组织机构ID',
  `permission_id` bigint(20) UNSIGNED NOT NULL COMMENT '权限ID',
  `conflict_type` enum('role_user','role_role','inheritance','explicit_deny') NOT NULL COMMENT '冲突类型',
  `conflict_sources` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '冲突来源详情' CHECK (json_valid(`conflict_sources`)),
  `resolution_strategy` enum('allow','deny','manual') NOT NULL DEFAULT 'manual' COMMENT '解决策略',
  `priority` enum('high','medium','low') NOT NULL DEFAULT 'medium' COMMENT '优先级',
  `status` enum('unresolved','resolved','ignored') NOT NULL DEFAULT 'unresolved' COMMENT '状态',
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '解决者ID',
  `resolved_at` timestamp NULL DEFAULT NULL COMMENT '解决时间',
  `resolution_notes` text DEFAULT NULL COMMENT '解决说明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `permission_conflicts`
--

INSERT INTO `permission_conflicts` (`id`, `user_id`, `role_id`, `organization_id`, `permission_id`, `conflict_type`, `conflict_sources`, `resolution_strategy`, `priority`, `status`, `resolved_by`, `resolved_at`, `resolution_notes`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 7, 25, 'inheritance', '[{\"source_type\":\"inheritance\",\"source_id\":1,\"source_name\":\"\\u4e0a\\u7ea7\\u7ec4\\u7ec7\\u7ee7\\u627f\"}]', 'manual', 'low', 'resolved', 3, '2025-06-12 02:58:10', '手动解决冲突', '2025-06-10 17:58:10', '2025-06-22 02:58:10'),
(2, 1, NULL, 2, 33, 'inheritance', '[{\"source_type\":\"inheritance\",\"source_id\":1,\"source_name\":\"\\u4e0a\\u7ea7\\u7ec4\\u7ec7\\u7ee7\\u627f\"}]', 'manual', 'high', 'resolved', 3, '2025-06-20 02:58:10', '手动解决冲突', '2025-06-21 00:58:10', '2025-06-22 02:58:10'),
(3, 2, NULL, 7, 36, 'inheritance', '[{\"source_type\":\"inheritance\",\"source_id\":1,\"source_name\":\"\\u4e0a\\u7ea7\\u7ec4\\u7ec7\\u7ee7\\u627f\"}]', 'manual', 'low', 'ignored', NULL, NULL, NULL, '2025-06-09 16:58:10', '2025-06-22 02:58:10'),
(4, 2, NULL, 5, 35, 'inheritance', '[{\"source_type\":\"inheritance\",\"source_id\":1,\"source_name\":\"\\u4e0a\\u7ea7\\u7ec4\\u7ec7\\u7ee7\\u627f\"}]', 'manual', 'low', 'resolved', 1, '2025-06-20 02:58:10', '手动解决冲突', '2025-06-19 01:58:10', '2025-06-22 02:58:10'),
(5, 2, NULL, 2, 14, 'inheritance', '[{\"source_type\":\"inheritance\",\"source_id\":1,\"source_name\":\"\\u4e0a\\u7ea7\\u7ec4\\u7ec7\\u7ee7\\u627f\"}]', 'manual', 'high', 'unresolved', NULL, NULL, NULL, '2025-05-19 09:58:10', '2025-06-22 02:58:10'),
(6, 3, NULL, 7, 28, 'inheritance', '[{\"source_type\":\"inheritance\",\"source_id\":1,\"source_name\":\"\\u4e0a\\u7ea7\\u7ec4\\u7ec7\\u7ee7\\u627f\"}]', 'manual', 'medium', 'ignored', NULL, NULL, NULL, '2025-06-01 20:58:10', '2025-06-22 02:58:10'),
(7, 1, NULL, 6, 20, 'explicit_deny', '[{\"source_type\":\"deny\",\"source_id\":null,\"source_name\":\"\\u663e\\u5f0f\\u62d2\\u7edd\"}]', 'manual', 'low', 'unresolved', NULL, NULL, NULL, '2025-04-22 14:58:10', '2025-06-22 02:58:10'),
(8, 2, NULL, 1, 12, 'role_role', '[{\"source_type\":\"role\",\"source_id\":1,\"source_name\":\"\\u7ba1\\u7406\\u5458\\u89d2\\u8272\"},{\"source_type\":\"role\",\"source_id\":2,\"source_name\":\"\\u6559\\u5e08\\u89d2\\u8272\"}]', 'manual', 'low', 'resolved', 2, '2025-06-18 02:58:10', '手动解决冲突', '2025-05-03 00:58:10', '2025-06-22 02:58:10'),
(9, 2, NULL, 5, 30, 'explicit_deny', '[{\"source_type\":\"deny\",\"source_id\":null,\"source_name\":\"\\u663e\\u5f0f\\u62d2\\u7edd\"}]', 'manual', 'low', 'unresolved', NULL, NULL, NULL, '2025-04-30 14:58:10', '2025-06-22 02:58:10'),
(10, 3, NULL, 5, 24, 'explicit_deny', '[{\"source_type\":\"deny\",\"source_id\":null,\"source_name\":\"\\u663e\\u5f0f\\u62d2\\u7edd\"}]', 'manual', 'high', 'ignored', NULL, NULL, NULL, '2025-06-03 02:58:10', '2025-06-22 02:58:10'),
(11, 2, NULL, 2, 24, 'role_role', '[{\"source_type\":\"role\",\"source_id\":1,\"source_name\":\"\\u7ba1\\u7406\\u5458\\u89d2\\u8272\"},{\"source_type\":\"role\",\"source_id\":2,\"source_name\":\"\\u6559\\u5e08\\u89d2\\u8272\"}]', 'manual', 'medium', 'ignored', NULL, NULL, NULL, '2025-05-30 11:58:10', '2025-06-22 02:58:10'),
(12, 1, NULL, 5, 8, 'explicit_deny', '[{\"source_type\":\"deny\",\"source_id\":null,\"source_name\":\"\\u663e\\u5f0f\\u62d2\\u7edd\"}]', 'manual', 'medium', 'ignored', NULL, NULL, NULL, '2025-05-24 18:58:10', '2025-06-22 02:58:10'),
(13, 2, NULL, 5, 19, 'explicit_deny', '[{\"source_type\":\"deny\",\"source_id\":null,\"source_name\":\"\\u663e\\u5f0f\\u62d2\\u7edd\"}]', 'manual', 'high', 'unresolved', NULL, NULL, NULL, '2025-04-24 20:58:10', '2025-06-22 02:58:10'),
(14, 3, NULL, 6, 22, 'role_role', '[{\"source_type\":\"role\",\"source_id\":1,\"source_name\":\"\\u7ba1\\u7406\\u5458\\u89d2\\u8272\"},{\"source_type\":\"role\",\"source_id\":2,\"source_name\":\"\\u6559\\u5e08\\u89d2\\u8272\"}]', 'manual', 'low', 'resolved', 3, '2025-06-22 02:58:10', '手动解决冲突', '2025-06-13 02:58:10', '2025-06-22 02:58:10'),
(15, 1, NULL, 3, 8, 'explicit_deny', '[{\"source_type\":\"deny\",\"source_id\":null,\"source_name\":\"\\u663e\\u5f0f\\u62d2\\u7edd\"}]', 'manual', 'medium', 'unresolved', NULL, NULL, NULL, '2025-05-23 13:58:10', '2025-06-22 02:58:10'),
(16, 3, NULL, 3, 28, 'role_user', '[{\"source_type\":\"role\",\"source_id\":1,\"source_name\":\"\\u7ba1\\u7406\\u5458\\u89d2\\u8272\"},{\"source_type\":\"direct\",\"source_id\":null,\"source_name\":\"\\u76f4\\u63a5\\u5206\\u914d\"}]', 'manual', 'medium', 'unresolved', NULL, NULL, NULL, '2025-04-26 14:58:10', '2025-06-22 02:58:10'),
(17, 3, NULL, 6, 24, 'role_role', '[{\"source_type\":\"role\",\"source_id\":1,\"source_name\":\"\\u7ba1\\u7406\\u5458\\u89d2\\u8272\"},{\"source_type\":\"role\",\"source_id\":2,\"source_name\":\"\\u6559\\u5e08\\u89d2\\u8272\"}]', 'manual', 'low', 'resolved', 2, '2025-06-15 02:58:10', '手动解决冲突', '2025-04-27 23:58:10', '2025-06-22 02:58:10'),
(18, 3, NULL, 6, 14, 'role_user', '[{\"source_type\":\"role\",\"source_id\":1,\"source_name\":\"\\u7ba1\\u7406\\u5458\\u89d2\\u8272\"},{\"source_type\":\"direct\",\"source_id\":null,\"source_name\":\"\\u76f4\\u63a5\\u5206\\u914d\"}]', 'manual', 'high', 'ignored', NULL, NULL, NULL, '2025-05-23 16:58:10', '2025-06-22 02:58:10'),
(19, 1, NULL, 3, 32, 'role_role', '[{\"source_type\":\"role\",\"source_id\":1,\"source_name\":\"\\u7ba1\\u7406\\u5458\\u89d2\\u8272\"},{\"source_type\":\"role\",\"source_id\":2,\"source_name\":\"\\u6559\\u5e08\\u89d2\\u8272\"}]', 'manual', 'high', 'ignored', NULL, NULL, NULL, '2025-05-24 11:58:10', '2025-06-22 02:58:10'),
(20, 3, NULL, 2, 2, 'role_role', '[{\"source_type\":\"role\",\"source_id\":1,\"source_name\":\"\\u7ba1\\u7406\\u5458\\u89d2\\u8272\"},{\"source_type\":\"role\",\"source_id\":2,\"source_name\":\"\\u6559\\u5e08\\u89d2\\u8272\"}]', 'manual', 'medium', 'resolved', 3, '2025-06-22 02:58:11', '手动解决冲突', '2025-06-17 02:58:11', '2025-06-22 02:58:11');

-- --------------------------------------------------------

--
-- 表的结构 `permission_inheritance`
--

CREATE TABLE `permission_inheritance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '父级组织ID',
  `child_organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '子级组织ID',
  `permission_id` bigint(20) UNSIGNED NOT NULL COMMENT '权限ID',
  `inheritance_type` enum('direct','indirect') NOT NULL DEFAULT 'direct' COMMENT '继承类型：直接/间接',
  `inheritance_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '继承路径JSON数组' CHECK (json_valid(`inheritance_path`)),
  `is_overridden` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否被覆盖',
  `overridden_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '覆盖者ID',
  `overridden_at` timestamp NULL DEFAULT NULL COMMENT '覆盖时间',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `permission_inheritance`
--

INSERT INTO `permission_inheritance` (`id`, `parent_organization_id`, `child_organization_id`, `permission_id`, `inheritance_type`, `inheritance_path`, `is_overridden`, `overridden_by`, `overridden_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:17', '2025-06-22 02:42:17'),
(2, 1, 2, 2, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:17', '2025-06-22 02:42:17'),
(3, 1, 2, 3, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:17', '2025-06-22 02:42:17'),
(4, 1, 2, 4, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:17', '2025-06-22 02:42:17'),
(5, 1, 2, 5, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:17', '2025-06-22 02:42:17'),
(6, 1, 2, 6, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:17', '2025-06-22 02:42:17'),
(7, 1, 2, 7, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:17', '2025-06-22 02:42:17'),
(8, 1, 2, 8, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(9, 1, 2, 9, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(10, 1, 2, 13, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(11, 1, 2, 14, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(12, 1, 2, 15, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(13, 1, 2, 16, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(14, 1, 2, 17, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(15, 1, 2, 18, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(16, 1, 2, 19, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(17, 1, 2, 20, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(18, 1, 2, 21, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(19, 1, 2, 22, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(20, 1, 2, 23, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(21, 1, 2, 24, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(22, 1, 2, 25, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(23, 1, 2, 26, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(24, 1, 2, 27, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(25, 1, 2, 28, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(26, 1, 2, 29, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(27, 1, 2, 30, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(28, 1, 2, 31, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(29, 1, 2, 32, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(30, 1, 2, 33, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(31, 1, 2, 34, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(32, 1, 2, 35, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(33, 1, 2, 36, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(34, 1, 2, 37, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(35, 1, 2, 38, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:18', '2025-06-22 02:42:18'),
(36, 2, 3, 1, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(37, 2, 3, 2, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(38, 2, 3, 3, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(39, 2, 3, 4, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(40, 2, 3, 5, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(41, 2, 3, 6, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(42, 2, 3, 7, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(43, 2, 3, 8, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(44, 2, 3, 9, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(45, 2, 3, 13, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(46, 2, 3, 14, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(47, 2, 3, 15, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(48, 2, 3, 16, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(49, 2, 3, 17, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(50, 2, 3, 18, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(51, 2, 3, 19, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(52, 2, 3, 20, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(53, 2, 3, 21, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(54, 2, 3, 22, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(55, 2, 3, 23, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(56, 2, 3, 24, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(57, 2, 3, 25, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(58, 2, 3, 26, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(59, 2, 3, 27, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(60, 2, 3, 28, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(61, 2, 3, 29, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(62, 2, 3, 30, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(63, 2, 3, 31, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(64, 2, 3, 32, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(65, 2, 3, 33, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(66, 2, 3, 34, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(67, 2, 3, 35, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(68, 2, 3, 36, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(69, 2, 3, 37, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:19', '2025-06-22 02:42:19'),
(70, 2, 3, 38, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(71, 3, 4, 1, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(72, 3, 4, 2, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(73, 3, 4, 3, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(74, 3, 4, 4, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(75, 3, 4, 5, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(76, 3, 4, 6, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(77, 3, 4, 7, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(78, 3, 4, 8, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(79, 3, 4, 9, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(80, 3, 4, 12, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(81, 3, 4, 13, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(82, 3, 4, 14, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(83, 3, 4, 15, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(84, 3, 4, 16, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(85, 3, 4, 17, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(86, 3, 4, 18, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(87, 3, 4, 23, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(88, 3, 4, 24, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(89, 3, 4, 25, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(90, 3, 4, 26, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(91, 3, 4, 27, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(92, 3, 4, 28, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:20', '2025-06-22 02:42:20'),
(93, 3, 4, 29, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(94, 3, 4, 30, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(95, 3, 4, 31, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(96, 3, 4, 32, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(97, 3, 4, 36, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(98, 4, 5, 1, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(99, 4, 5, 2, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(100, 4, 5, 4, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(101, 4, 5, 5, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 1, 1, '2025-06-22 02:42:22', 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:22'),
(102, 4, 5, 6, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(103, 4, 5, 7, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(104, 4, 5, 8, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(105, 4, 5, 9, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(106, 4, 5, 10, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(107, 4, 5, 11, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(108, 4, 5, 12, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(109, 4, 5, 13, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(110, 4, 5, 15, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(111, 4, 5, 16, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(112, 4, 5, 24, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u4e1c\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(113, 4, 6, 1, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(114, 4, 6, 2, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(115, 4, 6, 4, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(116, 4, 6, 5, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(117, 4, 6, 6, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(118, 4, 6, 7, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:21', '2025-06-22 02:42:21'),
(119, 4, 6, 8, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(120, 4, 6, 9, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(121, 4, 6, 10, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(122, 4, 6, 11, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(123, 4, 6, 12, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(124, 4, 6, 13, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(125, 4, 6, 15, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(126, 4, 6, 16, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(127, 4, 6, 24, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u897f\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(128, 4, 7, 1, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(129, 4, 7, 2, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(130, 4, 7, 4, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(131, 4, 7, 5, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(132, 4, 7, 6, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(133, 4, 7, 7, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(134, 4, 7, 8, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(135, 4, 7, 9, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(136, 4, 7, 10, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(137, 4, 7, 11, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(138, 4, 7, 12, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(139, 4, 7, 13, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(140, 4, 7, 15, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(141, 4, 7, 16, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22'),
(142, 4, 7, 24, 'direct', '[\"\\u6cb3\\u5317\\u7701\",\"\\u77f3\\u5bb6\\u5e84\\u5e02\",\"\\u85c1\\u57ce\\u533a\",\"\\u5ec9\\u5dde\\u5b66\\u533a\",\"\\u5357\\u57ce\\u5c0f\\u5b66\"]', 0, NULL, NULL, 'active', '2025-06-22 02:42:22', '2025-06-22 02:42:22');

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

--
-- 转存表中的数据 `permission_role`
--

INSERT INTO `permission_role` (`id`, `permission_id`, `role_id`, `conditions`, `access_type`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(2, 2, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(3, 3, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(4, 4, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(5, 5, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(6, 6, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(7, 7, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(8, 8, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(9, 9, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(10, 10, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(11, 11, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(12, 12, 1, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(13, 2, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(14, 3, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(15, 4, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(16, 5, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(17, 6, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(18, 7, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(19, 8, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(20, 9, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(21, 10, 2, NULL, 'allow', '2025-06-20 01:16:57', '2025-06-20 01:16:57'),
(22, 11, 2, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(23, 12, 2, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(24, 3, 3, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(25, 4, 3, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(26, 2, 3, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(27, 6, 3, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(28, 7, 3, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(29, 5, 3, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(30, 9, 3, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(31, 8, 3, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(32, 3, 4, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(33, 4, 4, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(34, 2, 4, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(35, 6, 4, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(36, 7, 4, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(37, 5, 4, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(38, 9, 4, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(39, 8, 4, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(40, 3, 5, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(41, 4, 5, NULL, 'allow', '2025-06-20 01:16:58', '2025-06-20 01:16:58'),
(42, 2, 5, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(43, 6, 5, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(44, 7, 5, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(45, 5, 5, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(46, 9, 5, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(47, 8, 5, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(48, 12, 5, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(49, 4, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(50, 2, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(51, 6, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(52, 7, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(53, 5, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(54, 9, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(55, 8, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(56, 11, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(57, 12, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(58, 10, 6, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(59, 2, 7, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(60, 7, 7, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(61, 5, 7, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(62, 11, 7, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(63, 12, 7, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(64, 10, 7, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(65, 5, 8, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(66, 11, 8, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(67, 12, 8, NULL, 'allow', '2025-06-20 01:16:59', '2025-06-20 01:16:59'),
(68, 10, 8, NULL, 'allow', '2025-06-20 01:17:00', '2025-06-20 01:17:00'),
(69, 10, 9, NULL, 'allow', '2025-06-20 01:17:00', '2025-06-20 01:17:00'),
(70, 11, 10, NULL, 'allow', '2025-06-20 01:17:00', '2025-06-27 17:45:11'),
(71, 12, 10, NULL, 'allow', '2025-06-20 01:17:00', '2025-06-27 17:45:11'),
(72, 10, 10, NULL, 'allow', '2025-06-20 01:17:00', '2025-06-27 17:45:11'),
(73, 13, 1, NULL, 'allow', '2025-06-20 03:11:54', '2025-06-20 03:11:54'),
(74, 14, 1, NULL, 'allow', '2025-06-20 03:11:54', '2025-06-20 03:11:54'),
(75, 15, 1, NULL, 'allow', '2025-06-20 03:11:54', '2025-06-20 03:11:54'),
(76, 16, 1, NULL, 'allow', '2025-06-20 03:45:28', '2025-06-20 03:45:28'),
(77, 17, 1, NULL, 'allow', '2025-06-20 03:45:28', '2025-06-20 03:45:28'),
(78, 18, 1, NULL, 'allow', '2025-06-20 03:45:28', '2025-06-20 03:45:28'),
(79, 19, 1, NULL, 'allow', '2025-06-20 03:45:28', '2025-06-20 03:45:28'),
(80, 20, 1, NULL, 'allow', '2025-06-20 03:45:28', '2025-06-20 03:45:28'),
(81, 21, 1, NULL, 'allow', '2025-06-20 03:45:28', '2025-06-20 03:45:28'),
(82, 22, 1, NULL, 'allow', '2025-06-20 03:45:28', '2025-06-20 03:45:28'),
(83, 39, 1, NULL, 'allow', '2025-06-26 04:44:26', '2025-06-26 04:44:26'),
(86, 12, 14, NULL, 'allow', '2025-06-27 17:46:41', '2025-06-27 17:46:41');

-- --------------------------------------------------------

--
-- 表的结构 `permission_templates`
--

CREATE TABLE `permission_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `display_name` varchar(150) NOT NULL COMMENT '模板显示名称',
  `description` text DEFAULT NULL COMMENT '模板描述',
  `template_type` enum('role','organization','user') NOT NULL COMMENT '模板类型',
  `target_level` tinyint(4) DEFAULT NULL COMMENT '目标级别',
  `permission_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '权限ID数组' CHECK (json_valid(`permission_ids`)),
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '应用条件' CHECK (json_valid(`conditions`)),
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否系统模板',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认模板',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '创建者ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新者ID',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `permission_templates`
--

INSERT INTO `permission_templates` (`id`, `name`, `display_name`, `description`, `template_type`, `target_level`, `permission_ids`, `conditions`, `is_system`, `is_default`, `status`, `created_by`, `updated_by`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'system_admin', '系统管理员模板', '系统管理员拥有所有系统管理权限，包括用户管理、组织管理、权限管理等', 'role', 1, '[1,2,3,5,6,8,13,14,16,17,19,20,21,28,29,30,31,32,33,34,35,36]', NULL, 1, 1, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18'),
(2, 'province_admin', '省级管理员模板', '省级管理员权限模板，可管理省内的组织机构和用户', 'role', 1, '[2,3,5,6,8,9,13,16,28,29]', NULL, 1, 0, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18'),
(3, 'city_admin', '市级管理员模板', '市级管理员权限模板，可管理市内的组织机构和用户', 'role', 2, '[2,3,5,6,8,9,13,16]', NULL, 0, 1, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18'),
(4, 'district_admin', '区县级管理员模板', '区县级管理员权限模板，可管理区县内的组织机构和用户', 'role', 3, '[2,5,6,8,13,16]', NULL, 0, 1, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18'),
(5, 'school_district_admin', '学区管理员模板', '学区管理员权限模板，可管理学区内的学校和教师', 'role', 4, '[27,2,16,5]', NULL, 0, 1, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18'),
(6, 'school_admin', '学校管理员模板', '学校管理员权限模板，可管理学校内的实验教学', 'role', 5, '[2,5,10,11,12]', NULL, 0, 1, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18'),
(7, 'teacher', '教师权限模板', '教师权限模板，可查看和管理实验教学', 'user', 5, '[11,10,2,5]', NULL, 0, 0, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18'),
(8, 'observer', '观察员权限模板', '观察员权限模板，只能查看相关信息', 'user', NULL, '[10,2,5]', NULL, 0, 0, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18'),
(9, 'auditor', '审计员权限模板', '审计员权限模板，可查看权限审计信息', 'user', NULL, '[2,5,28,31,38]', NULL, 0, 0, 'active', NULL, NULL, 0, '2025-06-22 03:02:18', '2025-06-22 03:02:18');

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
(1, 'App\\Models\\User', 1, 'test-token', '30c9c60ed490f71e57e5ec0bbf6f7dcebcc27c1a5861c56f17d89ef362387e44', '[\"*\"]', NULL, NULL, '2025-06-20 01:18:35', '2025-06-20 01:18:35'),
(2, 'App\\Models\\User', 1, 'auth-token', '12f55ce140ca4b20b530f15da9b087b007b30bdbb9eb2871cb272b98047fabb8', '[\"*\"]', NULL, NULL, '2025-06-20 01:26:54', '2025-06-20 01:26:54'),
(3, 'App\\Models\\User', 1, 'auth-token', '1ac974f8c2e28f97dc6b62fbc7bb7b234650382a75ca17c78584d0537570df25', '[\"*\"]', '2025-06-22 05:12:25', NULL, '2025-06-20 01:29:22', '2025-06-22 05:12:25'),
(4, 'App\\Models\\User', 1, 'test-token', '0a03e38558b50532b1086e4386dbd488e1e1f93cc7a8475d8e93bc8bcc253886', '[\"*\"]', NULL, NULL, '2025-06-20 01:36:23', '2025-06-20 01:36:23'),
(5, 'App\\Models\\User', 1, 'test-token', 'd104bec555514da74178f00ff4677828da6282f91bb2aa020bfa8b585193f9d6', '[\"*\"]', NULL, NULL, '2025-06-20 01:38:59', '2025-06-20 01:38:59'),
(6, 'App\\Models\\User', 1, 'auth-token', '7f6a9efa5097786440ce662e056cf234eaeb8b4bcb59dd521ffe46d0760985d6', '[\"*\"]', '2025-06-20 05:46:45', NULL, '2025-06-20 05:40:05', '2025-06-20 05:46:45'),
(8, 'App\\Models\\User', 1, 'auth-token', '78bf1ca0f864c11b42eb208b47c5dfe929c932828a43d25029858019e66c6bdd', '[\"*\"]', '2025-06-23 22:50:39', NULL, '2025-06-20 22:57:10', '2025-06-23 22:50:39'),
(9, 'App\\Models\\User', 1, 'auth-token', 'b05738097794b4b15f8d6233b944a63dbd4898859b598903ec6c4c24f376215c', '[\"*\"]', '2025-06-21 05:49:11', NULL, '2025-06-21 05:48:51', '2025-06-21 05:49:11'),
(11, 'App\\Models\\User', 1, 'auth-token', '15a9e3005968a92d64d0e1f8640fbb41021bc9d5aca8756375875b6308f0bbc5', '[\"*\"]', NULL, NULL, '2025-06-21 16:49:14', '2025-06-21 16:49:14'),
(13, 'App\\Models\\User', 1, 'auth-token', '48632c6007fdea23f6ae9e69080371ae18f89221ddc2b01d333098256e30e03b', '[\"*\"]', '2025-06-22 04:40:00', NULL, '2025-06-21 19:49:01', '2025-06-22 04:40:00'),
(14, 'App\\Models\\User', 1, 'auth-token', 'a88398cadd3900be05498b4b54e328c9fc69731556e614a5f66743c3b7a64908', '[\"*\"]', '2025-06-22 04:50:41', NULL, '2025-06-22 04:44:43', '2025-06-22 04:50:41'),
(15, 'App\\Models\\User', 1, 'auth-token', '1eba8d620556b283f030ae00d457fdbd4873368131830cf311f93c37a6dd38b9', '[\"*\"]', '2025-06-26 01:44:13', NULL, '2025-06-22 04:51:26', '2025-06-26 01:44:13'),
(16, 'App\\Models\\User', 1, 'auth-token', '0dcca9aef1e71bd2fbc563adc3f0c9486b04f61efa040f21ceac0299d7da0796', '[\"*\"]', '2025-06-22 05:27:36', NULL, '2025-06-22 05:14:48', '2025-06-22 05:27:36'),
(17, 'App\\Models\\User', 1, 'auth-token', '08c670d6728df09bd2225af9f9dbe620a8903b6cff4f2b66077a0fcb0573c8d5', '[\"*\"]', '2025-06-23 23:29:23', NULL, '2025-06-23 22:34:08', '2025-06-23 23:29:23'),
(18, 'App\\Models\\User', 1, 'auth-token', '8d67affca16c5414d4d74151ee2c0be52c1b04462182c8f58d82a00ee9a9079c', '[\"*\"]', '2025-06-24 00:23:40', NULL, '2025-06-23 23:06:50', '2025-06-24 00:23:40'),
(19, 'App\\Models\\User', 1, 'auth-token', 'a7ce306fae8aa778ee6d7f3c1f8fce28590b62db9e02b855c1d897627b0d0098', '[\"*\"]', '2025-06-24 01:36:24', NULL, '2025-06-24 00:29:19', '2025-06-24 01:36:24'),
(20, 'App\\Models\\User', 1, 'auth-token', 'ff82dc35fac4a7c1fb569d0c363c1e883acdfb769b36d864b30cfb58660a3a57', '[\"*\"]', '2025-06-24 01:34:44', NULL, '2025-06-24 00:42:29', '2025-06-24 01:34:44'),
(21, 'App\\Models\\User', 1, 'auth-token', '9d7f372ffcb0254bb75cf6eb92b4a6fd6b8ef90f44641f1113ee3bb8e8cfdfbc', '[\"*\"]', '2025-06-24 06:18:27', NULL, '2025-06-24 01:37:36', '2025-06-24 06:18:27'),
(23, 'App\\Models\\User', 1, 'auth-token', '14df7a73dc3ebcd7a2ff327f987dc5f85959b94c24b594e5427d50caca80d9d4', '[\"*\"]', '2025-06-25 23:59:35', NULL, '2025-06-25 00:49:46', '2025-06-25 23:59:35'),
(24, 'App\\Models\\User', 1, 'auth-token', 'd2f6638468aacb19a5324efcf436b0bb76cc300ad1ae0ed56e73436b6b351024', '[\"*\"]', '2025-06-25 23:54:48', NULL, '2025-06-25 23:54:25', '2025-06-25 23:54:48'),
(25, 'App\\Models\\User', 1, 'auth-token', '6de918783c954bb0717974e3860d61682228ffbcd4a20821a22918120d96da06', '[\"*\"]', '2025-06-26 03:55:09', NULL, '2025-06-26 00:15:41', '2025-06-26 03:55:09'),
(35, 'App\\Models\\User', 1, 'auth-token', 'f82493c119c4640595b6808c11b642b3bffc0d56bd677924d10ad9e293d112aa', '[\"*\"]', '2025-06-26 18:41:11', NULL, '2025-06-26 18:37:37', '2025-06-26 18:41:11'),
(37, 'App\\Models\\User', 1, 'auth-token', 'a7f6a969173c253b0e9786683ec69d7a89311cffacb51cafb75c4898c8e5428e', '[\"*\"]', '2025-06-27 04:31:12', NULL, '2025-06-27 04:31:11', '2025-06-27 04:31:12'),
(38, 'App\\Models\\User', 1, 'auth-token', '7814813786f3b393bf1566f02b18b9bd484499343e7474848e7f7ab07c1294ea', '[\"*\"]', '2025-06-27 04:35:05', NULL, '2025-06-27 04:35:04', '2025-06-27 04:35:05'),
(39, 'App\\Models\\User', 1, 'auth-token', '641c5a1c10ff3ba71fe125267659563b3a728f7026330e1039a87082c314ea50', '[\"*\"]', '2025-06-27 04:37:49', NULL, '2025-06-27 04:37:35', '2025-06-27 04:37:49'),
(43, 'App\\Models\\User', 1, 'auth-token', '512c6de032ad3aabf838337cc3c0e25df082e5dbbf5e8cb3bae6e7e188bd14ad', '[\"*\"]', '2025-06-27 16:52:33', NULL, '2025-06-27 15:36:53', '2025-06-27 16:52:33'),
(44, 'App\\Models\\User', 1, 'auth-token', 'fd019b6b572a0ee6bbe9ea4483a93a6e396b58499d49155ad81e58a740c6a388', '[\"*\"]', '2025-06-27 16:54:27', NULL, '2025-06-27 16:54:15', '2025-06-27 16:54:27'),
(47, 'App\\Models\\User', 1, 'auth-token', 'fe5d0d73243cb803fe5ee21f1d31ef0f8d96c69570a5dc18379eb3b781866d50', '[\"*\"]', '2025-06-27 18:42:02', NULL, '2025-06-27 17:40:13', '2025-06-27 18:42:02');

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

--
-- 转存表中的数据 `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `role_type`, `level`, `applicable_org_types`, `status`, `sort_order`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'system_admin', '系统管理员', '系统最高管理员，拥有所有权限', 'system', 1, '[\"province\",\"city\",\"district\",\"education_zone\",\"school\"]', 'active', 1, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:17', NULL),
(2, 'province_admin', '省级管理员', '省级教育管理员，管理省内所有教育机构', 'system', 1, '[\"province\"]', 'active', 2, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:17', NULL),
(3, 'city_admin', '市级管理员', '市级教育管理员，管理市内所有教育机构', 'system', 2, '[\"city\"]', 'active', 3, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:17', NULL),
(4, 'district_admin', '区县级管理员', '区县级教育管理员，管理区县内所有教育机构', 'system', 3, '[\"district\"]', 'active', 4, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:17', NULL),
(5, 'education_zone_admin', '学区管理员', '学区管理员，管理学区内所有学校', 'system', 4, '[\"education_zone\"]', 'active', 5, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:17', NULL),
(6, 'principal', '校长', '学校校长，管理学校所有事务', 'system', 5, '[\"school\"]', 'active', 6, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:17', NULL),
(7, 'vice_principal', '副校长', '学校副校长，协助校长管理学校事务', 'system', 5, '[\"school\"]', 'active', 7, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:17', NULL),
(8, 'academic_director', '教务主任', '教务主任，负责学校教学管理', 'custom', 5, '[\"school\"]', 'active', 8, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:18', NULL),
(9, 'teacher', '教师', '普通教师，负责教学工作', 'custom', 5, '[\"school\"]', 'active', 9, 1, '2025-06-20 01:16:55', '2025-06-27 04:49:18', NULL),
(10, 'lab_admin', '实验管理员', '实验室管理员，负责实验教学管理', 'custom', 5, '[\"school\"]', 'active', 10, 0, '2025-06-20 01:16:55', '2025-06-27 04:49:18', NULL),
(11, 'org_admin', '组织管理员', '组织管理员角色', 'custom', 2, NULL, 'active', 2, 0, '2025-06-20 05:26:23', '2025-06-20 05:26:23', NULL),
(12, 'dept_admin', '部门管理员', '部门管理员角色', 'custom', 3, NULL, 'active', 3, 0, '2025-06-20 05:26:23', '2025-06-20 05:26:23', NULL),
(13, 'normal_user', '普通用户', '普通用户角色', 'custom', 4, NULL, 'active', 4, 0, '2025-06-20 05:26:23', '2025-06-20 05:26:23', NULL),
(14, 'student', '学生', '学生用户', 'custom', 4, NULL, 'active', 0, 0, '2025-06-27 17:46:41', '2025-06-27 17:46:50', '2025-06-27 17:46:50');

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

--
-- 转存表中的数据 `role_user`
--

INSERT INTO `role_user` (`id`, `user_id`, `role_id`, `organization_id`, `scope_type`, `effective_date`, `expiry_date`, `status`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, 'all_subordinates', '2020-01-01', NULL, 'active', NULL, 1, '2025-06-27 04:49:40', '2025-06-27 04:49:40'),
(2, 2, 5, 4, 'all_subordinates', '2018-09-01', NULL, 'active', NULL, 1, '2025-06-27 04:49:40', '2025-06-27 04:49:40'),
(3, 3, 6, 5, 'current_org', '2015-09-01', NULL, 'active', NULL, 2, '2025-06-27 04:49:40', '2025-06-27 04:49:40'),
(4, 6, 5, 16, 'current_org', NULL, NULL, 'active', NULL, 1, '2025-06-27 15:32:29', '2025-06-27 17:23:19'),
(7, 3, 5, 5, 'current_org', NULL, NULL, 'active', NULL, 1, '2025-06-27 17:24:24', '2025-06-27 17:24:24');

-- --------------------------------------------------------

--
-- 表的结构 `school_import_logs`
--

CREATE TABLE `school_import_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL COMMENT '导入文件名',
  `file_size` bigint(20) NOT NULL COMMENT '文件大小（字节）',
  `file_path` varchar(255) NOT NULL COMMENT '文件存储路径',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '父级组织ID',
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '导入用户ID',
  `status` enum('pending','processing','success','partial_success','failed') NOT NULL DEFAULT 'pending' COMMENT '导入状态',
  `total_rows` int(11) NOT NULL DEFAULT 0 COMMENT '总行数',
  `success_rows` int(11) NOT NULL DEFAULT 0 COMMENT '成功行数',
  `failed_rows` int(11) NOT NULL DEFAULT 0 COMMENT '失败行数',
  `error_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '错误详情' CHECK (json_valid(`error_details`)),
  `warning_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '警告详情' CHECK (json_valid(`warning_details`)),
  `import_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '导入选项' CHECK (json_valid(`import_options`)),
  `started_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `school_locations`
--

CREATE TABLE `school_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '学校组织ID',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT '纬度',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT '经度',
  `address` varchar(500) DEFAULT NULL COMMENT '详细地址',
  `province` varchar(50) DEFAULT NULL COMMENT '省份',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `district` varchar(50) DEFAULT NULL COMMENT '区县',
  `street` varchar(100) DEFAULT NULL COMMENT '街道',
  `postal_code` varchar(10) DEFAULT NULL COMMENT '邮政编码',
  `student_count` int(11) NOT NULL DEFAULT 0 COMMENT '学生数量',
  `teacher_count` int(11) NOT NULL DEFAULT 0 COMMENT '教师数量',
  `class_count` int(11) NOT NULL DEFAULT 0 COMMENT '班级数量',
  `area_size` decimal(8,2) DEFAULT NULL COMMENT '学校面积(平方米)',
  `school_type` enum('primary','middle','high','mixed') NOT NULL DEFAULT 'primary' COMMENT '学校类型',
  `facilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '设施信息' CHECK (json_valid(`facilities`)),
  `remarks` text DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `school_locations`
--

INSERT INTO `school_locations` (`id`, `organization_id`, `latitude`, `longitude`, `address`, `province`, `city`, `district`, `street`, `postal_code`, `student_count`, `teacher_count`, `class_count`, `area_size`, `school_type`, `facilities`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 5, 38.04280000, 114.84560000, '河北省石家庄市藁城区廉州镇东城村', '河北省', '石家庄市', '藁城区', '廉州镇', NULL, 450, 28, 12, 5600.00, 'primary', '\"{\\\"playground\\\":true,\\\"library\\\":true,\\\"computer_room\\\":true,\\\"science_lab\\\":false}\"', NULL, '2025-06-21 06:34:09', '2025-06-21 06:34:09');

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
('qkf9ilrZWSzAgUoO81on3qYxHodpXANFgpecgffQ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; zh-CN) WindowsPowerShell/5.1.26100.4202', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOEN4V2tHMzkzemVlekNBeHFyNDNHSU1UNjA1N1czYWltbzcxaWh0bCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1751027580);

-- --------------------------------------------------------

--
-- 表的结构 `template_permissions`
--

CREATE TABLE `template_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED NOT NULL COMMENT '权限模板ID',
  `permission_id` bigint(20) UNSIGNED NOT NULL COMMENT '权限ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `name` varchar(255) NOT NULL,
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
  `primary_organization_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '主要组织机构ID',
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

INSERT INTO `users` (`id`, `username`, `name`, `real_name`, `gender`, `birth_date`, `phone`, `id_card`, `address`, `department`, `position`, `title`, `hire_date`, `last_login_at`, `last_login_ip`, `preferences`, `remarks`, `email`, `organization_id`, `primary_organization_id`, `employee_id`, `user_type`, `status`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'sysadmin', '系统管理员', '系统管理员', 'male', '1980-01-01', '13800000001', '130100198001010001', '河北省石家庄市长安区', '信息中心', '系统管理员', '高级工程师', '2020-01-01', '2025-06-20 05:40:05', '127.0.0.1', NULL, NULL, 'sysadmin@gcqets.edu.cn', 3, NULL, 'SYS001', 'admin', 'active', '2025-06-27 04:29:12', '$2y$12$8bZMXLBYC0witTO1ZbLKfuMEinI9C5eH6TEwHhTLq8nlFcEYEkgJS', NULL, '2025-06-20 01:16:56', '2025-06-27 04:29:12', NULL),
(2, 'lianzhou_admin', '赵学区主任', '赵学区主任', 'male', '1975-05-15', '13800000002', '130100197505150002', '河北省石家庄市藁城区廉州镇', '廉州学区', '学区主任', '高级教师', '2018-09-01', NULL, NULL, NULL, NULL, 'zhao.admin@gcqets.edu.cn', 4, NULL, 'LZ001', 'supervisor', 'active', '2025-06-27 04:29:12', '$2y$12$cSPnC/RvfHreCpqYU3liLu3Hnc9ZNQP5QYgBTBvXtekCcCBzNUypq', NULL, '2025-06-20 01:16:56', '2025-06-27 04:29:12', NULL),
(3, 'dongcheng_principal', '刘校长', '刘校长', 'male', '1970-08-20', '13800000003', '130100197008200003', '河北省石家庄市藁城区廉州镇东城村', '校长室', '校长', '特级教师', '2015-09-01', NULL, NULL, NULL, NULL, 'liu.principal@dongcheng.edu.cn', 5, NULL, 'DC001', 'admin', 'active', '2025-06-27 04:29:12', '$2y$12$sdzWbPf86hvvl.Z.lnCx2.UDcXqFbaNpnv5gMiTmHPr4QDXTzDWsi', NULL, '2025-06-20 01:16:57', '2025-06-27 04:29:12', NULL),
(6, 'gs_admin', 'hh', 'hh', 'male', NULL, '15123445677', NULL, NULL, 'quzhongxin', 'gly', NULL, NULL, NULL, NULL, NULL, '1133', 'hh78@163.com', NULL, 16, '123', 'teacher', 'active', NULL, '$2y$12$NF8dtcrLqelgpGe7J1aXAuqHulAWi4TagV.xLYrbZsCT1KXiW/UxK', NULL, '2025-06-27 15:32:29', '2025-06-27 17:23:18', NULL);

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

--
-- 转存表中的数据 `user_organizations`
--

INSERT INTO `user_organizations` (`id`, `user_id`, `organization_id`, `is_primary`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, 'active', '2025-06-20 01:17:00', '2025-06-20 01:17:00'),
(2, 2, 4, 1, 'active', '2025-06-20 01:17:00', '2025-06-20 01:17:00'),
(3, 3, 5, 1, 'active', '2025-06-20 01:17:00', '2025-06-20 01:17:00'),
(5, 6, 20, 0, 'active', '2025-06-27 15:32:29', '2025-06-27 17:23:19');

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
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '过期时间',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `source` enum('direct','role','inherited','template') NOT NULL DEFAULT 'direct' COMMENT '权限来源',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `zone_schools`
--

CREATE TABLE `zone_schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `zone_id` bigint(20) UNSIGNED NOT NULL COMMENT '学区ID',
  `school_id` bigint(20) UNSIGNED NOT NULL COMMENT '学校ID',
  `assignment_type` enum('auto','manual') NOT NULL DEFAULT 'auto' COMMENT '分配类型',
  `distance` decimal(10,2) DEFAULT NULL COMMENT '距离学区中心点距离(公里)',
  `assignment_reason` text DEFAULT NULL COMMENT '分配原因',
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '分配人ID',
  `assigned_at` timestamp NULL DEFAULT NULL COMMENT '分配时间',
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
-- 表的索引 `district_assignment_history`
--
ALTER TABLE `district_assignment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `district_assignment_history_old_district_id_foreign` (`old_district_id`),
  ADD KEY `district_assignment_history_operated_by_foreign` (`operated_by`),
  ADD KEY `idx_school_created` (`school_id`,`created_at`),
  ADD KEY `idx_district_type` (`new_district_id`,`assignment_type`),
  ADD KEY `idx_effective_date` (`effective_date`);

--
-- 表的索引 `district_boundaries`
--
ALTER TABLE `district_boundaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `district_boundaries_created_by_foreign` (`created_by`),
  ADD KEY `district_boundaries_updated_by_foreign` (`updated_by`),
  ADD KEY `district_boundaries_education_district_id_status_index` (`education_district_id`,`status`),
  ADD KEY `district_boundaries_center_latitude_center_longitude_index` (`center_latitude`,`center_longitude`);

--
-- 表的索引 `education_zones`
--
ALTER TABLE `education_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `education_zones_code_unique` (`code`),
  ADD KEY `education_zones_district_id_foreign` (`district_id`),
  ADD KEY `education_zones_manager_id_foreign` (`manager_id`);

--
-- 表的索引 `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- 表的索引 `import_logs`
--
ALTER TABLE `import_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `import_logs_user_id_foreign` (`user_id`);

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
-- 表的索引 `organization_import_logs`
--
ALTER TABLE `organization_import_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_import_logs_parent_id_foreign` (`parent_id`),
  ADD KEY `organization_import_logs_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `organization_import_logs_status_index` (`status`);

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
  ADD KEY `permissions_scope_type_index` (`scope_type`),
  ADD KEY `permissions_parent_id_index` (`parent_id`),
  ADD KEY `permissions_group_index` (`group`),
  ADD KEY `permissions_module_index` (`module`),
  ADD KEY `permissions_is_menu_index` (`is_menu`);

--
-- 表的索引 `permission_audit_logs`
--
ALTER TABLE `permission_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_audit_logs_role_id_foreign` (`role_id`),
  ADD KEY `permission_audit_logs_permission_id_foreign` (`permission_id`),
  ADD KEY `permission_audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `permission_audit_logs_target_user_id_action_index` (`target_user_id`,`action`),
  ADD KEY `permission_audit_logs_organization_id_action_index` (`organization_id`,`action`),
  ADD KEY `permission_audit_logs_action_target_type_index` (`action`,`target_type`),
  ADD KEY `permission_audit_logs_created_at_status_index` (`created_at`,`status`);

--
-- 表的索引 `permission_conflicts`
--
ALTER TABLE `permission_conflicts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_conflicts_resolved_by_foreign` (`resolved_by`),
  ADD KEY `permission_conflicts_user_id_status_index` (`user_id`,`status`),
  ADD KEY `permission_conflicts_role_id_conflict_type_index` (`role_id`,`conflict_type`),
  ADD KEY `permission_conflicts_organization_id_priority_index` (`organization_id`,`priority`),
  ADD KEY `permission_conflicts_permission_id_status_index` (`permission_id`,`status`),
  ADD KEY `permission_conflicts_conflict_type_status_index` (`conflict_type`,`status`),
  ADD KEY `permission_conflicts_priority_created_at_index` (`priority`,`created_at`);

--
-- 表的索引 `permission_inheritance`
--
ALTER TABLE `permission_inheritance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_org_permission_inherit` (`parent_organization_id`,`child_organization_id`,`permission_id`),
  ADD KEY `permission_inheritance_child_organization_id_foreign` (`child_organization_id`),
  ADD KEY `permission_inheritance_overridden_by_foreign` (`overridden_by`),
  ADD KEY `idx_parent_child_org` (`parent_organization_id`,`child_organization_id`),
  ADD KEY `idx_permission_inheritance_type` (`permission_id`,`inheritance_type`),
  ADD KEY `idx_overridden_status` (`is_overridden`,`status`);

--
-- 表的索引 `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permission_role` (`permission_id`,`role_id`),
  ADD KEY `permission_role_permission_id_role_id_index` (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_access_type_index` (`role_id`,`access_type`);

--
-- 表的索引 `permission_templates`
--
ALTER TABLE `permission_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_template_name_type` (`name`,`template_type`),
  ADD KEY `permission_templates_created_by_foreign` (`created_by`),
  ADD KEY `permission_templates_updated_by_foreign` (`updated_by`),
  ADD KEY `permission_templates_template_type_target_level_index` (`template_type`,`target_level`),
  ADD KEY `permission_templates_is_system_is_default_index` (`is_system`,`is_default`),
  ADD KEY `permission_templates_status_sort_order_index` (`status`,`sort_order`);

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
-- 表的索引 `school_import_logs`
--
ALTER TABLE `school_import_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_import_logs_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `school_import_logs_status_created_at_index` (`status`,`created_at`),
  ADD KEY `school_import_logs_parent_id_index` (`parent_id`);

--
-- 表的索引 `school_locations`
--
ALTER TABLE `school_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_locations_organization_id_unique` (`organization_id`),
  ADD KEY `school_locations_latitude_longitude_index` (`latitude`,`longitude`),
  ADD KEY `school_locations_province_city_district_index` (`province`,`city`,`district`);

--
-- 表的索引 `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- 表的索引 `template_permissions`
--
ALTER TABLE `template_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_permissions_template_id_permission_id_unique` (`template_id`,`permission_id`),
  ADD KEY `template_permissions_permission_id_foreign` (`permission_id`);

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
  ADD KEY `users_phone_index` (`phone`),
  ADD KEY `users_username_index` (`username`),
  ADD KEY `users_primary_organization_id_index` (`primary_organization_id`);

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
-- 表的索引 `zone_schools`
--
ALTER TABLE `zone_schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zone_schools_zone_id_school_id_unique` (`zone_id`,`school_id`),
  ADD KEY `zone_schools_school_id_foreign` (`school_id`),
  ADD KEY `zone_schools_assigned_by_foreign` (`assigned_by`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `district_assignment_history`
--
ALTER TABLE `district_assignment_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `district_boundaries`
--
ALTER TABLE `district_boundaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `education_zones`
--
ALTER TABLE `education_zones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `import_logs`
--
ALTER TABLE `import_logs`
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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- 使用表AUTO_INCREMENT `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `organization_import_logs`
--
ALTER TABLE `organization_import_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- 使用表AUTO_INCREMENT `permission_audit_logs`
--
ALTER TABLE `permission_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- 使用表AUTO_INCREMENT `permission_conflicts`
--
ALTER TABLE `permission_conflicts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `permission_inheritance`
--
ALTER TABLE `permission_inheritance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- 使用表AUTO_INCREMENT `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- 使用表AUTO_INCREMENT `permission_templates`
--
ALTER TABLE `permission_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- 使用表AUTO_INCREMENT `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用表AUTO_INCREMENT `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `school_import_logs`
--
ALTER TABLE `school_import_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `school_locations`
--
ALTER TABLE `school_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `template_permissions`
--
ALTER TABLE `template_permissions`
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `zone_schools`
--
ALTER TABLE `zone_schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `district_assignment_history`
--
ALTER TABLE `district_assignment_history`
  ADD CONSTRAINT `district_assignment_history_new_district_id_foreign` FOREIGN KEY (`new_district_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `district_assignment_history_old_district_id_foreign` FOREIGN KEY (`old_district_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `district_assignment_history_operated_by_foreign` FOREIGN KEY (`operated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `district_assignment_history_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- 限制表 `district_boundaries`
--
ALTER TABLE `district_boundaries`
  ADD CONSTRAINT `district_boundaries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `district_boundaries_education_district_id_foreign` FOREIGN KEY (`education_district_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `district_boundaries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `education_zones`
--
ALTER TABLE `education_zones`
  ADD CONSTRAINT `education_zones_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `education_zones_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `import_logs`
--
ALTER TABLE `import_logs`
  ADD CONSTRAINT `import_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `organizations`
--
ALTER TABLE `organizations`
  ADD CONSTRAINT `organizations_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- 限制表 `organization_import_logs`
--
ALTER TABLE `organization_import_logs`
  ADD CONSTRAINT `organization_import_logs_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `organization_import_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- 限制表 `permission_audit_logs`
--
ALTER TABLE `permission_audit_logs`
  ADD CONSTRAINT `permission_audit_logs_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_audit_logs_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_audit_logs_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_audit_logs_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `permission_conflicts`
--
ALTER TABLE `permission_conflicts`
  ADD CONSTRAINT `permission_conflicts_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_conflicts_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_conflicts_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_conflicts_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_conflicts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `permission_inheritance`
--
ALTER TABLE `permission_inheritance`
  ADD CONSTRAINT `permission_inheritance_child_organization_id_foreign` FOREIGN KEY (`child_organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_inheritance_overridden_by_foreign` FOREIGN KEY (`overridden_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_inheritance_parent_organization_id_foreign` FOREIGN KEY (`parent_organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_inheritance_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- 限制表 `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- 限制表 `permission_templates`
--
ALTER TABLE `permission_templates`
  ADD CONSTRAINT `permission_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `role_user_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `school_import_logs`
--
ALTER TABLE `school_import_logs`
  ADD CONSTRAINT `school_import_logs_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `school_import_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `school_locations`
--
ALTER TABLE `school_locations`
  ADD CONSTRAINT `school_locations_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- 限制表 `template_permissions`
--
ALTER TABLE `template_permissions`
  ADD CONSTRAINT `template_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `template_permissions_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `permission_templates` (`id`) ON DELETE CASCADE;

--
-- 限制表 `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_primary_organization_id_foreign` FOREIGN KEY (`primary_organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL;

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
-- 限制表 `zone_schools`
--
ALTER TABLE `zone_schools`
  ADD CONSTRAINT `zone_schools_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `zone_schools_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zone_schools_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `education_zones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
