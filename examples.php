<?php
/**
 * 短信插件使用示例
 * 
 * 本文件展示如何使用 bleeld/think-sms 短信插件
 */

// 引入自动加载文件
require_once __DIR__ . '/../../autoload.php';

use bleeld\sms\SmsService;
use bleeld\sms\Exception\SmsException;

// ========================================
// 示例1：发送单条短信（使用默认驱动）
// ========================================
function example1()
{
    $result = SmsService::send('13800138000', 'SMS_123456', [
        'code' => '1234',
    ]);

    if ($result['code'] === 1) {
        echo "✓ 发送成功\n";
        print_r($result['data']);
    } else {
        echo "✗ 发送失败: {$result['msg']}\n";
    }
}

// ========================================
// 示例2：指定驱动发送短信
// ========================================
function example2()
{
    // 使用阿里云
    $result = SmsService::driver('aliyun')->send('13800138000', 'SMS_123456', [
        'code' => '1234',
    ]);

    // 使用腾讯云
    $result = SmsService::driver('qcloud')->send('13800138000', '1234567', [
        '1234',
    ]);
}

// ========================================
// 示例3：批量发送短信
// ========================================
function example3()
{
    $messages = [
        [
            'phone' => '13800138000',
            'template_id' => 'SMS_123456',
            'params' => ['code' => '1234'],
        ],
        [
            'phone' => '13900139000',
            'template_id' => 'SMS_123456',
            'params' => ['code' => '5678'],
        ],
    ];

    $result = SmsService::sendBatch($messages);

    if ($result['code'] === 1) {
        echo "✓ 批量发送成功，共 {$result['data']['count']} 条\n";
    } else {
        echo "✗ 批量发送失败: {$result['msg']}\n";
    }
}

// ========================================
// 示例4：动态注册新驱动
// ========================================
function example4()
{
    // 假设你有一个百度云驱动类
    // SmsService::registerDriver('baidu', \YourPackage\BaiduDriver::class);
    
    // 然后就可以使用了
    // $result = SmsService::driver('baidu')->send('13800138000', 'template_id', ['code' => '1234']);
}

// ========================================
// 示例5：切换默认驱动
// ========================================
function example5()
{
    // 临时切换到腾讯云
    SmsService::use('qcloud');
    
    // 现在 send() 会使用腾讯云
    $result = SmsService::send('13800138000', '1234567', ['1234']);
}

// ========================================
// 示例6：获取驱动信息
// ========================================
function example6()
{
    // 检查驱动是否已注册
    if (SmsService::hasDriver('aliyun')) {
        echo "阿里云驱动已注册\n";
    }

    // 获取所有已注册的驱动
    $drivers = SmsService::getDrivers();
    echo "已注册的驱动: " . implode(', ', $drivers) . "\n";
}

// ========================================
// 示例7：异常处理
// ========================================
function example7()
{
    try {
        $result = SmsService::send('13800138000', 'SMS_123', ['code' => '1234']);
        
        if ($result['code'] === 0) {
            throw new SmsException($result['msg']);
        }
        
        echo "发送成功\n";
    } catch (SmsException $e) {
        echo "短信发送失败: " . $e->getMessage() . "\n";
    }
}

// ========================================
// 示例8：在控制器中使用
// ========================================
/*
namespace app\index\controller;

use bleeld\sms\SmsService;
use think\Controller;

class Index extends Controller
{
    public function sendSms()
    {
        $phone = input('post.phone');
        $code = rand(1000, 9999);
        
        // 保存到缓存或数据库
        cache('sms_code_' . $phone, $code, 300); // 5分钟有效期
        
        // 发送短信
        $result = SmsService::send($phone, 'SMS_123456', [
            'code' => (string)$code,
        ]);
        
        if ($result['code'] === 1) {
            return json(['code' => 1, 'msg' => '验证码已发送']);
        } else {
            return json(['code' => 0, 'msg' => $result['msg']]);
        }
    }
}
*/

// ========================================
// 运行示例
// ========================================
echo "=== Think-SMS 使用示例 ===\n\n";

echo "示例1：发送单条短信\n";
// example1();

echo "\n示例2：指定驱动发送\n";
// example2();

echo "\n示例3：批量发送\n";
// example3();

echo "\n示例4：动态注册驱动\n";
// example4();

echo "\n示例5：切换默认驱动\n";
// example5();

echo "\n示例6：获取驱动信息\n";
example6();

echo "\n示例7：异常处理\n";
// example7();

echo "\n=== 示例结束 ===\n";
