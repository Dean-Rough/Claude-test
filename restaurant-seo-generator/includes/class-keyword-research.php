<?php
/**
 * Keyword Research Class
 *
 * Handles keyword generation, intent classification, and competitor analysis
 */

if (!defined('ABSPATH')) {
    exit;
}

class RSG_Keyword_Research {

    private $db;

    public function __construct() {
        $this->db = new RSG_Database();
    }

    /**
     * Generate keywords from seed keywords
     */
    public function generate_keywords($seed_keywords, $location = '', $auto_expand = true) {
        $all_keywords = [];

        foreach ($seed_keywords as $seed) {
            $seed = trim($seed);
            if (empty($seed)) continue;

            // Add the original seed
            $all_keywords[] = $seed;

            if ($auto_expand) {
                // Get Google Autocomplete suggestions
                $suggestions = $this->get_autocomplete_suggestions($seed);
                $all_keywords = array_merge($all_keywords, $suggestions);

                // Add question variations
                $questions = ['how', 'what', 'where', 'when', 'why', 'best', 'top'];
                foreach ($questions as $q) {
                    $query = "$q $seed";
                    $results = $this->get_autocomplete_suggestions($query);
                    $all_keywords = array_merge($all_keywords, $results);
                    usleep(500000); // 0.5 second delay
                }

                // Add local variations if location provided
                if (!empty($location)) {
                    $local_query = "$seed $location";
                    $results = $this->get_autocomplete_suggestions($local_query);
                    $all_keywords = array_merge($all_keywords, $results);

                    // Reverse order
                    $local_query = "$location $seed";
                    $results = $this->get_autocomplete_suggestions($local_query);
                    $all_keywords = array_merge($all_keywords, $results);

                    usleep(500000);
                }
            }
        }

        // Remove duplicates and filter
        $all_keywords = array_unique($all_keywords);
        $all_keywords = array_filter($all_keywords, function($kw) {
            return strlen($kw) > 3 && strlen($kw) < 100;
        });

        // Sort by length (shorter = more important)
        usort($all_keywords, function($a, $b) {
            return strlen($a) - strlen($b);
        });

        return array_values($all_keywords);
    }

    /**
     * Get Google Autocomplete suggestions
     */
    public function get_autocomplete_suggestions($query) {
        $url = 'http://suggestqueries.google.com/complete/search';
        $params = [
            'client' => 'firefox',
            'q' => $query,
        ];

        $response = wp_remote_get($url . '?' . http_build_query($params), [
            'timeout' => 10,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ]);

        if (is_wp_error($response)) {
            return [];
        }

        $body = wp_remote_retrieve_body($response);

        // Google returns JSON array format
        $data = json_decode($body, true);

        if (!isset($data[1]) || !is_array($data[1])) {
            return [];
        }

        return $data[1];
    }

    /**
     * Classify keywords with intent
     */
    public function classify_keywords($keywords, $site_name = '') {
        $classified = [];

        foreach ($keywords as $keyword) {
            $intent = $this->classify_intent($keyword, $site_name);
            $classified[] = [
                'keyword' => $keyword,
                'intent' => $intent,
                'confidence' => 0.8, // Pattern-based confidence
            ];
        }

        return $classified;
    }

    /**
     * Classify individual keyword intent using pattern matching
     */
    public function classify_intent($keyword, $site_name = '') {
        $keyword_lower = strtolower(trim($keyword));

        // Navigational signals (brand name searches)
        if (!empty($site_name)) {
            $site_name_lower = strtolower($site_name);
            if (strpos($keyword_lower, $site_name_lower) !== false) {
                return 'navigational';
            }
        }

        // Transactional signals (ready to buy/book/order)
        $transactional = [
            'buy', 'order', 'delivery', 'reservation', 'book', 'booking',
            'menu', 'hours', 'phone', 'contact', 'price', 'pricing',
            'cost', 'hire', 'get quote', 'free quote', 'estimate',
            'appointment', 'schedule', 'call', 'emergency',
            'near me', 'open now', 'coupon', 'deal', 'discount'
        ];

        foreach ($transactional as $signal) {
            if (strpos($keyword_lower, $signal) !== false) {
                return 'transactional';
            }
        }

        // Commercial investigation (comparing options)
        $commercial = [
            'best', 'top', 'review', 'reviews', 'vs', 'versus', 'compare',
            'comparison', 'alternative', 'cheapest', 'affordable',
            'recommended', 'rating', 'rated'
        ];

        foreach ($commercial as $signal) {
            if (strpos($keyword_lower, $signal) !== false) {
                return 'commercial';
            }
        }

        // Question words typically indicate informational intent
        $questions = ['how', 'what', 'why', 'when', 'where', 'who', 'which'];
        foreach ($questions as $q) {
            if (strpos($keyword_lower, $q) === 0) {
                return 'informational';
            }
        }

        // Default to informational
        return 'informational';
    }

