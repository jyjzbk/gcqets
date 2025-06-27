<?php

require_once 'backend/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

try {
    // 创建Laravel应用实例
    $app = require_once 'backend/bootstrap/app.php';
    
    // 启动应用
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // 创建一个简单的请求
    $request = Request::create('/api/health', 'GET');
    
    // 处理请求
    $response = $kernel->handle($request);
    
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
    
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
