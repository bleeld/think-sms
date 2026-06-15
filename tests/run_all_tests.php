<?php
/**
 * 运行所有测试
 */

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║           Think-SMS 插件测试套件                         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$tests = [
    '驱动接口测试' => __DIR__ . '/test_driver_interface.php',
    '驱动工厂测试' => __DIR__ . '/test_driver_factory.php',
    '短信服务测试' => __DIR__ . '/test_sms_service.php',
    '签名算法测试' => __DIR__ . '/test_signature.php',
];

$totalTests = count($tests);
$passedTests = 0;
$failedTests = 0;

foreach ($tests as $name => $file) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "运行测试: {$name}\n";
    echo str_repeat('=', 60) . "\n";
    
    if (!file_exists($file)) {
        echo "✗ 测试文件不存在: {$file}\n";
        $failedTests++;
        continue;
    }
    
    // 捕获输出和错误
    ob_start();
    $exitCode = 0;
    
    try {
        include $file;
    } catch (\Throwable $e) {
        echo "\n✗ 测试执行出错: " . $e->getMessage() . "\n";
        echo "   文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
        $exitCode = 1;
    }
    
    $output = ob_get_clean();
    echo $output;
    
    // 检查是否有失败标记
    if (strpos($output, '✗') !== false || $exitCode !== 0) {
        $failedTests++;
    } else {
        $passedTests++;
    }
}

// 显示总结
echo "\n" . str_repeat('=', 60) . "\n";
echo "测试总结\n";
echo str_repeat('=', 60) . "\n";
echo "总测试数: {$totalTests}\n";
echo "通过: {$passedTests}\n";
echo "失败: {$failedTests}\n";

if ($failedTests === 0) {
    echo "\n✓ 所有测试通过！\n";
    exit(0);
} else {
    echo "\n✗ 部分测试失败，请检查上面的错误信息\n";
    exit(1);
}
