<?php
/**
 * 驱动接口测试
 */

// 使用项目根目录的 autoload
require_once __DIR__ . '/../../../autoload.php';

use bleeld\sms\DriverInterface;
use bleeld\sms\drivers\AliyunDriver;
use bleeld\sms\drivers\QcloudDriver;

echo "=== 驱动接口测试 ===\n\n";

// 测试1：检查驱动是否实现接口
echo "测试1：检查驱动是否实现 DriverInterface\n";
$aliyunDriver = new AliyunDriver();
$qcloudDriver = new QcloudDriver();

if ($aliyunDriver instanceof DriverInterface) {
    echo "✓ AliyunDriver 实现了 DriverInterface\n";
} else {
    echo "✗ AliyunDriver 未实现 DriverInterface\n";
}

if ($qcloudDriver instanceof DriverInterface) {
    echo "✓ QcloudDriver 实现了 DriverInterface\n";
} else {
    echo "✗ QcloudDriver 未实现 DriverInterface\n";
}

// 测试2：检查驱动方法是否存在
echo "\n测试2：检查驱动方法是否存在\n";
$requiredMethods = ['setConfig', 'send', 'sendBatch', 'getName'];

foreach ([$aliyunDriver, $qcloudDriver] as $driver) {
    $driverName = $driver->getName();
    echo "\n{$driverName}:\n";
    
    foreach ($requiredMethods as $method) {
        if (method_exists($driver, $method)) {
            echo "  ✓ {$method}() 方法存在\n";
        } else {
            echo "  ✗ {$method}() 方法不存在\n";
        }
    }
}

// 测试3：检查 getName() 返回值
echo "\n测试3：检查 getName() 返回值\n";
echo "AliyunDriver::getName() = '{$aliyunDriver->getName()}'\n";
echo "QcloudDriver::getName() = '{$qcloudDriver->getName()}'\n";

// 测试4：检查 setConfig() 功能
echo "\n测试4：检查 setConfig() 功能\n";
$testConfig = [
    'access_key_id' => 'test_key',
    'access_key_secret' => 'test_secret',
    'sign_name' => '测试签名',
];

$aliyunDriver->setConfig($testConfig);
echo "✓ setConfig() 调用成功\n";

echo "\n=== 所有测试完成 ===\n";
