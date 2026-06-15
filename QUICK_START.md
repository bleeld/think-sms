# Think-SMS 快速开始指南

## 📦 安装完成

短信插件已成功安装到您的项目中！

**位置：** `vendor/bleeld/think-sms`

**版本：** v1.0.0

---

## ⚙️ 配置步骤

### 1. 编辑配置文件

打开 `config/sms.php`，填写您的短信服务商配置：

```php
return [
    // 默认驱动（aliyun 或 qcloud）
    'default' => 'aliyun',

    // 阿里云配置
    'aliyun' => [
        'access_key_id' => 'your_access_key_id',      // 必填
        'access_key_secret' => 'your_access_key_secret', // 必填
        'sign_name' => '您的短信签名',                  // 必填
        'endpoint' => 'dysmsapi.aliyuncs.com',
        'region_id' => 'cn-hangzhou',
    ],

    // 腾讯云配置
    'qcloud' => [
        'secret_id' => 'your_secret_id',              // 必填
        'secret_key' => 'your_secret_key',            // 必填
        'sign_name' => '您的短信签名',                  // 必填
        'sdk_app_id' => '1400xxxxxx',                 // 必填
        'endpoint' => 'sms.tencentcloudapi.com',
        'region' => 'ap-guangzhou',
    ],
];
```

### 2. 获取配置信息

#### 阿里云短信
1. 登录 [阿里云控制台](https://dysms.console.aliyun.com/)
2. 进入"国内消息" → "签名管理"，创建签名
3. 进入"模板管理"，创建短信模板
4. 在 AccessKey 管理中获取 AccessKey ID 和 Secret

#### 腾讯云短信
1. 登录 [腾讯云控制台](https://console.cloud.tencent.com/smsv2)
2. 创建应用，获取 SDK AppID
3. 创建签名和模板
4. 在 API 密钥管理中获取 SecretId 和 SecretKey

---

## 🚀 快速使用

### 基础用法

```php
use bleeld\sms\SmsService;

// 发送验证码
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
SmsService::driver('aliyun')->send('13800138000', 'SMS_123456', ['code' => '1234']);

// 使用腾讯云
SmsService::driver('qcloud')->send('13800138000', '1234567', ['1234']);
```

### 批量发送

```php
$messages = [
    ['phone' => '13800138000', 'template_id' => 'SMS_123', 'params' => ['code' => '1234']],
    ['phone' => '13900139000', 'template_id' => 'SMS_123', 'params' => ['code' => '5678']],
];

$result = SmsService::sendBatch($messages);
```

---

## 🧪 测试插件

### 方式1：访问测试页面

浏览器访问：`http://你的域名/sms_test.html`

这是一个可视化的测试界面，可以直接发送测试短信。

### 方式2：使用测试控制器

```bash
# 获取驱动列表
curl http://你的域名/index/sms_test/getDrivers

# 发送验证码（POST）
curl -X POST http://你的域名/index/sms_test/sendCode \
  -d "phone=13800138000"
```

### 方式3：运行示例文件

```bash
php vendor/bleeld/think-sms/examples.php
```

---

## 📝 实际应用示例

### 用户注册发送验证码

```php
namespace app\index\controller;

use bleeld\sms\SmsService;
use think\Controller;

class User extends Controller
{
    public function register()
    {
        $phone = input('post.phone');
        
        // 生成验证码
        $code = rand(1000, 9999);
        
        // 保存到缓存（5分钟）
        cache('sms_code_' . $phone, $code, 300);
        
        // 发送短信
        $result = SmsService::send($phone, 'SMS_TEMPLATE_ID', [
            'code' => (string)$code,
        ]);
        
        if ($result['code'] === 1) {
            return json(['code' => 1, 'msg' => '验证码已发送']);
        } else {
            return json(['code' => 0, 'msg' => $result['msg']]);
        }
    }
    
    public function verifyCode()
    {
        $phone = input('post.phone');
        $code = input('post.code');
        
        // 验证验证码
        $savedCode = cache('sms_code_' . $phone);
        
        if ($savedCode && $savedCode == $code) {
            // 验证通过，删除验证码
            cache('sms_code_' . $phone, null);
            
            // 继续注册流程...
            return json(['code' => 1, 'msg' => '验证通过']);
        } else {
            return json(['code' => 0, 'msg' => '验证码错误或已过期']);
        }
    }
}
```

### 订单通知

```php
use bleeld\sms\SmsService;

// 订单支付成功通知
SmsService::send($userPhone, 'ORDER_PAY_TEMPLATE', [
    'order_no' => $orderNo,
    'amount' => $amount,
    'time' => date('Y-m-d H:i:s'),
]);
```

---

## 🔧 高级功能

### 切换默认驱动

```php
// 临时切换到腾讯云
SmsService::use('qcloud');

// 后续调用将使用腾讯云
SmsService::send('13800138000', '1234567', ['1234']);
```

### 动态注册新驱动

```php
// 注册第三方驱动
SmsService::registerDriver('baidu', \YourPackage\BaiduDriver::class);

// 使用
SmsService::driver('baidu')->send('13800138000', 'template_id', ['code' => '1234']);
```

### 异常处理

```php
use bleeld\sms\Exception\SmsException;

try {
    $result = SmsService::send('13800138000', 'SMS_123', ['code' => '1234']);
    
    if ($result['code'] === 0) {
        throw new SmsException($result['msg']);
    }
    
    echo '发送成功';
} catch (SmsException $e) {
    echo '发送失败: ' . $e->getMessage();
}
```

---

## 📊 响应格式

所有方法返回统一格式：

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

---

## 📖 更多信息

- **完整文档：** 查看 `vendor/bleeld/think-sms/README.md`
- **使用示例：** 查看 `vendor/bleeld/think-sms/examples.php`
- **测试控制器：** `app/index/controller/SmsTest.php`
- **测试页面：** `public/sms_test.html`

---

## ❓ 常见问题

### Q: 如何查看发送日志？
A: 日志保存在 `runtime/log/` 目录下，按日期分文件存储。

### Q: 支持哪些短信服务商？
A: 当前支持阿里云、腾讯云。百度云、火山云正在开发中。

### Q: 如何添加新的短信服务商？
A: 参考 README.md 中的"扩展新驱动"章节，只需实现 DriverInterface 接口即可。

### Q: 手机号验证规则是什么？
A: 中国大陆手机号，1开头，11位数字，第二位为3-9。

---

## 🎉 开始使用

现在您可以开始在项目中使用短信服务了！

如有问题，请查看完整文档或联系技术支持。
