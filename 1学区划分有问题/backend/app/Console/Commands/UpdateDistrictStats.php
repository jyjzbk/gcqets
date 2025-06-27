<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DistrictBoundary;

class UpdateDistrictStats extends Command
{
    protected $signature = 'stats:update';
    protected $description = '更新学区统计信息';

    public function handle()
    {
        DistrictBoundary::all()->each->updateStatistics();
        $this->info('学区统计已更新');
    }
}