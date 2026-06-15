<?php
/**
 * 签名算法测试
 */

// 使用项目根目录的 autoload
require_once __DIR__ . '/../../../autoload.php';

use bleeld\sms\drivers\AliyunDriver;
use bleeld\sms\drivers\QcloudDriver;

echo "=== 签名算法测试 ===\n\n";

// 测试1：阿里云 HMAC-SHA1 签名
echo "测试1：阿里云 HMAC-SHA1 签名算法\n";

$aliyunDriver = new AliyunDriver();
$aliyunDriver->setConfig([
    'access_key_id' => 'test_access_key_id',
    'access_key_secret' => 'test_access_key_secret',
    'sign_name' => '测试签名',
]);

// 使用反射调用私有方法
$reflection = new ReflectionClass($aliyunDriver);

// 测试 percentEncode 方法
$percentEncodeMethod = $reflection->getMethod('percentEncode');
$percentEncodeMethod->setAccessible(true);

$testStrings = [
    'Hello World' => 'Hello%20World',
    'test+value' => 'test%2Bvalue',
    'a*b' => 'a%2Ab',
    'a~b' => 'a~b',
];

echo "\nURL编码测试:\n";
foreach ($testStrings as $input => $expected) {
    $result = $percentEncodeMethod->invoke($aliyunDriver, $input);
    if ($result === $expected) {
        echo "  ✓ '{$input}' -> '{$result}'\n";
    } else {
        echo "  ✗ '{$input}' -> '{$result}' (期望: '{$expected}')\n";
    }
}

// 测试 generateSignature 方法
$generateSignatureMethod = $reflection->getMethod('generateSignature');
$generateSignatureMethod->setAccessible(true);

$params = [
    'AccessKeyId' => 'test_key',
    'Action' => 'SendSms',
    'Format' => 'JSON',
    'Version' => '2017-05-25',
    'SignatureMethod' => 'HMAC-SHA1',
    'SignatureVersion' => '1.0',
    'SignatureNonce' => 'test_nonce',
    'Timestamp' => '2026-05-23T00:00:00Z',
];

try {
    $signature = $generateSignatureMethod->invoke($aliyunDriver, $params, 'test_secret&');
    echo "\n✓ 成功生成阿里云签名\n";
    echo "  签名长度: " . strlen($signature) . " 字符\n";
    echo "  签名字符: " . substr($signature, 0, 20) . "...\n";
} catch (\Exception $e) {
    echo "\n✗ 生成签名失败: " . $e->getMessage() . "\n";
}

// 测试2：腾讯云 TC3-HMAC-SHA256 签名
echo "\n测试2：腾讯云 TC3-HMAC-SHA256 签名算法\n";

$qcloudDriver = new QcloudDriver();
$qcloudDriver->setConfig([
    'secret_id' => 'test_secret_id',
    'secret_key' => 'test_secret_key',
    'sign_name' => '测试签名',
    'sdk_app_id' => '1400000000',
]);

// 验证配置设置（使用反射访问 protected 方法）
$qcloudReflection = new ReflectionClass($qcloudDriver);
$getConfigMethod = $qcloudReflection->getMethod('getConfig');
$getConfigMethod->setAccessible(true);

if ($getConfigMethod->invoke($qcloudDriver, 'secret_id') === 'test_secret_id') {
    echo "✓ 配置设置成功\n";
} else {
    echo "✗ 配置设置失败\n";
}

echo "\n注意：腾讯云签名算法在实际HTTP请求中测试，这里只验证配置功能\n";

// 测试3：手机号验证
echo "\n测试3：手机号验证功能\n";

$validatePhoneMethod = $reflection->getMethod('validatePhone');
$validatePhoneMethod->setAccessible(true);

$phones = [
    '13800138000' => true,
    '13900139000' => true,
    '18800188000' => true,
    '12345678901' => false,
    '1380013800' => false,
    '138001380001' => false,
    'abc12345678' => false,
];

foreach ($phones as $phone => $expected) {
    $result = $validatePhoneMethod->invoke($aliyunDriver, $phone);
    if ($result === $expected) {
        echo "  ✓ '{$phone}' -> " . ($result ? '有效' : '无效') . "\n";
    } else {
        echo "  ✗ '{$phone}' -> " . ($result ? '有效' : '无效') . " (期望: " . ($expected ? '有效' : '无效') . ")\n";
    }
}

// 测试4：模板参数构建
echo "\n测试4：模板参数构建\n";

$buildTemplateParamsMethod = $reflection->getMethod('buildTemplateParams');
$buildTemplateParamsMethod->setAccessible(true);

$testCases = [
    ['input' => [], 'expected' => '{}'],
    ['input' => ['code' => '1234'], 'expected' => '{"code":"1234"}'],
    ['input' => ['name' => '张三', 'code' => '5678'], 'expected' => '{"name":"张三","code":"5678"}'],
];

foreach ($testCases as $case) {
    $result = $buildTemplateParamsMethod->invoke($aliyunDriver, $case['input']);
    // JSON可能有不同的键顺序，所以比较解码后的结果
    $resultDecoded = json_decode($result, true);
    $expectedDecoded = json_decode($case['expected'], true);
    
    if ($resultDecoded == $expectedDecoded) {
        echo "  ✓ " . json_encode($case['input'], JSON_UNESCAPED_UNICODE) . " -> {$result}\n";
    } else {
        echo "  ✗ 参数构建失败\n";
    }
}

echo "\n=== 所有测试完成 ===\n";