    /**
     * Analyze competitor website
     */
    public function analyze_competitor($url) {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid URL'];
        }

        // Fetch the page
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            return ['error' => 'Could not fetch URL: ' . $response->get_error_message()];
        }

        $html = wp_remote_retrieve_body($response);

        if (empty($html)) {
            return ['error' => 'Empty response from URL'];
        }

        // Parse HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Extract data
        $analysis = [
            'url' => $url,
            'title' => $this->extract_title($xpath),
            'meta_description' => $this->extract_meta($xpath, 'description'),
            'word_count' => $this->count_words($html),
            'h1_count' => $xpath->query('//h1')->length,
            'h2_count' => $xpath->query('//h2')->length,
            'h3_count' => $xpath->query('//h3')->length,
            'h2_headings' => $this->extract_headings($xpath, 'h2'),
            'h3_headings' => $this->extract_headings($xpath, 'h3'),
            'image_count' => $xpath->query('//img')->length,
            'has_schema' => $this->detect_schema($html),
            'internal_links' => $this->count_internal_links($xpath, $url),
        ];

        return $analysis;
    }

    /**
     * Extract page title
     */
    private function extract_title($xpath) {
        $title_nodes = $xpath->query('//title');
        if ($title_nodes->length > 0) {
            return trim($title_nodes->item(0)->textContent);
        }
        return '';
    }

    /**
     * Extract meta tag content
     */
    private function extract_meta($xpath, $name) {
        // Try name attribute
        $meta = $xpath->query("//meta[@name='{$name}']");
        if ($meta->length > 0) {
            return $meta->item(0)->getAttribute('content');
        }

        // Try property attribute (for Open Graph)
        $meta = $xpath->query("//meta[@property='og:{$name}']");
        if ($meta->length > 0) {
            return $meta->item(0)->getAttribute('content');
        }

        return '';
    }

    /**
     * Count words in HTML content
     */
    private function count_words($html) {
        // Remove script and style tags
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

        // Strip HTML tags
        $text = strip_tags($html);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Count words
        return str_word_count($text);
    }

    /**
     * Extract heading text
     */
    private function extract_headings($xpath, $tag) {
        $headings = [];
        $nodes = $xpath->query("//{$tag}");

        foreach ($nodes as $node) {
            $text = trim($node->textContent);
            if (!empty($text) && strlen($text) < 200) {
                $headings[] = $text;
            }
        }

        return $headings;
    }

    /**
     * Detect schema markup
     */
    private function detect_schema($html) {
        // Check for JSON-LD
        if (strpos($html, 'application/ld+json') !== false) {
            return true;
        }

        // Check for microdata
        if (strpos($html, 'itemscope') !== false || strpos($html, 'itemprop') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Count internal links
     */
    private function count_internal_links($xpath, $url) {
        $parsed_url = parse_url($url);
        $domain = $parsed_url['host'];

        $all_links = $xpath->query('//a[@href]');
        $internal_count = 0;

        foreach ($all_links as $link) {
            $href = $link->getAttribute('href');

            // Skip empty, anchor, and external links
            if (empty($href) || $href[0] == '#') {
                continue;
            }

            // Relative links are internal
            if ($href[0] == '/') {
                $internal_count++;
                continue;
            }

            // Check if absolute link is to same domain
            if (strpos($href, $domain) !== false) {
                $internal_count++;
            }
        }

        return $internal_count;
    }

    /**
     * Get People Also Ask questions (basic scraping)
     * Note: This is a simplified version. Google's PAA can be tricky to scrape.
     */
    public function get_people_also_ask($query) {
        // This would require more sophisticated scraping
        // For MVP, we'll focus on autocomplete
        // Future enhancement: Scrape PAA or use API
        return [];
    }

    /**
     * Extract competitor insights summary
     */
    public function get_competitor_insights($site_id) {
        return $this->db->get_competitor_summary($site_id);
    }
}
