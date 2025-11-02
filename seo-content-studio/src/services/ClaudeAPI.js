/**
 * Claude API Service
 * Handles all interactions with the Claude API
 * Ported from WordPress plugin PHP class
 */

const axios = require('axios');

class ClaudeAPI {
    constructor(apiKey = null, model = null) {
        this.apiKey = apiKey;
        this.model = model || 'claude-sonnet-4-20250514';
        this.apiUrl = 'https://api.anthropic.com/v1/messages';
        this.apiVersion = '2023-06-01';
    }

    /**
     * Test API connection
     */
    async testConnection() {
        try {
            const response = await this.generateContent(
                'Respond with just the word "success" if you can read this.',
                1000
            );

            if (response.toLowerCase().includes('success')) {
                return {
                    status: 'success',
                    message: 'API key is valid and working!'
                };
            } else {
                throw new Error('Unexpected response from API');
            }
        } catch (error) {
            throw new Error('API test failed: ' + error.message);
        }
    }

    /**
     * Generate content from a prompt
     */
    async generateContent(prompt, maxTokens = 4000, temperature = 0.7) {
        if (!this.apiKey) {
            throw new Error('Claude API key not configured');
        }

        try {
            const response = await axios.post(
                this.apiUrl,
                {
                    model: this.model,
                    max_tokens: maxTokens,
                    temperature: temperature,
                    messages: [
                        {
                            role: 'user',
                            content: prompt
                        }
                    ]
                },
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'x-api-key': this.apiKey,
                        'anthropic-version': this.apiVersion
                    },
                    timeout: 120000
                }
            );

            if (!response.data.content || !response.data.content[0] || !response.data.content[0].text) {
                throw new Error('Invalid response format from Claude API');
            }

