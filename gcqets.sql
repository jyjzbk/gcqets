-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-07-04 16:24:48
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
-- 表的结构 `catalog_import_logs`
--

CREATE TABLE `catalog_import_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL COMMENT '导入文件名',
  `file_size` bigint(20) NOT NULL COMMENT '文件大小（字节）',
  `file_path` varchar(255) NOT NULL COMMENT '文件存储路径',
  `organization_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '目标组织ID',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '导入用户ID',
  `import_type` enum('standard_library','custom','template') NOT NULL COMMENT '导入类型',
  `status` enum('pending','processing','success','partial_success','failed') NOT NULL DEFAULT 'pending' COMMENT '导入状态',
  `total_rows` int(11) NOT NULL DEFAULT 0 COMMENT '总行数',
  `success_rows` int(11) NOT NULL DEFAULT 0 COMMENT '成功行数',
  `failed_rows` int(11) NOT NULL DEFAULT 0 COMMENT '失败行数',
  `skipped_rows` int(11) NOT NULL DEFAULT 0 COMMENT '跳过行数',
  `error_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '错误详情' CHECK (json_valid(`error_details`)),
  `warning_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '警告详情' CHECK (json_valid(`warning_details`)),
  `import_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '导入选项' CHECK (json_valid(`import_options`)),
  `validation_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '校验规则' CHECK (json_valid(`validation_rules`)),
  `started_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `catalog_versions`
--

CREATE TABLE `catalog_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `catalog_id` bigint(20) UNSIGNED NOT NULL COMMENT '实验目录ID',
  `version` varchar(50) NOT NULL COMMENT '版本号',
  `version_description` text DEFAULT NULL COMMENT '版本描述',
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '变更内容' CHECK (json_valid(`changes`)),
  `catalog_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '目录数据快照' CHECK (json_valid(`catalog_data`)),
  `change_type` enum('create','update','delete','restore') NOT NULL COMMENT '变更类型',
  `change_reason` text DEFAULT NULL COMMENT '变更原因',
  `status` enum('active','archived','rollback') NOT NULL DEFAULT 'active' COMMENT '状态',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `catalog_versions`
--

INSERT INTO `catalog_versions` (`id`, `catalog_id`, `version`, `version_description`, `changes`, `catalog_data`, `change_type`, `change_reason`, `status`, `created_by`, `created_at`) VALUES
(1, 1, '1.0.0', '版本 1.0.0 - 创建', '{\"action\":\"\\u521b\\u5efa\\u5b9e\\u9a8c\\u76ee\\u5f55\"}', '{\"id\":1,\"name\":\"\\u89c2\\u5bdf\\u690d\\u7269\\u7684\\u6839\\u3001\\u830e\\u3001\\u53f6\",\"code\":\"EXP_SCI_001\",\"subject\":\"science\",\"grade\":\"grade3\",\"textbook_version\":\"\\u4eba\\u6559\\u72482022\",\"experiment_type\":\"group\",\"description\":\"\\u901a\\u8fc7\\u89c2\\u5bdf\\u4e0d\\u540c\\u690d\\u7269\\u7684\\u6839\\u3001\\u830e\\u3001\\u53f6\\uff0c\\u4e86\\u89e3\\u690d\\u7269\\u7684\\u57fa\\u672c\\u7ed3\\u6784\\u7279\\u5f81\\u3002\",\"objectives\":\"1. \\u8ba4\\u8bc6\\u690d\\u7269\\u7684\\u57fa\\u672c\\u7ed3\\u6784\\\\n2. \\u5b66\\u4f1a\\u4f7f\\u7528\\u653e\\u5927\\u955c\\u89c2\\u5bdf\\\\n3. \\u57f9\\u517b\\u89c2\\u5bdf\\u8bb0\\u5f55\\u80fd\\u529b\",\"materials\":\"\\u653e\\u5927\\u955c\\u3001\\u5404\\u79cd\\u690d\\u7269\\u6807\\u672c\\u3001\\u8bb0\\u5f55\\u8868\\u3001\\u5f69\\u8272\\u94c5\\u7b14\",\"procedures\":\"1. \\u51c6\\u5907\\u89c2\\u5bdf\\u6750\\u6599\\\\n2. \\u4f7f\\u7528\\u653e\\u5927\\u955c\\u4ed4\\u7ec6\\u89c2\\u5bdf\\u690d\\u7269\\u7684\\u6839\\u3001\\u830e\\u3001\\u53f6\\\\n3. \\u8bb0\\u5f55\\u89c2\\u5bdf\\u7ed3\\u679c\\\\n4. \\u5bf9\\u6bd4\\u4e0d\\u540c\\u690d\\u7269\\u7684\\u7279\\u5f81\\\\n5. \\u603b\\u7ed3\\u690d\\u7269\\u7ed3\\u6784\\u7684\\u5171\\u540c\\u70b9\\u548c\\u4e0d\\u540c\\u70b9\",\"safety_notes\":\"1. \\u5c0f\\u5fc3\\u4f7f\\u7528\\u653e\\u5927\\u955c\\uff0c\\u907f\\u514d\\u9633\\u5149\\u76f4\\u5c04\\u773c\\u775b\\\\n2. \\u8f7b\\u62ff\\u8f7b\\u653e\\u690d\\u7269\\u6807\\u672c\\\\n3. \\u4fdd\\u6301\\u5b9e\\u9a8c\\u53f0\\u9762\\u6574\\u6d01\",\"duration_minutes\":40,\"student_count\":4,\"difficulty_level\":\"easy\",\"status\":\"active\",\"curriculum_standard_id\":1,\"organization_id\":5,\"created_by\":3,\"updated_by\":null,\"extra_data\":{\"season\":\"spring\",\"location\":\"classroom\",\"equipment_needed\":[\"magnifier\",\"specimens\",\"worksheets\"]},\"created_at\":\"2025-07-02T06:51:30.000000Z\",\"updated_at\":\"2025-07-02T06:51:30.000000Z\",\"deleted_at\":null}', 'create', '初始创建', 'active', 3, '2025-07-01 22:51:30'),
(2, 2, '1.0.0', '版本 1.0.0 - 创建', '{\"action\":\"\\u521b\\u5efa\\u5b9e\\u9a8c\\u76ee\\u5f55\"}', '{\"id\":2,\"name\":\"\\u9178\\u78b1\\u6307\\u793a\\u5242\\u7684\\u53d8\\u8272\\u5b9e\\u9a8c\",\"code\":\"EXP_CHEM_001\",\"subject\":\"chemistry\",\"grade\":\"grade9\",\"textbook_version\":\"\\u4eba\\u6559\\u72482022\",\"experiment_type\":\"demonstration\",\"description\":\"\\u901a\\u8fc7\\u9178\\u78b1\\u6307\\u793a\\u5242\\u5728\\u4e0d\\u540c\\u6eb6\\u6db2\\u4e2d\\u7684\\u53d8\\u8272\\u73b0\\u8c61\\uff0c\\u8ba4\\u8bc6\\u9178\\u78b1\\u6027\\u8d28\\u3002\",\"objectives\":\"1. \\u8ba4\\u8bc6\\u5e38\\u89c1\\u7684\\u9178\\u78b1\\u6307\\u793a\\u5242\\\\n2. \\u89c2\\u5bdf\\u6307\\u793a\\u5242\\u7684\\u53d8\\u8272\\u73b0\\u8c61\\\\n3. \\u7406\\u89e3\\u9178\\u78b1\\u7684\\u6982\\u5ff5\",\"materials\":\"\\u77f3\\u854a\\u8bd5\\u6db2\\u3001\\u915a\\u915e\\u8bd5\\u6db2\\u3001\\u7a00\\u76d0\\u9178\\u3001\\u7a00\\u6c22\\u6c27\\u5316\\u94a0\\u6eb6\\u6db2\\u3001\\u8bd5\\u7ba1\\u3001\\u6ef4\\u7ba1\",\"procedures\":\"1. \\u51c6\\u5907\\u5b9e\\u9a8c\\u5668\\u6750\\u548c\\u8bd5\\u5242\\\\n2. \\u5728\\u8bd5\\u7ba1\\u4e2d\\u5206\\u522b\\u52a0\\u5165\\u7a00\\u76d0\\u9178\\u548c\\u7a00\\u6c22\\u6c27\\u5316\\u94a0\\u6eb6\\u6db2\\\\n3. \\u5206\\u522b\\u6ef4\\u5165\\u77f3\\u854a\\u8bd5\\u6db2\\uff0c\\u89c2\\u5bdf\\u989c\\u8272\\u53d8\\u5316\\\\n4. \\u91cd\\u590d\\u6b65\\u9aa4\\uff0c\\u4f7f\\u7528\\u915a\\u915e\\u8bd5\\u6db2\\\\n5. \\u8bb0\\u5f55\\u5b9e\\u9a8c\\u73b0\\u8c61\\\\n6. \\u5206\\u6790\\u5b9e\\u9a8c\\u7ed3\\u679c\",\"safety_notes\":\"1. \\u7a7f\\u6234\\u5b9e\\u9a8c\\u670d\\u548c\\u62a4\\u76ee\\u955c\\\\n2. \\u5c0f\\u5fc3\\u4f7f\\u7528\\u9178\\u78b1\\u6eb6\\u6db2\\uff0c\\u907f\\u514d\\u63a5\\u89e6\\u76ae\\u80a4\\\\n3. \\u5b9e\\u9a8c\\u540e\\u53ca\\u65f6\\u6e05\\u6d17\\u5668\\u6750\\\\n4. \\u4fdd\\u6301\\u5b9e\\u9a8c\\u5ba4\\u901a\\u98ce\",\"duration_minutes\":45,\"student_count\":30,\"difficulty_level\":\"medium\",\"status\":\"active\",\"curriculum_standard_id\":2,\"organization_id\":5,\"created_by\":3,\"updated_by\":null,\"extra_data\":{\"safety_level\":\"medium\",\"location\":\"chemistry_lab\",\"equipment_needed\":[\"test_tubes\",\"droppers\",\"indicators\",\"solutions\"]},\"created_at\":\"2025-07-02T06:51:30.000000Z\",\"updated_at\":\"2025-07-02T06:51:30.000000Z\",\"deleted_at\":null}', 'create', '初始创建', 'active', 3, '2025-07-01 22:51:30'),
(3, 3, '1.0.0', '版本 1.0.0 - 创建', '{\"action\":\"\\u521b\\u5efa\\u5b9e\\u9a8c\\u76ee\\u5f55\"}', '{\"id\":3,\"name\":\"111\",\"code\":\"AA111\",\"subject\":\"science\",\"grade\":\"grade1\",\"textbook_version\":\"\\u6211\\u6211\",\"experiment_type\":\"demonstration\",\"description\":\"\\u5de5\",\"objectives\":\"\\u591a\",\"materials\":\"\\u591a\\u5c11 \\u4eba\",\"procedures\":\"\\u94dd\\u5408\\u91d1\",\"safety_notes\":\"\\u6b20\",\"duration_minutes\":22,\"student_count\":11,\"difficulty_level\":\"medium\",\"status\":\"draft\",\"curriculum_standard_id\":1,\"organization_id\":3,\"created_by\":1,\"updated_by\":null,\"extra_data\":null,\"created_at\":\"2025-07-04T13:31:10.000000Z\",\"updated_at\":\"2025-07-04T13:31:10.000000Z\",\"deleted_at\":null}', 'create', '新建实验目录', 'active', 1, '2025-07-04 05:31:10'),
(4, 4, '1.0.0', '版本 1.0.0 - 创建', '{\"action\":\"\\u521b\\u5efa\\u5b9e\\u9a8c\\u76ee\\u5f55\"}', '{\"id\":4,\"name\":\"222\",\"code\":\"b2222\",\"subject\":\"science\",\"grade\":\"grade2\",\"textbook_version\":\"asd\",\"experiment_type\":\"demonstration\",\"description\":\"qqq\",\"objectives\":\"qqq\",\"materials\":\"qqq\",\"procedures\":\"qqq\",\"safety_notes\":\"qq\",\"duration_minutes\":22,\"student_count\":22,\"difficulty_level\":\"medium\",\"status\":\"draft\",\"curriculum_standard_id\":1,\"organization_id\":5,\"created_by\":3,\"updated_by\":null,\"extra_data\":null,\"created_at\":\"2025-07-04T13:34:17.000000Z\",\"updated_at\":\"2025-07-04T13:34:17.000000Z\",\"deleted_at\":null}', 'create', '新建实验目录', 'active', 3, '2025-07-04 05:34:17');

-- --------------------------------------------------------

--
-- 表的结构 `curriculum_standards`
--

CREATE TABLE `curriculum_standards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL COMMENT '课程标准名称',
  `code` varchar(50) NOT NULL COMMENT '标准编码',
  `version` varchar(50) NOT NULL COMMENT '版本号',
  `subject` enum('physics','chemistry','biology','science') NOT NULL COMMENT '学科',
  `grade` enum('grade1','grade2','grade3','grade4','grade5','grade6','grade7','grade8','grade9') NOT NULL COMMENT '年级',
  `content` text DEFAULT NULL COMMENT '标准内容',
  `learning_objectives` text DEFAULT NULL COMMENT '学习目标',
  `key_concepts` text DEFAULT NULL COMMENT '核心概念',
  `skills_requirements` text DEFAULT NULL COMMENT '技能要求',
  `assessment_criteria` text DEFAULT NULL COMMENT '评价标准',
  `effective_date` date DEFAULT NULL COMMENT '生效日期',
  `expiry_date` date DEFAULT NULL COMMENT '失效日期',
  `status` enum('active','inactive','draft','archived') NOT NULL DEFAULT 'draft' COMMENT '状态',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `curriculum_standards`
--

INSERT INTO `curriculum_standards` (`id`, `name`, `code`, `version`, `subject`, `grade`, `content`, `learning_objectives`, `key_concepts`, `skills_requirements`, `assessment_criteria`, `effective_date`, `expiry_date`, `status`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '小学科学课程标准（2022版）', 'CS_SCI_2022', '2022.1', 'science', 'grade3', '小学三年级科学课程标准，注重培养学生的科学思维和实践能力。', '1. 培养学生观察能力\\n2. 培养学生实验操作能力\\n3. 培养学生科学思维', '物质的性质、生命现象、地球与宇宙', '观察、记录、分析、总结', '实验操作规范性、观察记录完整性、分析总结准确性', '2022-09-01', NULL, 'active', 1, 1, NULL, NULL, '2025-07-01 22:51:29', '2025-07-01 22:51:29', NULL),
(2, '初中化学课程标准（2022版）', 'CS_CHEM_2022', '2022.1', 'chemistry', 'grade9', '初中九年级化学课程标准，重点培养学生化学实验技能和安全意识。', '1. 掌握基本化学实验操作\\n2. 理解化学反应原理\\n3. 培养安全实验意识', '化学反应、物质组成、化学方程式', '实验操作、数据分析、安全防护', '实验安全性、操作准确性、结果分析能力', '2022-09-01', NULL, 'active', 1, 1, NULL, NULL, '2025-07-01 22:51:29', '2025-07-01 22:51:29', NULL);

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
-- 表的结构 `equipment`
--

