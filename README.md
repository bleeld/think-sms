# Think-SMS 短信服务插件

一个轻量级的 ThinkPHP 8 短信服务插件，支持多厂商短信服务，零依赖大厂SDK。

## 特性

- ✅ **零依赖**：不引用大厂完整SDK包，只提取核心功能
- ✅ **多厂商支持**：阿里云、腾讯云（当前），百度云、火山云（扩展中）
- ✅ **易于扩展**：策略模式 + 工厂模式设计，轻松添加新服务商
- ✅ **配置简洁**：只需配置参数，驱动类自动映射
- ✅ **统一接口**：所有驱动使用相同的API接口
- ✅ **批量发送**：支持单条和批量短信发送
- ✅ **日志记录**：完整的请求日志记录

## 安装

### 方式一：本地开发（推荐）

1. 将插件放在 `vendor/bleeld/think-sms` 目录

2. 在主项目的 `composer.json` 中添加：

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./vendor/bleeld/think-sms",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "bleeld/think-sms": "@dev"
    }
}
```

3. 执行安装：

```bash
composer update bleeld/think-sms
```

### 方式二：直接复制

直接将 `vendor/bleeld/think-sms` 目录复制到项目中即可使用。

## 配置

### 1. 配置文件位置

- 插件默认配置：`vendor/bleeld/think-sms/config/sms.php`
- 项目自定义配置：`config/sms.php`（优先级更高）

### 2. 配置示例

```php
return [
    // 默认驱动
    'default' => 'aliyun',

    // 阿里云配置
    'aliyun' => [
        'access_key_id' => 'your_access_key_id',
        'access_key_secret' => 'your_access_key_secret',
        'sign_name' => '您的签名',
        'endpoint' => 'dysmsapi.aliyuncs.com',
        'region_id' => 'cn-hangzhou',
    ],

    // 腾讯云配置
    'qcloud' => [
        'secret_id' => 'your_secret_id',
        'secret_key' => 'your_secret_key',
        'sign_name' => '您的签名',
        'sdk_app_id' => '1400xxxxxx',
        'endpoint' => 'sms.tencentcloudapi.com',
        'region' => 'ap-guangzhou',
    ],

    // 通用配置
    'timeout' => 30,
    'retry_times' => 0,
];
```

## 使用方法

### 基础用法

```php
use bleeld\sms\SmsService;

// 发送单条短信（使用默认驱动）
$result = SmsService::send('13800138000', 'SMS_123456', [
    'code' => '1234',
]);

if ($result['code'] === 1) {
    echo '发送成功';
} else {
    echo '发送失败: ' . $result['msg'];
}
```

### 指定驱动

```php
// 使用阿里云
$result = SmsService::driver('aliyun')->send('13800138000', 'SMS_123456', [
    'code' => '1234',
]);

// 使用腾讯云
$result = SmsService::driver('qcloud')->send('13800138000', '1234567', [
    '1234',
]);
```

### 批量发送

```php
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
```

### 动态注册新驱动

```php
use bleeld\sms\SmsService;
use YourPackage\BaiduDriver;

// 注册百度云驱动
SmsService::registerDriver('baidu', BaiduDriver::class);

// 使用
$result = SmsService::driver('baidu')->send('13800138000', 'template_id', [
    'code' => '1234',
]);
```

### 切换默认驱动

```php
// 临时切换到腾讯云
SmsService::use('qcloud');

// 发送短信
$result = SmsService::send('13800138000', '1234567', ['1234']);
```

## 响应格式

所有方法返回统一的数组格式：

```php
[
    'code' => 1,          // 1=成功, 0=失败
    'msg' => '发送成功',   // 提示信息
    'data' => [           // 附加数据
        'biz_id' => 'xxx',
        'request_id' => 'xxx',
    ],
]
```

## 扩展新驱动

### 步骤1：创建驱动类

```php
namespace YourPackage;

use bleeld\sms\BaseDriver;

class BaiduDriver extends BaseDriver
{
    protected string $name = 'baidu';

    public function getName(): string
    {
        return $this->name;
    }

    public function send(string $phone, string $templateId, array $params = []): array
    {
        // 实现百度云短信发送逻辑
        // ...
    }

    public function sendBatch(array $messages): array
    {
        // 实现批量发送逻辑
        // ...
    }
}
```

### 步骤2：注册驱动

```php
SmsService::registerDriver('baidu', \YourPackage\BaiduDriver::class);
```

### 步骤3：添加配置

在 `config/sms.php` 中添加：

```php
'baidu' => [
    'access_key' => 'your_key',
    'secret_key' => 'your_secret',
    // 其他配置...
],
```

### 步骤4：使用

```php
$result = SmsService::driver('baidu')->send('13800138000', 'template_id', [
    'code' => '1234',
]);
```

## 异常处理

插件提供以下异常类：

- `SmsException` - 基础异常
- `DriverNotFoundException` - 驱动未找到
- `ConfigException` - 配置错误
- `SendFailedException` - 发送失败

```php
use bleeld\sms\Exception\SmsException;

try {
    $result = SmsService::send('13800138000', 'SMS_123', ['code' => '1234']);
} catch (SmsException $e) {
    echo '短信发送失败: ' . $e->getMessage();
}
```

## 日志记录

插件会自动记录短信发送日志到 ThinkPHP 日志系统：

```php
// 查看日志文件
runtime/log/202605/23.log
```

## 注意事项

1. **手机号验证**：插件会验证中国大陆手机号格式（1开头，11位数字）
2. **模板参数**：不同厂商的参数格式可能不同，请参考各自文档
3. **签名管理**：短信签名需要在各厂商平台预先申请
4. **频率限制**：注意各厂商的发送频率限制，避免被封禁

## 许可证

MIT License

## 支持的服务商

- ✅ 阿里云短信
- ✅ 腾讯云短信
- 🔄 百度云短信（开发中）
- 🔄 火山云短信（开发中）

## 更新日志

### v1.0.0 (2026-05-23)
- 初始版本发布
- 支持阿里云、腾讯云短信
- 提供完整的扩展机制
