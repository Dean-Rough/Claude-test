<?php
/**
 * Test Keyword Research Class
 */

require_once __DIR__ . '/mock-wordpress.php';
require_once __DIR__ . '/../includes/class-database.php';
require_once __DIR__ . '/../includes/class-keyword-research.php';

class KeywordResearchTest {
    private $kr;
    private $passed = 0;
    private $failed = 0;

    public function __construct() {
        $this->kr = new RSG_Keyword_Research();
    }

    public function run_all_tests() {
        echo "\n=== Testing RSG_Keyword_Research Class ===\n\n";

        $this->test_classify_intent_transactional();
        $this->test_classify_intent_commercial();
        $this->test_classify_intent_informational();
        $this->test_classify_intent_navigational();
        $this->test_classify_keywords();

        $this->print_results();
    }

    private function test_classify_intent_transactional() {
        echo "Testing classify_intent() - Transactional... ";

        $test_cases = [
            'pizza delivery brooklyn' => 'transactional',
            'order pizza online' => 'transactional',
            'book table restaurant' => 'transactional',
            'plumber near me' => 'transactional',
            'emergency plumber' => 'transactional',
        ];

        $all_passed = true;
        foreach ($test_cases as $keyword => $expected) {
            $result = $this->kr->classify_intent($keyword);
            if ($result !== $expected) {
                $all_passed = false;
                echo "\n  ❌ '$keyword' returned '$result', expected '$expected'";
            }
        }

        if ($all_passed) {
            $this->pass("All transactional keywords classified correctly");
        } else {
            $this->fail("Some transactional keywords misclassified");
        }
    }

    private function test_classify_intent_commercial() {
        echo "Testing classify_intent() - Commercial... ";

        $test_cases = [
            'best pizza brooklyn' => 'commercial',
            'top restaurants near me' => 'commercial',
            'pizza reviews' => 'commercial',
            'best plumber vs electrician' => 'commercial',
        ];

        $all_passed = true;
        foreach ($test_cases as $keyword => $expected) {
            $result = $this->kr->classify_intent($keyword);
            if ($result !== $expected) {
                $all_passed = false;
                echo "\n  ❌ '$keyword' returned '$result', expected '$expected'";
            }
        }

        if ($all_passed) {
            $this->pass("All commercial keywords classified correctly");
        } else {
            $this->fail("Some commercial keywords misclassified");
        }
    }

    private function test_classify_intent_informational() {
        echo "Testing classify_intent() - Informational... ";

        $test_cases = [
            'how to make pizza dough' => 'informational',
            'what is neapolitan pizza' => 'informational',
            'why is pizza popular' => 'informational',
            'pizza history' => 'informational',
        ];

        $all_passed = true;
        foreach ($test_cases as $keyword => $expected) {
            $result = $this->kr->classify_intent($keyword);
            if ($result !== $expected) {
                $all_passed = false;
                echo "\n  ❌ '$keyword' returned '$result', expected '$expected'";
            }
        }

        if ($all_passed) {
            $this->pass("All informational keywords classified correctly");
        } else {
            $this->fail("Some informational keywords misclassified");
        }
    }

    private function test_classify_intent_navigational() {
        echo "Testing classify_intent() - Navigational... ";

        $result = $this->kr->classify_intent('mario\'s pizza brooklyn', 'Mario\'s Pizza');

        if ($result === 'navigational') {
            $this->pass("Navigational keyword classified correctly");
        } else {
            $this->fail("Expected 'navigational', got '$result'");
        }
    }

    private function test_classify_keywords() {
        echo "Testing classify_keywords()... ";

        $keywords = [
            'pizza brooklyn',
            'best pizza near me',
            'pizza delivery',
            'how to make pizza'
        ];

        $classified = $this->kr->classify_keywords($keywords);

        if (count($classified) === count($keywords)) {
            $has_intent = true;
            $has_confidence = true;

            foreach ($classified as $kw) {
                if (!isset($kw['intent']) || !isset($kw['confidence'])) {
                    $has_intent = false;
                    $has_confidence = false;
                    break;
                }
            }

            if ($has_intent && $has_confidence) {
                $this->pass("All keywords classified with intent and confidence");
            } else {
                $this->fail("Missing intent or confidence in results");
            }
        } else {
            $this->fail("Expected " . count($keywords) . " results, got " . count($classified));
        }
    }

    private function pass($message = '') {
        $this->passed++;
        echo "✅ PASS" . ($message ? " - $message" : "") . "\n";
    }

    private function fail($message = '') {
        $this->failed++;
        echo "❌ FAIL" . ($message ? " - $message" : "") . "\n";
    }

    private function print_results() {
        $total = $this->passed + $this->failed;
        echo "\n=== Results ===\n";
        echo "Total tests: $total\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo ($this->failed === 0 ? "✅ All tests passed!\n" : "❌ Some tests failed\n");
    }
}

// Run tests
$test = new KeywordResearchTest();
$test->run_all_tests();
