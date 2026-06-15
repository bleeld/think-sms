# Think-SMS 测试套件

本目录包含 Think-SMS 短信插件的完整测试套件。

## 📁 测试文件说明

### 1. test_driver_interface.php
**驱动接口测试**

测试内容：
- ✓ 验证驱动类是否实现 DriverInterface 接口
- ✓ 检查必需方法是否存在（setConfig, send, sendBatch, getName）
- ✓ 验证 getName() 返回值
- ✓ 测试 setConfig() 功能

运行方式：
```bash
php tests/test_driver_interface.php
```

### 2. test_driver_factory.php
**驱动工厂测试**

测试内容：
- ✓ 检查内置驱动是否已注册
- ✓ 创建驱动实例
- ✓ 测试驱动缓存机制
- ✓ 清除缓存功能
- ✓ 动态注册新驱动
- ✓ 未注册驱动的异常处理

运行方式：
```bash
php tests/test_driver_factory.php
```

### 3. test_sms_service.php
**短信服务测试**

测试内容：
- ✓ 配置设置和获取
- ✓ 获取默认驱动
- ✓ 指定驱动名称
- ✓ 检查驱动注册状态
- ✓ 获取驱动列表
- ✓ 切换默认驱动
- ✓ 动态注册新驱动
- ✓ 静态方法调用

运行方式：
```bash
php tests/test_sms_service.php
```

### 4. test_signature.php
**签名算法测试**

测试内容：
- ✓ 阿里云 HMAC-SHA1 签名算法
- ✓ URL编码功能（percentEncode）
- ✓ 腾讯云 TC3-HMAC-SHA256 签名配置
- ✓ 手机号格式验证
- ✓ 模板参数构建

运行方式：
```bash
php tests/test_signature.php
```

### 5. run_all_tests.php
**运行所有测试**

自动运行上述所有测试并生成总结报告。

运行方式：
```bash
php tests/run_all_tests.php
```

## 🚀 快速开始

### 运行单个测试

```bash
# 进入插件目录
cd vendor/bleeld/think-sms

# 运行特定测试
php tests/test_driver_interface.php
```

### 运行所有测试

```bash
# 进入插件目录
cd vendor/bleeld/think-sms

# 运行所有测试
php tests/run_all_tests.php
```

## 📊 测试输出示例

```
╔═══════════════════════════════════════════════════════════╗
║           Think-SMS 插件测试套件                         ║
╚═══════════════════════════════════════════════════════════╝

============================================================
运行测试: 驱动接口测试
============================================================
=== 驱动接口测试 ===

测试1：检查驱动是否实现 DriverInterface
✓ AliyunDriver 实现了 DriverInterface
✓ QcloudDriver 实现了 DriverInterface

...

============================================================
测试总结
============================================================
总测试数: 4
通过: 4
失败: 0

✓ 所有测试通过！
```

## ✅ 测试覆盖范围

### 核心功能
- [x] 驱动接口实现
- [x] 驱动工厂模式
- [x] 短信服务API
- [x] 签名算法
- [x] 配置管理
- [x] 异常处理

### 辅助功能
- [x] 手机号验证
- [x] 模板参数构建
- [x] URL编码
- [x] 驱动缓存
- [x] 动态注册

### 边界情况
- [x] 未注册驱动的异常
- [x] 无效手机号格式
- [x] 空配置处理
- [x] 缓存清除

## 🔧 添加新测试

如需添加新的测试，请遵循以下规范：

1. **命名规范**：`test_功能名称.php`
2. **文件头注释**：说明测试内容
3. **引入自动加载**：`require_once __DIR__ . '/../vendor/autoload.php';`
4. **清晰的输出**：使用 `✓` 和 `✗` 标记测试结果
5. **分组测试**：按功能模块分组，每组有明确的标题

示例：

```php
<?php
/**
 * 新功能测试
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== 新功能测试 ===\n\n";

// 测试1：功能描述
echo "测试1：功能描述\n";
// 测试代码...
if ($result) {
    echo "✓ 测试通过\n";
} else {
    echo "✗ 测试失败\n";
}

echo "\n=== 所有测试完成 ===\n";
```

然后在 `run_all_tests.php` 中添加：

```php
$tests = [
    // ... 现有测试
    '新功能测试' => __DIR__ . '/test_new_feature.php',
];
```

## ⚠️ 注意事项

1. **测试环境**：这些测试不需要真实的短信服务商配置
2. **网络请求**：大部分测试不涉及实际的网络请求
3. **反射使用**：部分测试使用反射访问私有方法，仅用于测试目的
4. **独立运行**：每个测试文件都可以独立运行
5. **无副作用**：测试不会修改系统状态或配置文件

## 🐛 问题排查

如果测试失败：

1. **检查自动加载**：确保 `vendor/autoload.php` 存在
2. **检查PHP版本**：需要 PHP 8.0+
3. **检查依赖**：确保 curl 和 json 扩展已启用
4. **查看详细错误**：查看具体的错误信息定位问题

## 📝 持续集成

可以将测试添加到 CI/CD 流程中：

```yaml
# GitHub Actions 示例
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php vendor/bleeld/think-sms/tests/run_all_tests.php
```

## 📖 更多信息

- 主文档：[README.md](../README.md)
- 快速开始：[QUICK_START.md](../QUICK_START.md)
- 开发总结：[开发完成总结.md](../开发完成总结.md)

---

**祝测试顺利！** 🎉