CREATE TABLE `equipment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL COMMENT '设备名称',
  `code` varchar(100) NOT NULL COMMENT '设备编码',
  `model` varchar(100) DEFAULT NULL COMMENT '设备型号',
  `brand` varchar(100) DEFAULT NULL COMMENT '品牌',
  `description` text DEFAULT NULL COMMENT '设备描述',
  `category_id` bigint(20) UNSIGNED NOT NULL COMMENT '设备分类ID',
  `serial_number` varchar(100) DEFAULT NULL COMMENT '序列号',
  `purchase_price` decimal(10,2) DEFAULT NULL COMMENT '采购价格',
  `purchase_date` date DEFAULT NULL COMMENT '采购日期',
  `supplier` varchar(200) DEFAULT NULL COMMENT '供应商',
  `warranty_date` date DEFAULT NULL COMMENT '保修期至',
  `status` enum('available','borrowed','maintenance','damaged','scrapped') NOT NULL DEFAULT 'available' COMMENT '设备状态',
  `location` varchar(200) DEFAULT NULL COMMENT '存放位置',
  `usage_notes` text DEFAULT NULL COMMENT '使用说明',
  `maintenance_notes` text DEFAULT NULL COMMENT '维护记录',
  `total_usage_count` int(11) NOT NULL DEFAULT 0 COMMENT '总使用次数',
  `total_usage_hours` int(11) NOT NULL DEFAULT 0 COMMENT '总使用时长(小时)',
  `last_maintenance_date` date DEFAULT NULL COMMENT '最后维护日期',
  `next_maintenance_date` date DEFAULT NULL COMMENT '下次维护日期',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `code`, `model`, `brand`, `description`, `category_id`, `serial_number`, `purchase_price`, `purchase_date`, `supplier`, `warranty_date`, `status`, `location`, `usage_notes`, `maintenance_notes`, `total_usage_count`, `total_usage_hours`, `last_maintenance_date`, `next_maintenance_date`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '数字显微镜', 'EQ_MICROSCOPE_001', 'DM-2000X', '奥林巴斯', '高倍数字显微镜，适用于生物观察', 2, 'OLY2024001', 15000.00, '2024-01-15', '科学仪器有限公司', '2027-01-15', 'available', '物理实验室A', '使用前请检查镜头清洁度', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(2, '电子天平', 'EQ_BALANCE_001', 'FA2004N', '上海精科', '精密电子天平，精度0.1mg', 4, 'SJ2024001', 8000.00, '2024-02-20', '实验设备公司', '2027-02-20', 'borrowed', '化学实验室B', '称量前需预热30分钟', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-04 05:02:56', NULL),
(3, '力学实验台', 'EQ_MECHANICS_001', 'LX-2024', '教学设备厂', '多功能力学实验台', 3, 'JX2024001', 5000.00, '2024-03-10', '教育装备公司', '2026-03-10', 'available', '物理实验室C', '实验前检查各部件连接', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(4, '激光器', 'EQ_LASER_001', 'LD-650', '激光科技', '红光激光器，功率5mW', 2, 'LS2024001', 3000.00, '2024-04-05', '光电设备公司', '2026-04-05', 'maintenance', '物理实验室A', '使用时必须佩戴防护眼镜', '激光功率不稳定，需要校准', 0, 0, '2024-06-01', '2024-12-01', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(5, '示波器', 'EQ_OSCILLOSCOPE_001', 'DS1054Z', '普源精电', '数字示波器，4通道50MHz', 1, 'DS2024001', 3500.00, '2024-03-15', '仪器设备公司', '2027-03-15', 'available', '物理实验室B', '使用前需预热5分钟', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(6, '万用表', 'EQ_MULTIMETER_001', 'UT61E', '优利德', '数字万用表，真有效值', 1, 'UT2024001', 280.00, '2024-04-10', '电子仪器商城', '2026-04-10', 'available', '物理实验室A', '测量前检查档位设置', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(7, '光谱仪', 'EQ_SPECTROMETER_001', 'SP-2000', '海光仪器', '可见光分光光度计', 1, 'HG2024001', 12000.00, '2024-02-20', '光学仪器公司', '2027-02-20', 'maintenance', '物理实验室C', '需要专业人员操作', '光源需要更换', 0, 0, '2024-06-15', '2024-12-15', 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(8, '酸度计', 'EQ_PH_METER_001', 'PHS-3C', '雷磁', '台式酸度计，精度0.01pH', 4, 'LC2024001', 1800.00, '2024-03-25', '化学仪器公司', '2026-03-25', 'available', '化学实验室A', '使用前需校准', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(9, '离心机', 'EQ_CENTRIFUGE_001', 'TDL-5-A', '安亭科学', '台式低速离心机', 4, 'AT2024001', 4500.00, '2024-01-30', '实验设备公司', '2026-01-30', 'borrowed', '化学实验室B', '使用时注意平衡', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(10, '培养箱', 'EQ_INCUBATOR_001', 'DHP-9272', '一恒', '电热恒温培养箱', 5, 'YH2024001', 3200.00, '2024-02-15', '生物仪器公司', '2026-02-15', 'available', '生物实验室A', '温度设置不超过60℃', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(11, '超净工作台', 'EQ_CLEAN_BENCH_001', 'SW-CJ-1F', '苏净', '单人单面净化工作台', 5, 'SJ2024001', 5800.00, '2024-03-05', '净化设备公司', '2026-03-05', 'available', '生物实验室B', '使用前紫外消毒30分钟', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(12, '投影仪', 'EQ_PROJECTOR_001', 'EB-X41', '爱普生', '教学投影仪，3600流明', 6, 'EP2024001', 2800.00, '2024-04-20', '教学设备公司', '2026-04-20', 'available', '多媒体教室', '使用后及时关闭', NULL, 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(13, '打印机', 'EQ_PRINTER_001', 'LaserJet Pro M404n', '惠普', '黑白激光打印机', 6, 'HP2024001', 1200.00, '2024-05-10', '办公设备公司', '2025-05-10', 'damaged', '办公室', '定期更换硒鼓', '进纸器故障，需要维修', 0, 0, NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `equipment_borrowings`
--

CREATE TABLE `equipment_borrowings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `borrowing_code` varchar(50) NOT NULL COMMENT '借用编号',
  `equipment_id` bigint(20) UNSIGNED NOT NULL COMMENT '设备ID',
  `borrower_id` bigint(20) UNSIGNED NOT NULL COMMENT '借用人ID',
  `approver_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '审批人ID',
  `purpose` text NOT NULL COMMENT '借用目的',
  `planned_start_time` datetime NOT NULL COMMENT '计划开始时间',
  `planned_end_time` datetime NOT NULL COMMENT '计划结束时间',
  `actual_start_time` datetime DEFAULT NULL COMMENT '实际开始时间',
  `actual_end_time` datetime DEFAULT NULL COMMENT '实际结束时间',
  `status` enum('pending','approved','rejected','borrowed','returned','overdue','cancelled') NOT NULL DEFAULT 'pending' COMMENT '借用状态',
  `approval_notes` text DEFAULT NULL COMMENT '审批备注',
  `borrowing_notes` text DEFAULT NULL COMMENT '借用备注',
  `return_notes` text DEFAULT NULL COMMENT '归还备注',
  `equipment_condition_before` enum('good','normal','damaged') DEFAULT NULL COMMENT '借用前设备状态',
  `equipment_condition_after` enum('good','normal','damaged') DEFAULT NULL COMMENT '归还后设备状态',
  `approved_at` datetime DEFAULT NULL COMMENT '审批时间',
  `borrowed_at` datetime DEFAULT NULL COMMENT '借用时间',
  `returned_at` datetime DEFAULT NULL COMMENT '归还时间',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `equipment_borrowings`
--

INSERT INTO `equipment_borrowings` (`id`, `borrowing_code`, `equipment_id`, `borrower_id`, `approver_id`, `purpose`, `planned_start_time`, `planned_end_time`, `actual_start_time`, `actual_end_time`, `status`, `approval_notes`, `borrowing_notes`, `return_notes`, `equipment_condition_before`, `equipment_condition_after`, `approved_at`, `borrowed_at`, `returned_at`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'BOR_20250703025020_1', 1, 6, 1, '物理光学实验教学', '2025-07-04 02:50:20', '2025-07-06 02:50:20', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, NULL, '2025-07-04 13:02:30', NULL, NULL, 5, 6, NULL, NULL, '2025-07-02 18:50:20', '2025-07-04 05:02:30', NULL),
(2, 'BOR_20250703025020_2', 2, 6, 1, '化学分析实验', '2025-07-01 02:50:20', '2025-07-04 02:50:20', '2025-07-04 13:02:56', NULL, 'borrowed', '实验需要，批准借用', NULL, NULL, 'good', NULL, '2025-07-02 02:50:20', '2025-07-04 13:02:56', NULL, 5, 6, NULL, NULL, '2025-07-02 18:50:20', '2025-07-04 05:02:56', NULL),
(3, 'BOR_20250703025020_3', 3, 6, 1, '学生实验课程', '2025-06-28 02:50:20', '2025-06-30 02:50:20', NULL, NULL, 'borrowed', '同意借用', '设备状态良好', NULL, 'good', NULL, '2025-06-29 02:50:20', '2025-06-29 02:50:20', NULL, 5, 6, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL),
(4, 'BOR_20250703025020_4', 5, 6, 1, '教师演示实验', '2025-06-23 02:50:20', '2025-06-25 02:50:20', '2025-06-24 02:50:20', '2025-06-26 02:50:20', 'returned', '批准借用', '设备正常', '使用正常，已归还', 'good', 'good', '2025-06-24 02:50:20', '2025-06-24 02:50:20', '2025-06-26 02:50:20', 5, 6, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `equipment_categories`
--

CREATE TABLE `equipment_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '分类名称',
  `code` varchar(50) NOT NULL COMMENT '分类编码',
  `description` text DEFAULT NULL COMMENT '分类描述',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '父级分类ID',
  `subject` varchar(50) DEFAULT NULL COMMENT '适用学科',
  `grade_range` varchar(100) DEFAULT NULL COMMENT '适用年级范围',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `equipment_categories`
--

