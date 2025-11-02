<?php
/**
 * Content Generator Class
 *
 * Orchestrates content generation using Claude API
 */

if (!defined('ABSPATH')) {
    exit;
}

class RSG_Content_Generator {

    private $db;
    private $claude_api;
    private $schema_generator;

    public function __construct() {
        $this->db = new RSG_Database();
        $this->claude_api = new RSG_Claude_API();
        $this->schema_generator = new RSG_Schema_Generator();
    }

    /**
     * Generate site architecture from keywords
     */
    public function generate_site_architecture($site_id, $keywords, $competitor_urls = []) {
        $site_profile = $this->db->get_site_profile($site_id);

        if (!$site_profile) {
            throw new Exception('Site profile not found');
        }

        // Get competitor insights if URLs provided
        $competitor_summary = null;
        if (!empty($competitor_urls)) {
            $competitor_summary = $this->db->get_competitor_summary($site_id);
        }

        // Generate architecture via Claude
        $architecture = $this->claude_api->generate_site_architecture(
            $site_profile,
            $keywords,
            $competitor_summary
        );

        if (!isset($architecture['pages']) || !is_array($architecture['pages'])) {
            throw new Exception('Invalid architecture response from Claude API');
        }

        return $architecture;
    }

    /**
     * Generate all pages for a site
     */
    public function generate_all_pages($site_id, $pages) {
        $site_profile = $this->db->get_site_profile($site_id);

        if (!$site_profile) {
            throw new Exception('Site profile not found');
        }

        // Get competitor insights
        $competitor_summary = $this->db->get_competitor_summary($site_id);

        $results = [];
        $total = count($pages);

        foreach ($pages as $index => $page_spec) {
            // Update progress
            set_transient('rsg_generation_progress', [
                'current' => $index + 1,
                'total' => $total,
                'page' => $page_spec['title'],
                'status' => 'generating',
            ], 3600);

            try {
                // Generate content
                $content = $this->generate_page($site_id, $page_spec, $competitor_summary);

                $results[] = [
                    'page' => $page_spec['title'],
                    'status' => 'success',
                    'word_count' => isset($content['seo_checklist']['word_count']) ? $content['seo_checklist']['word_count'] : 0,
                    'page_id' => $content['page_id'],
                ];
            } catch (Exception $e) {
                $results[] = [
                    'page' => $page_spec['title'],
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }

            // Delay between requests to avoid rate limits
            if ($index < $total - 1) {
                sleep(2);
            }
        }

        // Clear progress
        delete_transient('rsg_generation_progress');

        return $results;
    }

    /**
     * Generate single page content
     */
    public function generate_page($site_id, $page_spec, $competitor_summary = null) {
        $site_profile = $this->db->get_site_profile($site_id);

        if (!$site_profile) {
            throw new Exception('Site profile not found');
        }

        if (!$competitor_summary) {
            $competitor_summary = $this->db->get_competitor_summary($site_id);
        }

        // Generate content via Claude
        $content_data = $this->claude_api->generate_page_content(
            $site_profile,
            $page_spec,
            $competitor_summary
        );

        // Generate HTML from content
        $html = $this->generate_html($content_data);

        // Generate schema markup
        $schema = $this->schema_generator->generate_schema(
            $site_profile,
            $page_spec,
            $content_data
        );

        // Calculate word count
        $word_count = isset($content_data['seo_checklist']['word_count'])
            ? $content_data['seo_checklist']['word_count']
            : str_word_count(strip_tags($html));

        // Calculate SEO score
        $seo_score = $this->calculate_seo_score($content_data, $page_spec);

        // Save to database
        $page_data = [
            'site_id' => $site_id,
            'title' => $page_spec['title'],
            'slug' => isset($content_data['meta']['slug']) ? $content_data['meta']['slug'] : $page_spec['slug'],
            'primary_keyword' => $page_spec['primary_keyword'],
            'secondary_keywords' => isset($page_spec['secondary_keywords']) ? $page_spec['secondary_keywords'] : [],
            'intent' => $page_spec['intent'],
            'page_type' => isset($page_spec['page_type']) ? $page_spec['page_type'] : 'standard',
            'target_words' => isset($page_spec['target_words']) ? $page_spec['target_words'] : 1000,
            'priority' => isset($page_spec['priority']) ? $page_spec['priority'] : 'medium',
            'content_html' => $html,
            'content_json' => $content_data,
            'meta_title' => isset($content_data['meta']['title']) ? $content_data['meta']['title'] : '',
            'meta_description' => isset($content_data['meta']['description']) ? $content_data['meta']['description'] : '',
            'schema_markup' => $schema,
            'status' => 'ready',
            'word_count' => $word_count,
            'seo_score' => $seo_score,
        ];

        $page_id = $this->db->save_page($page_data);

        $content_data['page_id'] = $page_id;

        return $content_data;
    }

    /**
     * Regenerate existing page
     */
    public function regenerate_page($page_id) {
        $page = $this->db->get_page($page_id);

        if (!$page) {
            throw new Exception('Page not found');
        }

        $page_spec = [
            'title' => $page['title'],
            'slug' => $page['slug'],
            'primary_keyword' => $page['primary_keyword'],
            'secondary_keywords' => $page['secondary_keywords'],
            'intent' => $page['intent'],
            'page_type' => $page['page_type'],
            'target_words' => $page['target_words'],
            'priority' => $page['priority'],
        ];

        return $this->generate_page($page['site_id'], $page_spec);
    }

    /**
     * Generate HTML from content data
     */
    private function generate_html($content_data) {
        if (!isset($content_data['content'])) {
            throw new Exception('Invalid content data structure');
        }

        $content = $content_data['content'];
        $meta = isset($content_data['meta']) ? $content_data['meta'] : [];

        ob_start();
        ?>
<!-- SEO Meta Tags -->
<!--
Title: <?php echo esc_html($meta['title'] ?? ''); ?>

Description: <?php echo esc_html($meta['description'] ?? ''); ?>

URL Slug: <?php echo esc_html($meta['slug'] ?? ''); ?>

-->

<!-- Page Content -->
<article class="rsg-content">

    <h1><?php echo esc_html($content['h1']); ?></h1>

    <div class="rsg-intro">
        <?php echo wpautop($content['intro']); ?>
    </div>

    <?php if (isset($content['sections']) && is_array($content['sections'])): ?>
        <?php foreach ($content['sections'] as $section): ?>
    <section class="rsg-section">
        <h2><?php echo esc_html($section['h2']); ?></h2>

        <?php if (isset($section['content'])): ?>
        <div class="rsg-section-content">
            <?php echo wpautop($section['content']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($section['h3_subsections']) && is_array($section['h3_subsections'])): ?>
            <?php foreach ($section['h3_subsections'] as $subsection): ?>
        <div class="rsg-subsection">
            <h3><?php echo esc_html($subsection['h3']); ?></h3>
            <div class="rsg-subsection-content">
                <?php echo wpautop($subsection['content']); ?>
            </div>
        </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($content['conclusion'])): ?>
    <div class="rsg-conclusion">
        <?php echo wpautop($content['conclusion']); ?>
    </div>
    <?php endif; ?>

</article>
        <?php
        return ob_get_clean();
    }

