# 实验教学管理系统 API 文档

## 📋 概述

本文档描述了实验教学管理系统模块三"实验过程管理"的所有API接口。

### 基础信息
- **基础URL**: `http://localhost:8000/api`
- **认证方式**: Bearer Token
- **数据格式**: JSON
- **字符编码**: UTF-8

### 通用响应格式
```json
{
  "success": true,
  "message": "操作成功",
  "data": {
    // 具体数据
  }
}
```

### 错误响应格式
```json
{
  "success": false,
  "message": "错误信息",
  "errors": {
    // 详细错误信息
  }
}
```

## 🔐 认证接口

### 登录
```http
POST /auth/login
```

**请求参数**:
```json
{
  "username": "string",
  "password": "string"
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "登录成功",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 1,
      "username": "teacher01",
      "real_name": "张老师",
      "user_type": "teacher",
      "organization_id": 1
    }
  }
}
```

## 📅 实验计划管理

### 获取实验计划列表
```http
GET /experiment-plans
```

**查询参数**:
- `page`: 页码 (默认: 1)
- `per_page`: 每页数量 (默认: 15)
- `status`: 状态筛选
- `teacher_id`: 教师ID筛选
- `search`: 搜索关键词

**响应示例**:
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "光的折射实验",
        "code": "EXP20250703001",
        "status": "approved",
        "status_label": "已批准",
        "planned_date": "2025-07-10",
        "planned_duration": 45,
        "student_count": 30,
        "class_name": "五年级A班",
        "teacher": {
          "id": 2,
          "real_name": "张老师"
        },
        "experiment_catalog": {
          "id": 1,
          "name": "物理实验"
        },
        "created_at": "2025-07-03T10:00:00Z"
      }
    ],
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

### 创建实验计划
```http
POST /experiment-plans
```

**请求参数**:
```json
{
  "name": "光的折射实验",
  "experiment_catalog_id": 1,
  "class_name": "五年级A班",
  "student_count": 30,
  "planned_date": "2025-07-10",
  "planned_duration": 45,
  "priority": "medium",
  "description": "通过实验观察光在不同介质中的折射现象",
  "objectives": "理解光的折射定律",
  "key_points": "实验操作规范，数据记录准确",
  "safety_requirements": "注意实验安全",
  "equipment_requirements": {
    "显微镜": "10台",
    "试管": "30支"
  },
  "material_requirements": {
    "试剂": "5种",
    "记录表": "30张"
  }
}
```

### 获取实验计划详情
```http
GET /experiment-plans/{id}
```

### 更新实验计划
```http
PUT /experiment-plans/{id}
```

### 删除实验计划
```http
DELETE /experiment-plans/{id}
```

### 提交审批
```http
POST /experiment-plans/{id}/submit
```

### 审批实验计划
```http
POST /experiment-plans/{id}/approve
```

**请求参数**:
```json
{
  "action": "approve", // approve, reject
  "comments": "审批意见"
}
```

## 📝 实验记录管理

### 获取实验记录列表
```http
GET /experiment-records
```

### 创建实验记录
```http
POST /experiment-records
```

**请求参数**:
```json
{
  "experiment_plan_id": 1,
  "execution_date": "2025-07-10",
  "actual_duration": 50,
  "actual_student_count": 28,
  "completion_status": "completed",
  "experiment_process": "实验过程描述",
  "observations": "观察记录",
  "results": "实验结果",
  "problems_encountered": "遇到的问题",
  "improvements": "改进建议",
  "teacher_notes": "教师备注",
  "equipment_used": {
    "显微镜": "10台",
    "试管": "28支"
  },
  "materials_consumed": {
    "试剂A": "50ml",
    "记录表": "28张"
  }
}
```

### 上传实验照片
```http
POST /experiment-records/{id}/photos
```

**请求参数** (multipart/form-data):
- `photos[]`: 照片文件
- `photo_types[]`: 照片类型 (setup, process, result, cleanup)
- `descriptions[]`: 照片描述

## ✅ 实验记录审核

### 获取待审核记录
```http
GET /experiment-review/pending
```

### 批量审核
```http
POST /experiment-review/batch
```

**请求参数**:
```json
{
  "record_ids": [1, 2, 3],
  "action": "approve", // approve, reject, revision
  "comments": "批量审核意见"
}
```

### 单个审核
```http
POST /experiment-review/{id}/approve
POST /experiment-review/{id}/reject
POST /experiment-review/{id}/revision
```

### AI照片检查
```http
POST /experiment-review/{id}/ai-check
```

### 获取审核日志
```http
GET /experiment-review/{id}/logs
```

## 📅 实验日历

### 获取日历数据
```http
GET /experiment-calendar/data
```

