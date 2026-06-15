<?php
declare(strict_types=1);

namespace bleeld\sms;

use bleeld\sms\Exception\DriverNotFoundException;

/**
 * 驱动工厂类
 * 负责驱动的注册、实例化和管理
 */
class DriverFactory
{
    /**
     * 驱动映射（驱动名称 => 驱动类）
     */
    protected static array $driverMap = [
        'aliyun'     => \bleeld\sms\drivers\AliyunDriver::class,
        'qcloud'     => \bleeld\sms\drivers\QcloudDriver::class,
        // 未来扩展
        // 'baidu'      => \bleeld\sms\drivers\BaiduDriver::class,
        // 'volcengine' => \bleeld\sms\drivers\VolcengineDriver::class,
    ];

    /**
     * 驱动实例缓存
     */
    protected static array $instances = [];

    /**
     * 注册驱动
     * 
     * @param string $name 驱动名称
     * @param string $class 驱动类名（必须实现 DriverInterface）
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function register(string $name, string $class): void
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("驱动类 {$class} 不存在");
        }

        if (!is_subclass_of($class, DriverInterface::class)) {
            throw new \InvalidArgumentException("驱动类 {$class} 必须实现 DriverInterface 接口");
        }

        self::$driverMap[$name] = $class;
        
        // 清除缓存实例
        unset(self::$instances[$name]);
    }

    /**
     * 创建驱动实例
     * 
     * @param string $name 驱动名称
     * @param array $config 驱动配置
     * @return DriverInterface
     * @throws DriverNotFoundException
     */
    public static function make(string $name, array $config): DriverInterface
    {
        // 如果已有缓存实例，直接返回
        if (isset(self::$instances[$name])) {
            $driver = self::$instances[$name];
            $driver->setConfig($config);
            return $driver;
        }

        // 检查驱动是否已注册
        if (!isset(self::$driverMap[$name])) {
            throw new DriverNotFoundException("短信驱动 [{$name}] 未注册");
        }

        $class = self::$driverMap[$name];
        
        // 创建实例
        $driver = new $class();
        
        // 设置配置
        $driver->setConfig($config);
        
        // 缓存实例
        self::$instances[$name] = $driver;

        return $driver;
    }

    /**
     * 检查驱动是否已注册
     */
    public static function has(string $name): bool
    {
        return isset(self::$driverMap[$name]);
    }

    /**
     * 获取所有已注册的驱动名称
     */
    public static function getDrivers(): array
    {
        return array_keys(self::$driverMap);
    }

    /**
     * 清除驱动实例缓存
     */
    public static function clearCache(): void
    {
        self::$instances = [];
    }
}
