<?php
/**
 * Claude API Wrapper Class
 *
 * Handles all interactions with the Claude API
 */

if (!defined('ABSPATH')) {
    exit;
}

class RSG_Claude_API {

    private $api_key;
    private $model;
    private $api_url = 'https://api.anthropic.com/v1/messages';
    private $api_version = '2023-06-01';

    public function __construct($api_key = null, $model = null) {
        $this->api_key = $api_key ? $api_key : get_option('rsg_claude_api_key');
        $this->model = $model ? $model : get_option('rsg_default_model', 'claude-sonnet-4-20250514');
    }

    /**
     * Test API connection
     */
    public function test_connection() {
        try {
            $response = $this->generate_content(
                'Respond with just the word "success" if you can read this.',
                1000
            );

            if (stripos($response, 'success') !== false) {
                return [
                    'status' => 'success',
                    'message' => 'API key is valid and working!',
                ];
            } else {
                throw new Exception('Unexpected response from API');
            }
        } catch (Exception $e) {
            throw new Exception('API test failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate content from a prompt
     *
     * @param string $prompt The prompt to send
     * @param int $max_tokens Maximum tokens to generate
     * @param float $temperature Temperature for generation (0-1)
     * @return string Generated content
     */
    public function generate_content($prompt, $max_tokens = 4000, $temperature = 0.7) {
        if (empty($this->api_key)) {
            throw new Exception('Claude API key not configured');
        }

        $body = [
            'model' => $this->model,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];

        $response = wp_remote_post($this->api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => $this->api_key,
                'anthropic-version' => $this->api_version,
            ],
            'body' => json_encode($body),
            'timeout' => 120,
        ]);

        if (is_wp_error($response)) {
            throw new Exception('API request failed: ' . $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            $error_data = json_decode($response_body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'Unknown error';
            throw new Exception("Claude API error ({$response_code}): {$error_message}");
        }

        $data = json_decode($response_body, true);

        if (!isset($data['content'][0]['text'])) {
            throw new Exception('Invalid response format from Claude API');
        }

        return $data['content'][0]['text'];
    }

    /**
     * Generate JSON content from a prompt
     * Automatically extracts JSON from markdown code blocks if present
     */
    public function generate_json($prompt, $max_tokens = 4000) {
        $content = $this->generate_content($prompt, $max_tokens);

        // Try to extract JSON from markdown code block
        if (preg_match('/```(?:json)?\s*([\s\S]+?)```/i', $content, $matches)) {
            $json_str = trim($matches[1]);
        } else {
            // Try to find JSON object directly
            if (preg_match('/(\{[\s\S]+\})/i', $content, $matches)) {
                $json_str = trim($matches[1]);
            } else {
                $json_str = $content;
            }
        }

        $decoded = json_decode($json_str, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to decode JSON response: ' . json_last_error_msg() . "\nContent: " . substr($content, 0, 500));
        }

        return $decoded;
    }

    /**
     * Generate site architecture
     */
    public function generate_site_architecture($site_profile, $keywords, $competitor_summary = null) {
        $prompt = $this->build_architecture_prompt($site_profile, $keywords, $competitor_summary);
        return $this->generate_json($prompt, 4000);
    }

    /**
     * Generate page content
     */
    public function generate_page_content($site_profile, $page_spec, $competitor_summary = null) {
        $prompt = $this->build_content_prompt($site_profile, $page_spec, $competitor_summary);
        return $this->generate_json($prompt, 4000);
    }

    /**
     * Classify keyword intent
     */
    public function classify_keyword_intent($keyword, $context = '') {
        $prompt = "Classify the search intent for this keyword: \"{$keyword}\"\n\n";

        if ($context) {
            $prompt .= "Context: {$context}\n\n";
        }

        $prompt .= "Respond with just one word: informational, commercial, transactional, or navigational.";

        $response = $this->generate_content($prompt, 100, 0.3);
        $intent = strtolower(trim($response));

        $valid_intents = ['informational', 'commercial', 'transactional', 'navigational'];

        if (!in_array($intent, $valid_intents)) {
            return 'informational'; // Default fallback
        }

        return $intent;
    }

    /**
     * Build architecture prompt
     */
    private function build_architecture_prompt($site_profile, $keywords, $competitor_summary) {
        $prompt = "You're creating a website for a {$site_profile['business_type']} called {$site_profile['site_name']} in {$site_profile['location']}.\n\n";

        $prompt .= "BUSINESS PROFILE:\n";
        $prompt .= "Name: {$site_profile['site_name']}\n";
        $prompt .= "Type: {$site_profile['business_type']}\n";
        $prompt .= "Location: {$site_profile['location']}\n";
        $prompt .= "Target Audience: {$site_profile['target_audience']}\n";
        $prompt .= "Brand Voice: {$site_profile['brand_voice']}\n";
        $prompt .= "USP: {$site_profile['unique_selling_point']}\n";
        $prompt .= "Key Services: {$site_profile['key_services']}\n\n";

        if ($competitor_summary) {
            $prompt .= "COMPETITOR INSIGHTS:\n";
            $prompt .= "Analyzed {$competitor_summary['count']} competitor websites:\n";
            $prompt .= "- Average word count: {$competitor_summary['avg_word_count']} words\n";
            $prompt .= "- Average sections (H2s): {$competitor_summary['avg_h2_count']}\n";
            $prompt .= "- Average images: {$competitor_summary['avg_image_count']}\n";
            $prompt .= "- Schema usage: {$competitor_summary['schema_usage']} competitors\n\n";

            if (!empty($competitor_summary['common_topics'])) {
                $prompt .= "Common topics covered by competitors:\n";
                foreach (array_slice($competitor_summary['common_topics'], 0, 5) as $topic) {
                    $prompt .= "- {$topic['text']} ({$topic['count']} sites)\n";
                }
                $prompt .= "\n";
            }
        }

        $prompt .= "KEYWORDS WITH INTENT:\n";
        foreach ($keywords as $kw) {
            $intent = isset($kw['intent']) ? $kw['intent'] : 'informational';
            $prompt .= "- {$kw['keyword']} ({$intent})\n";
        }
        $prompt .= "\n";

        $prompt .= "Create a simple 8-10 page site structure optimized for local SEO.\n\n";

        $prompt .= $this->get_business_type_guidelines($site_profile['business_type']);

        $prompt .= "\n\nOUTPUT AS JSON in this exact format:\n";
        $prompt .= "```json\n";
        $prompt .= "{\n";
        $prompt .= "  \"pages\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"title\": \"Page title here\",\n";
        $prompt .= "      \"slug\": \"url-slug-here\",\n";
        $prompt .= "      \"primary_keyword\": \"main keyword\",\n";
        $prompt .= "      \"secondary_keywords\": [\"keyword 1\", \"keyword 2\"],\n";
        $prompt .= "      \"intent\": \"transactional\",\n";
        $prompt .= "      \"page_type\": \"service\",\n";
        $prompt .= "      \"target_words\": 1500,\n";
        $prompt .= "      \"priority\": \"high\",\n";
        $prompt .= "      \"should_include\": [\"topic 1\", \"topic 2\"],\n";
        $prompt .= "      \"internal_links_to\": [\"slug1\", \"slug2\"]\n";
        $prompt .= "    }\n";
        $prompt .= "  ]\n";
        $prompt .= "}\n";
        $prompt .= "```\n\n";

        $prompt .= "RULES:\n";
        $prompt .= "- Homepage targets main commercial keyword\n";
        $prompt .= "- Service pages target transactional keywords\n";
        $prompt .= "- Content pages target informational keywords\n";
        $prompt .= "- Every page should mention location for local SEO\n";
        $prompt .= "- Include clear conversion paths\n";
        $prompt .= "- Keep practical: 8-10 pages max\n";

        if ($competitor_summary) {
            $prompt .= "- Target word counts should match or exceed competitor average: {$competitor_summary['target_word_count']}\n";
        }

        return $prompt;
    }

    /**
     * Build content generation prompt
     */
    private function build_content_prompt($site_profile, $page_spec, $competitor_summary) {
        $prompt = "You are an expert SEO copywriter for local businesses.\n\n";

        $prompt .= "SITE CONTEXT:\n";
        $prompt .= "Business: {$site_profile['site_name']}\n";
        $prompt .= "Type: {$site_profile['business_type']}\n";
        $prompt .= "Location: {$site_profile['location']}\n";
        $prompt .= "Target Audience: {$site_profile['target_audience']}\n";
        $prompt .= "Voice: {$site_profile['brand_voice']}\n";
        $prompt .= "USP: {$site_profile['unique_selling_point']}\n";
        $prompt .= "Key Services: {$site_profile['key_services']}\n";

        if (!empty($site_profile['certifications'])) {
            $prompt .= "Credentials: {$site_profile['certifications']}\n";
        }

        $prompt .= "\n";

        if (!empty($site_profile['writing_sample'])) {
            $prompt .= "VOICE TRAINING:\n";
            $prompt .= "{$site_profile['writing_sample']}\n\n";
            $prompt .= "Write like this - match the tone exactly.\n\n";
        } else {
            $prompt .= "Write in a {$site_profile['brand_voice']} style that's professional, trustworthy, and conversational without being overly salesy.\n\n";
        }

        if ($competitor_summary) {
            $prompt .= "COMPETITOR INSIGHTS:\n";
            $prompt .= "Successful pages in this niche average {$competitor_summary['avg_word_count']} words with {$competitor_summary['avg_h2_count']} main sections.\n";
            if (!empty($competitor_summary['common_topics'])) {
                $prompt .= "Common sections: ";
                $topics = array_slice($competitor_summary['common_topics'], 0, 3);
                $prompt .= implode(', ', array_column($topics, 'text')) . "\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "PAGE TO CREATE:\n";
        $prompt .= "Title: {$page_spec['title']}\n";
        $prompt .= "Primary Keyword: {$page_spec['primary_keyword']}\n";

        if (!empty($page_spec['secondary_keywords'])) {
            $keywords = is_array($page_spec['secondary_keywords']) ? implode(', ', $page_spec['secondary_keywords']) : $page_spec['secondary_keywords'];
            $prompt .= "Secondary Keywords: {$keywords}\n";
        }

        $prompt .= "Intent: {$page_spec['intent']}\n";
        $prompt .= "Target Length: {$page_spec['target_words']} words\n";

        if (!empty($page_spec['should_include'])) {
            $includes = is_array($page_spec['should_include']) ? implode(', ', $page_spec['should_include']) : $page_spec['should_include'];
            $prompt .= "Should Include: {$includes}\n";
        }

        $prompt .= "\n";

        $prompt .= $this->get_content_requirements($site_profile['business_type']);

        $prompt .= "\n\nOUTPUT FORMAT (JSON):\n";
        $prompt .= "```json\n";
        $prompt .= "{\n";
        $prompt .= "  \"meta\": {\n";
        $prompt .= "    \"title\": \"SEO title (60 chars max)\",\n";
        $prompt .= "    \"description\": \"Meta description (155 chars max)\",\n";
        $prompt .= "    \"slug\": \"url-slug\"\n";
        $prompt .= "  },\n";
        $prompt .= "  \"content\": {\n";
        $prompt .= "    \"h1\": \"Page heading\",\n";
        $prompt .= "    \"intro\": \"Introduction paragraph (150 words)\",\n";
        $prompt .= "    \"sections\": [\n";
        $prompt .= "      {\n";
        $prompt .= "        \"h2\": \"Section heading\",\n";
        $prompt .= "        \"content\": \"Section content in HTML paragraphs\",\n";
        $prompt .= "        \"h3_subsections\": [\n";
        $prompt .= "          {\"h3\": \"Subsection\", \"content\": \"Content\"}\n";
        $prompt .= "        ]\n";
        $prompt .= "      }\n";
        $prompt .= "    ],\n";
        $prompt .= "    \"conclusion\": \"Conclusion paragraph with CTA\"\n";
        $prompt .= "  },\n";
        $prompt .= "  \"seo_checklist\": {\n";
        $prompt .= "    \"keyword_in_title\": true,\n";
        $prompt .= "    \"keyword_in_h1\": true,\n";
        $prompt .= "    \"keyword_in_first_100\": true,\n";
        $prompt .= "    \"location_mentions\": 6,\n";
        $prompt .= "    \"word_count\": 1543\n";
        $prompt .= "  }\n";
        $prompt .= "}\n";
        $prompt .= "```\n\n";

        $prompt .= "CRITICAL:\n";
        $prompt .= "- Write real, helpful content that answers customer questions\n";
        $prompt .= "- No fluff or generic statements\n";
        $prompt .= "- Be specific about services, processes, pricing philosophy\n";
        $prompt .= "- Sound like a knowledgeable local business owner\n";
        $prompt .= "- Include primary keyword naturally throughout\n";
        $prompt .= "- Mention {$site_profile['location']} 5-7 times naturally\n";

        return $prompt;
    }

    /**
     * Get business type specific guidelines for architecture
     */
    private function get_business_type_guidelines($business_type) {
        $guidelines = "BUSINESS TYPE GUIDELINES:\n\n";

        // Normalize business type
        $type_lower = strtolower($business_type);

        if (strpos($type_lower, 'restaurant') !== false || strpos($type_lower, 'bar') !== false || strpos($type_lower, 'cafe') !== false || strpos($type_lower, 'bakery') !== false) {
            $guidelines .= "For Food & Drink businesses:\n";
            $guidelines .= "- Homepage (brand + location + cuisine type)\n";
            $guidelines .= "- Menu page\n";
            $guidelines .= "- About/Story page\n";
            $guidelines .= "- Location/Contact/Hours\n";
            $guidelines .= "- 4-6 content pages (signature dishes, neighborhood guides, catering, etc.)\n";
        }
        elseif (strpos($type_lower, 'plumb') !== false || strpos($type_lower, 'electric') !== false || strpos($type_lower, 'hvac') !== false || strpos($type_lower, 'landscape') !== false || strpos($type_lower, 'clean') !== false) {
            $guidelines .= "For Home Services:\n";
            $guidelines .= "- Homepage (service + location)\n";
            $guidelines .= "- Services page (main offerings)\n";
            $guidelines .= "- Service area page\n";
            $guidelines .= "- Emergency/24-7 page (if applicable)\n";
            $guidelines .= "- About/Experience/Credentials\n";
            $guidelines .= "- Contact/Quote\n";
            $guidelines .= "- 3-5 content pages (common problems, maintenance tips, service guides)\n";
        }
        elseif (strpos($type_lower, 'dentist') !== false || strpos($type_lower, 'chiropract') !== false || strpos($type_lower, 'yoga') !== false || strpos($type_lower, 'gym') !== false || strpos($type_lower, 'fitness') !== false) {
            $guidelines .= "For Health & Wellness:\n";
            $guidelines .= "- Homepage\n";
            $guidelines .= "- Services/Treatments page\n";
            $guidelines .= "- About the practitioner/team\n";
            $guidelines .= "- New patient/client information\n";
            $guidelines .= "- Insurance/Pricing\n";
            $guidelines .= "- Contact/Appointment\n";
            $guidelines .= "- 3-5 content pages (conditions treated, wellness tips, procedure guides)\n";
        }
        elseif (strpos($type_lower, 'account') !== false || strpos($type_lower, 'lawyer') !== false || strpos($type_lower, 'attorney') !== false || strpos($type_lower, 'insurance') !== false) {
            $guidelines .= "For Professional Services:\n";
            $guidelines .= "- Homepage\n";
            $guidelines .= "- Services/Practice areas\n";
            $guidelines .= "- About/Credentials\n";
            $guidelines .= "- Resources/Blog\n";
            $guidelines .= "- Contact/Consultation\n";
            $guidelines .= "- 3-5 content pages (service deep-dives, guides, FAQs)\n";
        }
        elseif (strpos($type_lower, 'salon') !== false || strpos($type_lower, 'barber') !== false || strpos($type_lower, 'spa') !== false || strpos($type_lower, 'nail') !== false) {
            $guidelines .= "For Beauty & Personal Care:\n";
            $guidelines .= "- Homepage\n";
            $guidelines .= "- Services/Menu\n";
            $guidelines .= "- About/Team\n";
            $guidelines .= "- Gallery/Portfolio\n";
            $guidelines .= "- Pricing\n";
            $guidelines .= "- Booking/Contact\n";
            $guidelines .= "- 3-5 content pages (style guides, care tips, trends)\n";
        }
        else {
            $guidelines .= "For Local Businesses:\n";
            $guidelines .= "- Homepage (service + location)\n";
            $guidelines .= "- Services/Products page\n";
            $guidelines .= "- About/Story\n";
            $guidelines .= "- Location/Service Area\n";
            $guidelines .= "- Contact/Quote\n";
            $guidelines .= "- 3-5 content pages (related topics, guides, FAQs)\n";
        }

        return $guidelines;
    }

    /**
     * Get content requirements based on business type
     */
    private function get_content_requirements($business_type) {
        $type_lower = strtolower($business_type);

        $requirements = "CONTENT STRUCTURE:\n";
        $requirements .= "- H1: Page title (include primary keyword + location)\n";
        $requirements .= "- Introduction (150 words): Hook, include keyword in first 100 words, establish credibility\n";
        $requirements .= "- 6-8 H2 sections with detailed content\n";
        $requirements .= "- Conclusion (100 words): Summarize benefits, strong CTA\n\n";

        $requirements .= "SEO ELEMENTS:\n";
        $requirements .= "- Natural keyword usage (1-2% density)\n";
        $requirements .= "- Local references (neighborhoods, landmarks, zip codes)\n";
        $requirements .= "- Location mentions: 5-7 times naturally\n\n";

        $requirements .= "BUSINESS-SPECIFIC REQUIREMENTS:\n\n";

        if (strpos($type_lower, 'plumb') !== false || strpos($type_lower, 'electric') !== false || strpos($type_lower, 'hvac') !== false) {
            $requirements .= "For Home Services:\n";
            $requirements .= "- Emphasize licensing, insurance, bonding\n";
            $requirements .= "- Include emergency availability if applicable\n";
            $requirements .= "- Mention response times\n";
            $requirements .= "- Address common fears (mess, cost, reliability)\n";
            $requirements .= "- Specific service examples\n";
            $requirements .= "- Mention brands/equipment worked with\n";
        }
        elseif (strpos($type_lower, 'dentist') !== false || strpos($type_lower, 'chiropract') !== false) {
            $requirements .= "For Health & Wellness:\n";
            $requirements .= "- Emphasize credentials and training\n";
            $requirements .= "- Address common anxieties/concerns\n";
            $requirements .= "- Explain procedures clearly\n";
            $requirements .= "- Mention insurance accepted\n";
            $requirements .= "- Patient comfort and care philosophy\n";
            $requirements .= "- Technology/modern equipment\n";
        }
        elseif (strpos($type_lower, 'restaurant') !== false || strpos($type_lower, 'bar') !== false || strpos($type_lower, 'cafe') !== false) {
            $requirements .= "For Food & Drink:\n";
            $requirements .= "- Sensory details (aromas, textures, presentation)\n";
            $requirements .= "- Specific menu items by name\n";
            $requirements .= "- Chef/owner background\n";
            $requirements .= "- Sourcing/quality of ingredients\n";
            $requirements .= "- Ambiance and experience\n";
        }
        elseif (strpos($type_lower, 'salon') !== false || strpos($type_lower, 'barber') !== false) {
            $requirements .= "For Beauty & Personal Care:\n";
            $requirements .= "- Before/after descriptions\n";
            $requirements .= "- Product lines used\n";
            $requirements .= "- Stylist/technician experience\n";
            $requirements .= "- Latest techniques/trends\n";
            $requirements .= "- Appointment/booking ease\n";
        }
        else {
            $requirements .= "For Local Businesses:\n";
            $requirements .= "- Emphasize experience and expertise\n";
            $requirements .= "- Address customer pain points\n";
            $requirements .= "- Show credentials/certifications\n";
            $requirements .= "- Explain process/what to expect\n";
            $requirements .= "- Include trust signals\n";
        }

        $requirements .= "\nTONE:\n";
        $requirements .= "- Professional but authentic\n";
        $requirements .= "- Write for local customers with specific needs\n";
        $requirements .= "- Avoid clichés and marketing-speak\n";
        $requirements .= "- Be specific, not vague\n";
        $requirements .= "- Show expertise through knowledge, not claims\n";

        return $requirements;
    }
}
