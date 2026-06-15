<?php
declare(strict_types=1);

namespace bleeld\sms;

/**
 * 短信驱动接口
 * 所有短信服务商驱动必须实现此接口
 */
interface DriverInterface
{
    /**
     * 设置配置
     * 
     * @param array $config 驱动配置
     * @return void
     */
    public function setConfig(array $config): void;

    /**
     * 发送单条短信
     * 
     * @param string $phone 手机号
     * @param string $templateId 模板ID
     * @param array $params 模板参数
     * @return array ['code' => 1/0, 'msg' => '消息', 'data' => [...]]
     */
    public function send(string $phone, string $templateId, array $params = []): array;

    /**
     * 批量发送短信
     * 
     * @param array $messages 消息数组 [['phone' => '', 'template_id' => '', 'params' => []], ...]
     * @return array ['code' => 1/0, 'msg' => '消息', 'data' => [...]]
     */
    public function sendBatch(array $messages): array;

    /**
     * 获取驱动名称
     * 
     * @return string
     */
    public function getName(): string;
}
