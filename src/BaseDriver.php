<?php
declare(strict_types=1);

namespace bleeld\sms;

/**
 * 短信驱动基础抽象类
 * 提供所有驱动共用的功能
 */
abstract class BaseDriver implements DriverInterface
{
    /**
     * 驱动配置
     */
    protected array $config = [];

    /**
     * 驱动名称
     */
    protected string $name = '';

    /**
     * 设置配置
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * 获取配置项
     */
    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * HTTP请求封装
     * 
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @param string $method 请求方法 GET/POST
     * @param array $headers 请求头
     * @return string 响应内容
     * @throws \Exception
     */
    protected function httpRequest(
        string $url, 
        array $params = [], 
        string $method = 'POST', 
        array $headers = []
    ): string {
        $ch = curl_init();
        
        $timeout = $this->getConfig('timeout', 30);
        
        if (strtoupper($method) === 'GET') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $this->buildHeaders($headers),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false || $httpCode >= 400) {
            throw new \Exception("HTTP请求失败: {$error} (HTTP {$httpCode})");
        }
        
        return $response;
    }

    /**
     * 发送JSON格式的HTTP请求
     */
    protected function httpJsonRequest(
        string $url, 
        array $data = [], 
        array $headers = []
    ): string {
        $ch = curl_init();
        
        $timeout = $this->getConfig('timeout', 30);
        $jsonBody = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        $defaultHeaders = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonBody),
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => $this->buildHeaders(array_merge($defaultHeaders, $headers)),
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false || $httpCode >= 400) {
            throw new \Exception("HTTP请求失败: {$error} (HTTP {$httpCode})");
        }
        
        return $response;
    }

    /**
     * 构建请求头
     */
    protected function buildHeaders(array $headers): array
    {
        $defaultHeaders = [
            'User-Agent: Think-SMS/1.0',
            'Accept: application/json',
        ];
        
        return array_merge($defaultHeaders, $headers);
    }

    /**
     * 验证手机号格式
     */
    protected function validatePhone(string $phone): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $phone) === 1;
    }

    /**
     * 格式化模板参数为JSON字符串
     */
    protected function buildTemplateParams(array $params): string
    {
        if (empty($params)) {
            return '{}';
        }
        
        return json_encode($params, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 日志记录
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if (class_exists('\think\facade\Log')) {
            \think\facade\Log::record($message, $level, $context);
        }
    }

    /**
     * 构建成功响应
     */
    protected function success(array $data = []): array
    {
        return [
            'code' => 1,
            'msg' => '发送成功',
            'data' => $data,
        ];
    }

    /**
     * 构建失败响应
     */
    protected function error(string $msg, array $data = []): array
    {
        return [
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
        ];
    }
}