INSERT INTO `equipment_categories` (`id`, `name`, `code`, `description`, `parent_id`, `subject`, `grade_range`, `sort_order`, `status`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '物理实验设备', 'PHYS_EQ', '物理学科实验设备', NULL, 'physics', 'grade7-12', 1, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(2, '光学设备', 'PHYS_OPTICS', '光学实验设备', 1, 'physics', 'grade7-12', 1, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(3, '力学设备', 'PHYS_MECHANICS', '力学实验设备', 1, 'physics', 'grade7-12', 2, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(4, '化学实验设备', 'CHEM_EQ', '化学学科实验设备', NULL, 'chemistry', 'grade7-12', 2, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(5, '生物实验设备', 'BIO_EQ', '生物学科实验设备', NULL, 'biology', 'grade7-12', 3, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(6, '通用实验设备', 'GEN_EQ', '通用实验设备', NULL, 'general', 'all', 4, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `experiment_catalogs`
--

CREATE TABLE `experiment_catalogs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL COMMENT '实验名称',
  `code` varchar(50) NOT NULL COMMENT '实验编码',
  `subject` enum('physics','chemistry','biology','science') NOT NULL COMMENT '学科',
  `grade` enum('grade1','grade2','grade3','grade4','grade5','grade6','grade7','grade8','grade9') NOT NULL COMMENT '年级',
  `textbook_version` varchar(100) NOT NULL COMMENT '教材版本',
  `experiment_type` enum('demonstration','group','individual','inquiry') NOT NULL COMMENT '实验类型',
  `description` text DEFAULT NULL COMMENT '实验描述',
  `objectives` text DEFAULT NULL COMMENT '实验目标',
  `materials` text DEFAULT NULL COMMENT '实验材料',
  `procedures` text DEFAULT NULL COMMENT '实验步骤',
  `safety_notes` text DEFAULT NULL COMMENT '安全注意事项',
  `duration_minutes` int(11) DEFAULT NULL COMMENT '实验时长(分钟)',
  `student_count` int(11) DEFAULT NULL COMMENT '适合学生数量',
  `difficulty_level` enum('easy','medium','hard') NOT NULL DEFAULT 'medium' COMMENT '难度等级',
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'draft' COMMENT '状态',
  `curriculum_standard_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '课程标准ID',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `experiment_catalogs`
--

INSERT INTO `experiment_catalogs` (`id`, `name`, `code`, `subject`, `grade`, `textbook_version`, `experiment_type`, `description`, `objectives`, `materials`, `procedures`, `safety_notes`, `duration_minutes`, `student_count`, `difficulty_level`, `status`, `curriculum_standard_id`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '观察植物的根、茎、叶', 'EXP_SCI_001', 'science', 'grade3', '人教版2022', 'group', '通过观察不同植物的根、茎、叶，了解植物的基本结构特征。', '1. 认识植物的基本结构\\n2. 学会使用放大镜观察\\n3. 培养观察记录能力', '放大镜、各种植物标本、记录表、彩色铅笔', '1. 准备观察材料\\n2. 使用放大镜仔细观察植物的根、茎、叶\\n3. 记录观察结果\\n4. 对比不同植物的特征\\n5. 总结植物结构的共同点和不同点', '1. 小心使用放大镜，避免阳光直射眼睛\\n2. 轻拿轻放植物标本\\n3. 保持实验台面整洁', 40, 4, 'easy', 'active', 1, 5, 3, NULL, '{\"season\":\"spring\",\"location\":\"classroom\",\"equipment_needed\":[\"magnifier\",\"specimens\",\"worksheets\"]}', '2025-07-01 22:51:30', '2025-07-01 22:51:30', NULL),
(2, '酸碱指示剂的变色实验', 'EXP_CHEM_001', 'chemistry', 'grade9', '人教版2022', 'demonstration', '通过酸碱指示剂在不同溶液中的变色现象，认识酸碱性质。', '1. 认识常见的酸碱指示剂\\n2. 观察指示剂的变色现象\\n3. 理解酸碱的概念', '石蕊试液、酚酞试液、稀盐酸、稀氢氧化钠溶液、试管、滴管', '1. 准备实验器材和试剂\\n2. 在试管中分别加入稀盐酸和稀氢氧化钠溶液\\n3. 分别滴入石蕊试液，观察颜色变化\\n4. 重复步骤，使用酚酞试液\\n5. 记录实验现象\\n6. 分析实验结果', '1. 穿戴实验服和护目镜\\n2. 小心使用酸碱溶液，避免接触皮肤\\n3. 实验后及时清洗器材\\n4. 保持实验室通风', 45, 30, 'medium', 'active', 2, 5, 3, NULL, '{\"safety_level\":\"medium\",\"location\":\"chemistry_lab\",\"equipment_needed\":[\"test_tubes\",\"droppers\",\"indicators\",\"solutions\"]}', '2025-07-01 22:51:30', '2025-07-01 22:51:30', NULL),
(3, '111', 'AA111', 'science', 'grade1', '我我', 'demonstration', '工', '多', '多少 人', '铝合金', '欠', 22, 11, 'medium', 'draft', 1, 3, 1, NULL, NULL, '2025-07-04 05:31:10', '2025-07-04 05:31:10', NULL),
(4, '222', 'b2222', 'science', 'grade2', 'asd', 'demonstration', 'qqq', 'qqq', 'qqq', 'qqq', 'qq', 22, 22, 'medium', 'draft', 1, 5, 3, NULL, NULL, '2025-07-04 05:34:17', '2025-07-04 05:34:17', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `experiment_photos`
--

CREATE TABLE `experiment_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `experiment_record_id` bigint(20) UNSIGNED NOT NULL COMMENT '实验记录ID',
  `photo_type` enum('preparation','process','result','equipment','safety') NOT NULL COMMENT '照片类型',
  `file_path` varchar(500) NOT NULL COMMENT '文件路径',
  `file_name` varchar(255) NOT NULL COMMENT '文件名',
  `original_name` varchar(255) NOT NULL COMMENT '原始文件名',
  `file_size` int(11) NOT NULL COMMENT '文件大小(字节)',
  `mime_type` varchar(100) NOT NULL COMMENT 'MIME类型',
  `width` int(11) DEFAULT NULL COMMENT '图片宽度',
  `height` int(11) DEFAULT NULL COMMENT '图片高度',
  `upload_method` enum('mobile','web') NOT NULL DEFAULT 'web' COMMENT '上传方式',
  `location_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '位置信息(经纬度)' CHECK (json_valid(`location_info`)),
  `timestamp_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '时间戳信息' CHECK (json_valid(`timestamp_info`)),
  `watermark_applied` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已添加水印',
  `ai_analysis_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'AI分析结果' CHECK (json_valid(`ai_analysis_result`)),
  `compliance_status` enum('pending','compliant','non_compliant','needs_review') NOT NULL DEFAULT 'pending' COMMENT '合规状态',
  `description` text DEFAULT NULL COMMENT '照片描述',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `is_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否必需照片',
  `hash` varchar(64) DEFAULT NULL COMMENT '文件哈希值',
  `exif_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'EXIF数据' CHECK (json_valid(`exif_data`)),
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `experiment_photos`
--

INSERT INTO `experiment_photos` (`id`, `experiment_record_id`, `photo_type`, `file_path`, `file_name`, `original_name`, `file_size`, `mime_type`, `width`, `height`, `upload_method`, `location_info`, `timestamp_info`, `watermark_applied`, `ai_analysis_result`, `compliance_status`, `description`, `sort_order`, `is_required`, `hash`, `exif_data`, `organization_id`, `created_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'preparation', 'experiment_photos/1/preparation/sample_preparation.jpg', 'sample_preparation.jpg', '实验preparation照片.jpg', 776333, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, NULL, 'compliant', '实验前的器材准备情况，所有设备摆放整齐', 0, 0, 'da7fe8079fd6fab77accd1bbe74c523bfe2d781da0b73a624041c219dc1c2169', NULL, 5, 6, NULL, '2025-07-03 02:35:10', '2025-07-03 02:35:10', NULL),
(2, 1, 'process', 'experiment_photos/1/process/sample_process.jpg', 'sample_process.jpg', '实验process照片.jpg', 1445910, 'image/jpeg', 1920, 1080, 'mobile', NULL, NULL, 1, NULL, 'compliant', '学生正在进行实验操作的过程记录', 0, 0, 'ca3950f0f588fed7fac3106c7f5793145126783223868d1644d68726cbd580a0', NULL, 5, 6, NULL, '2025-07-03 02:35:10', '2025-07-03 02:35:10', NULL),
(3, 1, 'result', 'experiment_photos/1/result/sample_result.jpg', 'sample_result.jpg', '实验result照片.jpg', 1153269, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, NULL, 'compliant', '实验结果展示，可以清楚看到实验现象', 0, 0, '269caf873459d6001102d463eda9d00cbcc0bbdf29fa2a97d4fa2f3f358b9ad3', NULL, 5, 6, NULL, '2025-07-03 02:35:10', '2025-07-03 02:35:10', NULL),
(4, 2, 'preparation', 'experiment_photos/2/preparation/sample_preparation.jpg', 'sample_preparation.jpg', '实验preparation照片.jpg', 575029, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, '{\"blur_detection\":{\"is_blurry\":false,\"blur_score\":0.1},\"content_detection\":{\"has_experiment_content\":true,\"confidence\":0.85},\"safety_check\":{\"safety_violations\":[],\"is_safe\":true},\"analyzed_at\":\"2025-07-04T01:02:01.378683Z\"}', 'compliant', '实验前的器材准备情况，所有设备摆放整齐', 0, 0, 'acacdec04c5ce5b8ae78db590c9b76e97dc34c2ba81ed4b623f5f78b3c8ddcf5', NULL, 6, 6, NULL, '2025-07-03 02:35:11', '2025-07-03 17:02:01', NULL),
(5, 2, 'process', 'experiment_photos/2/process/sample_process.jpg', 'sample_process.jpg', '实验process照片.jpg', 523300, 'image/jpeg', 1920, 1080, 'mobile', NULL, NULL, 1, '{\"blur_detection\":{\"is_blurry\":false,\"blur_score\":0.1},\"content_detection\":{\"has_experiment_content\":true,\"confidence\":0.85},\"safety_check\":{\"safety_violations\":[],\"is_safe\":true},\"analyzed_at\":\"2025-07-04T01:02:01.381811Z\"}', 'compliant', '学生正在进行实验操作的过程记录', 0, 0, 'b0eceda3086b9c65c46e33a4b83f11f48c3d92557dc3312f5a93903e921e8e1d', NULL, 6, 6, NULL, '2025-07-03 02:35:11', '2025-07-03 17:02:01', NULL),
(6, 2, 'result', 'experiment_photos/2/result/sample_result.jpg', 'sample_result.jpg', '实验result照片.jpg', 1713298, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, '{\"blur_detection\":{\"is_blurry\":false,\"blur_score\":0.1},\"content_detection\":{\"has_experiment_content\":true,\"confidence\":0.85},\"safety_check\":{\"safety_violations\":[],\"is_safe\":true},\"analyzed_at\":\"2025-07-04T01:02:01.382921Z\"}', 'compliant', '实验结果展示，可以清楚看到实验现象', 0, 0, '0b6f0e24105c536124e7f8faddb588b31d0ea4622df72779ce3636c14dddcb8b', NULL, 6, 6, NULL, '2025-07-03 02:35:11', '2025-07-03 17:02:01', NULL),
(7, 3, 'preparation', 'experiment_photos/3/preparation/sample_preparation.jpg', 'sample_preparation.jpg', '实验preparation照片.jpg', 1955115, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, NULL, 'compliant', '实验前的器材准备情况，所有设备摆放整齐', 0, 0, 'bb0575f05d6279bdb8c59762a8a96eee4522669c76b301ea09d6de79becd09dc', NULL, 5, 6, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(8, 3, 'process', 'experiment_photos/3/process/sample_process.jpg', 'sample_process.jpg', '实验process照片.jpg', 1005793, 'image/jpeg', 1920, 1080, 'mobile', NULL, NULL, 1, NULL, 'compliant', '学生正在进行实验操作的过程记录', 0, 0, 'eea54ff60a43e627d060393a8847572b0fb48932aa075b56f988aa14e20cb6fc', NULL, 5, 6, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(9, 3, 'result', 'experiment_photos/3/result/sample_result.jpg', 'sample_result.jpg', '实验result照片.jpg', 1272022, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, NULL, 'compliant', '实验结果展示，可以清楚看到实验现象', 0, 0, '479369025dfa39e6072ff98da391c8aa682d66267677af9ff55ed66cc821ba72', NULL, 5, 6, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(10, 4, 'preparation', 'experiment_photos/4/preparation/sample_preparation.jpg', 'sample_preparation.jpg', '实验preparation照片.jpg', 1418746, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, NULL, 'pending', '实验前的器材准备情况，所有设备摆放整齐', 0, 0, 'f874d64468a531d7037ed25107812d360df39623d0950293682433c52d50ae43', NULL, 6, 6, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(11, 4, 'process', 'experiment_photos/4/process/sample_process.jpg', 'sample_process.jpg', '实验process照片.jpg', 708021, 'image/jpeg', 1920, 1080, 'mobile', NULL, NULL, 1, NULL, 'pending', '学生正在进行实验操作的过程记录', 0, 0, 'e26d545e125af9e8dab44859deba44d43a4f039fcfc55497e277980511e5a666', NULL, 6, 6, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(12, 4, 'result', 'experiment_photos/4/result/sample_result.jpg', 'sample_result.jpg', '实验result照片.jpg', 687058, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, NULL, 'pending', '实验结果展示，可以清楚看到实验现象', 0, 0, '36860d82bffa7189607d5a94d2e9dd0b0e276579265ed3a1222dc8b7dab8e8fd', NULL, 6, 6, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(13, 5, 'preparation', 'experiment_photos/5/preparation/sample_preparation.jpg', 'sample_preparation.jpg', '实验preparation照片.jpg', 1904369, 'image/jpeg', 1920, 1080, 'web', NULL, NULL, 1, NULL, 'pending', '实验前的器材准备情况，所有设备摆放整齐', 0, 0, '0524350a03f53db0efb7060b669a57cf0c954d07834adf227a543392ae7540ce', NULL, 5, 6, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(14, 5, 'process', 'experiment_photos/5/process/sample_process.jpg', 'sample_process.jpg', '实验process照片.jpg', 1223115, 'image/jpeg', 1920, 1080, 'mobile', NULL, NULL, 1, NULL, 'pending', '学生正在进行实验操作的过程记录', 0, 0, '6ac681100ba322b342f7f870d8cddeadbd50b5640b91f7d6d98185a32658e8d6', NULL, 5, 6, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `experiment_plans`
--

CREATE TABLE `experiment_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL COMMENT '计划名称',
  `code` varchar(50) NOT NULL COMMENT '计划编码',
  `experiment_catalog_id` bigint(20) UNSIGNED NOT NULL COMMENT '实验目录ID',
  `curriculum_standard_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '课程标准ID',
  `teacher_id` bigint(20) UNSIGNED NOT NULL COMMENT '教师ID',
  `class_name` varchar(100) DEFAULT NULL COMMENT '班级名称',
  `student_count` int(11) DEFAULT NULL COMMENT '学生人数',
  `planned_date` date DEFAULT NULL COMMENT '计划执行日期',
  `planned_duration` int(11) DEFAULT NULL COMMENT '计划时长(分钟)',
  `status` enum('draft','pending','approved','rejected','executing','completed','cancelled') NOT NULL DEFAULT 'draft' COMMENT '状态',
  `description` text DEFAULT NULL COMMENT '计划描述',
  `objectives` text DEFAULT NULL COMMENT '教学目标',
  `key_points` text DEFAULT NULL COMMENT '重点难点',
  `safety_requirements` text DEFAULT NULL COMMENT '安全要求',
  `equipment_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '设备需求' CHECK (json_valid(`equipment_requirements`)),
  `material_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '材料需求' CHECK (json_valid(`material_requirements`)),
  `approval_notes` text DEFAULT NULL COMMENT '审批意见',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '审批人ID',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT '审批时间',
  `rejection_reason` text DEFAULT NULL COMMENT '拒绝原因',
  `revision_count` int(11) NOT NULL DEFAULT 0 COMMENT '修改次数',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium' COMMENT '优先级',
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否公开',
  `schedule_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '排课信息' CHECK (json_valid(`schedule_info`)),
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `experiment_plans`
--

INSERT INTO `experiment_plans` (`id`, `name`, `code`, `experiment_catalog_id`, `curriculum_standard_id`, `teacher_id`, `class_name`, `student_count`, `planned_date`, `planned_duration`, `status`, `description`, `objectives`, `key_points`, `safety_requirements`, `equipment_requirements`, `material_requirements`, `approval_notes`, `approved_by`, `approved_at`, `rejection_reason`, `revision_count`, `priority`, `is_public`, `schedule_info`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '观察植物根茎叶实验计划', 'EP20250703001', 1, 1, 6, '三年级一班', 35, '2025-07-10', 45, 'approved', '通过观察植物的根、茎、叶，让学生了解植物的基本结构和功能。', '1. 认识植物的基本结构\\n2. 了解根茎叶的功能\\n3. 培养观察能力', '重点：植物各部分的特征\\n难点：理解各部分的功能', '1. 小心使用放大镜\\n2. 不要随意采摘植物\\n3. 注意保持实验台整洁', '[{\"name\":\"\\u653e\\u5927\\u955c\",\"quantity\":10,\"specification\":\"5\\u500d\\u653e\\u5927\"},{\"name\":\"\\u954a\\u5b50\",\"quantity\":10,\"specification\":\"\\u4e0d\\u9508\\u94a2\\u6750\\u8d28\"},{\"name\":\"\\u57f9\\u517b\\u76bf\",\"quantity\":10,\"specification\":\"\\u76f4\\u5f8410cm\"}]', '[{\"name\":\"\\u65b0\\u9c9c\\u690d\\u7269\\u6837\\u672c\",\"quantity\":10,\"unit\":\"\\u4efd\",\"specification\":\"\\u5305\\u542b\\u6839\\u830e\\u53f6\\u5b8c\\u6574\\u7ed3\\u6784\"},{\"name\":\"\\u84b8\\u998f\\u6c34\",\"quantity\":500,\"unit\":\"ml\",\"specification\":\"\\u5b9e\\u9a8c\\u7528\\u7eaf\\u51c0\\u6c34\"}]', '计划详细，符合教学要求，同意执行。', 6, '2025-07-03 01:59:09', NULL, 0, 'medium', 1, NULL, 5, 6, NULL, NULL, '2025-07-03 01:59:09', '2025-07-03 01:59:09', NULL),
(2, '酸碱指示剂变色实验计划', 'EP20250703002', 2, 2, 6, '五年级二班', 32, '2025-07-15', 40, 'pending', '通过酸碱指示剂的变色反应，让学生了解酸碱性质。', '1. 认识酸碱指示剂\\n2. 观察指示剂变色现象\\n3. 理解酸碱的基本概念', '重点：指示剂的变色规律\\n难点：酸碱概念的理解', '1. 穿戴防护用品\\n2. 小心使用化学试剂\\n3. 避免试剂接触皮肤\\n4. 实验后及时清洗', '[{\"name\":\"\\u8bd5\\u7ba1\",\"quantity\":20,\"specification\":\"15ml\\u5bb9\\u91cf\"},{\"name\":\"\\u8bd5\\u7ba1\\u67b6\",\"quantity\":5,\"specification\":\"\\u6728\\u8d28\\u6216\\u5851\\u6599\"},{\"name\":\"\\u6ef4\\u7ba1\",\"quantity\":10,\"specification\":\"\\u5851\\u6599\\u6750\\u8d28\"}]', '[{\"name\":\"\\u77f3\\u854a\\u8bd5\\u6db2\",\"quantity\":50,\"unit\":\"ml\",\"specification\":\"0.1%\\u6d53\\u5ea6\"},{\"name\":\"\\u7a00\\u76d0\\u9178\",\"quantity\":100,\"unit\":\"ml\",\"specification\":\"0.1mol\\/L\"},{\"name\":\"\\u6c22\\u6c27\\u5316\\u94a0\\u6eb6\\u6db2\",\"quantity\":100,\"unit\":\"ml\",\"specification\":\"0.1mol\\/L\"}]', NULL, NULL, NULL, NULL, 0, 'high', 0, NULL, 6, 6, NULL, NULL, '2025-07-03 01:59:09', '2025-07-03 01:59:09', NULL),
(3, '简单电路制作实验计划', 'EP20250703003', 1, 1, 6, '四年级三班', 28, '2025-07-20', 50, 'draft', '让学生动手制作简单电路，理解电路的基本原理。', '1. 了解电路的基本组成\\n2. 学会制作简单电路\\n3. 培养动手能力', '重点：电路连接方法\\n难点：理解电流路径', '1. 使用低压电源\\n2. 注意导线连接\\n3. 避免短路', '[{\"name\":\"\\u7535\\u6c60\\u76d2\",\"quantity\":10,\"specification\":\"2\\u82825\\u53f7\\u7535\\u6c60\"},{\"name\":\"\\u5c0f\\u706f\\u6ce1\",\"quantity\":20,\"specification\":\"2.5V\"},{\"name\":\"\\u5bfc\\u7ebf\",\"quantity\":30,\"specification\":\"\\u5e26\\u9cc4\\u9c7c\\u5939\"},{\"name\":\"\\u4e2d\\u53e3\",\"quantity\":1,\"specification\":\"7\"}]', '[{\"name\":\"5\\u53f7\\u7535\\u6c60\",\"quantity\":20,\"unit\":\"\\u8282\",\"specification\":\"1.5V\\u5e72\\u7535\\u6c60\"},{\"name\":\"\\u5f00\\u5173\",\"quantity\":10,\"unit\":\"\\u4e2a\",\"specification\":\"\\u6309\\u94ae\\u5f0f\\u5f00\\u5173\"}]', NULL, NULL, NULL, NULL, 0, 'low', 1, NULL, 5, 6, 1, NULL, '2025-07-03 01:59:09', '2025-07-04 05:06:12', NULL),
(4, '水的三态变化观察实验', 'EP20250703004', 1, NULL, 6, '二年级一班', 30, '2025-07-25', 35, 'rejected', '观察水在不同温度下的状态变化。', '1. 认识水的三种状态\\n2. 观察状态变化过程\\n3. 理解温度对物质状态的影响', '重点：三态变化现象\\n难点：理解变化原因', '1. 小心热水烫伤\\n2. 注意用电安全\\n3. 保持实验环境整洁', '[{\"name\":\"\\u70e7\\u676f\",\"quantity\":5,\"specification\":\"250ml\"},{\"name\":\"\\u6e29\\u5ea6\\u8ba1\",\"quantity\":5,\"specification\":\"0-100\\u2103\"},{\"name\":\"\\u7535\\u70ed\\u5668\",\"quantity\":2,\"specification\":\"\\u5b9e\\u9a8c\\u5ba4\\u4e13\\u7528\"}]', '[{\"name\":\"\\u84b8\\u998f\\u6c34\",\"quantity\":1000,\"unit\":\"ml\",\"specification\":\"\\u7eaf\\u51c0\\u6c34\"},{\"name\":\"\\u51b0\\u5757\",\"quantity\":500,\"unit\":\"g\",\"specification\":\"\\u5b9e\\u9a8c\\u7528\\u51b0\"}]', NULL, 6, '2025-07-03 01:59:09', '实验设计存在安全隐患，请修改后重新提交。', 0, 'medium', 0, NULL, 5, 6, NULL, NULL, '2025-07-03 01:59:09', '2025-07-03 01:59:09', NULL),
(6, '观察种子', 'EP20250704001', 1, 1, 3, '二年级3', 22, '2025-07-05', 22, 'approved', '观察根茎叶三年级科学', '观察根茎叶三年级科学', '观察根茎叶三年级科学', '观察根茎叶三年级科学', '[{\"name\":\"\\u89c2\\u5bdf\\u6839\\u830e\\u53f6\\u4e09\\u5e74\\u7ea7\\u79d1\\u5b66\",\"quantity\":1,\"specification\":\"\\u89c2\\u5bdf\\u6839\\u830e\\u53f6\\u4e09\\u5e74\\u7ea7\\u79d1\\u5b66\"},{\"name\":\"\\u8981\",\"quantity\":1,\"specification\":\"\\u8981\"}]', '[{\"name\":\"\\u89c2\\u5bdf\\u6839\\u830e\\u53f6\\u4e09\\u5e74\\u7ea7\\u79d1\\u5b661\",\"quantity\":1,\"unit\":\"ml\",\"specification\":\"ddd\"}]', NULL, 3, '2025-07-04 05:43:08', NULL, 1, 'medium', 1, NULL, 5, 3, NULL, NULL, '2025-07-04 05:41:37', '2025-07-04 05:43:08', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `experiment_records`
--

CREATE TABLE `experiment_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `experiment_plan_id` bigint(20) UNSIGNED NOT NULL COMMENT '实验计划ID',
  `execution_date` date NOT NULL COMMENT '执行日期',
  `start_time` time DEFAULT NULL COMMENT '开始时间',
  `end_time` time DEFAULT NULL COMMENT '结束时间',
  `actual_duration` int(11) DEFAULT NULL COMMENT '实际时长(分钟)',
  `actual_student_count` int(11) DEFAULT NULL COMMENT '实际参与学生数',
  `completion_status` enum('not_started','in_progress','partial','completed','cancelled') NOT NULL DEFAULT 'not_started' COMMENT '完成状态',
  `execution_notes` text DEFAULT NULL COMMENT '执行说明',
  `problems_encountered` text DEFAULT NULL COMMENT '遇到的问题',
  `solutions_applied` text DEFAULT NULL COMMENT '解决方案',
  `teaching_reflection` text DEFAULT NULL COMMENT '教学反思',
  `student_feedback` text DEFAULT NULL COMMENT '学生反馈',
  `equipment_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '使用的设备' CHECK (json_valid(`equipment_used`)),
  `materials_consumed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '消耗的材料' CHECK (json_valid(`materials_consumed`)),
  `data_records` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '实验数据记录' CHECK (json_valid(`data_records`)),
  `safety_incidents` text DEFAULT NULL COMMENT '安全事件记录',
  `status` enum('draft','submitted','under_review','approved','rejected','revision_required') NOT NULL DEFAULT 'draft' COMMENT '审核状态',
  `review_notes` text DEFAULT NULL COMMENT '审核意见',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '审核人ID',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT '审核时间',
  `photo_count` int(11) NOT NULL DEFAULT 0 COMMENT '照片数量',
  `equipment_confirmed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '器材准备已确认',
  `equipment_confirmed_at` timestamp NULL DEFAULT NULL COMMENT '器材确认时间',
  `validation_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '验证结果' CHECK (json_valid(`validation_results`)),
  `completion_percentage` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '完成百分比',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `experiment_records`
--

INSERT INTO `experiment_records` (`id`, `experiment_plan_id`, `execution_date`, `start_time`, `end_time`, `actual_duration`, `actual_student_count`, `completion_status`, `execution_notes`, `problems_encountered`, `solutions_applied`, `teaching_reflection`, `student_feedback`, `equipment_used`, `materials_consumed`, `data_records`, `safety_incidents`, `status`, `review_notes`, `reviewed_by`, `reviewed_at`, `photo_count`, `equipment_confirmed`, `equipment_confirmed_at`, `validation_results`, `completion_percentage`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '2025-07-08', '09:00:00', '09:45:00', 45, 34, 'completed', '实验顺利进行，学生积极参与观察植物根茎叶的结构。通过放大镜观察，学生能够清楚地看到植物各部分的特征。', '部分学生在使用放大镜时需要指导，有2个放大镜出现轻微划痕。', '安排学习小组互助，更换了有划痕的放大镜。', '学生对植物结构的理解比预期要好，下次可以增加更多的观察内容。实验时间安排合理。', '学生反映实验很有趣，希望能观察更多不同种类的植物。', '[{\"name\":\"\\u653e\\u5927\\u955c\",\"quantity\":10,\"condition\":\"\\u826f\\u597d\",\"notes\":\"2\\u4e2a\\u6709\\u8f7b\\u5fae\\u5212\\u75d5\\u5df2\\u66f4\\u6362\"},{\"name\":\"\\u954a\\u5b50\",\"quantity\":10,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u4f7f\\u7528\\u6b63\\u5e38\"},{\"name\":\"\\u57f9\\u517b\\u76bf\",\"quantity\":10,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u6e05\\u6d01\\u5b8c\\u597d\"}]', '[{\"name\":\"\\u65b0\\u9c9c\\u690d\\u7269\\u6837\\u672c\",\"quantity\":10,\"unit\":\"\\u4efd\",\"notes\":\"\\u5305\\u542b\\u6839\\u830e\\u53f6\\u5b8c\\u6574\\u7ed3\\u6784\"},{\"name\":\"\\u84b8\\u998f\\u6c34\",\"quantity\":200,\"unit\":\"ml\",\"notes\":\"\\u7528\\u4e8e\\u6e05\\u6d01\\u548c\\u4fdd\\u6e7f\"}]', '[{\"parameter\":\"\\u6839\\u957f\\u5ea6\",\"value\":\"8-12\",\"unit\":\"cm\",\"notes\":\"\\u5e73\\u5747\\u503c\"},{\"parameter\":\"\\u53f6\\u7247\\u6570\\u91cf\",\"value\":\"6-8\",\"unit\":\"\\u7247\",\"notes\":\"\\u6bcf\\u682a\\u5e73\\u5747\"},{\"parameter\":\"\\u830e\\u76f4\\u5f84\",\"value\":\"2-3\",\"unit\":\"mm\",\"notes\":\"\\u6d4b\\u91cf\\u503c\"}]', NULL, 'approved', '记录详细完整，实验执行效果良好，同意通过。', 6, '2025-07-03 02:35:10', 3, 1, '2025-07-02 02:35:10', NULL, 100.00, 5, 6, NULL, NULL, '2025-07-03 02:35:10', '2025-07-03 02:35:10', NULL),
(2, 2, '2025-07-12', '14:00:00', '14:35:00', 35, 30, 'partial', '酸碱指示剂实验进行了一半，由于时间不够，部分实验内容延后进行。', '实验准备时间过长，导致实际实验时间不足。部分试剂浓度需要调整。', '下次课继续完成剩余实验内容，提前准备试剂。', '需要更好地控制实验节奏，提前做好充分准备。', '学生对变色现象很感兴趣，希望能看到更多的变色实验。', '[{\"name\":\"\\u8bd5\\u7ba1\",\"quantity\":15,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u4f7f\\u7528\\u6b63\\u5e38\"},{\"name\":\"\\u8bd5\\u7ba1\\u67b6\",\"quantity\":5,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u7a33\\u5b9a\\u6027\\u597d\"},{\"name\":\"\\u6ef4\\u7ba1\",\"quantity\":8,\"condition\":\"\\u826f\\u597d\",\"notes\":\"2\\u4e2a\\u9700\\u8981\\u6e05\\u6d01\"}]', '[{\"name\":\"\\u77f3\\u854a\\u8bd5\\u6db2\",\"quantity\":30,\"unit\":\"ml\",\"notes\":\"0.1%\\u6d53\\u5ea6\"},{\"name\":\"\\u7a00\\u76d0\\u9178\",\"quantity\":50,\"unit\":\"ml\",\"notes\":\"0.1mol\\/L\"},{\"name\":\"\\u6c22\\u6c27\\u5316\\u94a0\\u6eb6\\u6db2\",\"quantity\":50,\"unit\":\"ml\",\"notes\":\"0.1mol\\/L\"}]', '[{\"parameter\":\"\\u9178\\u6027\\u6eb6\\u6db2\\u989c\\u8272\",\"value\":\"\\u7ea2\\u8272\",\"unit\":\"\",\"notes\":\"\\u77f3\\u854a\\u8bd5\\u6db2\\u53d8\\u8272\"},{\"parameter\":\"\\u78b1\\u6027\\u6eb6\\u6db2\\u989c\\u8272\",\"value\":\"\\u84dd\\u8272\",\"unit\":\"\",\"notes\":\"\\u77f3\\u854a\\u8bd5\\u6db2\\u53d8\\u8272\"},{\"parameter\":\"\\u53d8\\u8272\\u65f6\\u95f4\",\"value\":\"2-3\",\"unit\":\"\\u79d2\",\"notes\":\"\\u53cd\\u5e94\\u901f\\u5ea6\"}]', NULL, 'submitted', NULL, NULL, NULL, 3, 1, '2025-07-03 00:35:10', NULL, 100.00, 6, 6, NULL, NULL, '2025-07-03 02:35:10', '2025-07-03 02:35:11', NULL),
(3, 1, '2025-07-08', '09:00:00', '09:45:00', 45, 34, 'completed', '实验顺利进行，学生积极参与观察植物根茎叶的结构。通过放大镜观察，学生能够清楚地看到植物各部分的特征。', '部分学生在使用放大镜时需要指导，有2个放大镜出现轻微划痕。', '安排学习小组互助，更换了有划痕的放大镜。', '学生对植物结构的理解比预期要好，下次可以增加更多的观察内容。实验时间安排合理。', '学生反映实验很有趣，希望能观察更多不同种类的植物。', '[{\"name\":\"\\u653e\\u5927\\u955c\",\"quantity\":10,\"condition\":\"\\u826f\\u597d\",\"notes\":\"2\\u4e2a\\u6709\\u8f7b\\u5fae\\u5212\\u75d5\\u5df2\\u66f4\\u6362\"},{\"name\":\"\\u954a\\u5b50\",\"quantity\":10,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u4f7f\\u7528\\u6b63\\u5e38\"},{\"name\":\"\\u57f9\\u517b\\u76bf\",\"quantity\":10,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u6e05\\u6d01\\u5b8c\\u597d\"}]', '[{\"name\":\"\\u65b0\\u9c9c\\u690d\\u7269\\u6837\\u672c\",\"quantity\":10,\"unit\":\"\\u4efd\",\"notes\":\"\\u5305\\u542b\\u6839\\u830e\\u53f6\\u5b8c\\u6574\\u7ed3\\u6784\"},{\"name\":\"\\u84b8\\u998f\\u6c34\",\"quantity\":200,\"unit\":\"ml\",\"notes\":\"\\u7528\\u4e8e\\u6e05\\u6d01\\u548c\\u4fdd\\u6e7f\"}]', '[{\"parameter\":\"\\u6839\\u957f\\u5ea6\",\"value\":\"8-12\",\"unit\":\"cm\",\"notes\":\"\\u5e73\\u5747\\u503c\"},{\"parameter\":\"\\u53f6\\u7247\\u6570\\u91cf\",\"value\":\"6-8\",\"unit\":\"\\u7247\",\"notes\":\"\\u6bcf\\u682a\\u5e73\\u5747\"},{\"parameter\":\"\\u830e\\u76f4\\u5f84\",\"value\":\"2-3\",\"unit\":\"mm\",\"notes\":\"\\u6d4b\\u91cf\\u503c\"}]', NULL, 'approved', '记录详细完整，实验执行效果良好，同意通过。', 6, '2025-07-03 02:36:02', 3, 1, '2025-07-02 02:36:02', NULL, 100.00, 5, 6, NULL, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(4, 2, '2025-07-12', '14:00:00', '14:35:00', 35, 30, 'partial', '酸碱指示剂实验进行了一半，由于时间不够，部分实验内容延后进行。', '实验准备时间过长，导致实际实验时间不足。部分试剂浓度需要调整。', '下次课继续完成剩余实验内容，提前准备试剂。', '需要更好地控制实验节奏，提前做好充分准备。', '学生对变色现象很感兴趣，希望能看到更多的变色实验。', '[{\"name\":\"\\u8bd5\\u7ba1\",\"quantity\":15,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u4f7f\\u7528\\u6b63\\u5e38\"},{\"name\":\"\\u8bd5\\u7ba1\\u67b6\",\"quantity\":5,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u7a33\\u5b9a\\u6027\\u597d\"},{\"name\":\"\\u6ef4\\u7ba1\",\"quantity\":8,\"condition\":\"\\u826f\\u597d\",\"notes\":\"2\\u4e2a\\u9700\\u8981\\u6e05\\u6d01\"}]', '[{\"name\":\"\\u77f3\\u854a\\u8bd5\\u6db2\",\"quantity\":30,\"unit\":\"ml\",\"notes\":\"0.1%\\u6d53\\u5ea6\"},{\"name\":\"\\u7a00\\u76d0\\u9178\",\"quantity\":50,\"unit\":\"ml\",\"notes\":\"0.1mol\\/L\"},{\"name\":\"\\u6c22\\u6c27\\u5316\\u94a0\\u6eb6\\u6db2\",\"quantity\":50,\"unit\":\"ml\",\"notes\":\"0.1mol\\/L\"}]', '[{\"parameter\":\"\\u9178\\u6027\\u6eb6\\u6db2\\u989c\\u8272\",\"value\":\"\\u7ea2\\u8272\",\"unit\":\"\",\"notes\":\"\\u77f3\\u854a\\u8bd5\\u6db2\\u53d8\\u8272\"},{\"parameter\":\"\\u78b1\\u6027\\u6eb6\\u6db2\\u989c\\u8272\",\"value\":\"\\u84dd\\u8272\",\"unit\":\"\",\"notes\":\"\\u77f3\\u854a\\u8bd5\\u6db2\\u53d8\\u8272\"},{\"parameter\":\"\\u53d8\\u8272\\u65f6\\u95f4\",\"value\":\"2-3\",\"unit\":\"\\u79d2\",\"notes\":\"\\u53cd\\u5e94\\u901f\\u5ea6\"}]', NULL, 'submitted', NULL, NULL, NULL, 3, 1, '2025-07-03 00:36:02', NULL, 100.00, 6, 6, NULL, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:02', NULL),
(5, 3, '2025-07-15', '10:30:00', NULL, NULL, 28, 'in_progress', '简单电路制作实验正在进行中，学生正在组装电路。', '部分电池电量不足，需要更换。', '已更换新电池，继续实验。', '', '', '[{\"name\":\"\\u7535\\u6c60\\u76d2\",\"quantity\":8,\"condition\":\"\\u826f\\u597d\",\"notes\":\"2\\u4e2a\\u7535\\u6c60\\u76d2\\u7535\\u6c60\\u9700\\u66f4\\u6362\"},{\"name\":\"\\u5c0f\\u706f\\u6ce1\",\"quantity\":16,\"condition\":\"\\u826f\\u597d\",\"notes\":\"4\\u4e2a\\u5907\\u7528\"},{\"name\":\"\\u5bfc\\u7ebf\",\"quantity\":24,\"condition\":\"\\u826f\\u597d\",\"notes\":\"\\u9cc4\\u9c7c\\u5939\\u5b8c\\u597d\"}]', '[{\"name\":\"5\\u53f7\\u7535\\u6c60\",\"quantity\":16,\"unit\":\"\\u8282\",\"notes\":\"1.5V\\u5e72\\u7535\\u6c60\"},{\"name\":\"\\u5f00\\u5173\",\"quantity\":8,\"unit\":\"\\u4e2a\",\"notes\":\"\\u6309\\u94ae\\u5f0f\\u5f00\\u5173\"}]', '[{\"parameter\":\"\\u7535\\u8def\\u901a\\u8def\\u6570\",\"value\":\"6\",\"unit\":\"\\u4e2a\",\"notes\":\"\\u6210\\u529f\\u70b9\\u4eae\\u706f\\u6ce1\"},{\"parameter\":\"\\u7535\\u6c60\\u7535\\u538b\",\"value\":\"3.0\",\"unit\":\"V\",\"notes\":\"\\u4e24\\u8282\\u7535\\u6c60\\u4e32\\u8054\"}]', NULL, 'draft', NULL, NULL, NULL, 2, 0, NULL, NULL, 50.00, 5, 6, NULL, NULL, '2025-07-03 02:36:02', '2025-07-03 02:36:03', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `experiment_review_logs`
--

CREATE TABLE `experiment_review_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `experiment_record_id` bigint(20) UNSIGNED NOT NULL COMMENT '实验记录ID',
  `review_type` enum('submit','approve','reject','revision_request','force_complete','batch_approve','batch_reject','ai_check','manual_check') NOT NULL COMMENT '审核类型',
  `previous_status` varchar(50) DEFAULT NULL COMMENT '之前状态',
  `new_status` varchar(50) NOT NULL COMMENT '新状态',
  `review_notes` text DEFAULT NULL COMMENT '审核意见',
  `review_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '审核详情' CHECK (json_valid(`review_details`)),
  `attachment_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '附件文件' CHECK (json_valid(`attachment_files`)),
  `review_category` enum('format','content','photo','data','safety','completeness','other') DEFAULT NULL COMMENT '审核分类',
  `review_score` int(11) DEFAULT NULL COMMENT '审核评分(1-10)',
  `is_ai_review` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否AI审核',
  `ai_analysis_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'AI分析结果' CHECK (json_valid(`ai_analysis_result`)),
  `reviewer_id` bigint(20) UNSIGNED NOT NULL COMMENT '审核人ID',
  `reviewer_role` varchar(50) NOT NULL COMMENT '审核人角色',
  `reviewer_name` varchar(100) NOT NULL COMMENT '审核人姓名',
  `review_deadline` timestamp NULL DEFAULT NULL COMMENT '审核截止时间',
  `review_duration` int(11) DEFAULT NULL COMMENT '审核耗时(分钟)',
  `is_urgent` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否紧急',
  `ip_address` varchar(45) DEFAULT NULL COMMENT '操作IP地址',
  `user_agent` varchar(500) DEFAULT NULL COMMENT '用户代理',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `experiment_review_logs`
--

INSERT INTO `experiment_review_logs` (`id`, `experiment_record_id`, `review_type`, `previous_status`, `new_status`, `review_notes`, `review_details`, `attachment_files`, `review_category`, `review_score`, `is_ai_review`, `ai_analysis_result`, `reviewer_id`, `reviewer_role`, `reviewer_name`, `review_deadline`, `review_duration`, `is_urgent`, `ip_address`, `user_agent`, `organization_id`, `extra_data`, `created_at`, `updated_at`) VALUES
(1, 1, 'submit', 'draft', 'submitted', '实验记录已完成，提交审核。', NULL, NULL, NULL, NULL, 0, NULL, 6, 'teacher', '张老师', NULL, 5, 0, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 5, NULL, '2025-07-01 04:46:01', '2025-07-01 04:46:01'),
(2, 1, 'ai_check', 'submitted', 'submitted', 'AI自动检查完成，共检查 3 张照片', NULL, NULL, NULL, NULL, 1, '{\"total_photos\":3,\"compliant_photos\":3,\"non_compliant_photos\":0,\"compliance_rate\":100,\"check_results\":[{\"photo_id\":1,\"photo_type\":\"preparation\",\"compliance_status\":\"compliant\"},{\"photo_id\":2,\"photo_type\":\"process\",\"compliance_status\":\"compliant\"},{\"photo_id\":3,\"photo_type\":\"result\",\"compliance_status\":\"compliant\"}]}', 1, 'admin', 'AI系统', NULL, 1, 0, '127.0.0.1', 'AI-System/1.0', 5, NULL, '2025-07-02 02:46:01', '2025-07-02 02:46:01'),
(3, 1, 'approve', 'submitted', 'approved', '记录详细完整，实验执行效果良好，同意通过。', NULL, NULL, 'completeness', 9, 0, NULL, 1, 'admin', '系统管理员', NULL, 15, 0, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 5, NULL, '2025-07-02 04:46:01', '2025-07-02 04:46:01'),
(4, 2, 'submit', 'draft', 'submitted', '实验记录提交审核。', NULL, NULL, NULL, NULL, 0, NULL, 6, 'teacher', '李老师', NULL, 3, 0, '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 6, NULL, '2025-07-02 22:46:01', '2025-07-02 22:46:01'),
(5, 2, 'revision_request', 'submitted', 'revision_required', '实验记录需要补充完善，请按要求修改后重新提交。', '{\"revision_requirements\":[\"\\u8865\\u5145\\u6559\\u5b66\\u53cd\\u601d\",\"\\u4e0a\\u4f20\\u66f4\\u591a\\u7167\\u7247\",\"\\u5b8c\\u5584\\u5b9e\\u9a8c\\u6570\\u636e\"],\"revision_deadline\":\"2025-07-06T12:46:01.138486Z\"}', NULL, 'content', NULL, 0, NULL, 3, 'admin', '刘校长', '2025-07-06 04:46:01', 20, 0, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 6, NULL, '2025-07-03 02:46:01', '2025-07-03 02:46:01'),
(6, 3, 'submit', 'draft', 'submitted', '实验记录提交审核。', NULL, NULL, NULL, NULL, 0, NULL, 6, 'teacher', '王老师', NULL, 2, 0, '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 5, NULL, '2025-07-03 04:16:01', '2025-07-03 04:16:01'),
(7, 1, 'batch_approve', 'submitted', 'approved', '批量审核通过，记录质量良好。', '{\"batch_operation\":true,\"batch_size\":2}', NULL, 'completeness', NULL, 0, NULL, 1, 'admin', '系统管理员', NULL, 8, 0, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 5, NULL, '2025-06-30 04:46:01', '2025-06-30 04:46:01'),
(8, 2, 'batch_approve', 'submitted', 'approved', '批量审核通过，记录质量良好。', '{\"batch_operation\":true,\"batch_size\":2}', NULL, 'completeness', NULL, 0, NULL, 1, 'admin', '系统管理员', NULL, 8, 0, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 6, NULL, '2025-06-30 04:46:01', '2025-06-30 04:46:01'),
(9, 2, 'ai_check', 'submitted', 'submitted', 'AI自动检查完成，共检查 3 张照片', NULL, NULL, NULL, NULL, 1, '{\"total_photos\":3,\"compliant_photos\":3,\"non_compliant_photos\":0,\"compliance_rate\":100,\"check_results\":[{\"photo_id\":4,\"photo_type\":\"preparation\",\"compliance_status\":\"compliant\",\"analysis_result\":{\"blur_detection\":{\"is_blurry\":false,\"blur_score\":0.1},\"content_detection\":{\"has_experiment_content\":true,\"confidence\":0.85},\"safety_check\":{\"safety_violations\":[],\"is_safe\":true},\"analyzed_at\":\"2025-07-04T01:02:01.378683Z\"}},{\"photo_id\":5,\"photo_type\":\"process\",\"compliance_status\":\"compliant\",\"analysis_result\":{\"blur_detection\":{\"is_blurry\":false,\"blur_score\":0.1},\"content_detection\":{\"has_experiment_content\":true,\"confidence\":0.85},\"safety_check\":{\"safety_violations\":[],\"is_safe\":true},\"analyzed_at\":\"2025-07-04T01:02:01.381811Z\"}},{\"photo_id\":6,\"photo_type\":\"result\",\"compliance_status\":\"compliant\",\"analysis_result\":{\"blur_detection\":{\"is_blurry\":false,\"blur_score\":0.1},\"content_detection\":{\"has_experiment_content\":true,\"confidence\":0.85},\"safety_check\":{\"safety_violations\":[],\"is_safe\":true},\"analyzed_at\":\"2025-07-04T01:02:01.382921Z\"}}]}', 1, 'admin', '系统管理员', NULL, NULL, 0, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', 6, NULL, '2025-07-03 17:02:01', '2025-07-03 17:02:01');

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
-- 表的结构 `materials`
--

CREATE TABLE `materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL COMMENT '材料名称',
  `code` varchar(100) NOT NULL COMMENT '材料编码',
  `specification` varchar(200) DEFAULT NULL COMMENT '规格型号',
  `brand` varchar(100) DEFAULT NULL COMMENT '品牌',
  `description` text DEFAULT NULL COMMENT '材料描述',
  `category_id` bigint(20) UNSIGNED NOT NULL COMMENT '材料分类ID',
  `unit` varchar(20) NOT NULL COMMENT '计量单位',
  `unit_price` decimal(8,2) DEFAULT NULL COMMENT '单价',
  `current_stock` int(11) NOT NULL DEFAULT 0 COMMENT '当前库存',
  `min_stock` int(11) NOT NULL DEFAULT 0 COMMENT '最低库存',
  `max_stock` int(11) DEFAULT NULL COMMENT '最高库存',
  `total_purchased` int(11) NOT NULL DEFAULT 0 COMMENT '累计采购数量',
  `total_consumed` int(11) NOT NULL DEFAULT 0 COMMENT '累计消耗数量',
  `storage_location` varchar(200) DEFAULT NULL COMMENT '存储位置',
  `storage_conditions` text DEFAULT NULL COMMENT '存储条件',
  `expiry_date` date DEFAULT NULL COMMENT '有效期至',
  `safety_notes` text DEFAULT NULL COMMENT '安全注意事项',
  `status` enum('active','inactive','expired','out_of_stock') NOT NULL DEFAULT 'active' COMMENT '状态',
  `supplier` varchar(200) DEFAULT NULL COMMENT '供应商',
  `last_purchase_date` date DEFAULT NULL COMMENT '最后采购日期',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `materials`
--

INSERT INTO `materials` (`id`, `name`, `code`, `specification`, `brand`, `description`, `category_id`, `unit`, `unit_price`, `current_stock`, `min_stock`, `max_stock`, `total_purchased`, `total_consumed`, `storage_location`, `storage_conditions`, `expiry_date`, `safety_notes`, `status`, `supplier`, `last_purchase_date`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '氢氧化钠', 'MAT_NAOH_001', 'AR级，500g/瓶', '国药集团', '分析纯氢氧化钠', 1, 'g', 0.05, 1950, 500, 5000, 2000, 0, '化学试剂柜A-1', '密封保存，防潮', '2025-12-31', '强碱性，使用时佩戴防护用品', 'active', '化学试剂公司', '2025-04-24', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 18:50:20', NULL),
(2, '盐酸', 'MAT_HCL_001', 'AR级，37%，500ml/瓶', '国药集团', '分析纯盐酸', 1, 'ml', 0.02, 2980, 1000, 8000, 3000, 0, '化学试剂柜A-2', '通风保存，远离碱性物质', '2025-06-30', '强酸性，腐蚀性强，小心使用', 'active', '化学试剂公司', '2025-04-19', 5, 1, NULL, NULL, '2025-07-02 06:01:33', '2025-07-02 18:50:20', NULL),
(3, '洋葱表皮', 'MAT_ONION_001', '新鲜洋葱表皮', '生物材料供应商', '用于细胞观察实验', 2, '片', 1.00, 90, 20, 200, 100, 0, '生物实验室冷藏柜', '4°C冷藏保存', '2024-08-15', '使用前检查新鲜度', 'active', '生物材料公司', '2025-05-24', 5, 1, NULL, NULL, '2025-07-02 06:01:33', '2025-07-02 18:50:21', NULL),
(4, '滤纸', 'MAT_FILTER_001', '定性滤纸，直径9cm', '实验用品厂', '实验用定性滤纸', 3, '张', 0.10, 495, 100, 1000, 500, 0, '实验用品柜B-1', '干燥保存', NULL, '无特殊安全要求', 'active', '实验用品公司', '2025-05-09', 5, 1, NULL, NULL, '2025-07-02 06:01:33', '2025-07-02 18:50:21', NULL),
(5, '玻璃棒', 'MAT_GLASS_ROD_001', '长度20cm，直径5mm', '玻璃器皿厂', '实验用玻璃搅拌棒', 4, '根', 2.00, 50, 10, 100, 50, 0, '玻璃器皿柜C-1', '小心轻放，防止破损', NULL, '使用时小心玻璃碎片', 'active', '玻璃器皿公司', '2025-04-22', 5, 1, NULL, NULL, '2025-07-02 06:01:33', '2025-07-02 06:01:33', NULL),
(6, '硫酸', 'MAT_H2SO4_001', 'AR级，98%，500ml/瓶', '国药集团', '分析纯浓硫酸', 1, 'ml', 0.08, 1500, 300, 3000, 1500, 0, '化学试剂柜A-3', '密封保存，防潮，通风', '2025-08-31', '强酸性，腐蚀性极强，使用时必须佩戴防护用品', 'active', '化学试剂公司', '2025-04-14', 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(7, '碳酸钠', 'MAT_NA2CO3_001', 'AR级，500g/瓶', '国药集团', '分析纯碳酸钠', 1, 'g', 0.03, 800, 200, 2000, 800, 0, '化学试剂柜B-1', '干燥保存，防潮', '2026-03-31', '避免吸入粉尘', 'active', '化学试剂公司', '2025-05-22', 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(8, '酚酞指示剂', 'MAT_PHENOL_001', '1%乙醇溶液，100ml/瓶', '天津化学', '酸碱指示剂', 1, 'ml', 0.15, 500, 100, 1000, 500, 0, '化学试剂柜C-2', '避光保存，室温', '2025-12-31', '避免接触皮肤和眼睛', 'active', '化学试剂公司', '2025-06-02', 5, 1, NULL, NULL, '2025-07-02 18:50:19', '2025-07-02 18:50:19', NULL),
(9, '琼脂粉', 'MAT_AGAR_001', '微生物级，250g/瓶', 'Oxoid', '微生物培养基原料', 2, 'g', 0.80, 200, 50, 500, 200, 0, '生物实验室冷藏柜', '4°C冷藏保存', '2025-09-30', '使用前检查是否变质', 'active', '生物材料公司', '2025-05-08', 5, 1, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL),
(10, '酵母菌', 'MAT_YEAST_001', '活性干酵母，10g/包', '安琪', '实验用酵母菌', 2, 'g', 2.00, 80, 20, 200, 80, 0, '生物实验室冷藏柜', '4°C冷藏保存', '2024-12-31', '使用前检查活性', 'active', '生物材料公司', '2025-05-28', 5, 1, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL),
(11, '试管', 'MAT_TEST_TUBE_001', '硼硅玻璃，15ml，φ16×150mm', '舒博', '实验用试管', 3, '支', 1.50, 200, 50, 500, 200, 0, '玻璃器皿柜A-1', '小心轻放，防止破损', NULL, '使用时小心玻璃碎片', 'active', '实验用品公司', '2025-04-05', 5, 1, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL),
(12, '橡胶手套', 'MAT_GLOVES_001', '一次性乳胶手套，M号', '蓝帆', '实验防护手套', 3, '双', 0.50, 500, 100, 1000, 500, 0, '防护用品柜', '干燥保存，避免阳光直射', '2026-06-30', '使用前检查是否破损', 'active', '防护用品公司', '2025-05-04', 5, 1, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL),
(13, '酒精灯', 'MAT_ALCOHOL_LAMP_001', '玻璃材质，150ml容量', '教学仪器厂', '实验加热用酒精灯', 4, '个', 8.00, 30, 10, 50, 30, 0, '实验器材柜B-2', '小心轻放，防止破损', NULL, '使用时注意防火安全', 'active', '教学仪器公司', '2025-04-15', 5, 1, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `material_categories`
--

CREATE TABLE `material_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '分类名称',
  `code` varchar(50) NOT NULL COMMENT '分类编码',
  `description` text DEFAULT NULL COMMENT '分类描述',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '父级分类ID',
  `subject` varchar(50) DEFAULT NULL COMMENT '适用学科',
  `grade_range` varchar(100) DEFAULT NULL COMMENT '适用年级范围',
  `material_type` enum('consumable','reusable','chemical','biological') NOT NULL COMMENT '材料类型',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT '状态',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `material_categories`
--

INSERT INTO `material_categories` (`id`, `name`, `code`, `description`, `parent_id`, `subject`, `grade_range`, `material_type`, `sort_order`, `status`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '化学试剂', 'CHEM_REAGENT', '各类化学试剂', NULL, 'chemistry', 'grade7-12', 'chemical', 1, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(2, '生物材料', 'BIO_MATERIAL', '生物实验材料', NULL, 'biology', 'grade7-12', 'biological', 2, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(3, '消耗品', 'CONSUMABLE', '实验消耗品', NULL, 'general', 'all', 'consumable', 3, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL),
(4, '可重复使用材料', 'REUSABLE', '可重复使用的实验材料', NULL, 'general', 'all', 'reusable', 4, 'active', 5, 1, NULL, NULL, '2025-07-02 06:01:32', '2025-07-02 06:01:32', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `material_stock_logs`
--

CREATE TABLE `material_stock_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_code` varchar(50) NOT NULL COMMENT '日志编号',
  `material_id` bigint(20) UNSIGNED NOT NULL COMMENT '材料ID',
  `operation_type` enum('purchase','usage','adjustment','expired','damaged') NOT NULL COMMENT '操作类型',
  `quantity_before` int(11) NOT NULL COMMENT '操作前数量',
  `quantity_change` int(11) NOT NULL COMMENT '变更数量(正数为增加，负数为减少)',
  `quantity_after` int(11) NOT NULL COMMENT '操作后数量',
  `unit_price` decimal(8,2) DEFAULT NULL COMMENT '单价',
  `total_amount` decimal(10,2) DEFAULT NULL COMMENT '总金额',
  `reason` text NOT NULL COMMENT '操作原因',
  `reference_type` varchar(50) DEFAULT NULL COMMENT '关联类型',
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '关联ID',
  `notes` text DEFAULT NULL COMMENT '备注',
  `operator_id` bigint(20) UNSIGNED NOT NULL COMMENT '操作人ID',
  `operated_at` datetime NOT NULL COMMENT '操作时间',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `material_stock_logs`
--

INSERT INTO `material_stock_logs` (`id`, `log_code`, `material_id`, `operation_type`, `quantity_before`, `quantity_change`, `quantity_after`, `unit_price`, `total_amount`, `reason`, `reference_type`, `reference_id`, `notes`, `operator_id`, `operated_at`, `organization_id`, `extra_data`, `created_at`, `updated_at`) VALUES
(1, 'LOG_20250703025020_1', 1, 'usage', 2000, -50, 1950, NULL, NULL, '酸碱中和滴定实验', 'material_usage', 1, NULL, 6, '2025-07-03 02:50:20', 5, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20'),
(2, 'LOG_20250703025020_2', 2, 'usage', 3000, -20, 2980, NULL, NULL, '金属活动性实验', 'material_usage', 2, NULL, 6, '2025-07-03 02:50:20', 5, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20'),
(3, 'LOG_20250703025021_3', 3, 'usage', 100, -10, 90, NULL, NULL, '酸碱指示剂实验', 'material_usage', 3, NULL, 6, '2025-07-03 02:50:21', 5, NULL, '2025-07-02 18:50:21', '2025-07-02 18:50:21'),
(4, 'LOG_20250703025021_4', 4, 'usage', 500, -5, 495, NULL, NULL, '微生物培养基制备', 'material_usage', 4, NULL, 1, '2025-07-03 02:50:21', 5, NULL, '2025-07-02 18:50:21', '2025-07-02 18:50:21');

-- --------------------------------------------------------

--
-- 表的结构 `material_usages`
--

CREATE TABLE `material_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usage_code` varchar(50) NOT NULL COMMENT '使用编号',
  `material_id` bigint(20) UNSIGNED NOT NULL COMMENT '材料ID',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '使用人ID',
  `experiment_catalog_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '关联实验目录ID',
  `quantity_used` int(11) NOT NULL COMMENT '使用数量',
  `purpose` text NOT NULL COMMENT '使用目的',
  `used_at` datetime NOT NULL COMMENT '使用时间',
  `notes` text DEFAULT NULL COMMENT '使用备注',
  `usage_type` enum('experiment','maintenance','teaching','other') NOT NULL DEFAULT 'experiment' COMMENT '使用类型',
  `class_name` varchar(100) DEFAULT NULL COMMENT '班级名称',
  `student_count` int(11) DEFAULT NULL COMMENT '学生人数',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `material_usages`
--

INSERT INTO `material_usages` (`id`, `usage_code`, `material_id`, `user_id`, `experiment_catalog_id`, `quantity_used`, `purpose`, `used_at`, `notes`, `usage_type`, `class_name`, `student_count`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'USE_20250703025020_1', 1, 6, 1, 50, '酸碱中和滴定实验', '2025-07-02 02:50:20', '学生分组实验，每组使用约1.5ml', 'experiment', '九年级(1)班', 35, 5, 6, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL),
(2, 'USE_20250703025020_2', 2, 6, 1, 20, '金属活动性实验', '2025-06-30 02:50:20', '演示实验用量', 'experiment', '九年级(2)班', 32, 5, 6, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL),
(3, 'USE_20250703025020_3', 3, 6, 1, 10, '酸碱指示剂实验', '2025-06-28 02:50:20', '课堂演示实验', 'teaching', '八年级(3)班', 30, 5, 6, NULL, NULL, '2025-07-02 18:50:20', '2025-07-02 18:50:20', NULL),
(4, 'USE_20250703025021_4', 4, 1, 1, 5, '微生物培养基制备', '2025-06-26 02:50:20', '生物实验室培养基准备', 'experiment', NULL, NULL, 5, 1, NULL, NULL, '2025-07-02 18:50:21', '2025-07-02 18:50:21', NULL);

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
(40, '2025_06_28_020000_add_source_to_user_permissions_table', 13),
(41, '2025_06_28_030000_create_organization_permissions_table', 14),
(42, '2025_07_02_100001_create_experiment_catalogs_table', 14),
(43, '2025_07_02_100002_create_curriculum_standards_table', 14),
(44, '2025_07_02_100003_create_photo_templates_table', 14),
(45, '2025_07_02_100004_create_catalog_versions_table', 14),
(46, '2025_07_02_100005_create_catalog_import_logs_table', 14),
(47, '2025_07_02_120001_create_equipment_categories_table', 15),
(48, '2025_07_02_120002_create_equipment_table', 15),
(49, '2025_07_02_120003_create_material_categories_table', 15),
(50, '2025_07_02_120004_create_materials_table', 15),
(51, '2025_07_02_120005_create_equipment_borrowings_table', 15),
(52, '2025_07_02_120006_create_material_usages_table', 15),
(53, '2025_07_02_120007_create_material_stock_logs_table', 15),
(54, '2025_07_03_100001_create_experiment_plans_table', 16),
(55, '2025_07_03_110001_create_experiment_records_table', 17),
(56, '2025_07_03_110002_create_experiment_photos_table', 17),
(59, '2025_07_03_120001_create_experiment_review_logs_table', 18);

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
-- 表的结构 `organization_permissions`
--

CREATE TABLE `organization_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organization_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `access_type` enum('allow','deny') NOT NULL DEFAULT 'allow' COMMENT '访问类型',
  `granted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `granted_at` timestamp NULL DEFAULT NULL COMMENT '授权时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(37, 'App\\Models\\User', 1, 'auth-token', 'a7f6a969173c253b0e9786683ec69d7a89311cffacb51cafb75c4898c8e5428e', '[\"*\"]', '2025-06-27 04:31:12', NULL, '2025-06-27 04:31:11', '2025-06-27 04:31:12'),
(38, 'App\\Models\\User', 1, 'auth-token', '7814813786f3b393bf1566f02b18b9bd484499343e7474848e7f7ab07c1294ea', '[\"*\"]', '2025-06-27 04:35:05', NULL, '2025-06-27 04:35:04', '2025-06-27 04:35:05'),
(39, 'App\\Models\\User', 1, 'auth-token', '641c5a1c10ff3ba71fe125267659563b3a728f7026330e1039a87082c314ea50', '[\"*\"]', '2025-06-27 04:37:49', NULL, '2025-06-27 04:37:35', '2025-06-27 04:37:49'),
(43, 'App\\Models\\User', 1, 'auth-token', '512c6de032ad3aabf838337cc3c0e25df082e5dbbf5e8cb3bae6e7e188bd14ad', '[\"*\"]', '2025-06-27 16:52:33', NULL, '2025-06-27 15:36:53', '2025-06-27 16:52:33'),
(44, 'App\\Models\\User', 1, 'auth-token', 'fd019b6b572a0ee6bbe9ea4483a93a6e396b58499d49155ad81e58a740c6a388', '[\"*\"]', '2025-06-27 16:54:27', NULL, '2025-06-27 16:54:15', '2025-06-27 16:54:27'),
(47, 'App\\Models\\User', 1, 'auth-token', 'fe5d0d73243cb803fe5ee21f1d31ef0f8d96c69570a5dc18379eb3b781866d50', '[\"*\"]', '2025-07-02 05:51:00', NULL, '2025-06-27 17:40:13', '2025-07-02 05:51:00'),
(49, 'App\\Models\\User', 1, 'auth-token', 'b6436b64cadc086d106d3c1524508bf8e2a825334412517eb0fd50d3d3d2a281', '[\"*\"]', '2025-07-02 01:06:09', NULL, '2025-07-02 00:45:44', '2025-07-02 01:06:09'),
(50, 'App\\Models\\User', 1, 'auth-token', '5ccf8b5140891fc651885f4fe9fafa2e5a70f3a38d9abf1c3bc6c04a9991f4b6', '[\"*\"]', '2025-07-02 19:36:34', NULL, '2025-07-02 01:08:36', '2025-07-02 19:36:34'),
(51, 'App\\Models\\User', 1, 'auth-token', 'c95b6a39ffdea6a55bf71c68ddd33d1cf4caf651facb27761e2f291580cb57f2', '[\"*\"]', NULL, NULL, '2025-07-02 06:06:29', '2025-07-02 06:06:29'),
(52, 'App\\Models\\User', 1, 'auth-token', '4fc176c9f193345775c3438ed60f7ca58773a649766f8f01a83edd90ef609a24', '[\"*\"]', '2025-07-02 06:16:57', NULL, '2025-07-02 06:13:38', '2025-07-02 06:16:57'),
(54, 'App\\Models\\User', 1, 'auth-token', '414dd033421e8d632427e67aef3b1307f855466b1833afedbe5607243f2411e3', '[\"*\"]', NULL, NULL, '2025-07-02 18:41:01', '2025-07-02 18:41:01'),
(56, 'App\\Models\\User', 1, 'auth-token', '73c2d3f286a3e6e7dea3750001fbfef35e9c0db93977c2e9979feefad4d361cf', '[\"*\"]', '2025-07-02 19:32:28', NULL, '2025-07-02 18:52:57', '2025-07-02 19:32:28'),
(57, 'App\\Models\\User', 1, 'auth-token', 'bd6d4c83c0c843d0f368399edfc238840cf460c2ef1fae73be3914911c6f964d', '[\"*\"]', '2025-07-02 19:33:23', NULL, '2025-07-02 19:32:44', '2025-07-02 19:33:23'),
(60, 'App\\Models\\User', 1, 'auth-token', 'e240e331da8f37290304dab9d31b2fccbfd64a259b136b47dbcced4a47640a9a', '[\"*\"]', NULL, NULL, '2025-07-03 02:25:22', '2025-07-03 02:25:22'),
(61, 'App\\Models\\User', 1, 'auth-token', 'ea94d31b2c6887717d62effaced4f8ffc1322d6b5e15ad09f3e9cb1181c4787e', '[\"*\"]', NULL, NULL, '2025-07-03 02:25:29', '2025-07-03 02:25:29'),
(62, 'App\\Models\\User', 1, 'auth-token', '15dc1a428035302d56090fbd3b02c6ed50678908c7a883fba8e42ccfcbb74de4', '[\"*\"]', NULL, NULL, '2025-07-03 05:08:11', '2025-07-03 05:08:11'),
(67, 'App\\Models\\User', 1, 'auth-token', '7485d21b72cc31fe609e47ac1df4f12b90ba7523cc97ff5843014c7937935519', '[\"*\"]', '2025-07-03 06:27:46', NULL, '2025-07-03 06:20:35', '2025-07-03 06:27:46'),
(68, 'App\\Models\\User', 1, 'auth-token', 'cfded0c75f98fed69bd88032f6b7a50c75dc4a681d74c432a0660c238f2bbab2', '[\"*\"]', NULL, NULL, '2025-07-03 06:30:37', '2025-07-03 06:30:37'),
(69, 'App\\Models\\User', 3, 'auth-token', 'd4ff8f85989dd96b872ab05be6fd46791521f3c3c90438ed94779de4a97c9e27', '[\"*\"]', NULL, NULL, '2025-07-03 06:30:46', '2025-07-03 06:30:46'),
(71, 'App\\Models\\User', 1, 'auth-token', 'da8813b335e9f052d7a5dcb166b335fb8ef3ed5d240955aead191fccbc6c50e2', '[\"*\"]', '2025-07-03 06:32:18', NULL, '2025-07-03 06:32:11', '2025-07-03 06:32:18'),
(77, 'App\\Models\\User', 1, 'auth-token', '57a5618052d6381282622a645846c2fef6e8b234eb4fa6bbd56d1bbff217dea7', '[\"*\"]', '2025-07-03 23:39:53', NULL, '2025-07-03 22:35:45', '2025-07-03 23:39:53'),
(78, 'App\\Models\\User', 1, 'auth-token', '27312bfaaccd7d993fd5e1370acc0fdac17a80fe985ef3967abe377595b2f05d', '[\"*\"]', NULL, NULL, '2025-07-03 23:01:11', '2025-07-03 23:01:11'),
(79, 'App\\Models\\User', 1, 'auth-token', 'd882f6b2a3fdf19621898218560ae110de81adad1fc48f7f446df066c3d6e536', '[\"*\"]', NULL, NULL, '2025-07-03 23:01:44', '2025-07-03 23:01:44'),
(80, 'App\\Models\\User', 1, 'auth-token', 'a6ba2b0a643623eec3000c9087d2b3164b8c11752f9a7615dbcb619dd2bbef35', '[\"*\"]', NULL, NULL, '2025-07-03 23:02:23', '2025-07-03 23:02:23'),
(81, 'App\\Models\\User', 1, 'auth-token', 'db714b6457873dd6fc849de3eb30f11258c9c2ffd5fbdac817c0f954ff4bb809', '[\"*\"]', '2025-07-03 23:08:05', NULL, '2025-07-03 23:08:04', '2025-07-03 23:08:05'),
(82, 'App\\Models\\User', 1, 'auth-token', 'e24e700373c282684f2df6ba558920d510db89145c6b872de9b0138a0e2602a1', '[\"*\"]', '2025-07-03 23:14:10', NULL, '2025-07-03 23:14:10', '2025-07-03 23:14:10'),
(83, 'App\\Models\\User', 1, 'auth-token', '84b8a3099b8ce4ce4aa1a1d74f775e283cd248262cfdb8016019c49888d08af2', '[\"*\"]', '2025-07-03 23:19:07', NULL, '2025-07-03 23:19:07', '2025-07-03 23:19:07'),
(84, 'App\\Models\\User', 1, 'auth-token', '528fbabc54fddcfd360cacba4bd8e1e6bff244fe5113d242fba350537dde735b', '[\"*\"]', '2025-07-03 23:27:15', NULL, '2025-07-03 23:27:14', '2025-07-03 23:27:15'),
(85, 'App\\Models\\User', 1, 'auth-token', '14a02ec6c421d1dc95b7b87e1f06ad06fcc5da9e46ee09136879f0f4777b8734', '[\"*\"]', '2025-07-03 23:30:43', NULL, '2025-07-03 23:30:42', '2025-07-03 23:30:43'),
(86, 'App\\Models\\User', 1, 'auth-token', 'f860dfa4bb583708e45540aa6d15e3d4a41ab796e1c1f14b7d21d4bca6681e2e', '[\"*\"]', '2025-07-03 23:34:36', NULL, '2025-07-03 23:34:35', '2025-07-03 23:34:36'),
(87, 'App\\Models\\User', 1, 'auth-token', '9caf652078305f9a4480c0f2f0712adddf2a1ead4576e8a7a668390e5ee630c4', '[\"*\"]', '2025-07-03 23:40:46', NULL, '2025-07-03 23:40:46', '2025-07-03 23:40:46'),
(88, 'App\\Models\\User', 1, 'auth-token', '95804f333c502e3b3bd2c8db3099066b82833859b6feb6db9948f4c7e281290c', '[\"*\"]', '2025-07-03 23:52:30', NULL, '2025-07-03 23:52:30', '2025-07-03 23:52:30'),
(89, 'App\\Models\\User', 1, 'auth-token', 'ddef03d32b79d3b97e5d2f3aa0722ea58194617454c9ac7bf5f40c038938266e', '[\"*\"]', '2025-07-04 00:22:50', NULL, '2025-07-04 00:22:50', '2025-07-04 00:22:50'),
(90, 'App\\Models\\User', 1, 'auth-token', '7ec08c3fa1de14b24b1ad4e8dbc5e0534293122abc1cc7ce6e5c8c3376d8f104', '[\"*\"]', '2025-07-04 00:35:00', NULL, '2025-07-04 00:34:59', '2025-07-04 00:35:00'),
(91, 'App\\Models\\User', 1, 'auth-token', 'c95339e8228efcbe3e67fc71f65c12d6021093c6403bf12e5dde1380d95e5663', '[\"*\"]', '2025-07-04 01:07:02', NULL, '2025-07-04 01:07:01', '2025-07-04 01:07:02'),
(92, 'App\\Models\\User', 1, 'auth-token', 'e69cf2f52d07a6145d156feb21004e9adb79d1e990aa1ecf3dc3be5fd252b73d', '[\"*\"]', '2025-07-04 01:37:40', NULL, '2025-07-04 01:37:40', '2025-07-04 01:37:40'),
(93, 'App\\Models\\User', 1, 'auth-token', '00a7517c9f086201721c4b2b76a5c06968aebf392e4ea182c24235f0b2ab7767', '[\"*\"]', '2025-07-04 01:39:04', NULL, '2025-07-04 01:39:03', '2025-07-04 01:39:04'),
(94, 'App\\Models\\User', 1, 'auth-token', '39b015d8bccf3d834919bc6684c9d9da5d7e1602d049cc0967dad261ba175ad8', '[\"*\"]', '2025-07-04 01:40:35', NULL, '2025-07-04 01:40:34', '2025-07-04 01:40:35'),
(95, 'App\\Models\\User', 1, 'auth-token', 'a59d63647729b508d339a761c81ef3c56d01c45dec0fc343d8ec7765c6696196', '[\"*\"]', '2025-07-04 01:41:00', NULL, '2025-07-04 01:40:59', '2025-07-04 01:41:00'),
(96, 'App\\Models\\User', 1, 'auth-token', '2d47c3fd8e2194bfbfc4f0aa189e065a0d6b9e9afaca5f0b7a9e07e9af74886e', '[\"*\"]', '2025-07-04 01:42:35', NULL, '2025-07-04 01:42:35', '2025-07-04 01:42:35'),
(97, 'App\\Models\\User', 1, 'auth-token', '1ca8ff70b8f04cbdaf9c434da30054a6ad3858a252582acc763dae5c70ff4dba', '[\"*\"]', '2025-07-04 01:42:55', NULL, '2025-07-04 01:42:55', '2025-07-04 01:42:55'),
(110, 'App\\Models\\User', 1, 'auth-token', 'aed4681cf1fdabec66c28a99ea261e0983bea892782960110339b25faba5b201', '[\"*\"]', '2025-07-04 05:54:02', NULL, '2025-07-04 05:52:19', '2025-07-04 05:54:02'),
(111, 'App\\Models\\User', 1, 'auth-token', 'b1695783fb5c4bc6b76d3d1cd41b939b892c172582b97e4384c4c870f2a73cb0', '[\"*\"]', '2025-07-04 06:09:11', NULL, '2025-07-04 06:08:57', '2025-07-04 06:09:11');

-- --------------------------------------------------------

--
-- 表的结构 `photo_templates`
--

CREATE TABLE `photo_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL COMMENT '模板名称',
  `code` varchar(50) NOT NULL COMMENT '模板编码',
  `subject` enum('physics','chemistry','biology','science') NOT NULL COMMENT '学科',
  `grade` enum('grade1','grade2','grade3','grade4','grade5','grade6','grade7','grade8','grade9') NOT NULL COMMENT '年级',
  `textbook_version` varchar(100) NOT NULL COMMENT '教材版本',
  `experiment_type` enum('demonstration','group','individual','inquiry') NOT NULL COMMENT '实验类型',
  `description` text DEFAULT NULL COMMENT '模板描述',
  `required_photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '必需照片配置' CHECK (json_valid(`required_photos`)),
  `optional_photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '可选照片配置' CHECK (json_valid(`optional_photos`)),
  `photo_specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '照片规格要求' CHECK (json_valid(`photo_specifications`)),
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'draft' COMMENT '状态',
  `organization_id` bigint(20) UNSIGNED NOT NULL COMMENT '所属组织ID',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID',
  `extra_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '扩展数据' CHECK (json_valid(`extra_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `photo_templates`
--

INSERT INTO `photo_templates` (`id`, `name`, `code`, `subject`, `grade`, `textbook_version`, `experiment_type`, `description`, `required_photos`, `optional_photos`, `photo_specifications`, `status`, `organization_id`, `created_by`, `updated_by`, `extra_data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '小学科学观察实验照片模板', 'PT_SCI_OBS', 'science', 'grade3', '人教版2022', 'group', '适用于小学科学观察类实验的照片拍摄模板', '[{\"name\":\"\\u5b9e\\u9a8c\\u6750\\u6599\\u51c6\\u5907\",\"description\":\"\\u5c55\\u793a\\u5b9e\\u9a8c\\u6240\\u9700\\u7684\\u6240\\u6709\\u6750\\u6599\",\"timing\":\"before\",\"angle\":\"overview\"},{\"name\":\"\\u5b66\\u751f\\u89c2\\u5bdf\\u8fc7\\u7a0b\",\"description\":\"\\u5b66\\u751f\\u4f7f\\u7528\\u653e\\u5927\\u955c\\u89c2\\u5bdf\\u7684\\u8fc7\\u7a0b\",\"timing\":\"during\",\"angle\":\"close-up\"},{\"name\":\"\\u89c2\\u5bdf\\u8bb0\\u5f55\\u7ed3\\u679c\",\"description\":\"\\u5b66\\u751f\\u8bb0\\u5f55\\u7684\\u89c2\\u5bdf\\u7ed3\\u679c\",\"timing\":\"after\",\"angle\":\"document\"}]', '[{\"name\":\"\\u5c0f\\u7ec4\\u8ba8\\u8bba\",\"description\":\"\\u5b66\\u751f\\u5c0f\\u7ec4\\u8ba8\\u8bba\\u5b9e\\u9a8c\\u7ed3\\u679c\",\"timing\":\"after\",\"angle\":\"group\"}]', '{\"resolution\":\"1920x1080\",\"format\":\"JPG\",\"quality\":\"high\",\"lighting\":\"natural_light_preferred\"}', 'active', 5, 3, NULL, NULL, '2025-07-01 22:51:30', '2025-07-01 22:51:30', NULL),
(2, '初中化学实验照片模板', 'PT_CHEM_EXP', 'chemistry', 'grade9', '人教版2022', 'demonstration', '适用于初中化学演示实验的照片拍摄模板', '[{\"name\":\"\\u5b9e\\u9a8c\\u5668\\u6750\\u51c6\\u5907\",\"description\":\"\\u5b9e\\u9a8c\\u524d\\u5668\\u6750\\u548c\\u8bd5\\u5242\\u7684\\u51c6\\u5907\\u60c5\\u51b5\",\"timing\":\"before\",\"angle\":\"overview\"},{\"name\":\"\\u5b9e\\u9a8c\\u64cd\\u4f5c\\u8fc7\\u7a0b\",\"description\":\"\\u6559\\u5e08\\u6f14\\u793a\\u5b9e\\u9a8c\\u64cd\\u4f5c\\u7684\\u5173\\u952e\\u6b65\\u9aa4\",\"timing\":\"during\",\"angle\":\"close-up\"},{\"name\":\"\\u5b9e\\u9a8c\\u73b0\\u8c61\\u8bb0\\u5f55\",\"description\":\"\\u5b9e\\u9a8c\\u4e2d\\u51fa\\u73b0\\u7684\\u989c\\u8272\\u53d8\\u5316\\u7b49\\u73b0\\u8c61\",\"timing\":\"during\",\"angle\":\"close-up\"},{\"name\":\"\\u5b89\\u5168\\u9632\\u62a4\\u63aa\\u65bd\",\"description\":\"\\u5b9e\\u9a8c\\u4e2d\\u7684\\u5b89\\u5168\\u9632\\u62a4\\u63aa\\u65bd\\u5c55\\u793a\",\"timing\":\"during\",\"angle\":\"safety\"}]', '[{\"name\":\"\\u5b66\\u751f\\u89c2\\u5bdf\\u53cd\\u5e94\",\"description\":\"\\u5b66\\u751f\\u89c2\\u5bdf\\u5b9e\\u9a8c\\u73b0\\u8c61\\u7684\\u53cd\\u5e94\",\"timing\":\"during\",\"angle\":\"audience\"},{\"name\":\"\\u5b9e\\u9a8c\\u603b\\u7ed3\\u8ba8\\u8bba\",\"description\":\"\\u5b9e\\u9a8c\\u540e\\u7684\\u603b\\u7ed3\\u548c\\u8ba8\\u8bba\",\"timing\":\"after\",\"angle\":\"classroom\"}]', '{\"resolution\":\"1920x1080\",\"format\":\"JPG\",\"quality\":\"high\",\"lighting\":\"lab_lighting\",\"safety_note\":\"ensure_no_hazardous_materials_visible\"}', 'active', 5, 3, NULL, NULL, '2025-07-01 22:51:30', '2025-07-01 22:51:30', NULL);

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
(3, 3, 6, 5, 'current_org', '2015-09-01', NULL, 'active', NULL, 1, '2025-06-27 04:49:40', '2025-07-04 05:48:27'),
(4, 6, 5, 16, 'current_org', NULL, NULL, 'active', NULL, 1, '2025-06-27 15:32:29', '2025-06-27 17:23:19'),
(8, 7, 8, 5, 'current_org', NULL, NULL, 'active', NULL, 1, '2025-07-04 05:49:20', '2025-07-04 05:49:20'),
(9, 8, 9, 5, 'current_org', NULL, NULL, 'active', NULL, 1, '2025-07-04 05:51:10', '2025-07-04 05:51:10');

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
(1, 'sysadmin', '系统管理员', '系统管理员', 'male', '1980-01-01', '13800000001', '130100198001010001', '河北省石家庄市长安区', '信息中心', '系统管理员', '高级工程师', '2020-01-01', '2025-06-20 05:40:05', '127.0.0.1', NULL, NULL, 'sysadmin@gcqets.edu.cn', 3, 5, 'SYS001', 'admin', 'active', '2025-07-02 00:08:59', '$2y$12$OTtNGn5toyQ4T/c5fqU5FOQhQCjzLjLGtQdLiHfVoOZ5jcpZKCDoK', NULL, '2025-06-20 01:16:56', '2025-07-02 19:31:06', NULL),
(2, 'lianzhou_admin', '赵学区主任', '赵学区主任', 'male', '1975-05-15', '13800000002', '130100197505150002', '河北省石家庄市藁城区廉州镇', '廉州学区', '学区主任', '高级教师', '2018-09-01', NULL, NULL, NULL, NULL, 'zhao.admin@gcqets.edu.cn', 4, NULL, 'LZ001', 'supervisor', 'active', '2025-07-02 00:08:59', '$2y$12$xIlKdktr6dlGQQCsZQ5mq.qKtsbarqyPEvlCzwAMotrjk02.7o5HG', NULL, '2025-06-20 01:16:56', '2025-07-02 00:08:59', NULL),
(3, 'dongcheng_principal', '刘校长', '刘校长', 'male', '1970-08-20', '13800000003', '130100197008200003', '河北省石家庄市藁城区廉州镇东城村', '校长室', '校长', '特级教师', '2015-09-01', NULL, NULL, NULL, NULL, 'liu.principal@dongcheng.edu.cn', 5, 5, 'DC001', 'admin', 'active', '2025-07-02 00:09:00', '$2y$12$bnDZYWgI4Os6sIscLnSRTeSxCWKF6tY37S4JnLJQQoKNuwpdm/v8i', NULL, '2025-06-20 01:16:57', '2025-07-04 05:48:27', NULL),
(6, 'gs_admin', 'hh', 'hh', 'male', NULL, '15123445677', NULL, NULL, 'quzhongxin', 'gly', NULL, NULL, NULL, NULL, NULL, '1133', 'hh78@163.com', NULL, 16, '123', 'teacher', 'active', NULL, '$2y$12$NF8dtcrLqelgpGe7J1aXAuqHulAWi4TagV.xLYrbZsCT1KXiW/UxK', NULL, '2025-06-27 15:32:29', '2025-06-27 17:23:18', NULL),
(7, 'li111', '李李', '李李', 'male', NULL, '14124228901', NULL, NULL, '科学研究', '科学教师', NULL, NULL, NULL, NULL, NULL, '科学教师', 'jjjjjzk@163.com', NULL, 5, '1112', 'teacher', 'active', NULL, '$2y$12$0WW4ifRU1YgW7of3CdqmgOEUiSejmu32QyjxUxCodV4l3CBwhTkSS', NULL, '2025-07-04 05:20:46', '2025-07-04 05:20:46', NULL),
(8, 'zhao2', '赵教师', '赵教师', 'male', NULL, NULL, NULL, NULL, NULL, '教师', NULL, NULL, NULL, NULL, NULL, '教师', 'sfa@163.com', NULL, 5, NULL, 'teacher', 'active', NULL, '$2y$12$sK0cSWBmma9RQ5LrNP.7mOG1oMoUBKz240CrABe/dzTt9hSCneSza', NULL, '2025-07-04 05:51:10', '2025-07-04 05:51:10', NULL);

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
(3, 3, 5, 1, 'active', '2025-06-20 01:17:00', '2025-07-04 05:48:27'),
(5, 6, 20, 0, 'active', '2025-06-27 15:32:29', '2025-06-27 17:23:19'),
(6, 7, 5, 1, 'active', '2025-07-04 05:20:46', '2025-07-04 05:49:20'),
(7, 8, 5, 1, 'active', '2025-07-04 05:51:10', '2025-07-04 05:51:10');

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
-- 表的索引 `catalog_import_logs`
--
ALTER TABLE `catalog_import_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catalog_import_logs_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `catalog_import_logs_status_created_at_index` (`status`,`created_at`),
  ADD KEY `catalog_import_logs_organization_id_import_type_index` (`organization_id`,`import_type`),
  ADD KEY `catalog_import_logs_import_type_status_index` (`import_type`,`status`);

--
-- 表的索引 `catalog_versions`
--
ALTER TABLE `catalog_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catalog_versions_catalog_id_version_index` (`catalog_id`,`version`),
  ADD KEY `catalog_versions_catalog_id_created_at_index` (`catalog_id`,`created_at`),
  ADD KEY `catalog_versions_change_type_status_index` (`change_type`,`status`),
  ADD KEY `catalog_versions_created_by_created_at_index` (`created_by`,`created_at`);

--
-- 表的索引 `curriculum_standards`
--
ALTER TABLE `curriculum_standards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `curriculum_standards_code_unique` (`code`),
  ADD KEY `curriculum_standards_subject_grade_version_index` (`subject`,`grade`,`version`),
  ADD KEY `curriculum_standards_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `curriculum_standards_effective_date_expiry_date_index` (`effective_date`,`expiry_date`),
  ADD KEY `curriculum_standards_created_by_created_at_index` (`created_by`,`created_at`);

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
-- 表的索引 `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_code_unique` (`code`),
  ADD KEY `equipment_category_id_status_index` (`category_id`,`status`),
  ADD KEY `equipment_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `equipment_status_location_index` (`status`,`location`),
  ADD KEY `equipment_purchase_date_warranty_date_index` (`purchase_date`,`warranty_date`),
  ADD KEY `equipment_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `equipment_updated_by_foreign` (`updated_by`);

--
-- 表的索引 `equipment_borrowings`
--
ALTER TABLE `equipment_borrowings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_borrowings_borrowing_code_unique` (`borrowing_code`),
  ADD KEY `equipment_borrowings_equipment_id_status_index` (`equipment_id`,`status`),
  ADD KEY `equipment_borrowings_borrower_id_status_index` (`borrower_id`,`status`),
  ADD KEY `equipment_borrowings_approver_id_approved_at_index` (`approver_id`,`approved_at`),
  ADD KEY `equipment_borrowings_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `equipment_borrowings_planned_start_time_planned_end_time_index` (`planned_start_time`,`planned_end_time`),
  ADD KEY `equipment_borrowings_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `equipment_borrowings_updated_by_foreign` (`updated_by`);

--
-- 表的索引 `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_categories_code_unique` (`code`),
  ADD KEY `equipment_categories_parent_id_status_index` (`parent_id`,`status`),
  ADD KEY `equipment_categories_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `equipment_categories_subject_grade_range_index` (`subject`,`grade_range`),
  ADD KEY `equipment_categories_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `equipment_categories_updated_by_foreign` (`updated_by`);

--
-- 表的索引 `experiment_catalogs`
--
ALTER TABLE `experiment_catalogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `experiment_catalogs_code_unique` (`code`),
  ADD KEY `experiment_catalogs_subject_grade_textbook_version_index` (`subject`,`grade`,`textbook_version`),
  ADD KEY `experiment_catalogs_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `experiment_catalogs_experiment_type_difficulty_level_index` (`experiment_type`,`difficulty_level`),
  ADD KEY `experiment_catalogs_created_by_created_at_index` (`created_by`,`created_at`);

--
-- 表的索引 `experiment_photos`
--
ALTER TABLE `experiment_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `experiment_photos_experiment_record_id_photo_type_index` (`experiment_record_id`,`photo_type`),
  ADD KEY `experiment_photos_compliance_status_created_at_index` (`compliance_status`,`created_at`),
  ADD KEY `experiment_photos_organization_id_photo_type_index` (`organization_id`,`photo_type`),
  ADD KEY `experiment_photos_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `experiment_photos_upload_method_created_at_index` (`upload_method`,`created_at`),
  ADD KEY `experiment_photos_hash_index` (`hash`);

--
-- 表的索引 `experiment_plans`
--
ALTER TABLE `experiment_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `experiment_plans_code_unique` (`code`),
  ADD KEY `experiment_plans_experiment_catalog_id_teacher_id_index` (`experiment_catalog_id`,`teacher_id`),
  ADD KEY `experiment_plans_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `experiment_plans_planned_date_status_index` (`planned_date`,`status`),
  ADD KEY `experiment_plans_teacher_id_status_index` (`teacher_id`,`status`),
  ADD KEY `experiment_plans_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `experiment_plans_approved_by_approved_at_index` (`approved_by`,`approved_at`),
  ADD KEY `experiment_plans_curriculum_standard_id_foreign` (`curriculum_standard_id`),
  ADD KEY `experiment_plans_updated_by_foreign` (`updated_by`);

--
-- 表的索引 `experiment_records`
--
ALTER TABLE `experiment_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `experiment_records_experiment_plan_id_execution_date_index` (`experiment_plan_id`,`execution_date`),
  ADD KEY `experiment_records_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `experiment_records_completion_status_status_index` (`completion_status`,`status`),
  ADD KEY `experiment_records_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `experiment_records_reviewed_by_reviewed_at_index` (`reviewed_by`,`reviewed_at`),
  ADD KEY `experiment_records_execution_date_completion_status_index` (`execution_date`,`completion_status`);

--
-- 表的索引 `experiment_review_logs`
--
ALTER TABLE `experiment_review_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `experiment_review_logs_experiment_record_id_review_type_index` (`experiment_record_id`,`review_type`),
  ADD KEY `experiment_review_logs_reviewer_id_created_at_index` (`reviewer_id`,`created_at`),
  ADD KEY `experiment_review_logs_organization_id_review_type_index` (`organization_id`,`review_type`),
  ADD KEY `experiment_review_logs_new_status_created_at_index` (`new_status`,`created_at`),
  ADD KEY `experiment_review_logs_review_category_created_at_index` (`review_category`,`created_at`),
  ADD KEY `experiment_review_logs_is_ai_review_created_at_index` (`is_ai_review`,`created_at`),
  ADD KEY `experiment_review_logs_is_urgent_created_at_index` (`is_urgent`,`created_at`);

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
-- 表的索引 `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `materials_code_unique` (`code`),
  ADD KEY `materials_category_id_status_index` (`category_id`,`status`),
  ADD KEY `materials_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `materials_current_stock_min_stock_index` (`current_stock`,`min_stock`),
  ADD KEY `materials_expiry_date_status_index` (`expiry_date`,`status`),
  ADD KEY `materials_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `materials_updated_by_foreign` (`updated_by`);

--
-- 表的索引 `material_categories`
--
ALTER TABLE `material_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_categories_code_unique` (`code`),
  ADD KEY `material_categories_parent_id_status_index` (`parent_id`,`status`),
  ADD KEY `material_categories_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `material_categories_subject_grade_range_index` (`subject`,`grade_range`),
  ADD KEY `material_categories_material_type_status_index` (`material_type`,`status`),
  ADD KEY `material_categories_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `material_categories_updated_by_foreign` (`updated_by`);

--
-- 表的索引 `material_stock_logs`
--
ALTER TABLE `material_stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_stock_logs_log_code_unique` (`log_code`),
  ADD KEY `material_stock_logs_material_id_operated_at_index` (`material_id`,`operated_at`),
  ADD KEY `material_stock_logs_operation_type_operated_at_index` (`operation_type`,`operated_at`),
  ADD KEY `material_stock_logs_operator_id_operated_at_index` (`operator_id`,`operated_at`),
  ADD KEY `material_stock_logs_organization_id_operated_at_index` (`organization_id`,`operated_at`),
  ADD KEY `material_stock_logs_reference_type_reference_id_index` (`reference_type`,`reference_id`);

--
-- 表的索引 `material_usages`
--
ALTER TABLE `material_usages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_usages_usage_code_unique` (`usage_code`),
  ADD KEY `material_usages_material_id_used_at_index` (`material_id`,`used_at`),
  ADD KEY `material_usages_user_id_used_at_index` (`user_id`,`used_at`),
  ADD KEY `material_usages_experiment_catalog_id_used_at_index` (`experiment_catalog_id`,`used_at`),
  ADD KEY `material_usages_organization_id_used_at_index` (`organization_id`,`used_at`),
  ADD KEY `material_usages_usage_type_used_at_index` (`usage_type`,`used_at`),
  ADD KEY `material_usages_created_by_created_at_index` (`created_by`,`created_at`),
  ADD KEY `material_usages_updated_by_foreign` (`updated_by`);

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
-- 表的索引 `organization_permissions`
--
ALTER TABLE `organization_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organization_permissions_organization_id_permission_id_unique` (`organization_id`,`permission_id`),
  ADD KEY `organization_permissions_permission_id_foreign` (`permission_id`),
  ADD KEY `organization_permissions_granted_by_foreign` (`granted_by`),
  ADD KEY `organization_permissions_organization_id_access_type_index` (`organization_id`,`access_type`);

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
-- 表的索引 `photo_templates`
--
ALTER TABLE `photo_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `photo_templates_code_unique` (`code`),
  ADD KEY `photo_templates_subject_grade_textbook_version_index` (`subject`,`grade`,`textbook_version`),
  ADD KEY `photo_templates_organization_id_status_index` (`organization_id`,`status`),
  ADD KEY `photo_templates_experiment_type_status_index` (`experiment_type`,`status`),
  ADD KEY `photo_templates_created_by_created_at_index` (`created_by`,`created_at`);

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
-- 使用表AUTO_INCREMENT `catalog_import_logs`
--
ALTER TABLE `catalog_import_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `catalog_versions`
--
ALTER TABLE `catalog_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `curriculum_standards`
--
ALTER TABLE `curriculum_standards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- 使用表AUTO_INCREMENT `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用表AUTO_INCREMENT `equipment_borrowings`
--
ALTER TABLE `equipment_borrowings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `equipment_categories`
--
ALTER TABLE `equipment_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `experiment_catalogs`
--
ALTER TABLE `experiment_catalogs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `experiment_photos`
--
ALTER TABLE `experiment_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用表AUTO_INCREMENT `experiment_plans`
--
ALTER TABLE `experiment_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `experiment_records`
--
ALTER TABLE `experiment_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `experiment_review_logs`
--
ALTER TABLE `experiment_review_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- 使用表AUTO_INCREMENT `materials`
--
ALTER TABLE `materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用表AUTO_INCREMENT `material_categories`
--
ALTER TABLE `material_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `material_stock_logs`
--
ALTER TABLE `material_stock_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `material_usages`
--
ALTER TABLE `material_usages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

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
-- 使用表AUTO_INCREMENT `organization_permissions`
--
ALTER TABLE `organization_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- 使用表AUTO_INCREMENT `photo_templates`
--
ALTER TABLE `photo_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用表AUTO_INCREMENT `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `user_organizations`
--
ALTER TABLE `user_organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- 限制表 `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `equipment_borrowings`
--
ALTER TABLE `equipment_borrowings`
  ADD CONSTRAINT `equipment_borrowings_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_borrowings_borrower_id_foreign` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_borrowings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_borrowings_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_borrowings_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_borrowings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD CONSTRAINT `equipment_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_categories_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `equipment_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `experiment_plans`
--
ALTER TABLE `experiment_plans`
  ADD CONSTRAINT `experiment_plans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `experiment_plans_curriculum_standard_id_foreign` FOREIGN KEY (`curriculum_standard_id`) REFERENCES `curriculum_standards` (`id`),
  ADD CONSTRAINT `experiment_plans_experiment_catalog_id_foreign` FOREIGN KEY (`experiment_catalog_id`) REFERENCES `experiment_catalogs` (`id`),
  ADD CONSTRAINT `experiment_plans_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  ADD CONSTRAINT `experiment_plans_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `experiment_plans_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `import_logs`
--
ALTER TABLE `import_logs`
  ADD CONSTRAINT `import_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `material_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `material_categories`
--
ALTER TABLE `material_categories`
  ADD CONSTRAINT `material_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_categories_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `material_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- 限制表 `material_stock_logs`
--
ALTER TABLE `material_stock_logs`
  ADD CONSTRAINT `material_stock_logs_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_stock_logs_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_stock_logs_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- 限制表 `material_usages`
--
ALTER TABLE `material_usages`
  ADD CONSTRAINT `material_usages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_usages_experiment_catalog_id_foreign` FOREIGN KEY (`experiment_catalog_id`) REFERENCES `experiment_catalogs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `material_usages_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_usages_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_usages_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `material_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- 限制表 `organization_permissions`
--
ALTER TABLE `organization_permissions`
  ADD CONSTRAINT `organization_permissions_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `organization_permissions_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organization_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

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
