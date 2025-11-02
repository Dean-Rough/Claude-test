<?php
/**
 * Database Operations Class
 *
 * Handles all database interactions for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class RSG_Database {

    private $wpdb;
    private $table_sites;
    private $table_pages;
    private $table_keywords;
    private $table_competitors;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_sites = $wpdb->prefix . 'rsg_sites';
        $this->table_pages = $wpdb->prefix . 'rsg_pages';
        $this->table_keywords = $wpdb->prefix . 'rsg_keywords';
        $this->table_competitors = $wpdb->prefix . 'rsg_competitors';
    }

    /**
     * Save or update site profile
     */
    public function save_site_profile($data, $site_id = null) {
        // Prepare profile data as JSON
        $profile_data = json_encode($data);

        $db_data = [
            'site_name' => $data['site_name'],
            'industry' => $data['industry'],
            'business_type' => $data['business_type'],
            'location' => $data['location'],
            'service_area_type' => $data['service_area_type'],
            'service_area_radius' => $data['service_area_radius'],
            'target_audience' => $data['target_audience'],
            'brand_voice' => $data['brand_voice'],
            'unique_selling_point' => $data['unique_selling_point'],
            'key_services' => $data['key_services'],
            'certifications' => $data['certifications'],
            'writing_sample' => $data['writing_sample'],
            'profile_data' => $profile_data,
        ];

        if ($site_id) {
            // Update existing site
            $this->wpdb->update(
                $this->table_sites,
                $db_data,
                ['id' => $site_id],
                ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'],
                ['%d']
            );
            return $site_id;
        } else {
            // Insert new site
            $this->wpdb->insert(
                $this->table_sites,
                $db_data,
                ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
            );
            return $this->wpdb->insert_id;
        }
    }

    /**
     * Get site profile by ID
     */
    public function get_site_profile($site_id) {
        $site = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_sites} WHERE id = %d",
                $site_id
            ),
            ARRAY_A
        );

        if ($site && $site['profile_data']) {
            $site['profile_data_parsed'] = json_decode($site['profile_data'], true);
        }

        return $site;
    }

    /**
     * Get all sites
     */
    public function get_all_sites() {
        return $this->wpdb->get_results(
            "SELECT s.*, COUNT(p.id) as page_count
             FROM {$this->table_sites} s
             LEFT JOIN {$this->table_pages} p ON s.id = p.site_id
             GROUP BY s.id
             ORDER BY s.created_at DESC",
            ARRAY_A
        );
    }

    /**
     * Delete site and all related data
     */
    public function delete_site($site_id) {
        // Delete pages
        $this->wpdb->delete($this->table_pages, ['site_id' => $site_id], ['%d']);

        // Delete keywords
        $this->wpdb->delete($this->table_keywords, ['site_id' => $site_id], ['%d']);

        // Delete competitors
        $this->wpdb->delete($this->table_competitors, ['site_id' => $site_id], ['%d']);

        // Delete site
        return $this->wpdb->delete($this->table_sites, ['id' => $site_id], ['%d']);
    }

    /**
     * Save generated page
     */
    public function save_page($data) {
        $db_data = [
            'site_id' => $data['site_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'primary_keyword' => $data['primary_keyword'],
            'secondary_keywords' => is_array($data['secondary_keywords']) ? json_encode($data['secondary_keywords']) : $data['secondary_keywords'],
            'intent' => $data['intent'],
            'page_type' => isset($data['page_type']) ? $data['page_type'] : 'standard',
            'target_words' => isset($data['target_words']) ? $data['target_words'] : 1000,
            'priority' => isset($data['priority']) ? $data['priority'] : 'medium',
            'content_html' => isset($data['content_html']) ? $data['content_html'] : '',
            'content_json' => isset($data['content_json']) ? json_encode($data['content_json']) : '',
            'meta_title' => isset($data['meta_title']) ? $data['meta_title'] : '',
            'meta_description' => isset($data['meta_description']) ? $data['meta_description'] : '',
            'schema_markup' => isset($data['schema_markup']) ? json_encode($data['schema_markup']) : '',
            'status' => isset($data['status']) ? $data['status'] : 'draft',
            'word_count' => isset($data['word_count']) ? $data['word_count'] : 0,
            'seo_score' => isset($data['seo_score']) ? $data['seo_score'] : 0,
        ];

        // Check if page already exists
        $existing = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->table_pages} WHERE site_id = %d AND slug = %s",
                $data['site_id'],
                $data['slug']
            )
        );

        if ($existing) {
            // Update existing page
            $this->wpdb->update(
                $this->table_pages,
                $db_data,
                ['id' => $existing],
                ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d'],
                ['%d']
            );
            return $existing;
        } else {
            // Insert new page
            $this->wpdb->insert(
                $this->table_pages,
                $db_data,
                ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d']
            );
            return $this->wpdb->insert_id;
        }
    }

    /**
     * Get page by ID
     */
    public function get_page($page_id) {
        $page = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_pages} WHERE id = %d",
                $page_id
            ),
            ARRAY_A
        );

        if ($page) {
            if ($page['secondary_keywords']) {
                $page['secondary_keywords'] = json_decode($page['secondary_keywords'], true);
            }
            if ($page['content_json']) {
                $page['content_json'] = json_decode($page['content_json'], true);
            }
            if ($page['schema_markup']) {
                $page['schema_markup'] = json_decode($page['schema_markup'], true);
            }
        }

        return $page;
    }

    /**
     * Get all pages for a site
     */
    public function get_site_pages($site_id, $status = null) {
        $sql = "SELECT * FROM {$this->table_pages} WHERE site_id = %d";

        if ($status) {
            $sql .= " AND status = %s";
            $pages = $this->wpdb->get_results(
                $this->wpdb->prepare($sql, $site_id, $status),
                ARRAY_A
            );
        } else {
            $pages = $this->wpdb->get_results(
                $this->wpdb->prepare($sql, $site_id),
                ARRAY_A
            );
        }

        foreach ($pages as &$page) {
            if ($page['secondary_keywords']) {
                $page['secondary_keywords'] = json_decode($page['secondary_keywords'], true);
            }
            if ($page['content_json']) {
                $page['content_json'] = json_decode($page['content_json'], true);
            }
            if ($page['schema_markup']) {
                $page['schema_markup'] = json_decode($page['schema_markup'], true);
            }
        }

        return $pages;
    }

    /**
     * Delete page
     */
    public function delete_page($page_id) {
        return $this->wpdb->delete($this->table_pages, ['id' => $page_id], ['%d']);
    }

    /**
     * Save keyword
     */
    public function save_keyword($data) {
        $db_data = [
            'site_id' => isset($data['site_id']) ? $data['site_id'] : null,
            'keyword' => $data['keyword'],
            'intent' => isset($data['intent']) ? $data['intent'] : null,
            'confidence' => isset($data['confidence']) ? $data['confidence'] : 0.5,
            'source' => $data['source'],
            'search_volume' => isset($data['search_volume']) ? $data['search_volume'] : 0,
            'competition' => isset($data['competition']) ? $data['competition'] : null,
            'related_keywords' => isset($data['related_keywords']) ? json_encode($data['related_keywords']) : null,
        ];

        $this->wpdb->insert(
            $this->table_keywords,
            $db_data,
            ['%d', '%s', '%s', '%f', '%s', '%d', '%s', '%s']
        );

        return $this->wpdb->insert_id;
    }

    /**
     * Get cached keyword
     */
    public function get_cached_keyword($keyword, $max_age_hours = 168) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_keywords}
                 WHERE keyword = %s
                 AND cached_at > DATE_SUB(NOW(), INTERVAL %d HOUR)
                 ORDER BY cached_at DESC
                 LIMIT 1",
                $keyword,
                $max_age_hours
            ),
            ARRAY_A
        );
    }

    /**
     * Save competitor analysis
     */
    public function save_competitor_analysis($site_id, $analysis) {
        $db_data = [
            'site_id' => $site_id,
            'url' => $analysis['url'],
            'title' => isset($analysis['title']) ? $analysis['title'] : null,
            'meta_description' => isset($analysis['meta_description']) ? $analysis['meta_description'] : null,
            'word_count' => isset($analysis['word_count']) ? $analysis['word_count'] : 0,
            'h1_count' => isset($analysis['h1_count']) ? $analysis['h1_count'] : 0,
            'h2_count' => isset($analysis['h2_count']) ? $analysis['h2_count'] : 0,
            'h3_count' => isset($analysis['h3_count']) ? $analysis['h3_count'] : 0,
            'h2_headings' => isset($analysis['h2_headings']) ? json_encode($analysis['h2_headings']) : null,
            'image_count' => isset($analysis['image_count']) ? $analysis['image_count'] : 0,
            'has_schema' => isset($analysis['has_schema']) ? $analysis['has_schema'] : 0,
            'internal_links' => isset($analysis['internal_links']) ? $analysis['internal_links'] : 0,
            'analysis_data' => json_encode($analysis),
        ];

        $this->wpdb->insert(
            $this->table_competitors,
            $db_data,
            ['%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%s']
        );

        return $this->wpdb->insert_id;
    }

    /**
     * Get competitor analyses for a site
     */
    public function get_competitor_analyses($site_id) {
        $analyses = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_competitors} WHERE site_id = %d ORDER BY analyzed_at DESC",
                $site_id
            ),
            ARRAY_A
        );

        foreach ($analyses as &$analysis) {
            if ($analysis['h2_headings']) {
                $analysis['h2_headings'] = json_decode($analysis['h2_headings'], true);
            }
            if ($analysis['analysis_data']) {
                $analysis['analysis_data'] = json_decode($analysis['analysis_data'], true);
            }
        }

        return $analyses;
    }

    /**
     * Get summary statistics for competitor analyses
     */
    public function get_competitor_summary($site_id) {
        $analyses = $this->get_competitor_analyses($site_id);

        if (empty($analyses)) {
            return null;
        }

        $word_counts = array_column($analyses, 'word_count');
        $h2_counts = array_column($analyses, 'h2_count');
        $image_counts = array_column($analyses, 'image_count');
        $schema_count = array_sum(array_column($analyses, 'has_schema'));

        // Collect all H2 headings for topic analysis
        $all_h2s = [];
        foreach ($analyses as $analysis) {
            if (!empty($analysis['h2_headings'])) {
                $all_h2s = array_merge($all_h2s, $analysis['h2_headings']);
            }
        }

        return [
            'count' => count($analyses),
            'avg_word_count' => round(array_sum($word_counts) / count($word_counts)),
            'min_word_count' => min($word_counts),
            'max_word_count' => max($word_counts),
            'target_word_count' => round(array_sum($word_counts) / count($word_counts) * 1.1), // 10% more than average
            'avg_h2_count' => round(array_sum($h2_counts) / count($h2_counts)),
            'avg_image_count' => round(array_sum($image_counts) / count($image_counts)),
            'schema_usage' => $schema_count . '/' . count($analyses),
            'common_topics' => $this->extract_common_topics($all_h2s),
            'analyses' => $analyses,
        ];
    }

    /**
     * Extract common topics from H2 headings
     */
    private function extract_common_topics($headings) {
        if (empty($headings)) {
            return [];
        }

        // Count heading occurrences (case-insensitive, normalized)
        $topics = [];
        foreach ($headings as $heading) {
            $normalized = strtolower(trim($heading));
            if (!empty($normalized)) {
                if (!isset($topics[$normalized])) {
                    $topics[$normalized] = ['text' => $heading, 'count' => 0];
                }
                $topics[$normalized]['count']++;
            }
        }

        // Sort by count
        uasort($topics, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        // Return top 10
        return array_slice(array_values($topics), 0, 10);
    }

    /**
     * Update page status
     */
    public function update_page_status($page_id, $status) {
        return $this->wpdb->update(
            $this->table_pages,
            ['status' => $status],
            ['id' => $page_id],
            ['%s'],
            ['%d']
        );
    }

    /**
     * Get site statistics
     */
    public function get_site_statistics($site_id) {
        $pages = $this->get_site_pages($site_id);

        $stats = [
            'total_pages' => count($pages),
            'draft' => 0,
            'ready' => 0,
            'published' => 0,
            'total_words' => 0,
            'avg_words' => 0,
            'intents' => [],
        ];

        foreach ($pages as $page) {
            $stats[$page['status']]++;
            $stats['total_words'] += $page['word_count'];

            if (!isset($stats['intents'][$page['intent']])) {
                $stats['intents'][$page['intent']] = 0;
            }
            $stats['intents'][$page['intent']]++;
        }

        if ($stats['total_pages'] > 0) {
            $stats['avg_words'] = round($stats['total_words'] / $stats['total_pages']);
        }

        return $stats;
    }
}