            return response.data.content[0].text;
        } catch (error) {
            if (error.response) {
                const errorMessage = error.response.data?.error?.message || 'Unknown error';
                throw new Error(`Claude API error (${error.response.status}): ${errorMessage}`);
            } else {
                throw new Error('API request failed: ' + error.message);
            }
        }
    }

    /**
     * Generate JSON content from a prompt
     * Automatically extracts JSON from markdown code blocks if present
     */
    async generateJSON(prompt, maxTokens = 4000) {
        const content = await this.generateContent(prompt, maxTokens);

        // Try to extract JSON from markdown code block
        let jsonStr;
        const markdownMatch = content.match(/```(?:json)?\s*([\s\S]+?)```/i);

        if (markdownMatch) {
            jsonStr = markdownMatch[1].trim();
        } else {
            // Try to find JSON object directly
            const jsonMatch = content.match(/(\{[\s\S]+\})/i);
            if (jsonMatch) {
                jsonStr = jsonMatch[1].trim();
            } else {
                jsonStr = content;
            }
        }

        try {
            return JSON.parse(jsonStr);
        } catch (error) {
            throw new Error(`Failed to decode JSON response: ${error.message}\nContent: ${content.substring(0, 500)}`);
        }
    }

    /**
     * Generate site architecture
     */
    async generateSiteArchitecture(siteProfile, keywords, competitorSummary = null) {
        const prompt = this.buildArchitecturePrompt(siteProfile, keywords, competitorSummary);
        return await this.generateJSON(prompt, 4000);
    }

    /**
     * Generate page content
     */
    async generatePageContent(siteProfile, pageSpec, competitorSummary = null) {
        const prompt = this.buildContentPrompt(siteProfile, pageSpec, competitorSummary);
        return await this.generateJSON(prompt, 4000);
    }

    /**
     * Classify keyword intent
     */
    async classifyKeywordIntent(keyword, context = '') {
        let prompt = `Classify the search intent for this keyword: "${keyword}"\n\n`;

        if (context) {
            prompt += `Context: ${context}\n\n`;
        }

        prompt += 'Respond with just one word: informational, commercial, transactional, or navigational.';

        const response = await this.generateContent(prompt, 100, 0.3);
        const intent = response.toLowerCase().trim();

        const validIntents = ['informational', 'commercial', 'transactional', 'navigational'];

        if (!validIntents.includes(intent)) {
            return 'informational'; // Default fallback
        }

        return intent;
    }

    /**
     * Build architecture prompt
     */
    buildArchitecturePrompt(siteProfile, keywords, competitorSummary) {
        let prompt = `You're creating a website for a ${siteProfile.business_type} called ${siteProfile.business_name} in ${siteProfile.location}.\n\n`;

        prompt += `BUSINESS PROFILE:\n`;
        prompt += `Name: ${siteProfile.business_name}\n`;
        prompt += `Type: ${siteProfile.business_type}\n`;
        prompt += `Location: ${siteProfile.location}\n`;
        prompt += `Brand Voice: ${siteProfile.brand_voice}\n`;

        if (siteProfile.description) {
            prompt += `Description: ${siteProfile.description}\n`;
        }

        prompt += '\n';

        if (competitorSummary && competitorSummary.competitors) {
            prompt += `COMPETITOR INSIGHTS:\n`;
            prompt += `Analyzed ${competitorSummary.total_analyzed} competitor websites\n`;
            if (competitorSummary.common_topics && competitorSummary.common_topics.length > 0) {
                prompt += `Common topics covered:\n`;
                competitorSummary.common_topics.slice(0, 5).forEach(topic => {
                    prompt += `- ${topic}\n`;
                });
            }
            prompt += '\n';
        }

        prompt += `KEYWORDS WITH INTENT:\n`;
        keywords.forEach(kw => {
            const intent = kw.search_intent || kw.intent || 'informational';
            const keyword = kw.keyword;
            prompt += `- ${keyword} (${intent})\n`;
        });
        prompt += '\n';

        prompt += `Create a simple 8-10 page site structure optimized for local SEO.\n\n`;

        prompt += this.getBusinessTypeGuidelines(siteProfile.business_type);

        prompt += `\n\nOUTPUT AS JSON in this exact format:\n`;
        prompt += '```json\n';
        prompt += '{\n';
        prompt += '  "pages": [\n';
        prompt += '    {\n';
        prompt += '      "title": "Page title here",\n';
        prompt += '      "slug": "url-slug-here",\n';
        prompt += '      "primary_keyword": "main keyword",\n';
        prompt += '      "secondary_keywords": ["keyword 1", "keyword 2"],\n';
        prompt += '      "intent": "transactional",\n';
        prompt += '      "page_type": "service",\n';
        prompt += '      "target_words": 1500,\n';
        prompt += '      "priority": "high",\n';
        prompt += '      "should_include": ["topic 1", "topic 2"],\n';
        prompt += '      "internal_links_to": ["slug1", "slug2"]\n';
        prompt += '    }\n';
        prompt += '  ]\n';
        prompt += '}\n';
        prompt += '```\n\n';

        prompt += `RULES:\n`;
        prompt += `- Homepage targets main commercial keyword\n`;
        prompt += `- Service pages target transactional keywords\n`;
        prompt += `- Content pages target informational keywords\n`;
        prompt += `- Every page should mention location for local SEO\n`;
        prompt += `- Include clear conversion paths\n`;
        prompt += `- Keep practical: 8-10 pages max\n`;

        return prompt;
    }

    /**
     * Build content generation prompt
     */
    buildContentPrompt(siteProfile, pageSpec, competitorSummary) {
        let prompt = `You are an expert SEO copywriter for local businesses.\n\n`;

        prompt += `SITE CONTEXT:\n`;
        prompt += `Business: ${siteProfile.business_name}\n`;
        prompt += `Type: ${siteProfile.business_type}\n`;
        prompt += `Location: ${siteProfile.location}\n`;
        prompt += `Voice: ${siteProfile.brand_voice}\n`;

        if (siteProfile.description) {
            prompt += `Description: ${siteProfile.description}\n`;
        }

        prompt += '\n';

        if (siteProfile.writing_sample) {
            prompt += `VOICE TRAINING:\n`;
            prompt += `${siteProfile.writing_sample}\n\n`;
            prompt += `Write like this - match the tone exactly.\n\n`;
        } else {
            prompt += `Write in a ${siteProfile.brand_voice} style that's professional, trustworthy, and conversational without being overly salesy.\n\n`;
        }

        if (competitorSummary) {
            prompt += `COMPETITOR INSIGHTS:\n`;
            prompt += `Analyzed competitor websites in this niche.\n`;
            if (competitorSummary.common_topics && competitorSummary.common_topics.length > 0) {
                prompt += `Common sections: `;
                prompt += competitorSummary.common_topics.slice(0, 3).join(', ') + '\n';
            }
            prompt += '\n';
        }

        prompt += `PAGE TO CREATE:\n`;
        prompt += `Title: ${pageSpec.title}\n`;
        prompt += `Primary Keyword: ${pageSpec.primary_keyword || pageSpec.target_keyword}\n`;

        if (pageSpec.secondary_keywords && pageSpec.secondary_keywords.length > 0) {
            prompt += `Secondary Keywords: ${pageSpec.secondary_keywords.join(', ')}\n`;
        }

        prompt += `Intent: ${pageSpec.intent || pageSpec.search_intent}\n`;
        prompt += `Target Length: ${pageSpec.target_words || 1500} words\n`;

        if (pageSpec.should_include && pageSpec.should_include.length > 0) {
            prompt += `Should Include: ${pageSpec.should_include.join(', ')}\n`;
        }

        prompt += '\n';

        prompt += this.getContentRequirements(siteProfile.business_type);

        prompt += `\n\nOUTPUT FORMAT (JSON):\n`;
        prompt += '```json\n';
        prompt += '{\n';
        prompt += '  "meta": {\n';
        prompt += '    "title": "SEO title (60 chars max)",\n';
        prompt += '    "description": "Meta description (155 chars max)",\n';
        prompt += '    "slug": "url-slug"\n';
        prompt += '  },\n';
        prompt += '  "content": {\n';
        prompt += '    "h1": "Page heading",\n';
        prompt += '    "intro": "Introduction paragraph (150 words)",\n';
        prompt += '    "sections": [\n';
        prompt += '      {\n';
        prompt += '        "h2": "Section heading",\n';
        prompt += '        "content": "Section content in HTML paragraphs",\n';
        prompt += '        "h3_subsections": [\n';
        prompt += '          {"h3": "Subsection", "content": "Content"}\n';
        prompt += '        ]\n';
        prompt += '      }\n';
        prompt += '    ],\n';
        prompt += '    "conclusion": "Conclusion paragraph with CTA"\n';
        prompt += '  },\n';
        prompt += '  "seo_checklist": {\n';
        prompt += '    "keyword_in_title": true,\n';
        prompt += '    "keyword_in_h1": true,\n';
        prompt += '    "keyword_in_first_100": true,\n';
        prompt += '    "location_mentions": 6,\n';
        prompt += '    "word_count": 1543\n';
        prompt += '  }\n';
        prompt += '}\n';
        prompt += '```\n\n';

        prompt += `CRITICAL:\n`;
        prompt += `- Write real, helpful content that answers customer questions\n`;
        prompt += `- No fluff or generic statements\n`;
        prompt += `- Be specific about services, processes, pricing philosophy\n`;
        prompt += `- Sound like a knowledgeable local business owner\n`;
        prompt += `- Include primary keyword naturally throughout\n`;
        prompt += `- Mention ${siteProfile.location} 5-7 times naturally\n`;

        return prompt;
    }

    /**
     * Get business type specific guidelines for architecture
     */
    getBusinessTypeGuidelines(businessType) {
        let guidelines = "BUSINESS TYPE GUIDELINES:\n\n";

        const typeLower = businessType.toLowerCase();

        if (typeLower.includes('restaurant') || typeLower.includes('bar') ||
            typeLower.includes('cafe') || typeLower.includes('bakery')) {
            guidelines += "For Food & Drink businesses:\n";
            guidelines += "- Homepage (brand + location + cuisine type)\n";
            guidelines += "- Menu page\n";
            guidelines += "- About/Story page\n";
            guidelines += "- Location/Contact/Hours\n";
            guidelines += "- 4-6 content pages (signature dishes, neighborhood guides, catering, etc.)\n";
        }
        else if (typeLower.includes('plumb') || typeLower.includes('electric') ||
                 typeLower.includes('hvac') || typeLower.includes('landscape') ||
                 typeLower.includes('clean')) {
            guidelines += "For Home Services:\n";
            guidelines += "- Homepage (service + location)\n";
            guidelines += "- Services page (main offerings)\n";
            guidelines += "- Service area page\n";
            guidelines += "- Emergency/24-7 page (if applicable)\n";
            guidelines += "- About/Experience/Credentials\n";
            guidelines += "- Contact/Quote\n";
            guidelines += "- 3-5 content pages (common problems, maintenance tips, service guides)\n";
        }
        else if (typeLower.includes('dentist') || typeLower.includes('chiropract') ||
                 typeLower.includes('yoga') || typeLower.includes('gym') ||
                 typeLower.includes('fitness')) {
            guidelines += "For Health & Wellness:\n";
            guidelines += "- Homepage\n";
            guidelines += "- Services/Treatments page\n";
            guidelines += "- About the practitioner/team\n";
            guidelines += "- New patient/client information\n";
            guidelines += "- Insurance/Pricing\n";
            guidelines += "- Contact/Appointment\n";
            guidelines += "- 3-5 content pages (conditions treated, wellness tips, procedure guides)\n";
        }
        else if (typeLower.includes('account') || typeLower.includes('lawyer') ||
                 typeLower.includes('attorney') || typeLower.includes('insurance')) {
            guidelines += "For Professional Services:\n";
            guidelines += "- Homepage\n";
            guidelines += "- Services/Practice areas\n";
            guidelines += "- About/Credentials\n";
            guidelines += "- Resources/Blog\n";
            guidelines += "- Contact/Consultation\n";
            guidelines += "- 3-5 content pages (service deep-dives, guides, FAQs)\n";
        }
        else if (typeLower.includes('salon') || typeLower.includes('barber') ||
                 typeLower.includes('spa') || typeLower.includes('nail')) {
            guidelines += "For Beauty & Personal Care:\n";
            guidelines += "- Homepage\n";
            guidelines += "- Services/Menu\n";
            guidelines += "- About/Team\n";
            guidelines += "- Gallery/Portfolio\n";
            guidelines += "- Pricing\n";
            guidelines += "- Booking/Contact\n";
            guidelines += "- 3-5 content pages (style guides, care tips, trends)\n";
        }
        else {
            guidelines += "For Local Businesses:\n";
            guidelines += "- Homepage (service + location)\n";
            guidelines += "- Services/Products page\n";
            guidelines += "- About/Story\n";
            guidelines += "- Location/Service Area\n";
            guidelines += "- Contact/Quote\n";
            guidelines += "- 3-5 content pages (related topics, guides, FAQs)\n";
        }

        return guidelines;
    }

    /**
     * Get content requirements based on business type
     */
    getContentRequirements(businessType) {
        const typeLower = businessType.toLowerCase();

        let requirements = "CONTENT STRUCTURE:\n";
        requirements += "- H1: Page title (include primary keyword + location)\n";
        requirements += "- Introduction (150 words): Hook, include keyword in first 100 words, establish credibility\n";
        requirements += "- 6-8 H2 sections with detailed content\n";
        requirements += "- Conclusion (100 words): Summarize benefits, strong CTA\n\n";

        requirements += "SEO ELEMENTS:\n";
        requirements += "- Natural keyword usage (1-2% density)\n";
        requirements += "- Local references (neighborhoods, landmarks, zip codes)\n";
        requirements += "- Location mentions: 5-7 times naturally\n\n";

        requirements += "BUSINESS-SPECIFIC REQUIREMENTS:\n\n";

        if (typeLower.includes('plumb') || typeLower.includes('electric') || typeLower.includes('hvac')) {
            requirements += "For Home Services:\n";
            requirements += "- Emphasize licensing, insurance, bonding\n";
            requirements += "- Include emergency availability if applicable\n";
            requirements += "- Mention response times\n";
            requirements += "- Address common fears (mess, cost, reliability)\n";
            requirements += "- Specific service examples\n";
            requirements += "- Mention brands/equipment worked with\n";
        }
        else if (typeLower.includes('dentist') || typeLower.includes('chiropract')) {
            requirements += "For Health & Wellness:\n";
            requirements += "- Emphasize credentials and training\n";
            requirements += "- Address common anxieties/concerns\n";
            requirements += "- Explain procedures clearly\n";
            requirements += "- Mention insurance accepted\n";
            requirements += "- Patient comfort and care philosophy\n";
            requirements += "- Technology/modern equipment\n";
        }
        else if (typeLower.includes('restaurant') || typeLower.includes('bar') || typeLower.includes('cafe')) {
            requirements += "For Food & Drink:\n";
            requirements += "- Sensory details (aromas, textures, presentation)\n";
            requirements += "- Specific menu items by name\n";
            requirements += "- Chef/owner background\n";
            requirements += "- Sourcing/quality of ingredients\n";
            requirements += "- Ambiance and experience\n";
        }
        else if (typeLower.includes('salon') || typeLower.includes('barber')) {
            requirements += "For Beauty & Personal Care:\n";
            requirements += "- Before/after descriptions\n";
            requirements += "- Product lines used\n";
            requirements += "- Stylist/technician experience\n";
            requirements += "- Latest techniques/trends\n";
            requirements += "- Appointment/booking ease\n";
        }
        else {
            requirements += "For Local Businesses:\n";
            requirements += "- Emphasize experience and expertise\n";
            requirements += "- Address customer pain points\n";
            requirements += "- Show credentials/certifications\n";
            requirements += "- Explain process/what to expect\n";
            requirements += "- Include trust signals\n";
        }

        requirements += "\nTONE:\n";
        requirements += "- Professional but authentic\n";
        requirements += "- Write for local customers with specific needs\n";
        requirements += "- Avoid clichés and marketing-speak\n";
        requirements += "- Be specific, not vague\n";
        requirements += "- Show expertise through knowledge, not claims\n";

        return requirements;
    }
}

module.exports = ClaudeAPI;
