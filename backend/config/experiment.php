<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 实验教学管理系统配置
    |--------------------------------------------------------------------------
    */

    // 分页配置
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
        'plans_per_page' => 15,
        'records_per_page' => 20,
        'reviews_per_page' => 25,
    ],

    // 文件上传配置
    'upload' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_photo_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'photo_quality' => 85,
        'thumbnail_size' => [200, 200],
        'watermark_enabled' => true,
        'watermark_text' => '实验教学管理系统',
    ],

    // 缓存配置
    'cache' => [
        'enabled' => true,
        'default_ttl' => 3600, // 1小时
        'statistics_ttl' => 1800, // 30分钟
        'options_ttl' => 7200, // 2小时
        'user_permissions_ttl' => 3600, // 1小时
    ],

    // 审核配置
    'review' => [
        'auto_ai_check' => true,
        'ai_check_timeout' => 30, // 秒
        'batch_size_limit' => 50,
        'review_timeout_days' => 7,
        'revision_timeout_days' => 3,
        'max_revision_count' => 3,
    ],

    // 通知配置
    'notification' => [
        'enabled' => true,
        'channels' => ['database', 'mail'],
        'review_notifications' => true,
        'deadline_notifications' => true,
        'status_change_notifications' => true,
    ],

    // 安全配置
    'security' => [
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15分钟
        'password_min_length' => 8,
        'session_timeout' => 7200, // 2小时
        'api_rate_limit' => 1000, // 每小时请求数
    ],

    // 数据验证配置
    'validation' => [
        'plan_name_max_length' => 200,
        'plan_description_max_length' => 2000,
        'record_notes_max_length' => 5000,
        'review_notes_max_length' => 1000,
        'min_photos_required' => 1,
        'max_photos_per_record' => 20,
    ],

    // 统计配置
    'statistics' => [
        'cache_enabled' => true,
        'real_time_updates' => false,
        'batch_update_interval' => 300, // 5分钟
        'history_retention_days' => 365,
    ],

    // 导出配置
    'export' => [
        'max_records' => 10000,
        'timeout' => 300, // 5分钟
        'formats' => ['xlsx', 'csv', 'pdf'],
        'include_photos' => false,
        'compress_large_exports' => true,
    ],

    // 日志配置
    'logging' => [
        'enabled' => true,
        'log_api_requests' => false,
        'log_database_queries' => false,
        'log_user_actions' => true,
        'retention_days' => 90,
    ],

    // 性能配置
    'performance' => [
        'enable_query_cache' => true,
        'enable_response_cache' => true,
        'enable_compression' => true,
        'lazy_loading' => true,
        'eager_load_relations' => [
            'plans' => ['experimentCatalog', 'teacher', 'organization'],
            'records' => ['experimentPlan', 'creator', 'organization'],
            'photos' => ['experimentRecord'],
            'reviews' => ['experimentRecord', 'reviewer'],
        ],
    ],

    // 业务规则配置
    'business_rules' => [
        'plan_approval_required' => true,
        'record_review_required' => true,
        'allow_self_review' => false,
        'require_equipment_confirmation' => true,
        'require_safety_notes' => true,
        'auto_archive_completed' => false,
    ],

    // 状态配置
    'statuses' => [
        'plan' => [
            'draft' => '草稿',
            'pending' => '待审批',
            'approved' => '已批准',
            'rejected' => '已拒绝',
            'executing' => '执行中',
            'completed' => '已完成',
            'cancelled' => '已取消',
        ],
        'record' => [
            'draft' => '草稿',
            'submitted' => '已提交',
            'under_review' => '审核中',
            'approved' => '已通过',
            'rejected' => '已拒绝',
            'revision_required' => '需要修改',
        ],
        'photo' => [
            'pending' => '待检查',
            'compliant' => '合规',
            'non_compliant' => '不合规',
            'under_review' => '人工审核中',
        ],
    ],

    // 优先级配置
    'priorities' => [
        'low' => '低',
        'medium' => '中',
        'high' => '高',
        'urgent' => '紧急',
    ],

    // 完成状态配置
    'completion_statuses' => [
        'not_started' => '未开始',
        'in_progress' => '进行中',
        'completed' => '已完成',
        'cancelled' => '已取消',
        'delayed' => '延期',
    ],

    // 照片类型配置
    'photo_types' => [
        'preparation' => '准备阶段',
        'process' => '过程记录',
        'result' => '结果展示',
        'equipment' => '器材准备',
        'safety' => '安全记录',
    ],

    // 审核类型配置
    'review_types' => [
        'submit' => '提交审核',
        'approve' => '审核通过',
        'reject' => '审核拒绝',
        'revision_request' => '要求修改',
        'force_complete' => '强制完成',
        'batch_approve' => '批量通过',
        'batch_reject' => '批量拒绝',
        'ai_check' => 'AI检查',
        'manual_check' => '人工检查',
    ],

    // 审核分类配置
    'review_categories' => [
        'format' => '格式问题',
        'content' => '内容问题',
        'photo' => '照片问题',
        'data' => '数据问题',
        'safety' => '安全问题',
        'completeness' => '完整性问题',
        'other' => '其他问题',
    ],

    // API配置
    'api' => [
        'version' => 'v1',
        'rate_limit' => 1000,
        'timeout' => 30,
        'retry_attempts' => 3,
        'response_format' => 'json',
        'include_debug_info' => false,
    ],

    // 前端配置
    'frontend' => [
        'auto_save_interval' => 30, // 秒
        'session_warning_time' => 300, // 5分钟前警告
        'max_file_upload_size' => '10MB',
        'supported_browsers' => ['Chrome', 'Firefox', 'Safari', 'Edge'],
        'mobile_responsive' => true,
    ],

    // 集成配置
    'integrations' => [
        'ai_service' => [
            'enabled' => false,
            'provider' => 'local',
            'api_key' => env('AI_SERVICE_API_KEY'),
            'timeout' => 30,
        ],
        'email_service' => [
            'enabled' => true,
            'provider' => 'smtp',
            'templates_path' => 'emails.experiment',
        ],
        'sms_service' => [
            'enabled' => false,
            'provider' => 'aliyun',
            'api_key' => env('SMS_API_KEY'),
        ],
    ],

    // 维护配置
    'maintenance' => [
        'auto_cleanup_enabled' => true,
        'cleanup_interval' => 'daily',
        'delete_old_logs_days' => 90,
        'delete_old_files_days' => 365,
        'optimize_database' => true,
        'backup_enabled' => true,
        'backup_retention_days' => 30,
    ],
];
