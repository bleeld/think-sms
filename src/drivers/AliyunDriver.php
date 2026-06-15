<?php
declare(strict_types=1);

namespace bleeld\sms\drivers;

use bleeld\sms\BaseDriver;

/**
 * 阿里云短信驱动
 */
class AliyunDriver extends BaseDriver
{
    /**
     * 驱动名称
     */
    protected string $name = 'aliyun';

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
            $accessKeyId = $this->getConfig('access_key_id');
            $accessKeySecret = $this->getConfig('access_key_secret');
            $signName = $this->getConfig('sign_name');

            if (empty($accessKeyId) || empty($accessKeySecret) || empty($signName)) {
                return $this->error('阿里云短信配置不完整');
            }

            // 构造请求参数
            $params = [
                'PhoneNumbers' => $phone,
                'SignName' => $signName,
                'TemplateCode' => $templateId,
                'TemplateParam' => $this->buildTemplateParams($params),
            ];

            // 添加公共参数
            $params = array_merge($params, [
                'AccessKeyId' => $accessKeyId,
                'Action' => 'SendSms',
                'Format' => 'JSON',
                'Version' => '2017-05-25',
                'SignatureMethod' => 'HMAC-SHA1',
                'SignatureVersion' => '1.0',
                'SignatureNonce' => uniqid(),
                'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            ]);

            // 生成签名
            $params['Signature'] = $this->generateSignature($params, $accessKeySecret);

            // 发送请求
            $endpoint = $this->getConfig('endpoint', 'dysmsapi.aliyuncs.com');
            $url = 'https://' . $endpoint . '/';
            
            $this->log('info', '阿里云短信发送请求', [
                'phone' => $phone,
                'template_id' => $templateId,
            ]);

            $response = $this->httpRequest($url, $params, 'GET');
            $result = json_decode($response, true);

            // 判断是否成功
            if (isset($result['Code']) && $result['Code'] === 'OK') {
                $this->log('info', '阿里云短信发送成功', [
                    'phone' => $phone,
                    'biz_id' => $result['BizId'] ?? '',
                ]);

                return $this->success([
                    'biz_id' => $result['BizId'] ?? '',
                    'request_id' => $result['RequestId'] ?? '',
                ]);
            }

            $errorMsg = $result['Message'] ?? '发送失败';
            $this->log('error', '阿里云短信发送失败', [
                'phone' => $phone,
                'code' => $result['Code'] ?? '',
                'message' => $errorMsg,
            ]);

            return $this->error("阿里云短信发送失败: {$errorMsg}", [
                'code' => $result['Code'] ?? '',
            ]);

        } catch (\Exception $e) {
            $this->log('error', '阿里云短信发送异常', [
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
            $accessKeyId = $this->getConfig('access_key_id');
            $accessKeySecret = $this->getConfig('access_key_secret');
            $signName = $this->getConfig('sign_name');

            if (empty($accessKeyId) || empty($accessKeySecret) || empty($signName)) {
                return $this->error('阿里云短信配置不完整');
            }

            // 批量发送使用 SendBatchSms 接口
            $phoneNumbers = [];
            $signNames = [];
            $templateCodes = [];
            $templateParams = [];

            foreach ($messages as $index => $msg) {
                $phone = $msg['phone'] ?? '';
                $templateId = $msg['template_id'] ?? '';
                $params = $msg['params'] ?? [];

                if (!$this->validatePhone($phone)) {
                    return $this->error("第 {$index} 条手机号格式不正确: {$phone}");
                }

                $phoneNumbers[] = $phone;
                $signNames[] = $signName;
                $templateCodes[] = $templateId;
                $templateParams[] = $this->buildTemplateParams($params);
            }

            // 构造请求参数
            $params = [
                'PhoneNumberJson' => json_encode($phoneNumbers, JSON_UNESCAPED_UNICODE),
                'SignNameJson' => json_encode($signNames, JSON_UNESCAPED_UNICODE),
                'TemplateCodeJson' => json_encode($templateCodes, JSON_UNESCAPED_UNICODE),
                'TemplateParamJson' => json_encode($templateParams, JSON_UNESCAPED_UNICODE),
            ];

            // 添加公共参数
            $params = array_merge($params, [
                'AccessKeyId' => $accessKeyId,
                'Action' => 'SendBatchSms',
                'Format' => 'JSON',
                'Version' => '2017-05-25',
                'SignatureMethod' => 'HMAC-SHA1',
                'SignatureVersion' => '1.0',
                'SignatureNonce' => uniqid(),
                'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            ]);

            // 生成签名
            $params['Signature'] = $this->generateSignature($params, $accessKeySecret);

            // 发送请求
            $endpoint = $this->getConfig('endpoint', 'dysmsapi.aliyuncs.com');
            $url = 'https://' . $endpoint . '/';

            $this->log('info', '阿里云批量短信发送请求', [
                'count' => count($messages),
            ]);

            $response = $this->httpRequest($url, $params, 'GET');
            $result = json_decode($response, true);

            // 判断是否成功
            if (isset($result['Code']) && $result['Code'] === 'OK') {
                $this->log('info', '阿里云批量短信发送成功', [
                    'count' => count($messages),
                    'biz_id' => $result['BizId'] ?? '',
                ]);

                return $this->success([
                    'biz_id' => $result['BizId'] ?? '',
                    'request_id' => $result['RequestId'] ?? '',
                    'count' => count($messages),
                ]);
            }

            $errorMsg = $result['Message'] ?? '批量发送失败';
            $this->log('error', '阿里云批量短信发送失败', [
                'code' => $result['Code'] ?? '',
                'message' => $errorMsg,
            ]);

            return $this->error("阿里云批量短信发送失败: {$errorMsg}", [
                'code' => $result['Code'] ?? '',
            ]);

        } catch (\Exception $e) {
            $this->log('error', '阿里云批量短信发送异常', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('短信批量发送异常: ' . $e->getMessage());
        }
    }

    /**
     * 生成阿里云签名
     */
    private function generateSignature(array $params, string $accessKeySecret): string
    {
        // 参数排序
        ksort($params);

        // 构造规范化请求字符串
        $canonicalizedQueryString = '';
        foreach ($params as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $canonicalizedQueryString = substr($canonicalizedQueryString, 1);

        // 构造签名字符串
        $stringToSign = 'GET&' . $this->percentEncode('/') . '&' . $this->percentEncode($canonicalizedQueryString);

        // 计算签名
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));

        return $signature;
    }

    /**
     * URL编码
     */
    private function percentEncode(string $str): string
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
}
