<?php
declare(strict_types=1);

namespace bleeld\sms;

use think\Service as ThinkService;

/**
 * ThinkPHP 服务提供者
 */
class Service extends ThinkService
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册短信服务单例
        $this->app->bind('sms', SmsService::class);
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 自动加载配置
        $configFile = __DIR__ . '/../config/sms.php';
        
        if (file_exists($configFile)) {
            $config = include $configFile;
            
            // 合并到ThinkPHP配置
            if (function_exists('config')) {
                config($config, 'sms');
            }
        }
    }
}