**查询参数**:
- `start`: 开始日期 (YYYY-MM-DD)
- `end`: 结束日期 (YYYY-MM-DD)

**响应示例**:
```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": 1,
        "title": "光的折射实验",
        "start": "2025-07-10",
        "end": "2025-07-10",
        "allDay": true,
        "backgroundColor": "#67C23A",
        "extendedProps": {
          "type": "experiment_plan",
          "status": "approved",
          "teacher": "张老师",
          "className": "五年级A班",
          "isOverdue": false
        }
      }
    ],
    "summary": {
      "totalPlans": 10,
      "overduePlans": 2
    }
  }
}
```

### 获取逾期预警
```http
GET /experiment-calendar/overdue-alerts
```

### 检查日程冲突
```http
POST /experiment-calendar/check-conflicts
```

**请求参数**:
```json
{
  "date": "2025-07-10",
  "teacher_id": 2,
  "exclude_id": 1
}
```

## 📊 实验监控

### 获取监控看板
```http
GET /experiment-monitor/dashboard
```

**查询参数**:
- `time_range`: 时间范围 (week, month, quarter, year)

**响应示例**:
```json
{
  "success": true,
  "data": {
    "basicStats": {
      "totalPlans": 100,
      "approvedPlans": 80,
      "completedPlans": 60,
      "overduePlans": 5,
      "completionRate": 60.0
    },
    "progressStats": {
      "statusStats": {
        "approved": 80,
        "completed": 60,
        "pending": 15
      },
      "monthlyStats": [
        {
          "month": "2025-07",
          "count": 25
        }
      ]
    },
    "trendData": [
      {
        "date": "2025-07-01",
        "total": 5,
        "approved": 4,
        "completed": 2
      }
    ],
    "anomalyData": {
      "overdueExperiments": [],
      "pendingRecords": [],
      "lowCompletionExperiments": []
    },
    "rankings": {
      "teacherRanking": [
        {
          "teacher": "张老师",
          "planCount": 15
        }
      ],
      "catalogRanking": [
        {
          "catalog": "物理实验",
          "usageCount": 25
        }
      ]
    }
  }
}
```

### 获取实时统计
```http
GET /experiment-monitor/realtime-stats
```

### 获取进度统计
```http
GET /experiment-monitor/progress-stats
```

### 获取异常分析
```http
GET /experiment-monitor/anomaly-analysis
```

## 📋 状态码说明

| 状态码 | 说明 |
|--------|------|
| 200 | 请求成功 |
| 201 | 创建成功 |
| 400 | 请求参数错误 |
| 401 | 未授权 |
| 403 | 权限不足 |
| 404 | 资源不存在 |
| 422 | 数据验证失败 |
| 500 | 服务器内部错误 |

## 🔒 权限说明

### 用户类型
- `admin`: 系统管理员
- `school_admin`: 学校管理员  
- `district_admin`: 区域管理员
- `teacher`: 教师

### 权限矩阵

| 功能 | 系统管理员 | 学校管理员 | 区域管理员 | 教师 |
|------|------------|------------|------------|------|
| 创建实验计划 | ✅ | ✅ | ✅ | ✅ |
| 审批实验计划 | ✅ | ✅ | ✅ | ❌ |
| 填报实验记录 | ✅ | ✅ | ✅ | ✅ |
| 审核实验记录 | ✅ | ✅ | ✅ | ❌ |
| 查看监控数据 | ✅ | ✅ | ✅ | ✅ |
| 系统测试功能 | ✅ | ❌ | ❌ | ❌ |

## 📝 数据字典

### 实验计划状态
- `draft`: 草稿
- `pending`: 待审批
- `approved`: 已批准
- `rejected`: 已拒绝
- `executing`: 执行中
- `completed`: 已完成
- `cancelled`: 已取消

### 完成状态
- `not_started`: 未开始
- `in_progress`: 进行中
- `partial`: 部分完成
- `completed`: 已完成
- `cancelled`: 已取消

### 审核状态
- `pending`: 待审核
- `approved`: 已通过
- `rejected`: 已拒绝
- `revision_required`: 需要修订

### 照片类型
- `setup`: 实验准备
- `process`: 实验过程
- `result`: 实验结果
- `cleanup`: 实验清理

## 🔧 开发说明

### 环境要求
- PHP >= 8.0
- Laravel >= 9.0
- MySQL >= 5.7
- Node.js >= 16.0

### 本地开发
```bash
# 后端
cd backend
composer install
php artisan migrate
php artisan db:seed
php artisan serve

# 前端
cd frontend
npm install
npm run dev
```

### 测试
```bash
# 后端测试
php artisan test

# 前端测试
npm run test
```

---

**文档版本**: v1.0  
**更新时间**: 2025-07-03  
**维护者**: 开发团队
