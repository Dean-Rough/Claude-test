<?php
/**
 * Test Database Class
 */

require_once __DIR__ . '/mock-wordpress.php';
require_once __DIR__ . '/../includes/class-database.php';

class DatabaseTest {
    private $db;
    private $passed = 0;
    private $failed = 0;

    public function __construct() {
        $this->db = new RSG_Database();
    }

    public function run_all_tests() {
        echo "\n=== Testing RSG_Database Class ===\n\n";

        $this->test_save_site_profile();
        $this->test_get_site_profile();
        $this->test_save_page();
        $this->test_get_page();
        $this->test_save_keyword();
        $this->test_save_competitor_analysis();

        $this->print_results();
    }

    private function test_save_site_profile() {
        echo "Testing save_site_profile()... ";

        $data = [
            'site_name' => 'Test Restaurant',
            'industry' => 'food_drink',
            'business_type' => 'restaurant',
            'location' => 'Brooklyn, NY',
            'service_area_type' => 'single_location',
            'service_area_radius' => 15,
            'target_audience' => 'Families and food lovers',
            'brand_voice' => 'friendly_approachable',
            'unique_selling_point' => 'Farm to table',
            'key_services' => 'Italian cuisine, wood-fired pizza',
            'certifications' => 'NYC Health Grade A',
            'writing_sample' => 'Welcome to our restaurant...'
        ];

        $site_id = $this->db->save_site_profile($data);

        if ($site_id > 0) {
            $this->pass("Site ID: $site_id");
        } else {
            $this->fail("Expected site ID > 0");
        }
    }

    private function test_get_site_profile() {
        echo "Testing get_site_profile()... ";

        $site = $this->db->get_site_profile(1);

        if ($site === null) {
            $this->pass("Returns null for non-existent site");
        } else {
            $this->fail("Expected null for non-existent site");
        }
    }

    private function test_save_page() {
        echo "Testing save_page()... ";

        $data = [
            'site_id' => 1,
            'title' => 'Best Pizza in Brooklyn',
            'slug' => 'best-pizza-brooklyn',
            'primary_keyword' => 'best pizza brooklyn',
            'secondary_keywords' => ['brooklyn pizza', 'pizza near me'],
            'intent' => 'commercial',
            'page_type' => 'content',
            'target_words' => 1500,
            'priority' => 'high',
            'content_html' => '<h1>Best Pizza in Brooklyn</h1>',
            'content_json' => ['h1' => 'Best Pizza'],
            'meta_title' => 'Best Pizza in Brooklyn | Mario\'s Pizza',
            'meta_description' => 'Discover the best pizza in Brooklyn...',
            'schema_markup' => ['@type' => 'Restaurant'],
            'status' => 'ready',
            'word_count' => 1543,
            'seo_score' => 92
        ];

        $page_id = $this->db->save_page($data);

        if ($page_id > 0) {
            $this->pass("Page ID: $page_id");
        } else {
            $this->fail("Expected page ID > 0");
        }
    }

    private function test_get_page() {
        echo "Testing get_page()... ";

        $page = $this->db->get_page(999);

        if ($page === null) {
            $this->pass("Returns null for non-existent page");
        } else {
            $this->fail("Expected null");
        }
    }

    private function test_save_keyword() {
        echo "Testing save_keyword()... ";

        $data = [
            'site_id' => 1,
            'keyword' => 'pizza brooklyn',
            'intent' => 'commercial',
            'confidence' => 0.9,
            'source' => 'autocomplete',
            'search_volume' => 1000,
            'competition' => 'medium'
        ];

        $keyword_id = $this->db->save_keyword($data);

        if ($keyword_id > 0) {
            $this->pass("Keyword ID: $keyword_id");
        } else {
            $this->fail("Expected keyword ID > 0");
        }
    }

    private function test_save_competitor_analysis() {
        echo "Testing save_competitor_analysis()... ";

        $analysis = [
            'url' => 'https://competitor.com',
            'title' => 'Competitor Restaurant',
            'meta_description' => 'Best pizza in town',
            'word_count' => 2000,
            'h1_count' => 1,
            'h2_count' => 8,
            'h3_count' => 12,
            'h2_headings' => ['About Us', 'Menu', 'Contact'],
            'image_count' => 10,
            'has_schema' => 1,
            'internal_links' => 25
        ];

        $competitor_id = $this->db->save_competitor_analysis(1, $analysis);

        if ($competitor_id > 0) {
            $this->pass("Competitor ID: $competitor_id");
        } else {
            $this->fail("Expected competitor ID > 0");
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
$test = new DatabaseTest();
$test->run_all_tests();
