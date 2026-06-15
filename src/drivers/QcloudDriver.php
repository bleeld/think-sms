<?php
declare(strict_types=1);

namespace bleeld\sms\drivers;

use bleeld\sms\BaseDriver;

/**
 * 腾讯云短信驱动
 */
class QcloudDriver extends BaseDriver
{
    /**
     * 驱动名称
     */
    protected string $name = 'qcloud';

    /**
     * 获取驱动名称
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 发送单条短信
     */
    public function send(string $phone, string $templateId, array $params = []): array
    {
        try {
            // 验证手机号
            if (!$this->validatePhone($phone)) {
                return $this->error('手机号格式不正确');
            }

            // 验证配置
            $secretId = $this->getConfig('secret_id');
            $secretKey = $this->getConfig('secret_key');
            $signName = $this->getConfig('sign_name');
            $sdkAppId = $this->getConfig('sdk_app_id');

            if (empty($secretId) || empty($secretKey) || empty($signName) || empty($sdkAppId)) {
                return $this->error('腾讯云短信配置不完整');
            }

            // 构造请求体
            $body = [
                'PhoneNumberSet' => [$phone],
                'SmsSdkAppId' => $sdkAppId,
                'SignName' => $signName,
                'TemplateId' => $templateId,
                'TemplateParamSet' => array_values($params), // 腾讯云使用数组参数
            ];

            // 端点
            $endpoint = $this->getConfig('endpoint', 'sms.tencentcloudapi.com');
            $service = 'sms';
            $version = '2021-01-11';
            $action = 'SendSms';
            $region = $this->getConfig('region', 'ap-guangzhou');

            $this->log('info', '腾讯云短信发送请求', [
                'phone' => $phone,
                'template_id' => $templateId,
            ]);

            // 发送请求
            $response = $this->tc3HttpRequest(
                $endpoint,
                $body,
                $service,
                $version,
                $action,
                $region,
                $secretId,
                $secretKey
            );

            $result = json_decode($response, true);

            // 判断是否成功
            if (isset($result['Response']['Error'])) {
                $error = $result['Response']['Error'];
                $errorMsg = $error['Message'] ?? '发送失败';
                
                $this->log('error', '腾讯云短信发送失败', [
                    'phone' => $phone,
                    'code' => $error['Code'] ?? '',
                    'message' => $errorMsg,
                ]);

                return $this->error("腾讯云短信发送失败: {$errorMsg}", [
                    'code' => $error['Code'] ?? '',
                ]);
            }

            // 检查发送状态
            $sendStatusSet = $result['Response']['SendStatusSet'] ?? [];
            if (!empty($sendStatusSet) && $sendStatusSet[0]['Code'] === 'Ok') {
                $this->log('info', '腾讯云短信发送成功', [
                    'phone' => $phone,
                    'serial_no' => $sendStatusSet[0]['SerialNo'] ?? '',
                ]);

                return $this->success([
                    'serial_no' => $sendStatusSet[0]['SerialNo'] ?? '',
                    'request_id' => $result['Response']['RequestId'] ?? '',
                ]);
            }

            $errorMsg = $sendStatusSet[0]['Message'] ?? '发送失败';
            $this->log('error', '腾讯云短信发送失败', [
                'phone' => $phone,
                'message' => $errorMsg,
            ]);

            return $this->error("腾讯云短信发送失败: {$errorMsg}");

        } catch (\Exception $e) {
            $this->log('error', '腾讯云短信发送异常', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return $this->error('短信发送异常: ' . $e->getMessage());
        }
    }

    /**
     * 批量发送短信
     */
    public function sendBatch(array $messages): array
    {
        try {
            // 验证配置
            $secretId = $this->getConfig('secret_id');
            $secretKey = $this->getConfig('secret_key');
            $signName = $this->getConfig('sign_name');
            $sdkAppId = $this->getConfig('sdk_app_id');

            if (empty($secretId) || empty($secretKey) || empty($signName) || empty($sdkAppId)) {
                return $this->error('腾讯云短信配置不完整');
            }

            // 收集所有手机号和参数
            $phones = [];
            $templateParams = [];

            foreach ($messages as $index => $msg) {
                $phone = $msg['phone'] ?? '';
                $templateId = $msg['template_id'] ?? '';
                $params = $msg['params'] ?? [];

                if (!$this->validatePhone($phone)) {
                    return $this->error("第 {$index} 条手机号格式不正确: {$phone}");
                }

                $phones[] = $phone;
                $templateParams[] = array_values($params);
            }

            // 构造请求体
            $body = [
                'PhoneNumberSet' => $phones,
                'SmsSdkAppId' => $sdkAppId,
                'SignName' => $signName,
                'TemplateId' => $messages[0]['template_id'] ?? '', // 批量发送使用相同模板
                'TemplateParamSet' => $templateParams,
            ];

            // 端点
            $endpoint = $this->getConfig('endpoint', 'sms.tencentcloudapi.com');
            $service = 'sms';
            $version = '2021-01-11';
            $action = 'SendSms';
            $region = $this->getConfig('region', 'ap-guangzhou');

            $this->log('info', '腾讯云批量短信发送请求', [
                'count' => count($messages),
            ]);

            // 发送请求
            $response = $this->tc3HttpRequest(
                $endpoint,
                $body,
                $service,
                $version,
                $action,
                $region,
                $secretId,
                $secretKey
            );

            $result = json_decode($response, true);

            // 判断是否成功
            if (isset($result['Response']['Error'])) {
                $error = $result['Response']['Error'];
                $errorMsg = $error['Message'] ?? '批量发送失败';
                
                $this->log('error', '腾讯云批量短信发送失败', [
                    'code' => $error['Code'] ?? '',
                    'message' => $errorMsg,
                ]);

                return $this->error("腾讯云批量短信发送失败: {$errorMsg}", [
                    'code' => $error['Code'] ?? '',
                ]);
            }

            $sendStatusSet = $result['Response']['SendStatusSet'] ?? [];
            $successCount = 0;
            foreach ($sendStatusSet as $status) {
                if ($status['Code'] === 'Ok') {
                    $successCount++;
                }
            }

            $this->log('info', '腾讯云批量短信发送完成', [
                'total' => count($messages),
                'success' => $successCount,
            ]);

            return $this->success([
                'total' => count($messages),
                'success' => $successCount,
                'request_id' => $result['Response']['RequestId'] ?? '',
            ]);

        } catch (\Exception $e) {
            $this->log('error', '腾讯云批量短信发送异常', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('短信批量发送异常: ' . $e->getMessage());
        }
    }

    /**
     * 腾讯云TC3-HMAC-SHA256签名请求
     */
    private function tc3HttpRequest(
        string $endpoint,
        array $body,
        string $service,
        string $version,
        string $action,
        string $region,
        string $secretId,
        string $secretKey
    ): string {
        $host = $endpoint;
        $algorithm = 'TC3-HMAC-SHA256';
        $timestamp = time();
        $date = gmdate('Y-m-d', $timestamp);

        // 1. 拼接规范请求串
        $canonicalUri = '/';
        $canonicalQuerystring = '';
        $canonicalHeaders = "content-type:application/json; charset=utf-8\nhost:{$host}\n";
        $signedHeaders = 'content-type;host';
        $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
        $hashedRequestPayload = hash('sha256', $payload);
        
        $canonicalRequest = implode("\n", [
            'POST',
            $canonicalUri,
            $canonicalQuerystring,
            $canonicalHeaders,
            $signedHeaders,
            $hashedRequestPayload,
        ]);

        // 2. 拼接待签名字符串
        $credentialScope = "{$date}/{$service}/tc3_request";
        $hashedCanonicalRequest = hash('sha256', $canonicalRequest);
        
        $stringToSign = implode("\n", [
            $algorithm,
            $timestamp,
            $credentialScope,
            $hashedCanonicalRequest,
        ]);

        // 3. 计算签名
        $secretDate = hash_hmac('sha256', $date, 'TC3' . $secretKey, true);
        $secretService = hash_hmac('sha256', $service, $secretDate, true);
        $secretSigning = hash_hmac('sha256', 'tc3_request', $secretService, true);
        $signature = hash_hmac('sha256', $stringToSign, $secretSigning);

        // 4. 拼接Authorization
        $authorization = sprintf(
            '%s Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            $algorithm,
            $secretId,
            $credentialScope,
            $signedHeaders,
            $signature
        );

        // 5. 构造请求头
        $headers = [
            'Authorization: ' . $authorization,
            'Content-Type: application/json; charset=utf-8',
            'Host: ' . $host,
            'X-TC-Action: ' . $action,
            'X-TC-Timestamp: ' . $timestamp,
            'X-TC-Version: ' . $version,
            'X-TC-Region: ' . $region,
        ];

        // 6. 发送请求
        $url = 'https://' . $host;
        $response = $this->httpJsonRequest($url, $body, $headers);

        return $response;
    }
}
