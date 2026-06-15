<?php
/**
 * 短信服务配置文件
 * 
 * 说明：
 * - default: 默认使用的短信驱动名称
 * - 各驱动配置只需填写参数，驱动类由工厂自动映射
 * - 支持阿里云、腾讯云，预留百度云、火山云扩展位
 */

return [
    // 默认驱动
    'default' => 'aliyun',

    // 阿里云短信配置
    'aliyun' => [
        'access_key_id' => '',      // AccessKey ID
        'access_key_secret' => '',  // AccessKey Secret
        'sign_name' => '',          // 短信签名
        'endpoint' => 'dysmsapi.aliyuncs.com',  // API端点
        'region_id' => 'cn-hangzhou',           // 区域ID
    ],

    // 腾讯云短信配置
    'qcloud' => [
        'secret_id' => '',          // SecretId
        'secret_key' => '',         // SecretKey
        'sign_name' => '',          // 短信签名
        'sdk_app_id' => '',         // SDK AppID
        'endpoint' => 'sms.tencentcloudapi.com',  // API端点
        'region' => 'ap-guangzhou', // 区域
    ],

    // 百度云短信配置（预留）
    'baidu' => [
        'access_key' => '',         // Access Key
        'secret_key' => '',         // Secret Key
        'endpoint' => 'smsv3.bj.baidubce.com',  // API端点
    ],

    // 火山云短信配置（预留）
    'volcengine' => [
        'access_key' => '',         // Access Key
        'secret_key' => '',         // Secret Key
        'region' => 'cn-north-1',   // 区域
        'endpoint' => 'sms.volcengineapi.com',  // API端点
    ],

    // 通用配置
    'timeout' => 30,           // 请求超时时间（秒）
    'retry_times' => 0,        // 失败重试次数
    'log_channel' => 'sms',    // 日志通道名称
];
