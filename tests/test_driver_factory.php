<?php
/**
 * 驱动工厂测试
 */

// 使用项目根目录的 autoload
require_once __DIR__ . '/../../../autoload.php';

use bleeld\sms\DriverFactory;
use bleeld\sms\DriverInterface;

echo "=== 驱动工厂测试 ===\n\n";

// 测试1：检查内置驱动是否已注册
echo "测试1：检查内置驱动是否已注册\n";
$drivers = DriverFactory::getDrivers();
echo "已注册的驱动: " . implode(', ', $drivers) . "\n";

if (DriverFactory::has('aliyun')) {
    echo "✓ aliyun 驱动已注册\n";
} else {
    echo "✗ aliyun 驱动未注册\n";
}

if (DriverFactory::has('qcloud')) {
    echo "✓ qcloud 驱动已注册\n";
} else {
    echo "✗ qcloud 驱动未注册\n";
}

// 测试2：创建驱动实例
echo "\n测试2：创建驱动实例\n";
try {
    $aliyunConfig = [
        'access_key_id' => 'test_key',
        'access_key_secret' => 'test_secret',
        'sign_name' => '测试签名',
    ];
    
    $driver = DriverFactory::make('aliyun', $aliyunConfig);
    
    if ($driver instanceof DriverInterface) {
        echo "✓ 成功创建 AliyunDriver 实例\n";
        echo "  驱动名称: {$driver->getName()}\n";
    } else {
        echo "✗ 创建的实例不是 DriverInterface 类型\n";
    }
} catch (\Exception $e) {
    echo "✗ 创建驱动实例失败: " . $e->getMessage() . "\n";
}

// 测试3：测试驱动缓存
echo "\n测试3：测试驱动缓存\n";
$driver1 = DriverFactory::make('aliyun', $aliyunConfig);
$driver2 = DriverFactory::make('aliyun', $aliyunConfig);

if ($driver1 === $driver2) {
    echo "✓ 驱动实例被正确缓存（返回同一实例）\n";
} else {
    echo "✗ 驱动实例未被缓存\n";
}

// 测试4：清除缓存
echo "\n测试4：清除缓存\n";
DriverFactory::clearCache();
$driver3 = DriverFactory::make('aliyun', $aliyunConfig);

if ($driver1 !== $driver3) {
    echo "✓ 缓存已成功清除（创建了新的实例）\n";
} else {
    echo "✗ 缓存清除失败\n";
}

// 测试5：动态注册新驱动
echo "\n测试5：动态注册新驱动\n";

// 创建一个测试驱动类
class TestDriver implements DriverInterface
{
    protected array $config = [];
    
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
    
    public function send(string $phone, string $templateId, array $params = []): array
    {
        return ['code' => 1, 'msg' => '测试发送'];
    }
    
    public function sendBatch(array $messages): array
    {
        return ['code' => 1, 'msg' => '测试批量发送'];
    }
    
    public function getName(): string
    {
        return 'test';
    }
}

try {
    DriverFactory::register('test', TestDriver::class);
    echo "✓ 成功注册 test 驱动\n";
    
    if (DriverFactory::has('test')) {
        echo "✓ test 驱动已存在于注册表中\n";
    }
    
    $testDriver = DriverFactory::make('test', []);
    echo "✓ 成功创建 test 驱动实例\n";
    echo "  驱动名称: {$testDriver->getName()}\n";
} catch (\Exception $e) {
    echo "✗ 注册驱动失败: " . $e->getMessage() . "\n";
}

// 测试6：测试未注册的驱动
echo "\n测试6：测试未注册的驱动\n";
try {
    $unknownDriver = DriverFactory::make('unknown', []);
    echo "✗ 应该抛出异常但没有\n";
} catch (\bleeld\sms\Exception\DriverNotFoundException $e) {
    echo "✓ 正确抛出 DriverNotFoundException\n";
    echo "  错误信息: {$e->getMessage()}\n";
} catch (\Exception $e) {
    echo "✗ 抛出了错误的异常类型: " . get_class($e) . "\n";
}

echo "\n=== 所有测试完成 ===\n";
