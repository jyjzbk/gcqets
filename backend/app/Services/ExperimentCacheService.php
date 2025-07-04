<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\ExperimentPlan;
use App\Models\ExperimentRecord;
use Carbon\Carbon;

class ExperimentCacheService
{
    /**
     * 缓存过期时间（分钟）
     */
    const CACHE_TTL = [
        'dashboard' => 5,      // 监控看板缓存5分钟
        'calendar' => 10,      // 日历数据缓存10分钟
        'stats' => 15,         // 统计数据缓存15分钟
        'alerts' => 3,         // 预警数据缓存3分钟
    ];

    /**
     * 获取缓存键
     */
    private function getCacheKey($type, $params = [])
    {
        $user = Auth::user();
        $userKey = $user ? "{$user->id}_{$user->user_type}_{$user->organization_id}" : 'guest';
        $paramsKey = md5(serialize($params));
        
        return "experiment_{$type}_{$userKey}_{$paramsKey}";
    }

    /**
     * 获取监控看板缓存数据
     */
    public function getDashboardData($timeRange, $callback)
    {
        $cacheKey = $this->getCacheKey('dashboard', ['time_range' => $timeRange]);
        
        return Cache::remember($cacheKey, self::CACHE_TTL['dashboard'], $callback);
    }

    /**
     * 获取日历数据缓存
     */
    public function getCalendarData($start, $end, $callback)
    {
        $cacheKey = $this->getCacheKey('calendar', ['start' => $start, 'end' => $end]);
        
        return Cache::remember($cacheKey, self::CACHE_TTL['calendar'], $callback);
    }

    /**
     * 获取统计数据缓存
     */
    public function getStatsData($type, $params, $callback)
    {
        $cacheKey = $this->getCacheKey("stats_{$type}", $params);
        
        return Cache::remember($cacheKey, self::CACHE_TTL['stats'], $callback);
    }

    /**
     * 获取预警数据缓存
     */
    public function getAlertsData($params, $callback)
    {
        $cacheKey = $this->getCacheKey('alerts', $params);
        
        return Cache::remember($cacheKey, self::CACHE_TTL['alerts'], $callback);
    }

    /**
     * 清除用户相关缓存
     */
    public function clearUserCache($userId = null)
    {
        $user = $userId ? \App\Models\User::find($userId) : Auth::user();
        
        if (!$user) return;

        $patterns = [
            "experiment_dashboard_{$user->id}_*",
            "experiment_calendar_{$user->id}_*",
            "experiment_stats_{$user->id}_*",
            "experiment_alerts_{$user->id}_*",
        ];

        foreach ($patterns as $pattern) {
            $this->clearCacheByPattern($pattern);
        }
    }

    /**
     * 清除组织相关缓存
     */
    public function clearOrganizationCache($organizationId)
    {
        $patterns = [
            "experiment_*_{$organizationId}_*",
        ];

        foreach ($patterns as $pattern) {
            $this->clearCacheByPattern($pattern);
        }
    }

    /**
     * 清除所有实验相关缓存
     */
    public function clearAllExperimentCache()
    {
        $patterns = [
            'experiment_dashboard_*',
            'experiment_calendar_*',
            'experiment_stats_*',
            'experiment_alerts_*',
        ];

        foreach ($patterns as $pattern) {
            $this->clearCacheByPattern($pattern);
        }
    }

    /**
     * 根据模式清除缓存
     */
    private function clearCacheByPattern($pattern)
    {
        // 注意：这个方法在生产环境中可能需要根据缓存驱动进行调整
        // Redis 支持模式匹配，但文件缓存不支持
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // 对于文件缓存，我们只能清除所有缓存
                Cache::flush();
            }
        } catch (\Exception $e) {
            // 记录错误但不抛出异常
            \Log::warning('清除缓存失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取缓存统计信息
     */
    public function getCacheStats()
    {
        $user = Auth::user();
        $userKey = $user ? "{$user->id}_{$user->user_type}_{$user->organization_id}" : 'guest';
        
        $cacheKeys = [
            'dashboard' => "experiment_dashboard_{$userKey}_*",
            'calendar' => "experiment_calendar_{$userKey}_*",
            'stats' => "experiment_stats_{$userKey}_*",
            'alerts' => "experiment_alerts_{$userKey}_*",
        ];

        $stats = [];
        
        foreach ($cacheKeys as $type => $pattern) {
            $stats[$type] = [
                'pattern' => $pattern,
                'ttl' => self::CACHE_TTL[$type],
                'exists' => $this->checkCacheExists($pattern)
            ];
        }

        return $stats;
    }

    /**
     * 检查缓存是否存在
     */
    private function checkCacheExists($pattern)
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                return count($keys) > 0;
            }
            return false; // 文件缓存无法精确检查
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 预热缓存
     */
    public function warmupCache()
    {
        $user = Auth::user();
        if (!$user) return;

        try {
            // 预热监控看板数据
            $this->getDashboardData('month', function() {
                // 这里可以调用实际的数据获取逻辑
                return ['warmed_up' => true, 'timestamp' => now()];
            });

            // 预热日历数据
            $start = now()->startOfMonth()->toDateString();
            $end = now()->endOfMonth()->toDateString();
            $this->getCalendarData($start, $end, function() {
                return ['warmed_up' => true, 'timestamp' => now()];
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('缓存预热失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 缓存健康检查
     */
    public function healthCheck()
    {
        try {
            $testKey = 'experiment_cache_health_check';
            $testValue = ['timestamp' => now(), 'test' => true];
            
            // 写入测试
            Cache::put($testKey, $testValue, 1);
            
            // 读取测试
            $retrieved = Cache::get($testKey);
            
            // 删除测试
            Cache::forget($testKey);
            
            return [
                'status' => 'healthy',
                'driver' => config('cache.default'),
                'write_test' => true,
                'read_test' => $retrieved !== null,
                'delete_test' => Cache::get($testKey) === null
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'driver' => config('cache.default')
            ];
        }
    }
}
