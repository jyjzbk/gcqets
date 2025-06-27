<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 查找admin用户
$user = User::where('username', 'admin')->first();

if ($user) {
    $user->password = Hash::make('123456');
    $user->save();
    echo "Password updated for admin user\n";
    echo "Username: admin\n";
    echo "Password: 123456\n";
} else {
    echo "Admin user not found\n";
}

// 显示所有用户
echo "\nAll users:\n";
foreach (User::all() as $user) {
    echo "ID: {$user->id}, Username: {$user->username}, Email: {$user->email}, Status: " . ($user->status ? 'active' : 'inactive') . "\n";
}
