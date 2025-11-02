<?php
/**
 * Test Runner - Run All Tests
 */

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║   Restaurant SEO Generator - Test Suite                     ║\n";
echo "║   WordPress Simulation Testing                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";

$start_time = microtime(true);

// Run all test files
$test_files = [
    'test-database.php',
    'test-keyword-research.php',
];

$total_passed = 0;
$total_failed = 0;

foreach ($test_files as $test_file) {
    $output = shell_exec("php " . __DIR__ . "/$test_file 2>&1");
    echo $output;

    // Parse results
    if (preg_match('/Passed: (\d+)/', $output, $matches)) {
        $total_passed += (int)$matches[1];
    }
    if (preg_match('/Failed: (\d+)/', $output, $matches)) {
        $total_failed += (int)$matches[1];
    }

    echo "\n" . str_repeat("-", 64) . "\n\n";
}

$end_time = microtime(true);
$duration = round($end_time - $start_time, 2);

// Summary
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                      OVERALL SUMMARY                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Total Tests Run:    " . ($total_passed + $total_failed) . "\n";
echo "✅ Passed:          $total_passed\n";
echo "❌ Failed:          $total_failed\n";
echo "⏱  Duration:        {$duration}s\n";
echo "\n";

if ($total_failed === 0) {
    echo "🎉 ALL TESTS PASSED! 🎉\n";
    exit(0);
} else {
    echo "⚠️  SOME TESTS FAILED - Review output above\n";
    exit(1);
}
