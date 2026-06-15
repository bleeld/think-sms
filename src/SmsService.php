<?php
declare(strict_types=1);

namespace bleeld\sms;

use bleeld\sms\Exception\ConfigException;

/**
 * 短信服务类
 * 提供统一的短信发送接口
 */
class SmsService
{
    /**
     * 配置数据
     */
    protected static array $config = [];

    /**
     * 默认驱动名称
     */
    protected static string $defaultDriver = '';

    /**
     * 初始化服务
     */
    public static function init(array $config = []): void
    {
        if (empty($config)) {
            // 尝试从ThinkPHP配置加载
            if (function_exists('config')) {
                $config = config('sms', []);
            }
        }

        if (empty($config)) {
            throw new ConfigException('短信配置不能为空');
        }

        self::$config = $config;
        self::$defaultDriver = $config['default'] ?? 'aliyun';
    }

    /**
     * 获取配置
     */
    public static function getConfig(): array
    {
        return self::$config;
    }

    /**
     * 设置配置
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
        self::$defaultDriver = $config['default'] ?? 'aliyun';
        
        // 清除驱动缓存
        DriverFactory::clearCache();
    }

    /**
     * 获取默认驱动实例
     */
    public static function driver(?string $name = null): DriverInterface
    {
        if (empty(self::$config)) {
            self::init();
        }

        $driverName = $name ?? self::$defaultDriver;
        $driverConfig = self::$config[$driverName] ?? [];

        if (empty($driverConfig)) {
            throw new ConfigException("短信驱动 [{$driverName}] 配置不存在");
        }

        return DriverFactory::make($driverName, $driverConfig);
    }

    /**
     * 快速发送短信（使用默认驱动）
     */
    public static function send(string $phone, string $templateId, array $params = [], ?string $driver = null): array
    {
        return self::driver($driver)->send($phone, $templateId, $params);
    }

    /**
     * 批量发送短信（使用默认驱动）
     */
    public static function sendBatch(array $messages, ?string $driver = null): array
    {
        return self::driver($driver)->sendBatch($messages);
    }

    /**
     * 注册新驱动
     */
    public static function registerDriver(string $name, string $class): void
    {
        DriverFactory::register($name, $class);
    }

    /**
     * 检查驱动是否已注册
     */
    public static function hasDriver(string $name): bool
    {
        return DriverFactory::has($name);
    }

    /**
     * 获取所有已注册的驱动
     */
    public static function getDrivers(): array
    {
        return DriverFactory::getDrivers();
    }

    /**
     * 切换默认驱动
     */
    public static function use(string $driverName): self
    {
        self::$defaultDriver = $driverName;
        return new self();
    }

    /**
     * 魔术方法：动态调用驱动方法
     */
    public function __call(string $method, array $arguments)
    {
        $driver = self::driver();
        
        if (method_exists($driver, $method)) {
            return call_user_func_array([$driver, $method], $arguments);
        }

        throw new \BadMethodCallException("方法 {$method} 不存在");
    }

    /**
     * 静态魔术方法
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $instance = new self();
        return $instance->__call($method, $arguments);
    }
}
