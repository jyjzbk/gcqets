<?php

// 首先测试健康检查API
$healthUrl = 'http://localhost/gcqets/backend/public/index.php/api/health';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $healthUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$healthResponse = curl_exec($ch);
$healthHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$healthError = curl_error($ch);
curl_close($ch);

echo "=== HEALTH CHECK ===\n";
echo "HTTP Code: " . $healthHttpCode . "\n";
echo "Error: " . $healthError . "\n";
echo "Response: " . $healthResponse . "\n\n";

// 测试登录API
$loginUrl = 'http://localhost/gcqets/backend/public/index.php/api/auth/login';
$loginData = json_encode([
    'username' => 'sysadmin',
    'password' => '123456'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$loginError = curl_error($ch);
curl_close($ch);

echo "=== LOGIN TEST ===\n";
echo "HTTP Code: " . $loginHttpCode . "\n";
echo "Error: " . $loginError . "\n";
echo "Response: " . $loginResponse . "\n\n";

// 如果登录成功，提取token并测试角色API
if ($loginHttpCode == 200) {
    $loginData = json_decode($loginResponse, true);
    if (isset($loginData['data']['token'])) {
        $token = $loginData['data']['token'];

        // 测试角色API
        $roleUrl = 'http://localhost/gcqets/backend/public/index.php/api/roles/1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $roleUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);

        $roleResponse = curl_exec($ch);
        $roleHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $roleError = curl_error($ch);
        curl_close($ch);

        echo "=== ROLE API TEST ===\n";
        echo "HTTP Code: " . $roleHttpCode . "\n";
        echo "Error: " . $roleError . "\n";
        echo "Response: " . $roleResponse . "\n";
    }
}