    /**
     * Calculate SEO score based on checklist
     */
    private function calculate_seo_score($content_data, $page_spec) {
        $score = 0;
        $max_score = 100;

        if (!isset($content_data['seo_checklist'])) {
            return 50; // Default middle score
        }

        $checklist = $content_data['seo_checklist'];

        // Keyword in title (15 points)
        if (isset($checklist['keyword_in_title']) && $checklist['keyword_in_title']) {
            $score += 15;
        }

        // Keyword in H1 (15 points)
        if (isset($checklist['keyword_in_h1']) && $checklist['keyword_in_h1']) {
            $score += 15;
        }

        // Keyword in first 100 words (15 points)
        if (isset($checklist['keyword_in_first_100']) && $checklist['keyword_in_first_100']) {
            $score += 15;
        }

        // Location mentions (15 points, scaled)
        if (isset($checklist['location_mentions'])) {
            $location_score = min($checklist['location_mentions'] * 2.5, 15);
            $score += $location_score;
        }

        // Word count meets target (20 points)
        if (isset($checklist['word_count']) && isset($page_spec['target_words'])) {
            $word_ratio = $checklist['word_count'] / $page_spec['target_words'];
            if ($word_ratio >= 0.9 && $word_ratio <= 1.2) {
                $score += 20;
            } elseif ($word_ratio >= 0.7) {
                $score += 10;
            }
        }

        // Meta description present (10 points)
        if (isset($content_data['meta']['description']) && !empty($content_data['meta']['description'])) {
            $score += 10;
        }

        // Sections/structure (10 points)
        if (isset($content_data['content']['sections']) && count($content_data['content']['sections']) >= 5) {
            $score += 10;
        }

        return min($score, $max_score);
    }

