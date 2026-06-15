<?php
/**
 * 短信服务测试
 */

// 使用项目根目录的 autoload
require_once __DIR__ . '/../../../autoload.php';

use bleeld\sms\SmsService;
use bleeld\sms\DriverInterface;

echo "=== 短信服务测试 ===\n\n";

// 初始化配置
$config = [
    'default' => 'aliyun',
    'aliyun' => [
        'access_key_id' => 'test_key',
        'access_key_secret' => 'test_secret',
        'sign_name' => '测试签名',
    ],
    'qcloud' => [
        'secret_id' => 'test_id',
        'secret_key' => 'test_key',
        'sign_name' => '测试签名',
        'sdk_app_id' => '1400000000',
    ],
];

SmsService::setConfig($config);

// 测试1：检查配置
echo "测试1：检查配置\n";
$savedConfig = SmsService::getConfig();
if (!empty($savedConfig)) {
    echo "✓ 配置已成功设置\n";
    echo "  默认驱动: {$savedConfig['default']}\n";
} else {
    echo "✗ 配置为空\n";
}

// 测试2：获取默认驱动
echo "\n测试2：获取默认驱动\n";
try {
    $driver = SmsService::driver();
    if ($driver instanceof DriverInterface) {
        echo "✓ 成功获取默认驱动\n";
        echo "  驱动名称: {$driver->getName()}\n";
    } else {
        echo "✗ 返回的不是有效的驱动实例\n";
    }
} catch (\Exception $e) {
    echo "✗ 获取驱动失败: " . $e->getMessage() . "\n";
}

// 测试3：指定驱动名称
echo "\n测试3：指定驱动名称\n";
try {
    $aliyunDriver = SmsService::driver('aliyun');
    echo "✓ 成功获取 aliyun 驱动\n";
    
    $qcloudDriver = SmsService::driver('qcloud');
    echo "✓ 成功获取 qcloud 驱动\n";
} catch (\Exception $e) {
    echo "✗ 获取指定驱动失败: " . $e->getMessage() . "\n";
}

// 测试4：检查驱动是否已注册
echo "\n测试4：检查驱动是否已注册\n";
if (SmsService::hasDriver('aliyun')) {
    echo "✓ aliyun 驱动已注册\n";
} else {
    echo "✗ aliyun 驱动未注册\n";
}

if (SmsService::hasDriver('qcloud')) {
    echo "✓ qcloud 驱动已注册\n";
} else {
    echo "✗ qcloud 驱动未注册\n";
}

if (!SmsService::hasDriver('unknown')) {
    echo "✓ unknown 驱动正确判断为未注册\n";
} else {
    echo "✗ unknown 驱动判断错误\n";
}

// 测试5：获取所有驱动列表
echo "\n测试5：获取所有驱动列表\n";
$drivers = SmsService::getDrivers();
echo "已注册的驱动: " . implode(', ', $drivers) . "\n";

if (in_array('aliyun', $drivers) && in_array('qcloud', $drivers)) {
    echo "✓ 驱动列表包含 aliyun 和 qcloud\n";
} else {
    echo "✗ 驱动列表不完整\n";
}

// 测试6：切换默认驱动
echo "\n测试6：切换默认驱动\n";
SmsService::use('qcloud');
$qcloudDriver = SmsService::driver();
if ($qcloudDriver->getName() === 'qcloud') {
    echo "✓ 成功切换到 qcloud 驱动\n";
} else {
    echo "✗ 切换驱动失败\n";
}

// 测试7：动态注册新驱动
echo "\n测试7：动态注册新驱动\n";

class CustomDriver implements DriverInterface
{
    protected array $config = [];
    
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
    
    public function send(string $phone, string $templateId, array $params = []): array
    {
        return ['code' => 1, 'msg' => 'Custom发送'];
    }
    
    public function sendBatch(array $messages): array
    {
        return ['code' => 1, 'msg' => 'Custom批量发送'];
    }
    
    public function getName(): string
    {
        return 'custom';
    }
}

try {
    // 创建新的配置，包含 custom 驱动
    $newConfig = [
        'default' => 'aliyun',
        'aliyun' => [
            'access_key_id' => 'test_key',
            'access_key_secret' => 'test_secret',
            'sign_name' => '测试签名',
        ],
        'qcloud' => [
            'secret_id' => 'test_id',
            'secret_key' => 'test_key',
            'sign_name' => '测试签名',
            'sdk_app_id' => '1400000000',
        ],
        'custom' => [], // custom 驱动配置
    ];
    
    SmsService::setConfig($newConfig);
    SmsService::registerDriver('custom', CustomDriver::class);
    echo "✓ 成功注册 custom 驱动\n";
    
    if (SmsService::hasDriver('custom')) {
        echo "✓ custom 驱动已存在于系统中\n";
    }
    
    $customDriver = SmsService::driver('custom');
    echo "✓ 成功获取 custom 驱动\n";
    echo "  驱动名称: {$customDriver->getName()}\n";
} catch (\Exception $e) {
    echo "✗ 注册驱动失败: " . $e->getMessage() . "\n";
}

// 测试8：测试静态方法调用
echo "\n测试8：测试静态方法调用（不实际发送）\n";
echo "注意：以下测试不会真正发送短信，仅测试接口调用\n";

// 由于没有真实配置，这里只测试方法是否存在
if (method_exists(SmsService::class, 'send')) {
    echo "✓ SmsService::send() 方法存在\n";
} else {
    echo "✗ SmsService::send() 方法不存在\n";
}

if (method_exists(SmsService::class, 'sendBatch')) {
    echo "✓ SmsService::sendBatch() 方法存在\n";
} else {
    echo "✗ SmsService::sendBatch() 方法不存在\n";
}

echo "\n=== 所有测试完成 ===\n";