    /**
     * Get generation progress
     */
    public function get_progress() {
        $progress = get_transient('rsg_generation_progress');

        if (!$progress) {
            return [
                'current' => 0,
                'total' => 0,
                'page' => '',
                'status' => 'idle',
            ];
        }

        return $progress;
    }

    /**
     * Export page as HTML file
     */
    public function export_page_html($page_id) {
        $page = $this->db->get_page($page_id);

        if (!$page) {
            throw new Exception('Page not found');
        }

        $site = $this->db->get_site_profile($page['site_id']);

        // Generate complete HTML with schema
        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang=\"en\">\n";
        $html .= "<head>\n";
        $html .= "    <meta charset=\"UTF-8\">\n";
        $html .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $html .= "    <title>" . esc_html($page['meta_title']) . "</title>\n";
        $html .= "    <meta name=\"description\" content=\"" . esc_attr($page['meta_description']) . "\">\n";

        // Add schema markup
        if (!empty($page['schema_markup'])) {
            $html .= "    <script type=\"application/ld+json\">\n";
            $html .= json_encode($page['schema_markup'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $html .= "\n    </script>\n";
        }

        $html .= "</head>\n";
        $html .= "<body>\n\n";
        $html .= $page['content_html'];
        $html .= "\n\n</body>\n";
        $html .= "</html>";

        return $html;
    }

    /**
     * Export all site pages as ZIP
     */
    public function export_site_zip($site_id) {
        $site = $this->db->get_site_profile($site_id);
        $pages = $this->db->get_site_pages($site_id, 'ready');

        if (empty($pages)) {
            throw new Exception('No pages ready to export');
        }

        // Create temp directory
        $temp_dir = sys_get_temp_dir() . '/rsg_export_' . $site_id . '_' . time();
        wp_mkdir_p($temp_dir);

        // Export each page
        foreach ($pages as $page) {
            $html = $this->export_page_html($page['id']);
            $filename = $page['slug'] . '.html';
            file_put_contents($temp_dir . '/' . $filename, $html);
        }

        // Create README
        $readme = "SEO Content Export\n";
        $readme .= "==================\n\n";
        $readme .= "Site: {$site['site_name']}\n";
        $readme .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $readme .= "Pages: " . count($pages) . "\n\n";
        $readme .= "Instructions:\n";
        $readme .= "1. Open each HTML file\n";
        $readme .= "2. Copy the content between <article> tags\n";
        $readme .= "3. Paste into your page builder\n";
        $readme .= "4. Copy the <script type=\"application/ld+json\"> schema markup to your page head\n";
        file_put_contents($temp_dir . '/README.txt', $readme);

        // Create ZIP
        $zip_file = $temp_dir . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zip_file, ZipArchive::CREATE) !== true) {
            throw new Exception('Could not create ZIP file');
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($temp_dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($temp_dir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        // Clean up temp directory
        $this->delete_directory($temp_dir);

        return $zip_file;
    }

    /**
     * Recursively delete directory
     */
    private function delete_directory($dir) {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->delete_directory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
